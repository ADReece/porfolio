# Prodigi API Integration Guide

This document outlines the implementation of Prodigi API integration for order fulfillment in your Laravel application.

## Overview

The Prodigi integration allows your application to automatically submit print orders to Prodigi for fulfillment. When customers order print products, the system:

1. Captures the order details
2. Submits them to Prodigi for production and shipping
3. Tracks fulfillment status
4. Updates your database with tracking information

## Setup

### 1. Environment Variables

Add these to your `.env` file:

```env
PRODIGI_API_KEY=your_api_key_here
PRODIGI_MERCHANT_ID=your_merchant_id_here
PRODIGI_ENVIRONMENT=sandbox  # Use 'sandbox' for testing, 'production' for live
PRODIGI_BASE_URL=https://sandbox.prodigi.com/v4.0  # Adjust for production
```

You can obtain credentials from [Prodigi's API Documentation](https://www.prodigi.com/print-api/).

### 2. Run Migrations

Apply the new migration to add Prodigi fields to the orders table:

```bash
php artisan migrate
```

The migration adds these fields to track Prodigi orders:
- `prodigi_order_id` - Prodigi's unique order identifier
- `prodigi_status` - Current status in Prodigi (pending, received, validated, processing, shipped, cancelled, failed)
- `prodigi_charge` - Amount charged by Prodigi
- `prodigi_submitted_at` - When order was submitted
- `prodigi_shipped_at` - When order was shipped
- `prodigi_tracking_number` - Shipping tracking number
- `prodigi_error_message` - Any error messages

## Architecture

### Services

#### `ProdigiService` (`app/Service/ProdigiService.php`)

Low-level HTTP client for Prodigi API. Handles authentication and API communication.

**Methods:**
- `createOrder(array $orderData)` - Submit a new order
- `getOrderStatus(string $prodigiOrderId)` - Get order status
- `cancelOrder(string $prodigiOrderId)` - Cancel an order
- `getCountries()` - List available shipping countries
- `getProducts()` - Get Prodigi product catalog

#### `ProdigiOrderService` (`app/Service/ProdigiOrderService.php`)

Business logic for transforming your orders into Prodigi format.

**Key Methods:**
- `submitOrderToProdigi(Order $order)` - Submit an order to Prodigi
- `syncOrderStatus(Order $order)` - Fetch and update order status from Prodigi
- `buildProdigiOrderPayload(Order $order, $printItems)` - Transform order data

**Important: Customization Required**

The `ProdigiOrderService` has several methods that need customization for your setup:

1. **`mapProductToPraigiSku()`** - Map your product types to Prodigi SKUs
   ```php
   $skuMap = [
       'print_poster' => 'POSTER_A2_GLOSS', // Use actual Prodigi SKUs
       'print_mug' => 'MUG_CERAMIC_11OZ',
       'print_canvas' => 'CANVAS_12X16_GLOSS',
   ];
   ```

2. **`getRecipientAddress()`** - Get customer shipping address
   - Currently a placeholder
   - Customize to load from your User/Address model if you have one
   - Prodigi requires: line1, city, county, postcode, countryCode

3. **`getCustomerPhone()`** - Get customer phone number
   - Currently pulls from `$order->user->phone`
   - Add phone field to your users table if not present
   - Required by Prodigi API

4. **`getProductImageUrl()`** - Get image URL for printing
   - Currently uses photo path from product relationship
   - Ensure the URL is publicly accessible from Prodigi's servers

### Jobs

#### `SubmitOrderToProdigi` (`app/Jobs/SubmitOrderToProdigi.php`)

Background job that:
- Submits orders to Prodigi asynchronously
- Retries up to 3 times on failure
- Stores error messages if submission fails
- Runs with 5-minute timeout

**Dispatch the job when an order is paid:**

```php
// In your order controller or payment handler
SubmitOrderToProdigi::dispatch($order);
```

#### `SyncProdigiOrderStatus` (`app/Jobs/SyncProdigiOrderStatus.php`)

Background job that:
- Fetches current status from Prodigi
- Updates local order record
- Retries up to 3 times on failure

**Dispatch manually or schedule periodically:**

```php
// In a scheduled command or event
SyncProdigiOrderStatus::dispatch($order);
```

### Artisan Commands

#### `php artisan prodigi:sync-orders`

Syncs all pending Prodigi orders with current status.

**Options:**
- `--order-id=UUID` - Sync a specific order

**Examples:**
```bash
# Sync all pending orders
php artisan prodigi:sync-orders

# Sync a specific order
php artisan prodigi:sync-orders --order-id=123e4567-e89b-12d3-a456-426614174000
```

## Usage Examples

### 1. Submit an Order When Payment Completes

In your payment confirmation handler (e.g., after Stripe webhook):

```php
// After payment is confirmed
$order->update(['status' => 'completed', 'paid_at' => now()]);

// Queue order for Prodigi submission
SubmitOrderToProdigi::dispatch($order);
```

### 2. Check Order Status

```php
$order = Order::find($orderId);

if ($order->isProdigiSubmitted()) {
    echo "Prodigi Order ID: {$order->prodigi_order_id}";
    echo "Status: {$order->prodigi_status}";
    
    if ($order->isProdigiShipped()) {
        echo "Tracking: {$order->prodigi_tracking_number}";
    }
}
```

### 3. Manually Sync a Single Order

```php
// In a console command or controller
$order = Order::find($orderId);
dispatch(new SyncProdigiOrderStatus($order));
```

### 4. Handle Order Submission Errors

Errors are stored in `prodigi_error_message`. Check the logs:

```bash
# View recent errors
tail -f storage/logs/laravel.log | grep Prodigi
```

### 5. Filter Orders by Prodigi Status

```php
// Get all shipped orders
$shippedOrders = Order::where('prodigi_status', 'shipped')->get();

// Get all pending submissions
$pendingSubmission = Order::whereNull('prodigi_order_id')
    ->where('status', 'completed')
    ->get();

// Get failed orders
$failedOrders = Order::where('prodigi_status', 'failed')->get();
```

## Important Notes

### Product Type Matching

Your products use types like `'print_poster'`, `'print_mug'`, `'print_canvas'`. These must map to actual Prodigi SKUs. Check Prodigi's product catalog:

```php
// List Prodigi products
$prodigiService = new ProdigiService();
$products = $prodigiService->getProducts();
```

### Image Handling

- Images must be publicly accessible URLs
- Prodigi will fetch them from your server, so ensure the URL is live
- Consider optimizing images for print (high DPI recommendations)

### Shipping Address Requirements

Prodigi requires complete shipping addresses. If you currently don't store these, you'll need to:

1. Add address fields to your User or Order model
2. Collect address during checkout
3. Update `getRecipientAddress()` in `ProdigiOrderService`

### Testing in Sandbox

Before going live, thoroughly test with Prodigi's sandbox environment:

```bash
# Verify your test credentials work
php artisan tinker
>>> $service = new \App\Service\ProdigiService();
>>> $countries = $service->getCountries();
```

### Error Handling

The system logs all errors to `storage/logs/laravel.log`. Monitor for:
- Invalid SKUs
- Missing shipping addresses
- Invalid image URLs
- Authentication failures

## Monitoring

### Check Order Submission Status

```php
// In tinker or controller
$orders = Order::whereNotNull('prodigi_order_id')
    ->where('prodigi_status', 'failed')
    ->get();
```

### View Recent Errors

```bash
grep "Prodigi order creation failed" storage/logs/laravel.log
```

### Schedule Automatic Syncing

Add to `app/Console/Kernel.php`:

```php
protected function schedule(Schedule $schedule)
{
    // Sync Prodigi orders every 30 minutes
    $schedule->command('prodigi:sync-orders')
        ->everyThirtyMinutes()
        ->withoutOverlapping();
}
```

## Troubleshooting

### "Prodigi API credentials not configured"

Ensure `PRODIGI_API_KEY` and `PRODIGI_MERCHANT_ID` are set in `.env`.

### "Invalid SKU"

Update the `mapProductToPraigiSku()` method in `ProdigiOrderService` with correct SKU values from Prodigi's catalog.

### "Address validation failed"

Ensure the shipping address includes all required fields:
- `line1` (street address)
- `city`
- `county` (state/province)
- `postcode` (zip code)
- `countryCode` (2-letter ISO code)

### "Failed to fetch product image"

Ensure your image URLs are:
- Publicly accessible (not behind authentication)
- Valid HTTP/HTTPS URLs
- Returning the correct content type

### Queue Not Processing

If jobs aren't processing, check your queue has a worker running:

```bash
# Start queue worker in development
php artisan queue:work

# In production, use supervisor or similar
```

## API Documentation

For complete Prodigi API reference, see:
https://www.prodigi.com/print-api/

Key endpoints used:
- `POST /orders` - Create order
- `GET /orders/{id}` - Get order status
- `POST /orders/{id}/cancel` - Cancel order
- `GET /countries` - List countries
- `GET /products` - List products

## Support

All Prodigi API errors are logged with full response details. Check `storage/logs/laravel.log` for debugging information.
