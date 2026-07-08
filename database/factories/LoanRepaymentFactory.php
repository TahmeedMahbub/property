<?php

namespace Database\Factories;

use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class LoanRepaymentFactory extends Factory
{
    protected $model = LoanRepayment::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'loan_id' => Loan::factory(),
            'created_by' => null,
            'payment_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'principal_paid' => fake()->randomFloat(2, 10000, 200000),
            'interest_paid' => fake()->randomFloat(2, 1000, 50000),
            'penalty' => 0,
            'payment_method' => fake()->randomElement(['cash', 'cheque', 'bank_transfer', 'mobile_banking']),
            'reference_no' => strtoupper(fake()->bothify('TXN########')),
            'remarks' => fake()->optional()->sentence(),
        ];
    }
}
