<?php

namespace Tests\Feature;

use App\Models\SystemSetting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class WhatsAppGatewayRouteTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Feature Test 1: Verifikasi bahwa route POST /api/system/test-whatsapp TERDAFTAR dan DAPAT DIAKSES (Bukan 404 Route Not Found).
     */
    public function test_test_whatsapp_route_is_reachable()
    {
        $user = User::factory()->create();
        \Laravel\Sanctum\Sanctum::actingAs($user);

        $response = $this->postJson('/api/system/test-whatsapp', [
            'phone' => '081234567890',
            'message' => 'Uji Coba Reachability Route',
        ]);

        $this->assertNotEquals(404, $response->getStatusCode(), 'Endpoint /api/system/test-whatsapp tidak boleh 404!');
    }

    /**
     * Feature Test 2: Verifikasi bahwa route POST /api/system/test-whatsapp mengembalikan response sukses saat Mock Mode aktif.
     */
    public function test_test_whatsapp_route_executes_successfully_via_http()
    {
        $user = User::factory()->create();
        \Laravel\Sanctum\Sanctum::actingAs($user);

        Config::set('services.fonnte.mock_enabled', true);

        $response = $this->postJson('/api/system/test-whatsapp', [
            'phone' => '081234567890',
            'message' => 'Uji Coba HTTP Route Mock',
        ]);

        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
        ]);
        $this->assertStringStartsWith('MOCK-', $response->json('data.response_id'));
    }

    /**
     * Feature Test 3: Verifikasi bahwa route GET /api/system/gateway-status terdaftar dan mengembalikan status konsisten.
     */
    public function test_gateway_status_route_returns_json_and_resolves_token_hierarchy()
    {
        $user = User::factory()->create();
        \Laravel\Sanctum\Sanctum::actingAs($user);

        // 1. Kondisi Unconfigured
        Config::set('services.fonnte.mock_enabled', false);
        Config::set('services.fonnte.token', '');
        SystemSetting::where('key', 'fonnte_api_key')->delete();

        $response = $this->getJson('/api/system/gateway-status');
        $response->assertStatus(200);
        $response->assertJson([
            'success' => true,
            'data' => [
                'state' => 'UNCONFIGURED',
                'token_configured' => false,
                'mock_enabled' => false,
            ]
        ]);

        // 2. Kondisi Token di DB (Primary Hierarchy)
        SystemSetting::updateOrCreate(
            ['key' => 'fonnte_api_key'],
            ['value' => 'real_fonnte_token_from_db', 'type' => 'STRING', 'group' => 'INTEGRATION']
        );

        $response2 = $this->getJson('/api/system/gateway-status');
        $response2->assertStatus(200);
        $response2->assertJson([
            'success' => true,
            'data' => [
                'state' => 'ACTIVE',
                'token_configured' => true,
            ]
        ]);
    }
}
