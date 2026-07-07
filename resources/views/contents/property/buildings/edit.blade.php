@extends('contents.body')

@section('title', 'Edit Building')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/buildings') }}">Buildings</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Building: {{ $building->name }}</h4>

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

                <form method="POST" action="{{ url("/buildings/{$building->uuid}") }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label for="name" class="form-label">Building Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name', $building->name) }}" required>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="total_floors" class="form-label">Total Floors</label>
                            <input type="number" class="form-control" id="total_floors" name="total_floors"
                                value="{{ old('total_floors', $building->total_floors) }}" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="total_units" class="form-label">Total Units</label>
                            <input type="number" class="form-control" id="total_units" name="total_units"
                                value="{{ old('total_units', $building->total_units) }}" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address"
                            value="{{ old('address', $building->address) }}">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="planning" {{ old('status', $building->status) == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="under_construction" {{ old('status', $building->status) == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                            <option value="completed" {{ old('status', $building->status) == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="handed_over" {{ old('status', $building->status) == 'handed_over' ? 'selected' : '' }}>Handed Over</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"
                            rows="3">{{ old('description', $building->description) }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url('/buildings') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
