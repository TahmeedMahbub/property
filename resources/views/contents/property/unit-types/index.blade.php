@extends('contents.body')

@section('title', 'Unit Types')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        {{-- Breadcrumb --}}
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Unit Types</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Unit Types</h4>
            <a href="{{ url('/unit-types/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Type
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
                <form method="GET" action="{{ url('/unit-types') }}" class="d-flex gap-2">
                    <input type="text" name="search" value="{{ request('search') }}"
                        class="form-control" placeholder="Search unit types...">
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="mdi mdi-magnify"></i>
                    </button>
                    @if(request('search'))
                        <a href="{{ url('/unit-types') }}" class="btn btn-outline-danger">
                            <i class="mdi mdi-close"></i>
                        </a>
                    @endif
                </form>
            </div>
            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Description</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($unitTypes as $type)
                            <tr>
                                <td class="fw-medium">{{ $type->name }}</td>
                                <td class="text-muted">{{ Str::limit($type->description, 50) ?: '—' }}</td>
                                <td>
                                    @if ($type->is_active)
                                        <span class="badge bg-label-success">Active</span>
                                    @else
                                        <span class="badge bg-label-secondary">Inactive</span>
                                    @endif
                                </td>
                                <td class="text-end">
                                    <a href="{{ url("/unit-types/{$type->uuid}/edit") }}"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </a>
                                    <form method="POST" action="{{ url("/unit-types/{$type->uuid}") }}"
                                        class="d-inline" onsubmit="return confirm('Delete this unit type?')">
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
                                <td colspan="4" class="text-center text-muted py-4">
                                    No unit types found. <a href="{{ url('/unit-types/create') }}">Create one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($unitTypes->hasPages())
                <div class="card-footer">
                    {{ $unitTypes->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
