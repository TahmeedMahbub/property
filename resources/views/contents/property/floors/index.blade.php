@extends('contents.body')

@section('title', 'Floors')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Floors</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Floors</h4>
            <a href="{{ url('/floors/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Floor
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
                <form method="GET" action="{{ url('/floors') }}" class="row g-2">
                    <div class="col-12 col-md-5">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search floors...">
                    </div>
                    <div class="col-12 col-md-4">
                        <select name="building" class="form-select">
                            <option value="">All Buildings</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->uuid }}" {{ request('building') == $building->uuid ? 'selected' : '' }}>
                                    {{ $building->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-12 col-md-3 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary flex-fill">
                            <i class="mdi mdi-magnify me-1"></i> Filter
                        </button>
                        @if(request('search') || request('building'))
                            <a href="{{ url('/floors') }}" class="btn btn-outline-danger">
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
                            <th>Floor</th>
                            <th>Building</th>
                            <th class="d-none d-md-table-cell">Project</th>
                            <th>Units</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($floors as $floor)
                            <tr>
                                <td class="fw-medium">{{ $floor->name }}</td>
                                <td>{{ $floor->building->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $floor->project->name ?? '—' }}</td>
                                <td>{{ $floor->total_units }}</td>
                                <td class="text-end">
                                    <a href="{{ url("/floors/{$floor->uuid}/edit") }}"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </a>
                                    <form method="POST" action="{{ url("/floors/{$floor->uuid}") }}"
                                        class="d-inline" onsubmit="return confirm('Delete this floor?')">
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
                                <td colspan="5" class="text-center text-muted py-4">
                                    No floors found. <a href="{{ url('/floors/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($floors->hasPages())
                <div class="card-footer">
                    {{ $floors->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
