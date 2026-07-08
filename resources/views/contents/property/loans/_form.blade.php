@php
    $loan = $loan ?? null;
    $val = fn ($field, $default = null) => old($field, $loan->$field ?? $default);
    $selectedProject = $loan?->project?->uuid;
@endphp

@if ($errors->any())
    <div class="alert alert-danger py-2">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="lender_type" class="form-label">Lender Type <span class="text-danger">*</span></label>
        <select class="form-select" id="lender_type" name="lender_type" required>
            @foreach (['bank' => 'Bank', 'shareholder' => 'Shareholder', 'director' => 'Director', 'third_party' => 'Third Party'] as $v => $l)
                <option value="{{ $v }}" {{ $val('lender_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label for="lender_name" class="form-label">Lender Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="lender_name" name="lender_name" value="{{ $val('lender_name') }}" required>
    </div>
</div>

<div class="row">
    <div class="col-md-6 mb-3">
        <label for="reference_no" class="form-label">Reference / Sanction No</label>
        <input type="text" class="form-control" id="reference_no" name="reference_no" value="{{ $val('reference_no') }}">
    </div>
    <div class="col-md-6 mb-3">
        <label for="project_id" class="form-label">Project</label>
        <select class="form-select" id="project_id" name="project_id">
            <option value="">— Company-level (no project) —</option>
            @foreach ($projects as $project)
                <option value="{{ $project->uuid }}" {{ old('project_id', $selectedProject) === $project->uuid ? 'selected' : '' }}>{{ $project->name }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="principal_amount" class="form-label">Principal Amount <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0.01" class="form-control" id="principal_amount" name="principal_amount" value="{{ $val('principal_amount') }}" required>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="interest_rate" class="form-label">Interest Rate (annual %) <span class="text-danger">*</span></label>
        <input type="number" step="0.01" min="0" max="100" class="form-control" id="interest_rate" name="interest_rate" value="{{ $val('interest_rate', '0') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="interest_type" class="form-label">Interest Type <span class="text-danger">*</span></label>
        <select class="form-select" id="interest_type" name="interest_type" required>
            <option value="flat" {{ $val('interest_type', 'flat') === 'flat' ? 'selected' : '' }}>Flat</option>
            <option value="reducing" {{ $val('interest_type') === 'reducing' ? 'selected' : '' }}>Reducing Balance</option>
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="start_date" class="form-label">Start Date <span class="text-danger">*</span></label>
        <input type="date" class="form-control" id="start_date" name="start_date"
            value="{{ old('start_date', $loan?->start_date?->format('Y-m-d')) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="end_date" class="form-label">End / Maturity Date</label>
        <input type="date" class="form-control" id="end_date" name="end_date"
            value="{{ old('end_date', $loan?->end_date?->format('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="repayment_frequency" class="form-label">Repayment Frequency <span class="text-danger">*</span></label>
        <select class="form-select" id="repayment_frequency" name="repayment_frequency" required>
            @foreach (['monthly' => 'Monthly', 'quarterly' => 'Quarterly', 'yearly' => 'Yearly'] as $v => $l)
                <option value="{{ $v }}" {{ $val('repayment_frequency', 'monthly') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="row">
    <div class="col-md-4 mb-3">
        <label for="emi_amount" class="form-label">EMI / Installment Amount</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control" id="emi_amount" name="emi_amount" value="{{ $val('emi_amount') }}">
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select" id="status" name="status" required>
            @foreach (['active' => 'Active', 'closed' => 'Closed', 'defaulted' => 'Defaulted'] as $v => $l)
                <option value="{{ $v }}" {{ $val('status', 'active') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
</div>

<div class="mb-3">
    <label for="collateral" class="form-label">Collateral / Security</label>
    <textarea class="form-control" id="collateral" name="collateral" rows="2">{{ $val('collateral') }}</textarea>
</div>

<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea class="form-control" id="notes" name="notes" rows="2">{{ $val('notes') }}</textarea>
</div>
