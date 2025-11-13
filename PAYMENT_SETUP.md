# Payment & Subscription System Setup Guide

This guide will help you set up the complete payment and subscription system for PortfolioHub.

## Features Implemented

### 1. Subscription Tiers
- **Free Plan**: 100 photos, 5 collections, basic features
- **Photographer Plan ($9.99/mo)**: Unlimited photos/collections, watermarking, selling, private galleries
- **Videographer Plan ($14.99/mo)**: Everything in Photographer + video upload support

### 2. Payment Integration
- Laravel Cashier for subscription management
- Stripe for payment processing
- Stripe Connect for receiving payments from clients
- Automatic subscription billing and invoicing

### 3. Feature Gates
- Photo upload limits enforced via middleware
- Collection creation limits enforced via middleware
- Feature checks for watermarking, private collections, selling, etc.

### 4. Legal Pages
- Terms of Service
- Privacy Policy
- Service Level Agreement (SLA)

### 5. Views Created
- Modern homepage with pricing preview
- Full pricing page with plan comparison
- Checkout page with Stripe integration
- Stripe Connect setup page
- All legal pages

## Setup Instructions

### Step 1: Configure Stripe

1. Create a Stripe account at https://stripe.com
2. Get your API keys from the Stripe Dashboard (Developers > API keys)
3. Set up Stripe Connect:
   - Go to Connect > Settings
   - Get your Connect Client ID
   - Set redirect URI to: `https://yourdomain.com/connect/stripe/callback`

### Step 2: Update Environment Variables

Copy `.env.example` to `.env` and add your Stripe credentials:

```bash
STRIPE_KEY=pk_test_your_publishable_key
STRIPE_SECRET=sk_test_your_secret_key
STRIPE_WEBHOOK_SECRET=whsec_your_webhook_secret
STRIPE_CONNECT_CLIENT_ID=ca_your_connect_client_id
```

### Step 3: Create Stripe Products and Prices

In your Stripe Dashboard:

1. Go to Products > Add product
2. Create two products:
   - **Photographer Plan**: $9.99/month recurring
   - **Videographer Plan**: $14.99/month recurring
3. Copy the Price IDs for each product

### Step 4: Update Subscription Plans in Database

Update the subscription plans with your Stripe Price IDs:

```bash
php artisan tinker
```

```php
$photographer = App\Models\SubscriptionPlan::where('slug', 'photographer')->first();
$photographer->stripe_price_id = 'price_YOUR_PHOTOGRAPHER_PRICE_ID';
$photographer->stripe_product_id = 'prod_YOUR_PHOTOGRAPHER_PRODUCT_ID';
$photographer->save();

$videographer = App\Models\SubscriptionPlan::where('slug', 'videographer')->first();
$videographer->stripe_price_id = 'price_YOUR_VIDEOGRAPHER_PRICE_ID';
$videographer->stripe_product_id = 'prod_YOUR_VIDEOGRAPHER_PRODUCT_ID';
$videographer->save();
```

### Step 5: Set Up Stripe Webhooks

1. In Stripe Dashboard, go to Developers > Webhooks
2. Add endpoint: `https://yourdomain.com/stripe/webhook`
3. Select events to listen for:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `payment_intent.succeeded`
   - `payment_intent.failed`
4. Copy the webhook signing secret to your `.env` file

### Step 6: Configure Cashier

Publish Cashier config (if needed):

```bash
php artisan vendor:publish --tag="cashier-config"
```

Update `config/cashier.php` if you need custom settings.

### Step 7: Assign Default Plan to Existing Users

Run this to assign the free plan to existing users:

```bash
php artisan tinker
```

```php
$freePlan = App\Models\SubscriptionPlan::where('slug', 'free')->first();
App\Models\User::whereNull('subscription_plan_id')->update(['subscription_plan_id' => $freePlan->id]);
```

## Testing the System

### Test Subscription Flow

1. Visit `/pricing` to see all plans
2. Click "Subscribe Now" on a paid plan
3. Use Stripe test card: `4242 4242 4242 4242`
4. Expiry: Any future date
5. CVC: Any 3 digits
6. Verify subscription is created in your dashboard

### Test Feature Limits

1. Create a user with Free plan
2. Try to upload more than 100 photos - should be blocked
3. Try to create more than 5 collections - should be blocked
4. Try to access watermarking - should require upgrade

### Test Stripe Connect

1. Upgrade to Photographer or Videographer plan
2. Go to `/connect/stripe`
3. Connect your Stripe account
4. Create products from photos
5. Test purchasing (uses Stripe Connect for payouts)

## Middleware Usage

Apply middleware to routes that require feature checks:

```php
// Check if user can upload photos
Route::post('/upload', [PhotoController::class, 'upload'])
    ->middleware('photo.limit');

// Check if user can create collections
Route::post('/collections', [CollectionController::class, 'store'])
    ->middleware('collection.limit');

// Check if user has specific feature
Route::get('/watermark', [PhotoController::class, 'watermark'])
    ->middleware('subscription:watermarking');
```

## Model Methods

### User Model

```php
// Check if user has a feature
$user->hasFeature('watermarking');
$user->hasFeature('selling');
$user->hasFeature('video_upload');

// Check limits
$user->canUploadPhotos();
$user->canCreateCollections();
$user->getRemainingPhotos();
$user->getRemainingCollections();

// Subscription checks
$user->subscribed('default'); // Laravel Cashier method
$user->subscription('default')->onGracePeriod();
```

### SubscriptionPlan Model

```php
$plan->isFree();
$plan->isPhotographer();
$plan->isVideographer();
$plan->hasFeature('watermarking');
```

## Important Notes

### Stripe Connect Commission

The platform takes a 5% commission on sales made through Stripe Connect. This is configured in the `WebhookController` when processing payments and creating transfers.

To change the commission rate, update the `platform_fee` calculation in `WebhookController.php`.

### Email Notifications

Set up your mail driver in `.env` to send:
- Subscription confirmation emails
- Payment receipts
- Failed payment notifications

### Queue Configuration

For production, use a queue driver (Redis, database, etc.) instead of `sync`:

```bash
QUEUE_CONNECTION=database
php artisan queue:table
php artisan migrate
php artisan queue:work
```

### Cashier Billing Portal

Users can manage their subscriptions through Stripe's billing portal:
- Update payment methods
- View invoices
- Cancel subscriptions

Access via the `/billing/portal` route.

## Security Considerations

1. **Webhook Verification**: Cashier automatically verifies webhook signatures
2. **HTTPS Required**: Use HTTPS in production for Stripe integration
3. **Environment Variables**: Never commit `.env` file with real credentials
4. **Rate Limiting**: Consider adding rate limiting to checkout routes
5. **User Verification**: Require email verification before allowing subscriptions

## Troubleshooting

### Webhooks Not Working

- Verify webhook URL is correct in Stripe Dashboard
- Check webhook signing secret in `.env`
- Review Laravel logs for webhook errors
- Use Stripe CLI for local testing: `stripe listen --forward-to localhost:8000/stripe/webhook`

### Subscription Not Activating

- Check Stripe logs for failed payments
- Verify Price IDs are correct
- Ensure user has valid payment method
- Check Laravel logs for errors

### Feature Gates Not Working

- Verify user has `subscription_plan_id` set
- Check middleware is applied to routes
- Ensure subscription plan has features enabled

## Additional Features to Consider

1. **Annual Billing**: Offer discounted annual plans
2. **Trial Periods**: Add 14-day free trial for paid plans
3. **Promo Codes**: Implement coupon support via Stripe
4. **Usage Alerts**: Email users when approaching limits
5. **Analytics**: Track subscription metrics and conversions
6. **Referral Program**: Reward users for referrals
7. **Enterprise Plan**: Custom pricing for large teams

## Support

For issues or questions:
- Email: support@portfoliohub.com
- Documentation: Stripe Cashier docs
- Stripe Support: https://support.stripe.com

## License

This payment system is part of PortfolioHub and follows the same license terms.

