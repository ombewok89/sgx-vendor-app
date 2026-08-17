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

// Authenticated Routes
Route::middleware('auth:sanctum')->group(function () {
    // Auth & Users
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/users', [AuthController::class, 'users']);
    Route::get('/roles', [AuthController::class, 'roles']);

    // Work Orders
    Route::get('/work-orders', [WorkOrderController::class, 'index']);
    Route::get('/work-orders/{id}', [WorkOrderController::class, 'show']);
    Route::post('/work-orders', [WorkOrderController::class, 'store']);
    Route::post('/work-orders/{id}/assign', [WorkOrderController::class, 'assignTeam']);

    // Check-In (Geofencing)
    Route::post('/work-orders/{id}/check-in', [CheckInController::class, 'checkIn']);

    // Evidence Photos & Issues
    Route::post('/evidence/upload', [EvidenceController::class, 'upload']);
    Route::get('/evidence/photos', [EvidenceController::class, 'gallery']);
    Route::delete('/evidence/photos/{id}', [EvidenceController::class, 'deletePhoto']);
    Route::post('/evidence/issues', [EvidenceController::class, 'reportIssue']);
    Route::post('/evidence/issues/{id}/resolve', [EvidenceController::class, 'resolveIssue']);

    // Reviews & Revisions
    Route::post('/reviews/{id}/approve', [ReviewController::class, 'approve']);
    Route::post('/reviews/{id}/revision', [ReviewController::class, 'requestRevision']);

    // Berita Acara (BA) Opname
    Route::post('/ba/generate/{workOrderId}', [BaDocumentController::class, 'generate']);
    Route::get('/ba/{workOrderId}', [BaDocumentController::class, 'show']);
    Route::get('/ba/templates', [BaDocumentController::class, 'templates']);
    Route::get('/ba/{workOrderId}/pdf', [BaDocumentController::class, 'downloadPdf']);

    // Master Data
    Route::get('/master/vendors', [MasterDataController::class, 'vendors']);
    Route::post('/master/vendors', [MasterDataController::class, 'storeVendor']);
    Route::get('/master/areas', [MasterDataController::class, 'areas']);
    Route::get('/master/job-types', [MasterDataController::class, 'jobTypes']);
    Route::get('/master/field-teams', [MasterDataController::class, 'fieldTeams']);
    Route::get('/master/audit-logs', [MasterDataController::class, 'auditLogs']);
    Route::get('/master/settings', [MasterDataController::class, 'settings']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
