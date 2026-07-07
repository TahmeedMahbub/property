@extends('contents.body')

@section('title', 'Members')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Members</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Members</h4>
            <a href="{{ url('/members/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Member
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
                <form method="GET" action="{{ url('/members') }}" class="row g-2">
                    <div class="col-6 col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search by name or email...">
                    </div>
                    <div class="col-4 col-md-3">
                        <select name="status" class="form-select">
                            <option value="">All Status</option>
                            <option value="active" {{ request('status') == 'active' ? 'selected' : '' }}>Active</option>
                            <option value="inactive" {{ request('status') == 'inactive' ? 'selected' : '' }}>Inactive</option>
                        </select>
                    </div>
                    <div class="col-2 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        @if(request('search') || request('status'))
                            <a href="{{ url('/members') }}" class="btn btn-outline-danger">
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
                            <th>Email</th>
                            <th class="d-none d-md-table-cell">Role</th>
                            <th class="d-none d-md-table-cell">Title</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($members as $member)
                            <tr>
                                <td class="fw-medium">
                                    {{ $member->user->name ?? '—' }}
                                    @if($member->is_owner)
                                        <span class="badge bg-label-warning ms-1">Owner</span>
                                    @endif
                                </td>
                                <td>{{ $member->user->email ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $member->role->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $member->title ?? '—' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $member->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($member->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ url("/members/{$member->id}/edit") }}"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </a>
                                    <form method="POST" action="{{ url("/members/{$member->id}") }}"
                                        class="d-inline" onsubmit="return confirm('Remove this member?')">
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
                                    No members found. <a href="{{ url('/members/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($members->hasPages())
                <div class="card-footer">{{ $members->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
