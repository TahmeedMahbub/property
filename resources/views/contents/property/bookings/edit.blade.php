@extends('contents.body')

@section('title', 'Edit Booking')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-10">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/bookings') }}">Bookings</a></li>
                <li class="breadcrumb-item"><a href="{{ url("/bookings/{$booking->uuid}") }}">{{ $booking->booking_no }}</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Booking — {{ $booking->booking_no }}</h4>

        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ url("/bookings/{$booking->uuid}") }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('contents.property.bookings._form')
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update Booking</button>
                        <a href="{{ url("/bookings/{$booking->uuid}") }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
