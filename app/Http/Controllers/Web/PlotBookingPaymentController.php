<?php

namespace App\Http\Controllers\Web;

use App\Domains\Plot\Services\PlotBookingService;
use App\Http\Controllers\Controller;
use App\Models\PlotBooking;
use App\Models\PlotBookingInstallment;
use App\Models\PlotBookingPayment;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PlotBookingPaymentController extends Controller
{
    public function __construct(
        private readonly PlotBookingService $bookings = new PlotBookingService(),
    ) {}

    public function store(Request $request, string $bookingUuid)
    {
        $company = app('currentCompany');
        $booking = PlotBooking::forCompany($company->id)->where('uuid', $bookingUuid)->firstOrFail();

        $validated = $request->validate([
            'installment_id' => [
                'nullable',
                Rule::exists('p_plot_booking_installments', 'id')->where(fn ($q) => $q->where('booking_id', $booking->id)),
            ],
            'payment_type' => ['required', Rule::in(array_keys(PlotBookingPayment::TYPES))],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_date' => ['required', 'date'],
            'payment_method' => ['nullable', 'string', 'max:100'],
            'reference_no' => ['nullable', 'string', 'max:100'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ]);

        $this->bookings->recordPayment($booking, $validated);

        return redirect("/bookings/{$booking->uuid}")->with('success', 'Payment recorded successfully.');
    }

    public function destroy(string $bookingUuid, string $paymentUuid)
    {
        $company = app('currentCompany');
        $booking = PlotBooking::forCompany($company->id)->where('uuid', $bookingUuid)->firstOrFail();
        $payment = PlotBookingPayment::where('booking_id', $booking->id)->where('uuid', $paymentUuid)->firstOrFail();

        $this->bookings->deletePayment($payment);

        return redirect("/bookings/{$booking->uuid}")->with('success', 'Payment deleted successfully.');
    }
}
