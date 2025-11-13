#!/usr/bin/env php
<?php

/**
 * Subscription System Setup Helper
 *
 * This script helps set up the subscription system quickly.
 * Run: php setup-subscriptions.php
 */

echo "\n╔══════════════════════════════════════════════════════════════╗\n";
echo "║  PortfolioHub Subscription System Setup Helper              ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

// Check if running from correct directory
if (!file_exists('artisan')) {
    echo "❌ Error: Please run this script from your project root directory.\n\n";
    exit(1);
}

// Load Laravel
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

echo "✅ Laravel loaded successfully\n\n";

// Step 1: Check database connection
echo "📊 Step 1: Checking database connection...\n";
try {
    DB::connection()->getPdo();
    echo "✅ Database connection successful\n\n";
} catch (\Exception $e) {
    echo "❌ Database connection failed: " . $e->getMessage() . "\n";
    echo "   Please check your .env database settings\n\n";
    exit(1);
}

// Step 2: Check for Stripe keys
echo "🔑 Step 2: Checking Stripe configuration...\n";
$stripeKey = env('STRIPE_KEY');
$stripeSecret = env('STRIPE_SECRET');

if (empty($stripeKey) || empty($stripeSecret)) {
    echo "⚠️  Stripe keys not configured\n";
    echo "   Please add the following to your .env file:\n";
    echo "   STRIPE_KEY=pk_test_your_key\n";
    echo "   STRIPE_SECRET=sk_test_your_key\n";
    echo "   STRIPE_WEBHOOK_SECRET=whsec_your_secret\n";
    echo "   STRIPE_CONNECT_CLIENT_ID=ca_your_client_id\n\n";
} else {
    echo "✅ Stripe keys found\n";
    echo "   Key: " . substr($stripeKey, 0, 20) . "...\n\n";
}

// Step 3: Run migrations
echo "🗄️  Step 3: Running migrations...\n";
Artisan::call('migrate', ['--force' => true]);
echo Artisan::output();

// Step 4: Seed subscription plans
echo "🌱 Step 4: Seeding subscription plans...\n";
try {
    Artisan::call('db:seed', ['--class' => 'SubscriptionPlanSeeder', '--force' => true]);
    echo "✅ Subscription plans seeded\n\n";
} catch (\Exception $e) {
    echo "❌ Error seeding plans: " . $e->getMessage() . "\n\n";
}

// Step 5: Show subscription plans
echo "📋 Step 5: Current subscription plans:\n";
echo "────────────────────────────────────────────────────────────\n";

$plans = App\Models\SubscriptionPlan::orderBy('sort_order')->get();

foreach ($plans as $plan) {
    echo "\n🎯 {$plan->name} - \${$plan->price}/month\n";
    echo "   Slug: {$plan->slug}\n";
    echo "   Photos: " . ($plan->photo_limit ? $plan->photo_limit : 'Unlimited') . "\n";
    echo "   Collections: " . ($plan->collection_limit ? $plan->collection_limit : 'Unlimited') . "\n";

    if ($plan->stripe_price_id) {
        echo "   ✅ Stripe Price ID: {$plan->stripe_price_id}\n";
    } else {
        echo "   ⚠️  Stripe Price ID: Not set\n";
    }
}

echo "\n────────────────────────────────────────────────────────────\n\n";

// Step 6: Check for users without plans
echo "👥 Step 6: Checking users...\n";
$usersWithoutPlan = App\Models\User::whereNull('subscription_plan_id')->count();
$totalUsers = App\Models\User::count();

echo "   Total users: {$totalUsers}\n";
echo "   Users without plan: {$usersWithoutPlan}\n";

if ($usersWithoutPlan > 0) {
    echo "\n   Would you like to assign the Free plan to all users without a plan? (y/n): ";
    $handle = fopen("php://stdin", "r");
    $line = fgets($handle);

    if (trim($line) === 'y' || trim($line) === 'Y') {
        $freePlan = App\Models\SubscriptionPlan::where('slug', 'free')->first();
        if ($freePlan) {
            $updated = App\Models\User::whereNull('subscription_plan_id')
                ->update(['subscription_plan_id' => $freePlan->id]);
            echo "   ✅ Assigned Free plan to {$updated} users\n";
        }
    }
    fclose($handle);
}

echo "\n";

// Step 7: Next steps
echo "📝 Next Steps:\n";
echo "────────────────────────────────────────────────────────────\n";

$nextSteps = [];

if (empty($stripeKey) || empty($stripeSecret)) {
    $nextSteps[] = "1. Add Stripe API keys to your .env file";
}

$missingPriceIds = $plans->where('slug', '!=', 'free')->where('stripe_price_id', null);
if ($missingPriceIds->count() > 0) {
    $nextSteps[] = "2. Create products in Stripe Dashboard and add Price IDs to database";
    $nextSteps[] = "   Run: php artisan tinker";
    $nextSteps[] = "   Then update plans with: \$plan->stripe_price_id = 'price_xxx'; \$plan->save();";
}

if (empty(env('STRIPE_WEBHOOK_SECRET'))) {
    $nextSteps[] = "3. Set up webhook in Stripe Dashboard";
    $nextSteps[] = "   URL: " . url('/stripe/webhook');
    $nextSteps[] = "   Add webhook secret to .env: STRIPE_WEBHOOK_SECRET=whsec_xxx";
}

if (empty(env('STRIPE_CONNECT_CLIENT_ID'))) {
    $nextSteps[] = "4. Enable Stripe Connect and add Client ID to .env";
}

$nextSteps[] = "5. Test the subscription flow at: " . url('/pricing');
$nextSteps[] = "6. Review setup documentation: PAYMENT_SETUP.md";

foreach ($nextSteps as $step) {
    echo "   {$step}\n";
}

echo "────────────────────────────────────────────────────────────\n\n";

// Step 8: Configuration summary
echo "📊 Configuration Summary:\n";
echo "────────────────────────────────────────────────────────────\n";
echo "   Subscription plans: " . $plans->count() . "\n";
echo "   Users with plans: " . ($totalUsers - $usersWithoutPlan) . "/{$totalUsers}\n";
echo "   Stripe configured: " . (empty($stripeKey) ? '❌' : '✅') . "\n";
echo "   Price IDs set: " . ($plans->where('slug', '!=', 'free')->where('stripe_price_id', '!=', null)->count()) . "/2\n";
echo "   Webhook configured: " . (empty(env('STRIPE_WEBHOOK_SECRET')) ? '❌' : '✅') . "\n";
echo "   Connect configured: " . (empty(env('STRIPE_CONNECT_CLIENT_ID')) ? '❌' : '✅') . "\n";
echo "─────────────────────────────────��──────────────────────────\n\n";

echo "🎉 Setup helper completed!\n";
echo "   Visit " . url('/pricing') . " to test the subscription system\n\n";

