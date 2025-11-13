<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $plans = [
            [
                'name' => 'Free',
                'slug' => 'free',
                'stripe_price_id' => null,
                'stripe_product_id' => null,
                'price' => 0.00,
                'annual_price' => 0.00,
                'annual_stripe_price_id' => null,
                'photo_limit' => 100,
                'collection_limit' => 5,
                'private_collections' => false,
                'watermarking' => false,
                'selling' => false,
                'video_upload' => false,
                'custom_templates' => false,
                'features' => [
                    'Up to 100 photos',
                    'Up to 5 collections',
                    'Public portfolio',
                    'Basic templates',
                    'Photo sharing',
                ],
                'active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Photographer',
                'slug' => 'photographer',
                'stripe_price_id' => null, // Set in Admin Settings
                'stripe_product_id' => null, // Set in Admin Settings
                'price' => 8.33, // £8.33/month
                'annual_price' => 83.00, // £83/year (save 17% vs £99.96/year)
                'annual_stripe_price_id' => null, // Set in Admin Settings
                'photo_limit' => null, // Unlimited
                'collection_limit' => null, // Unlimited
                'private_collections' => true,
                'watermarking' => true,
                'selling' => true,
                'video_upload' => false,
                'custom_templates' => true,
                'features' => [
                    'Unlimited photos',
                    'Unlimited collections',
                    'Private collections with password protection',
                    'Custom watermarking',
                    'Sell digital downloads',
                    'Sell physical prints & products',
                    'More portfolio templates',
                    'Customizable templates',
                    'Client galleries',
                    'Advanced sharing options',
                ],
                'active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Videographer',
                'slug' => 'videographer',
                'stripe_price_id' => null, // Set in Admin Settings
                'stripe_product_id' => null, // Set in Admin Settings
                'price' => 12.50, // £12.50/month
                'annual_price' => 125.00, // £125/year (save 17% vs £150/year)
                'annual_stripe_price_id' => null, // Set in Admin Settings
                'photo_limit' => null, // Unlimited
                'collection_limit' => null, // Unlimited
                'private_collections' => true,
                'watermarking' => true,
                'selling' => true,
                'video_upload' => true,
                'custom_templates' => true,
                'features' => [
                    'Everything in Photographer plan',
                    'Video upload and hosting',
                    'Video galleries',
                    'Sell video downloads',
                    'HD video streaming',
                    'Priority support',
                ],
                'active' => true,
                'sort_order' => 3,
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                $plan
            );
        }
    }
}

