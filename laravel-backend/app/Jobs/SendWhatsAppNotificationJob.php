<?php

namespace App\Jobs;

use App\Models\NotificationLog;
use App\Services\FonnteService;
use App\Services\WhatsAppTemplateService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [5, 15, 60];

    public string $phone;
    public string $templateKey;
    public array $params;
    public ?string $idempotencyKey;
    public ?string $referenceType;
    public ?int $referenceId;
    public ?int $existingLogId;

    /**
     * Create a new job instance.
     */
    public function __construct(
        string $phone,
        string $templateKey,
        array $params = [],
        ?string $idempotencyKey = null,
        ?string $referenceType = null,
        ?int $referenceId = null,
        ?int $existingLogId = null
    ) {
        $this->phone          = $phone;
        $this->templateKey    = $templateKey;
        $this->params         = $params;
        $this->idempotencyKey = $idempotencyKey;
        $this->referenceType  = $referenceType;
        $this->referenceId    = $referenceId;
        $this->existingLogId  = $existingLogId;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        $result = FonnteService::sendTemplatedMessage(
            $this->phone,
            $this->templateKey,
            $this->params,
            $this->idempotencyKey,
            $this->referenceType,
            $this->referenceId,
            $this->existingLogId
        );

        // If skipped (idempotency match), complete without error
        if ($result['skipped'] ?? false) {
            return;
        }

        // If failed and failure is TEMPORARY, retry job if attempts remain
        if (!$result['success']) {
            $failureType = $result['failure_type'] ?? 'PERMANENT';

            if ($failureType === 'TEMPORARY' && $this->attempts() < $this->tries) {
                Log::warning('[WhatsApp Job] Temporary failure detected, re-queueing with backoff.', [
                    'attempt'      => $this->attempts(),
                    'template'     => $this->templateKey,
                    'error'        => $result['message'],
                ]);
                $this->release($this->backoff[$this->attempts() - 1] ?? 60);
                return;
            }

            Log::warning('[WhatsApp Job] Notification failed permanently or max retries reached.', [
                'attempt'      => $this->attempts(),
                'template'     => $this->templateKey,
                'failure_type' => $failureType,
                'error'        => $result['message'],
            ]);
        }
    }
}
