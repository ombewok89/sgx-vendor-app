<?php

use Illuminate\Support\Facades\Route;

// 1. Direct Image & Storage File Streamer (Works even without symlinks)
Route::get('/storage/{path}', function ($path) {
    $candidatePaths = [
        storage_path('app/public/' . $path),
        base_path('storage/app/public/' . $path),
        base_path('../laravel-backend/storage/app/public/' . $path),
        public_path('storage/' . $path),
        base_path('../uploads/' . $path),
    ];

    foreach ($candidatePaths as $file) {
        if (file_exists($file) && !is_dir($file)) {
            $mimeType = mime_content_type($file) ?: 'image/jpeg';
            return response()->file($file, [
                'Content-Type' => $mimeType,
                'Cache-Control' => 'public, max-age=86400',
            ]);
        }
    }

    return response('File not found', 404);
})->where('path', '.*');

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
