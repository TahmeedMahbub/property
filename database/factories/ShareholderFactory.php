<?php

namespace Database\Factories;

use App\Models\Company;
use App\Models\Shareholder;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class ShareholderFactory extends Factory
{
    protected $model = Shareholder::class;

    public function definition(): array
    {
        return [
            'uuid' => Str::uuid(),
            'company_id' => Company::factory(),
            'user_id' => null,
            'name' => fake()->name(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'share_percentage' => fake()->randomFloat(4, 1, 50),
            'share_amount' => fake()->randomFloat(2, 100000, 10000000),
            'shares_owned' => 0,
            'ownership_percentage' => 0,
            'share_type' => 'common',
            'acquired_at' => fake()->date(),
            'status' => 'active',
        ];
    }
}
