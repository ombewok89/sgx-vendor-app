<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\WorkOrderController;
use App\Http\Controllers\Api\CheckInController;
use App\Http\Controllers\Api\EvidenceController;
use App\Http\Controllers\Api\ReviewController;
use App\Http\Controllers\Api\BaDocumentController;
use App\Http\Controllers\Api\MasterDataController;
use App\Http\Controllers\Api\NotificationController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\PermissionController;

// Public Health Check
Route::get('/health', function () {
    return response()->json([
        'status' => 'online',
        'app' => 'SGX Vendor Work Evidence API (Laravel Native)',
        'timestamp' => now()->toIso8601String(),
        'environment' => app()->environment(),
    ]);
});

// Public Authentication
Route::post('/auth/login', [AuthController::class, 'login']);

// Public Direct Storage & Evidence Image Streamer (Bypasses all symlink and Apache rewrite issues)
Route::get('/storage-stream/{path}', function ($path) {
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
})->where('path', '.*');

// Public Direct Photo Streamer by ID
Route::get('/evidence/photos/{id}/view', [EvidenceController::class, 'streamPhoto']);
Route::get('/evidence/photos/{id}/file', [EvidenceController::class, 'streamPhoto']);

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Profile
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::put('/auth/profile', [AuthController::class, 'updateProfile']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Permissions & RBAC Matrix
    Route::get('/permissions/my-permissions', [PermissionController::class, 'myPermissions']);
    Route::get('/permissions/matrix', [PermissionController::class, 'matrix']);
    Route::post('/permissions/matrix', [PermissionController::class, 'updateMatrix']);

    // Reports & Dashboard KPIs
    Route::get('/reports/dashboard-kpis', [ReportController::class, 'dashboardKpis']);
    Route::get('/reports/audit-logs', [MasterDataController::class, 'auditLogs']);

    // System Settings & Audit Logs (Superuser Console)
    Route::get('/system/settings', [MasterDataController::class, 'settings']);
    Route::put('/system/settings', [MasterDataController::class, 'updateSetting']);
    Route::get('/system/audit-logs', [MasterDataController::class, 'auditLogs']);
    Route::get('/system/notifications', [NotificationController::class, 'index']);

    // In-App Notification Feed
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::get('/notifications-feed', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications-feed/mark-read/{id}', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications-feed/mark-all-read', [NotificationController::class, 'markAllAsRead']);

    // Work Orders
    Route::get('/work-orders', [WorkOrderController::class, 'index']);
    Route::get('/work-orders/{id}', [WorkOrderController::class, 'show']);
    Route::post('/work-orders', [WorkOrderController::class, 'store']);
    Route::post('/work-orders/update-location', [WorkOrderController::class, 'updateLocation']);
    Route::post('/work-orders/toggle-checkin', [WorkOrderController::class, 'toggleCheckin']);
    Route::match(['put', 'post'], '/work-orders/{id}', [WorkOrderController::class, 'update']);
    Route::post('/work-orders/{id}/update', [WorkOrderController::class, 'update']);
    Route::post('/work-orders/{id}/assign', [WorkOrderController::class, 'assignTeam']);
    Route::post('/work-orders/{id}/submit', [WorkOrderController::class, 'submit']);

    // Check-In (Geofencing)
    Route::post('/check-ins', [CheckInController::class, 'store']);
    Route::post('/work-orders/{id}/check-in', [CheckInController::class, 'checkIn']);

    // Evidence & Issues
    Route::post('/evidence/upload', [EvidenceController::class, 'upload']);
    Route::get('/evidence/photos', [EvidenceController::class, 'gallery']);
    Route::delete('/evidence/photos/{id}', [EvidenceController::class, 'deletePhoto']);
    Route::get('/evidence/issues', [EvidenceController::class, 'issuesList']);
    Route::post('/evidence/issues', [EvidenceController::class, 'reportIssue']);
    Route::post('/evidence/issues/{id}/resolve', [EvidenceController::class, 'resolveIssue']);

    // Reviews & Revisions
    Route::post('/reviews/approve', [ReviewController::class, 'approve']);
    Route::post('/reviews/request-revision', [ReviewController::class, 'requestRevision']);
    Route::post('/reviews/{id}/approve', [ReviewController::class, 'approve']);
    Route::post('/reviews/{id}/revision', [ReviewController::class, 'requestRevision']);

    // Berita Acara (BA) Opname
    Route::post('/ba/generate', [BaDocumentController::class, 'generate']);
    Route::post('/ba/generate/{workOrderId}', [BaDocumentController::class, 'generate']);
    Route::get('/ba', [BaDocumentController::class, 'index']);
    Route::get('/ba/{identifier}', [BaDocumentController::class, 'show']);
    Route::get('/ba/{identifier}/pdf', [BaDocumentController::class, 'downloadPdf']);

    // Master Data CRUD
    Route::get('/master/vendors', [MasterDataController::class, 'vendors']);
    Route::post('/master/vendors', [MasterDataController::class, 'storeVendor']);
    Route::put('/master/vendors/{id}', [MasterDataController::class, 'updateVendor']);
    Route::delete('/master/vendors/{id}', [MasterDataController::class, 'deleteVendor']);

    Route::get('/master/areas', [MasterDataController::class, 'areas']);
    Route::post('/master/areas', [MasterDataController::class, 'storeArea']);
    Route::put('/master/areas/{id}', [MasterDataController::class, 'updateArea']);
    Route::delete('/master/areas/{id}', [MasterDataController::class, 'deleteArea']);

    Route::get('/master/job-types', [MasterDataController::class, 'jobTypes']);
    Route::post('/master/job-types', [MasterDataController::class, 'storeJobType']);
    Route::put('/master/job-types/{id}', [MasterDataController::class, 'updateJobType']);
    Route::delete('/master/job-types/{id}', [MasterDataController::class, 'deleteJobType']);

    Route::get('/master/field-teams', [MasterDataController::class, 'fieldTeams']);
    Route::post('/master/field-teams', [MasterDataController::class, 'storeFieldTeam']);
    Route::put('/master/field-teams/{id}', [MasterDataController::class, 'updateFieldTeam']);
    Route::delete('/master/field-teams/{id}', [MasterDataController::class, 'deleteFieldTeam']);

    Route::get('/master/users', [AuthController::class, 'users']);
    Route::post('/master/users', [AuthController::class, 'storeUser']);
    Route::put('/master/users/{id}', [AuthController::class, 'updateUser']);
    Route::delete('/master/users/{id}', [AuthController::class, 'deleteUser']);

    Route::get('/master/templates', [BaDocumentController::class, 'templates']);
    Route::get('/master/templates/{id}', [BaDocumentController::class, 'show']);
    Route::post('/master/templates', [BaDocumentController::class, 'storeTemplate']);
    Route::put('/master/templates/{id}', [BaDocumentController::class, 'updateTemplate']);
    Route::post('/master/templates/{id}/set-default', [BaDocumentController::class, 'setDefaultTemplate']);
    Route::delete('/master/templates/{id}', [BaDocumentController::class, 'deleteTemplate']);

    Route::get('/master/audit-logs', [MasterDataController::class, 'auditLogs']);
    Route::get('/master/settings', [MasterDataController::class, 'settings']);
    Route::get('/users', [AuthController::class, 'users']);
    Route::get('/roles', [AuthController::class, 'roles']);
});
