<?php

namespace Database\Factories;

use App\Models\Photo;
use App\Models\Set;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Photo>
 */
class PhotoFactory extends Factory
{
    protected $model = Photo::class;

    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'set_id' => Set::factory(),
            'url' => 's3://portfolio-bucket/photos/' . fake()->md5() . '.jpg',
            'thumbnail_url' => 's3://portfolio-bucket/thumbnails/' . fake()->md5() . '.jpg',
            'watermarked_url' => 's3://portfolio-bucket/watermarked/' . fake()->md5() . '.jpg',
            'caption' => fake()->sentence(),
            'description' => fake()->paragraph(),
            'is_private' => false,
            'original_filename' => fake()->word() . '.jpg',
            'file_size' => fake()->numberBetween(500000, 5000000),
            'width' => 1920,
            'height' => 1080,
        ];
    }

    public function private(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_private' => true,
        ]);
    }
}
