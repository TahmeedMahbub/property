<?php

namespace App\Http\Controllers\Web;

use App\Domains\Plot\Requests\StorePlotRequest;
use App\Domains\Plot\Requests\UpdatePlotRequest;
use App\Domains\Plot\Services\PlotReportService;
use App\Domains\Plot\Services\PlotService;
use App\Http\Controllers\Controller;
use App\Models\DocumentCategory;
use App\Models\Plot;
use Illuminate\Http\Request;

class PlotController extends Controller
{
    public function __construct(
        private readonly PlotService $plots = new PlotService(),
        private readonly PlotReportService $reports = new PlotReportService(),
    ) {}

    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = Plot::forCompany($company->id)->withSum('payments as paid_total', 'amount');

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('plot_name', 'like', "%{$search}%")
                    ->orWhere('plot_code', 'like', "%{$search}%")
                    ->orWhere('mouza', 'like', "%{$search}%")
                    ->orWhere('district', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $plots = $query->latest()->paginate(15)->withQueryString();
        $metrics = $this->reports->companyMetrics($company->id);

        return view('contents.property.plots.index', compact('plots', 'metrics'));
    }

    public function create()
    {
        $documentCategories = $this->documentCategories();

        return view('contents.property.plots.create', compact('documentCategories'));
    }

    public function store(StorePlotRequest $request)
    {
        $company = app('currentCompany');

        $this->plots->create($company->id, $request->validated());

        return redirect('/plots')->with('success', 'Plot created successfully.');
    }

    public function show(string $uuid)
    {
        $company = app('currentCompany');

        $plot = Plot::forCompany($company->id)
            ->with([
                'sellers',
                'owners',
                'creator',
                'payments' => fn ($q) => $q->latest('payment_date')->latest('id'),
                'documents' => fn ($q) => $q->with('category')->latest(),
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $documentCategories = $this->documentCategories();

        return view('contents.property.plots.show', compact('plot', 'documentCategories'));
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');

        $plot = Plot::forCompany($company->id)
            ->with([
                'sellers',
                'owners',
                'documents' => fn ($q) => $q->with('category')->latest(),
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $documentCategories = $this->documentCategories();

        return view('contents.property.plots.edit', compact('plot', 'documentCategories'));
    }

    public function update(UpdatePlotRequest $request, string $uuid)
    {
        $company = app('currentCompany');

        $plot = Plot::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();

        $this->plots->update($plot, $request->validated());

        return redirect('/plots')->with('success', 'Plot updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');

        $plot = Plot::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();

        $this->plots->delete($plot);

        return redirect('/plots')->with('success', 'Plot deleted successfully.');
    }

    /**
     * Document categories (grouped parent → children) available to the current
     * company. These drive the document upload selects on the plot forms.
     */
    private function documentCategories()
    {
        return DocumentCategory::forCompany(app('currentCompany')->id)
            ->roots()
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }
}
