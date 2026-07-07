<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\UnitType;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class UnitTypeFactory extends Factory
{
    protected $model = UnitType::class;

    public function definition(): array
    {
        $name = fake()->unique()->randomElement([
            '1 BHK', '2 BHK', '3 BHK', '4 BHK', '5 BHK',
            'Studio', 'Penthouse', 'Duplex', 'Shop',
            'Office', 'Garage', 'Store Room', 'Triplex',
            'Loft', 'Maisonette', 'Villa', 'Townhouse',
        ]);

        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'name' => $name,
            'slug' => Str::slug($name),
            'description' => fake()->optional()->sentence(),
            'is_active' => true,
        ];
    }
}
