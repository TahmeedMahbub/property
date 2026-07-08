@extends('contents.body')

@section('title', 'Plot Reports')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/plots') }}">Plots</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Plot Reports</h4>

        <div class="row g-3">
            @php
                $reports = [
                    ['register', 'Plot Register', 'Every plot with its location, land size and status.', 'mdi-format-list-bulleted', 'primary'],
                    ['acquisition', 'Plot Acquisition Report', 'Cost breakdown and paid/due per plot.', 'mdi-cash-multiple', 'info'],
                    ['payment', 'Plot Payment Report', 'Every payment transaction across all plots.', 'mdi-cash-check', 'success'],
                    ['due', 'Plot Due Report', 'Plots that still carry an outstanding acquisition due.', 'mdi-cash-remove', 'danger'],
                    ['cost', 'Plot Cost Summary', 'Aggregated cost components across all plots.', 'mdi-chart-box-outline', 'warning'],
                ];
            @endphp
            @foreach ($reports as [$slug, $name, $desc, $icon, $color])
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="{{ url("/plots/reports/{$slug}") }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <span class="badge bg-label-{{ $color }} rounded p-2 mb-2"><i class="mdi {{ $icon }} mdi-24px"></i></span>
                            <h6 class="mb-1">{{ $name }}</h6>
                            <p class="text-muted small mb-0">{{ $desc }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
