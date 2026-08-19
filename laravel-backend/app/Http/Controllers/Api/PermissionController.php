<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use App\Models\SystemSetting;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    private static $defaultModules = [
        ['id' => 'admin_dashboard', 'name' => 'Dashboard Operasional', 'section' => 'UTAMA', 'icon' => 'LayoutDashboard', 'sort_order' => 1],
        ['id' => 'admin_spk', 'name' => 'Pekerjaan / SPK', 'section' => 'WORK MANAGEMENT', 'icon' => 'FileText', 'sort_order' => 2],
        ['id' => 'admin_review', 'name' => 'Review & Revisi', 'section' => 'WORK MANAGEMENT', 'icon' => 'CheckSquare', 'sort_order' => 3],
        ['id' => 'admin_evidence', 'name' => 'Evidence Gallery', 'section' => 'DOCUMENTATION', 'icon' => 'Camera', 'sort_order' => 4],
        ['id' => 'admin_issues', 'name' => 'Kendala Teknis', 'section' => 'DOCUMENTATION', 'icon' => 'AlertTriangle', 'sort_order' => 5],
        ['id' => 'admin_ba', 'name' => 'BA Opname', 'section' => 'REPORTING', 'icon' => 'FileCheck2', 'sort_order' => 6],
        ['id' => 'admin_reports', 'name' => 'Laporan & Statistik', 'section' => 'REPORTING', 'icon' => 'BarChart3', 'sort_order' => 7],
        ['id' => 'admin_vendors', 'name' => 'Master Vendor', 'section' => 'MASTER DATA', 'icon' => 'Building2', 'sort_order' => 8],
        ['id' => 'admin_teams', 'name' => 'Tim Lapangan', 'section' => 'MASTER DATA', 'icon' => 'Users', 'sort_order' => 9],
        ['id' => 'admin_areas', 'name' => 'Master Area', 'section' => 'MASTER DATA', 'icon' => 'MapPin', 'sort_order' => 10],
        ['id' => 'admin_jobtypes', 'name' => 'Jenis Pekerjaan', 'section' => 'MASTER DATA', 'icon' => 'Briefcase', 'sort_order' => 11],
        ['id' => 'admin_templates', 'name' => 'Template Dokumen', 'section' => 'SYSTEM', 'icon' => 'FileCode', 'sort_order' => 12],
        ['id' => 'admin_notifications', 'name' => 'WhatsApp Logs', 'section' => 'SYSTEM', 'icon' => 'Bell', 'sort_order' => 13],
        ['id' => 'admin_audit', 'name' => 'Audit Trail', 'section' => 'SYSTEM', 'icon' => 'History', 'sort_order' => 14],
        ['id' => 'field_dashboard', 'name' => 'Dashboard Lapangan', 'section' => 'FIELD TEAM', 'icon' => 'Smartphone', 'sort_order' => 15],
        ['id' => 'field_tasks', 'name' => 'Pekerjaan Saya (Mobile)', 'section' => 'FIELD TEAM', 'icon' => 'CheckSquare', 'sort_order' => 16],
        ['id' => 'field_history', 'name' => 'Riwayat Tugas', 'section' => 'FIELD TEAM', 'icon' => 'History', 'sort_order' => 17],
        ['id' => 'client_dashboard', 'name' => 'Dashboard Monitoring Klien', 'section' => 'CLIENT', 'icon' => 'LayoutDashboard', 'sort_order' => 18],
        ['id' => 'client_tasks', 'name' => 'Progres & Evidensi Toko', 'section' => 'CLIENT', 'icon' => 'Store', 'sort_order' => 19],
        ['id' => 'client_ba', 'name' => 'Dokumen BA Opname Klien', 'section' => 'CLIENT', 'icon' => 'FileCheck2', 'sort_order' => 20],
    ];

    public static function getDefaultModules(): array
    {
        return self::$defaultModules;
    }

    public static function getDefaultMatrixForRole(string $roleCode): array
    {
        $matrix = [];
        foreach (self::$defaultModules as $mod) {
            $isField = ($mod['section'] === 'FIELD TEAM');
            $isClient = ($mod['section'] === 'CLIENT');
            $canView = 0;
            $canCreate = 0;
            $canUpdate = 0;
            $canDelete = 0;

            if ($roleCode === 'ADMIN' && !$isField && !$isClient) {
                $canView = 1; $canCreate = 1; $canUpdate = 1; $canDelete = 1;
            } elseif ($roleCode === 'FIELD_TEAM' && $isField) {
                $canView = 1; $canCreate = 1; $canUpdate = 1; $canDelete = 0;
            } elseif (($roleCode === 'VENDOR' || $roleCode === 'CLIENT') && $isClient) {
                $canView = 1; $canCreate = 0; $canUpdate = 0; $canDelete = 0;
            }

            $matrix[] = array_merge($mod, [
                'module_id' => $mod['id'],
                'can_view' => $canView,
                'can_create' => $canCreate,
                'can_update' => $canUpdate,
                'can_delete' => $canDelete,
            ]);
        }
        return $matrix;
    }

    public function myPermissions(Request $request)
    {
        $user = $request->user();
        $role = $user->roles->first()?->name ?? 'FIELD_TEAM';

        if ($role === 'SUPERUSER') {
            $permMap = [];
            foreach (self::$defaultModules as $m) {
                $permMap[$m['id']] = ['can_view' => 1, 'can_create' => 1, 'can_update' => 1, 'can_delete' => 1];
            }
            return response()->json(['success' => true, 'data' => $permMap]);
        }

        $savedMatrix = SystemSetting::where('key', "rbac_matrix_{$role}")->first();
        if ($savedMatrix && $savedMatrix->value) {
            $decoded = json_decode($savedMatrix->value, true);
            $permMap = [];
            foreach ($decoded as $item) {
                $moduleId = $item['module_id'] ?? $item['id'] ?? '';
                if ($moduleId) {
                    $permMap[$moduleId] = [
                        'can_view' => !empty($item['can_view']) ? 1 : 0,
                        'can_create' => !empty($item['can_create']) ? 1 : 0,
                        'can_update' => !empty($item['can_update']) ? 1 : 0,
                        'can_delete' => !empty($item['can_delete']) ? 1 : 0,
                    ];
                }
            }
            return response()->json(['success' => true, 'data' => $permMap]);
        }

        // Default fallback permissions based on role
        $permMap = [];
        foreach (self::$defaultModules as $m) {
            $isField = ($m['section'] === 'FIELD TEAM');
            $isClient = ($m['section'] === 'CLIENT');
            if ($role === 'ADMIN') {
                $canView = (!$isField && !$isClient) ? 1 : 0;
                $permMap[$m['id']] = ['can_view' => $canView, 'can_create' => $canView, 'can_update' => $canView, 'can_delete' => $canView];
            } elseif ($role === 'FIELD_TEAM') {
                $canView = $isField ? 1 : 0;
                $permMap[$m['id']] = ['can_view' => $canView, 'can_create' => $canView, 'can_update' => $canView, 'can_delete' => 0];
            } elseif ($role === 'VENDOR' || $role === 'CLIENT') {
                $canView = $isClient ? 1 : 0;
                $permMap[$m['id']] = ['can_view' => $canView, 'can_create' => 0, 'can_update' => 0, 'can_delete' => 0];
            }
        }

        return response()->json(['success' => true, 'data' => $permMap]);
    }

    public function matrix(Request $request)
    {
        if (!$request->user()->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator/Superuser yang berwenang mengakses matriks hak akses.',
            ], 403);
        }

        $roleCode = $request->query('role', 'ADMIN');
        $roles = [
            ['code' => 'ADMIN', 'name' => 'Admin Operasional'],
            ['code' => 'FIELD_TEAM', 'name' => 'Tim Lapangan (Mobile)'],
            ['code' => 'VENDOR', 'name' => 'Mitra Vendor'],
            ['code' => 'CLIENT', 'name' => 'Client QA & Monitoring'],
        ];

        $savedMatrix = SystemSetting::where('key', "rbac_matrix_{$roleCode}")->first();
        if ($savedMatrix && $savedMatrix->value) {
            $decoded = json_decode($savedMatrix->value, true);
            $customMap = [];
            foreach ($decoded as $item) {
                $mId = $item['module_id'] ?? $item['id'] ?? '';
                if ($mId) {
                    $customMap[$mId] = $item;
                }
            }

            $matrix = [];
            foreach (self::$defaultModules as $mod) {
                if (isset($customMap[$mod['id']])) {
                    $matrix[] = array_merge($mod, [
                        'module_id' => $mod['id'],
                        'can_view' => !empty($customMap[$mod['id']]['can_view']) ? 1 : 0,
                        'can_create' => !empty($customMap[$mod['id']]['can_create']) ? 1 : 0,
                        'can_update' => !empty($customMap[$mod['id']]['can_update']) ? 1 : 0,
                        'can_delete' => !empty($customMap[$mod['id']]['can_delete']) ? 1 : 0,
                    ]);
                } else {
                    $matrix[] = array_merge($mod, [
                        'module_id' => $mod['id'],
                        'can_view' => 0,
                        'can_create' => 0,
                        'can_update' => 0,
                        'can_delete' => 0,
                    ]);
                }
            }
        } else {
            $matrix = self::getDefaultMatrixForRole($roleCode);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'roles' => $roles,
                'role_code' => $roleCode,
                'matrix' => $matrix,
            ]
        ]);
    }

    public function updateMatrix(Request $request)
    {
        if (!$request->user()->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator/Superuser yang berwenang mengubah matriks hak akses.',
            ], 403);
        }

        $raw = $request->all();
        $matrix = $request->input('matrix') ?? $request->input('permissions') ?? $request->input('data') ?? [];

        if (empty($matrix) || !is_array($matrix)) {
            if (is_array($raw) && isset($raw[0])) {
                $matrix = $raw;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Data matriks perizinan tidak boleh kosong.',
                ], 422);
            }
        }

        $roleCode = $request->input('role_code') ?? $request->input('role') ?? 'ADMIN';

        // Normalize boolean/integers for each item
        $normalized = [];
        foreach ($matrix as $item) {
            if (!is_array($item)) continue;
            $mId = $item['module_id'] ?? $item['id'] ?? '';
            if (!$mId) continue;
            $normalized[] = [
                'id' => $mId,
                'module_id' => $mId,
                'name' => $item['name'] ?? '',
                'section' => $item['section'] ?? '',
                'icon' => $item['icon'] ?? 'LayoutDashboard',
                'sort_order' => $item['sort_order'] ?? 1,
                'can_view' => !empty($item['can_view']) ? 1 : 0,
                'can_create' => !empty($item['can_create']) ? 1 : 0,
                'can_update' => !empty($item['can_update']) ? 1 : 0,
                'can_delete' => !empty($item['can_delete']) ? 1 : 0,
            ];
        }

        $setting = SystemSetting::firstOrNew(['key' => "rbac_matrix_{$roleCode}"]);
        $setting->value = json_encode($normalized);
        $setting->description = "Dynamic RBAC permissions matrix for {$roleCode}";
        $setting->save();

        AuditService::log($request->user(), 'UPDATE_RBAC_MATRIX', 'ROLE_PERMISSIONS', null, null, [
            'role_code' => $roleCode,
            'modules_count' => count($normalized),
        ]);

        return response()->json([
            'success' => true,
            'message' => "Hak akses role '{$roleCode}' berhasil diperbarui!",
            'data' => $normalized,
        ]);
    }
}
