@extends('contents.body')

@section('title', 'Dashboard')

@section('content')
<div class="row gy-4">
    {{-- Stats Cards --}}
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Projects</h6>
                        <h4 class="fw-bold mb-0">{{ $stats['projects'] }}</h4>
                    </div>
                    <div class="avatar avatar-md bg-label-primary rounded">
                        <i class="mdi mdi-city-variant-outline mdi-24px"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Buildings</h6>
                        <h4 class="fw-bold mb-0">{{ $stats['buildings'] }}</h4>
                    </div>
                    <div class="avatar avatar-md bg-label-info rounded">
                        <i class="mdi mdi-domain mdi-24px"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Total Units</h6>
                        <h4 class="fw-bold mb-0">{{ $stats['total_units'] }}</h4>
                    </div>
                    <div class="avatar avatar-md bg-label-success rounded">
                        <i class="mdi mdi-office-building-outline mdi-24px"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="d-flex align-items-center justify-content-between">
                    <div>
                        <h6 class="text-muted mb-1">Available</h6>
                        <h4 class="fw-bold mb-0">{{ $stats['available_units'] }}</h4>
                    </div>
                    <div class="avatar avatar-md bg-label-warning rounded">
                        <i class="mdi mdi-check-circle-outline mdi-24px"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Status Overview --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Unit Status Overview</h5>
                <a href="{{ url('/units') }}" class="btn btn-sm btn-outline-primary">View All Units</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md">
                        <div class="p-3 rounded bg-label-success text-center">
                            <h5 class="fw-bold mb-1">{{ $stats['available_units'] }}</h5>
                            <small>Available</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-3 rounded bg-label-warning text-center">
                            <h5 class="fw-bold mb-1">{{ $stats['reserved_units'] }}</h5>
                            <small>Reserved</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-3 rounded bg-label-info text-center">
                            <h5 class="fw-bold mb-1">{{ $stats['booked_units'] }}</h5>
                            <small>Booked</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-3 rounded bg-label-primary text-center">
                            <h5 class="fw-bold mb-1">{{ $stats['sold_units'] }}</h5>
                            <small>Sold</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
