@extends('contents.body')

@section('title', 'Investments')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-1">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item active">Investments</li>
                </ol>
            </nav>
            <a href="{{ url('/shareholders') }}" class="btn btn-sm btn-primary">
                <i class="mdi mdi-cash-multiple me-1"></i>Manage Investment
            </a>
        </div>

        <h4 class="fw-bold mb-3">Investments Overview</h4>

        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Deposit &amp; Withdraw History</h6>
                <span class="badge bg-label-secondary">{{ $transactions->total() }} total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Shareholder</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Shares</th>
                            <th class="text-end">Share Price</th>
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transactions as $txn)
                            @php $isDeposit = $txn->type === 'issue'; @endphp
                            <tr>
                                <td class="text-nowrap">{{ $txn->created_at->format('d M Y, H:i') }}</td>
                                <td>
                                    @if ($txn->shareholder)
                                        <a href="{{ url("/shareholders/{$txn->shareholder->uuid}/investment") }}">{{ $txn->shareholder->name }}</a>
                                    @else
                                        <span class="text-muted">—</span>
                                    @endif
                                </td>
                                <td>
                                    @if ($isDeposit)
                                        <span class="badge bg-label-success"><i class="mdi mdi-arrow-down-bold-circle-outline me-1"></i>Deposit</span>
                                    @else
                                        <span class="badge bg-label-danger"><i class="mdi mdi-arrow-up-bold-circle-outline me-1"></i>Withdraw</span>
                                    @endif
                                </td>
                                <td class="text-end fw-medium {{ $isDeposit ? 'text-success' : 'text-danger' }}">
                                    {{ $isDeposit ? '+' : '−' }}{{ number_format(abs((float) $txn->investment_amount), 2) }}
                                </td>
                                <td class="text-end">{{ rtrim(rtrim(number_format(abs((float) $txn->shares_issued), 6), '0'), '.') }}</td>
                                <td class="text-end">{{ rtrim(rtrim(number_format((float) $txn->share_price, 6), '0'), '.') }}</td>
                                <td class="text-truncate" style="max-width: 220px;">{{ $txn->notes ?: '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">No transactions yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($transactions->hasPages())
                <div class="card-footer">
                    {{ $transactions->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
