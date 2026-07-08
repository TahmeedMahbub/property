<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Loan;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LoanFactory extends Factory
{
    protected $model = Loan::class;

    public function definition(): array
    {
        $start = fake()->dateTimeBetween('-2 years', '-2 months');

        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'project_id' => null,
            'created_by' => null,
            'lender_type' => fake()->randomElement(['bank', 'shareholder', 'director', 'third_party']),
            'lender_name' => fake()->company(),
            'reference_no' => strtoupper(fake()->bothify('LN-####-????')),
            'principal_amount' => fake()->randomFloat(2, 500000, 50000000),
            'interest_rate' => fake()->randomFloat(2, 6, 16),
            'interest_type' => fake()->randomElement(['flat', 'reducing']),
            'emi_amount' => null,
            'start_date' => $start,
            'end_date' => (clone $start)->modify('+3 years'),
            'repayment_frequency' => 'monthly',
            'collateral' => fake()->optional()->sentence(),
            'notes' => fake()->optional()->sentence(),
            'status' => 'active',
        ];
    }

    public function bank(): static
    {
        return $this->state(fn () => ['lender_type' => 'bank']);
    }

    public function closed(): static
    {
        return $this->state(fn () => ['status' => 'closed']);
    }
}
