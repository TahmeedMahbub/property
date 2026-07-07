@extends('contents.body')

@section('title', 'Edit Unit')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/units') }}">Units</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Unit: {{ $unit->unit_number }}</h4>

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

                <form method="POST" action="{{ url("/units/{$unit->uuid}") }}">
                    @csrf
                    @method('PUT')

                    {{-- Read-only context --}}
                    <div class="row mb-3">
                        <div class="col-md-6">
                            <label class="form-label text-muted">Building</label>
                            <input type="text" class="form-control" value="{{ $unit->building->name ?? '' }}" disabled>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label text-muted">Floor</label>
                            <input type="text" class="form-control" value="{{ $unit->floor->name ?? '' }}" disabled>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit_number" class="form-label">Unit Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unit_number" name="unit_number"
                                value="{{ old('unit_number', $unit->unit_number) }}" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit_type_id" class="form-label">Unit Type</label>
                            <select class="form-select" id="unit_type_id" name="unit_type_id">
                                <option value="">None</option>
                                @foreach($unitTypes as $type)
                                    <option value="{{ $type->uuid }}"
                                        {{ old('unit_type_id', optional($unit->unitType)->uuid) == $type->uuid ? 'selected' : '' }}>
                                        {{ $type->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="size" class="form-label">Size (sqft)</label>
                            <input type="number" step="0.01" class="form-control" id="size" name="size"
                                value="{{ old('size', $unit->size) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Price (৳)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price"
                                value="{{ old('price', $unit->price) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="facing" class="form-label">Facing</label>
                            <select class="form-select" id="facing" name="facing">
                                <option value="">Select</option>
                                @foreach(['North', 'South', 'East', 'West', 'North-East', 'North-West', 'South-East', 'South-West'] as $f)
                                    <option value="{{ $f }}" {{ old('facing', $unit->facing) == $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available" {{ old('status', $unit->status) == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="reserved" {{ old('status', $unit->status) == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="booked" {{ old('status', $unit->status) == 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="sold" {{ old('status', $unit->status) == 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="handovered" {{ old('status', $unit->status) == 'handovered' ? 'selected' : '' }}>Handovered</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"
                            rows="2">{{ old('description', $unit->description) }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url('/units') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
