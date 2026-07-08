<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\PlotOwner;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlotOwnerFactory extends Factory
{
    protected $model = PlotOwner::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'plot_id' => Plot::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'nid' => (string) fake()->numberBetween(1000000000, 9999999999),
            'address' => fake()->address(),
            'ownership_percentage' => fake()->randomFloat(2, 5, 100),
        ];
    }
}
