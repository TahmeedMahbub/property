@extends('contents.body')

@section('title', 'Edit Expense')

@section('content')
@php
    $moduleSourced = $expense->expensable_type && $expense->expensable_type !== \App\Models\Company::class;
@endphp
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-9">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/expenses') }}">Expenses</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Expense</h4>

        @if ($moduleSourced)
            <div class="alert alert-info">
                <i class="mdi mdi-information-outline me-1"></i>
                This expense is linked to
                <span class="badge bg-label-{{ $expense->source_color }}">{{ $expense->source_type_label }}</span>
                <strong>{{ $expense->source_name }}</strong>. Its source cannot be changed here.
            </div>
        @endif

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ url("/expenses/{$expense->uuid}") }}">
                    @csrf
                    @method('PUT')
                    @include('contents.property.expenses._form', [
                        'lockSource' => $moduleSourced,
                        'sourceValue' => $expense->expensable_type === \App\Models\Project::class ? 'project' : 'company',
                        'projectValue' => $expense->expensable_type === \App\Models\Project::class ? optional($expense->expensable)->uuid : '',
                    ])
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Update Expense</button>
                        <a href="{{ url("/expenses/{$expense->uuid}") }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
