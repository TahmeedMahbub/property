@extends('contents.body')

@section('title', 'Record Plot Payment')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-7">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/plots') }}">Plots</a></li>
                <li class="breadcrumb-item"><a href="{{ url("/plots/{$plot->uuid}") }}">{{ $plot->plot_code }}</a></li>
                <li class="breadcrumb-item active">Record Payment</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-1">Record Payment</h4>
        <p class="text-muted mb-3">
            {{ $plot->plot_name }} — Outstanding due:
            <strong class="{{ $plot->total_due > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($plot->total_due, 2) }}</strong>
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

                <form method="POST" action="{{ url("/plots/{$plot->uuid}/payments") }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_type" class="form-label">Payment Type <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_type" name="payment_type" required>
                                @foreach (\App\Models\PlotPayment::TYPES as $v => $l)
                                    <option value="{{ $v }}" {{ old('payment_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0.01" class="form-control" id="amount" name="amount" value="{{ old('amount') }}" required>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Payment Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                @foreach (['cash' => 'Cash', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', 'mobile_banking' => 'Mobile Banking', 'other' => 'Other'] as $v => $l)
                                    <option value="{{ $v }}" {{ old('payment_method', 'bank_transfer') === $v ? 'selected' : '' }}>{{ $l }}</option>
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
                        <button type="submit" class="btn btn-primary">Save Payment</button>
                        <a href="{{ url("/plots/{$plot->uuid}") }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
