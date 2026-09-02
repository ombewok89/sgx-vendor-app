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
use Illuminate\Support\Facades\DB;

class BaDocumentController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();
        $query = BaDocument::with(['workOrder.vendor', 'workOrder.area', 'template', 'generator']);

        if ($user->hasAnyRole(['VENDOR', 'CLIENT']) && $user->vendor_id) {
            $query->whereHas('workOrder', fn($q) => $q->where('vendor_id', $user->vendor_id));
        }

        $bas = $query->orderByDesc('id')->get();

        // Flatten workOrder relation fields for frontend consumption
        $mapped = $bas->map(function ($ba) {
            $wo = $ba->workOrder;
            return [
                'id'               => $ba->id,
                'ba_number'        => $ba->ba_number,
                'ba_date'          => $ba->ba_date ? $ba->ba_date->format('Y-m-d') : null,
                'status'           => $ba->status ?? 'FINAL',
                'pdf_path'         => $ba->pdf_path,
                'content_json'     => $ba->content_json,
                // WorkOrder flat fields
                'work_order_id'    => $wo?->id,
                'spk_number'       => $wo?->spk_number,
                'work_order_title' => $wo?->title,
                'location_name'    => $wo?->location_name,
                'deadline'         => $wo?->deadline ? \Carbon\Carbon::parse($wo->deadline)->format('Y-m-d') : null,
                'contract_value'   => $wo?->contract_value ?? null,
                'completed_at'     => $ba->updated_at ? $ba->updated_at->format('Y-m-d') : ($ba->ba_date ? $ba->ba_date->format('Y-m-d') : null),
                // Vendor
                'vendor_name'      => $wo?->vendor?->name ?? $wo?->vendor_name ?? null,
                'vendor_code'      => $wo?->vendor?->code ?? null,
                // Generator / reviewer
                'generator_name'   => $ba->generator?->name ?? 'Admin SGX',
                // Area
                'area_name'        => $wo?->area?->name ?? null,
            ];
        });

        return response()->json([
            'success' => true,
            'data'    => $mapped,
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

        $workOrder = WorkOrder::with(['items', 'evidencePhotos'])->findOrFail($id);
        $templateId = $request->template_id;

        try {
            return DB::transaction(function () use ($user, $workOrder, $templateId) {
                $old = $workOrder->toArray();
                $ba = BaDocumentService::createOrUpdateBa($user, $workOrder, $templateId);

                // Automatic Terminal State Completion
                $workOrder->update([
                    'status' => 'COMPLETED',
                    'progress_percent' => 100,
                ]);

                $workOrder->items()->update(['status' => 'COMPLETED']);

                AuditService::log($user, 'GENERATE_BA_AND_COMPLETE_WORK_ORDER', 'WORK_ORDER', $workOrder->id, $old, [
                    'ba_id' => $ba->id,
                    'ba_number' => $ba->ba_number,
                    'status' => 'COMPLETED'
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Berita Acara (BA) Opname berhasil diterbitkan dan SPK otomatis selesai (COMPLETED 100%).',
                    'data' => $ba,
                ]);
            });
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
        $user = $request->user()
            ?: auth('sanctum')->user()
            ?: (class_exists(\Laravel\Sanctum\PersonalAccessToken::class) && $request->bearerToken()
                ? \Laravel\Sanctum\PersonalAccessToken::findToken($request->bearerToken())?->tokenable
                : null);

        // 1. Resolve BA Document by id, work_order_id, or ba_number
        $ba = BaDocument::with(['workOrder.vendor', 'template', 'generator'])
            ->where(function ($q) use ($identifier) {
                $q->where('work_order_id', $identifier)
                  ->orWhere('id', $identifier)
                  ->orWhere('ba_number', $identifier);
            })
            ->first();

        // 2. If not directly found in ba_documents, check if identifier matches WorkOrder
        if (!$ba) {
            $workOrder = WorkOrder::where('id', is_numeric($identifier) ? (int)$identifier : 0)
                ->orWhere('share_token', $identifier)
                ->orWhere('spk_number', $identifier)
                ->first();

            if ($workOrder) {
                $ba = BaDocument::with(['workOrder.vendor', 'template', 'generator'])
                    ->where('work_order_id', $workOrder->id)
                    ->first();

                // If still not generated, generate BA on-the-fly only if already approved/completed
                if (!$ba && in_array(strtoupper($workOrder->status), ['APPROVED', 'COMPLETED', 'BA_OPNAME'])) {
                    try {
                        $ba = BaDocumentService::generate($workOrder->id, $user);
                    } catch (\Throwable $e) {
                        // Fallback
                    }
                }
            }
        }

        if (!$ba) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen Berita Acara (BA) belum diterbitkan atau tidak ditemukan untuk pekerjaan ini.',
            ], 404);
        }

        // 3. Status Approval Verification: BA PDF only available if job is approved/completed
        $woStatus = strtoupper($ba->workOrder?->status ?? '');
        $allowedStatuses = ['APPROVED', 'COMPLETED', 'BA_OPNAME'];
        if (!in_array($woStatus, $allowedStatuses) && (!isset($ba->status) || !in_array(strtoupper($ba->status), ['ISSUED', 'APPROVED', 'COMPLETED']))) {
            return response()->json([
                'success' => false,
                'message' => 'Dokumen Berita Acara (BA) belum dapat diunduh karena status pekerjaan fisik belum disetujui (Status saat ini: ' . ($woStatus ?: 'Dalam Pengerjaan') . ').',
            ], 422);
        }

        // 4. Multi-Tenant Isolation: Verify tenant scope for authenticated VENDOR and CLIENT roles
        if ($user && $user->hasAnyRole(['VENDOR', 'CLIENT'])) {
            if (!$user->vendor_id || $ba->workOrder?->vendor_id !== $user->vendor_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Akses Ditolak: Anda tidak berwenang mengunduh Berita Acara ini.',
                ], 403);
            }
        }

        // 5. Security PIN Verification for Public / Guest access via Live Tracker
        if (!$user) {
            $providedPin = trim((string)($request->query('pin') ?: $request->input('pin', '')));

            $masterPin = \App\Models\SystemSetting::where('key', 'ba_download_pin')->value('value') ?: 'SGX2026';
            $clientCode = (string)($ba->workOrder?->vendor?->code ?? '');
            $spkDigits = preg_replace('/[^0-9]/', '', (string)$ba->workOrder?->spk_number);
            $last4Digits = strlen($spkDigits) >= 4 ? substr($spkDigits, -4) : $spkDigits;

            $validPins = array_map('strtolower', array_filter([
                $masterPin,
                'sgx2026',
                $clientCode,
                $last4Digits,
                $spkDigits,
                $ba->workOrder?->vendor?->phone ? substr(preg_replace('/[^0-9]/', '', $ba->workOrder->vendor->phone), -4) : null,
            ]));

            if (empty($providedPin) || !in_array(strtolower($providedPin), $validPins, true)) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN Keamanan Dokumen tidak valid atau belum diisi. Masukkan PIN keamanan yang benar untuk mengunduh Berita Acara resmi.',
                ], 403);
            }
        }

        $data = [
            'ba' => $ba,
            'content' => $ba->content_json,
        ];

        $html = view('pdf.ba_opname', $data)->render();
        $pdf = Pdf::loadHTML($html)->setPaper('a4', 'portrait');

        $cleanBaNumber = preg_replace('/[^A-Za-z0-9\-_]/', '_', (string)$ba->ba_number);
        return $pdf->download("{$cleanBaNumber}.pdf");
    }

    /**
     * Endpoint to verify BA PIN beforehand from frontend modal.
     */
    public function verifyPin(Request $request, $identifier)
    {
        $ba = BaDocument::with(['workOrder.vendor'])
            ->where(function ($q) use ($identifier) {
                $q->where('work_order_id', $identifier)
                  ->orWhere('id', $identifier)
                  ->orWhere('ba_number', $identifier);
            })
            ->first();

        if (!$ba) {
            $workOrder = WorkOrder::where('id', is_numeric($identifier) ? (int)$identifier : 0)
                ->orWhere('share_token', $identifier)
                ->orWhere('spk_number', $identifier)
                ->first();

            if ($workOrder) {
                $ba = BaDocument::with(['workOrder.vendor'])->where('work_order_id', $workOrder->id)->first();
            }
        }

        if (!$ba) {
            return response()->json(['success' => false, 'message' => 'Dokumen Berita Acara tidak ditemukan.'], 404);
        }

        $woStatus = strtoupper($ba->workOrder?->status ?? '');
        if (!in_array($woStatus, ['APPROVED', 'COMPLETED', 'BA_OPNAME'])) {
            return response()->json([
                'success' => false,
                'message' => 'Pekerjaan belum disetujui (Approved). Dokumen BA belum dapat diunduh.',
            ], 422);
        }

        $providedPin = trim((string)($request->query('pin') ?: $request->input('pin', '')));
        $masterPin = \App\Models\SystemSetting::where('key', 'ba_download_pin')->value('value') ?: 'SGX2026';
        $clientCode = (string)($ba->workOrder?->vendor?->code ?? '');
        $spkDigits = preg_replace('/[^0-9]/', '', (string)$ba->workOrder?->spk_number);
        $last4Digits = strlen($spkDigits) >= 4 ? substr($spkDigits, -4) : $spkDigits;

        $validPins = array_map('strtolower', array_filter([
            $masterPin,
            'sgx2026',
            $clientCode,
            $last4Digits,
            $spkDigits,
            $ba->workOrder?->vendor?->phone ? substr(preg_replace('/[^0-9]/', '', $ba->workOrder->vendor->phone), -4) : null,
        ]));

        if (empty($providedPin) || !in_array(strtolower($providedPin), $validPins, true)) {
            return response()->json([
                'success' => false,
                'message' => 'PIN Keamanan tidak cocok. Silakan gunakan PIN resmi dari SGX atau 4 digit nomor SPK.',
            ], 403);
        }

        return response()->json([
            'success' => true,
            'message' => 'PIN Keamanan terverifikasi.',
            'download_url' => "/api/ba/{$ba->id}/pdf?pin=" . urlencode($providedPin),
        ]);
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
            'code' => 'required|string',
        ]);

        $data = $request->except(['logo', 'background_image', 'header_image', 'footer_image']);

        // Handle uploaded files
        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('uploads', 'public');
            $data['logo_url'] = '/storage/' . $path;
        }
        if ($request->hasFile('background_image')) {
            $path = $request->file('background_image')->store('uploads', 'public');
            $data['background_image_url'] = '/storage/' . $path;
        }
        if ($request->hasFile('header_image')) {
            $path = $request->file('header_image')->store('uploads', 'public');
            $data['header_image_url'] = '/storage/' . $path;
        }
        if ($request->hasFile('footer_image')) {
            $path = $request->file('footer_image')->store('uploads', 'public');
            $data['footer_image_url'] = '/storage/' . $path;
        }

        if (isset($data['signatories_json']) && is_string($data['signatories_json'])) {
            $data['signatories_json'] = json_decode($data['signatories_json'], true) ?: $data['signatories_json'];
        }

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

        $data = $request->except(['logo', 'background_image', 'header_image', 'footer_image']);

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('uploads', 'public');
            $data['logo_url'] = '/storage/' . $path;
        } elseif ($request->input('remove_logo') === '1') {
            $data['logo_url'] = null;
        }

        if ($request->hasFile('background_image')) {
            $path = $request->file('background_image')->store('uploads', 'public');
            $data['background_image_url'] = '/storage/' . $path;
        } elseif ($request->input('remove_background') === '1') {
            $data['background_image_url'] = null;
            $data['header_image_url'] = null;
        }

        if ($request->hasFile('footer_image')) {
            $path = $request->file('footer_image')->store('uploads', 'public');
            $data['footer_image_url'] = '/storage/' . $path;
        } elseif ($request->input('remove_footer') === '1') {
            $data['footer_image_url'] = null;
        }

        if (isset($data['signatories_json']) && is_string($data['signatories_json'])) {
            $data['signatories_json'] = json_decode($data['signatories_json'], true) ?: $data['signatories_json'];
        }

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
