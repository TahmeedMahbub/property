<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use App\Models\PlotBooking;
use App\Services\ExpenseService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlotBookingExpenseController extends Controller
{
    public function __construct(
        private readonly ExpenseService $expenses = new ExpenseService(),
    ) {}

    public function create(string $bookingUuid)
    {
        $company = app('currentCompany');
        $booking = PlotBooking::forCompany($company->id)
            ->with(['plot', 'customer'])
            ->where('uuid', $bookingUuid)
            ->firstOrFail();

        return view('contents.property.bookings.expense', compact('booking'));
    }

    public function store(Request $request, string $bookingUuid)
    {
        $company = app('currentCompany');
        $booking = PlotBooking::forCompany($company->id)->where('uuid', $bookingUuid)->firstOrFail();

        $validated = $request->validate([
            'category' => ['required', Rule::in(array_keys(Expense::CATEGORIES))],
            'title' => ['nullable', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'expense_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->expenses->record($company->id, $validated, $booking);

        return redirect("/bookings/{$booking->uuid}")->with('success', 'Expense recorded successfully.');
    }

    public function destroy(string $bookingUuid, string $expenseUuid)
    {
        $company = app('currentCompany');
        $booking = PlotBooking::forCompany($company->id)->where('uuid', $bookingUuid)->firstOrFail();
        $expense = Expense::forCompany($company->id)
            ->where('expensable_type', $booking->getMorphClass())
            ->where('expensable_id', $booking->id)
            ->where('uuid', $expenseUuid)
            ->firstOrFail();

        $this->expenses->delete($expense);

        return redirect("/bookings/{$booking->uuid}")->with('success', 'Expense deleted successfully.');
    }
}
