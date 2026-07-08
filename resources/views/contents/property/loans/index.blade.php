@extends('contents.body')

@section('title', 'Loans')

@section('content')
<style>
    .loans-table { overflow: visible; }
</style>
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Loans</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="fw-bold mb-0">Loan Management</h4>
            <div class="d-flex gap-2">
                <a href="{{ url('/loans/reports') }}" class="btn btn-outline-secondary">
                    <i class="mdi mdi-file-chart-outline me-1"></i> Reports
                </a>
                <a href="{{ url('/loans/create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus me-1"></i> Add Loan
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Metrics --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-danger rounded p-2 me-2"><i class="mdi mdi-cash-remove"></i></span>
                        <span class="text-muted small">Outstanding</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['total_outstanding'], 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-primary rounded p-2 me-2"><i class="mdi mdi-bank"></i></span>
                        <span class="text-muted small">Borrowed</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['total_principal_borrowed'], 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-success rounded p-2 me-2"><i class="mdi mdi-cash-check"></i></span>
                        <span class="text-muted small">Repaid</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['total_principal_repaid'], 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-warning rounded p-2 me-2"><i class="mdi mdi-percent-outline"></i></span>
                        <span class="text-muted small">Interest Paid</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['total_interest_paid'], 2) }}</h5>
                </div></div>
            </div>
        </div>

        {{-- Upcoming payments alert --}}
        @if ($metrics['upcoming_payments']->isNotEmpty())
            <div class="alert alert-warning">
                <h6 class="alert-heading mb-2"><i class="mdi mdi-bell-alert-outline me-1"></i>Upcoming Loan Payments (next 30 days)</h6>
                <ul class="mb-0 ps-3">
                    @foreach ($metrics['upcoming_payments']->take(5) as $row)
                        <li>
                            <a href="{{ url("/loans/{$row['loan']->uuid}") }}">{{ $row['loan']->lender_name }}</a>
                            — {{ $row['kind'] === 'maturity' ? 'Maturity' : 'Installment' }} due
                            {{ $row['due_date']->format('d M Y') }}
                            <span class="text-muted">({{ $row['days_left'] }} days)</span>
                        </li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ url('/loans') }}" class="row g-2">
                    <div class="col-12 col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search lender or reference...">
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="lender_type" class="form-select">
                            <option value="">All Lender Types</option>
                            @foreach (['bank' => 'Bank', 'shareholder' => 'Shareholder', 'director' => 'Director', 'third_party' => 'Third Party'] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('lender_type') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach (['active' => 'Active', 'closed' => 'Closed', 'defaulted' => 'Defaulted'] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary w-100"><i class="mdi mdi-magnify"></i></button>
                        @if (request()->hasAny(['search', 'lender_type', 'status']))
                            <a href="{{ url('/loans') }}" class="btn btn-outline-danger"><i class="mdi mdi-close"></i></a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive loans-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Lender</th>
                            <th class="d-none d-md-table-cell">Type</th>
                            <th class="text-end">Principal</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end d-none d-md-table-cell">Interest Rate</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($loans as $loan)
                            @php $outstanding = round((float) $loan->principal_amount - (float) ($loan->principal_repaid ?? 0), 2); @endphp
                            <tr>
                                <td class="fw-medium">
                                    <a href="{{ url("/loans/{$loan->uuid}") }}">{{ $loan->lender_name }}</a>
                                    @if ($loan->reference_no)<div class="small text-muted">{{ $loan->reference_no }}</div>@endif
                                </td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge bg-label-info">{{ ucwords(str_replace('_', ' ', $loan->lender_type)) }}</span>
                                </td>
                                <td class="text-end">৳{{ number_format($loan->principal_amount, 2) }}</td>
                                <td class="text-end fw-medium {{ $outstanding > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($outstanding, 2) }}</td>
                                <td class="text-end d-none d-md-table-cell">{{ rtrim(rtrim(number_format($loan->interest_rate, 4), '0'), '.') }}%</td>
                                <td>
                                    <span class="badge bg-label-{{ ['active' => 'success', 'closed' => 'secondary', 'defaulted' => 'danger'][$loan->status] }}">
                                        {{ ucfirst($loan->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="{{ url("/loans/{$loan->uuid}") }}">
                                                <i class="mdi mdi-eye-outline me-1"></i> View
                                            </a>
                                            @if ($loan->status !== 'closed')
                                                <a class="dropdown-item" href="{{ url("/loans/{$loan->uuid}/repayments/create") }}">
                                                    <i class="mdi mdi-cash-multiple me-1"></i> Repay Loan
                                                </a>
                                            @endif
                                            <a class="dropdown-item" href="{{ url("/loans/{$loan->uuid}/edit") }}">
                                                <i class="mdi mdi-pencil-outline me-1"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ url("/loans/{$loan->uuid}") }}"
                                                onsubmit="return confirm('Delete this loan and all its repayments?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="mdi mdi-delete-outline me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No loans found. <a href="{{ url('/loans/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($loans->hasPages())
                <div class="card-footer">{{ $loans->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
