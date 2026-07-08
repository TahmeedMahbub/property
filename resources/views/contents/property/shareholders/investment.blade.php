@extends('contents.body')

@section('title', 'Manage Investment')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/shareholders') }}">Shareholders</a></li>
                    <li class="breadcrumb-item"><a href="{{ url("/shareholders/{$shareholder->uuid}/edit") }}">{{ $shareholder->name }}</a></li>
                    <li class="breadcrumb-item active">Manage Investment</li>
                </ol>
            </nav>
            <a href="{{ url('/shareholders') }}" class="btn btn-sm btn-outline-secondary">
                <i class="mdi mdi-arrow-left me-1"></i>Back to Shareholders
            </a>
        </div>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Manage Investment: {{ $shareholder->name }}</h4>
            <span class="badge bg-label-primary fs-6">{{ rtrim(rtrim(number_format((float) $shareholder->ownership_percentage, 6), '0'), '.') }}% ownership</span>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="card mb-4">
            <div class="card-body">
                <div class="row g-3">
                    {{-- <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="small text-muted">Shares Owned</div>
                            <div class="fw-bold">{{ rtrim(rtrim(number_format((float) $shareholder->shares_owned, 6), '0'), '.') }}</div>
                        </div>
                    </div> --}}
                    <div class="col-6 col-md-6">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="small text-muted">Ownership %</div>
                            <div class="fw-bold">{{ rtrim(rtrim(number_format((float) $shareholder->ownership_percentage, 6), '0'), '.') }}%</div>
                        </div>
                    </div>
                    <div class="col-6 col-md-6">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="small text-muted">Invested Amount</div>
                            <div class="fw-bold">{{ number_format((float) $shareholder->share_amount, 2) }}</div>
                        </div>
                    </div>
                    {{-- <div class="col-6 col-md-3">
                        <div class="border rounded p-2 text-center bg-light">
                            <div class="small text-muted">Share Price</div>
                            <div class="fw-bold">{{ rtrim(rtrim(number_format((float) optional($shareholder->company->metrics)->current_share_price, 6), '0'), '.') ?: '—' }}</div>
                        </div>
                    </div> --}}
                </div>
            </div>
        </div>

        <div class="row g-4">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <h6 class="mb-3">Deposit or Withdraw</h6>
                        <form method="POST" action="{{ url("/shareholders/{$shareholder->uuid}/transaction") }}" id="transactionForm">
                            @csrf
                            <div class="mb-3">
                                <label class="form-label d-block">Action <span class="text-danger">*</span></label>
                                <div class="btn-group" role="group" aria-label="Transaction type">
                                    <input type="radio" class="btn-check" name="action" id="action_deposit"
                                        value="deposit" autocomplete="off" {{ old('action') === 'deposit' ? 'checked' : '' }} required>
                                    <label class="btn btn-outline-success" for="action_deposit">
                                        <i class="mdi mdi-arrow-down-bold-circle-outline me-1"></i>Deposit
                                    </label>

                                    <input type="radio" class="btn-check" name="action" id="action_withdraw"
                                        value="withdraw" autocomplete="off" {{ old('action') === 'withdraw' ? 'checked' : '' }}>
                                    <label class="btn btn-outline-danger" for="action_withdraw">
                                        <i class="mdi mdi-arrow-up-bold-circle-outline me-1"></i>Withdraw
                                    </label>
                                </div>
                                <div class="form-text" id="actionHelp">Deposit issues new shares; withdraw buys back shares at the current price.</div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                                    <input type="number" step="0.01" min="0.01" class="form-control" id="amount"
                                        name="amount" value="{{ old('amount') }}" placeholder="0.00" required>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label for="notes" class="form-label">Notes</label>
                                    <input type="text" class="form-control" id="notes" name="notes"
                                        value="{{ old('notes') }}" maxlength="500">
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary" id="submitBtn" disabled
                                onclick="return confirm('Proceed with this transaction?')">
                                <i class="mdi mdi-check me-1"></i><span id="submitLabel">Select an action</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mt-4">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Transaction History</h6>
                <span class="badge bg-label-secondary">{{ $transactions->total() }} total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            {{-- <th class="text-end">Shares</th> --}}
                            <th class="text-end">% of Company</th>
                            {{-- <th class="text-end">Share Price</th> --}}
                            <th>Note</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $totalShares = (float) optional($shareholder->company->metrics)->total_shares; @endphp
                        @forelse ($transactions as $txn)
                            @php $isDeposit = $txn->type === 'issue'; @endphp
                            <tr>
                                <td class="text-nowrap">{{ $txn->created_at->format('d M Y, H:i') }}</td>
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
                                {{-- <td class="text-end">{{ rtrim(rtrim(number_format(abs((float) $txn->shares_issued), 6), '0'), '.') }}</td> --}}
                                <td class="text-end">{{ $totalShares > 0 ? rtrim(rtrim(number_format(abs((float) $txn->shares_issued) / $totalShares * 100, 2), '0'), '.') . '%' : '—' }}</td>
                                {{-- <td class="text-end">{{ rtrim(rtrim(number_format((float) $txn->share_price, 6), '0'), '.') }}</td> --}}
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

@section('page-script')
<script>
    (function () {
        const label = document.getElementById('submitLabel');
        const submitBtn = document.getElementById('submitBtn');
        function applyAction(value) {
            label.textContent = value === 'withdraw' ? 'Withdraw' : 'Deposit';
            submitBtn.disabled = false;
        }
        document.querySelectorAll('input[name="action"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                applyAction(this.value);
            });
        });
        const preselected = document.querySelector('input[name="action"]:checked');
        if (preselected) {
            applyAction(preselected.value);
        }
    })();
</script>
@endsection
