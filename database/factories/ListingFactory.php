<?php

namespace Database\Factories;

use App\Models\Category;
use App\Models\Listing;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Listing>
 */
class ListingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'category_id' => Category::factory(),
            'title' => fake()->sentence(5),
            'detail' => fake()->paragraph(),
            'country' => fake()->country(),
            'state' => fake()->state(),
            'city' => fake()->city(),
            'area' => fake()->optional()->streetName(),
            'price' => fake()->randomFloat(2, 1, 50000),
        ];
    }
}
