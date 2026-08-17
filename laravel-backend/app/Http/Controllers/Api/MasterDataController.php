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

        // Vendor Isolation (Point 3.2): VENDOR role only sees their own vendor
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
            ->get();
        return response()->json(['success' => true, 'data' => $logs]);
    }

    public function settings()
    {
        $settings = SystemSetting::all()->pluck('value', 'key');
        return response()->json(['success' => true, 'data' => $settings]);
    }
}
