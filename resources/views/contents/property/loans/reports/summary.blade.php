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

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Lender</th>
                            <th class="d-none d-md-table-cell">Type</th>
                            <th class="d-none d-md-table-cell">Project</th>
                            <th class="text-end">Principal</th>
                            <th class="text-end">Repaid</th>
                            <th class="text-end">Outstanding</th>
                            <th class="text-end d-none d-md-table-cell">Interest Paid</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $loan)
                            <tr>
                                <td class="fw-medium"><a href="{{ url("/loans/{$loan->uuid}") }}">{{ $loan->lender_name }}</a></td>
                                <td class="d-none d-md-table-cell">{{ ucwords(str_replace('_', ' ', $loan->lender_type)) }}</td>
                                <td class="d-none d-md-table-cell">{{ $loan->project?->name ?: '—' }}</td>
                                <td class="text-end">৳{{ number_format($loan->principal_amount, 2) }}</td>
                                <td class="text-end">৳{{ number_format($loan->total_principal_paid, 2) }}</td>
                                <td class="text-end fw-medium {{ $loan->outstanding_balance > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($loan->outstanding_balance, 2) }}</td>
                                <td class="text-end d-none d-md-table-cell">৳{{ number_format($loan->total_interest_paid, 2) }}</td>
                                <td><span class="badge bg-label-{{ ['active' => 'success', 'closed' => 'secondary', 'defaulted' => 'danger'][$loan->status] }}">{{ ucfirst($loan->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="8" class="text-center text-muted py-4">No loans found.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($data->isNotEmpty())
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="3" class="text-end d-none d-md-table-cell">Totals</td>
                                <td class="d-md-none text-end">Totals</td>
                                <td class="text-end">৳{{ number_format($data->sum('principal_amount'), 2) }}</td>
                                <td class="text-end">৳{{ number_format($data->sum(fn ($l) => $l->total_principal_paid), 2) }}</td>
                                <td class="text-end">৳{{ number_format($data->sum(fn ($l) => $l->outstanding_balance), 2) }}</td>
                                <td class="text-end d-none d-md-table-cell">৳{{ number_format($data->sum(fn ($l) => $l->total_interest_paid), 2) }}</td>
                                <td></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
