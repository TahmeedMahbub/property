@extends('layouts.guest')

@section('title', 'Register')

@section('auth-content')
<div class="card" style="max-width: 400px; width: 100%;">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/img/project/logo.svg') }}" alt="Logo" width="48" height="48" style="border-radius: 12px;">
            <h4 class="mt-2 fw-bold">{{ config('app.name') }}</h4>
            <p class="text-muted">Create your free account</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ url('/register') }}">
            @csrf

            <div class="mb-3">
                <label for="company_name" class="form-label">Company Name</label>
                <input type="text" class="form-control" id="company_name" name="company_name"
                    value="{{ old('company_name') }}" required autofocus placeholder="Your company name">
            </div>

            <div class="mb-3">
                <label for="name" class="form-label">Your Name</label>
                <input type="text" class="form-control" id="name" name="name"
                    value="{{ old('name') }}" required placeholder="Full name">
            </div>

            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="{{ old('email') }}" required placeholder="your@email.com">
            </div>

            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone"
                    value="{{ old('phone') }}" placeholder="01XXXXXXXXX">
            </div>

            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password"
                    required placeholder="••••••••">
            </div>

            <div class="mb-3">
                <label for="password_confirmation" class="form-label">Confirm Password</label>
                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                    required placeholder="••••••••">
            </div>

            <button type="submit" class="btn btn-primary d-grid w-100">Create Account</button>
        </form>

        <p class="text-center mt-3">
            <small class="text-muted">Already have an account? <a href="{{ route('login') }}">Sign in</a></small>
        </p>
    </div>
</div>
@endsection
