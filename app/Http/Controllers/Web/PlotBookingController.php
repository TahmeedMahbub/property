<?php

namespace App\Http\Controllers\Web;

use App\Domains\Plot\Services\PlotBookingService;
use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DocumentCategory;
use App\Models\Plot;
use App\Models\PlotBooking;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PlotBookingController extends Controller
{
    public function __construct(
        private readonly PlotBookingService $bookings = new PlotBookingService(),
    ) {}

    public function index(Request $request)
    {
        $company = app('currentCompany');

        $query = PlotBooking::forCompany($company->id)
            ->with(['plot', 'customer', 'payments']);

        if ($search = $request->input('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('booking_no', 'like', "%{$search}%")
                    ->orWhereHas('customer', fn ($c) => $c->where('name', 'like', "%{$search}%"))
                    ->orWhereHas('plot', fn ($p) => $p->where('plot_name', 'like', "%{$search}%")
                        ->orWhere('plot_code', 'like', "%{$search}%"));
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        $bookings = $query->latest()->paginate(15)->withQueryString();

        return view('contents.property.bookings.index', compact('bookings'));
    }

    public function create()
    {
        $company = app('currentCompany');

        return view('contents.property.bookings.create', [
            'plots' => $this->availablePlots($company->id),
            'customers' => $this->customers($company->id),
            'documentCategories' => $this->documentCategories(),
        ]);
    }

    public function store(Request $request)
    {
        $company = app('currentCompany');

        $data = $this->validateData($request, $company->id);

        try {
            $this->bookings->create($company->id, $data);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['shares_count' => $e->getMessage()]);
        }

        return redirect('/bookings')->with('success', 'Booking created successfully.');
    }

    public function show(string $uuid)
    {
        $company = app('currentCompany');

        $booking = PlotBooking::forCompany($company->id)
            ->with([
                'plot',
                'customer',
                'creator',
                'installments.payments',
                'payments' => fn ($q) => $q->with('installment')->latest('payment_date')->latest('id'),
                'documents' => fn ($q) => $q->with('category')->latest(),
            ])
            ->where('uuid', $uuid)
            ->firstOrFail();

        $documentCategories = $this->documentCategories();

        return view('contents.property.bookings.show', compact('booking', 'documentCategories'));
    }

    public function edit(string $uuid)
    {
        $company = app('currentCompany');

        $booking = PlotBooking::forCompany($company->id)
            ->with(['installments', 'documents' => fn ($q) => $q->with('category')->latest()])
            ->where('uuid', $uuid)
            ->firstOrFail();

        return view('contents.property.bookings.edit', [
            'booking' => $booking,
            'plots' => $this->availablePlots($company->id, $booking->plot_id),
            'customers' => $this->customers($company->id),
            'documentCategories' => $this->documentCategories(),
        ]);
    }

    public function update(Request $request, string $uuid)
    {
        $company = app('currentCompany');

        $booking = PlotBooking::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();

        $data = $this->validateData($request, $company->id);

        try {
            $this->bookings->update($booking, $data);
        } catch (\RuntimeException $e) {
            throw ValidationException::withMessages(['shares_count' => $e->getMessage()]);
        }

        return redirect('/bookings')->with('success', 'Booking updated successfully.');
    }

    public function destroy(string $uuid)
    {
        $company = app('currentCompany');

        $booking = PlotBooking::forCompany($company->id)->where('uuid', $uuid)->firstOrFail();

        $this->bookings->delete($booking);

        return redirect('/bookings')->with('success', 'Booking deleted successfully.');
    }

    /**
     * Validate and normalise the booking form payload.
     *
     * @return array<string, mixed>
     */
    private function validateData(Request $request, int $companyId): array
    {
        $validated = $request->validate([
            'plot_id' => [
                'required',
                Rule::exists('p_plots', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'customer_id' => [
                'required',
                Rule::exists('p_customers', 'id')->where(fn ($q) => $q->where('company_id', $companyId)),
            ],
            'shares_count' => ['required', 'integer', 'min:1'],
            'share_price' => ['required', 'numeric', 'min:0'],
            'booking_money' => ['required', 'numeric', 'min:0'],
            'registration_fee' => ['nullable', 'numeric', 'min:0'],
            'other_fee' => ['nullable', 'numeric', 'min:0'],
            'discount' => ['nullable', 'numeric', 'min:0'],
            'booking_date' => ['nullable', 'date'],
            'status' => ['required', Rule::in(PlotBooking::STATUSES)],
            'other_info' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],

            // "Paid" flags per amount field (records a cash-in payment when checked)
            'paid' => ['nullable', 'array'],
            'paid.*' => ['nullable', 'in:0,1'],

            'installments' => ['nullable', 'array'],
            'installments.*.title' => ['nullable', 'string', 'max:255'],
            'installments.*.due_date' => ['nullable', 'date'],
            'installments.*.amount' => ['nullable', 'numeric', 'min:0'],
            'installments.*.notes' => ['nullable', 'string', 'max:255'],

            'documents' => ['nullable', 'array'],
            'documents.*.category_id' => [
                'nullable',
                Rule::exists('p_document_categories', 'id')->where(
                    fn ($q) => $q->where(fn ($w) => $w->where('company_id', $companyId)->orWhereNull('company_id')),
                ),
            ],
            'documents.*.title' => ['nullable', 'string', 'max:255'],
            'documents.*.description' => ['nullable', 'string', 'max:2000'],
            'documents.*.file' => ['nullable', 'file', 'mimes:jpg,jpeg,png,gif,webp,pdf', 'max:3072'],
        ]);

        $validated['registration_fee'] = $validated['registration_fee'] ?? 0;
        $validated['other_fee'] = $validated['other_fee'] ?? 0;
        $validated['discount'] = $validated['discount'] ?? 0;

        return $validated;
    }

    /**
     * Plots with shares defined, keeping the currently-selected plot even when
     * it is otherwise fully booked (so an edit can retain its plot).
     */
    private function availablePlots(int $companyId, ?int $keepPlotId = null)
    {
        return Plot::forCompany($companyId)
            ->with('bookings')
            ->where(function ($q) {
                $q->whereNotNull('total_shares')->where('total_shares', '>', 0);
            })
            ->orderBy('plot_name')
            ->get()
            ->filter(fn ($plot) => $plot->shares_available > 0 || $plot->id === $keepPlotId)
            ->values();
    }

    private function customers(int $companyId)
    {
        return Customer::where('company_id', $companyId)
            ->orderBy('name')
            ->get(['id', 'name', 'phone']);
    }

    private function documentCategories()
    {
        return DocumentCategory::forCompany(app('currentCompany')->id)
            ->roots()
            ->with(['children' => fn ($q) => $q->orderBy('sort_order')])
            ->orderBy('sort_order')
            ->get();
    }
}
