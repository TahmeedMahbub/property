<?php

namespace Database\Factories;

use App\Models\Company;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class CompanyFactory extends Factory
{
    protected $model = Company::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'name' => fake()->company(),
            'legal_name' => fake()->company() . ' Ltd.',
            'registration_number' => fake()->numerify('REG-######'),
            'tax_id' => fake()->numerify('TIN-##########'),
            'type' => fake()->randomElement(['real_estate', 'construction', 'trading', 'services']),
            'email' => fake()->companyEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'country' => 'Bangladesh',
            'currency' => 'BDT',
            'fiscal_year_start_month' => 7,
            'status' => 'active',
        ];
    }
}
