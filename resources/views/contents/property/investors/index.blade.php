@extends('contents.body')

@section('title', 'Investors')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Investors</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Investors</h4>
            <a href="{{ url('/investors/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Investor
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
                <form method="GET" action="{{ url('/investors') }}" class="row g-2">
                    <div class="col-5 col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search investors...">
                    </div>
                    <div class="col-5 col-md-3">
                        <select name="project" class="form-select">
                            <option value="">All Projects</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->uuid }}" {{ request('project') == $project->uuid ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-2 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        @if(request('search') || request('project'))
                            <a href="{{ url('/investors') }}" class="btn btn-outline-danger">
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
                            <th class="d-none d-md-table-cell">Amount</th>
                            <th class="d-none d-md-table-cell">%</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($investors as $investor)
                            <tr>
                                <td class="fw-medium">{{ $investor->name }}</td>
                                <td>{{ $investor->project->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $investor->investment_amount ? '৳' . number_format($investor->investment_amount) : '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $investor->investment_percentage ? $investor->investment_percentage . '%' : '—' }}</td>
                                <td>
                                    @php
                                        $colors = ['active' => 'success', 'inactive' => 'secondary', 'exited' => 'warning'];
                                    @endphp
                                    <span class="badge bg-label-{{ $colors[$investor->status] ?? 'secondary' }}">
                                        {{ ucfirst($investor->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ url("/investors/{$investor->uuid}/edit") }}"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </a>
                                    <form method="POST" action="{{ url("/investors/{$investor->uuid}") }}"
                                        class="d-inline" onsubmit="return confirm('Delete this investor?')">
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
                                    No investors found. <a href="{{ url('/investors/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($investors->hasPages())
                <div class="card-footer">{{ $investors->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
