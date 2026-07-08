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
                            <th>Plot</th>
                            <th class="text-end">Purchase</th>
                            <th class="text-end">Registration</th>
                            <th class="text-end">Mutation</th>
                            <th class="text-end">Legal</th>
                            <th class="text-end">Broker</th>
                            <th class="text-end">Other</th>
                            <th class="text-end">Total Cost</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $tCost = 0; $tPaid = 0; $tDue = 0;
                        @endphp
                        @forelse ($data as $plot)
                            @php
                                $tCost += $plot->total_acquisition_cost;
                                $tPaid += $plot->total_paid;
                                $tDue += $plot->total_due;
                            @endphp
                            <tr>
                                <td class="fw-medium"><a href="{{ url("/plots/{$plot->uuid}") }}">{{ $plot->plot_code }}</a></td>
                                <td class="text-end">৳{{ number_format($plot->purchase_price, 2) }}</td>
                                <td class="text-end">৳{{ number_format($plot->registration_cost, 2) }}</td>
                                <td class="text-end">৳{{ number_format($plot->mutation_cost, 2) }}</td>
                                <td class="text-end">৳{{ number_format($plot->legal_cost, 2) }}</td>
                                <td class="text-end">৳{{ number_format($plot->broker_cost, 2) }}</td>
                                <td class="text-end">৳{{ number_format($plot->other_cost, 2) }}</td>
                                <td class="text-end fw-medium">৳{{ number_format($plot->total_acquisition_cost, 2) }}</td>
                                <td class="text-end text-success">৳{{ number_format($plot->total_paid, 2) }}</td>
                                <td class="text-end {{ $plot->total_due > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($plot->total_due, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="10" class="text-center text-muted py-4">No plots found.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($data->isNotEmpty())
                        <tfoot>
                            <tr class="fw-bold border-top">
                                <td colspan="7" class="text-end">Totals</td>
                                <td class="text-end">৳{{ number_format($tCost, 2) }}</td>
                                <td class="text-end text-success">৳{{ number_format($tPaid, 2) }}</td>
                                <td class="text-end text-danger">৳{{ number_format($tDue, 2) }}</td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
