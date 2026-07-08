@extends('contents.body')

@section('title', 'Shareholders')

@section('content')
<style>
    /* Let the actions three-dot dropdown overflow the table instead of being clipped. */
    .shareholders-table {
        overflow: visible;
    }
</style>
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Shareholders</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Shareholders</h4>
            <a href="{{ url('/shareholders/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Shareholder
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
                <form method="GET" action="{{ url('/shareholders') }}" class="row g-2">
                    <div class="col-8 col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Search shareholders...">
                    </div>
                    <div class="col-4 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        @if(request('search'))
                            <a href="{{ url('/shareholders') }}" class="btn btn-outline-danger">
                                <i class="mdi mdi-close"></i>
                            </a>
                        @endif
                    </div>
                </form>
            </div>
            <div class="table-responsive shareholders-table">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th class="d-none d-md-table-cell">Email</th>
                            {{-- <th class="d-none d-md-table-cell">Shares</th> --}}
                            <th>Ownership %</th>
                            <th class="d-none d-md-table-cell">Amount</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($shareholders as $sh)
                            <tr>
                                <td class="fw-medium">{{ $sh->name }}</td>
                                <td class="d-none d-md-table-cell">{{ $sh->email ?? '—' }}</td>
                                {{-- <td class="d-none d-md-table-cell">{{ $sh->shares_owned ? rtrim(rtrim(number_format($sh->shares_owned, 6), '0'), '.') : '—' }}</td> --}}
                                <td>{{ $sh->ownership_percentage > 0 ? rtrim(rtrim(number_format($sh->ownership_percentage, 6), '0'), '.') . '%' : '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $sh->share_amount ? '৳' . number_format($sh->share_amount) : '—' }}</td>
                                <td>
                                    <span class="badge bg-label-{{ $sh->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($sh->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="{{ url("/shareholders/{$sh->uuid}/investment") }}">
                                                <i class="mdi mdi-cash-multiple me-1"></i> Manage Investment
                                            </a>
                                            <a class="dropdown-item" href="{{ url("/shareholders/{$sh->uuid}/edit") }}">
                                                <i class="mdi mdi-pencil-outline me-1"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ url("/shareholders/{$sh->uuid}") }}"
                                                onsubmit="return confirm('Delete this shareholder?')">
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
                                <td colspan="7" class="text-center text-muted py-4">
                                    No shareholders found. <a href="{{ url('/shareholders/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($shareholders->hasPages())
                <div class="card-footer">{{ $shareholders->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
