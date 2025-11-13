# Payment Testing Quick Reference Card

## 🚀 Quick Start (5 minutes)

### 1. Get Stripe Keys
```
1. Go to https://dashboard.stripe.com (Test mode ON)
2. Developers → API keys
3. Copy pk_test_... and sk_test_...
```

### 2. Add to .env
```bash
STRIPE_KEY=pk_test_YOUR_KEY
STRIPE_SECRET=sk_test_YOUR_KEY
```

### 3. Create Products in Stripe
- Photographer: $9.99/month → Copy Price ID
- Videographer: $14.99/month → Copy Price ID

### 4. Update Database
```bash
./vendor/bin/sail artisan tinker
```
```php
$p = App\Models\SubscriptionPlan::where('slug','photographer')->first();
$p->stripe_price_id = 'price_YOUR_ID';
$p->save();

$v = App\Models\SubscriptionPlan::where('slug','videographer')->first();
$v->stripe_price_id = 'price_YOUR_ID';
$v->save();
exit;
```

### 5. Clear Cache & Test
```bash
./vendor/bin/sail artisan config:clear
open http://localhost/pricing
```

---

## 🧪 Test Card Numbers

| Card | Result |
|------|--------|
| `4242 4242 4242 4242` | ✅ Success |
| `4000 0000 0000 0002` | ❌ Declined |
| `4000 0025 0000 3155` | 🔐 3D Secure |

**Expiry:** Any future date (12/30)  
**CVC:** Any 3 digits (123)  
**ZIP:** Any 5 digits (12345)

---

## 📍 Key URLs

| Page | URL |
|------|-----|
| Pricing | http://localhost/pricing |
| Admin | http://localhost/admin |
| Register | http://localhost/register |
| Stripe Connect | http://localhost/connect/stripe |

---

## ✅ Testing Checklist

### Monthly Subscription
- [ ] Visit /pricing
- [ ] Click "Subscribe Now" on Photographer
- [ ] Enter test card: 4242 4242 4242 4242
- [ ] Complete checkout
- [ ] Verify success message
- [ ] Check Stripe Dashboard for subscription

### Annual Subscription
- [ ] Toggle Monthly/Yearly
- [ ] Verify prices change (show discount)
- [ ] Subscribe with test card
- [ ] Verify $99.00/year in Stripe

### Feature Gates
- [ ] Free plan: Limited to 100 photos, 5 collections
- [ ] Photographer: Unlimited + selling features
- [ ] Videographer: Everything + video upload

### Admin Features
- [ ] Make self admin via tinker
- [ ] Visit /admin
- [ ] View user list
- [ ] Toggle friend override
- [ ] Set override expiry

### Subscription Management
- [ ] Cancel subscription
- [ ] Verify grace period
- [ ] Resume subscription
- [ ] Switch plans
- [ ] Access billing portal

---

## 🔧 Common Commands

### Check Status
```bash
./check-payment-ready.sh
```

### View Plans
```bash
./vendor/bin/sail artisan tinker --execute="
App\Models\SubscriptionPlan::all()->each(function(\$p){ 
  echo \$p->name . ' - ' . \$p->stripe_price_id . PHP_EOL; 
});"
```

### Make User Admin
```bash
./vendor/bin/sail artisan tinker --execute="
\$user = App\Models\User::where('email', 'YOUR@EMAIL.com')->first();
\$user->is_admin = true;
\$user->save();
echo 'Admin status granted';"
```

### Grant Friend Override
```bash
./vendor/bin/sail artisan tinker --execute="
\$user = App\Models\User::where('email', 'FRIEND@EMAIL.com')->first();
\$user->feature_override = true;
\$user->save();
echo 'Override enabled';"
```

### Clear All Cache
```bash
./vendor/bin/sail artisan optimize:clear
```

---

## 🐛 Quick Troubleshooting

**"Stripe key not configured"**
→ Add keys to .env, then run `./vendor/bin/sail artisan config:clear`

**Checkout fails silently**
→ Check browser console, verify pk_test_ key is correct

**Webhooks not working**
→ Use Stripe CLI: `stripe listen --forward-to localhost/stripe/webhook`

**Price ID mismatch**
→ Check database has correct Price IDs from Stripe Dashboard

**User stuck on Free**
→ Run seeder, assign free plan to users without one

---

## 📚 Full Documentation

- **Complete Guide**: `TESTING_PAYMENT_INTEGRATION.md`
- **Setup Instructions**: `PAYMENT_SETUP.md`
- **Implementation Details**: `PAYMENT_IMPLEMENTATION.md`
- **Quick Start**: `QUICK_START.md`

---

**Questions?** Check the full testing guide in `TESTING_PAYMENT_INTEGRATION.md`

