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
        $this->call(DemoAccountSeeder::class);

        $this->command->info('');
        $this->command->info('🎉 Database seeding completed successfully!');
    }
}

