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
    public function vendors(Request $request)
    {
        $user = $request->user();
        $query = Vendor::query();

        // Vendor Isolation: VENDOR role only sees their own vendor
        if ($user && $user->hasRole('VENDOR') && $user->vendor_id) {
            $query->where('id', $user->vendor_id);
        }

        $vendors = $query->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $vendors]);
    }

    public function storeVendor(Request $request)
    {
        $request->validate(['code' => 'required|unique:vendors', 'name' => 'required']);
        $vendor = Vendor::create($request->all());
        AuditService::log($request->user(), 'CREATE_VENDOR', 'VENDOR', $vendor->id, null, $vendor->toArray());
        return response()->json(['success' => true, 'data' => $vendor], 201);
    }

    public function areas()
    {
        $areas = Area::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $areas]);
    }

    public function jobTypes()
    {
        $jobTypes = JobType::where('is_active', true)->orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $jobTypes]);
    }

    public function fieldTeams()
    {
        $teams = FieldTeam::with(['leader', 'area', 'members'])->where('is_active', true)->get();
        return response()->json(['success' => true, 'data' => $teams]);
    }

    public function auditLogs(Request $request)
    {
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

    public function settings()
    {
        $settings = SystemSetting::orderBy('id')->get();
        return response()->json(['success' => true, 'data' => $settings]);
    }

    public function updateSetting(Request $request)
    {
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
                'group' => 'GENERAL',
                'description' => $request->key,
            ]);
            AuditService::log($request->user(), 'CREATE_SETTING', 'SYSTEM_SETTING', $setting->id, null, $setting->toArray());
        }

        return response()->json(['success' => true, 'data' => $setting]);
    }
}
