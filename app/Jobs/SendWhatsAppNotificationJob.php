<?php

namespace App\Jobs;

use App\Services\FonnteService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public string $phone;
    public string $message;
    public string $messageType;
    public array $metadata;
    public ?int $workOrderId;
    public int $randomJitter;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 2;

    /**
     * The maximum number of unhandled exceptions to allow before failing.
     */
    public int $maxExceptions = 2;

    /**
     * Create a new job instance.
     *
     * @param string $phone
     * @param string $message
     * @param string $messageType
     * @param array $metadata
     * @param int|null $workOrderId
     * @param int $randomJitter Jeda acak (detik) untuk human-like rate limiting
     */
    public function __construct(
        string $phone,
        string $message,
        string $messageType = 'CUSTOM',
        array $metadata = [],
        ?int $workOrderId = null,
        int $randomJitter = 0
    ) {
        $this->phone = $phone;
        $this->message = $message;
        $this->messageType = $messageType;
        $this->metadata = $metadata;
        $this->workOrderId = $workOrderId;
        $this->randomJitter = $randomJitter;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        // 1. Safety Check: Verify Master Gateway Power Switch
        if (!FonnteService::isGatewayEnabled()) {
            Log::info("[WA_QUEUE] Skipped WhatsApp to {$this->phone} ({$this->messageType}) — Master Gateway is turned OFF.");
            return;
        }

        // 2. Anti-Spam Rate Limiting: Apply micro random jitter sleep if specified
        if ($this->randomJitter > 0) {
            $sleepSeconds = min(15, max(1, $this->randomJitter));
            Log::info("[WA_QUEUE] Applying {$sleepSeconds}s human-like jitter delay before sending to {$this->phone}");
            sleep($sleepSeconds);
        }

        // 3. Process Spintax & Unique Anti-Hash Signature if not already parsed
        $parsedMessage = FonnteService::renderSpintax($this->message);
        $finalMessage = FonnteService::appendAntiSpamSignature($parsedMessage, (string)($this->workOrderId ?? uniqid()));

        // 4. Send Message via FonnteService
        try {
            $result = FonnteService::sendMessage(
                $this->phone,
                $finalMessage,
                $this->messageType,
                $this->metadata,
                $this->workOrderId
            );

            Log::info("[WA_QUEUE] WhatsApp dispatched to {$this->phone} (Status: " . ($result['status'] ?? 'UNKNOWN') . ")");
        } catch (\Throwable $e) {
            Log::error("[WA_QUEUE] Failed to send WhatsApp to {$this->phone}: " . $e->getMessage());
            throw $e;
        }
    }
}
