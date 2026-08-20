<?php

use Illuminate\Foundation\Application;
use Illuminate\Http\Request;

define('LARAVEL_START', microtime(true));

// Always sync root .env into laravel-backend/.env if root .env exists
if (file_exists(__DIR__.'/.env')) {
    @copy(__DIR__.'/.env', __DIR__.'/laravel-backend/.env');
}

// Auto-clean stale compiled caches so updates in routes/api.php and routes/web.php take effect immediately
foreach (glob(__DIR__.'/laravel-backend/bootstrap/cache/*.php') as $f) {
    if (basename($f) !== '.gitignore') {
        @unlink($f);
    }
}
foreach (glob(__DIR__.'/bootstrap/cache/*.php') as $f) {
    if (basename($f) !== '.gitignore') {
        @unlink($f);
    }
}

// 0. Direct Reliable WhatsApp Test Interceptor
$uri = $_SERVER['REQUEST_URI'] ?? '';
if (strpos($uri, 'test-whatsapp') !== false || strpos($uri, 'test-wa') !== false) {
    header('Content-Type: application/json');
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
    header('Access-Control-Allow-Headers: *');
    if (($_SERVER['REQUEST_METHOD'] ?? 'GET') === 'OPTIONS') {
        exit;
    }

    $raw = file_get_contents('php://input');
    $body = json_decode($raw, true) ?: $_POST;
    $phone = $body['phone'] ?? $_GET['phone'] ?? '';
    $message = $body['message'] ?? $_GET['message'] ?? '';

    if (empty($phone)) {
        echo json_encode(['success' => false, 'message' => 'Nomor WhatsApp tujuan wajib diisi.']);
        exit;
    }

    $phoneClean = preg_replace('/[^0-9]/', '', $phone);
    if (str_starts_with($phoneClean, '08')) {
        $phoneClean = '628' . substr($phoneClean, 2);
    }

    $envContent = file_exists(__DIR__.'/.env') ? file_get_contents(__DIR__.'/.env') : (file_exists(__DIR__.'/laravel-backend/.env') ? file_get_contents(__DIR__.'/laravel-backend/.env') : '');
    $apiKey = 'GoPzcxdiUP2yt5HbByUK';
    if (preg_match('/FONNTE_API_KEY=([^\s]+)/', $envContent, $m)) {
        $apiKey = trim($m[1], "\"' \r\n");
    }

    $msg = $message ?: "🔔 *SGX Work Evidence System Test*\n\nUji coba konektivitas WhatsApp Gateway Fonnte berhasil terhubung secara normal pada " . date('d/m/Y H:i:s') . " WIB.";

    $ch = curl_init('https://api.fonnte.com/send');
    curl_setopt_array($ch, [
        CURLOPT_POST => true,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => ["Authorization: {$apiKey}"],
        CURLOPT_POSTFIELDS => [
            'target' => $phoneClean,
            'message' => $msg,
            'countryCode' => '62'
        ],
        CURLOPT_TIMEOUT => 15,
    ]);
    $res = curl_exec($ch);
    $err = curl_error($ch);
    curl_close($ch);

    if ($err) {
        echo json_encode(['success' => false, 'message' => 'Gagal menghubungi Fonnte: ' . $err]);
        exit;
    }

    $data = json_decode($res, true) ?: ['raw' => $res];
    if (isset($data['status']) && $data['status'] === false) {
        $reason = $data['reason'] ?? 'Gagal memproses pesan di Fonnte';
        echo json_encode([
            'success' => false,
            'message' => 'Fonnte Gateway Error: ' . $reason . ' (Pastikan API Token dan Device di Fonnte sudah aktif / Connected).',
            'data' => $data
        ]);
        exit;
    }

    echo json_encode([
        'success' => true,
        'message' => 'Pesan WhatsApp berhasil dikirim ke antrean Fonnte (' . ($data['detail'] ?? 'Proses Sukses') . ').',
        'data' => $data
    ]);
    exit;
}

// Determine if the application is in maintenance mode...
if (file_exists($maintenance = __DIR__.'/laravel-backend/storage/framework/maintenance.php')) {
    require $maintenance;
}

// Register the Composer autoloader...
if (file_exists(__DIR__.'/laravel-backend/vendor/autoload.php')) {
    require __DIR__.'/laravel-backend/vendor/autoload.php';
    /** @var Application $app */
    $app = require_once __DIR__.'/laravel-backend/bootstrap/app.php';
    $app->handleRequest(Request::capture());
} elseif (file_exists(__DIR__.'/vendor/autoload.php')) {
    require __DIR__.'/vendor/autoload.php';
    /** @var Application $app */
    $app = require_once __DIR__.'/bootstrap/app.php';
    $app->handleRequest(Request::capture());
}
