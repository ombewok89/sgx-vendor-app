<?php

namespace Tests\Unit;

use App\Models\NotificationLog;
use App\Models\SystemSetting;
use App\Services\FonnteService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class FonnteServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test 1: Validasi normalisasi nomor HP
     * Input 081234567890, +6281234567890, 6281234567890 -> semua menjadi 6281234567890
     */
    public function test_normalize_phone_number_formats()
    {
        $this->assertEquals('6281234567890', FonnteService::normalizePhoneNumber('081234567890'));
        $this->assertEquals('6281234567890', FonnteService::normalizePhoneNumber('+6281234567890'));
        $this->assertEquals('6281234567890', FonnteService::normalizePhoneNumber('6281234567890'));
        $this->assertEquals('6281234567890', FonnteService::normalizePhoneNumber('0812-3456-7890'));
        $this->assertEquals('6281234567890', FonnteService::normalizePhoneNumber('+62 812 3456 7890'));
        $this->assertEquals('6281234567890', FonnteService::normalizePhoneNumber('81234567890'));
    }

    /**
     * Test 2: Token kosong dengan mock_disabled = false
     * Harus GAGAL eksplisit, return false, dan mencatat status FAILED dengan error message jelas.
     */
    public function test_empty_token_with_mock_disabled_fails_explicitly()
    {
        Config::set('services.fonnte.mock_enabled', false);
        Config::set('services.fonnte.token', '');
        SystemSetting::where('key', 'fonnte_api_key')->delete();

        $result = FonnteService::sendMessage('081234567890', 'Pesan Uji Coba');

        $this->assertFalse($result['success']);
        $this->assertEquals('FAILED', $result['status']);
        $this->assertStringContainsString('FONNTE_TOKEN belum dikonfigurasi', $result['error']);

        $this->assertDatabaseHas('notification_logs', [
            'recipient' => '6281234567890',
            'status' => 'FAILED',
            'error_message' => 'FONNTE_TOKEN belum dikonfigurasi di dashboard maupun .env',
        ]);
    }

    /**
     * Test 3: Mode mock eksplisit saat diaktifkan
     * Harus mencatat log dengan fonnte_response_id berawalan 'MOCK-' tanpa memanggil API eksternal.
     */
    public function test_mock_mode_when_enabled_returns_mock_id()
    {
        Http::fake();
        Config::set('services.fonnte.mock_enabled', true);

        $result = FonnteService::sendMessage('081234567890', 'Pesan Uji Coba Mock');

        $this->assertTrue($result['success']);
        $this->assertTrue($result['mock']);
        $this->assertStringStartsWith('MOCK-', $result['response_id']);

        $this->assertDatabaseHas('notification_logs', [
            'recipient' => '6281234567890',
            'status' => 'SENT',
        ]);

        Http::assertNothingSent();
    }

    /**
     * Test 4: Pengiriman API Fonnte sukses
     * Respons sukses mencatat status SENT dan fonnte_response_id asli.
     */
    public function test_successful_api_dispatch_logs_sent_status()
    {
        Config::set('services.fonnte.mock_enabled', false);
        Config::set('services.fonnte.token', 'valid_test_token_123');

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => true,
                'id' => ['174189999'],
                'detail' => 'success! message in queue',
                'target' => ['6281234567890'],
            ], 200),
        ]);

        $result = FonnteService::sendMessage('081234567890', 'Pesan Sukses');

        $this->assertTrue($result['success']);
        $this->assertEquals('SENT', $result['status']);
        $this->assertEquals('174189999', $result['response_id']);

        $this->assertDatabaseHas('notification_logs', [
            'recipient' => '6281234567890',
            'status' => 'SENT',
            'fonnte_response_id' => '174189999',
        ]);
    }

    /**
     * Test 5: ROOT CAUSE TEST - HTTP 200 tetapi data.status: false
     * Menguji saat Fonnte membalas HTTP 200 dengan status: false (contoh: unknown token / device disconnected)
     * Sistem WAJIB menganggap FAILED dan mencatat error asli Fonnte (tidak boleh lapor sukses palsu).
     */
    public function test_http_200_with_fonnte_status_false_marks_failed()
    {
        Config::set('services.fonnte.mock_enabled', false);
        Config::set('services.fonnte.token', 'invalid_token_xyz');

        Http::fake([
            'https://api.fonnte.com/send' => Http::response([
                'status' => false,
                'reason' => 'device disconnected',
            ], 200),
        ]);

        $result = FonnteService::sendMessage('081234567890', 'Pesan Uji Coba Disconnected');

        $this->assertFalse($result['success']);
        $this->assertEquals('FAILED', $result['status']);
        $this->assertEquals('device disconnected', $result['error']);

        $this->assertDatabaseHas('notification_logs', [
            'recipient' => '6281234567890',
            'status' => 'FAILED',
            'error_message' => 'device disconnected',
        ]);
    }
}
