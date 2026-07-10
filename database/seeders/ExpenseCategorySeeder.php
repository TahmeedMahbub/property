<?php

namespace Database\Seeders;

use App\Models\ExpenseCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ExpenseCategorySeeder extends Seeder
{
    /**
     * Global default expense categories (company_id = null) shared by every
     * company. Companies can add their own on top of these.
     */
    public function run(): void
    {
        $categories = [
            ['name' => 'Registration Fee', 'description' => 'Government land/flat registration charges.'],
            ['name' => 'Mutation Fee', 'description' => 'Land mutation / namjari charges.'],
            ['name' => 'Legal Fee', 'description' => 'Lawyer, deed and documentation charges.'],
            ['name' => 'Commission', 'description' => 'Broker / agent commission.'],
            ['name' => 'Interest', 'description' => 'Loan interest paid.'],
            ['name' => 'Penalty', 'description' => 'Loan penalty / late fee paid.'],
            ['name' => 'Salary', 'description' => 'Staff salary and wages.'],
            ['name' => 'Rent', 'description' => 'Office or site rent.'],
            ['name' => 'Marketing', 'description' => 'Advertising and promotion.'],
            ['name' => 'Travel', 'description' => 'Transport and travel costs.'],
            ['name' => 'Utility', 'description' => 'Electricity, water, gas, internet bills.'],
            ['name' => 'Snacks', 'description' => 'Refreshments and entertainment.'],
            ['name' => 'Maintenance', 'description' => 'Repairs and upkeep.'],
            ['name' => 'Other', 'description' => 'Other miscellaneous fees.'],
            ['name' => 'General', 'description' => 'General uncategorised expense.'],
        ];

        foreach ($categories as $category) {
            ExpenseCategory::updateOrCreate(
                ['company_id' => null, 'slug' => Str::slug($category['name'])],
                [
                    'name' => $category['name'],
                    'description' => $category['description'],
                    'is_active' => true,
                ],
            );
        }
    }
}
