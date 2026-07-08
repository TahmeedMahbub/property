@extends('contents.body')

@section('title', 'Record Repayment')

@section('content')
<div class="row justify-content-center gy-4">
    <div class="col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb mb-3">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/loans') }}">Loans</a></li>
                <li class="breadcrumb-item"><a href="{{ url("/loans/{$loan->uuid}") }}">{{ $loan->lender_name }}</a></li>
                <li class="breadcrumb-item active">Record Repayment</li>
            </ol>
        </nav>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
                <h5 class="mb-0">Record a Repayment</h5>
                <span class="badge bg-label-warning fs-6">
                    Outstanding: ৳{{ number_format($loan->outstanding_balance, 2) }}
                </span>
            </div>
            <div class="card-body">
                <div class="mb-4 text-muted small">
                    <i class="mdi mdi-bank-outline me-1"></i>
                    {{ $loan->lender_name }}
                    <span class="badge bg-label-info align-middle">{{ ucwords(str_replace('_', ' ', $loan->lender_type)) }}</span>
                    &middot; Principal ৳{{ number_format($loan->principal_amount, 2) }}
                    &middot; Interest {{ rtrim(rtrim(number_format($loan->interest_rate, 2), '0'), '.') }}%
                </div>

                @if ($errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url("/loans/{$loan->uuid}/repayments") }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="payment_date" class="form-label">Payment Date <span class="text-danger">*</span></label>
                            <input type="date" class="form-control" id="payment_date" name="payment_date" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="payment_method" class="form-label">Method <span class="text-danger">*</span></label>
                            <select class="form-select" id="payment_method" name="payment_method" required>
                                @foreach (['bank_transfer' => 'Bank Transfer', 'cheque' => 'Cheque', 'cash' => 'Cash', 'mobile_banking' => 'Mobile Banking', 'other' => 'Other'] as $v => $l)
                                    <option value="{{ $v }}" {{ old('payment_method', 'bank_transfer') === $v ? 'selected' : '' }}>{{ $l }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="principal_paid" class="form-label">Principal</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="principal_paid" name="principal_paid" value="{{ old('principal_paid', '0') }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="interest_paid" class="form-label">Interest</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="interest_paid" name="interest_paid" value="{{ old('interest_paid', '0') }}">
                            </div>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="penalty" class="form-label">Penalty</label>
                            <div class="input-group">
                                <span class="input-group-text">৳</span>
                                <input type="number" step="0.01" min="0" class="form-control" id="penalty" name="penalty" value="{{ old('penalty', '0') }}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="reference_no" class="form-label">Reference / Cheque No</label>
                            <input type="text" class="form-control" id="reference_no" name="reference_no" value="{{ old('reference_no') }}">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="remarks" class="form-label">Remarks</label>
                            <input type="text" class="form-control" id="remarks" name="remarks" value="{{ old('remarks') }}">
                        </div>
                    </div>
                    <div class="d-flex gap-2 mt-2">
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-cash-plus me-1"></i>Add Repayment
                        </button>
                        <a href="{{ url("/loans/{$loan->uuid}") }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
