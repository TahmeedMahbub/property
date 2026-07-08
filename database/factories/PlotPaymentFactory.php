<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\PlotPayment;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlotPaymentFactory extends Factory
{
    protected $model = PlotPayment::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'plot_id' => Plot::factory(),
            'created_by' => null,
            'payment_type' => fake()->randomElement(array_keys(PlotPayment::TYPES)),
            'amount' => fake()->randomFloat(2, 50000, 5000000),
            'payment_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'payment_method' => fake()->randomElement(['cash', 'cheque', 'bank_transfer', 'mobile_banking', 'other']),
            'reference_no' => strtoupper(fake()->bothify('REF-####')),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
