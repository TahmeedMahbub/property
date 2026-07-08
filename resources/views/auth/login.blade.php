@extends('layouts.guest')

@section('title', t('authpage.login_heading'))

@section('auth-content')
<div class="card" style="max-width: 400px; width: 100%;">
    <div class="card-body p-4">
        <div class="text-center mb-4">
            <img src="{{ asset('assets/img/project/logo.svg') }}" alt="Logo" width="48" height="48" style="border-radius: 12px;">
            <h4 class="mt-2 fw-bold">{{ config('app.name') }}</h4>
            <p class="text-muted">{{ t('authpage.login_subtitle') }}</p>
        </div>

        @if ($errors->any())
            <div class="alert alert-danger py-2 px-3">
                {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ url('/login') }}">
            @csrf
            <div class="mb-3">
                <label for="login" class="form-label">{{ t('auth.email_or_mobile') }}</label>
                <input type="text" class="form-control" id="login" name="login"
                    value="{{ old('login') }}" required autofocus placeholder="your@email.com / 01XXXXXXXXX">
            </div>
            <div class="mb-3">
                <label for="password" class="form-label">{{ t('auth.password') }}</label>
                <input type="password" class="form-control" id="password" name="password"
                    required placeholder="••••••••">
            </div>
            <div class="mb-3">
                <div class="form-check">
                    <input class="form-check-input" type="checkbox" id="remember" name="remember">
                    <label class="form-check-label" for="remember">{{ t('auth.remember_me') }}</label>
                </div>
            </div>
            <button type="submit" class="btn btn-primary d-grid w-100">{{ t('auth.sign_in') }}</button>
        </form>

        <p class="text-center mt-3">
            <small class="text-muted">{{ t('auth.no_account') }} <a href="{{ route('register') }}">{{ t('auth.sign_up') }}</a></small>
        </p>
    </div>
</div>
@endsection
