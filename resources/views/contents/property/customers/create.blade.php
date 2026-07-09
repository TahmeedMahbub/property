@extends('contents.body')

@section('title', 'Add Customer')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/customers') }}">Customers</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-1">Add Customer</h4>
        <p class="text-muted mb-3">Just a name and mobile number is enough — you can add the rest later.</p>

        <div class="card">
            <div class="card-body">
                @if ($errors->any())
                    <div class="alert alert-danger py-2">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ url('/customers') }}" enctype="multipart/form-data">
                    @csrf
                    @include('contents.property.customers._form', ['customer' => null])

                    <div class="form-check mt-4">
                        <input class="form-check-input" type="checkbox" value="1"
                            id="generate_profile_link" name="generate_profile_link"
                            {{ old('generate_profile_link') ? 'checked' : '' }}>
                        <label class="form-check-label" for="generate_profile_link">
                            Generate Profile Completion Link
                            <span class="text-muted small d-block">
                                Creates a secure link (valid 30 days) the customer can use to complete their own profile.
                            </span>
                        </label>
                    </div>

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Save Customer</button>
                        <a href="{{ url('/customers') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

