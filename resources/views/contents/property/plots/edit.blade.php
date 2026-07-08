@extends('contents.body')

@section('title', 'Edit Plot')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/plots') }}">Plots</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Plot</h4>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ url("/plots/{$plot->uuid}") }}">
                    @csrf
                    @method('PUT')
                    @include('contents.property.plots._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Plot</button>
                        <a href="{{ url('/plots') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
