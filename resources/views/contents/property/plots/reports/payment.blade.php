@extends('contents.body')

@section('title', $title)

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/plots') }}">Plots</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/plots/reports') }}">Reports</a></li>
                <li class="breadcrumb-item active">{{ $title }}</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">{{ $title }}</h4>

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Plot</th>
                            <th>Type</th>
                            <th>Method</th>
                            <th>Reference</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $total = 0; @endphp
                        @forelse ($data as $payment)
                            @php $total += $payment->amount; @endphp
                            <tr>
                                <td class="text-nowrap">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td class="fw-medium"><a href="{{ url("/plots/{$payment->plot->uuid}") }}">{{ $payment->plot->plot_code }}</a></td>
                                <td><span class="badge bg-label-primary">{{ \App\Models\PlotPayment::TYPES[$payment->payment_type] ?? ucfirst($payment->payment_type) }}</span></td>
                                <td>{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td>{{ $payment->reference_no ?: '—' }}</td>
                                <td class="text-end fw-medium">৳{{ number_format($payment->amount, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="text-center text-muted py-4">No payments found.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($data->isNotEmpty())
                        <tfoot>
                            <tr class="fw-bold border-top">
                                <td colspan="5" class="text-end">Total Paid</td>
                                <td class="text-end text-success">৳{{ number_format($total, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
