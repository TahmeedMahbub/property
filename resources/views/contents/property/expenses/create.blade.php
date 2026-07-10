@extends('contents.body')

@section('title', 'Record Expense')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-9">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/expenses') }}">Expenses</a></li>
                <li class="breadcrumb-item active">Record</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Record Expense</h4>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ url('/expenses') }}">
                    @csrf
                    @include('contents.property.expenses._form')
                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Save Expense</button>
                        <a href="{{ url('/expenses') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
