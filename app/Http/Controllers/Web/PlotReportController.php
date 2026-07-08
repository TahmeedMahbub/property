<?php

namespace App\Http\Controllers\Web;

use App\Domains\Plot\Services\PlotReportService;
use App\Http\Controllers\Controller;

class PlotReportController extends Controller
{
    public function __construct(
        private readonly PlotReportService $reports = new PlotReportService(),
    ) {}

    public function index()
    {
        return view('contents.property.plots.reports.index');
    }

    public function show(string $type)
    {
        $company = app('currentCompany');

        $reports = [
            'register' => 'Plot Register',
            'acquisition' => 'Plot Acquisition Report',
            'payment' => 'Plot Payment Report',
            'due' => 'Plot Due Report',
            'cost' => 'Plot Cost Summary',
        ];

        abort_unless(array_key_exists($type, $reports), 404);

        $title = $reports[$type];

        $data = match ($type) {
            'register' => $this->reports->registerReport($company->id),
            'acquisition' => $this->reports->acquisitionReport($company->id),
            'payment' => $this->reports->paymentReport($company->id),
            'due' => $this->reports->dueReport($company->id),
            'cost' => $this->reports->costSummary($company->id),
        };

        return view("contents.property.plots.reports.{$type}", compact('data', 'title', 'type'));
    }
}
