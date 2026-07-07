<?php

namespace Database\Factories;

use App\Models\Building;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UnitFactory extends Factory
{
    protected $model = Unit::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'project_id' => Project::factory(),
            'building_id' => Building::factory(),
            'floor_id' => Floor::factory(),
            'unit_type_id' => null,
            'unit_number' => strtoupper(fake()->bothify('??-###')),
            'size' => fake()->randomFloat(2, 500, 5000),
            'price' => fake()->randomFloat(2, 1000000, 50000000),
            'facing' => fake()->randomElement(['North', 'South', 'East', 'West', 'North-East', 'South-West']),
            'status' => 'available',
            'description' => fake()->optional()->sentence(),
            'meta' => null,
        ];
    }

    public function available(): static
    {
        return $this->state(fn () => ['status' => 'available']);
    }

    public function reserved(): static
    {
        return $this->state(fn () => ['status' => 'reserved']);
    }

    public function booked(): static
    {
        return $this->state(fn () => ['status' => 'booked']);
    }

    public function sold(): static
    {
        return $this->state(fn () => ['status' => 'sold']);
    }

    public function handovered(): static
    {
        return $this->state(fn () => ['status' => 'handovered']);
    }
}
