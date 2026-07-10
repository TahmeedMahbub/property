@extends('contents.body')

@section('title', 'Expense Detail')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/expenses') }}">Expenses</a></li>
                <li class="breadcrumb-item active">Detail</li>
            </ol>
        </nav>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <h4 class="fw-bold mb-0">{{ $expense->title ?: $expense->category_name }}</h4>
            <div class="d-flex gap-2">
                <a href="{{ url("/expenses/{$expense->uuid}/edit") }}" class="btn btn-outline-primary">
                    <i class="mdi mdi-pencil-outline me-1"></i> Edit
                </a>
                <form method="POST" action="{{ url("/expenses/{$expense->uuid}") }}"
                    onsubmit="return confirm('Delete this expense?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-outline-danger"><i class="mdi mdi-delete-outline me-1"></i> Delete</button>
                </form>
            </div>
        </div>

        <div class="card">
            <div class="card-body">
                <dl class="row mb-0">
                    <dt class="col-5 col-sm-4 text-muted fw-normal">Amount</dt>
                    <dd class="col-7 col-sm-8"><span class="h5 text-danger">৳{{ number_format($expense->amount, 2) }}</span></dd>

                    <dt class="col-5 col-sm-4 text-muted fw-normal">Category</dt>
                    <dd class="col-7 col-sm-8"><span class="badge bg-label-secondary">{{ $expense->category_name }}</span></dd>

                    <dt class="col-5 col-sm-4 text-muted fw-normal">Title</dt>
                    <dd class="col-7 col-sm-8">{{ $expense->title ?: '—' }}</dd>

                    <dt class="col-5 col-sm-4 text-muted fw-normal">Date</dt>
                    <dd class="col-7 col-sm-8">{{ $expense->expense_date->format('d M Y') }}</dd>

                    <dt class="col-5 col-sm-4 text-muted fw-normal">Source</dt>
                    <dd class="col-7 col-sm-8">
                        <span class="badge bg-label-{{ $expense->source_color }}">{{ $expense->source_type_label }}</span>
                        @if ($expense->source_name)<span class="ms-1">{{ $expense->source_name }}</span>@endif
                    </dd>

                    <dt class="col-5 col-sm-4 text-muted fw-normal">Recorded By</dt>
                    <dd class="col-7 col-sm-8">{{ $expense->creator?->name ?? '—' }}</dd>

                    @if ($expense->notes)
                        <dt class="col-5 col-sm-4 text-muted fw-normal">Notes</dt>
                        <dd class="col-7 col-sm-8">{!! nl2br(e($expense->notes)) !!}</dd>
                    @endif
                </dl>
            </div>
        </div>
    </div>
</div>
@endsection
