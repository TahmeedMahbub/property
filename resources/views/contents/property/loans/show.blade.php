@extends('contents.body')

@section('title', 'Loan Details')

@section('content')
<style>
    .repay-table { overflow: visible; }
</style>
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/loans') }}">Loans</a></li>
                    <li class="breadcrumb-item active">{{ $loan->lender_name }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="{{ url("/loans/{$loan->uuid}/edit") }}" class="btn btn-sm btn-outline-primary">
                    <i class="mdi mdi-pencil-outline me-1"></i>Edit
                </a>
                <a href="{{ url('/loans') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2 mb-3">
            <h4 class="fw-bold mb-0">
                {{ $loan->lender_name }}
                <span class="badge bg-label-info align-middle">{{ ucwords(str_replace('_', ' ', $loan->lender_type)) }}</span>
            </h4>
            <span class="badge bg-label-{{ ['active' => 'success', 'closed' => 'secondary', 'defaulted' => 'danger'][$loan->status] }} fs-6">
                {{ ucfirst($loan->status) }}
            </span>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Summary stat cards --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Principal</div>
                    <h5 class="mb-0">৳{{ number_format($loan->principal_amount, 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Outstanding Balance</div>
                    <h5 class="mb-0 {{ $loan->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($loan->outstanding_balance, 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Principal Repaid</div>
                    <h5 class="mb-0">৳{{ number_format($loan->total_principal_paid, 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Interest Paid</div>
                    <h5 class="mb-0">৳{{ number_format($loan->total_interest_paid, 2) }}</h5>
                </div></div>
            </div>
        </div>
    </div>

    {{-- Overview --}}
    <div class="col-12 col-lg-5">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Overview</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Reference No</dt><dd class="col-7">{{ $loan->reference_no ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Project</dt><dd class="col-7">{{ $loan->project?->name ?: 'Company-level' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Interest Rate</dt><dd class="col-7">{{ rtrim(rtrim(number_format($loan->interest_rate, 4), '0'), '.') }}% ({{ ucfirst($loan->interest_type) }})</dd>
                    <dt class="col-5 text-muted fw-normal">EMI Amount</dt><dd class="col-7">{{ $loan->emi_amount ? '৳' . number_format($loan->emi_amount, 2) : '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Repayment</dt><dd class="col-7">{{ ucfirst($loan->repayment_frequency) }}</dd>
                    <dt class="col-5 text-muted fw-normal">Start Date</dt><dd class="col-7">{{ $loan->start_date?->format('d M Y') ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Maturity Date</dt><dd class="col-7">{{ $loan->end_date?->format('d M Y') ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Next Due</dt><dd class="col-7">{{ $nextDue && $loan->status === 'active' ? $nextDue->format('d M Y') : '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Collateral</dt><dd class="col-7">{{ $loan->collateral ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Notes</dt><dd class="col-7">{{ $loan->notes ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Interest summary + Add repayment --}}
    <div class="col-12 col-lg-7">
        <div class="card mb-4">
            <div class="card-header"><h6 class="mb-0">Interest &amp; Payment Summary</h6></div>
            <div class="card-body">
                <div class="row g-3 text-center">
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="small text-muted">Interest Paid</div>
                            <div class="fw-bold">৳{{ number_format($loan->total_interest_paid, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="small text-muted">Penalty Paid</div>
                            <div class="fw-bold">৳{{ number_format($loan->total_penalty_paid, 2) }}</div>
                        </div>
                    </div>
                    <div class="col-4">
                        <div class="border rounded p-2">
                            <div class="small text-muted">Total Paid</div>
                            <div class="fw-bold">৳{{ number_format($loan->total_paid, 2) }}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        @if ($loan->status !== 'closed')
            <div class="card" id="repay">
                <div class="card-header"><h6 class="mb-0">Record a Repayment</h6></div>
                <div class="card-body">
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
                                <input type="number" step="0.01" min="0" class="form-control" id="principal_paid" name="principal_paid" value="{{ old('principal_paid', '0') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="interest_paid" class="form-label">Interest</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="interest_paid" name="interest_paid" value="{{ old('interest_paid', '0') }}">
                            </div>
                            <div class="col-md-4 mb-3">
                                <label for="penalty" class="form-label">Penalty</label>
                                <input type="number" step="0.01" min="0" class="form-control" id="penalty" name="penalty" value="{{ old('penalty', '0') }}">
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
                        <button type="submit" class="btn btn-primary">
                            <i class="mdi mdi-cash-plus me-1"></i>Add Repayment
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>

    {{-- Repayment history --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Repayment History</h6>
                <span class="badge bg-label-secondary">{{ $loan->repayments->count() }} payments</span>
            </div>
            <div class="table-responsive repay-table">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th class="text-end">Principal</th>
                            <th class="text-end">Interest</th>
                            <th class="text-end">Penalty</th>
                            <th class="text-end">Total</th>
                            <th class="d-none d-md-table-cell">Method</th>
                            <th class="d-none d-md-table-cell">Reference</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loan->repayments as $rp)
                            <tr>
                                <td class="text-nowrap">{{ $rp->payment_date->format('d M Y') }}</td>
                                <td class="text-end">৳{{ number_format($rp->principal_paid, 2) }}</td>
                                <td class="text-end">৳{{ number_format($rp->interest_paid, 2) }}</td>
                                <td class="text-end">৳{{ number_format($rp->penalty, 2) }}</td>
                                <td class="text-end fw-medium">৳{{ number_format($rp->total_paid, 2) }}</td>
                                <td class="d-none d-md-table-cell">{{ ucwords(str_replace('_', ' ', $rp->payment_method)) }}</td>
                                <td class="d-none d-md-table-cell">{{ $rp->reference_no ?: '—' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ url("/loans/{$loan->uuid}/repayments/{$rp->uuid}") }}"
                                        onsubmit="return confirm('Delete this repayment?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill">
                                            <i class="mdi mdi-delete-outline"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No repayments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
