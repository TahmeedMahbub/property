<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Employee;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class EmployeeFactory extends Factory
{
    protected $model = Employee::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'user_id' => null,
            'membership_id' => null,
            'employee_id_number' => fake()->numerify('EMP-####'),
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'department' => fake()->randomElement(['Engineering', 'Sales', 'Marketing', 'Finance', 'Operations']),
            'designation' => fake()->jobTitle(),
            'date_of_birth' => fake()->date('Y-m-d', '-25 years'),
            'date_of_joining' => fake()->date('Y-m-d', '-2 years'),
            'salary' => fake()->randomFloat(2, 20000, 200000),
            'salary_type' => 'monthly',
            'status' => 'active',
        ];
    }
}
