<?php

namespace App\Services;

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
 *   - Nomor tujuan selalu di-mask di log.
 *   - Gateway failure TIDAK boleh membuat transaksi bisnis utama gagal.
 *
 * USAGE:
 *   $result = FonnteService::sendMessage('08123456789', 'Halo!');
 *   // Returns: ['success' => bool, 'status' => int|null, 'message' => string]
 */
class FonnteService
{
    const BASE_URL       = 'https://api.fonnte.com';
    const SEND_TIMEOUT   = 15;  // detik — HTTP request timeout
    const CONNECT_TIMEOUT = 8;  // detik — TCP connection timeout

    // =========================================================================
    // PUBLIC API
    // =========================================================================

    /**
     * Kirim pesan WhatsApp ke nomor tujuan.
     *
     * @param  string $phone   Nomor tujuan (format bebas Indonesia: 08x, 62x, +62x)
     * @param  string $message Isi pesan teks
     * @return array  ['success' => bool, 'status' => int|null, 'message' => string]
     */
    public static function sendMessage(string $phone, string $message): array
    {
        // Guard: gateway dinonaktifkan
        if (!self::isEnabled()) {
            return self::result(false, null, 'WhatsApp gateway dinonaktifkan (whatsapp_enabled=0).');
        }

        // Guard: API token tidak tersedia
        $token = self::getApiToken();
        if (empty($token)) {
            Log::warning('[Fonnte] sendMessage skipped: fonnte_api_key belum dikonfigurasi di System Settings.');
            return self::result(false, null, 'fonnte_api_key belum dikonfigurasi.');
        }

        // Normalisasi & validasi nomor
        $normalizedPhone = self::normalizePhone($phone);
        if (empty($normalizedPhone)) {
            Log::warning('[Fonnte] sendMessage skipped: format nomor tidak valid.', [
                'masked_input' => self::maskPhone($phone),
            ]);
            return self::result(false, null, 'Format nomor tidak valid: ' . self::maskPhone($phone));
        }

        $masked = self::maskPhone($normalizedPhone);

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
                return self::result(true, $httpStatus, 'Pesan WhatsApp berhasil dikirim.');
            }

            $reason = $body['reason'] ?? ($body['message'] ?? 'Unknown error from provider');
            Log::warning('[Fonnte] Pesan gagal dikirim.', [
                'to'          => $masked,
                'http_status' => $httpStatus,
                'reason'      => $reason,
                'result'      => 'failed',
            ]);
            return self::result(false, $httpStatus, "Provider error: {$reason}");

        } catch (\Illuminate\Http\Client\ConnectionException $e) {
            Log::error('[Fonnte] Koneksi gagal.', [
                'to'    => $masked,
                'error' => 'ConnectionException: ' . class_basename($e),
            ]);
            return self::result(false, null, 'Koneksi ke Fonnte gagal (timeout/refused).');

        } catch (\Throwable $e) {
            Log::error('[Fonnte] Error tidak terduga.', [
                'to'    => $masked,
                'error' => class_basename($e) . ': ' . $e->getMessage(),
            ]);
            return self::result(false, null, 'Error: ' . class_basename($e));
        }
    }

    /**
     * Test koneksi ke Fonnte API menggunakan endpoint /device.
     * Tidak mengirim pesan — hanya memvalidasi token dan reachability.
     *
     * @return array ['success' => bool, 'message' => string, 'http_status' => int|null, 'device' => array|null]
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
     * Baca dari system_settings key: whatsapp_enabled
     * Default: true (jika key belum ada).
     */
    public static function isEnabled(): bool
    {
        $val = SystemSetting::where('key', 'whatsapp_enabled')->value('value');
        if ($val === null) return true; // default aktif jika belum ada setting
        return filter_var($val, FILTER_VALIDATE_BOOLEAN);
    }

    // =========================================================================
    // PHONE NORMALIZATION
    // =========================================================================

    /**
     * Normalisasi nomor telepon Indonesia ke format 62xxx.
     *
     * Input yang diterima:
     *   08xxxxxxxxx  →  628xxxxxxxxx
     *   628xxxxxxxxx →  628xxxxxxxxx  (no change)
     *   +628xxxxxxxx →  628xxxxxxxxx  (strip +)
     *   8xxxxxxxxx   →  628xxxxxxxxx  (leading 8, asumsi Indonesia)
     *
     * @param  string $phone Input mentah
     * @return string|null   Nomor ternormalisasi, atau null jika tidak valid
     */
    public static function normalizePhone(string $phone): ?string
    {
        if (empty(trim($phone))) return null;

        // Hapus semua karakter non-digit (kecuali + di awal)
        $cleaned = preg_replace('/[^0-9]/', '', ltrim(trim($phone), '+'));

        // Terlalu pendek / kosong
        if (strlen($cleaned) < 7) return null;

        // Sudah format 62xxx
        if (str_starts_with($cleaned, '62')) {
            return strlen($cleaned) >= 10 ? $cleaned : null;
        }

        // Format 08xxx → 628xxx
        if (str_starts_with($cleaned, '0')) {
            $normalized = '62' . substr($cleaned, 1);
            return strlen($normalized) >= 10 ? $normalized : null;
        }

        // Format 8xxx → 628xxx (nomor Indonesia dimulai 8)
        if (str_starts_with($cleaned, '8') && strlen($cleaned) >= 9) {
            return '62' . $cleaned;
        }

        // Format tidak dikenal — kembalikan apa adanya jika panjang cukup
        return strlen($cleaned) >= 10 ? $cleaned : null;
    }

    // =========================================================================
    // PRIVATE HELPERS
    // =========================================================================

    /**
     * Ambil API token dari SystemSetting.
     * JANGAN log, print, atau expose token ini.
     */
    private static function getApiToken(): ?string
    {
        $token = SystemSetting::where('key', 'fonnte_api_key')->value('value');
        if (empty($token) || $token === 'FONNTE_DEMO_KEY_SGX_2026') {
            return null; // Demo key dianggap belum dikonfigurasi
        }
        return $token;
    }

    /**
     * Mask nomor telepon untuk logging yang aman.
     * Contoh: 628123456789  →  62812*****789
     */
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

    /**
     * Helper untuk membuat array result standar.
     */
    private static function result(bool $success, ?int $status, string $message): array
    {
        return [
            'success' => $success,
            'status'  => $status,
            'message' => $message,
        ];
    }
}
