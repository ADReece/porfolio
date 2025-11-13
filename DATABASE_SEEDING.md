# Database Seeding Guide

## Quick Commands

### Seed Everything
```bash
./vendor/bin/sail artisan db:seed
```

### Seed Specific Seeder
```bash
./vendor/bin/sail artisan db:seed --class=SubscriptionPlanSeeder
```

### Fresh Migration + Seed
```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

### Rollback & Re-seed
```bash
./vendor/bin/sail artisan migrate:fresh
./vendor/bin/sail artisan db:seed
```

## What Gets Seeded

### ✅ Subscription Plans (SubscriptionPlanSeeder)

Three plans are created:

1. **Free Plan** - $0/month
   - 100 photos
   - 5 collections
   - Basic features

2. **Photographer Plan** - $9.99/month or $99/year
   - Save 17% with annual billing
   - Unlimited photos & collections
   - Watermarking, selling, custom templates

3. **Videographer Plan** - $14.99/month or $139/year
   - Save 23% with annual billing
   - Everything in Photographer
   - Plus video upload & hosting

## Annual Pricing Structure

| Plan | Monthly | Annual | Savings |
|------|---------|--------|---------|
| Free | $0 | $0 | — |
| Photographer | $9.99 | $99.00 | 17% ($20.88/yr) |
| Videographer | $14.99 | $139.00 | 23% ($40.88/yr) |

## Stripe Configuration Required

After seeding, you need to:

1. **Create Products in Stripe Dashboard**
   - Go to https://dashboard.stripe.com/test/products
   - Create 2 products: "Photographer" and "Videographer"
   - Add pricing:
     - Photographer: $9.99/month recurring + $99/year recurring
     - Videographer: $14.99/month recurring + $139/year recurring

2. **Update Database with Stripe IDs**
   ```bash
   ./vendor/bin/sail artisan tinker
   ```
   ```php
   // Get your Price IDs from Stripe Dashboard
   $photographer = \App\Models\SubscriptionPlan::where('slug', 'photographer')->first();
   $photographer->stripe_price_id = 'price_xxxxx'; // Monthly
   $photographer->annual_stripe_price_id = 'price_yyyyy'; // Annual
   $photographer->stripe_product_id = 'prod_zzzzz';
   $photographer->save();

   $videographer = \App\Models\SubscriptionPlan::where('slug', 'videographer')->first();
   $videographer->stripe_price_id = 'price_xxxxx'; // Monthly
   $videographer->annual_stripe_price_id = 'price_yyyyy'; // Annual
   $videographer->stripe_product_id = 'prod_zzzzz';
   $videographer->save();
   ```

3. **Activate Stripe Customer Portal**
   - Visit: https://dashboard.stripe.com/test/settings/billing/portal
   - Click "Activate test link"
   - Configure branding and features

## Testing the Seeder

### Verify Plans Were Created
```bash
./vendor/bin/sail artisan tinker --execute="
\App\Models\SubscriptionPlan::all()->each(function(\$p) {
    echo \$p->name . ': $' . \$p->price . '/mo ($' . \$p->annual_price . '/yr)' . PHP_EOL;
});"
```

### Check Annual Discount Percentage
```bash
./vendor/bin/sail artisan tinker --execute="
\App\Models\SubscriptionPlan::where('slug', '!=', 'free')->get()->each(function(\$p) {
    echo \$p->name . ': ' . \$p->annual_discount_percent . '% off' . PHP_EOL;
});"
```

## Re-seeding

If you need to update the plans:

```bash
# Just re-run the seeder (uses updateOrCreate)
./vendor/bin/sail artisan db:seed --class=SubscriptionPlanSeeder
```

The seeder uses `updateOrCreate` so it's safe to run multiple times - it will update existing plans rather than creating duplicates.

## What's New

✅ **Annual Pricing Added**
- `annual_price` field now populated
- `annual_stripe_price_id` field ready for Stripe integration
- Discount percentages calculated automatically by the model

✅ **DatabaseSeeder Created**
- Orchestrates all seeders in correct order
- Provides helpful next-steps after seeding
- Easy to extend with more seeders

✅ **Better Discount Structure**
- Photographer: 17% savings on annual
- Videographer: 23% savings on annual
- Attractive pricing to encourage annual subscriptions

## Future Seeders

You can add more seeders to DatabaseSeeder.php:

```php
$this->call([
    SubscriptionPlanSeeder::class,
    // Add more seeders here as needed
    // UserSeeder::class,
    // CategorySeeder::class,
]);
```

---

**Ready to seed?** Just run `./vendor/bin/sail artisan db:seed` 🌱

