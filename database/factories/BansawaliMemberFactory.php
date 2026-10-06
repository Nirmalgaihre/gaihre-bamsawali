<?php

namespace Database\Factories;

use App\Models\BansawaliMember;
use Illuminate\Database\Eloquent\Factories\Factory;

/** @extends Factory<BansawaliMember> */
class BansawaliMemberFactory extends Factory
{
    public function definition(): array
    {
        return ['name_ne' => 'गैह्रे '.fake()->unique()->numberBetween(1, 99999), 'name_en' => fake()->name().' Gaihre', 'parent_id' => null, 'generation' => 1, 'short_description' => fake()->sentence(), 'display_order' => 0, 'status' => true];
    }
}
