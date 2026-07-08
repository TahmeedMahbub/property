<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        // Platform-level system roles (company_id = null)
        $admin = Role::updateOrCreate(
            ['slug' => 'admin', 'company_id' => null],
            ['name' => 'Admin', 'is_system' => true],
        );

        $manager = Role::updateOrCreate(
            ['slug' => 'manager', 'company_id' => null],
            ['name' => 'Manager', 'is_system' => true],
        );

        $member = Role::updateOrCreate(
            ['slug' => 'member', 'company_id' => null],
            ['name' => 'Member', 'is_system' => true],
        );

        $viewer = Role::updateOrCreate(
            ['slug' => 'viewer', 'company_id' => null],
            ['name' => 'Viewer', 'is_system' => true],
        );

        // Assign all permissions to admin
        $allPermissions = Permission::pluck('id');
        $admin->permissions()->sync($allPermissions);

        // Manager gets everything except delete and settings.manage
        $managerPermissions = Permission::whereNotIn('slug', [
            'company.delete',
            'settings.manage',
        ])->pluck('id');
        $manager->permissions()->sync($managerPermissions);

        // Member gets view + create/upload
        $memberPermissions = Permission::where('slug', 'like', '%.view')
            ->orWhereIn('slug', ['projects.create', 'documents.upload', 'properties.manage', 'plots.manage'])
            ->pluck('id');
        $member->permissions()->sync($memberPermissions);

        // Viewer gets view only
        $viewerPermissions = Permission::where('slug', 'like', '%.view')->pluck('id');
        $viewer->permissions()->sync($viewerPermissions);
    }
}
