<?php

namespace App\Services;

use App\Models\BaDocument;
use App\Models\DocumentTemplate;
use App\Models\WorkOrder;
use Exception;

class BaDocumentService
{
    public static function generateBaNumber(): string
    {
        $year = date('Y');
        $month = date('m');
        $prefix = "BA-SGX-{$year}{$month}-";
        $latest = BaDocument::where('ba_number', 'LIKE', "{$prefix}%")
            ->orderByDesc('id')
            ->first();

        if ($latest) {
            $parts = explode('-', $latest->ba_number);
            $seq = intval(end($parts)) + 1;
        } else {
            $seq = 1;
        }

        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    public static function createOrUpdateBa($user, WorkOrder $workOrder, ?int $templateId = null): BaDocument
    {
        $template = $templateId ? DocumentTemplate::find($templateId) : DocumentTemplate::where('is_default', true)->first();
        if (!$template) {
            $template = DocumentTemplate::first();
        }

        $baNumber = self::generateBaNumber();

        $contentJson = [
            'work_order' => [
                'id' => $workOrder->id,
                'spk_number' => $workOrder->spk_number,
                'title' => $workOrder->title,
                'location_name' => $workOrder->location_name,
                'vendor_name' => $workOrder->vendor?->name,
                'pic_name' => $workOrder->pic?->name,
                'contract_value' => 15000000,
            ],
            'items' => $workOrder->items->map(fn($i) => [
                'id' => $i->id,
                'item_name' => $i->item_name,
                'doc_mode' => $i->doc_mode,
                'status' => $i->status,
            ])->toArray(),
            'check_in' => $workOrder->checkIns->first() ? [
                'latitude' => $workOrder->checkIns->first()->latitude,
                'longitude' => $workOrder->checkIns->first()->longitude,
                'server_timestamp' => $workOrder->checkIns->first()->server_timestamp,
            ] : null,
            'photos' => $workOrder->evidencePhotos->map(fn($p) => [
                'id' => $p->id,
                'stage' => $p->stage,
                'sequence' => $p->sequence,
                'file_path' => $p->file_path,
                'file_hash' => $p->file_hash,
                'server_timestamp' => $p->server_timestamp,
            ])->toArray(),
            'signatories' => [
                [
                    'party_title' => 'Pihak Pertama (Vendor Pelaksana)',
                    'company_name' => $workOrder->vendor?->name ?? 'Mitra Vendor',
                    'name' => $workOrder->pic?->name ?? 'Andi Pratama',
                    'role' => 'Penanggung Jawab Lapangan',
                ],
                [
                    'party_title' => 'Pihak Kedua (SGX Management)',
                    'company_name' => 'PT SINAR GRAHA KONSTRUKSI',
                    'name' => $user->name,
                    'role' => 'Quality Assurance & Operations',
                ]
            ]
        ];

        $ba = BaDocument::updateOrCreate(
            ['work_order_id' => $workOrder->id],
            [
                'ba_number' => $baNumber,
                'ba_date' => now()->toDateString(),
                'template_id' => $template?->id,
                'generated_by' => $user->id,
                'content_json' => $contentJson,
                'status' => 'FINAL',
            ]
        );

        AuditService::log($user, 'GENERATE_BA_OPNAME', 'BA_DOCUMENT', $ba->id, null, [
            'ba_number' => $baNumber,
            'work_order_id' => $workOrder->id,
        ]);

        return $ba->load(['workOrder', 'template', 'generator']);
    }
}
