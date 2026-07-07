<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Company;
use App\Models\Floor;
use App\Models\Project;
use App\Models\Unit;
use App\Models\UnitType;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class PropertySeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            return;
        }

        $project = $company->projects()->first() ?? Project::factory()->create([
            'company_id' => $company->id,
            'name' => 'Skyline Residences',
            'slug' => 'skyline-residences',
            'type' => 'residential',
            'status' => 'active',
        ]);

        // Create unit types
        $unitTypes = collect([
            '1 BHK', '2 BHK', '3 BHK', 'Studio', 'Penthouse', 'Shop',
        ])->map(fn ($name) => UnitType::updateOrCreate(
            ['company_id' => $company->id, 'slug' => Str::slug($name)],
            ['uuid' => Str::uuid(), 'name' => $name, 'is_active' => true],
        ));

        // Create buildings
        $buildings = collect(['Tower A', 'Tower B'])->map(fn ($name) => Building::updateOrCreate(
            ['project_id' => $project->id, 'slug' => Str::slug($name)],
            [
                'uuid' => Str::uuid(),
                'company_id' => $company->id,
                'name' => $name,
                'total_floors' => 10,
                'total_units' => 40,
                'status' => 'under_construction',
            ],
        ));

        foreach ($buildings as $building) {
            for ($i = 1; $i <= 10; $i++) {
                $floor = Floor::updateOrCreate(
                    ['building_id' => $building->id, 'floor_number' => $i],
                    [
                        'uuid' => Str::uuid(),
                        'company_id' => $company->id,
                        'project_id' => $project->id,
                        'name' => "Floor $i",
                        'total_units' => 4,
                    ],
                );

                // 4 units per floor
                for ($u = 1; $u <= 4; $u++) {
                    $unitNumber = $building->slug === 'tower-a'
                        ? "A-{$i}0{$u}"
                        : "B-{$i}0{$u}";

                    Unit::updateOrCreate(
                        ['building_id' => $building->id, 'unit_number' => $unitNumber],
                        [
                            'uuid' => Str::uuid(),
                            'company_id' => $company->id,
                            'project_id' => $project->id,
                            'floor_id' => $floor->id,
                            'unit_type_id' => $unitTypes->random()->id,
                            'size' => fake()->randomFloat(2, 800, 2500),
                            'price' => fake()->randomFloat(2, 3000000, 15000000),
                            'facing' => fake()->randomElement(['North', 'South', 'East', 'West']),
                            'status' => fake()->randomElement(['available', 'available', 'available', 'reserved', 'booked']),
                        ],
                    );
                }
            }
        }
    }
}
