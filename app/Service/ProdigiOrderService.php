<?php

namespace App\Service;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Support\Facades\Log;

class ProdigiOrderService
{
    private ProdigiService $prodigiService;

    public function __construct(ProdigiService $prodigiService)
    {
        $this->prodigiService = $prodigiService;
    }

    /**
     * Submit an order to Prodigi for fulfillment
     * 
     * @throws \Exception
     */
    public function submitOrderToProdigi(Order $order): array
    {
        // Get print items only (filter out digital products)
        $printItems = $order->items()
            ->whereHas('product', function ($query) {
                $query->where('type', 'like', 'print%')
                    ->orWhereNotNull('prodigi_sku');
            })
            ->get();

        if ($printItems->isEmpty()) {
            throw new \Exception('No print items in order to submit to Prodigi');
        }

        $orderPayload = $this->buildProdigiOrderPayload($order, $printItems);

        // Submit to Prodigi
        $response = $this->prodigiService->createOrder($orderPayload);

        // Store the Prodigi order ID and status in our database
        $order->update([
            'prodigi_order_id' => $response['id'] ?? null,
            'prodigi_status' => $response['status'] ?? 'pending',
            'prodigi_charge' => $response['charge'] ?? null,
        ]);

        Log::info('Order submitted to Prodigi', [
            'order_id' => $order->id,
            'prodigi_order_id' => $response['id'] ?? null,
        ]);

        return $response;
    }

    /**
     * Build the Prodigi order payload from our order
     */
    private function buildProdigiOrderPayload(Order $order, $printItems): array
    {
        // Get customer address - you may need to adjust based on where you store this
        $recipientAddress = $this->getRecipientAddress($order);

        return [
            'idempotencyKey' => 'order_' . $order->id . '_' . time(),
            'shipments' => [
                [
                    'recipient' => [
                        'name' => $order->customer_name ?? 'Customer',
                        'email' => $order->customer_email,
                        'phoneNumber' => $this->getCustomerPhone($order),
                        'address' => $recipientAddress,
                    ],
                    'lineItems' => $this->buildLineItems($printItems),
                    'shippingMethod' => 'Standard', // Can make this configurable
                ],
            ],
        ];
    }

    /**
     * Build line items for Prodigi order
     */
    private function buildLineItems($printItems): array
    {
        return $printItems->map(function (OrderItem $item) {
            $product = $item->product;

            return [
                'sku' => $this->mapProductToProdigiSku($product),
                'quantity' => $item->quantity,
                'assets' => [
                    [
                        'printArea' => 'default',
                        'assetType' => 'image',
                        'imageUrl' => $this->getProductImageUrl($product),
                    ],
                ],
            ];
        })->toArray();
    }

    /**
     * Map our product to a Prodigi SKU
     * Adjust this based on your product types and Prodigi catalog
     */
    private function mapProductToProdigiSku($product): string
    {
        if (!empty($product->prodigi_sku)) {
            return $product->prodigi_sku;
        }

        // Example mapping - you'll need to customize based on Prodigi's actual SKUs
        $skuMap = [
            'print_poster' => 'POSTER_A2_GLOSS', // Adjust to actual Prodigi SKUs
            'print_mug' => 'MUG_CERAMIC_11OZ',
            'print_canvas' => 'CANVAS_12X16_GLOSS',
        ];

        return $skuMap[$product->type] ?? 'POSTER_A2_GLOSS';
    }

    /**
     * Get the product image URL for printing
     */
    private function getProductImageUrl($product): string
    {
        if ($product->photo && $product->photo->path) {
            return url('storage/' . $product->photo->path);
        }

        // Fallback or placeholder
        return url('/placeholder.jpg');
    }

    private function getRecipientAddress(Order $order): array
    {
        $required = [
            'shipping_address_line1',
            'shipping_city',
            'shipping_county',
            'shipping_postcode',
            'shipping_country_code',
        ];

        foreach ($required as $field) {
            if (blank($order->{$field})) {
                throw new \Exception("Missing required shipping field: {$field}");
            }
        }

        return [
            'line1' => $order->shipping_address_line1,
            'line2' => $order->shipping_address_line2,
            'city' => $order->shipping_city,
            'county' => $order->shipping_county,
            'postcode' => $order->shipping_postcode,
            'countryCode' => strtoupper($order->shipping_country_code),
        ];
    }

    private function getCustomerPhone(Order $order): string
    {
        if (blank($order->customer_phone)) {
            throw new \Exception('Missing required customer phone on order');
        }

        return $order->customer_phone;
    }

    /**
     * Update order status from Prodigi
     */
    public function syncOrderStatus(Order $order): void
    {
        if (!$order->prodigi_order_id) {
            return;
        }

        $response = $this->prodigiService->getOrderStatus($order->prodigi_order_id);

        $order->update([
            'prodigi_status' => $response['status'] ?? 'unknown',
        ]);

        Log::info('Synced Prodigi order status', [
            'order_id' => $order->id,
            'prodigi_status' => $response['status'] ?? 'unknown',
        ]);
    }
}
