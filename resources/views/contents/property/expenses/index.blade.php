@extends('contents.body')

@section('title', 'Expenses')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Expenses</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="fw-bold mb-0">Expenses</h4>
            <a href="{{ url('/expenses/create') }}" class="btn btn-primary">
                <i class="mdi mdi-plus me-1"></i> Record Expense
            </a>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        {{-- Summary --}}
        <div class="row g-3 mb-4">
            <div class="col-12 col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-primary rounded p-2 me-2"><i class="mdi mdi-calendar-today"></i></span>
                        <span class="text-muted small">Today's Expense</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['today'], 2) }}</h5>
                </div></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-warning rounded p-2 me-2"><i class="mdi mdi-calendar-month"></i></span>
                        <span class="text-muted small">This Month</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['month'], 2) }}</h5>
                </div></div>
            </div>
            <div class="col-12 col-md-4">
                <div class="card h-100"><div class="card-body">
                    <div class="d-flex align-items-center mb-1">
                        <span class="badge bg-label-danger rounded p-2 me-2"><i class="mdi mdi-cash-remove"></i></span>
                        <span class="text-muted small">Total Expense</span>
                    </div>
                    <h5 class="mb-0">৳{{ number_format($metrics['total'], 2) }}</h5>
                </div></div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <form method="GET" action="{{ url('/expenses') }}" class="row g-2">
                    <div class="col-6 col-md-3">
                        <select name="category_id" class="form-select">
                            <option value="">All Categories</option>
                            @foreach ($categories as $cat)
                                <option value="{{ $cat->id }}" {{ (string) request('category_id') === (string) $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-3">
                        <select name="source" class="form-select">
                            <option value="">All Sources</option>
                            @foreach (['company' => 'Company', 'plot' => 'Plot', 'project' => 'Project', 'booking' => 'Booking'] as $val => $lbl)
                                <option value="{{ $val }}" {{ request('source') === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-6 col-md-2">
                        <input type="date" name="date_from" value="{{ request('date_from') }}" class="form-control" title="From date">
                    </div>
                    <div class="col-6 col-md-2">
                        <input type="date" name="date_to" value="{{ request('date_to') }}" class="form-control" title="To date">
                    </div>
                    <div class="col-12 col-md-2 d-flex gap-2">
                        <button type="submit" class="btn btn-outline-secondary w-100"><i class="mdi mdi-magnify"></i></button>
                        @if (request()->hasAny(['category_id', 'source', 'date_from', 'date_to']))
                            <a href="{{ url('/expenses') }}" class="btn btn-outline-danger"><i class="mdi mdi-close"></i></a>
                        @endif
                    </div>
                </form>
            </div>

            <div class="table-responsive">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Category</th>
                            <th>Title</th>
                            <th>Source</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($expenses as $expense)
                            <tr>
                                <td class="text-nowrap">{{ $expense->expense_date->format('d M Y') }}</td>
                                <td><span class="badge bg-label-secondary">{{ $expense->category_name }}</span></td>
                                <td><a href="{{ url("/expenses/{$expense->uuid}") }}">{{ $expense->title ?: '—' }}</a></td>
                                <td>
                                    <span class="badge bg-label-{{ $expense->source_color }}">{{ $expense->source_type_label }}</span>
                                    @if ($expense->source_name)<div class="small text-muted text-truncate" style="max-width:160px">{{ $expense->source_name }}</div>@endif
                                </td>
                                <td class="text-end fw-medium text-danger">৳{{ number_format($expense->amount, 2) }}</td>
                                <td class="text-end">
                                    <div class="dropdown">
                                        <button type="button" class="btn btn-sm btn-icon btn-text-secondary rounded-pill dropdown-toggle hide-arrow"
                                            data-bs-toggle="dropdown" aria-expanded="false">
                                            <i class="mdi mdi-dots-vertical"></i>
                                        </button>
                                        <div class="dropdown-menu dropdown-menu-end">
                                            <a class="dropdown-item" href="{{ url("/expenses/{$expense->uuid}") }}">
                                                <i class="mdi mdi-eye-outline me-1"></i> View
                                            </a>
                                            <a class="dropdown-item" href="{{ url("/expenses/{$expense->uuid}/edit") }}">
                                                <i class="mdi mdi-pencil-outline me-1"></i> Edit
                                            </a>
                                            <div class="dropdown-divider"></div>
                                            <form method="POST" action="{{ url("/expenses/{$expense->uuid}") }}"
                                                onsubmit="return confirm('Delete this expense?')">
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
                                <td colspan="6" class="text-center text-muted py-4">
                                    No expenses found. <a href="{{ url('/expenses/create') }}">Record one</a>.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($expenses->hasPages())
                <div class="card-footer">{{ $expenses->links() }}</div>
            @endif
        </div>
    </div>
</div>
@endsection
