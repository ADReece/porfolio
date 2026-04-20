<?php

namespace App\Service;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ProdigiService
{
    private string $apiKey;
    private string $merchantId;
    private string $baseUrl;

    public function __construct()
    {
        $this->apiKey = config('services.prodigi.api_key');
        $this->merchantId = config('services.prodigi.merchant_id');
        $this->baseUrl = config('services.prodigi.base_url');

        if (!$this->apiKey || !$this->merchantId) {
            throw new \Exception('Prodigi API credentials not configured');
        }
    }

    /**
     * Create a new order with Prodigi
     */
    public function createOrder(array $orderData): array
    {
        $response = $this->client()
            ->post($this->baseUrl . '/orders', $orderData);

        if (!$response->successful()) {
            Log::error('Prodigi order creation failed', [
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $orderData,
            ]);

            throw new \Exception(
                'Failed to create Prodigi order: ' . $response->json('message') ?? $response->body()
            );
        }

        Log::info('Prodigi order created successfully', [
            'order_id' => $response->json('id'),
        ]);

        return $response->json();
    }

    /**
     * Get order status from Prodigi
     */
    public function getOrderStatus(string $prodigiOrderId): array
    {
        $response = $this->client()
            ->get($this->baseUrl . '/orders/' . $prodigiOrderId);

        if (!$response->successful()) {
            Log::error('Failed to fetch Prodigi order status', [
                'prodigi_order_id' => $prodigiOrderId,
                'status' => $response->status(),
            ]);

            throw new \Exception('Failed to fetch order status from Prodigi');
        }

        return $response->json();
    }

    /**
     * Cancel a Prodigi order
     */
    public function cancelOrder(string $prodigiOrderId): array
    {
        $response = $this->client()
            ->post($this->baseUrl . '/orders/' . $prodigiOrderId . '/cancel');

        if (!$response->successful()) {
            Log::error('Failed to cancel Prodigi order', [
                'prodigi_order_id' => $prodigiOrderId,
                'status' => $response->status(),
            ]);

            throw new \Exception('Failed to cancel Prodigi order');
        }

        return $response->json();
    }

    /**
     * Get available countries for shipping
     */
    public function getCountries(): array
    {
        $response = $this->client()
            ->get($this->baseUrl . '/countries');

        if (!$response->successful()) {
            Log::error('Failed to fetch Prodigi countries', [
                'status' => $response->status(),
            ]);

            throw new \Exception('Failed to fetch countries from Prodigi');
        }

        return $response->json();
    }

    /**
     * Get products catalog from Prodigi
     */
    public function getProducts(): array
    {
        $response = $this->client()
            ->get($this->baseUrl . '/products');

        if (!$response->successful()) {
            Log::error('Failed to fetch Prodigi products', [
                'status' => $response->status(),
            ]);

            throw new \Exception('Failed to fetch products from Prodigi');
        }

        return $response->json();
    }

    /**
     * Build the HTTP client with authentication
     */
    private function client(): PendingRequest
    {
        return Http::withHeaders([
            'Authorization' => 'Bearer ' . $this->apiKey,
            'X-Merchant-Id' => $this->merchantId,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ])
            ->timeout(30)
            ->retry(3, 100);
    }
}
