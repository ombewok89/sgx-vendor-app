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
    // Auth & Profile
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Reports & Dashboard KPIs
    Route::get('/reports/dashboard-kpis', [ReportController::class, 'dashboardKpis']);
    Route::get('/reports/audit-logs', [MasterDataController::class, 'auditLogs']);

    // System Settings & Audit Logs (Superuser Console)
    Route::get('/system/settings', [MasterDataController::class, 'settings']);
    Route::put('/system/settings', [MasterDataController::class, 'updateSetting']);
    Route::get('/system/audit-logs', [MasterDataController::class, 'auditLogs']);

    // Work Orders
    Route::get('/work-orders', [WorkOrderController::class, 'index']);
    Route::get('/work-orders/{id}', [WorkOrderController::class, 'show']);
    Route::post('/work-orders', [WorkOrderController::class, 'store']);
    Route::post('/work-orders/{id}/assign', [WorkOrderController::class, 'assignTeam']);

    // Check-In
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

    // Master Data
    Route::get('/master/vendors', [MasterDataController::class, 'vendors']);
    Route::post('/master/vendors', [MasterDataController::class, 'storeVendor']);
    Route::get('/master/areas', [MasterDataController::class, 'areas']);
    Route::get('/master/job-types', [MasterDataController::class, 'jobTypes']);
    Route::get('/master/field-teams', [MasterDataController::class, 'fieldTeams']);
    Route::get('/master/users', [AuthController::class, 'users']);
    Route::get('/master/templates', [BaDocumentController::class, 'templates']);
    Route::get('/master/audit-logs', [MasterDataController::class, 'auditLogs']);
    Route::get('/master/settings', [MasterDataController::class, 'settings']);
    Route::get('/users', [AuthController::class, 'users']);
    Route::get('/roles', [AuthController::class, 'roles']);

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
});
