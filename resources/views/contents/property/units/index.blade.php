@extends('contents.body')

@section('title', 'Units')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Units</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Units</h4>
            <a href="{{ url('/units/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Unit
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
                <form method="GET" action="{{ url('/units') }}" class="row g-2">
                    <div class="col-6 col-md-3">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Unit number...">
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(['available', 'reserved', 'booked', 'sold', 'handovered'] as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                    {{ ucfirst($s) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="building" class="form-select">
                            <option value="">All Buildings</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->uuid }}" {{ request('building') == $building->uuid ? 'selected' : '' }}>
                                    {{ $building->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <select name="unit_type" class="form-select">
                            <option value="">All Types</option>
                            @foreach($unitTypes as $type)
                                <option value="{{ $type->uuid }}" {{ request('unit_type') == $type->uuid ? 'selected' : '' }}>
                                    {{ $type->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary flex-fill">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        @if(request('search') || request('status') || request('building') || request('unit_type'))
                            <a href="{{ url('/units') }}" class="btn btn-outline-danger">
                                <i class="mdi mdi-close"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Unit #</th>
                            <th>Building</th>
                            <th class="d-none d-md-table-cell">Floor</th>
                            <th class="d-none d-md-table-cell">Type</th>
                            <th class="d-none d-lg-table-cell">Size</th>
                            <th>Price</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($units as $unit)
                            <tr>
                                <td class="fw-medium">{{ $unit->unit_number }}</td>
                                <td>{{ $unit->building->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $unit->floor->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $unit->unitType->name ?? '—' }}</td>
                                <td class="d-none d-lg-table-cell">{{ $unit->size ? number_format($unit->size) . ' sqft' : '—' }}</td>
                                <td>{{ $unit->price ? '৳' . number_format($unit->price) : '—' }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'available' => 'success',
                                            'reserved' => 'warning',
                                            'booked' => 'info',
                                            'sold' => 'primary',
                                            'handovered' => 'secondary',
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$unit->status] ?? 'secondary' }}">
                                        {{ ucfirst($unit->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ url("/units/{$unit->uuid}/edit") }}"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </a>
                                    <form method="POST" action="{{ url("/units/{$unit->uuid}") }}"
                                        class="d-inline" onsubmit="return confirm('Delete this unit?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-icon btn-text-danger">
                                            <i class="mdi mdi-delete-outline"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted py-4">
                                    No units found. <a href="{{ url('/units/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($units->hasPages())
                <div class="card-footer">
                    {{ $units->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
