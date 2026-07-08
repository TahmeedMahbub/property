<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Loan;
use App\Models\LoanRepayment;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class LoanSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            return;
        }

        if (Loan::where('company_id', $company->id)->exists()) {
            return;
        }

        $project = $company->projects()->first();

        $samples = [
            [
                'lender_type' => 'bank',
                'lender_name' => 'City Bank PLC',
                'reference_no' => 'CBL-2024-00187',
                'principal_amount' => 50000000,
                'interest_rate' => 11.5,
                'interest_type' => 'reducing',
                'repayments' => 6,
            ],
            [
                'lender_type' => 'shareholder',
                'lender_name' => 'Tahmeed Rahman',
                'reference_no' => null,
                'principal_amount' => 10000000,
                'interest_rate' => 8,
                'interest_type' => 'flat',
                'repayments' => 3,
            ],
            [
                'lender_type' => 'third_party',
                'lender_name' => 'Meghna Investments Ltd.',
                'reference_no' => 'MIL-778',
                'principal_amount' => 7500000,
                'interest_rate' => 14,
                'interest_type' => 'flat',
                'repayments' => 2,
            ],
        ];

        foreach ($samples as $sample) {
            $start = Carbon::now()->subMonths(10);

            $loan = Loan::create([
                'company_id' => $company->id,
                'project_id' => $project?->id,
                'lender_type' => $sample['lender_type'],
                'lender_name' => $sample['lender_name'],
                'reference_no' => $sample['reference_no'],
                'principal_amount' => $sample['principal_amount'],
                'interest_rate' => $sample['interest_rate'],
                'interest_type' => $sample['interest_type'],
                'start_date' => $start,
                'end_date' => $start->copy()->addYears(3),
                'repayment_frequency' => 'monthly',
                'status' => 'active',
            ]);

            $monthlyPrincipal = round($sample['principal_amount'] / 36, 2);
            $monthlyInterest = round($sample['principal_amount'] * ($sample['interest_rate'] / 100) / 12, 2);

            for ($i = 1; $i <= $sample['repayments']; $i++) {
                LoanRepayment::create([
                    'loan_id' => $loan->id,
                    'payment_date' => $start->copy()->addMonths($i),
                    'principal_paid' => $monthlyPrincipal,
                    'interest_paid' => $monthlyInterest,
                    'penalty' => 0,
                    'payment_method' => 'bank_transfer',
                    'reference_no' => 'TXN'.str_pad((string) ($loan->id * 100 + $i), 8, '0', STR_PAD_LEFT),
                ]);
            }
        }
    }
}
