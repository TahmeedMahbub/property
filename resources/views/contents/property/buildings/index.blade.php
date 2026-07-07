@extends('contents.body')

@section('title', 'Buildings')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Buildings</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Buildings</h4>
            <a href="{{ url('/buildings/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Building
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
                <form method="GET" action="{{ url('/buildings') }}" class="row g-2">
                    <div class="col-12 col-md-5">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search buildings...">
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="project" class="form-select">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->uuid }}" {{ request('project') == $project->uuid ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary flex-fill">
                            <i class="mdi mdi-magnify me-1"></i> Filter
                        </button>
                        @if(request('search') || request('project'))
                            <a href="{{ url('/buildings') }}" class="btn btn-outline-danger">
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
                            <th>Name</th>
                            <th>Project</th>
                            <th class="d-none d-md-table-cell">Floors</th>
                            <th class="d-none d-md-table-cell">Units</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($buildings as $building)
                            <tr>
                                <td class="fw-medium">{{ $building->name }}</td>
                                <td>{{ $building->project->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $building->total_floors }}</td>
                                <td class="d-none d-md-table-cell">{{ $building->total_units }}</td>
                                <td>
                                    @php
                                        $statusColors = [
                                            'planning' => 'secondary',
                                            'under_construction' => 'warning',
                                            'completed' => 'success',
                                            'handed_over' => 'info',
                                        ];
                                    @endphp
                                    <span class="badge bg-label-{{ $statusColors[$building->status] ?? 'secondary' }}">
                                        {{ str_replace('_', ' ', ucfirst($building->status)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ url("/buildings/{$building->uuid}/edit") }}"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </a>
                                    <form method="POST" action="{{ url("/buildings/{$building->uuid}") }}"
                                        class="d-inline" onsubmit="return confirm('Delete this building?')">
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
                                <td colspan="6" class="text-center text-muted py-4">
                                    No buildings found. <a href="{{ url('/buildings/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($buildings->hasPages())
                <div class="card-footer">
                    {{ $buildings->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
