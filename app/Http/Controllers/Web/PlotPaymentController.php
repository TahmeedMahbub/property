<?php

namespace App\Http\Controllers\Web;

use App\Domains\Plot\Requests\StorePlotPaymentRequest;
use App\Domains\Plot\Services\PlotService;
use App\Http\Controllers\Controller;
use App\Models\Plot;
use App\Models\PlotPayment;

class PlotPaymentController extends Controller
{
    public function __construct(
        private readonly PlotService $plots = new PlotService(),
    ) {}

    public function create(string $plotUuid)
    {
        $company = app('currentCompany');
        $plot = Plot::forCompany($company->id)->where('uuid', $plotUuid)->firstOrFail();

        return view('contents.property.plots.pay', compact('plot'));
    }

    public function store(StorePlotPaymentRequest $request, string $plotUuid)
    {
        $company = app('currentCompany');
        $plot = Plot::forCompany($company->id)->where('uuid', $plotUuid)->firstOrFail();

        $this->plots->recordPayment($plot, $request->validated());

        return redirect("/plots/{$plot->uuid}")->with('success', 'Payment recorded successfully.');
    }

    public function destroy(string $plotUuid, string $paymentUuid)
    {
        $company = app('currentCompany');
        $plot = Plot::forCompany($company->id)->where('uuid', $plotUuid)->firstOrFail();
        $payment = PlotPayment::where('plot_id', $plot->id)->where('uuid', $paymentUuid)->firstOrFail();

        $this->plots->deletePayment($payment);

        return redirect("/plots/{$plot->uuid}")->with('success', 'Payment deleted successfully.');
    }
}
