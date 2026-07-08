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
                            <th>Status</th>
                            <th class="text-end">Acquisition Cost</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $tCost = 0; $tPaid = 0; $tDue = 0; @endphp
                        @forelse ($data as $plot)
                            @php $tCost += $plot->total_acquisition_cost; $tPaid += $plot->total_paid; $tDue += $plot->total_due; @endphp
                            <tr>
                                <td class="fw-medium">
                                    <a href="{{ url("/plots/{$plot->uuid}") }}">{{ $plot->plot_code }}</a>
                                    <div class="small text-muted">{{ $plot->plot_name }}</div>
                                </td>
                                <td><span class="badge bg-label-info">{{ ucwords(str_replace('_', ' ', $plot->status)) }}</span></td>
                                <td class="text-end">৳{{ number_format($plot->total_acquisition_cost, 2) }}</td>
                                <td class="text-end text-success">৳{{ number_format($plot->total_paid, 2) }}</td>
                                <td class="text-end fw-medium text-danger">৳{{ number_format($plot->total_due, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="text-center text-muted py-4">No plots with outstanding due.</td></tr>
                        @endforelse
                    </tbody>
                    @if ($data->isNotEmpty())
                        <tfoot>
                            <tr class="fw-bold border-top">
                                <td colspan="2" class="text-end">Totals</td>
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
