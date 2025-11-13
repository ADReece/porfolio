# Payment Integration Testing Guide

## Prerequisites Checklist

Before testing, ensure you have:
- [ ] Stripe account created at https://stripe.com
- [ ] All migrations run (`./vendor/bin/sail artisan migrate`)
- [ ] Subscription plans seeded (`./vendor/bin/sail artisan db:seed --class=SubscriptionPlanSeeder`)
- [ ] Application running (`./vendor/bin/sail up -d`)

---

## Step 1: Set Up Stripe Test Account (15 minutes)

### 1.1 Get Your Stripe API Keys

1. Go to https://dashboard.stripe.com
2. Toggle "Test mode" ON (in the top right)
3. Navigate to **Developers** > **API keys**
4. Copy your keys:
   - **Publishable key** (starts with `pk_test_`)
   - **Secret key** (starts with `sk_test_`)

### 1.2 Create Products in Stripe

1. In Stripe Dashboard, go to **Products** > **Add product**

2. **Create Photographer Plan (Monthly)**:
   - Name: `Photographer Plan - Monthly`
   - Description: `Unlimited photos and collections with premium features`
   - Pricing:
     - Price: `$9.99`
     - Billing period: `Recurring - Monthly`
   - Click **Save product**
   - **Copy the Price ID** (starts with `price_`)

3. **Create Photographer Plan (Annual)**:
   - Click **Add another price** on the Photographer product
   - Price: `$99.00`
   - Billing period: `Recurring - Yearly`
   - Click **Save**
   - **Copy the Price ID** (starts with `price_`)

4. **Create Videographer Plan (Monthly)**:
   - Name: `Videographer Plan - Monthly`
   - Description: `Everything in Photographer plus video support`
   - Price: `$14.99`
   - Billing period: `Recurring - Monthly`
   - Click **Save product**
   - **Copy the Price ID**

5. **Create Videographer Plan (Annual)**:
   - Add another price to Videographer product
   - Price: `$139.00`
   - Billing period: `Recurring - Yearly`
   - Click **Save**
   - **Copy the Price ID**

### 1.3 Set Up Stripe Connect (For Marketplace Features)

1. Go to **Connect** > **Settings**
2. Enable **OAuth for Standard accounts**
3. Copy your **Connect Client ID** (starts with `ca_`)
4. Add redirect URI: `http://localhost/connect/stripe/callback`
5. Click **Save**

### 1.4 Configure Webhooks

1. Go to **Developers** > **Webhooks**
2. Click **Add endpoint**
3. Endpoint URL: `http://localhost/stripe/webhook`
   - For local testing, use Stripe CLI (see Step 3)
4. Select events to listen for:
   - `customer.subscription.created`
   - `customer.subscription.updated`
   - `customer.subscription.deleted`
   - `payment_intent.succeeded`
   - `payment_intent.payment_failed`
5. Click **Add endpoint**
6. **Copy the Signing secret** (starts with `whsec_`)

---

## Step 2: Configure Your Application (5 minutes)

### 2.1 Update .env File

```bash
cd /Users/r.mathieson/Development/personal/portfolio
cp .env.example .env  # if you haven't already
```

Add these values to your `.env`:

```env
STRIPE_KEY=pk_test_YOUR_PUBLISHABLE_KEY
STRIPE_SECRET=sk_test_YOUR_SECRET_KEY
STRIPE_WEBHOOK_SECRET=whsec_YOUR_WEBHOOK_SECRET
STRIPE_CONNECT_CLIENT_ID=ca_YOUR_CONNECT_CLIENT_ID
```

### 2.2 Update Database with Stripe Price IDs

Run this in your terminal:

```bash
./vendor/bin/sail artisan tinker
```

Then paste and run:

```php
// Update Photographer Plan
$photographer = App\Models\SubscriptionPlan::where('slug', 'photographer')->first();
$photographer->stripe_price_id = 'price_YOUR_MONTHLY_PHOTOGRAPHER_PRICE_ID';
$photographer->stripe_product_id = 'prod_YOUR_PHOTOGRAPHER_PRODUCT_ID';
$photographer->annual_stripe_price_id = 'price_YOUR_ANNUAL_PHOTOGRAPHER_PRICE_ID';
$photographer->save();

// Update Videographer Plan
$videographer = App\Models\SubscriptionPlan::where('slug', 'videographer')->first();
$videographer->stripe_price_id = 'price_YOUR_MONTHLY_VIDEOGRAPHER_PRICE_ID';
$videographer->stripe_product_id = 'prod_YOUR_VIDEOGRAPHER_PRODUCT_ID';
$videographer->annual_stripe_price_id = 'price_YOUR_ANNUAL_VIDEOGRAPHER_PRICE_ID';
$videographer->save();

// Verify
App\Models\SubscriptionPlan::all()->each(function($p){ 
    echo $p->name . ' - Monthly: ' . $p->stripe_price_id . ' - Annual: ' . $p->annual_stripe_price_id . PHP_EOL; 
});

exit;
```

### 2.3 Clear Cache

```bash
./vendor/bin/sail artisan config:clear
./vendor/bin/sail artisan cache:clear
./vendor/bin/sail artisan view:clear
```

---

## Step 3: Set Up Local Webhook Testing (Optional but Recommended)

### 3.1 Install Stripe CLI

```bash
brew install stripe/stripe-cli/stripe
```

### 3.2 Login to Stripe CLI

```bash
stripe login
```

### 3.3 Forward Webhooks to Local

In a separate terminal window:

```bash
stripe listen --forward-to localhost/stripe/webhook
```

This will give you a webhook signing secret. **Copy it** and update your `.env`:

```env
STRIPE_WEBHOOK_SECRET=whsec_THE_SECRET_FROM_STRIPE_CLI
```

Then clear config again:

```bash
./vendor/bin/sail artisan config:clear
```

---

## Step 4: Test Basic Subscription Flow (10 minutes)

### 4.1 Create a Test User

1. Visit http://localhost/register
2. Create a new account:
   - Name: `Test User`
   - Email: `test@example.com`
   - Password: `password`

### 4.2 Test the Pricing Page

1. Visit http://localhost/pricing
2. Verify you see:
   - ✅ Free Plan ($0/month)
   - ✅ Photographer Plan ($9.99/month)
   - ✅ Videographer Plan ($14.99/month)
3. Click the **Monthly/Yearly** toggle
4. Verify prices change:
   - ✅ Photographer Plan ($99.00/year) with discount badge
   - ✅ Videographer Plan ($139.00/year) with discount badge

### 4.3 Test Monthly Subscription

1. Click **Subscribe Now** on the Photographer plan (monthly)
2. You should be redirected to `/checkout/{plan}?interval=month`
3. Verify the checkout page shows:
   - ✅ Plan name and features
   - ✅ Price: $9.99/mo
   - ✅ Stripe card input form

4. Enter test card details:
   - **Card number**: `4242 4242 4242 4242`
   - **Expiry**: Any future date (e.g., `12/30`)
   - **CVC**: Any 3 digits (e.g., `123`)
   - **ZIP**: Any 5 digits (e.g., `12345`)

5. Click **Subscribe Now**

6. Expected result:
   - ✅ Redirected to dashboard
   - ✅ Success message: "Subscription activated successfully!"
   - ✅ Can see subscription in Stripe Dashboard under Subscriptions

### 4.4 Verify Subscription Status

1. Go to http://localhost/pricing
2. Verify:
   - ✅ Your current plan shows "Current Plan: Photographer"
   - ✅ "Current Plan" button is disabled
   - ✅ Usage stats show:
     - Photos Used: 0 / Unlimited
     - Collections Used: 0 / Unlimited

---

## Step 5: Test Annual Subscription (5 minutes)

### 5.1 Cancel Current Subscription

1. On the pricing page, click **Cancel Subscription**
2. Confirm cancellation
3. Wait for cancellation to process (check grace period status)

Or create a new test user to test clean.

### 5.2 Subscribe to Annual Plan

1. Visit http://localhost/pricing
2. Toggle to **Yearly**
3. Click **Subscribe Now** on Photographer plan
4. Verify checkout shows `$99.00/yr`
5. Use test card: `4242 4242 4242 4242`
6. Complete subscription
7. Verify in Stripe Dashboard:
   - ✅ Subscription shows yearly interval
   - ✅ Amount is $99.00

---

## Step 6: Test Feature Gates (10 minutes)

### 6.1 Test Free Plan Limits

1. Create a new user or downgrade to Free plan
2. Try to upload photos:
   - ✅ Should work up to 100 photos
   - ✅ 101st photo should be blocked with error message
3. Try to create collections:
   - ✅ Should work up to 5 collections
   - ✅ 6th collection should be blocked

### 6.2 Test Feature Access

**As Free User:**
- Try to access watermarking: ❌ Should show upgrade prompt
- Try to access private collections: ❌ Should show upgrade prompt
- Try to access Stripe Connect: ❌ Should show upgrade prompt

**As Photographer User:**
- Access watermarking: ✅ Should work
- Create private collections: ✅ Should work
- Connect Stripe: ✅ Should work
- Upload video: ❌ Should not have option

**As Videographer User:**
- All features: ✅ Should work including video upload

---

## Step 7: Test Friend Override (5 minutes)

### 7.1 Make Yourself Admin

```bash
./vendor/bin/sail artisan tinker
```

```php
$user = App\Models\User::where('email', 'YOUR_EMAIL')->first();
$user->is_admin = true;
$user->save();
exit;
```

### 7.2 Grant Friend Override

1. Visit http://localhost/admin
2. Click **Users**
3. Find a user (or create a test friend account)
4. Click **Enable Override**
5. Optionally set an expiry date

### 7.3 Test Override Behavior

Log in as the friend account:
- ✅ All features unlocked regardless of plan
- ✅ Unlimited photo uploads
- ✅ Unlimited collections
- ✅ Can access watermarking, selling, etc.

---

## Step 8: Test Subscription Management (10 minutes)

### 8.1 Test Billing Portal

1. As a subscribed user, go to http://localhost/pricing
2. Click **Manage Billing**
3. You should be redirected to Stripe's Customer Portal
4. Verify you can:
   - ✅ Update payment method
   - ✅ View invoices
   - ✅ Cancel subscription

### 8.2 Test Subscription Cancellation

1. Click **Cancel Subscription** on pricing page
2. Confirm cancellation
3. Verify:
   - ✅ Subscription moves to "grace period"
   - ✅ Can still access paid features until period ends
   - ✅ See "Resume Subscription" button

### 8.3 Test Subscription Resumption

1. Click **Resume Subscription**
2. Verify:
   - ✅ Subscription reactivated
   - ✅ Cancel button appears again

### 8.4 Test Plan Switching

1. Visit http://localhost/pricing
2. Click **Subscribe Now** on a different plan
3. Verify:
   - ✅ Plan switches immediately
   - ✅ Prorated charge/credit applied
   - ✅ New plan features available

---

## Step 9: Test Stripe Connect (Optional - 15 minutes)

### 9.1 Connect Stripe Account

1. Upgrade to Photographer or Videographer plan
2. Visit http://localhost/connect/stripe
3. Click **Connect with Stripe**
4. Complete OAuth flow (use test account)
5. Verify:
   - ✅ Shows "Stripe Connected" success message
   - ✅ Can see disconnect button

### 9.2 Test Disconnect

1. Click **Disconnect Stripe Account**
2. Verify account is disconnected

---

## Step 10: Test Admin Dashboard (5 minutes)

### 10.1 View Admin Dashboard

1. Visit http://localhost/admin
2. Verify you see:
   - ✅ Total Users count
   - ✅ New Users (30d)
   - ✅ Active Subscribers
   - ✅ Orders count
   - ✅ GMV (Gross Merchandise Value)

### 10.2 Manage Users

1. Click **Users**
2. Test search functionality
3. Toggle override for a user
4. Set override expiry date
5. Toggle admin status

### 10.3 View Subscriptions

1. Click **Subscriptions**
2. Verify you see list of all subscriptions
3. Check user emails, status, and dates

### 10.4 View Orders

1. Click **Orders** (when you have orders)
2. Verify order listing and totals

---

## Common Test Cards

Use these Stripe test cards for different scenarios:

| Card Number | Scenario |
|------------|----------|
| `4242 4242 4242 4242` | Successful payment |
| `4000 0000 0000 0002` | Card declined |
| `4000 0025 0000 3155` | Requires authentication (3D Secure) |
| `4000 0000 0000 9995` | Insufficient funds |
| `4000 0000 0000 0341` | Charge succeeds, but later disputed |

---

## Verification Checklist

After testing, verify:

### Database
- [ ] Users have `subscription_plan_id` set
- [ ] Cashier `subscriptions` table has entries
- [ ] `subscription_items` table populated
- [ ] Plans have correct Stripe Price IDs

### Stripe Dashboard
- [ ] Customers created in Test mode
- [ ] Subscriptions appear and are active
- [ ] Invoices generated
- [ ] Webhook events delivered (check Developers > Events)

### Application
- [ ] Feature gates work correctly
- [ ] Usage limits enforced
- [ ] Override system works
- [ ] Admin dashboard accessible
- [ ] Navigation shows correct links

---

## Troubleshooting

### "Stripe key not configured" error
```bash
# Check .env has keys
cat .env | grep STRIPE

# Clear config cache
./vendor/bin/sail artisan config:clear
```

### Checkout fails silently
- Check browser console for JavaScript errors
- Verify Stripe Publishable Key is correct (starts with `pk_test_`)
- Check Laravel logs: `./vendor/bin/sail logs`

### Webhooks not firing
- Use Stripe CLI: `stripe listen --forward-to localhost/stripe/webhook`
- Check webhook signing secret in `.env`
- Review Laravel logs for webhook errors

### Price ID mismatch
```bash
./vendor/bin/sail artisan tinker
```
```php
// Check current Price IDs
App\Models\SubscriptionPlan::all()->each(function($p){ 
    echo $p->name . ': ' . $p->stripe_price_id . PHP_EOL; 
});
```

### User stuck on Free plan
```bash
./vendor/bin/sail artisan tinker
```
```php
// Assign Free plan to users without one
$free = App\Models\SubscriptionPlan::where('slug', 'free')->first();
App\Models\User::whereNull('subscription_plan_id')->update(['subscription_plan_id' => $free->id]);
```

---

## Next Steps

Once testing is complete:

1. **Production Setup**:
   - Switch to live Stripe keys
   - Update webhook endpoint to production URL
   - Create live products in Stripe
   - Set `APP_ENV=production` in `.env`

2. **Monitoring**:
   - Set up Stripe webhook monitoring
   - Configure error tracking (Sentry, Bugsnag)
   - Enable Laravel queue worker for async webhook processing

3. **Launch**:
   - Test with real card (small amount)
   - Monitor first few subscriptions closely
   - Have cancellation/refund policy ready

---

## Support Resources

- **Stripe Testing**: https://stripe.com/docs/testing
- **Cashier Docs**: https://laravel.com/docs/billing
- **Your Setup Guide**: `PAYMENT_SETUP.md`
- **Implementation Summary**: `PAYMENT_IMPLEMENTATION.md`

---

**Ready to test?** Start with Step 1 and work through each section. Good luck! 🚀

