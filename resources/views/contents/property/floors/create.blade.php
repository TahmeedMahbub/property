@extends('contents.body')

@section('title', 'Add Floor')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/floors') }}">Floors</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Add Floor</h4>

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

                <form method="POST" action="{{ url('/floors') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="building_id" class="form-label">Building <span class="text-danger">*</span></label>
                        <select class="form-select" id="building_id" name="building_id" required>
                            <option value="">Select building</option>
                            @foreach($buildings as $building)
                                <option value="{{ $building->uuid }}" {{ old('building_id') == $building->uuid ? 'selected' : '' }}>
                                    {{ $building->name }} ({{ $building->project->name ?? '' }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Floor Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name') }}" required placeholder="e.g. Ground Floor, Floor 1">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="floor_number" class="form-label">Floor Number <span class="text-danger">*</span></label>
                            <input type="number" class="form-control" id="floor_number" name="floor_number"
                                value="{{ old('floor_number', 0) }}" required min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="total_units" class="form-label">Total Units</label>
                        <input type="number" class="form-control" id="total_units" name="total_units"
                            value="{{ old('total_units', 0) }}" min="0">
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"
                            rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ url('/floors') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
