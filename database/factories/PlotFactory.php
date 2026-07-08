<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Plot;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlotFactory extends Factory
{
    protected $model = Plot::class;

    public function definition(): array
    {
        $purchase = fake()->randomFloat(2, 1000000, 50000000);

        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'created_by' => null,
            'plot_code' => strtoupper(fake()->bothify('PLT-####')),
            'plot_name' => fake()->streetName() . ' Plot',
            'status' => fake()->randomElement(Plot::STATUSES),
            'division' => fake()->city(),
            'district' => fake()->city(),
            'upazila' => fake()->citySuffix(),
            'area' => fake()->streetName(),
            'address' => fake()->address(),
            'mouza' => fake()->word(),
            'jl_no' => (string) fake()->numberBetween(1, 500),
            'khatian_no' => (string) fake()->numberBetween(1, 5000),
            'dag_no' => (string) fake()->numberBetween(1, 9999),
            'land_size' => fake()->randomFloat(2, 1, 100),
            'land_unit' => fake()->randomElement(['katha', 'decimal', 'acre']),
            'purchase_price' => $purchase,
            'price_per_katha' => fake()->randomFloat(2, 500000, 5000000),
            'bayna_amount' => round($purchase * 0.1, 2),
            'registration_cost' => fake()->randomFloat(2, 50000, 500000),
            'mutation_cost' => fake()->randomFloat(2, 10000, 100000),
            'legal_cost' => fake()->randomFloat(2, 10000, 100000),
            'broker_cost' => fake()->randomFloat(2, 10000, 200000),
            'other_cost' => fake()->randomFloat(2, 0, 50000),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
