<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $this->command->info('🌱 Starting database seeding...');

        // Seed subscription plans first (no dependencies)
        $this->command->info('📋 Seeding subscription plans...');
        $this->call(SubscriptionPlanSeeder::class);
        $this->command->info('✅ Subscription plans seeded successfully!');

        $this->command->info('');
        $this->command->info('🎉 Database seeding completed successfully!');
        $this->command->info('');
        $this->command->info('📝 Next steps:');
        $this->command->info('   1. Set up Stripe products and prices in your Stripe Dashboard');
        $this->command->info('   2. Update subscription_plans table with Stripe IDs');
        $this->command->info('   3. Configure Stripe Customer Portal at: https://dashboard.stripe.com/test/settings/billing/portal');
        $this->command->info('');
    }
}

