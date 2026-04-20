<?php

namespace App\Console\Commands;

use App\Jobs\SyncProdigiOrderStatus;
use App\Models\Order;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class SyncProdigiOrders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prodigi:sync-orders {--order-id= : Sync a specific order by ID}';

    /**
     * The description of the console command.
     *
     * @var string
     */
    protected $description = 'Sync Prodigi order statuses with local database';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $orderId = $this->option('order-id');

        if ($orderId) {
            $order = Order::find($orderId);
            if (!$order) {
                $this->error("Order not found: {$orderId}");
                return 1;
            }

            $this->syncOrder($order);
            return 0;
        }

        // Get all orders submitted to Prodigi but not yet shipped
        $orders = Order::whereNotNull('prodigi_order_id')
            ->whereNotIn('prodigi_status', ['shipped', 'cancelled'])
            ->orderBy('prodigi_submitted_at', 'desc')
            ->get();

        $count = $orders->count();
        $this->info("Syncing {$count} orders with Prodigi...");

        $orders->each(fn($order) => $this->syncOrder($order));

        $this->info('Sync complete!');
        return 0;
    }

    private function syncOrder(Order $order): void
    {
        try {
            dispatch(new SyncProdigiOrderStatus($order));
            $this->line("Queued sync for order: {$order->id}");
        } catch (\Exception $e) {
            $this->error("Error syncing order {$order->id}: {$e->getMessage()}");
            Log::error('Error syncing Prodigi order', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
