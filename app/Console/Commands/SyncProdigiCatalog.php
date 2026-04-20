<?php

namespace App\Console\Commands;

use App\Models\ProdigiProduct;
use App\Models\User;
use App\Service\ProdigiService;
use Illuminate\Console\Command;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Log;

class SyncProdigiCatalog extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'prodigi:sync-catalog {--seed-user-preferences : Attach new catalog products to all users as enabled by default}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Sync available products from Prodigi catalog into local database';

    /**
     * Execute the console command.
     */
    public function handle(ProdigiService $prodigiService): int
    {
        try {
            $response = $prodigiService->getProducts();
            $catalogRows = $this->extractProducts($response);

            if (empty($catalogRows)) {
                $this->warn('No products were returned by the Prodigi API response.');
                return self::SUCCESS;
            }

            $processed = 0;
            foreach ($catalogRows as $row) {
                $sku = (string) Arr::get($row, 'sku', Arr::get($row, 'merchantSku', ''));
                if ($sku === '') {
                    continue;
                }

                $name = (string) Arr::get($row, 'name', $sku);
                $category = Arr::get($row, 'category', Arr::get($row, 'productType'));
                $description = Arr::get($row, 'description', Arr::get($row, 'shortDescription'));

                ProdigiProduct::updateOrCreate(
                    ['sku' => $sku],
                    [
                        'name' => $name,
                        'category' => is_scalar($category) ? (string) $category : null,
                        'description' => is_scalar($description) ? (string) $description : null,
                        'is_active' => true,
                        'raw_payload' => $row,
                    ]
                );

                $processed++;
            }

            $this->info("Synced {$processed} Prodigi products.");

            if ($this->option('seed-user-preferences')) {
                $this->seedUserPreferences();
            }

            return self::SUCCESS;
        } catch (\Throwable $e) {
            Log::error('Prodigi catalog sync failed', [
                'error' => $e->getMessage(),
            ]);

            $this->error('Prodigi catalog sync failed: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Accept both nested and flat product response structures.
     */
    private function extractProducts(array $response): array
    {
        $products = Arr::get($response, 'products');
        if (is_array($products)) {
            return $products;
        }

        if (array_is_list($response)) {
            return $response;
        }

        $items = Arr::get($response, 'items');
        if (is_array($items)) {
            return $items;
        }

        return [];
    }

    private function seedUserPreferences(): void
    {
        $productIds = ProdigiProduct::query()->pluck('id')->all();
        if (empty($productIds)) {
            return;
        }

        $syncPayload = [];
        foreach ($productIds as $productId) {
            $syncPayload[$productId] = ['is_enabled' => true];
        }

        User::query()->select('id')->chunk(100, function ($users) use ($syncPayload) {
            foreach ($users as $user) {
                $user->prodigiProducts()->syncWithoutDetaching($syncPayload);
            }
        });

        $this->info('Default product preferences seeded for all users.');
    }
}
