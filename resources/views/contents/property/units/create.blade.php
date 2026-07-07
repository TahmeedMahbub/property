@extends('contents.body')

@section('title', 'Add Unit')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/units') }}">Units</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Add Unit</h4>

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

                <form method="POST" action="{{ url('/units') }}">
                    @csrf
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="building_select" class="form-label">Building <span class="text-danger">*</span></label>
                            <select class="form-select" id="building_select" required>
                                <option value="">Select building</option>
                                @foreach($buildings as $building)
                                    <option value="{{ $building->uuid }}" data-floors='@json($building->floors ?? [])'>
                                        {{ $building->name }} ({{ $building->project->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="floor_id" class="form-label">Floor <span class="text-danger">*</span></label>
                            <select class="form-select" id="floor_id" name="floor_id" required>
                                <option value="">Select floor</option>
                                @foreach($floors as $floor)
                                    <option value="{{ $floor->uuid }}" data-building="{{ $floor->building_id }}"
                                        {{ old('floor_id') == $floor->uuid ? 'selected' : '' }}>
                                        {{ $floor->name }} ({{ $floor->building->name ?? '' }})
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="unit_number" class="form-label">Unit Number <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="unit_number" name="unit_number"
                                value="{{ old('unit_number') }}" required placeholder="e.g. A-101">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="unit_type_id" class="form-label">Unit Type</label>
                            <select class="form-select" id="unit_type_id" name="unit_type_id">
                                <option value="">Select type (optional)</option>
                                @foreach($unitTypes as $type)
                                    <option value="{{ $type->uuid }}" {{ old('unit_type_id') == $type->uuid ? 'selected' : '' }}>
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
                                value="{{ old('size') }}" placeholder="e.g. 1200">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="price" class="form-label">Price (৳)</label>
                            <input type="number" step="0.01" class="form-control" id="price" name="price"
                                value="{{ old('price') }}" placeholder="e.g. 5000000">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="facing" class="form-label">Facing</label>
                            <select class="form-select" id="facing" name="facing">
                                <option value="">Select</option>
                                @foreach(['North', 'South', 'East', 'West', 'North-East', 'North-West', 'South-East', 'South-West'] as $f)
                                    <option value="{{ $f }}" {{ old('facing') == $f ? 'selected' : '' }}>{{ $f }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="available" {{ old('status', 'available') == 'available' ? 'selected' : '' }}>Available</option>
                            <option value="reserved" {{ old('status') == 'reserved' ? 'selected' : '' }}>Reserved</option>
                            <option value="booked" {{ old('status') == 'booked' ? 'selected' : '' }}>Booked</option>
                            <option value="sold" {{ old('status') == 'sold' ? 'selected' : '' }}>Sold</option>
                            <option value="handovered" {{ old('status') == 'handovered' ? 'selected' : '' }}>Handovered</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"
                            rows="2">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ url('/units') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buildingSelect = document.getElementById('building_select');
    const floorSelect = document.getElementById('floor_id');
    const allOptions = Array.from(floorSelect.options);

    buildingSelect.addEventListener('change', function() {
        const selectedBuilding = this.value;
        floorSelect.innerHTML = '<option value="">Select floor</option>';

        if (!selectedBuilding) return;

        allOptions.forEach(function(opt) {
            if (opt.value && opt.textContent.includes(buildingSelect.options[buildingSelect.selectedIndex].textContent.split(' (')[0])) {
                floorSelect.appendChild(opt.cloneNode(true));
            }
        });
    });
});
</script>
@endsection
