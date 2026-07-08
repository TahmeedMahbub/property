<?php

namespace Database\Seeders;

use App\Models\Permission;
use Illuminate\Database\Seeder;

class PermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // Company
            ['name' => 'View Company', 'slug' => 'company.view', 'group' => 'company'],
            ['name' => 'Update Company', 'slug' => 'company.update', 'group' => 'company'],
            ['name' => 'Delete Company', 'slug' => 'company.delete', 'group' => 'company'],

            // Members
            ['name' => 'View Members', 'slug' => 'members.view', 'group' => 'members'],
            ['name' => 'Manage Members', 'slug' => 'members.manage', 'group' => 'members'],

            // Shareholders
            ['name' => 'View Shareholders', 'slug' => 'shareholders.view', 'group' => 'shareholders'],
            ['name' => 'Manage Shareholders', 'slug' => 'shareholders.manage', 'group' => 'shareholders'],

            // Projects
            ['name' => 'View Projects', 'slug' => 'projects.view', 'group' => 'projects'],
            ['name' => 'Create Projects', 'slug' => 'projects.create', 'group' => 'projects'],
            ['name' => 'Update Projects', 'slug' => 'projects.update', 'group' => 'projects'],
            ['name' => 'Delete Projects', 'slug' => 'projects.delete', 'group' => 'projects'],

            // Investors
            ['name' => 'View Investors', 'slug' => 'investors.view', 'group' => 'investors'],
            ['name' => 'Manage Investors', 'slug' => 'investors.manage', 'group' => 'investors'],

            // Loans
            ['name' => 'View Loans', 'slug' => 'loans.view', 'group' => 'loans'],
            ['name' => 'Manage Loans', 'slug' => 'loans.manage', 'group' => 'loans'],
            ['name' => 'Delete Loans', 'slug' => 'loans.delete', 'group' => 'loans'],

            // Plots (land acquisition)
            ['name' => 'View Plots', 'slug' => 'plots.view', 'group' => 'plots'],
            ['name' => 'Manage Plots', 'slug' => 'plots.manage', 'group' => 'plots'],
            ['name' => 'Delete Plots', 'slug' => 'plots.delete', 'group' => 'plots'],

            // Employees
            ['name' => 'View Employees', 'slug' => 'employees.view', 'group' => 'employees'],
            ['name' => 'Manage Employees', 'slug' => 'employees.manage', 'group' => 'employees'],

            // Customers
            ['name' => 'View Customers', 'slug' => 'customers.view', 'group' => 'customers'],
            ['name' => 'Manage Customers', 'slug' => 'customers.manage', 'group' => 'customers'],

            // Documents
            ['name' => 'View Documents', 'slug' => 'documents.view', 'group' => 'documents'],
            ['name' => 'Upload Documents', 'slug' => 'documents.upload', 'group' => 'documents'],
            ['name' => 'Manage Documents', 'slug' => 'documents.manage', 'group' => 'documents'],

            // Settings
            ['name' => 'View Settings', 'slug' => 'settings.view', 'group' => 'settings'],
            ['name' => 'Manage Settings', 'slug' => 'settings.manage', 'group' => 'settings'],

            // Properties (Buildings, Floors, Units, Unit Types)
            ['name' => 'View Properties', 'slug' => 'properties.view', 'group' => 'properties'],
            ['name' => 'Manage Properties', 'slug' => 'properties.manage', 'group' => 'properties'],
            ['name' => 'Delete Properties', 'slug' => 'properties.delete', 'group' => 'properties'],
        ];

        foreach ($permissions as $permission) {
            Permission::updateOrCreate(
                ['slug' => $permission['slug']],
                $permission,
            );
        }
    }
}
