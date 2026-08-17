<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BaDocument;
use App\Models\DocumentTemplate;
use App\Models\WorkOrder;
use App\Services\BaDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BaDocumentController extends Controller
{
    public function generate(Request $request, $workOrderId)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menerbitkan Berita Acara.',
            ], 403);
        }

        $workOrder = WorkOrder::findOrFail($workOrderId);
        $templateId = $request->template_id;

        try {
            $ba = BaDocumentService::createOrUpdateBa($user, $workOrder, $templateId);

            return response()->json([
                'success' => true,
                'message' => 'Berita Acara (BA) Opname berhasil diterbitkan.',
                'data' => $ba,
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menerbitkan Berita Acara: ' . $e->getMessage(),
            ], 400);
        }
    }

    public function show($workOrderId)
    {
        $ba = BaDocument::with(['workOrder.vendor', 'template', 'generator'])
            ->where('work_order_id', $workOrderId)
            ->first();

        if (!$ba) {
            return response()->json([
                'success' => false,
                'message' => 'Berita Acara (BA) belum diterbitkan untuk pekerjaan ini.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $ba,
        ]);
    }

    public function templates()
    {
        $templates = DocumentTemplate::all();
        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    public function downloadPdf($workOrderId)
    {
        $ba = BaDocument::with(['workOrder.vendor', 'template', 'generator'])
            ->where('work_order_id', $workOrderId)
            ->firstOrFail();

        $data = [
            'ba' => $ba,
            'content' => $ba->content_json,
        ];

        $html = view('pdf.ba_opname', $data)->render();
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        return $pdf->download("{$ba->ba_number}.pdf");
    }
}
