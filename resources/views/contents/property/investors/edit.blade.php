@extends('contents.body')

@section('title', 'Edit Investor')

@section('content')
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/investors') }}">Investors</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Investor: {{ $investor->name }}</h4>

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

                <form method="POST" action="{{ url("/investors/{$investor->uuid}") }}">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label text-muted">Project</label>
                        <input type="text" class="form-control" value="{{ $investor->project->name ?? '' }}" disabled>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="name" name="name"
                                value="{{ old('name', $investor->name) }}" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" class="form-control" id="email" name="email"
                                value="{{ old('email', $investor->email) }}">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="phone" class="form-label">Phone</label>
                            <input type="text" class="form-control" id="phone" name="phone"
                                value="{{ old('phone', $investor->phone) }}">
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="investment_amount" class="form-label">Amount (৳)</label>
                            <input type="number" step="0.01" class="form-control" id="investment_amount" name="investment_amount"
                                value="{{ old('investment_amount', $investor->investment_amount) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="investment_percentage" class="form-label">Percentage %</label>
                            <input type="number" step="0.01" class="form-control" id="investment_percentage" name="investment_percentage"
                                value="{{ old('investment_percentage', $investor->investment_percentage) }}" min="0" max="100">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="investment_type" class="form-label">Type</label>
                            <select class="form-select" id="investment_type" name="investment_type">
                                <option value="">Select</option>
                                <option value="equity" {{ old('investment_type', $investor->investment_type) == 'equity' ? 'selected' : '' }}>Equity</option>
                                <option value="debt" {{ old('investment_type', $investor->investment_type) == 'debt' ? 'selected' : '' }}>Debt</option>
                                <option value="mezzanine" {{ old('investment_type', $investor->investment_type) == 'mezzanine' ? 'selected' : '' }}>Mezzanine</option>
                            </select>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label for="invested_at" class="form-label">Invested Date</label>
                            <input type="date" class="form-control" id="invested_at" name="invested_at"
                                value="{{ old('invested_at', $investor->invested_at?->format('Y-m-d')) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="expected_return" class="form-label">Expected Return (৳)</label>
                            <input type="number" step="0.01" class="form-control" id="expected_return" name="expected_return"
                                value="{{ old('expected_return', $investor->expected_return) }}">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
                            <select class="form-select" id="status" name="status" required>
                                <option value="active" {{ old('status', $investor->status) == 'active' ? 'selected' : '' }}>Active</option>
                                <option value="inactive" {{ old('status', $investor->status) == 'inactive' ? 'selected' : '' }}>Inactive</option>
                                <option value="exited" {{ old('status', $investor->status) == 'exited' ? 'selected' : '' }}>Exited</option>
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="notes" class="form-label">Notes</label>
                        <textarea class="form-control" id="notes" name="notes" rows="2">{{ old('notes', $investor->notes) }}</textarea>
                    </div>
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url('/investors') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
