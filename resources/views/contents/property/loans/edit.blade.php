@extends('contents.body')

@section('title', 'Edit Loan')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-9">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/loans') }}">Loans</a></li>
                <li class="breadcrumb-item"><a href="{{ url("/loans/{$loan->uuid}") }}">{{ $loan->lender_name }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Loan: {{ $loan->lender_name }}</h4>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ url("/loans/{$loan->uuid}") }}">
                    @csrf
                    @method('PUT')
                    @include('contents.property.loans._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Loan</button>
                        <a href="{{ url("/loans/{$loan->uuid}") }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
