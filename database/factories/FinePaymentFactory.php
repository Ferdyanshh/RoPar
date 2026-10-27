<?php

namespace Database\Factories;

use App\Enums\FinePaymentStatus;
use App\Models\FinePayment;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<FinePayment>
 */
class FinePaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'loan_id' => Loan::factory()->returned(1),
            'amount' => 5000,
            'proof_path' => 'fine-payments/sample-receipt.pdf',
            'status' => FinePaymentStatus::Pending,
            'reviewed_by' => null,
            'reviewed_at' => null,
            'rejection_reason' => null,
        ];
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FinePaymentStatus::Paid,
            'reviewed_at' => now(),
        ]);
    }

    public function rejected(): static
    {
        return $this->state(fn (array $attributes): array => [
            'status' => FinePaymentStatus::Rejected,
            'reviewed_at' => now(),
            'rejection_reason' => fake()->sentence(),
        ]);
    }
}
