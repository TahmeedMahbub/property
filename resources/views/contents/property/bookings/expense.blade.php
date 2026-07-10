@extends('contents.body')

@section('title', 'Record Booking Expense')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/bookings') }}">Bookings</a></li>
                <li class="breadcrumb-item"><a href="{{ url("/bookings/{$booking->uuid}") }}">{{ $booking->booking_no }}</a></li>
                <li class="breadcrumb-item active">Record Expense</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-1">Record Expense</h4>
        <p class="text-muted mb-3">
            {{ $booking->customer?->name }} — {{ $booking->plot?->plot_name }}
        </p>

        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url("/bookings/{$booking->uuid}/expenses") }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="category" class="form-label">Expense Category <span class="text-danger">*</span></label>
                            <select class="form-select" id="category" name="category" required>
                                @foreach (\App\Models\Expense::CATEGORIES as $v => $l)
                                    <option value="{{ $v }}" {{ old('category') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control" id="title" name="title" value="{{ old('title') }}" placeholder="Defaults to category name">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="expense_date" class="form-label">Expense Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="expense_date" name="expense_date" value="{{ old('expense_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Payment Method</label>
                            <select class="form-select" id="payment_method" name="payment_method">
                                @foreach (['cash' => 'Cash', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', 'mobile_banking' => 'Mobile Banking', 'other' => 'Other'] as $v => $l)
                                    <option value="{{ $v }}" {{ old('payment_method', 'cash') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="reference_no" class="form-label">Reference / Cheque No</label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no" value="{{ old('reference_no') }}">
                        </div>
                        <div class="col-12 mb-3">
                            <label for="notes" class="form-label">Notes</label>
                            <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes') }}</textarea>
                        </div>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary"><i class="mdi mdi-cash-minus me-1"></i>Save Expense</button>
                        <a href="{{ url("/bookings/{$booking->uuid}") }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
