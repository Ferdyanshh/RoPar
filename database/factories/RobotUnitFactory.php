<?php

namespace Database\Factories;

use App\Enums\RobotUnitStatus;
use App\Models\Robot;
use App\Models\RobotUnit;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RobotUnit>
 */
class RobotUnitFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'robot_id' => Robot::factory(),
            'unit_code' => 'RBT-'.fake()->unique()->numerify('#####'),
            'status' => RobotUnitStatus::Available,
            'condition_note' => fake()->optional()->sentence(),
        ];
    }
}
