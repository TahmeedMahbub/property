<?php

namespace Database\Factories;

use App\Models\Project;
use App\Models\ProjectInvestor;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ProjectInvestorFactory extends Factory
{
    protected $model = ProjectInvestor::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'project_id' => Project::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'investment_amount' => fake()->randomFloat(2, 100000, 50000000),
            'investment_percentage' => fake()->randomFloat(4, 1, 30),
            'investment_type' => 'equity',
            'invested_at' => fake()->date(),
            'status' => 'active',
        ];
    }
}
