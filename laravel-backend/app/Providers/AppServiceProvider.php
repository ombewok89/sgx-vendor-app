<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\MasterDataController;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Fail-safe Route Binding: Ensures WhatsApp & Gateway routes are ALWAYS accessible regardless of route cache state
        Route::match(['get', 'post', 'options'], '/api/system/test-whatsapp', [MasterDataController::class, 'testWhatsApp']);
        Route::match(['get', 'post', 'options'], '/system/test-whatsapp', [MasterDataController::class, 'testWhatsApp']);
        Route::match(['get', 'post', 'options'], '/api/test-whatsapp', [MasterDataController::class, 'testWhatsApp']);
        Route::match(['get', 'post', 'options'], '/test-whatsapp', [MasterDataController::class, 'testWhatsApp']);
        Route::match(['get', 'post', 'options'], '/api/system/gateway-status', [MasterDataController::class, 'gatewayStatus']);
        Route::match(['get', 'post', 'options'], '/system/gateway-status', [MasterDataController::class, 'gatewayStatus']);
        Route::match(['get', 'post', 'options'], '/api/system/whatsapp/send-test', [\App\Http\Controllers\Api\WhatsAppController::class, 'sendTestMessage']);
        Route::match(['get', 'post', 'options'], '/system/whatsapp/send-test', [\App\Http\Controllers\Api\WhatsAppController::class, 'sendTestMessage']);
        Route::match(['get', 'post', 'options'], '/api/system/whatsapp/test-connection', [\App\Http\Controllers\Api\WhatsAppController::class, 'testConnection']);
        Route::match(['get', 'post', 'options'], '/system/whatsapp/test-connection', [\App\Http\Controllers\Api\WhatsAppController::class, 'testConnection']);
        Route::match(['get', 'post', 'options'], '/api/system/whatsapp/stats', [\App\Http\Controllers\Api\WhatsAppController::class, 'stats']);
        Route::match(['get', 'post', 'options'], '/system/whatsapp/stats', [\App\Http\Controllers\Api\WhatsAppController::class, 'stats']);
        Route::match(['get', 'post', 'options'], '/api/system/whatsapp/logs', [\App\Http\Controllers\Api\WhatsAppController::class, 'logs']);
        Route::match(['get', 'post', 'options'], '/system/whatsapp/logs', [\App\Http\Controllers\Api\WhatsAppController::class, 'logs']);
    }
}
