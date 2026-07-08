<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\CompanyMembership;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        // Create Super Admin
        $superAdmin = User::updateOrCreate(
            ['email' => 'admin@g.c'],
            [
                'name' => 'Super Admin',
                'password' => '123456',
                'is_super_admin' => true,
                'email_verified_at' => now(),
                'status' => 'active',
            ],
        );

        // Create a demo company owner
        $owner = User::updateOrCreate(
            ['email' => 'owner@g.c'],
            [
                'name' => 'Demo Owner',
                'password' => '123456',
                'email_verified_at' => now(),
                'status' => 'active',
            ],
        );

        // Create demo company
        $company = Company::updateOrCreate(
            ['name' => 'Demo Property Co.'],
            [
                'legal_name' => 'Demo Property Company Ltd.',
                'type' => 'real_estate',
                'email' => 'info@demo-property.test',
                'city' => 'Dhaka',
                'country' => 'Bangladesh',
                'currency' => 'BDT',
                'fiscal_year_start_month' => 7,
                'status' => 'active',
            ],
        );

        // Assign owner to company
        $adminRole = Role::where('slug', 'admin')->whereNull('company_id')->first();

        CompanyMembership::updateOrCreate(
            ['company_id' => $company->id, 'user_id' => $owner->id],
            [
                'role_id' => $adminRole?->id,
                'is_owner' => true,
                'joined_at' => now(),
                'status' => 'active',
            ],
        );
    }
}
