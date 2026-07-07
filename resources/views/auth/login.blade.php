@extends('layouts.guest')

@section('title', 'Login')

@section('auth-content')
<div class="card" style="max-width: 400px; width: 100%;">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/img/project/logo.svg') }}" alt="Logo" width="48" height="48" style="border-radius: 12px;">
            <h4 class="mt-2 fw-bold">{{ config('app.name') }}</h4>
            <p class="text-muted">Sign in to your account</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email"
                    value="{{ old('email') }}" required autofocus placeholder="your@email.com">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">Password</label>
                <input type="password" class="form-control" id="password" name="password"
                    required placeholder="••••••••">
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">Remember me</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary d-grid w-100">Sign In</button>
        </form>
    </div>
</div>
@endsection
