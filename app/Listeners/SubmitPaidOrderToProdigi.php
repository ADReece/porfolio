<?php

namespace App\Listeners;

use App\Jobs\SubmitOrderToProdigi;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

/**
 * Listener for when an order payment is completed
 * 
 * Usage:
 * In your OrderPaidEvent listener or wherever you mark orders as paid:
 * 
 * event(new OrderPaid($order));
 */
class SubmitPaidOrderToProdigi implements ShouldQueue
{
    use InteractsWithQueue;

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     * 
     * Note: Adjust the event class name based on your actual event
     */
    public function handle($event): void
    {
        $order = $event->order;

        // Only submit if order has print items
        if (!$order->hasPrintItems()) {
            Log::info('Order has no print items, skipping Prodigi submission', [
                'order_id' => $order->id,
            ]);
            return;
        }

        // Queue for submission
        dispatch(new SubmitOrderToProdigi($order));

        Log::info('Order queued for Prodigi submission', [
            'order_id' => $order->id,
        ]);
    }
}
