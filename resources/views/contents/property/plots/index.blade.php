@extends('contents.body')

@section('title', 'Plots')

@section('content')
<style>
    .plots-table { overflow: visible; }
</style>
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Plots</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="fw-bold mb-0">Land Acquisition (Plots)</h4>
            <div class="d-flex gap-2">
                <a href="{{ url('/plots/reports') }}" class="btn btn-outline-secondary">
                    <i class="mdi mdi-file-chart-outline me-1"></i> Reports
                </a>
                <a href="{{ url('/plots/create') }}" class="btn btn-primary">
                    <i class="mdi mdi-plus me-1"></i> Add Plot
                </a>
            </div>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Metrics --}}
        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-primary rounded p-2 me-2"><i class="mdi mdi-map-marker-outline"></i></span>
                        <span class="text-muted small">Total Plots</span>
                    </div>
                    <h5 class="mb-0">{{ number_format($metrics['total_plots']) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-info rounded p-2 me-2"><i class="mdi mdi-vector-square"></i></span>
                        <span class="text-muted small">Total Land (katha)</span>
                    </div>
                    <h5 class="mb-0">{{ rtrim(rtrim(number_format($metrics['total_land_katha'], 4), '0'), '.') }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-warning rounded p-2 me-2"><i class="mdi mdi-cash-multiple"></i></span>
                        <span class="text-muted small">Acquisition Cost</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['total_acquisition_cost'], 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-danger rounded p-2 me-2"><i class="mdi mdi-cash-remove"></i></span>
                        <span class="text-muted small">Total Due</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['total_due'], 2) }}</h5>
                </div></div>
            </div>
        </div>

        <div class="row g-3 mb-4">
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Paid</div>
                    <h6 class="mb-0 text-success">৳{{ number_format($metrics['total_paid'], 2) }}</h6>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Bayna Pending</div>
                    <h6 class="mb-0">{{ number_format($metrics['bayna_pending']) }} plots</h6>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Registration Pending</div>
                    <h6 class="mb-0">{{ number_format($metrics['registration_pending']) }} plots</h6>
                </div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ url('/plots') }}" class="row g-2">
                    <div class="col-12 col-md-5">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search code, name, mouza, district...">
                    </div>
                    <div class="col-8 col-md-5">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach (\App\Models\Plot::STATUSES as $st)
                                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                                    {{ ucwords(str_replace('_', ' ', $st)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary w-100"><i class="mdi mdi-magnify"></i></button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ url('/plots') }}" class="btn btn-outline-danger"><i class="mdi mdi-close"></i></a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive plots-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Plot</th>
                            <th class="d-none d-md-table-cell">Location</th>
                            <th class="text-end d-none d-md-table-cell">Land</th>
                            <th class="text-end">Acquisition Cost</th>
                            <th class="text-end">Due</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($plots as $plot)
                            @php
                                $paid = (float) ($plot->paid_total ?? 0);
                                $cost = $plot->total_acquisition_cost;
                                $due = round($cost - $paid, 2);
                            @endphp
                            <tr>
                                <td class="fw-medium">
                                    <a href="{{ url("/plots/{$plot->uuid}") }}">{{ $plot->plot_name }}</a>
                                    <div class="small text-muted">{{ $plot->plot_code }}</div>
                                </td>
                                <td class="d-none d-md-table-cell">
                                    {{ collect([$plot->area, $plot->upazila, $plot->district])->filter()->implode(', ') ?: '—' }}
                                </td>
                                <td class="text-end d-none d-md-table-cell text-nowrap">
                                    {{ rtrim(rtrim(number_format($plot->land_size, 4), '0'), '.') }} {{ $plot->land_unit }}
                                </td>
                                <td class="text-end">৳{{ number_format($cost, 2) }}</td>
                                <td class="text-end fw-medium {{ $due > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($due, 2) }}</td>
                                <td>
                                    <span class="badge bg-label-info">{{ ucwords(str_replace('_', ' ', $plot->status)) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="{{ url("/plots/{$plot->uuid}") }}">
                                                <i class="mdi mdi-eye-outline me-1"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ url("/plots/{$plot->uuid}/payments/create") }}">
                                                <i class="mdi mdi-cash-plus me-1"></i> Add Payment
                                            </a>
                                            <a class="dropdown-item" href="{{ url("/plots/{$plot->uuid}/edit") }}">
                                                <i class="mdi mdi-pencil-outline me-1"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ url("/plots/{$plot->uuid}") }}"
                                                onsubmit="return confirm('Delete this plot and all its payments?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="dropdown-item text-danger">
                                                    <i class="mdi mdi-delete-outline me-1"></i> Delete
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center text-muted py-4">
                                    No plots found. <a href="{{ url('/plots/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($plots->hasPages())
                <div class="card-footer">{{ $plots->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
