@extends('contents.body')

@section('title', 'Customers')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Customers</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center mb-3">
            <h4 class="fw-bold mb-0">Customers</h4>
            <a href="{{ url('/customers/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Add Customer
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
                <form method="GET" action="{{ url('/customers') }}" class="row g-2">
                    <div class="col-5 col-md-4">
                        <input type="text" name="search" value="{{ request('search') }}"
                            class="form-control" placeholder="Name, phone, email...">
                    </div>
                    <div class="col-5 col-md-3">
                        <select name="type" class="form-select">
                            <option value="">All Types</option>
                            <option value="individual" {{ request('type') == 'individual' ? 'selected' : '' }}>Individual</option>
                            <option value="business" {{ request('type') == 'business' ? 'selected' : '' }}>Business</option>
                        </select>
                    </div>
                    <div class="col-2 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        @if(request('search') || request('type'))
                            <a href="{{ url('/customers') }}" class="btn btn-outline-danger">
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
                            <th>Phone</th>
                            <th class="d-none d-md-table-cell">Email</th>
                            <th class="d-none d-md-table-cell">Type</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            <tr>
                                <td class="fw-medium">{{ $customer->name }}</td>
                                <td>{{ $customer->phone ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $customer->email ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">
                                    <span class="badge bg-label-{{ $customer->type === 'business' ? 'info' : 'secondary' }}">
                                        {{ ucfirst($customer->type) }}
                                    </span>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $customer->status === 'active' ? 'success' : 'secondary' }}">
                                        {{ ucfirst($customer->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <a href="{{ url("/customers/{$customer->uuid}/edit") }}"
                                        class="btn btn-sm btn-icon btn-text-secondary">
                                        <i class="mdi mdi-pencil-outline"></i>
                                    </a>
                                    <form method="POST" action="{{ url("/customers/{$customer->uuid}") }}"
                                        class="d-inline" onsubmit="return confirm('Delete this customer?')">
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
                                    No customers found. <a href="{{ url('/customers/create') }}">Add one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($customers->hasPages())
                <div class="card-footer">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
