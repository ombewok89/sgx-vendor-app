<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::with('vendor')->where('email', strtolower($request->email))->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Email atau kata sandi tidak sesuai.',
            ], 401);
        }

        if (!$user->is_active) {
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda telah dinonaktifkan oleh administrator.',
            ], 403);
        }

        // Revoke previous tokens
        $user->tokens()->delete();

        // Create new token
        $token = $user->createToken('sgx_auth_token')->plainTextToken;

        $role = $user->roles->first()?->name ?? 'FIELD_TEAM';

        AuditService::log($user, 'LOGIN', 'USER', $user->id, null, ['ip' => $request->ip()]);

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $role,
            'vendor_id' => $user->vendor_id,
            'vendor_name' => $user->vendor?->name,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];

        return response()->json([
            'success' => true,
            'token' => $token,
            'user' => $userData,
            'data' => $userData,
        ]);
    }

    public function me(Request $request)
    {
        $user = $request->user()->load('vendor');
        $role = $user->roles->first()?->name ?? 'FIELD_TEAM';

        $userData = [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
            'role' => $role,
            'vendor_id' => $user->vendor_id,
            'vendor_name' => $user->vendor?->name,
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ];

        return response()->json([
            'success' => true,
            'user' => $userData,
            'data' => $userData,
        ]);
    }

    public function logout(Request $request)
    {
        $user = $request->user();
        $user->currentAccessToken()->delete();

        AuditService::log($user, 'LOGOUT', 'USER', $user->id);

        return response()->json([
            'success' => true,
            'message' => 'Anda telah berhasil keluar dari sistem.',
        ]);
    }

    public function users(Request $request)
    {
        $users = User::with(['roles', 'vendor'])
            ->orderBy('name')
            ->get()
            ->map(function ($u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'phone' => $u->phone,
                    'role' => $u->roles->first()?->name ?? 'FIELD_TEAM',
                    'vendor_id' => $u->vendor_id,
                    'vendor_name' => $u->vendor?->name,
                    'is_active' => $u->is_active,
                    'created_at' => $u->created_at,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $users,
        ]);
    }

    public function roles()
    {
        $roles = Role::all()->pluck('name');
        return response()->json([
            'success' => true,
            'data' => $roles,
        ]);
    }
}
