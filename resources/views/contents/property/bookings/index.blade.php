@extends('contents.body')

@section('title', 'Bookings')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Bookings</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="fw-bold mb-0">Plot Share Bookings</h4>
            <a href="{{ url('/bookings/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> New Booking
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ url('/bookings') }}" class="row g-2">
                    <div class="col-12 col-md-6">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search booking no, customer, plot...">
                    </div>
                    <div class="col-8 col-md-4">
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            @foreach (\App\Models\PlotBooking::STATUSES as $st)
                                <option value="{{ $st }}" {{ request('status') === $st ? 'selected' : '' }}>
                                    {{ ucfirst($st) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-4 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary w-100"><i class="mdi mdi-magnify"></i></button>
                        @if (request()->hasAny(['search', 'status']))
                            <a href="{{ url('/bookings') }}" class="btn btn-outline-danger"><i class="mdi mdi-close"></i></a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Booking</th>
                            <th>Customer</th>
                            <th class="d-none d-md-table-cell">Plot</th>
                            <th class="text-end">Shares</th>
                            <th class="text-end">Payable</th>
                            <th class="text-end">Due</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bookings as $booking)
                            <tr>
                                <td class="fw-medium">
                                    <a href="{{ url("/bookings/{$booking->uuid}") }}">{{ $booking->booking_no }}</a>
                                    <div class="small text-muted">{{ optional($booking->booking_date)->format('d M Y') }}</div>
                                </td>
                                <td>{{ $booking->customer?->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">
                                    {{ $booking->plot?->plot_name ?? '—' }}
                                    <div class="small text-muted">{{ $booking->plot?->plot_code }}</div>
                                </td>
                                <td class="text-end">{{ $booking->shares_count }}</td>
                                <td class="text-end">৳{{ number_format($booking->total_payable, 2) }}</td>
                                <td class="text-end fw-medium {{ $booking->total_due > 0 ? 'text-danger' : 'text-success' }}">
                                    ৳{{ number_format($booking->total_due, 2) }}
                                </td>
                                <td>
                                    @php
                                        $statusColors = ['booked' => 'info', 'active' => 'primary', 'completed' => 'success', 'cancelled' => 'secondary'];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$booking->status] ?? 'secondary' }}">{{ ucfirst($booking->status) }}</span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="{{ url("/bookings/{$booking->uuid}") }}">
                                                <i class="mdi mdi-eye-outline me-1"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ url("/bookings/{$booking->uuid}/edit") }}">
                                                <i class="mdi mdi-pencil-outline me-1"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ url("/bookings/{$booking->uuid}") }}"
                                                onsubmit="return confirm('Delete this booking and reverse all its payments?')">
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
                                <td colspan="8" class="text-center text-muted py-4">
                                    No bookings found. <a href="{{ url('/bookings/create') }}">Create one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($bookings->hasPages())
                <div class="card-footer">{{ $bookings->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
