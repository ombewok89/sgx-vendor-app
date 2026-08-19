<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BaDocument;
use App\Models\DocumentTemplate;
use App\Models\WorkOrder;
use App\Services\AuditService;
use App\Services\BaDocumentService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class BaDocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = BaDocument::with(['workOrder.vendor', 'template', 'generator']);

        if ($user->hasAnyRole(['VENDOR', 'CLIENT']) && $user->vendor_id) {
            $query->whereHas('workOrder', fn($q) => $q->where('vendor_id', $user->vendor_id));
        }

        $bas = $query->orderByDesc('id')->get();

        return response()->json([
            'success' => true,
            'data' => $bas,
        ]);
    }

    public function generate(Request $request, $workOrderId = null)
    {
        $id = $workOrderId ?: $request->work_order_id;
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menerbitkan Berita Acara.',
            ], 403);
        }

        $workOrder = WorkOrder::findOrFail($id);
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

    public function show(Request $request, $identifier)
    {
        $user = $request->user();
        $ba = BaDocument::with(['workOrder.vendor', 'template', 'generator'])
            ->where(function ($q) use ($identifier) {
                $q->where('work_order_id', $identifier)->orWhere('id', $identifier);
            })
            ->first();

        if (!$ba) {
            return response()->json([
                'success' => false,
                'message' => 'Berita Acara (BA) belum diterbitkan untuk pekerjaan ini.',
            ], 404);
        }

        // Multi-Tenant Isolation (H-01): Verify tenant scope for VENDOR and CLIENT roles
        if ($user && $user->hasAnyRole(['VENDOR', 'CLIENT'])) {
            if (!$user->vendor_id || $ba->workOrder?->vendor_id !== $user->vendor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak: Anda tidak memiliki wewenang untuk melihat Berita Acara ini.',
                ], 403);
            }
        }

        return response()->json([
            'success' => true,
            'data' => $ba,
        ]);
    }

    public function downloadPdf(Request $request, $identifier)
    {
        $user = $request->user();
        $ba = BaDocument::with(['workOrder.vendor', 'template', 'generator'])
            ->where(function ($q) use ($identifier) {
                $q->where('work_order_id', $identifier)->orWhere('id', $identifier);
            })
            ->firstOrFail();

        // Multi-Tenant Isolation (H-01): Verify tenant scope for VENDOR and CLIENT roles
        if ($user && $user->hasAnyRole(['VENDOR', 'CLIENT'])) {
            if (!$user->vendor_id || $ba->workOrder?->vendor_id !== $user->vendor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak: Anda tidak berwenang mengunduh Berita Acara ini.',
                ], 403);
            }
        }

        $data = [
            'ba' => $ba,
            'content' => $ba->content_json,
        ];

        $html = view('pdf.ba_opname', $data)->render();
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        return $pdf->download("{$ba->ba_number}.pdf");
    }

    // ==========================================
    // DOCUMENT TEMPLATES CRUD (Admin only)
    // ==========================================
    public function templates()
    {
        $templates = DocumentTemplate::orderByDesc('is_default')->orderBy('name')->get();
        return response()->json([
            'success' => true,
            'data' => $templates,
        ]);
    }

    public function storeTemplate(Request $request)
    {
        if (!$request->user()->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang membuat template dokumen.',
            ], 403);
        }

        $request->validate([
            'name' => 'required|string',
            'code' => 'required|unique:document_templates,code',
        ]);

        $data = $request->all();
        if ($request->boolean('is_default')) {
            DocumentTemplate::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $template = DocumentTemplate::create($data);
        AuditService::log($request->user(), 'CREATE_TEMPLATE', 'DOCUMENT_TEMPLATE', $template->id, null, $template->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Template dokumen berhasil dibuat.',
            'data' => $template,
        ], 201);
    }

    public function updateTemplate(Request $request, $id)
    {
        if (!$request->user()->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menyunting template dokumen.',
            ], 403);
        }

        $template = DocumentTemplate::findOrFail($id);
        $old = $template->toArray();

        $data = $request->all();
        if ($request->boolean('is_default')) {
            DocumentTemplate::where('is_default', true)->update(['is_default' => false]);
            $data['is_default'] = true;
        }

        $template->update($data);
        AuditService::log($request->user(), 'UPDATE_TEMPLATE', 'DOCUMENT_TEMPLATE', $template->id, $old, $template->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Template dokumen berhasil diperbarui.',
            'data' => $template,
        ]);
    }

    public function setDefaultTemplate(Request $request, $id)
    {
        if (!$request->user()->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menetapkan template default.',
            ], 403);
        }

        $template = DocumentTemplate::findOrFail($id);
        DocumentTemplate::where('is_default', true)->update(['is_default' => false]);
        $template->update(['is_default' => true]);

        AuditService::log($request->user(), 'SET_DEFAULT_TEMPLATE', 'DOCUMENT_TEMPLATE', $template->id);

        return response()->json([
            'success' => true,
            'message' => "Template '{$template->name}' ditetapkan sebagai template default.",
            'data' => $template,
        ]);
    }

    public function complete(Request $request, $id)
    {
        $user = $request->user();
        if (!$user->hasAnyRole(['SUPERUSER', 'ADMIN', 'CLIENT'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Anda tidak memiliki wewenang untuk menyelesaikan pekerjaan ini.',
            ], 403);
        }

        $workOrder = WorkOrder::findOrFail($id);

        if ($user->hasRole('CLIENT') && $user->vendor_id && $workOrder->vendor_id !== $user->vendor_id) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: SPK ini bukan milik akun Client Anda.',
            ], 403);
        }

        $old = $workOrder->toArray();
        $workOrder->update([
            'status' => 'COMPLETED',
            'progress_percent' => 100
        ]);
        AuditService::log($user, 'COMPLETE_WORK_ORDER', 'WORK_ORDER', $workOrder->id, $old, $workOrder->toArray());

        return response()->json([
            'success' => true,
            'message' => 'Pekerjaan berhasil diselesaikan dan status diperbarui menjadi COMPLETED (100%).',
            'data' => $workOrder,
        ]);
    }

    public function deleteTemplate(Request $request, $id)
    {
        if (!$request->user()->hasAnyRole(['SUPERUSER', 'ADMIN'])) {
            return response()->json([
                'success' => false,
                'message' => 'Akses Ditolak: Hanya Administrator yang berwenang menghapus template dokumen.',
            ], 403);
        }

        $template = DocumentTemplate::findOrFail($id);
        AuditService::log($request->user(), 'DELETE_TEMPLATE', 'DOCUMENT_TEMPLATE', $template->id, $template->toArray());
        $template->delete();

        return response()->json([
            'success' => true,
            'message' => 'Template dokumen berhasil dihapus.',
        ]);
    }
}
