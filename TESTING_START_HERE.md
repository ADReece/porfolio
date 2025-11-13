# Payment Integration Testing - Getting Started

## 📋 Current Status

✅ **Completed:**
- Database migrations run
- Subscription plans seeded (Free, Photographer, Videographer)
- Payment routes configured
- Admin dashboard created
- Friend override system ready
- Annual pricing support added

⚠️ **Needs Configuration:**
- Stripe API keys (from Stripe Dashboard)
- Stripe Product Price IDs
- Webhook configuration (optional for local testing)

---

## 🚀 Start Testing in 3 Steps

### Step 1: Get Stripe Test Keys (5 min)

1. Go to https://dashboard.stripe.com
2. Toggle **Test mode** ON (top right)
3. Navigate to **Developers** > **API keys**
4. Copy both keys:
   - Publishable key (starts with `pk_test_`)
   - Secret key (starts with `sk_test_`)
5. Add to your `.env` file:
   ```env
   STRIPE_KEY=pk_test_YOUR_KEY
   STRIPE_SECRET=sk_test_YOUR_KEY
   ```

### Step 2: Create Products in Stripe (10 min)

1. In Stripe Dashboard, go to **Products**
2. Click **Add product**

**For Photographer Plan:**
- Name: Photographer Plan
- Monthly price: $9.99 (recurring)
- Annual price: $99.00 (recurring)
- Copy both Price IDs

**For Videographer Plan:**
- Name: Videographer Plan
- Monthly price: $14.99 (recurring)
- Annual price: $139.00 (recurring)
- Copy both Price IDs

### Step 3: Update Database & Test (5 min)

```bash
# Update database with Price IDs
./vendor/bin/sail artisan tinker
```

```php
// Photographer Plan
$p = App\Models\SubscriptionPlan::where('slug', 'photographer')->first();
$p->stripe_price_id = 'price_MONTHLY_ID';
$p->annual_stripe_price_id = 'price_ANNUAL_ID';
$p->stripe_product_id = 'prod_PRODUCT_ID';
$p->save();

// Videographer Plan
$v = App\Models\SubscriptionPlan::where('slug', 'videographer')->first();
$v->stripe_price_id = 'price_MONTHLY_ID';
$v->annual_stripe_price_id = 'price_ANNUAL_ID';
$v->stripe_product_id = 'prod_PRODUCT_ID';
$v->save();

exit;
```

```bash
# Clear cache
./vendor/bin/sail artisan config:clear

# Open pricing page
open http://localhost/pricing
```

---

## 🧪 Quick Test

1. **Register a test account:**
   - Visit: http://localhost/register
   - Email: test@example.com
   - Password: password

2. **Subscribe to a plan:**
   - Visit: http://localhost/pricing
   - Click "Subscribe Now" on Photographer
   - Use test card: `4242 4242 4242 4242`
   - Expiry: `12/30`, CVC: `123`

3. **Verify success:**
   - Should see "Subscription activated successfully!"
   - Check Stripe Dashboard for subscription

---

## 🛠️ Helper Scripts

We've created helper scripts to make testing easier:

### Check Readiness
```bash
./check-payment-ready.sh
```
Shows what's configured and what needs attention.

### Quick Commands
```bash
# Show all plans and their Stripe Price IDs
./test-payment.sh plans

# Make yourself admin
./test-payment.sh admin your@email.com

# Grant friend override
./test-payment.sh override friend@email.com

# View recent users
./test-payment.sh users

# View active subscriptions
./test-payment.sh subs

# Clear cache
./test-payment.sh cache

# Open pages
./test-payment.sh open pricing
./test-payment.sh open admin

# Show test card numbers
./test-payment.sh test-card
```

---

## 📚 Documentation

| Document | Purpose |
|----------|---------|
| **TESTING_PAYMENT_INTEGRATION.md** | Complete testing guide (10 steps) |
| **TESTING_QUICK_REF.md** | Quick reference card |
| **PAYMENT_SETUP.md** | Detailed setup instructions |
| **PAYMENT_IMPLEMENTATION.md** | Implementation summary |
| **QUICK_START.md** | Quick start checklist |

---

## 🎯 Test Scenarios

### Basic Flow
1. ✅ View pricing page
2. ✅ Toggle monthly/yearly
3. ✅ Subscribe with test card
4. ✅ Verify subscription in Stripe
5. ✅ Check feature access

### Advanced Testing
1. ✅ Cancel subscription (grace period)
2. ✅ Resume subscription
3. ✅ Switch plans
4. ✅ Billing portal access
5. ✅ Test feature limits (Free plan)
6. ✅ Grant friend override
7. ✅ Test admin dashboard

### Stripe Connect (Optional)
1. ✅ Connect Stripe account
2. ✅ Test marketplace payments
3. ✅ Disconnect account

---

## 💳 Test Cards

| Card Number | Result |
|------------|--------|
| `4242 4242 4242 4242` | ✅ Success |
| `4000 0000 0000 0002` | ❌ Declined |
| `4000 0025 0000 3155` | 🔐 Requires 3D Secure |
| `4000 0000 0000 9995` | 💸 Insufficient Funds |

All cards:
- Expiry: Any future date (e.g., `12/30`)
- CVC: Any 3 digits (e.g., `123`)
- ZIP: Any 5 digits (e.g., `12345`)

---

## 🐛 Common Issues

### "Stripe key not configured"
```bash
# Add keys to .env, then:
./vendor/bin/sail artisan config:clear
```

### Checkout page doesn't load
- Verify `STRIPE_KEY` starts with `pk_test_`
- Check browser console for errors
- Ensure .env file is loaded

### Subscription not created
- Check Laravel logs: `./vendor/bin/sail logs`
- Verify Price IDs match Stripe Dashboard
- Test with different card

### Webhooks not working
```bash
# Use Stripe CLI for local testing:
stripe listen --forward-to localhost/stripe/webhook
```

---

## ✅ Testing Checklist

Before going live, verify:

**Configuration:**
- [ ] Stripe keys in .env
- [ ] Price IDs in database
- [ ] Plans have correct pricing
- [ ] Webhooks configured

**Functionality:**
- [ ] Can register account
- [ ] Can view pricing page
- [ ] Monthly subscription works
- [ ] Annual subscription works
- [ ] Can cancel subscription
- [ ] Can resume subscription
- [ ] Can switch plans
- [ ] Billing portal accessible

**Features:**
- [ ] Free plan limits work
- [ ] Paid plan features unlock
- [ ] Friend override works
- [ ] Admin dashboard accessible
- [ ] Usage tracking correct

**Stripe Integration:**
- [ ] Subscriptions appear in Stripe
- [ ] Invoices generated
- [ ] Webhook events received
- [ ] Customer data synced

---

## 🚀 Next Steps

Once testing is complete:

1. **For Production:**
   - Switch to live Stripe keys
   - Create live products
   - Update webhook endpoint
   - Set `APP_ENV=production`

2. **Monitor:**
   - Set up Stripe webhook monitoring
   - Configure error tracking
   - Enable queue worker for webhooks

3. **Launch:**
   - Test with real card (small amount)
   - Monitor first subscriptions
   - Have support ready

---

## 📞 Support

**Documentation:**
- Full Testing Guide: `TESTING_PAYMENT_INTEGRATION.md`
- Quick Reference: `TESTING_QUICK_REF.md`

**External Resources:**
- Stripe Testing: https://stripe.com/docs/testing
- Laravel Cashier: https://laravel.com/docs/billing
- Stripe Dashboard: https://dashboard.stripe.com

**Helper Scripts:**
- `./check-payment-ready.sh` - Readiness check
- `./test-payment.sh` - Quick commands

---

**Ready to start?** Run: `./check-payment-ready.sh`

