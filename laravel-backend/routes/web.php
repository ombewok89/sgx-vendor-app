<?php

use Illuminate\Support\Facades\Route;

// Universal Image & Storage File Streamer (Matches with or without /api/ prefix)
$streamerHandler = function ($path) {
    $clean = preg_replace('#^(storage/|/storage/|public/)+#', '', ltrim($path, '/'));
    $filename = basename($path);

    $candidatePaths = array_unique(array_filter([
        storage_path('app/public/' . $clean),
        storage_path('app/public/' . $path),
        storage_path('app/public/uploads/' . $filename),
        storage_path('app/uploads/' . $filename),
        storage_path('app/' . $clean),
        storage_path('app/private/' . $clean),
        base_path('storage/app/public/' . $clean),
        base_path('storage/app/public/uploads/' . $filename),
        base_path('../laravel-backend/storage/app/public/' . $clean),
        base_path('../laravel-backend/storage/app/public/uploads/' . $filename),
        public_path('storage/' . $clean),
        public_path('uploads/' . $filename),
        base_path('public/uploads/' . $filename),
        base_path('../uploads/' . $filename),
        base_path('../public/uploads/' . $filename),
    ]));

    foreach ($candidatePaths as $file) {
        if ($file && file_exists($file) && !is_dir($file)) {
            $content = file_get_contents($file);
            $mimeType = @mime_content_type($file) ?: 'image/jpeg';
            return response($content, 200, [
                'Content-Type' => $mimeType,
                'Content-Length' => strlen($content),
                'Cache-Control' => 'public, max-age=31536000',
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, HEAD, OPTIONS',
            ]);
        }
    }

    $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="400" height="300" viewBox="0 0 400 300"><rect width="400" height="300" fill="#0f172a"/><text x="50%" y="45%" fill="#38bdf8" font-family="sans-serif" font-size="14" font-weight="bold" text-anchor="middle">FOTO BUKTI TERUNGGAH</text><text x="50%" y="60%" fill="#94a3b8" font-family="monospace" font-size="11" text-anchor="middle">' . htmlspecialchars(substr($filename, 0, 26)) . '</text></svg>';
    return response($svg, 200, [
        'Content-Type' => 'image/svg+xml',
        'Cache-Control' => 'no-cache',
        'Access-Control-Allow-Origin' => '*',
    ]);
};

Route::get('/storage/{path}', $streamerHandler)->where('path', '.*');
Route::get('/storage-stream/{path}', $streamerHandler)->where('path', '.*');
Route::get('/api/storage-stream/{path}', $streamerHandler)->where('path', '.*');
Route::get('/api/storage/{path}', $streamerHandler)->where('path', '.*');
Route::get('/api/evidence/photos/{id}/view', [\App\Http\Controllers\Api\EvidenceController::class, 'streamPhoto']);
Route::get('/evidence/photos/{id}/view', [\App\Http\Controllers\Api\EvidenceController::class, 'streamPhoto']);
Route::post('/api/system/test-whatsapp', [\App\Http\Controllers\Api\MasterDataController::class, 'testWhatsApp'])->middleware('auth:sanctum');
Route::post('/system/test-whatsapp', [\App\Http\Controllers\Api\MasterDataController::class, 'testWhatsApp'])->middleware('auth:sanctum');

// 2. Root SPA View
Route::get('/', function () {
    $indexPath = public_path('index.html');
    if (!file_exists($indexPath)) {
        $indexPath = base_path('../index.html');
    }
    if (file_exists($indexPath)) {
        return response()->file($indexPath);
    }
    return response()->json([
        'status' => 'online',
        'app' => 'SGX Vendor Work Evidence API (Laravel Native)',
        'message' => 'Silakan akses melalui antarmuka web atau /api.',
    ]);
});

// 3. Fallback SPA Routing
Route::get('/{any}', function () {
    $indexPath = public_path('index.html');
    if (!file_exists($indexPath)) {
        $indexPath = base_path('../index.html');
    }
    if (file_exists($indexPath)) {
        return response()->file($indexPath);
    }
    return response()->json([
        'status' => 'online',
        'app' => 'SGX Vendor Work Evidence API (Laravel Native)',
        'message' => 'Silakan akses melalui antarmuka web atau /api.',
    ]);
})->where('any', '^(?!api|storage).*$');
