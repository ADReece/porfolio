# Prodigi Integration Setup Checklist

Use this checklist to ensure proper setup of the Prodigi API integration in your Laravel application.

## [ ] 1. Configuration

- [ ] Add Prodigi credentials to `.env`:
  ```env
  PRODIGI_API_KEY=your_api_key
  PRODIGI_MERCHANT_ID=your_merchant_id
  PRODIGI_ENVIRONMENT=sandbox
  PRODIGI_BASE_URL=https://sandbox.prodigi.com/v4.0
  ```

- [ ] Test credentials in sandbox environment
  ```bash
  php artisan tinker
  >>> app(\App\Service\ProdigiService::class)->getCountries()
  ```

## [ ] 2. Database

- [ ] Run migration to add Prodigi fields to orders table:
  ```bash
  php artisan migrate
  ```

- [ ] Verify new columns exist:
  ```bash
  php artisan tinker
  >>> \Schema::getColumns('orders')
  ```

## [ ] 3. Customize ProdigiOrderService

Edit `app/Service/ProdigiOrderService.php` and update these methods for your specific setup:

- [ ] **mapProductToPraigiSku()** - Map your product types to Prodigi SKUs
  - Get actual SKUs from Prodigi catalog
  - Update the `$skuMap` array
  
- [ ] **getRecipientAddress()** - Implement shipping address retrieval
  - Determine where addresses are stored (User model, separate Address table, etc.)
  - Ensure all required fields: line1, city, county, postcode, countryCode
  
- [ ] **getCustomerPhone()** - Implement phone number retrieval
  - Add phone field to users table if needed:
    ```bash
    php artisan make:migration add_phone_to_users_table
    ```
  - Update the method to retrieve customer phone
  
- [ ] **getProductImageUrl()** - Ensure image URLs are correct
  - Test that URLs are publicly accessible
  - Verify images are suitable for printing (check DPI/resolution)

## [ ] 4. Queue Setup

- [ ] Configure your queue driver in `.env`:
  ```env
  QUEUE_CONNECTION=database  # or redis, sync for testing
  ```

- [ ] If using database queue, run:
  ```bash
  php artisan queue:table
  php artisan migrate
  ```

- [ ] Start queue worker:
  ```bash
  php artisan queue:work
  ```

## [ ] 5. Integration Points

Determine where to trigger Prodigi order submission:

- [ ] Identify order payment confirmation event/method
  - Is it a Stripe webhook handler?
  - A controller action?
  - An event listener?

- [ ] Add order submission in that location:
  ```php
  use App\Jobs\SubmitOrderToProdigi;
  
  // After payment confirmed:
  $order->update(['status' => 'completed', 'paid_at' => now()]);
  SubmitOrderToProdigi::dispatch($order);
  ```

- [ ] Set up the provided event listener (optional):
  ```php
  // In EventServiceProvider.php
  protected $listen = [
      // Your OrderPaid event:
      \App\Events\OrderPaid::class => [
          \App\Listeners\SubmitPaidOrderToProdigi::class,
      ],
  ];
  ```

## [ ] 6. Testing

### Basic Functionality Tests

- [ ] Create a test order with print items in sandbox
  ```bash
  php artisan tinker
  >>> $order = \App\Models\Order::factory()->create(['status' => 'completed']);
  >>> dispatch(new \App\Jobs\SubmitOrderToProdigi($order));
  ```

- [ ] Check that it appears in logs:
  ```bash
  tail -f storage/logs/laravel.log | grep Prodigi
  ```

- [ ] Verify order tracking fields are populated:
  ```bash
  php artisan tinker
  >>> $order->refresh();
  >>> $order->prodigi_order_id
  ```

### Error Handling Tests

- [ ] Test with invalid Prodigi credentials
- [ ] Test with missing shipping address
- [ ] Test with invalid product type
- [ ] Verify error messages are logged

### End-to-End Test

- [ ] Create a customer order through your normal flow
- [ ] Mark order as paid
- [ ] Verify it's submitted to Prodigi
- [ ] Check Prodigi sandbox dashboard to confirm order received
- [ ] Run sync command:
  ```bash
  php artisan prodigi:sync-orders
  ```
- [ ] Verify status was updated

## [ ] 7. Monitoring Setup

- [ ] Add schedule to sync orders periodically:
  ```php
  // In app/Console/Kernel.php
  protected function schedule(Schedule $schedule)
  {
      $schedule->command('prodigi:sync-orders')
          ->everyThirtyMinutes()
          ->withoutOverlapping();
  }
  ```

- [ ] Set up log monitoring for errors:
  ```bash
  grep -i prodigi storage/logs/laravel.log | tail -20
  ```

- [ ] Create monitoring dashboard query:
  ```php
  // Check for failed orders
  Order::where('prodigi_status', 'failed')->get();
  ```

## [ ] 8. Production Readiness

Before going live:

- [ ] Switch Prodigi credentials to production:
  ```env
  PRODIGI_ENVIRONMENT=production
  PRODIGI_BASE_URL=https://api.prodigi.com/v4.0
  PRODIGI_API_KEY=your_production_key
  PRODIGI_MERCHANT_ID=your_production_merchant_id
  ```

- [ ] Test with real order in production environment
- [ ] Verify queue worker will run in production (supervisor, etc.)
- [ ] Set up alerts for failed submissions
- [ ] Test order sync works from production server
- [ ] Configure backup phone number for customer orders without phone
- [ ] Have customer support docs ready for tracking/refund scenarios

## [ ] 9. Documentation

- [ ] Review PRODIGI_INTEGRATION.md completely
- [ ] Document any customizations you've made
- [ ] Create internal documentation for your team
- [ ] Document fallback procedures if Prodigi is unavailable

## [ ] 10. Support & Troubleshooting

- [ ] Bookmark Prodigi API docs: https://www.prodigi.com/print-api/
- [ ] Keep support contact info for Prodigi
- [ ] Document any SKU mappings or product-specific settings
- [ ] Review error handling and create runbook for common errors

---

## Quick Testing Commands

```bash
# Start queue worker in foreground
php artisan queue:work

# Test Prodigi credentials
php artisan tinker
>>> app(\App\Service\ProdigiService::class)->getCountries()

# View recent orders in queue
php artisan queue:failed

# Create test order and submit
php artisan tinker
>>> $order = \App\Models\Order::find('order-uuid');
>>> dispatch(new \App\Jobs\SubmitOrderToProdigi($order));
>>> $order->refresh();
>>> dd($order->prodigi_order_id, $order->prodigi_status);

# Manually sync all pending orders
php artisan prodigi:sync-orders

# Sync specific order
php artisan prodigi:sync-orders --order-id=uuid-here

# Watch logs in real time
tail -f storage/logs/laravel.log | grep -i prodigi
```

---

## Notes

- All code is structured to allow easy testing with PHPUnit
- Jobs are queued by default (use `QUEUE_CONNECTION=sync` for testing)
- All API calls are logged for debugging
- Errors are stored on the order model for visibility
- Implementation assumes print products have types: `print_poster`, `print_mug`, `print_canvas`
