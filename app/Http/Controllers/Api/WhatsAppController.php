<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\NotificationLog;
use App\Services\FonnteService;
use Illuminate\Http\Request;

class WhatsAppController extends Controller
{
    /**
     * Sends a test WhatsApp message.
     */
    public function sendTestMessage(Request $request)
    {
        $request->validate([
            'phone' => 'required|string',
            'message' => 'nullable|string',
        ]);

        $msg = $request->message ?: "🔔 *SGX Work Evidence System Test*\n\nUji coba konektivitas WhatsApp Gateway Fonnte berhasil terhubung secara normal pada " . now()->format('d/m/Y H:i:s') . " WIB.";

        $result = FonnteService::sendMessage($request->phone, $msg, 'TEST_MESSAGE');

        if (!$result['success']) {
            return response()->json([
                'success' => false,
                'message' => $result['message'] ?? $result['error'] ?? 'Gagal mengirim pesan WhatsApp.',
                'data' => $result,
            ], 400);
        }

        return response()->json([
            'success' => true,
            'message' => $result['message'] ?? 'Pesan WhatsApp berhasil dikirim ke antrean Fonnte.',
            'data' => $result,
        ]);
    }

    /**
     * Tests gateway connectivity.
     */
    public function testConnection(Request $request)
    {
        return $this->sendTestMessage($request);
    }

    /**
     * Returns gateway stats and health.
     */
    public function stats()
    {
        return response()->json([
            'success' => true,
            'data' => FonnteService::getGatewayStatus(),
        ]);
    }

    /**
     * Returns WhatsApp gateway logs.
     */
    public function logs(Request $request)
    {
        $limit = $request->get('limit', 50);
        $logs = NotificationLog::orderBy('id', 'desc')->limit($limit)->get();

        return response()->json([
            'success' => true,
            'data' => $logs,
        ]);
    }
}
