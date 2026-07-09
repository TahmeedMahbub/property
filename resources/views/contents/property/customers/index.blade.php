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
            <div class="alert alert-success alert-dismissible d-none" role="alert">
                {{ session('success') }}
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
                        <select name="status" class="form-select">
                            <option value="">All Statuses</option>
                            <option value="lead" {{ request('status') == 'lead' ? 'selected' : '' }}>Lead</option>
                            <option value="customer" {{ request('status') == 'customer' ? 'selected' : '' }}>Customer</option>
                            <option value="verified" {{ request('status') == 'verified' ? 'selected' : '' }}>Verified</option>
                        </select>
                    </div>
                    <div class="col-2 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="mdi mdi-magnify"></i>
                        </button>
                        @if(request('search') || request('status'))
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
                            <th class="d-none d-md-table-cell">Profile</th>
                            <th>Status</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($customers as $customer)
                            @php
                                $completion = $customer->profile_completion;
                                $barColor = $completion >= 100 ? 'success' : ($completion >= 50 ? 'info' : 'warning');
                                $statusColor = ['lead' => 'warning', 'customer' => 'primary', 'verified' => 'success'][$customer->status] ?? 'secondary';
                            @endphp
                            <tr>
                                <td class="fw-medium">{{ $customer->name }}</td>
                                <td>{{ $customer->phone ?? '—' }}</td>
                                <td class="d-none d-md-table-cell">{{ $customer->email ?? '—' }}</td>
                                <td class="d-none d-md-table-cell" style="min-width: 120px;">
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="progress flex-grow-1" style="height: 6px;">
                                            <div class="progress-bar bg-{{ $barColor }}"
                                                style="width: {{ $completion }}%;"></div>
                                        </div>
                                        <small class="text-muted">{{ $completion }}%</small>
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-label-{{ $statusColor }}">
                                        {{ ucfirst($customer->status) }}
                                    </span>
                                </td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <ul class="dropdown-menu dropdown-menu-end">
                                            <li>
                                                <a class="dropdown-item" href="{{ url("/customers/{$customer->uuid}/edit") }}">
                                                    <i class="mdi mdi-pencil-outline me-1"></i> Edit
                                                </a>
                                            </li>
                                            @if ($customer->hasProfileLink())
                                                <li>
                                                    <a class="dropdown-item" href="#"
                                                        data-copy-link="{{ $customer->profile_link }}">
                                                        <i class="mdi mdi-content-copy me-1"></i> Copy Profile Link
                                                    </a>
                                                </li>
                                            @endif
                                            <li>
                                                <form method="POST"
                                                    action="{{ route('customers.profile-link.regenerate', $customer->uuid) }}">
                                                    @csrf
                                                    <button type="submit" class="dropdown-item">
                                                        <i class="mdi mdi-refresh me-1"></i>
                                                        {{ $customer->hasProfileLink() ? 'Regenerate Profile Link' : 'Generate Profile Link' }}
                                                    </button>
                                                </form>
                                            </li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li>
                                                <form method="POST" action="{{ url("/customers/{$customer->uuid}") }}"
                                                    onsubmit="return confirm('Delete this customer?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="dropdown-item text-danger">
                                                        <i class="mdi mdi-delete-outline me-1"></i> Delete
                                                    </button>
                                                </form>
                                            </li>
                                        </ul>
                                    </div>
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

@include('contents.property.customers._link_scripts')
@endsection
