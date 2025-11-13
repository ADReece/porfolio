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
                'stripe_price_id' => null, // Set after creating in Stripe
                'stripe_product_id' => null, // Set after creating in Stripe
                'price' => 9.99,
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
                'stripe_price_id' => null, // Set after creating in Stripe
                'stripe_product_id' => null, // Set after creating in Stripe
                'price' => 14.99,
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

