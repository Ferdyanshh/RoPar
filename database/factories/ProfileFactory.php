<?php

namespace Database\Factories;

use App\Models\Profile;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Profile>
 */
class ProfileFactory extends Factory
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
            'full_name' => fake()->name(),
            'phone' => fake()->numerify('08#############'),
            'address' => fake()->optional()->address(),
            'birth_date' => fake()->optional()->dateTimeBetween('-60 years', '-18 years')?->format('Y-m-d'),
        ];
    }
}
