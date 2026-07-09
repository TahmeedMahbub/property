@php
    $c = $customer ?? null;
    $v = fn ($field, $default = '') => old($field, $c->{$field} ?? $default);
    $sel = fn ($field, $option, $default = '') => old($field, $c->{$field} ?? $default) == $option ? 'selected' : '';
    $dob = $c && $c->date_of_birth ? $c->date_of_birth->format('Y-m-d') : old('date_of_birth');
@endphp

{{-- ─── Quick Create: Name + Mobile is enough ─────────────────────── --}}
<div class="row g-3">
    <div class="col-md-6">
        <label for="name" class="form-label">Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="name" name="name"
            value="{{ $v('name') }}" placeholder="Customer name" autofocus required>
    </div>
    <div class="col-md-6">
        <label for="phone" class="form-label">Mobile <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="phone" name="phone"
            value="{{ $v('phone') }}" placeholder="017XXXXXXXX" required>
    </div>

    <div class="col-md-6">
        <label for="project_id" class="form-label">Project <span class="text-muted small">(optional)</span></label>
        <select class="form-select" id="project_id" name="project_id">
            <option value="">— None —</option>
            @foreach ($projects as $project)
                <option value="{{ $project->uuid }}"
                    {{ old('project_id', $c?->project?->uuid) == $project->uuid ? 'selected' : '' }}>
                    {{ $project->name }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6">
        <label for="email" class="form-label">Email <span class="text-muted small">(optional)</span></label>
        <input type="email" class="form-control" id="email" name="email" value="{{ $v('email') }}">
    </div>

    <div class="col-md-8">
        <label for="address" class="form-label">Address <span class="text-muted small">(optional)</span></label>
        <input type="text" class="form-control" id="address" name="address" value="{{ $v('address') }}">
    </div>
    <div class="col-md-4">
        <label for="status" class="form-label">Status</label>
        <select class="form-select" id="status" name="status">
            <option value="lead" {{ $sel('status', 'lead') }}>Lead</option>
            <option value="customer" {{ $sel('status', 'customer', 'customer') }}>Customer</option>
            <option value="verified" {{ $sel('status', 'verified') }}>Verified</option>
        </select>
    </div>

    <div class="col-12">
        <label for="notes" class="form-label">Notes <span class="text-muted small">(optional)</span></label>
        <textarea class="form-control" id="notes" name="notes" rows="2">{{ $v('notes') }}</textarea>
    </div>
</div>

{{-- ─── Add More Information (expandable, nothing required) ─────────── --}}
<div class="mt-4">
    <button class="btn btn-outline-primary" type="button" data-bs-toggle="collapse"
        data-bs-target="#additionalInfo" aria-expanded="{{ $errors->any() ? 'true' : 'false' }}"
        aria-controls="additionalInfo">
        <i class="mdi mdi-plus-circle-outline me-1"></i> Add More Information
    </button>
    <span class="text-muted small ms-2">All fields below are optional and can be completed later.</span>
</div>

<div class="collapse {{ $errors->any() ? 'show' : '' }} mt-3" id="additionalInfo">

    {{-- Personal --}}
    <h6 class="fw-bold text-uppercase text-muted small mt-3 mb-2">Personal</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="full_name_en" class="form-label">Full Name (English)</label>
            <input type="text" class="form-control" id="full_name_en" name="full_name_en" value="{{ $v('full_name_en') }}">
        </div>
        <div class="col-md-6">
            <label for="full_name_bn" class="form-label">Full Name (Bangla)</label>
            <input type="text" class="form-control" id="full_name_bn" name="full_name_bn" value="{{ $v('full_name_bn') }}">
        </div>
        <div class="col-md-6">
            <label for="father_name" class="form-label">Father Name (English)</label>
            <input type="text" class="form-control" id="father_name" name="father_name" value="{{ $v('father_name') }}">
        </div>
        <div class="col-md-6">
            <label for="father_name_bn" class="form-label">Father Name (Bangla)</label>
            <input type="text" class="form-control" id="father_name_bn" name="father_name_bn" value="{{ $v('father_name_bn') }}">
        </div>
        <div class="col-md-6">
            <label for="mother_name" class="form-label">Mother Name (English)</label>
            <input type="text" class="form-control" id="mother_name" name="mother_name" value="{{ $v('mother_name') }}">
        </div>
        <div class="col-md-6">
            <label for="mother_name_bn" class="form-label">Mother Name (Bangla)</label>
            <input type="text" class="form-control" id="mother_name_bn" name="mother_name_bn" value="{{ $v('mother_name_bn') }}">
        </div>
        <div class="col-md-4">
            <label for="date_of_birth" class="form-label">Date of Birth</label>
            <input type="date" class="form-control" id="date_of_birth" name="date_of_birth" value="{{ $dob }}">
        </div>
        <div class="col-md-4">
            <label for="gender" class="form-label">Gender</label>
            <select class="form-select" id="gender" name="gender">
                <option value="">—</option>
                <option value="male" {{ $sel('gender', 'male') }}>Male</option>
                <option value="female" {{ $sel('gender', 'female') }}>Female</option>
                <option value="other" {{ $sel('gender', 'other') }}>Other</option>
            </select>
        </div>
        <div class="col-md-4">
            <label for="marital_status" class="form-label">Marital Status</label>
            <select class="form-select" id="marital_status" name="marital_status">
                <option value="">—</option>
                <option value="single" {{ $sel('marital_status', 'single') }}>Single</option>
                <option value="married" {{ $sel('marital_status', 'married') }}>Married</option>
                <option value="divorced" {{ $sel('marital_status', 'divorced') }}>Divorced</option>
                <option value="widowed" {{ $sel('marital_status', 'widowed') }}>Widowed</option>
            </select>
        </div>
        <div class="col-md-6">
            <label for="profession" class="form-label">Profession</label>
            <input type="text" class="form-control" id="profession" name="profession" value="{{ $v('profession') }}">
        </div>
        <div class="col-md-6">
            <label for="nationality" class="form-label">Nationality</label>
            <input type="text" class="form-control" id="nationality" name="nationality" value="{{ $v('nationality') }}">
        </div>
    </div>

    {{-- Contact --}}
    <h6 class="fw-bold text-uppercase text-muted small mt-4 mb-2">Contact</h6>
    <div class="row g-3">
        <div class="col-md-4">
            <label for="alternative_mobile" class="form-label">Alternative Mobile</label>
            <input type="text" class="form-control" id="alternative_mobile" name="alternative_mobile" value="{{ $v('alternative_mobile') }}">
        </div>
        <div class="col-md-4">
            <label for="present_address" class="form-label">Present Address</label>
            <input type="text" class="form-control" id="present_address" name="present_address" value="{{ $v('present_address') }}">
        </div>
        <div class="col-md-4">
            <label for="permanent_address" class="form-label">Permanent Address</label>
            <input type="text" class="form-control" id="permanent_address" name="permanent_address" value="{{ $v('permanent_address') }}">
        </div>
    </div>

    {{-- Identity --}}
    <h6 class="fw-bold text-uppercase text-muted small mt-4 mb-2">Identity</h6>
    <div class="row g-3">
        <div class="col-md-3">
            <label for="nid_number" class="form-label">NID Number</label>
            <input type="text" class="form-control" id="nid_number" name="nid_number" value="{{ $v('nid_number') }}">
        </div>
        <div class="col-md-3">
            <label for="tin_number" class="form-label">TIN Number</label>
            <input type="text" class="form-control" id="tin_number" name="tin_number" value="{{ $v('tin_number') }}">
        </div>
        <div class="col-md-3">
            <label for="passport_number" class="form-label">Passport Number</label>
            <input type="text" class="form-control" id="passport_number" name="passport_number" value="{{ $v('passport_number') }}">
        </div>
        <div class="col-md-3">
            <label for="driving_license_number" class="form-label">Driving License Number</label>
            <input type="text" class="form-control" id="driving_license_number" name="driving_license_number" value="{{ $v('driving_license_number') }}">
        </div>
    </div>

    {{-- Nominee --}}
    <h6 class="fw-bold text-uppercase text-muted small mt-4 mb-2">Nominee</h6>
    <div class="row g-3">
        <div class="col-md-6">
            <label for="nominee_name" class="form-label">Nominee Name</label>
            <input type="text" class="form-control" id="nominee_name" name="nominee_name" value="{{ $v('nominee_name') }}">
        </div>
        <div class="col-md-6">
            <label for="nominee_relationship" class="form-label">Relationship</label>
            <input type="text" class="form-control" id="nominee_relationship" name="nominee_relationship" value="{{ $v('nominee_relationship') }}">
        </div>
        <div class="col-md-6">
            <label for="nominee_mobile" class="form-label">Mobile</label>
            <input type="text" class="form-control" id="nominee_mobile" name="nominee_mobile" value="{{ $v('nominee_mobile') }}">
        </div>
        <div class="col-md-6">
            <label for="nominee_address" class="form-label">Address</label>
            <input type="text" class="form-control" id="nominee_address" name="nominee_address" value="{{ $v('nominee_address') }}">
        </div>
        <div class="col-md-6">
            <label for="nominee_nid_number" class="form-label">NID Number</label>
            <input type="text" class="form-control" id="nominee_nid_number" name="nominee_nid_number" value="{{ $v('nominee_nid_number') }}">
        </div>
    </div>

    {{-- Documents --}}
    <h6 class="fw-bold text-uppercase text-muted small mt-4 mb-2">Documents</h6>
    <p class="text-muted small mb-2">Image or PDF only, max 3 MB per file.</p>
    <div class="row g-3">
        @foreach (\App\Models\Customer::DOCUMENT_TYPES as $type => $label)
            @php $existingDoc = $c?->documentOfType($type); @endphp
            <div class="col-md-4">
                <label for="doc_{{ $type }}" class="form-label">{{ $label }}</label>
                <input type="file" class="form-control" id="doc_{{ $type }}"
                    name="documents[{{ $type }}]" accept="image/*,application/pdf">
                @if ($existingDoc)
                    <div class="d-flex align-items-center gap-2 mt-1 small">
                        <a href="{{ route('documents.preview', $existingDoc->uuid) }}" target="_blank">
                            <i class="mdi mdi-file-eye-outline"></i> View current
                        </a>
                        <button type="button" class="btn btn-sm btn-text-danger p-0"
                            onclick="event.preventDefault(); document.getElementById('del_doc_{{ $type }}').submit();">
                            <i class="mdi mdi-delete-outline"></i> Remove
                        </button>
                    </div>
                @endif
            </div>
        @endforeach
    </div>
</div>
