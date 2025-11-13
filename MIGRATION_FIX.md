# Migration Fix - Duplicate Column Issue

## Problem
The custom migration `2025_11_13_150939_add_subscription_fields_to_users_table.php` was attempting to add columns that Laravel Cashier already adds in its migration `2019_05_03_000001_create_customer_columns.php`.

## Duplicate Columns
The following columns were being added twice:
- `stripe_id`
- `pm_type`
- `pm_last_four`
- `trial_ends_at`

## Solution
Updated the custom migration to **only** add the columns that Cashier doesn't provide:
- `subscription_plan_id` (foreign key to subscription_plans table)
- `stripe_connect_id` (for Stripe Connect marketplace payments)
- `stripe_connect_enabled` (boolean flag)

## Cashier Columns (Already Added)
These columns are handled by Cashier's migration and should NOT be added again:
- `stripe_id` - Stripe customer ID
- `pm_type` - Payment method type (card, etc.)
- `pm_last_four` - Last 4 digits of payment method
- `trial_ends_at` - Trial period end date

## Migration Order
1. `2019_05_03_000001_create_customer_columns.php` - Cashier adds Stripe fields
2. `2025_11_13_150939_add_subscription_fields_to_users_table.php` - Custom migration adds plan & Connect fields

## Running Migrations with Sail
```bash
# Roll back and re-run migrations
./vendor/bin/sail artisan migrate:fresh

# Or just run new migrations
./vendor/bin/sail artisan migrate

# Seed subscription plans
./vendor/bin/sail artisan db:seed --class=SubscriptionPlanSeeder
```

## Verification
After running migrations, the `users` table should have:
- ✅ `stripe_id` (from Cashier)
- ✅ `pm_type` (from Cashier)
- ✅ `pm_last_four` (from Cashier)
- ✅ `trial_ends_at` (from Cashier)
- ✅ `subscription_plan_id` (from custom migration)
- ✅ `stripe_connect_id` (from custom migration)
- ✅ `stripe_connect_enabled` (from custom migration)

## Fixed! ✅
The duplicate column issue has been resolved. Migrations should now run successfully with Sail.

