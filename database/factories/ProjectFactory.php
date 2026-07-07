<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectFactory extends Factory
{
    protected $model = Project::class;

    public function definition(): array
    {
        $name = fake()->words(3, true) . ' Project';

        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->paragraph(),
            'type' => fake()->randomElement(['residential', 'commercial', 'mixed_use', 'land']),
            'budget' => fake()->randomFloat(2, 1000000, 100000000),
            'start_date' => fake()->dateTimeBetween('-1 year', 'now'),
            'end_date' => fake()->dateTimeBetween('+1 year', '+5 years'),
            'location' => fake()->city(),
            'address' => fake()->streetAddress(),
            'city' => fake()->city(),
            'status' => fake()->randomElement(['planning', 'active', 'on_hold']),
        ];
    }
}
