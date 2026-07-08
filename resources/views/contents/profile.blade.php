@extends('contents.body')

@section('title', t('nav.profile_edit'))

@php
    $activeTab = 'profile';
    if ($errors->has('current_password') || $errors->has('password')) $activeTab = 'password';
    if ($errors->has('company_name') || $errors->has('company_email') || $errors->has('company_phone')) $activeTab = 'company';
    if (session('active_tab')) $activeTab = session('active_tab');
@endphp

@section('content')
<div class="row justify-content-center">
    <div class="col-md-8 col-lg-6">

        @if(session('success'))
            <div class="alert alert-success py-2 px-3 mb-3">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger py-2 px-3 mb-3">
                @foreach($errors->all() as $error)
                    <div>{{ $error }}</div>
                @endforeach
            </div>
        @endif

        <ul class="nav nav-tabs mb-3" role="tablist">
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'profile' ? 'active' : '' }}"
                   data-bs-toggle="tab" href="#tab-profile" role="tab">
                    <i class="mdi mdi-account-outline me-1"></i> {{ t('nav.profile') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'company' ? 'active' : '' }}"
                   data-bs-toggle="tab" href="#tab-company" role="tab">
                    <i class="mdi mdi-office-building-outline me-1"></i> {{ t('nav.company_info') }}
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link {{ $activeTab === 'password' ? 'active' : '' }}"
                   data-bs-toggle="tab" href="#tab-password" role="tab">
                    <i class="mdi mdi-lock-outline me-1"></i> {{ t('nav.change_password') }}
                </a>
            </li>
        </ul>

        <div class="tab-content">
            {{-- Profile Info Tab --}}
            <div class="tab-pane fade {{ $activeTab === 'profile' ? 'show active' : '' }}" id="tab-profile" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ url('/profile') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="name" class="form-label">{{ t('common.name') }}</label>
                                <input type="text" class="form-control" id="name" name="name"
                                    value="{{ old('name', $user->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="email" class="form-label">{{ t('auth.email') }}</label>
                                <input type="email" class="form-control" id="email" name="email"
                                    value="{{ old('email', $user->email) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="phone" class="form-label">{{ t('auth.phone') }}</label>
                                <input type="text" class="form-control" id="phone" name="phone"
                                    value="{{ old('phone', $user->phone) }}" placeholder="01XXXXXXXXX">
                            </div>

                            <button type="submit" class="btn btn-primary">{{ t('common.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Company Info Tab --}}
            <div class="tab-pane fade {{ $activeTab === 'company' ? 'show active' : '' }}" id="tab-company" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ url('/profile/company') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="company_name" class="form-label">{{ t('common.name') }}</label>
                                <input type="text" class="form-control" id="company_name" name="company_name"
                                    value="{{ old('company_name', $company->name) }}" required>
                            </div>

                            <div class="mb-3">
                                <label for="company_email" class="form-label">{{ t('auth.email') }}</label>
                                <input type="email" class="form-control" id="company_email" name="company_email"
                                    value="{{ old('company_email', $company->email) }}">
                            </div>

                            <div class="mb-3">
                                <label for="company_phone" class="form-label">{{ t('auth.phone') }}</label>
                                <input type="text" class="form-control" id="company_phone" name="company_phone"
                                    value="{{ old('company_phone', $company->phone) }}">
                            </div>

                            <div class="mb-3">
                                <label for="company_address" class="form-label">{{ t('common.address') }}</label>
                                <input type="text" class="form-control" id="company_address" name="company_address"
                                    value="{{ old('company_address', $company->address) }}">
                            </div>

                            <div class="mb-3">
                                <label for="company_website" class="form-label">{{ t('nav.website') }}</label>
                                <input type="url" class="form-control" id="company_website" name="company_website"
                                    value="{{ old('company_website', $company->website) }}" placeholder="https://">
                            </div>

                            <button type="submit" class="btn btn-primary">{{ t('common.save') }}</button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Change Password Tab --}}
            <div class="tab-pane fade {{ $activeTab === 'password' ? 'show active' : '' }}" id="tab-password" role="tabpanel">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="{{ url('/profile/password') }}">
                            @csrf
                            @method('PUT')

                            <div class="mb-3">
                                <label for="current_password" class="form-label">{{ t('auth.current_password') }}</label>
                                <input type="password" class="form-control" id="current_password" name="current_password"
                                    required placeholder="••••••••">
                            </div>

                            <div class="mb-3">
                                <label for="new_password" class="form-label">{{ t('auth.new_password') }}</label>
                                <input type="password" class="form-control" id="new_password" name="password"
                                    required placeholder="••••••••">
                            </div>

                            <div class="mb-3">
                                <label for="password_confirmation" class="form-label">{{ t('auth.confirm_password') }}</label>
                                <input type="password" class="form-control" id="password_confirmation" name="password_confirmation"
                                    required placeholder="••••••••">
                            </div>

                            <button type="submit" class="btn btn-primary">{{ t('nav.change_password') }}</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
