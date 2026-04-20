<?php

namespace App\Jobs;

use App\Models\Order;
use App\Service\ProdigiOrderService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SubmitOrderToProdigi implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected Order $order;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 3;

    /**
     * The maximum number of seconds a job can run.
     *
     * @var int
     */
    public $timeout = 300;

    /**
     * Create a new job instance.
     */
    public function __construct(Order $order)
    {
        $this->order = $order;
    }

    /**
     * Execute the job.
     */
    public function handle(ProdigiOrderService $prodigiOrderService): void
    {
        try {
            // Skip if order has no print items
            if (!$this->order->hasPrintItems()) {
                Log::info('Skipping Prodigi submission - no print items', [
                    'order_id' => $this->order->id,
                ]);
                return;
            }

            // Skip if already submitted
            if ($this->order->isProdigiSubmitted()) {
                Log::info('Order already submitted to Prodigi', [
                    'order_id' => $this->order->id,
                    'prodigi_order_id' => $this->order->prodigi_order_id,
                ]);
                return;
            }

            // Submit to Prodigi
            $response = $prodigiOrderService->submitOrderToProdigi($this->order);

            $this->order->update([
                'prodigi_submitted_at' => now(),
            ]);

            Log::info('Order successfully submitted to Prodigi', [
                'order_id' => $this->order->id,
                'prodigi_order_id' => $response['id'] ?? null,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to submit order to Prodigi', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
                'attempt' => $this->attempts(),
            ]);

            $this->order->update([
                'prodigi_status' => 'failed',
                'prodigi_error_message' => $e->getMessage(),
            ]);

            // Rethrow to queue the retry
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::critical('SubmitOrderToProdigi job permanently failed', [
            'order_id' => $this->order->id,
            'error' => $exception->getMessage(),
        ]);

        $this->order->update([
            'prodigi_status' => 'failed',
            'prodigi_error_message' => 'Permanent failure: ' . $exception->getMessage(),
        ]);
    }
}
