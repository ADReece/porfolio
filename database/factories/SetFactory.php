<?php

namespace Database\Factories;

use App\Models\Collection;
use App\Models\Set;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Set>
 */
class SetFactory extends Factory
{
    protected $model = Set::class;

    public function definition(): array
    {
        return [
            'collection_id' => Collection::factory(),
            'name' => fake()->words(2, true),
            'hide_from_portfolio' => false,
        ];
    }
}
