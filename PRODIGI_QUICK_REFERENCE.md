# Prodigi Integration - Quick Reference Guide

## Integration Points - Where to Implement

### 1. Payment Confirmation Handler
**File**: Wherever you confirm Stripe payments (webhook or controller)

```php
// After payment is confirmed and order status = 'completed'
use App\Jobs\SubmitOrderToProdigi;

$order->update(['status' => 'completed', 'paid_at' => now()]);

// Submit print orders to Prodigi
if ($order->hasPrintItems()) {
    SubmitOrderToProdigi::dispatch($order);
}
```

### 2. Event Listener (Alternative)
**File**: `app/Listeners/SubmitPaidOrderToProdigi.php` (already created)

Register in `app/Providers/EventServiceProvider.php`:
```php
protected $listen = [
    \App\Events\OrderPaid::class => [
        \App\Listeners\SubmitPaidOrderToProdigi::class,
    ],
];
```

### 3. Webhook Integration
**File**: `app/Http/Controllers/StripeWebhookController.php` (example provided)

Routes example:
```php
// routes/api.php or routes/webhook.php
Route::post('/stripe/webhook', [StripeWebhookController::class, 'handle']);
```

---

## Customization Checklist

### Required Customizations in ProdigiOrderService

#### 1. Product SKU Mapping
**File**: `app/Service/ProdigiOrderService.php` - `mapProductToPraigiSku()` method

```php
private function mapProductToPraigiSku($product): string
{
    $skuMap = [
        'print_poster' => 'POSTER_A2_GLOSS',     // ← CHANGE THESE
        'print_mug' => 'MUG_CERAMIC_11OZ',       // ← CHANGE THESE
        'print_canvas' => 'CANVAS_12X16_GLOSS',  // ← CHANGE THESE
    ];

    return $skuMap[$product->type] ?? 'POSTER_A2_GLOSS';
}
```

**How to find correct SKUs:**
```bash
php artisan tinker
>>> $service = new \App\Service\ProdigiService();
>>> $products = $service->getProducts();
>>> // Look for product SKUs that match your offerings
```

#### 2. Shipping Address
**File**: `app/Service/ProdigiOrderService.php` - `getRecipientAddress()` method

Current placeholder:
```php
private function getRecipientAddress(Order $order): array
{
    return [
        'line1' => 'Customer Address Line 1',    // ← GET ACTUAL ADDRESS
        'line2' => null,
        'city' => 'City',                        // ← GET ACTUAL CITY
        'county' => 'State/Province',            // ← GET ACTUAL STATE
        'postcode' => 'Postal Code',             // ← GET ACTUAL ZIP
        'countryCode' => 'US',                   // ← GET COUNTRY CODE
    ];
}
```

**Options for storing addresses:**

Option A - Add fields to Order model:
```php
// In migration:
$table->string('shipping_street');
$table->string('shipping_city');
$table->string('shipping_state');
$table->string('shipping_zip');
$table->string('shipping_country');
```

Option B - Create separate Address model:
```php
// Create: app/Models/Address.php
// Add to Order: public function shippingAddress() { return $this->hasOne(Address::class); }
```

Option C - Load from User profile:
```php
private function getRecipientAddress(Order $order): array
{
    $user = $order->user;
    return [
        'line1' => $user->address_line_1,
        'line2' => $user->address_line_2,
        'city' => $user->city,
        'county' => $user->state,
        'postcode' => $user->zip,
        'countryCode' => 'US', // or $user->country
    ];
}
```

#### 3. Customer Phone Number
**File**: `app/Service/ProdigiOrderService.php` - `getCustomerPhone()` method

Current placeholder:
```php
private function getCustomerPhone(Order $order): string
{
    return $order->user?->phone ?? '+1-555-0000';  // ← IMPLEMENT PROPERLY
}
```

**Implementation options:**

Option A - Add phone to users table:
```bash
php artisan make:migration add_phone_to_users_table
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->nullable();
    });
}
```

Option B - Add phone to orders:
```bash
php artisan make:migration add_phone_to_orders_table
```

```php
// In migration
$table->string('customer_phone')->nullable();

// In prodigiOrderService:
private function getCustomerPhone(Order $order): string
{
    return $order->customer_phone ?? $order->user?->phone ?? '';
}
```

#### 4. Product Image URLs
**File**: `app/Service/ProdigiOrderService.php` - `getProductImageUrl()` method

Current implementation:
```php
private function getProductImageUrl($product): string
{
    if ($product->photo && $product->photo->path) {
        return url('storage/' . $product->photo->path);
    }
    return url('/placeholder.jpg');
}
```

**Verify:**
- ✅ URLs are publicly accessible (test in browser)
- ✅ Images exist and are correct format
- ✅ No authentication required
- ✅ HTTPS works (Prodigi API accesses externally)

---

## Customization Example - Complete

Here's a complete example of customizing for a typical setup:

```php
// app/Service/ProdigiOrderService.php

private function mapProductToPraigiSku($product): string
{
    // Your actual Prodigi SKUs
    $skuMap = [
        'print_poster' => 'PSTR_12X18_GLOSS',
        'print_mug' => 'MUG_WHITE_325ML',
        'print_canvas' => 'CVSTR_20X30_STD',
    ];
    return $skuMap[$product->type] ?? 'PSTR_12X18_GLOSS';
}

private function getRecipientAddress(Order $order): array
{
    // Assuming you have address fields on the order
    return [
        'line1' => $order->shipping_street,
        'line2' => $order->shipping_street_2,
        'city' => $order->shipping_city,
        'county' => $order->shipping_state,
        'postcode' => $order->shipping_zip,
        'countryCode' => strtoupper($order->shipping_country), // e.g., 'US'
    ];
}

private function getCustomerPhone(Order $order): string
{
    // Assuming you added phone field to orders table
    if ($order->customer_phone) {
        return $order->customer_phone;
    }
    
    if ($order->user?->phone) {
        return $order->user->phone;
    }
    
    // Fallback - but this should be collected at checkout
    return '+1-000-0000';
}

private function getProductImageUrl($product): string
{
    if ($product->photo && $product->photo->path) {
        // Make sure this is publicly accessible
        return url('storage/' . $product->photo->path);
    }
    return url('/images/placeholder.jpg');
}
```

---

## Database Schema Updates Needed

### If storing address in orders table:
```php
php artisan make:migration add_shipping_address_to_orders
```

```php
public function up()
{
    Schema::table('orders', function (Blueprint $table) {
        $table->string('shipping_street');
        $table->string('shipping_street_2')->nullable();
        $table->string('shipping_city');
        $table->string('shipping_state');
        $table->string('shipping_zip');
        $table->string('shipping_country')->default('US');
    });
}
```

### If adding phone to users:
```php
php artisan make:migration add_phone_to_users
```

```php
public function up()
{
    Schema::table('users', function (Blueprint $table) {
        $table->string('phone')->nullable();
    });
}
```

---

## Testing Workflow

### 1. Basic Setup Test
```bash
# Test credentials
php artisan tinker
>>> app(\App\Service\ProdigiService::class)->getCountries()
// Should return countries list
```

### 2. Create Test Order
```bash
php artisan tinker
>>> $user = \App\Models\User::first();
>>> $product = \App\Models\Product::where('type', 'print_poster')->first();
>>> $order = \App\Models\Order::create([
...     'user_id' => $user->id,
...     'customer_email' => 'test@example.com',
...     'customer_name' => 'Test User',
...     'total' => 50.00,
...     'platform_fee' => 5.00,
...     'photographer_amount' => 45.00,
...     'status' => 'completed',
...     'paid_at' => now(),
... ]);
>>> \App\Models\OrderItem::create([
...     'order_id' => $order->id,
...     'product_id' => $product->id,
...     'quantity' => 1,
...     'price' => 50.00,
... ]);
```

### 3. Submit to Prodigi
```bash
php artisan tinker
>>> $order = \App\Models\Order::latest()->first();
>>> dispatch(new \App\Jobs\SubmitOrderToProdigi($order));
```

### 4. Check Results
```bash
php artisan tinker
>>> $order->refresh();
>>> $order->prodigi_order_id      // Should have ID
>>> $order->prodigi_status        // Should be 'pending' or 'received'
>>> $order->prodigi_error_message // Check if empty

# Or check logs
tail -50 storage/logs/laravel.log | grep -i prodigi
```

---

## Common SKU Formats

Common Prodigi product types (update with actual SKUs):

| Product Type | Example SKU | Size | Notes |
|---|---|---|---|
| Poster | PSTR_A2_GLOSS | 42 x 59.4cm | Most common |
| Canvas | CVSTR_16X20_STD | 40 x 50cm | Stretched canvas |
| Mug | MUG_WHITE | 325ml | Ceramic |
| Photo Print | PRNT_A4_GLOSS | 21 x 29.7cm | Standard photo |
| Acrylic | ACRYL_A3 | 29.7 x 42cm | Acrylic print |

**Get actual SKUs:**
```bash
php artisan tinker
>>> $prodigi = new \App\Service\ProdigiService();
>>> $products = $prodigi->getProducts();
>>> foreach($products as $p) echo $p['merchantSku'] . "\n";
```

---

## Monitoring & Maintenance

### Daily
```bash
# Check for failed orders
tail -20 storage/logs/laravel.log | grep "Prodigi"
```

### Weekly
```bash
# Sync all pending orders
php artisan prodigi:sync-orders

# Check for stuck orders
php artisan tinker
>>> \App\Models\Order::where('prodigi_status', 'failed')->count()
```

### Monthly
```bash
# Verify credentials still work
php artisan tinker
>>> app(\App\Service\ProdigiService::class)->getCountries()

# Review Prodigi pricing changes
>>> app(\App\Service\ProdigiService::class)->getProducts()
```

---

## Troubleshooting

### "Address validation failed"
- Check all required fields are present
- Verify countryCode is 2-letter ISO code (US, CA, GB, etc.)

### "Invalid SKU"
- Run the SKU discovery command above
- Update mapProductToPraigiSku() with correct SKUs

### "Phone number invalid"
- Prodigi expects format with country code: +1-555-000-0000
- Update getCustomerPhone() to format properly

### "Image URL not accessible"
- Test URL in browser
- Ensure no authentication required
- Check HTTPS works
- Verify full path is correct

### Jobs not processing
```bash
# Make sure queue worker is running
php artisan queue:work

# Check failed jobs
php artisan queue:failed
```

---

## Production Checklist

Before going live:

- [ ] Update .env with production Prodigi credentials
- [ ] Test end-to-end with real order in production
- [ ] Verify queue worker will persist (supervisor, systemd, etc.)
- [ ] Set up log monitoring for errors
- [ ] Have backup procedure if Prodigi is down
- [ ] Document customer support procedures
- [ ] Test refund/cancellation workflows
- [ ] Verify address collection in checkout
- [ ] Test phone number collection
- [ ] Verify all images are accessible from production server
