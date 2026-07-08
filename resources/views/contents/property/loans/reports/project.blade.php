@extends('contents.body')

@section('title', $title)

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/loans') }}">Loans</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/loans/reports') }}">Reports</a></li>
                    <li class="breadcrumb-item active">{{ $title }}</li>
                </ol>
            </nav>
            <button type="button" onclick="window.print()" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-printer-outline me-1"></i>Print
            </button>
        </div>
        <h4 class="fw-bold mb-3">{{ $title }}</h4>

        <div class="alert alert-info py-2 small">
            <i class="mdi mdi-information-outline me-1"></i>
            Net Project Profit = Sold-unit Revenue − Project Budget − Interest Expense.
        </div>

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Project</th>
                            <th class="text-end">Loan Amount</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end d-none d-md-table-cell">Revenue</th>
                            <th class="text-end d-none d-md-table-cell">Budget</th>
                            <th class="text-end">Interest Expense</th>
                            <th class="text-end">Net Profit</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $row)
                            <tr>
                                <td class="fw-medium">{{ $row['project']->name }}</td>
                                <td class="text-end">৳{{ number_format($row['project_loan_amount'], 2) }}</td>
                                <td class="text-end {{ $row['outstanding_loan'] > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($row['outstanding_loan'], 2) }}</td>
                                <td class="text-end d-none d-md-table-cell">৳{{ number_format($row['revenue'], 2) }}</td>
                                <td class="text-end d-none d-md-table-cell">৳{{ number_format($row['budget'], 2) }}</td>
                                <td class="text-end">৳{{ number_format($row['interest_expense'], 2) }}</td>
                                <td class="text-end fw-medium {{ $row['net_project_profit'] >= 0 ? 'text-success' : 'text-danger' }}">৳{{ number_format($row['net_project_profit'], 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No project loans found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
