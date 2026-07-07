@extends('contents.body')

@section('title', 'Projects')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Projects</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Projects</h4>
            <a href="{{ url('/projects/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Project
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
                <form method="GET" action="{{ url('/projects') }}" class="row g-2">
                    <div class="col-6 col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search projects...">
                    </div>
                    <div class="col-4 col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            @foreach(['planning', 'active', 'on_hold', 'completed', 'cancelled'] as $s)
                                <option value="{{ $s }}" {{ request('status') == $s ? 'selected' : '' }}>
                                    {{ ucfirst(str_replace('_', ' ', $s)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-2 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        @if(request('search') || request('status'))
                            <a href="{{ url('/projects') }}" class="btn btn-outline-danger">
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
                            <th class="d-none d-md-table-cell">Location</th>
                            <th class="d-none d-md-table-cell">Budget</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($projects as $project)
                            <tr>
                                <td class="fw-medium">{{ $project->name }}</td>
                                <td class="d-none d-md-table-cell">{{ $project->city ?? $project->location ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $project->budget ? '৳' . number_format($project->budget) : '—' }}</td>
                                <td>
                                    @php
                                        $colors = ['planning' => 'info', 'active' => 'success', 'on_hold' => 'warning', 'completed' => 'primary', 'cancelled' => 'danger'];
                                    @endphp
                                    <span class="badge bg-label-{{ $colors[$project->status] ?? 'secondary' }}">
                                        {{ ucfirst(str_replace('_', ' ', $project->status)) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ url("/projects/{$project->uuid}/edit") }}"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </a>
                                    <form method="POST" action="{{ url("/projects/{$project->uuid}") }}"
                                        class="d-inline" onsubmit="return confirm('Delete this project?')">
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
                                    No projects found. <a href="{{ url('/projects/create') }}">Create one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($projects->hasPages())
                <div class="card-footer">{{ $projects->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
