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
        <p class="text-muted">Aggregated across {{ $data['plot_count'] }} plot(s).</p>

        <div class="col-12 col-lg-8">
            <div class="card">
                <div class="card-body">
                    <table class="table mb-0">
                        <tbody>
                            <tr><td class="text-muted">Purchase Price</td><td class="text-end">৳{{ number_format($data['purchase_price'], 2) }}</td></tr>
                            <tr><td class="text-muted">Registration Cost</td><td class="text-end">৳{{ number_format($data['registration_cost'], 2) }}</td></tr>
                            <tr><td class="text-muted">Mutation Cost</td><td class="text-end">৳{{ number_format($data['mutation_cost'], 2) }}</td></tr>
                            <tr><td class="text-muted">Legal Cost</td><td class="text-end">৳{{ number_format($data['legal_cost'], 2) }}</td></tr>
                            <tr><td class="text-muted">Broker Cost</td><td class="text-end">৳{{ number_format($data['broker_cost'], 2) }}</td></tr>
                            <tr><td class="text-muted">Other Cost</td><td class="text-end">৳{{ number_format($data['other_cost'], 2) }}</td></tr>
                            <tr class="fw-bold border-top"><td>Total Acquisition Cost</td><td class="text-end">৳{{ number_format($data['total_acquisition_cost'], 2) }}</td></tr>
                            <tr><td class="text-success">Total Paid</td><td class="text-end text-success">৳{{ number_format($data['total_paid'], 2) }}</td></tr>
                            <tr class="fw-bold"><td class="text-danger">Total Due</td><td class="text-end text-danger">৳{{ number_format($data['total_due'], 2) }}</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
