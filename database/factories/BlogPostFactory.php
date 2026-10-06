<?php

namespace Database\Factories;

use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BlogPost> */
class BlogPostFactory extends Factory
{
    public function definition(): array
    {
        return ['title' => fake()->sentence(), 'slug' => fake()->unique()->slug(), 'short_description' => fake()->sentence(), 'content' => '<p>Family history article.</p>', 'author_id' => User::factory(), 'status' => 'draft', 'published_at' => null];
    }
}
