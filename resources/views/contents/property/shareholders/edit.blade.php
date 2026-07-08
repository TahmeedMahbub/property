@extends('contents.body')

@section('title', 'Edit Shareholder')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/shareholders') }}">Shareholders</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Shareholder: {{ $shareholder->name }}</h4>

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

                <form method="POST" action="{{ url("/shareholders/{$shareholder->uuid}") }}">
                    @csrf
                    @method('PUT')
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $shareholder->name) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $shareholder->email) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="{{ old('phone', $shareholder->phone) }}">
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="share_type" class="form-label">Share Type</label>
                            <select class="form-select" id="share_type" name="share_type">
                                <option value="">Select</option>
                                <option value="equity" {{ old('share_type', $shareholder->share_type) == 'equity' ? 'selected' : '' }}>Equity</option>
                                <option value="preferred" {{ old('share_type', $shareholder->share_type) == 'preferred' ? 'selected' : '' }}>Preferred</option>
                                <option value="common" {{ old('share_type', $shareholder->share_type) == 'common' ? 'selected' : '' }}>Common</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="acquired_at" class="form-label">Acquired Date</label>
                            <input type="date" class="form-control" id="acquired_at" name="acquired_at"
                                value="{{ old('acquired_at', $shareholder->acquired_at?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" {{ old('status', $shareholder->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $shareholder->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $shareholder->notes) }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url("/shareholders/{$shareholder->uuid}/investment") }}" class="btn btn-outline-primary">
                            <i class="mdi mdi-cash-multiple me-1"></i>Manage Investment
                        </a>
                        <a href="{{ url('/shareholders') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
