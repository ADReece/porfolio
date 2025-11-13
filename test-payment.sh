#!/usr/bin/env bash

# Payment Testing Helper Script
# Quick access to common testing commands

case "$1" in
  "check")
    echo "🔍 Checking payment system readiness..."
    /Users/r.mathieson/Development/personal/portfolio/check-payment-ready.sh
    ;;

  "plans")
    echo "📋 Subscription Plans:"
    cd /Users/r.mathieson/Development/personal/portfolio
    ./vendor/bin/sail artisan tinker --execute="
    App\Models\SubscriptionPlan::all()->each(function(\$p){
      echo '- ' . \$p->name . ' (\$' . \$p->price . '/mo)' . PHP_EOL;
      echo '  Monthly Price ID: ' . (\$p->stripe_price_id ?: 'NOT SET') . PHP_EOL;
      echo '  Annual Price ID: ' . (\$p->annual_stripe_price_id ?: 'NOT SET') . PHP_EOL;
      echo PHP_EOL;
    });"
    ;;

  "admin")
    if [ -z "$2" ]; then
      echo "Usage: $0 admin EMAIL"
      echo "Example: $0 admin test@example.com"
      exit 1
    fi
    echo "👑 Making $2 an admin..."
    cd /Users/r.mathieson/Development/personal/portfolio
    ./vendor/bin/sail artisan tinker --execute="
    \$user = App\Models\User::where('email', '$2')->first();
    if (\$user) {
      \$user->is_admin = true;
      \$user->save();
      echo 'Admin status granted to ' . \$user->email . PHP_EOL;
    } else {
      echo 'User not found!' . PHP_EOL;
    }"
    ;;

  "override")
    if [ -z "$2" ]; then
      echo "Usage: $0 override EMAIL [EXPIRES_DATE]"
      echo "Example: $0 override friend@example.com 2025-12-31"
      exit 1
    fi
    echo "✨ Granting feature override to $2..."
    cd /Users/r.mathieson/Development/personal/portfolio
    if [ -z "$3" ]; then
      ./vendor/bin/sail artisan tinker --execute="
      \$user = App\Models\User::where('email', '$2')->first();
      if (\$user) {
        \$user->feature_override = true;
        \$user->save();
        echo 'Feature override granted (no expiry)' . PHP_EOL;
      } else {
        echo 'User not found!' . PHP_EOL;
      }"
    else
      ./vendor/bin/sail artisan tinker --execute="
      \$user = App\Models\User::where('email', '$2')->first();
      if (\$user) {
        \$user->feature_override = true;
        \$user->feature_override_expires_at = '$3';
        \$user->save();
        echo 'Feature override granted (expires $3)' . PHP_EOL;
      } else {
        echo 'User not found!' . PHP_EOL;
      }"
    fi
    ;;

  "users")
    echo "👥 Recent Users:"
    cd /Users/r.mathieson/Development/personal/portfolio
    ./vendor/bin/sail artisan tinker --execute="
    App\Models\User::latest()->take(10)->get()->each(function(\$u){
      echo '- ' . \$u->email . ' | Plan: ' . (\$u->subscriptionPlan->name ?? 'None');
      if (\$u->is_admin) echo ' | ADMIN';
      if (\$u->feature_override) echo ' | OVERRIDE';
      echo PHP_EOL;
    });"
    ;;

  "subs")
    echo "💳 Active Subscriptions:"
    cd /Users/r.mathieson/Development/personal/portfolio
    ./vendor/bin/sail artisan tinker --execute="
    \$count = DB::table('subscriptions')->count();
    echo 'Total: ' . \$count . PHP_EOL;
    DB::table('subscriptions')
      ->join('users', 'subscriptions.user_id', '=', 'users.id')
      ->select('users.email', 'subscriptions.stripe_status', 'subscriptions.stripe_price')
      ->take(10)
      ->get()
      ->each(function(\$s){
        echo '- ' . \$s->email . ' | ' . \$s->stripe_status . ' | ' . \$s->stripe_price . PHP_EOL;
      });"
    ;;

  "cache")
    echo "🗑️  Clearing cache..."
    cd /Users/r.mathieson/Development/personal/portfolio
    ./vendor/bin/sail artisan optimize:clear
    echo "✅ Cache cleared!"
    ;;

  "open")
    case "$2" in
      "pricing"|"p")
        open http://localhost/pricing
        ;;
      "admin"|"a")
        open http://localhost/admin
        ;;
      "register"|"r")
        open http://localhost/register
        ;;
      *)
        open http://localhost
        ;;
    esac
    ;;

  "test-card")
    echo "💳 Test Card Numbers:"
    echo ""
    echo "Success:         4242 4242 4242 4242"
    echo "Declined:        4000 0000 0000 0002"
    echo "3D Secure:       4000 0025 0000 3155"
    echo ""
    echo "Expiry: Any future date (e.g., 12/30)"
    echo "CVC: Any 3 digits (e.g., 123)"
    echo "ZIP: Any 5 digits (e.g., 12345)"
    ;;

  *)
    echo "Payment Testing Helper"
    echo ""
    echo "Usage: $0 COMMAND [OPTIONS]"
    echo ""
    echo "Commands:"
    echo "  check              Check payment system readiness"
    echo "  plans              Show subscription plans and Price IDs"
    echo "  admin EMAIL        Make user an admin"
    echo "  override EMAIL     Grant feature override to user"
    echo "  users              List recent users"
    echo "  subs               List active subscriptions"
    echo "  cache              Clear all cache"
    echo "  open [page]        Open page in browser (pricing, admin, register)"
    echo "  test-card          Show test card numbers"
    echo ""
    echo "Examples:"
    echo "  $0 check"
    echo "  $0 admin test@example.com"
    echo "  $0 override friend@example.com 2025-12-31"
    echo "  $0 open pricing"
    echo ""
    exit 1
    ;;
esac

