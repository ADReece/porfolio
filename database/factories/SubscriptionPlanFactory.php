<?php

namespace Database\Factories;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\SubscriptionPlan>
 */
class SubscriptionPlanFactory extends Factory
{
    protected $model = SubscriptionPlan::class;

    public function definition(): array
    {
        return [
            'name' => fake()->words(2, true),
            'slug' => fake()->slug(),
            'price' => fake()->numberBetween(9, 99),
            'annual_price' => fake()->numberBetween(99, 999),
            'annual_discount_percent' => 15,
            'photo_limit' => fake()->numberBetween(100, 1000),
            'collection_limit' => fake()->numberBetween(5, 50),
            'private_collections' => true,
            'watermarking' => true,
            'selling' => true,
            'video_upload' => false,
            'custom_templates' => false,
            'features' => [],
            'active' => true,
            'sort_order' => 1,
            'recommended' => false,
        ];
    }

    public function free(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Free',
            'slug' => 'free',
            'photo_limit' => 50,
            'collection_limit' => 3,
            'private_collections' => false,
            'watermarking' => false,
            'selling' => false,
            'video_upload' => false,
        ]);
    }

    public function unlimited(): static
    {
        return $this->state(fn (array $attributes) => [
            'name' => 'Pro',
            'slug' => 'pro',
            'photo_limit' => null,
            'collection_limit' => null,
            'private_collections' => true,
            'watermarking' => true,
            'selling' => true,
            'video_upload' => true,
        ]);
    }
}
