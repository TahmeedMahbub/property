<?php

namespace Database\Seeders;

use App\Domains\Plot\Services\PlotService;
use App\Models\Company;
use App\Models\Plot;
use Illuminate\Database\Seeder;

class PlotSeeder extends Seeder
{
    public function run(): void
    {
        $company = Company::first();

        if (! $company) {
            return;
        }

        if (Plot::where('company_id', $company->id)->exists()) {
            return;
        }

        $service = new PlotService();

        $samples = [
            [
                'plot_code' => 'PLT-1001',
                'plot_name' => 'Bashundhara R/A Land',
                'status' => 'registration_complete',
                'division' => 'Dhaka',
                'district' => 'Dhaka',
                'upazila' => 'Badda',
                'area' => 'Bashundhara R/A',
                'mouza' => 'Vatara',
                'jl_no' => '25',
                'khatian_no' => '1420',
                'dag_no' => '3345',
                'land_size' => 10,
                'land_unit' => 'katha',
                'purchase_price' => 30000000,
                'price_per_katha' => 3000000,
                'bayna_amount' => 3000000,
                'registration_cost' => 450000,
                'mutation_cost' => 60000,
                'legal_cost' => 80000,
                'broker_cost' => 150000,
                'other_cost' => 20000,
                'sellers' => [
                    ['name' => 'Abdul Karim', 'phone' => '01711000001', 'nid' => '1990123456789'],
                ],
                'owners' => [
                    ['name' => 'Karim Family Trust', 'ownership_percentage' => 100],
                ],
                'payments' => [
                    ['payment_type' => 'bayna', 'amount' => 3000000],
                    ['payment_type' => 'land', 'amount' => 27000000],
                    ['payment_type' => 'registration', 'amount' => 450000],
                ],
            ],
            [
                'plot_code' => 'PLT-1002',
                'plot_name' => 'Purbachal Plot',
                'status' => 'bayna_done',
                'division' => 'Dhaka',
                'district' => 'Narayanganj',
                'upazila' => 'Rupganj',
                'area' => 'Purbachal',
                'mouza' => 'Kanchan',
                'jl_no' => '88',
                'khatian_no' => '905',
                'dag_no' => '1210',
                'land_size' => 5,
                'land_unit' => 'katha',
                'purchase_price' => 12000000,
                'price_per_katha' => 2400000,
                'bayna_amount' => 2000000,
                'registration_cost' => 200000,
                'mutation_cost' => 30000,
                'legal_cost' => 40000,
                'broker_cost' => 60000,
                'other_cost' => 0,
                'sellers' => [
                    ['name' => 'Sultana Begum', 'phone' => '01811000002', 'nid' => '1985987654321'],
                    ['name' => 'Rafiq Ahmed', 'phone' => '01911000003', 'nid' => '1978111222333'],
                ],
                'owners' => [
                    ['name' => 'Sultana Begum', 'ownership_percentage' => 60],
                    ['name' => 'Rafiq Ahmed', 'ownership_percentage' => 40],
                ],
                'payments' => [
                    ['payment_type' => 'bayna', 'amount' => 2000000],
                ],
            ],
        ];

        foreach ($samples as $sample) {
            $payments = $sample['payments'];
            unset($sample['payments']);

            $plot = $service->create($company->id, $sample);

            foreach ($payments as $payment) {
                $service->recordPayment($plot, array_merge($payment, [
                    'payment_date' => now()->subMonths(2)->toDateString(),
                    'payment_method' => 'bank_transfer',
                ]));
            }
        }
    }
}
