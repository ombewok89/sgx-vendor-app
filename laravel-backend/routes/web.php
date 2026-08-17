<?php

use Illuminate\Support\Facades\Route;

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
