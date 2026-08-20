<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\AuditLog;
use App\Models\FieldTeam;
use App\Models\JobType;
use App\Models\SystemSetting;
use App\Models\Vendor;
use App\Services\AuditService;
use Illuminate\Http\Request;

class MasterDataController extends Controller
{
    private function checkAdminAuth(Request $request)
    {
        $user = $request->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated: Sesi login tidak ditemukan.',
            ], 401);
        }
        if (!$user->hasAnyRole(['SUPERUSER', 'SUPERVISOR', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator/Superuser yang berwenang mengelola data master.',
            ], 403);
        }
        return null;
    }

    // ==========================================
    // VENDORS / CLIENTS
    // ==========================================
    public function vendors(Request $request)
    {
        $user = $request->user();
        $query = Vendor::query();

        if ($user && ($user->hasRole('VENDOR') || $user->hasRole('CLIENT')) && $user->vendor_id) {
            $query->where('id', $user->vendor_id);
        }

        $vendors = $query->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $vendors]);
    }

    public function storeVendor(Request $request)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $request->validate([
            'code' => 'required|unique:vendors,code',
            'name' => 'required|string',
        ]);

        $vendor = Vendor::create($request->all());
        AuditService::log($request->user(), 'CREATE_VENDOR', 'VENDOR', $vendor->id, null, $vendor->toArray());
        return response()->json(['success' => true, 'data' => $vendor], 201);
    }

    public function updateVendor(Request $request, $id)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $vendor = Vendor::findOrFail($id);
        $old = $vendor->toArray();
        $vendor->update($request->all());
        AuditService::log($request->user(), 'UPDATE_VENDOR', 'VENDOR', $vendor->id, $old, $vendor->toArray());
        return response()->json(['success' => true, 'data' => $vendor]);
    }

    public function deleteVendor(Request $request, $id)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $vendor = Vendor::findOrFail($id);
        AuditService::log($request->user(), 'DELETE_VENDOR', 'VENDOR', $vendor->id, $vendor->toArray());
        $vendor->delete();
        return response()->json(['success' => true, 'message' => 'Data vendor berhasil dihapus.']);
    }

    // ==========================================
    // AREAS
    // ==========================================
    public function areas()
    {
        $areas = Area::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $areas]);
    }

    public function storeArea(Request $request)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $request->validate([
            'name' => 'required|string',
            'province' => 'nullable|string',
            'city' => 'nullable|string',
        ]);

        $area = Area::create($request->all());
        AuditService::log($request->user(), 'CREATE_AREA', 'AREA', $area->id, null, $area->toArray());
        return response()->json(['success' => true, 'data' => $area], 201);
    }

    public function updateArea(Request $request, $id)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $area = Area::findOrFail($id);
        $old = $area->toArray();
        $area->update($request->all());
        AuditService::log($request->user(), 'UPDATE_AREA', 'AREA', $area->id, $old, $area->toArray());
        return response()->json(['success' => true, 'data' => $area]);
    }

    public function deleteArea(Request $request, $id)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $area = Area::findOrFail($id);
        AuditService::log($request->user(), 'DELETE_AREA', 'AREA', $area->id, $area->toArray());
        $area->delete();
        return response()->json(['success' => true, 'message' => 'Data area berhasil dihapus.']);
    }

    // ==========================================
    // JOB TYPES
    // ==========================================
    public function jobTypes()
    {
        $jobTypes = JobType::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $jobTypes]);
    }

    public function storeJobType(Request $request)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $request->validate([
            'code' => 'required|unique:job_types,code',
            'name' => 'required|string',
            'doc_mode' => 'required|in:BEFORE_PROCESS_AFTER,AFTER_ONLY',
            'standard_price' => 'nullable|numeric|min:0',
            'min_photos_per_stage' => 'nullable|integer|min:1',
        ]);

        $jobType = JobType::create($request->all());
        AuditService::log($request->user(), 'CREATE_JOB_TYPE', 'JOB_TYPE', $jobType->id, null, $jobType->toArray());
        return response()->json(['success' => true, 'data' => $jobType], 201);
    }

    public function updateJobType(Request $request, $id)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $request->validate([
            'name' => 'sometimes|required|string',
            'doc_mode' => 'sometimes|required|in:BEFORE_PROCESS_AFTER,AFTER_ONLY',
            'standard_price' => 'nullable|numeric|min:0',
            'min_photos_per_stage' => 'nullable|integer|min:1',
        ]);

        $jobType = JobType::findOrFail($id);
        $old = $jobType->toArray();
        $jobType->update($request->all());
        AuditService::log($request->user(), 'UPDATE_JOB_TYPE', 'JOB_TYPE', $jobType->id, $old, $jobType->toArray());
        return response()->json(['success' => true, 'data' => $jobType]);
    }

    public function deleteJobType(Request $request, $id)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $jobType = JobType::findOrFail($id);
        AuditService::log($request->user(), 'DELETE_JOB_TYPE', 'JOB_TYPE', $jobType->id, $jobType->toArray());
        $jobType->delete();
        return response()->json(['success' => true, 'message' => 'Data jenis pekerjaan berhasil dihapus.']);
    }

    // ==========================================
    // FIELD TEAMS
    // ==========================================
    public function fieldTeams()
    {
        $teams = FieldTeam::with(['leader', 'area', 'members'])->get();
        return response()->json(['success' => true, 'data' => $teams]);
    }

    public function storeFieldTeam(Request $request)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        if (!$request->has('leader_user_id') && $request->has('leader_id')) {
            $request->merge(['leader_user_id' => $request->leader_id]);
        }

        $request->validate([
            'name' => 'required|string',
            'leader_user_id' => 'required|exists:users,id',
            'area_id' => 'required|exists:areas,id',
        ]);

        $team = FieldTeam::create($request->only(['name', 'leader_user_id', 'area_id', 'is_active']));
        if ($request->has('member_ids') && is_array($request->member_ids)) {
            $team->members()->sync($request->member_ids);
        }

        AuditService::log($request->user(), 'CREATE_FIELD_TEAM', 'FIELD_TEAM', $team->id, null, $team->toArray());
        return response()->json(['success' => true, 'data' => $team->load(['leader', 'area', 'members'])], 201);
    }

    public function updateFieldTeam(Request $request, $id)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        if (!$request->has('leader_user_id') && $request->has('leader_id')) {
            $request->merge(['leader_user_id' => $request->leader_id]);
        }

        $team = FieldTeam::findOrFail($id);
        $old = $team->toArray();
        $team->update($request->only(['name', 'leader_user_id', 'area_id', 'is_active']));
        if ($request->has('member_ids') && is_array($request->member_ids)) {
            $team->members()->sync($request->member_ids);
        }

        AuditService::log($request->user(), 'UPDATE_FIELD_TEAM', 'FIELD_TEAM', $team->id, $old, $team->toArray());
        return response()->json(['success' => true, 'data' => $team->load(['leader', 'area', 'members'])]);
    }

    public function deleteFieldTeam(Request $request, $id)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $team = FieldTeam::findOrFail($id);
        AuditService::log($request->user(), 'DELETE_FIELD_TEAM', 'FIELD_TEAM', $team->id, $team->toArray());
        $team->delete();
        return response()->json(['success' => true, 'message' => 'Data tim lapangan berhasil dihapus.']);
    }

    // ==========================================
    // AUDIT LOGS & SETTINGS
    // ==========================================
    public function auditLogs(Request $request)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $logs = AuditLog::with('user:id,name,email')
            ->orderByDesc('id')
            ->limit(100)
            ->get()
            ->map(function ($log) {
                return [
                    'id' => $log->id,
                    'created_at' => $log->created_at,
                    'user_id' => $log->user_id,
                    'user_name' => $log->user?->name ?? 'System',
                    'action' => $log->action,
                    'entity_type' => $log->entity_type,
                    'entity_id' => $log->entity_id,
                    'old_value' => $log->old_value,
                    'new_value' => $log->new_value,
                    'ip_address' => $log->ip_address ?? '127.0.0.1',
                ];
            });

        return response()->json(['success' => true, 'data' => $logs]);
    }

    public function settings(Request $request)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        // Auto-seed default settings if empty
        if (SystemSetting::where('key', 'fonnte_api_key')->doesntExist()) {
            SystemSetting::create([
                'key' => 'fonnte_api_key',
                'value' => 'GoPzcxdiUP2yt5HbByUK',
                'description' => 'API Token untuk WhatsApp Gateway Fonnte',
            ]);
        }
        if (SystemSetting::where('key', 'app_name')->doesntExist()) {
            SystemSetting::create([
                'key' => 'app_name',
                'value' => 'SGX Vendor Work Evidence',
                'description' => 'Nama resmi platform aplikasi',
            ]);
        }
        if (SystemSetting::where('key', 'geofence_default_radius_meters')->doesntExist()) {
            SystemSetting::create([
                'key' => 'geofence_default_radius_meters',
                'value' => '200',
                'description' => 'Radius toleransi geofencing default dalam meter',
            ]);
        }
        if (SystemSetting::where('key', 'require_strict_gps')->doesntExist()) {
            SystemSetting::create([
                'key' => 'require_strict_gps',
                'value' => '1',
                'description' => 'Wajibkan GPS browser terverifikasi saat check-in',
            ]);
        }
        if (SystemSetting::where('key', 'sha256_integrity_lock')->doesntExist()) {
            SystemSetting::create([
                'key' => 'sha256_integrity_lock',
                'value' => '1',
                'description' => 'Segel integritas kriptografi bukti foto',
            ]);
        }

        $settings = SystemSetting::orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function updateSetting(Request $request)
    {
        if ($deny = $this->checkAdminAuth($request)) return $deny;

        $request->validate([
            'key' => 'required|string',
            'value' => 'required',
        ]);

        $setting = SystemSetting::where('key', $request->key)->first();
        if ($setting) {
            $old = $setting->value;
            $setting->update(['value' => $request->value]);
            AuditService::log($request->user(), 'UPDATE_SETTING', 'SYSTEM_SETTING', $setting->id, ['value' => $old], ['value' => $request->value]);
        } else {
            $setting = SystemSetting::create([
                'key' => $request->key,
                'value' => $request->value,
                'description' => $request->description ?? $request->key,
            ]);
            AuditService::log($request->user(), 'CREATE_SETTING', 'SYSTEM_SETTING', $setting->id, null, $setting->toArray());
        }

        return response()->json(['success' => true, 'data' => $setting]);
    }

    public function testWhatsApp(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string',
        ]);

        $msg = $request->message ?: "🔔 *SGX Work Evidence System Test*\n\nUji coba konektivitas WhatsApp Gateway Fonnte berhasil terhubung secara normal pada " . now()->format('d/m/Y H:i:s') . " WIB.";

        $result = \App\Services\FonnteService::sendMessage($request->phone, $msg, 'TEST_MESSAGE');

        if ($user = $request->user()) {
            \App\Services\AuditService::log($user, 'TEST_WHATSAPP_GATEWAY', 'SYSTEM', null, null, [
                'target' => $request->phone,
                'result' => $result,
            ]);
        }

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? $result['error'] ?? 'Gagal mengirim pesan WhatsApp.',
                'data' => $result['data'] ?? null,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Pesan WhatsApp berhasil dikirim ke antrean Fonnte.',
            'data' => $result,
        ]);
    }

    public function gatewayStatus()
    {
        return response()->json([
            'success' => true,
            'data' => \App\Services\FonnteService::getGatewayStatus(),
        ]);
    }
}
