<?php

namespace Database\Factories;

use App\Models\SubjectEntry;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<SubjectEntry> */
class SubjectEntryFactory extends Factory
{
    public function definition(): array
    {
        return ['title' => fake()->sentence(3), 'type' => 'topic', 'location' => null, 'description' => fake()->paragraph(), 'additional_notes' => null, 'parent_id' => null, 'display_order' => 0, 'status' => true];
    }
}
