# Quick Start Checklist ✅

## Immediate Actions

### 1. Database Setup ✓
- [x] Migrations created
- [x] Subscription plans seeded
- [ ] Assign free plan to existing users (run in tinker):
```php
$freePlan = App\Models\SubscriptionPlan::where('slug', 'free')->first();
App\Models\User::whereNull('subscription_plan_id')->update(['subscription_plan_id' => $freePlan->id]);
```

### 2. Stripe Configuration ⚠️
- [ ] Create Stripe account (https://stripe.com)
- [ ] Get test API keys from Dashboard
- [ ] Create two products in Stripe:
  - [ ] "Photographer Plan" - $9.99/month recurring
  - [ ] "Videographer Plan" - $14.99/month recurring
- [ ] Copy Price IDs and update database:
```php
// In tinker:
$photographer = App\Models\SubscriptionPlan::where('slug', 'photographer')->first();
$photographer->stripe_price_id = 'price_XXXXX';
$photographer->stripe_product_id = 'prod_XXXXX';
$photographer->save();

$videographer = App\Models\SubscriptionPlan::where('slug', 'videographer')->first();
$videographer->stripe_price_id = 'price_XXXXX';
$videographer->stripe_product_id = 'prod_XXXXX';
$videographer->save();
```

### 3. Environment Variables ⚠️
Update your `.env` file:
```env
STRIPE_KEY=pk_test_xxxxx
STRIPE_SECRET=sk_test_xxxxx
STRIPE_WEBHOOK_SECRET=whsec_xxxxx
STRIPE_CONNECT_CLIENT_ID=ca_xxxxx
```

### 4. Stripe Connect Setup ⚠️
- [ ] Enable Stripe Connect in Dashboard
- [ ] Get Connect Client ID
- [ ] Add redirect URI: `https://yourdomain.com/connect/stripe/callback`

### 5. Webhooks ⚠️
- [ ] Add webhook endpoint: `https://yourdomain.com/stripe/webhook`
- [ ] Select events:
  - customer.subscription.created
  - customer.subscription.updated  
  - customer.subscription.deleted
  - payment_intent.succeeded
  - payment_intent.failed
- [ ] Copy webhook signing secret to .env

### 6. Testing 🧪
- [ ] Visit `/pricing` page
- [ ] Click "Subscribe Now" on Photographer plan
- [ ] Use test card: `4242 4242 4242 4242`
- [ ] Verify subscription created
- [ ] Test feature gates with free account
- [ ] Test photo/collection limits
- [ ] Test Stripe Connect flow

## Routes Available

### Public
- `/` - Homepage
- `/pricing` - Pricing page  
- `/about` - About page
- `/terms` - Terms of Service
- `/privacy` - Privacy Policy
- `/sla` - Service Level Agreement

### Authenticated
- `/dashboard` - User dashboard
- `/checkout/{plan}` - Subscribe to plan
- `/billing/portal` - Manage billing (Stripe portal)
- `/subscription/cancel` - Cancel subscription
- `/subscription/resume` - Resume subscription
- `/connect/stripe` - Connect Stripe for selling

## Test Cards

```
Success: 4242 4242 4242 4242
Decline: 4000 0000 0000 0002
Requires Auth: 4000 0025 0000 3155
```

## Common Commands

```bash
# Run migrations
php artisan migrate

# Seed subscription plans
php artisan db:seed --class=SubscriptionPlanSeeder

# Clear cache
php artisan cache:clear
php artisan config:clear
php artisan view:clear

# Queue worker (for webhooks in production)
php artisan queue:work

# Tinker (database operations)
php artisan tinker
```

## Files Created

### Models
- `app/Models/SubscriptionPlan.php`
- `app/Models/Product.php`
- `app/Models/Order.php`
- `app/Models/OrderItem.php`
- `app/Models/User.php` (updated)

### Controllers
- `app/Http/Controllers/BillingController.php`
- `app/Http/Controllers/WebhookController.php`
- `app/Http/Controllers/HomeController.php`

### Middleware
- `app/Http/Middleware/CheckSubscription.php`
- `app/Http/Middleware/CheckPhotoLimit.php`
- `app/Http/Middleware/CheckCollectionLimit.php`

### Views
- `resources/views/home.blade.php`
- `resources/views/about.blade.php`
- `resources/views/billing/pricing.blade.php`
- `resources/views/billing/checkout.blade.php`
- `resources/views/billing/connect-stripe.blade.php`
- `resources/views/legal/terms.blade.php`
- `resources/views/legal/privacy.blade.php`
- `resources/views/legal/sla.blade.php`

### Migrations
- `database/migrations/xxxx_create_subscription_plans_table.php`
- `database/migrations/xxxx_create_products_and_orders_tables.php`
- `database/migrations/xxxx_add_subscription_fields_to_users_table.php`
- Cashier migrations (published)

### Seeders
- `database/seeders/SubscriptionPlanSeeder.php`

### Documentation
- `PAYMENT_SETUP.md` - Detailed setup guide
- `PAYMENT_IMPLEMENTATION.md` - Implementation summary
- `QUICK_START.md` - This file

## Status Check

Run through this checklist to verify everything is working:

- [ ] Can access homepage at `/`
- [ ] Can see all three plans on `/pricing`
- [ ] Can click "Get Started" and see login/register
- [ ] Logged-in users can see subscription status
- [ ] Can navigate to checkout page
- [ ] Stripe Elements loads on checkout page
- [ ] Can complete test subscription
- [ ] User's plan updates after subscription
- [ ] Photo upload blocks at limit (test with free account)
- [ ] Collection creation blocks at limit (test with free account)
- [ ] Paid plan users see "Unlimited" instead of limits
- [ ] Can access billing portal
- [ ] Can cancel subscription (with grace period)
- [ ] Can resume cancelled subscription
- [ ] Stripe Connect page loads
- [ ] Legal pages are accessible

## Troubleshooting

### "Stripe key not configured"
- Check `.env` has `STRIPE_KEY` and `STRIPE_SECRET`
- Run `php artisan config:clear`

### "Subscription plan not found"
- Run seeder: `php artisan db:seed --class=SubscriptionPlanSeeder`
- Check plans exist: `App\Models\SubscriptionPlan::all()` in tinker

### Checkout page not loading Stripe
- Verify `STRIPE_KEY` starts with `pk_test_` or `pk_live_`
- Check browser console for errors
- Ensure internet connection (Stripe.js loads from CDN)

### Webhooks not working
- Use Stripe CLI for local testing: `stripe listen --forward-to localhost:8000/stripe/webhook`
- Check webhook signing secret in `.env`
- Review Laravel logs: `tail -f storage/logs/laravel.log`

### User has no plan
```php
// In tinker:
$user = App\Models\User::find(1);
$freePlan = App\Models\SubscriptionPlan::where('slug', 'free')->first();
$user->subscription_plan_id = $freePlan->id;
$user->save();
```

## Production Checklist

Before going live:

- [ ] Switch to live Stripe keys
- [ ] Update webhook endpoint to production URL
- [ ] Set `APP_ENV=production` in `.env`
- [ ] Enable HTTPS (required for Stripe)
- [ ] Set up queue worker: `php artisan queue:work`
- [ ] Configure email service for receipts
- [ ] Test subscription flow with real card
- [ ] Test webhook delivery
- [ ] Monitor logs for first few days
- [ ] Set up monitoring/alerting

## Support

If you encounter issues:
1. Check Laravel logs: `storage/logs/laravel.log`
2. Check Stripe Dashboard logs
3. Review `PAYMENT_SETUP.md` for detailed instructions
4. Test with Stripe test mode first

---

🎉 **You're ready to start testing your subscription system!**

Start by visiting `/pricing` and trying out the subscription flow with Stripe's test cards.
 