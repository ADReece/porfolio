#!/usr/bin/env bash

echo "╔════════════════════════════════════════════════════════════╗"
echo "║  Payment Integration Readiness Check                      ║"
echo "╚════════════════════════════════════════════════════════════╝"
echo ""

cd /Users/r.mathieson/Development/personal/portfolio

# Check if Sail is running
echo "🔍 Checking if Docker containers are running..."
if ./vendor/bin/sail ps | grep -q "Up"; then
    echo "✅ Docker containers are running"
else
    echo "❌ Docker containers are NOT running"
    echo "   Run: ./vendor/bin/sail up -d"
    exit 1
fi

echo ""
echo "🔍 Checking .env configuration..."

# Check Stripe keys
if grep -q "STRIPE_KEY=pk_test_" .env 2>/dev/null; then
    echo "✅ Stripe Publishable Key configured"
else
    echo "⚠️  Stripe Publishable Key NOT configured"
    echo "   Add STRIPE_KEY=pk_test_... to .env"
fi

if grep -q "STRIPE_SECRET=sk_test_" .env 2>/dev/null; then
    echo "✅ Stripe Secret Key configured"
else
    echo "⚠️  Stripe Secret Key NOT configured"
    echo "   Add STRIPE_SECRET=sk_test_... to .env"
fi

echo ""
echo "🔍 Checking database..."

# Check subscription plans
PLANS_COUNT=$(./vendor/bin/sail artisan tinker --execute="echo App\Models\SubscriptionPlan::count();" 2>/dev/null | tail -1)
if [ "$PLANS_COUNT" = "3" ]; then
    echo "✅ Subscription plans seeded (3 plans found)"
else
    echo "⚠️  Subscription plans not properly seeded"
    echo "   Run: ./vendor/bin/sail artisan db:seed --class=SubscriptionPlanSeeder"
fi

echo ""
echo "🔍 Checking Stripe Price IDs..."
./vendor/bin/sail artisan tinker --execute="
\$plans = App\Models\SubscriptionPlan::whereNotNull('stripe_price_id')->count();
if (\$plans >= 2) {
    echo '✅ Stripe Price IDs configured for paid plans' . PHP_EOL;
} else {
    echo '⚠️  Stripe Price IDs NOT configured' . PHP_EOL;
    echo '   Configure in Stripe Dashboard and update database via tinker' . PHP_EOL;
}
" 2>/dev/null

echo ""
echo "🔍 Checking routes..."
if ./vendor/bin/sail artisan route:list 2>/dev/null | grep -q "pricing"; then
    echo "✅ Pricing routes registered"
else
    echo "❌ Pricing routes NOT found"
fi

if ./vendor/bin/sail artisan route:list 2>/dev/null | grep -q "admin.dashboard"; then
    echo "✅ Admin routes registered"
else
    echo "❌ Admin routes NOT found"
fi

echo ""
echo "📋 Quick Access URLs:"
echo "   Homepage:    http://localhost"
echo "   Pricing:     http://localhost/pricing"
echo "   Admin:       http://localhost/admin"
echo "   Register:    http://localhost/register"
echo ""

echo "📖 Next Steps:"
echo "   1. Read TESTING_PAYMENT_INTEGRATION.md for full testing guide"
echo "   2. Set up Stripe test account and get API keys"
echo "   3. Create products in Stripe Dashboard"
echo "   4. Update Stripe Price IDs in database"
echo "   5. Start testing!"
echo ""

echo "🚀 Ready to test? Run: open http://localhost/pricing"

