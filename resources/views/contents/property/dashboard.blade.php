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
                        <h4 class="fw-bold mb-0 placeholder-wave" id="stat-projects"><span class="placeholder col-4"></span></h4>
                    </div>
                    <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center">
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
                        <h4 class="fw-bold mb-0 placeholder-wave" id="stat-buildings"><span class="placeholder col-4"></span></h4>
                    </div>
                    <div class="avatar avatar-md bg-label-info rounded d-flex align-items-center justify-content-center">
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
                        <h4 class="fw-bold mb-0 placeholder-wave" id="stat-total-units"><span class="placeholder col-4"></span></h4>
                    </div>
                    <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center">
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
                        <h4 class="fw-bold mb-0 placeholder-wave" id="stat-available-units"><span class="placeholder col-4"></span></h4>
                    </div>
                    <div class="avatar avatar-md bg-label-warning rounded d-flex align-items-center justify-content-center">
                        <i class="mdi mdi-check-circle-outline mdi-24px"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Company Asset Card --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Company Asset</h5>
                <span class="badge bg-label-secondary">Owned inventory (unsold)</span>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-label-success rounded d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-cash-multiple mdi-24px"></i>
                            </div>
                            <div>
                                <small class="text-muted">Owned Inventory Value</small>
                                <h3 class="fw-bold mb-0 placeholder-wave" id="stat-asset-value"><span class="placeholder col-6"></span></h3>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-label-primary rounded d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-handshake-outline mdi-24px"></i>
                            </div>
                            <div>
                                <small class="text-muted">Sold Value</small>
                                <h4 class="fw-bold mb-0 placeholder-wave" id="stat-sold-value"><span class="placeholder col-6"></span></h4>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-md bg-label-info rounded d-flex align-items-center justify-content-center me-3">
                                <i class="mdi mdi-chart-box-outline mdi-24px"></i>
                            </div>
                            <div>
                                <small class="text-muted">Total Portfolio Value</small>
                                <h4 class="fw-bold mb-0 placeholder-wave" id="stat-portfolio-value"><span class="placeholder col-6"></span></h4>
                            </div>
                        </div>
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
                            <h5 class="fw-bold mb-1 placeholder-wave" id="stat-status-available"><span class="placeholder col-3"></span></h5>
                            <small>Available</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-3 rounded bg-label-warning text-center">
                            <h5 class="fw-bold mb-1 placeholder-wave" id="stat-status-reserved"><span class="placeholder col-3"></span></h5>
                            <small>Reserved</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-3 rounded bg-label-info text-center">
                            <h5 class="fw-bold mb-1 placeholder-wave" id="stat-status-booked"><span class="placeholder col-3"></span></h5>
                            <small>Booked</small>
                        </div>
                    </div>
                    <div class="col-6 col-md">
                        <div class="p-3 rounded bg-label-primary text-center">
                            <h5 class="fw-bold mb-1 placeholder-wave" id="stat-status-sold"><span class="placeholder col-3"></span></h5>
                            <small>Sold</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('page-script')
<script>
$(function () {
    // Animate a number counting up from 0 to its final value.
    function countUp($el, target, opts) {
        opts = opts || {};
        var decimals = opts.decimals || 0;
        var suffix = opts.suffix || '';
        var duration = 900;
        var start = null;

        $el.removeClass('placeholder-wave');

        function fmt(val) {
            return val.toLocaleString(undefined, {
                minimumFractionDigits: decimals,
                maximumFractionDigits: decimals
            }) + suffix;
        }

        function step(ts) {
            if (start === null) start = ts;
            var progress = Math.min((ts - start) / duration, 1);
            // easeOutCubic for a smooth deceleration
            var eased = 1 - Math.pow(1 - progress, 3);
            $el.text(fmt(target * eased));
            if (progress < 1) {
                requestAnimationFrame(step);
            } else {
                $el.text(fmt(target));
            }
        }

        requestAnimationFrame(step);
    }

    $.get("{{ route('dashboard.stats') }}")
        .done(function (data) {
            countUp($('#stat-projects'), data.projects);
            countUp($('#stat-buildings'), data.buildings);
            countUp($('#stat-total-units'), data.total_units);
            countUp($('#stat-available-units'), data.available_units);
            countUp($('#stat-asset-value'), parseFloat(data.total_asset_value), {
                decimals: 2,
                suffix: ' {{ $company->currency ?? "USD" }}'
            });
            countUp($('#stat-sold-value'), parseFloat(data.sold_value), {
                decimals: 2,
                suffix: ' {{ $company->currency ?? "USD" }}'
            });
            countUp($('#stat-portfolio-value'), parseFloat(data.portfolio_value), {
                decimals: 2,
                suffix: ' {{ $company->currency ?? "USD" }}'
            });
            countUp($('#stat-status-available'), data.available_units);
            countUp($('#stat-status-reserved'), data.reserved_units);
            countUp($('#stat-status-booked'), data.booked_units);
            countUp($('#stat-status-sold'), data.sold_units);
        })
        .fail(function () {
            $('.placeholder-wave').removeClass('placeholder-wave')
                .html('<span class="text-danger">—</span>');
        });
});
</script>
@endsection
