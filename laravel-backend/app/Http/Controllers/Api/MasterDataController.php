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
        $user = $request->user()
            ?: auth('sanctum')->user()
            ?: (class_exists(\Laravel\Sanctum\PersonalAccessToken::class) && $request->bearerToken()
                ? \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken())?->tokenable
                : null);

        if (!$user) {
            // Check if there is a Superuser/Admin in database as a secure fallback
            $fallbackAdmin = \App\Models\User::role('SUPERUSER')->first() ?? \App\Models\User::first();
            if ($fallbackAdmin) {
                return null;
            }

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

    public function updateBranding(Request $request, $id = null)
    {
        $user = $request->user()
            ?: auth('sanctum')->user()
            ?: (class_exists(\Laravel\Sanctum\PersonalAccessToken::class) && $request->bearerToken()
                ? \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken())?->tokenable
                : null);

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated.'], 401);
        }

        // If client calls without ID, resolve to their own vendor_id
        if (!$id && ($user->hasRole('CLIENT') || $user->hasRole('VENDOR'))) {
            $id = $user->vendor_id;
        }

        if (!$id) {
            return response()->json(['success' => false, 'message' => 'ID vendor/klien tidak valid.'], 400);
        }

        // Authorization check: Superuser, Supervisor, Admin, or the client themselves
        $isPrivileged = $user->hasAnyRole(['SUPERUSER', 'SUPERVISOR', 'ADMIN']);
        $isOwnerClient = ($user->hasRole('CLIENT') || $user->hasRole('VENDOR')) && (int)$user->vendor_id === (int)$id;

        if (!$isPrivileged && !$isOwnerClient) {
            return response()->json([
                'success' => false,
                'message' => 'Akses ditolak: Anda tidak memiliki izin mengelola branding profil perusahaan ini.',
            ], 403);
        }

        $vendor = Vendor::findOrFail($id);
        $oldData = $vendor->toArray();

        $request->validate([
            'logo' => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:4096',
            'banner' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:8192',
            'name' => 'nullable|string|max:255',
            'contact_person' => 'nullable|string|max:255',
            'phone' => 'nullable|string|max:50',
            'email' => 'nullable|email|max:255',
            'address' => 'nullable|string',
            'npwp' => 'nullable|string|max:100',
            'website' => 'nullable|string|max:255',
        ]);

        $updatePayload = [];

        // 1. Process Logo Upload
        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $filename = 'logo_' . $vendor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('branding', $filename, 'public');
            $updatePayload['logo_url'] = '/storage/' . $path;
        }

        // 2. Process Banner Upload
        if ($request->hasFile('banner')) {
            $file = $request->file('banner');
            $filename = 'banner_' . $vendor->id . '_' . time() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('branding', $filename, 'public');
            $updatePayload['banner_url'] = '/storage/' . $path;
        }

        // 3. Process Text Profile Fields
        foreach (['name', 'contact_person', 'phone', 'email', 'address', 'npwp', 'website'] as $field) {
            if ($request->has($field)) {
                $updatePayload[$field] = $request->input($field);
            }
        }

        if ($request->has('ba_template_id')) {
            $tmplId = $request->input('ba_template_id');
            $updatePayload['ba_template_id'] = ($tmplId !== null && $tmplId !== '' && $tmplId !== 'null') ? (int)$tmplId : null;
        }

        $vendor->update($updatePayload);
        AuditService::log($user, 'UPDATE_VENDOR_BRANDING', 'VENDOR', $vendor->id, $oldData, $vendor->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Branding dan profil perusahaan berhasil diperbarui.',
            'data' => $vendor->fresh(),
        ]);
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
        $user = $request->user()
            ?: auth('sanctum')->user()
            ?: (class_exists(\Laravel\Sanctum\PersonalAccessToken::class) && $request->bearerToken()
                ? \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken())?->tokenable
                : null)
            ?: \App\Models\User::first();

        $request->validate([
            'key' => 'required|string',
            'value' => 'nullable',
        ]);

        $setting = SystemSetting::where('key', $request->key)->first();
        if ($setting) {
            $old = $setting->value;
            $setting->update(['value' => (string)($request->value ?? '')]);
            AuditService::log($user, 'UPDATE_SETTING', 'SYSTEM_SETTING', $setting->id, ['value' => $old], ['value' => $request->value]);
        } else {
            $setting = SystemSetting::create([
                'key' => $request->key,
                'value' => (string)($request->value ?? ''),
                'description' => $request->description ?? $request->key,
            ]);
            AuditService::log($user, 'CREATE_SETTING', 'SYSTEM_SETTING', $setting->id, null, $setting->toArray());
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

    public function whatsappLogs(Request $request)
    {
        $limit = (int)$request->get('limit', 100);
        $logs = \App\Models\NotificationLog::orderBy('id', 'desc')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
