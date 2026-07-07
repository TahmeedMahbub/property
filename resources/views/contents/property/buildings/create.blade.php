@extends('contents.body')

@section('title', 'Add Building')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/buildings') }}">Buildings</a></li>
                <li class="breadcrumb-item active">Add</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Add Building</h4>

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

                <form method="POST" action="{{ url('/buildings') }}">
                    @csrf
                    <div class="mb-3">
                        <label for="project_id" class="form-label">Project <span class="text-danger">*</span></label>
                        <select class="form-select" id="project_id" name="project_id" required>
                            <option value="">Select project</option>
                            @foreach($projects as $project)
                                <option value="{{ $project->uuid }}" {{ old('project_id') == $project->uuid ? 'selected' : '' }}>
                                    {{ $project->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Building Name <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="name" name="name"
                            value="{{ old('name') }}" required placeholder="e.g. Tower A, Block 1">
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="total_floors" class="form-label">Total Floors</label>
                            <input type="number" class="form-control" id="total_floors" name="total_floors"
                                value="{{ old('total_floors', 0) }}" min="0">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="total_units" class="form-label">Total Units</label>
                            <input type="number" class="form-control" id="total_units" name="total_units"
                                value="{{ old('total_units', 0) }}" min="0">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="address" class="form-label">Address</label>
                        <input type="text" class="form-control" id="address" name="address"
                            value="{{ old('address') }}" placeholder="Building address">
                    </div>
                    <div class="mb-3">
                        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                        <select class="form-select" id="status" name="status" required>
                            <option value="planning" {{ old('status') == 'planning' ? 'selected' : '' }}>Planning</option>
                            <option value="under_construction" {{ old('status') == 'under_construction' ? 'selected' : '' }}>Under Construction</option>
                            <option value="completed" {{ old('status') == 'completed' ? 'selected' : '' }}>Completed</option>
                            <option value="handed_over" {{ old('status') == 'handed_over' ? 'selected' : '' }}>Handed Over</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="description" class="form-label">Description</label>
                        <textarea class="form-control" id="description" name="description"
                            rows="3">{{ old('description') }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Save</button>
                        <a href="{{ url('/buildings') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
