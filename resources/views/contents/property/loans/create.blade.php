@extends('contents.body')

@section('title', 'Add Loan')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-9">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/loans') }}">Loans</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Add Loan</h4>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ url('/loans') }}">
                    @csrf
                    @include('contents.property.loans._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Loan</button>
                        <a href="{{ url('/loans') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
