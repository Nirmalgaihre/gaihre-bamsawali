<?php

namespace Database\Factories;

use App\Models\ContentPage;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<ContentPage> */
class ContentPageFactory extends Factory
{
    public function definition(): array
    {
        return ['key' => fake()->unique()->slug(), 'title' => fake()->sentence(), 'content' => '<p>Page content.</p>', 'status' => true];
    }
}
