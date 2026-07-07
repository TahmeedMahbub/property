<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $company = app('currentCompany');

        $stats = [
            'projects' => $company->projects()->count(),
            'buildings' => $company->buildings()->count(),
            'total_units' => \App\Models\Unit::where('company_id', $company->id)->count(),
            'available_units' => \App\Models\Unit::where('company_id', $company->id)->where('status', 'available')->count(),
            'reserved_units' => \App\Models\Unit::where('company_id', $company->id)->where('status', 'reserved')->count(),
            'booked_units' => \App\Models\Unit::where('company_id', $company->id)->where('status', 'booked')->count(),
            'sold_units' => \App\Models\Unit::where('company_id', $company->id)->where('status', 'sold')->count(),
        ];

        return view('contents.property.dashboard', compact('stats', 'company'));
    }
}
