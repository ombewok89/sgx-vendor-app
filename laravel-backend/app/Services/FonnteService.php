<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FonnteService — Single-responsibility gateway ke Fonnte WhatsApp API.
 *
 * PRODUCTION HARDENED:
 *   - Gateway Toggle: mendukung 'whatsapp_enabled' dan 'fonnte_enabled'.
 *   - Failure Classification: TEMPORARY (timeout, 5xx, rate limit) vs PERMANENT (invalid phone/token).
 *   - Idempotency & Retry: Update record yang ada tanpa duplikasi.
 *   - Security: Zero secret leak in logs, database, or API output.
 */
class FonnteService
{
    const BASE_URL        = 'https://api.fonnte.com';
    const SEND_TIMEOUT    = 15;  // detik — HTTP request timeout
    const CONNECT_TIMEOUT = 8;   // detik — TCP connection timeout

    // =========================================================================
    // PUBLIC API
    // =========================================================================

    /**
     * Kirim pesan WhatsApp menggunakan template dan catat ke NotificationLog.
     *
     * @param string      $phone          Nomor tujuan
     * @param string      $templateKey    Kunci template (e.g. WORK_ORDER_CREATED, TEST_MESSAGE)
     * @param array       $params         Variabel pengganti
     * @param string|null $idempotencyKey Kunci unik untuk cegah duplicate (e.g. WORK_ORDER_CREATED:123)
     * @param string|null $referenceType  Tipe entitas (e.g. WORK_ORDER)
     * @param int|null    $referenceId    ID entitas
     * @param int|null    $existingLogId  ID log yang sudah ada (untuk retry)
     * @return array
     */
    public static function sendTemplatedMessage(
        string $phone,
        string $templateKey,
        array $params = [],
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $existingLogId = null
    ): array {
        // 1. Idempotency Check: cegah pengiriman ganda jika bukan manual retry
        if (!empty($idempotencyKey) && !$existingLogId) {
            $existing = NotificationLog::where('idempotency_key', $idempotencyKey)
                ->where('status', 'SENT')
                ->first();

            if ($existing) {
                Log::info('[Fonnte] Pesan dilewati karena idempotency key sudah terkirim.', [
                    'idempotency_key' => $idempotencyKey,
                ]);
                return [
                    'success'      => true,
                    'status'       => 200,
                    'message'      => 'Pesan sudah pernah dikirim sebelumnya (idempotency match).',
                    'skipped'      => true,
                    'failure_type' => null,
                ];
            }
        }

        // 2. Render isi pesan dari template
        $messageContent = WhatsAppTemplateService::render($templateKey, $params);

        // 3. Eksekusi pengiriman
        return self::sendMessage(
            $phone,
            $messageContent,
            $templateKey,
            $idempotencyKey,
            $referenceType,
            $referenceId,
            $existingLogId
        );
    }

    /**
     * Kirim pesan WhatsApp ke nomor tujuan dan catat log pengiriman.
     */
    public static function sendMessage(
        string $phone,
        string $message,
        string $messageType = 'CUSTOM_MESSAGE',
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $existingLogId = null
    ): array {
        $normalizedPhone = self::normalizePhone($phone);
        $masked          = self::maskPhone($normalizedPhone ?: $phone);
        $cleanMessage    = strip_tags(trim($message));

        // Guard: gateway dinonaktifkan
        if (!self::isEnabled()) {
            self::logNotification(
                $masked,
                $messageType,
                $cleanMessage,
                'SKIPPED',
                'PERMANENT',
                'WhatsApp gateway dinonaktifkan (fonnte_enabled/whatsapp_enabled=0)',
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId,
                null,
                $existingLogId
            );
            return self::result(false, null, 'WhatsApp gateway dinonaktifkan.', 'PERMANENT');
        }

        // Guard: API token tidak tersedia
        $token = self::getApiToken();
        if (empty($token)) {
            Log::warning('[Fonnte] sendMessage skipped: fonnte_api_key belum dikonfigurasi di System Settings.');
            self::logNotification(
                $masked,
                $messageType,
                $cleanMessage,
                'FAILED',
                'PERMANENT',
                'fonnte_api_key belum dikonfigurasi',
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId,
                null,
                $existingLogId
            );
            return self::result(false, null, 'fonnte_api_key belum dikonfigurasi.', 'PERMANENT');
        }

        // Guard: format nomor tidak valid
        if (empty($normalizedPhone)) {
            Log::warning('[Fonnte] sendMessage skipped: format nomor tidak valid.', [
                'masked_input' => $masked,
            ]);
            self::logNotification(
                $masked,
                $messageType,
                $cleanMessage,
                'FAILED',
                'PERMANENT',
                'Format nomor tidak valid: ' . $masked,
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId,
                null,
                $existingLogId
            );
            return self::result(false, null, 'Format nomor tidak valid: ' . $masked, 'PERMANENT');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])
            ->timeout(self::SEND_TIMEOUT)
            ->connectTimeout(self::CONNECT_TIMEOUT)
            ->asForm()
            ->post(self::BASE_URL . '/send', [
                'target'      => $normalizedPhone,
                'message'     => $cleanMessage,
                'countryCode' => '62',
            ]);

            $httpStatus = $response->status();
            $body       = $response->json() ?? [];
            $providerOk = ($body['status'] ?? false) === true;

            if ($response->successful() && $providerOk) {
                Log::info('[Fonnte] Pesan terkirim.', [
                    'to'          => $masked,
                    'http_status' => $httpStatus,
                    'result'      => 'success',
                ]);

                self::logNotification(
                    $masked,
                    $messageType,
                    $cleanMessage,
                    'SENT',
                    null,
                    null,
                    json_encode(['status' => true, 'target' => $masked, 'http' => $httpStatus]),
                    $idempotencyKey,
                    $referenceType,
                    $referenceId,
                    now(),
                    $existingLogId
                );

                return self::result(true, $httpStatus, 'Pesan WhatsApp berhasil dikirim.', null);
            }

            $reason = $body['reason'] ?? ($body['message'] ?? 'Unknown error from provider');
            // Classify 429, 500, 502, 503, 504 as TEMPORARY failure
            $isTemporary = $httpStatus >= 500 || $httpStatus === 429 || str_contains(strtolower($reason), 'timeout') || str_contains(strtolower($reason), 'busy');
            $failureType = $isTemporary ? 'TEMPORARY' : 'PERMANENT';

            Log::warning('[Fonnte] Pesan gagal dikirim.', [
                'to'           => $masked,
                'http_status'  => $httpStatus,
                'failure_type' => $failureType,
                'reason'       => $reason,
                'result'       => 'failed',
            ]);

            self::logNotification(
                $masked,
                $messageType,
                $cleanMessage,
                'FAILED',
                $failureType,
                "Provider error: {$reason}",
                json_encode(['status' => false, 'http' => $httpStatus, 'reason' => $reason]),
                $idempotencyKey,
                $referenceType,
                $referenceId,
                null,
                $existingLogId
            );

            return self::result(false, $httpStatus, "Provider error: {$reason}", $failureType);

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[Fonnte] Koneksi gagal (TEMPORARY).', [
                'to'    => $masked,
                'error' => 'ConnectionException: ' . class_basename($e),
            ]);

            self::logNotification(
                $masked,
                $messageType,
                $cleanMessage,
                'FAILED',
                'TEMPORARY',
                'Koneksi timeout/refused ke Fonnte API',
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId,
                null,
                $existingLogId
            );

            return self::result(false, null, 'Koneksi ke Fonnte gagal (timeout/refused).', 'TEMPORARY');

        } catch (\Throwable $e) {
            Log::error('[Fonnte] Error tidak terduga.', [
                'to'    => $masked,
                'error' => class_basename($e) . ': ' . $e->getMessage(),
            ]);

            self::logNotification(
                $masked,
                $messageType,
                $cleanMessage,
                'FAILED',
                'PERMANENT',
                class_basename($e) . ': ' . $e->getMessage(),
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId,
                null,
                $existingLogId
            );

            return self::result(false, null, 'Error: ' . class_basename($e), 'PERMANENT');
        }
    }

    /**
     * Test koneksi ke Fonnte API menggunakan endpoint /device.
     */
    public static function testConnection(): array
    {
        if (!self::isEnabled()) {
            return [
                'success'     => false,
                'message'     => 'WhatsApp gateway sedang dinonaktifkan (whatsapp_enabled=0).',
                'http_status' => null,
                'device'      => null,
            ];
        }

        $token = self::getApiToken();
        if (empty($token)) {
            return [
                'success'     => false,
                'message'     => 'fonnte_api_key belum dikonfigurasi di System Settings.',
                'http_status' => null,
                'device'      => null,
            ];
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $token,
            ])
            ->timeout(self::CONNECT_TIMEOUT)
            ->connectTimeout(6)
            ->post(self::BASE_URL . '/device');

            $httpStatus = $response->status();
            $body       = $response->json() ?? [];

            if ($response->ok()) {
                return [
                    'success'     => true,
                    'message'     => 'Fonnte gateway dapat dijangkau dan token valid.',
                    'http_status' => $httpStatus,
                    'device'      => [
                        'name'   => $body['name']   ?? null,
                        'device' => $body['device'] ?? null,
                        'status' => $body['status'] ?? null,
                        'quota'  => $body['quota']  ?? null,
                    ],
                ];
            }

            $reason = $body['reason'] ?? ($body['message'] ?? 'HTTP ' . $httpStatus);
            return [
                'success'     => false,
                'message'     => "Gateway mengembalikan HTTP {$httpStatus}: {$reason}",
                'http_status' => $httpStatus,
                'device'      => null,
            ];

        } catch (\Throwable $e) {
            return [
                'success'     => false,
                'message'     => 'Tidak dapat menjangkau Fonnte API: ' . class_basename($e),
                'http_status' => null,
                'device'      => null,
            ];
        }
    }

    /**
     * Cek apakah WhatsApp gateway diaktifkan.
     * Cek key 'whatsapp_enabled' dan 'fonnte_enabled'.
     */
    public static function isEnabled(): bool
    {
        $val1 = SystemSetting::where('key', 'whatsapp_enabled')->value('value');
        $val2 = SystemSetting::where('key', 'fonnte_enabled')->value('value');

        if ($val1 !== null && !filter_var($val1, FILTER_VALIDATE_BOOLEAN)) return false;
        if ($val2 !== null && !filter_var($val2, FILTER_VALIDATE_BOOLEAN)) return false;

        return true;
    }

    // =========================================================================
    // PHONE NORMALIZATION
    // =========================================================================

    public static function normalizePhone(string $phone): ?string
    {
        if (empty(trim($phone))) return null;

        $cleaned = preg_replace('/[^0-9]/', '', ltrim(trim($phone), '+'));

        if (strlen($cleaned) < 7) return null;

        if (str_starts_with($cleaned, '62')) {
            return strlen($cleaned) >= 10 ? $cleaned : null;
        }

        if (str_starts_with($cleaned, '0')) {
            $normalized = '62' . substr($cleaned, 1);
            return strlen($normalized) >= 10 ? $normalized : null;
        }

        if (str_starts_with($cleaned, '8') && strlen($cleaned) >= 9) {
            return '62' . $cleaned;
        }

        return strlen($cleaned) >= 10 ? $cleaned : null;
    }

    public static function maskPhone(string $phone): string
    {
        $len = strlen($phone);
        if ($len <= 6) return str_repeat('*', $len);

        $showStart = min(5, (int) ($len / 2));
        $showEnd   = min(3, $len - $showStart);
        $maskLen   = $len - $showStart - $showEnd;

        if ($maskLen <= 0) return $phone;

        return substr($phone, 0, $showStart)
            . str_repeat('*', $maskLen)
            . substr($phone, -$showEnd);
    }

    // =========================================================================
    // PRIVATE LOGGING & HELPERS
    // =========================================================================

    private static function logNotification(
        string $recipient,
        string $messageType,
        string $messageText,
        string $status,
        ?string $failureType = null,
        ?string $errorMessage = null,
        ?string $providerResponse = null,
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        $sentAt = null,
        ?int $existingLogId = null
    ): void {
        try {
            if ($existingLogId) {
                $log = NotificationLog::find($existingLogId);
                if ($log) {
                    $log->update([
                        'status'            => $status,
                        'attempts'          => $log->attempts + 1,
                        'failure_type'      => $failureType,
                        'error_message'     => $errorMessage,
                        'provider_response' => $providerResponse,
                        'sent_at'           => $sentAt ?? $log->sent_at,
                    ]);
                    return;
                }
            }

            NotificationLog::create([
                'channel'           => 'WHATSAPP',
                'provider'          => 'FONNTE',
                'recipient'         => $recipient,
                'message_type'      => $messageType,
                'reference_type'    => $referenceType,
                'reference_id'      => $referenceId,
                'idempotency_key'   => $idempotencyKey,
                'payload'           => json_encode(['text' => $messageText]),
                'status'            => $status,
                'attempts'          => 1,
                'failure_type'      => $failureType,
                'provider_response' => $providerResponse,
                'error_message'     => $errorMessage,
                'sent_at'           => $sentAt,
            ]);
        } catch (\Throwable $e) {
            Log::warning('[Fonnte] Gagal menyimpan log notifikasi: ' . $e->getMessage());
        }
    }

    private static function getApiToken(): ?string
    {
        $token = SystemSetting::where('key', 'fonnte_api_key')->value('value');
        if (empty($token) || $token === 'FONNTE_DEMO_KEY_SGX_2026') {
            return null;
        }
        return $token;
    }

    private static function result(bool $success, ?int $status, string $message, ?string $failureType = null): array
    {
        return [
            'success'      => $success,
            'status'       => $status,
            'message'      => $message,
            'failure_type' => $failureType,
        ];
    }
}
