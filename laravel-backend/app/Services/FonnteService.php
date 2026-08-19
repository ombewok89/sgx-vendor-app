<?php

namespace App\Services;

use App\Models\NotificationLog;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

/**
 * FonnteService — Single-responsibility gateway ke Fonnte WhatsApp API.
 *
 * ARCHITECTURE:
 *   Controller / Service  →  FonnteService  →  Fonnte API
 *
 * SECURITY RULES:
 *   - Token TIDAK PERNAH di-log, di-print, atau dikembalikan ke response.
 *   - Nomor tujuan selalu di-mask di log & database.
 *   - Gateway failure TIDAK boleh membuat transaksi bisnis utama gagal.
 *   - Idempotency proteksi double-send.
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
     * @return array
     */
    public static function sendTemplatedMessage(
        string $phone,
        string $templateKey,
        array $params = [],
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        // 1. Idempotency Check: cegah pengiriman ganda untuk event yang sama
        if (!empty($idempotencyKey)) {
            $existing = NotificationLog::where('idempotency_key', $idempotencyKey)
                ->where('status', 'SENT')
                ->first();

            if ($existing) {
                Log::info('[Fonnte] Pesan dilewati karena idempotency key sudah terkirim.', [
                    'idempotency_key' => $idempotencyKey,
                ]);
                return [
                    'success' => true,
                    'status'  => 200,
                    'message' => 'Pesan sudah pernah dikirim sebelumnya (idempotency match).',
                    'skipped' => true,
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
            $referenceId
        );
    }

    /**
     * Kirim pesan WhatsApp ke nomor tujuan dan catat log pengiriman.
     *
     * @param  string      $phone
     * @param  string      $message
     * @param  string      $messageType
     * @param  string|null $idempotencyKey
     * @param  string|null $referenceType
     * @param  int|null    $referenceId
     * @return array  ['success' => bool, 'status' => int|null, 'message' => string]
     */
    public static function sendMessage(
        string $phone,
        string $message,
        string $messageType = 'CUSTOM_MESSAGE',
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): array {
        $normalizedPhone = self::normalizePhone($phone);
        $masked          = self::maskPhone($normalizedPhone ?: $phone);

        // Guard: gateway dinonaktifkan
        if (!self::isEnabled()) {
            self::logNotification(
                $masked,
                $messageType,
                $message,
                'SKIPPED',
                'Gateway dinonaktifkan (whatsapp_enabled=0)',
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );
            return self::result(false, null, 'WhatsApp gateway dinonaktifkan (whatsapp_enabled=0).');
        }

        // Guard: API token tidak tersedia
        $token = self::getApiToken();
        if (empty($token)) {
            Log::warning('[Fonnte] sendMessage skipped: fonnte_api_key belum dikonfigurasi di System Settings.');
            self::logNotification(
                $masked,
                $messageType,
                $message,
                'FAILED',
                'fonnte_api_key belum dikonfigurasi',
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );
            return self::result(false, null, 'fonnte_api_key belum dikonfigurasi.');
        }

        // Guard: format nomor tidak valid
        if (empty($normalizedPhone)) {
            Log::warning('[Fonnte] sendMessage skipped: format nomor tidak valid.', [
                'masked_input' => $masked,
            ]);
            self::logNotification(
                $masked,
                $messageType,
                $message,
                'FAILED',
                'Format nomor tidak valid: ' . $masked,
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );
            return self::result(false, null, 'Format nomor tidak valid: ' . $masked);
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
                'message'     => $message,
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
                    $message,
                    'SENT',
                    null,
                    json_encode(['status' => true, 'target' => $masked, 'http' => $httpStatus]),
                    $idempotencyKey,
                    $referenceType,
                    $referenceId,
                    now()
                );

                return self::result(true, $httpStatus, 'Pesan WhatsApp berhasil dikirim.');
            }

            $reason = $body['reason'] ?? ($body['message'] ?? 'Unknown error from provider');
            Log::warning('[Fonnte] Pesan gagal dikirim.', [
                'to'          => $masked,
                'http_status' => $httpStatus,
                'reason'      => $reason,
                'result'      => 'failed',
            ]);

            self::logNotification(
                $masked,
                $messageType,
                $message,
                'FAILED',
                "Provider error: {$reason}",
                json_encode(['status' => false, 'http' => $httpStatus, 'reason' => $reason]),
                $idempotencyKey,
                $referenceType,
                $referenceId
            );

            return self::result(false, $httpStatus, "Provider error: {$reason}");

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[Fonnte] Koneksi gagal.', [
                'to'    => $masked,
                'error' => 'ConnectionException: ' . class_basename($e),
            ]);

            self::logNotification(
                $masked,
                $messageType,
                $message,
                'FAILED',
                'Koneksi timeout/refused ke Fonnte API',
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );

            return self::result(false, null, 'Koneksi ke Fonnte gagal (timeout/refused).');

        } catch (\Throwable $e) {
            Log::error('[Fonnte] Error tidak terduga.', [
                'to'    => $masked,
                'error' => class_basename($e) . ': ' . $e->getMessage(),
            ]);

            self::logNotification(
                $masked,
                $messageType,
                $message,
                'FAILED',
                class_basename($e) . ': ' . $e->getMessage(),
                null,
                $idempotencyKey,
                $referenceType,
                $referenceId
            );

            return self::result(false, null, 'Error: ' . class_basename($e));
        }
    }

    /**
     * Test koneksi ke Fonnte API menggunakan endpoint /device.
     */
    public static function testConnection(): array
    {
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
            ->get(self::BASE_URL . '/device');

            $httpStatus = $response->status();
            $body       = $response->json() ?? [];

            Log::info('[Fonnte] Connection test.', [
                'http_status' => $httpStatus,
                'reachable'   => $response->ok(),
                'result'      => $response->ok() ? 'success' : 'failed',
            ]);

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

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::warning('[Fonnte] Connection test gagal — tidak bisa menjangkau API.', [
                'error' => class_basename($e),
            ]);
            return [
                'success'     => false,
                'message'     => 'Tidak dapat menjangkau Fonnte API (timeout/connection refused).',
                'http_status' => null,
                'device'      => null,
            ];

        } catch (\Throwable $e) {
            Log::warning('[Fonnte] Connection test error.', [
                'error' => class_basename($e) . ': ' . $e->getMessage(),
            ]);
            return [
                'success'     => false,
                'message'     => 'Error: ' . class_basename($e),
                'http_status' => null,
                'device'      => null,
            ];
        }
    }

    /**
     * Cek apakah WhatsApp gateway diaktifkan.
     */
    public static function isEnabled(): bool
    {
        $val = SystemSetting::where('key', 'whatsapp_enabled')->value('value');
        if ($val === null) return true;
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
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
        ?string $errorMessage = null,
        ?string $providerResponse = null,
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        $sentAt = null
    ): void {
        try {
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

    private static function result(bool $success, ?int $status, string $message): array
    {
        return [
            'success' => $success,
            'status'  => $status,
            'message' => $message,
        ];
    }
}
