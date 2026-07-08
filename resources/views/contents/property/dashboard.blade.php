@extends('contents.body')

@section('title', 'Dashboard')

@section('content')
<div class="row gy-4">
    {{-- Stats Cards --}}
    <div class="col-6 col-xl-3">
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
    <div class="col-6 col-xl-3">
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
    <div class="col-6 col-xl-3">
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
    <div class="col-6 col-xl-3">
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
                <span class="badge bg-label-secondary">From ledger</span>
            </div>
            <div class="card-body">
                <div class="d-flex align-items-center mb-4">
                    <div class="avatar avatar-lg bg-label-success rounded d-flex align-items-center justify-content-center me-3">
                        <i class="mdi mdi-bank-outline mdi-36px"></i>
                    </div>
                    <div>
                        <small class="text-muted">Total Company Asset</small>
                        <h2 class="fw-bold mb-0 placeholder-wave" id="stat-asset-value"><span class="placeholder col-4"></span></h2>
                    </div>
                </div>
                <div class="row g-3">
                    <div class="col-6 col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-label-primary rounded d-flex align-items-center justify-content-center me-2">
                                <i class="mdi mdi-account-tie-outline"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Shareholder Investment</small>
                                <span class="fw-semibold placeholder-wave" id="stat-shareholder-investment"><span class="placeholder col-6"></span></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-label-info rounded d-flex align-items-center justify-content-center me-2">
                                <i class="mdi mdi-handshake-outline"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Investor Investment</small>
                                <span class="fw-semibold placeholder-wave" id="stat-investor-investment"><span class="placeholder col-6"></span></span>
                            </div>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="d-flex align-items-center">
                            <div class="avatar avatar-sm bg-label-warning rounded d-flex align-items-center justify-content-center me-2">
                                <i class="mdi mdi-home-city-outline"></i>
                            </div>
                            <div>
                                <small class="text-muted d-block">Unit Sales</small>
                                <span class="fw-semibold placeholder-wave" id="stat-unit-sales"><span class="placeholder col-6"></span></span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Plot / Land Acquisition Overview --}}
    @if ($plotMetrics && $plotMetrics['total_plots'] > 0)
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Land Acquisition Overview</h5>
                <a href="{{ url('/plots') }}" class="btn btn-sm btn-outline-primary">Manage Plots</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-label-primary text-center">
                            <h5 class="fw-bold mb-1">{{ number_format($plotMetrics['total_plots']) }}</h5>
                            <small>Total Plots</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-label-info text-center">
                            <h5 class="fw-bold mb-1">{{ rtrim(rtrim(number_format($plotMetrics['total_land_katha'], 2), '0'), '.') }}</h5>
                            <small>Land (katha)</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-label-warning text-center">
                            <h5 class="fw-bold mb-1">৳{{ number_format($plotMetrics['total_acquisition_cost'], 0) }}</h5>
                            <small>Acquisition Cost</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-label-danger text-center">
                            <h5 class="fw-bold mb-1">৳{{ number_format($plotMetrics['total_due'], 0) }}</h5>
                            <small>Total Due</small>
                        </div>
                    </div>
                </div>
                <div class="row g-3 mt-1">
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-label-success text-center">
                            <h6 class="fw-bold mb-1">৳{{ number_format($plotMetrics['total_paid'], 0) }}</h6>
                            <small>Total Paid</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-label-secondary text-center">
                            <h6 class="fw-bold mb-1">{{ number_format($plotMetrics['bayna_pending']) }}</h6>
                            <small>Bayna Pending</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="p-3 rounded bg-label-secondary text-center">
                            <h6 class="fw-bold mb-1">{{ number_format($plotMetrics['registration_pending']) }}</h6>
                            <small>Registration Pending</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Loan Overview --}}
    @if ($loanMetrics && $loanMetrics['loan_count'] > 0)
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h5 class="card-title mb-0">Loan Overview</h5>
                <a href="{{ url('/loans') }}" class="btn btn-sm btn-outline-primary">Manage Loans</a>
            </div>
            <div class="card-body">
                <div class="row g-3">
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-label-danger text-center">
                            <h5 class="fw-bold mb-1">৳{{ number_format($loanMetrics['total_outstanding'], 0) }}</h5>
                            <small>Outstanding</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-label-primary text-center">
                            <h5 class="fw-bold mb-1">৳{{ number_format($loanMetrics['total_principal_borrowed'], 0) }}</h5>
                            <small>Borrowed</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-label-success text-center">
                            <h5 class="fw-bold mb-1">৳{{ number_format($loanMetrics['total_principal_repaid'], 0) }}</h5>
                            <small>Repaid</small>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-3 rounded bg-label-warning text-center">
                            <h5 class="fw-bold mb-1">৳{{ number_format($loanMetrics['total_interest_paid'], 0) }}</h5>
                            <small>Interest Paid</small>
                        </div>
                    </div>
                </div>

                <div class="row g-3 mt-1">
                    <div class="col-12 col-lg-6">
                        <h6 class="mb-2 text-muted small text-uppercase">Loan by Lender Type</h6>
                        <div class="table-responsive">
                            <table class="table table-sm mb-0">
                                <tbody>
                                    @forelse ($loanMetrics['by_lender_type'] as $row)
                                        <tr>
                                            <td>{{ ucwords(str_replace('_', ' ', $row['lender_type'])) }}
                                                <span class="badge bg-label-secondary">{{ $row['count'] }}</span>
                                            </td>
                                            <td class="text-end">৳{{ number_format($row['principal'], 0) }}</td>
                                            <td class="text-end text-danger">৳{{ number_format($row['outstanding'], 0) }} due</td>
                                        </tr>
                                    @empty
                                        <tr><td class="text-muted">No loans.</td></tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="col-12 col-lg-6">
                        <h6 class="mb-2 text-muted small text-uppercase">Upcoming Payments (30 days)</h6>
                        @forelse ($loanMetrics['upcoming_payments']->take(5) as $row)
                            <div class="d-flex justify-content-between align-items-center border-bottom py-2">
                                <div>
                                    <a href="{{ url("/loans/{$row['loan']->uuid}") }}" class="fw-medium">{{ $row['loan']->lender_name }}</a>
                                    <div class="small text-muted">{{ $row['kind'] === 'maturity' ? 'Maturity' : 'Installment' }}</div>
                                </div>
                                <div class="text-end">
                                    <div class="small">{{ $row['due_date']->format('d M Y') }}</div>
                                    <span class="badge bg-label-{{ $row['days_left'] <= 7 ? 'danger' : 'warning' }}">{{ $row['days_left'] }} days</span>
                                </div>
                            </div>
                        @empty
                            <p class="text-muted mb-0">No payments due in the next 30 days.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif

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
            countUp($('#stat-shareholder-investment'), parseFloat(data.shareholder_investment), {
                decimals: 2,
                suffix: ' {{ $company->currency ?? "USD" }}'
            });
            countUp($('#stat-investor-investment'), parseFloat(data.investor_investment), {
                decimals: 2,
                suffix: ' {{ $company->currency ?? "USD" }}'
            });
            countUp($('#stat-unit-sales'), parseFloat(data.unit_sales), {
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
