<?php

namespace Database\Factories;

use App\Models\Plot;
use App\Models\PlotSeller;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PlotSellerFactory extends Factory
{
    protected $model = PlotSeller::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'plot_id' => Plot::factory(),
            'name' => fake()->name(),
            'phone' => fake()->phoneNumber(),
            'nid' => (string) fake()->numberBetween(1000000000, 9999999999),
            'address' => fake()->address(),
        ];
    }
}
