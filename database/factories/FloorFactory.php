<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class FloorFactory extends Factory
{
    protected $model = Floor::class;

    public function definition(): array
    {
        $floorNumber = fake()->numberBetween(1, 25);

        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'project_id' => Project::factory(),
            'building_id' => Building::factory(),
            'name' => 'Floor ' . $floorNumber,
            'floor_number' => $floorNumber,
            'description' => fake()->optional()->sentence(),
            'total_units' => fake()->numberBetween(2, 12),
        ];
    }
}
