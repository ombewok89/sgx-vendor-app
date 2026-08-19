<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\FonnteService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Test gateway connection / token status with Fonnte.
     * Admin/Superuser only.
     */
    public function testConnection(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang dapat menguji WhatsApp Gateway.',
            ], 403);
        }

        $result = FonnteService::testConnection();

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => [
                'http_status' => $result['http_status'],
                'device'      => $result['device'],
                'enabled'     => FonnteService::isEnabled(),
            ],
        ]);
    }

    /**
     * Send a single manual test message.
     * Admin/Superuser only.
     */
    public function sendTestMessage(Request $request)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang dapat mengirim pesan uji coba.',
            ], 403);
        }

        $request->validate([
            'phone'   => 'required|string',
            'message' => 'required|string|max:1000',
        ]);

        $result = FonnteService::sendMessage($request->phone, $request->message);

        return response()->json([
            'success' => $result['success'],
            'message' => $result['message'],
            'data'    => [
                'target_masked' => FonnteService::maskPhone($request->phone),
                'http_status'   => $result['status'],
            ],
        ], $result['success'] ? 200 : 400);
    }
}
