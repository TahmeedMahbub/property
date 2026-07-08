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
                            <th>Date</th>
                            <th>Lender</th>
                            <th class="text-end">Principal</th>
                            <th class="text-end">Interest</th>
                            <th class="text-end">Penalty</th>
                            <th class="text-end">Total</th>
                            <th class="d-none d-md-table-cell">Method</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $rp)
                            <tr>
                                <td class="text-nowrap">{{ $rp->payment_date->format('d M Y') }}</td>
                                <td><a href="{{ url("/loans/{$rp->loan->uuid}") }}">{{ $rp->loan->lender_name }}</a></td>
                                <td class="text-end">৳{{ number_format($rp->principal_paid, 2) }}</td>
                                <td class="text-end">৳{{ number_format($rp->interest_paid, 2) }}</td>
                                <td class="text-end">৳{{ number_format($rp->penalty, 2) }}</td>
                                <td class="text-end fw-medium">৳{{ number_format($rp->total_paid, 2) }}</td>
                                <td class="d-none d-md-table-cell">{{ ucwords(str_replace('_', ' ', $rp->payment_method)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No repayments recorded.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($data->isNotEmpty())
                        <tfoot>
                            <tr class="fw-bold">
                                <td colspan="2" class="text-end">Totals</td>
                                <td class="text-end">৳{{ number_format($data->sum('principal_paid'), 2) }}</td>
                                <td class="text-end">৳{{ number_format($data->sum('interest_paid'), 2) }}</td>
                                <td class="text-end">৳{{ number_format($data->sum('penalty'), 2) }}</td>
                                <td class="text-end">৳{{ number_format($data->sum(fn ($r) => $r->total_paid), 2) }}</td>
                                <td class="d-none d-md-table-cell"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
