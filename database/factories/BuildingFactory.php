<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Company;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class BuildingFactory extends Factory
{
    protected $model = Building::class;

    public function definition(): array
    {
        $name = 'Building ' . fake()->randomLetter() . fake()->numberBetween(1, 20);

        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'project_id' => Project::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->paragraph(),
            'total_floors' => fake()->numberBetween(5, 25),
            'total_units' => fake()->numberBetween(20, 200),
            'address' => fake()->optional()->streetAddress(),
            'status' => fake()->randomElement(['planning', 'under_construction', 'completed']),
        ];
    }
}
