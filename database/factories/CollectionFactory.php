<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Collection>
 */
class CollectionFactory extends Factory
{
    protected $model = Collection::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'name' => fake()->words(3, true),
            'status' => 'Draft',
            'event_date' => fake()->dateTimeBetween('-3 months', '+3 months'),
            'private' => false,
            'password' => null,
            'watermarked' => false,
            'hide_from_portfolio' => false,
        ];
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'private' => true,
            'password' => bcrypt('password123'),
        ]);
    }

    public function published(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'Published',
        ]);
    }
}
