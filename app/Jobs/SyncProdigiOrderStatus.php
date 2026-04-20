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

class SyncProdigiOrderStatus implements ShouldQueue
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
    public $timeout = 60;

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
            // Skip if not submitted to Prodigi
            if (!$this->order->isProdigiSubmitted()) {
                Log::debug('Skipping status sync - order not submitted to Prodigi', [
                    'order_id' => $this->order->id,
                ]);
                return;
            }

            // Skip if already shipped
            if ($this->order->isProdigiShipped()) {
                Log::debug('Skipping status sync - order already shipped', [
                    'order_id' => $this->order->id,
                ]);
                return;
            }

            $prodigiOrderService->syncOrderStatus($this->order);

            Log::info('Synced Prodigi order status', [
                'order_id' => $this->order->id,
                'prodigi_status' => $this->order->prodigi_status,
            ]);

        } catch (\Exception $e) {
            Log::error('Failed to sync Prodigi order status', [
                'order_id' => $this->order->id,
                'error' => $e->getMessage(),
            ]);

            throw $e;
        }
    }
}
