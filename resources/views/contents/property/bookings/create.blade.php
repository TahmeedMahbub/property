@extends('contents.body')

@section('title', 'New Booking')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/bookings') }}">Bookings</a></li>
                <li class="breadcrumb-item active">New</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">New Booking</h4>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ url('/bookings') }}" enctype="multipart/form-data">
                    @csrf
                    @include('contents.property.bookings._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save Booking</button>
                        <a href="{{ url('/bookings') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
