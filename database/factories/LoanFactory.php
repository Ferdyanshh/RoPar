<?php

namespace Database\Factories;

use App\Enums\LoanStatus;
use App\Models\Loan;
use App\Models\RobotUnit;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Loan>
 */
class LoanFactory extends Factory
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
            'robot_unit_id' => RobotUnit::factory(),
            'loan_date' => CarbonImmutable::today(),
            'due_date' => CarbonImmutable::today()->addDays(5),
            'return_date' => null,
            'returned_by' => null,
            'late_days' => 0,
            'fine_amount' => 0,
            'status' => LoanStatus::Borrowed,
        ];
    }

    public function returned(int $lateDays = 0): static
    {
        $returnDate = CarbonImmutable::today();

        return $this->state(fn (array $attributes): array => [
            'loan_date' => $returnDate->subDays(5 + $lateDays),
            'due_date' => $returnDate->subDays($lateDays),
            'return_date' => $returnDate,
            'returned_by' => User::factory()->state(['role' => 'admin']),
            'late_days' => $lateDays,
            'fine_amount' => $lateDays * 5000,
            'status' => LoanStatus::Returned,
        ]);
    }
}
