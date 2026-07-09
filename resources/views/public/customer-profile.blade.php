@extends('layouts.public')

@section('title', 'Customer Profile')

@section('content')
@php
    $customer = $customer ?? null;
    $company = $customer?->company;
@endphp

{{-- ─── Brand / header ─────────────────────────────────────────── --}}
<div class="text-center mb-4">
    <h4 class="fw-bold mb-1">{{ $company->name ?? 'Customer Profile' }}</h4>
    <p class="text-muted mb-0">Profile Completion</p>
</div>

@if ($state === 'invalid')
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="mdi mdi-link-off mdi-48px text-muted"></i>
            <h5 class="mt-2">Invalid Link</h5>
            <p class="text-muted mb-0">This profile link is not valid. Please contact the company for a new link.</p>
        </div>
    </div>

@elseif ($state === 'locked')
    <div class="card">
        <div class="card-body py-4">
            <div class="alert alert-success">
                <i class="mdi mdi-shield-check-outline me-1"></i>
                Your profile has already been verified. Please contact the company if any correction is required.
            </div>
            <dl class="row mb-0">
                <dt class="col-sm-3">Name</dt>
                <dd class="col-sm-9">{{ $customer->name }}</dd>
                <dt class="col-sm-3">Mobile</dt>
                <dd class="col-sm-9">{{ $customer->phone ?? '—' }}</dd>
            </dl>
        </div>
    </div>

@elseif ($state === 'expired')
    <div class="card">
        <div class="card-body text-center py-5">
            <i class="mdi mdi-clock-alert-outline mdi-48px text-danger"></i>
            <h5 class="mt-2">Link Expired</h5>
            <p class="text-muted mb-0">This profile link has expired. Please contact the company for a new link.</p>
        </div>
    </div>

@elseif ($state === 'completed')
    @php
        $show = fn ($label, $value) => $value ? ['label' => $label, 'value' => $value] : null;
        $rows = array_filter([
            $show('Full Name (English)', $customer->full_name_en),
            $show('Full Name (Bangla)', $customer->full_name_bn),
            $show('Father Name (English)', $customer->father_name),
            $show('Father Name (Bangla)', $customer->father_name_bn),
            $show('Mother Name (English)', $customer->mother_name),
            $show('Mother Name (Bangla)', $customer->mother_name_bn),
            $show('Date of Birth', optional($customer->date_of_birth)->format('d M Y')),
            $show('Gender', $customer->gender ? ucfirst($customer->gender) : null),
            $show('Marital Status', $customer->marital_status ? ucfirst($customer->marital_status) : null),
            $show('Religion', $customer->religion),
            $show('Spouse Name', $customer->spouse_name),
            $show('Profession', $customer->profession),
            $show('Nationality', $customer->nationality),
            $show('Mobile', $customer->phone),
            $show('Alternative Mobile', $customer->alternative_mobile),
            $show('Email', $customer->email),
            $show('Present Address', $customer->present_address),
            $show('Permanent Address', $customer->permanent_address),
            $show('NID Number', $customer->nid_number),
            $show('TIN Number', $customer->tin_number),
            $show('Passport Number', $customer->passport_number),
            $show('Driving License Number', $customer->driving_license_number),
            $show('Nominee Name', $customer->nominee_name),
            $show('Nominee Relationship', $customer->nominee_relationship),
            $show('Nominee Mobile', $customer->nominee_mobile),
            $show('Nominee Address', $customer->nominee_address),
            $show('Nominee NID Number', $customer->nominee_nid_number),
            $show('Bank Name', $customer->bank_name),
            $show('Account Name', $customer->bank_account_name),
            $show('Account Number', $customer->bank_account_number),
            $show('Emergency Contact Name', $customer->emergency_contact_name),
            $show('Emergency Contact Mobile', $customer->emergency_contact_mobile),
            $customer->has_joint_owner ? $show('Joint Owner Name', $customer->joint_owner_name) : null,
            $customer->has_joint_owner ? $show('Joint Owner Mobile', $customer->joint_owner_mobile) : null,
            $customer->has_joint_owner ? $show('Joint Owner NID', $customer->joint_owner_nid) : null,
            $customer->has_joint_owner ? $show('Joint Owner Address', $customer->joint_owner_address) : null,
        ]);
    @endphp
    <div class="card">
        <div class="card-body">
            <div class="alert alert-info">
                <i class="mdi mdi-check-circle-outline me-1"></i>
                Your profile has been submitted. You can no longer edit this information yourself.
                Please contact the company if any correction is required.
            </div>
            <dl class="row mb-0">
                <dt class="col-sm-4">Name</dt>
                <dd class="col-sm-8">{{ $customer->name }}</dd>
                @foreach ($rows as $row)
                    <dt class="col-sm-4">{{ $row['label'] }}</dt>
                    <dd class="col-sm-8">{{ $row['value'] }}</dd>
                @endforeach
            </dl>
            @php $uploaded = collect(\App\Models\Customer::DOCUMENT_TYPES)->filter(fn ($l, $t) => $customer->documentOfType($t)); @endphp
            @if ($uploaded->isNotEmpty())
                <hr>
                <h6 class="fw-bold text-uppercase text-muted small mb-2">Documents</h6>
                @foreach ($uploaded as $type => $label)
                    <span class="badge bg-label-success me-1 mb-1">
                        <i class="mdi mdi-check me-1"></i>{{ $label }}
                    </span>
                @endforeach
            @endif
        </div>
    </div>

@else
    {{-- editable --}}
    @php
        $val = fn ($f) => old($f, $customer->{$f} ?? '');
        $sel = fn ($f, $opt) => old($f, $customer->{$f} ?? '') == $opt ? 'selected' : '';
        $dob = $customer->date_of_birth ? $customer->date_of_birth->format('Y-m-d') : old('date_of_birth');
    @endphp

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0 ps-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('customer-profile.update', $customer->profile_token) }}"
        enctype="multipart/form-data">
        @csrf

        <p class="text-muted small mb-3">
            Fields marked with <span class="text-danger">*</span> are required.
        </p>

        {{-- Personal --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold">1. Personal Information</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Full Name (English) <span class="text-danger">*</span></label>
                    <input type="text" name="full_name_en" class="form-control" value="{{ $val('full_name_en') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Full Name (Bangla) <span class="text-danger">*</span></label>
                    <input type="text" name="full_name_bn" class="form-control" value="{{ $val('full_name_bn') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Father Name (English) <span class="text-danger">*</span></label>
                    <input type="text" name="father_name" class="form-control" value="{{ $val('father_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Father Name (Bangla) <span class="text-danger">*</span></label>
                    <input type="text" name="father_name_bn" class="form-control" value="{{ $val('father_name_bn') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mother Name (English) <span class="text-danger">*</span></label>
                    <input type="text" name="mother_name" class="form-control" value="{{ $val('mother_name') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mother Name (Bangla) <span class="text-danger">*</span></label>
                    <input type="text" name="mother_name_bn" class="form-control" value="{{ $val('mother_name_bn') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Date of Birth <span class="text-danger">*</span></label>
                    <input type="date" name="date_of_birth" class="form-control" value="{{ $dob }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Gender <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select" required>
                        <option value="">—</option>
                        <option value="male" {{ $sel('gender', 'male') }}>Male</option>
                        <option value="female" {{ $sel('gender', 'female') }}>Female</option>
                        <option value="other" {{ $sel('gender', 'other') }}>Other</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Marital Status</label>
                    <select name="marital_status" class="form-select">
                        <option value="">—</option>
                        <option value="single" {{ $sel('marital_status', 'single') }}>Single</option>
                        <option value="married" {{ $sel('marital_status', 'married') }}>Married</option>
                        <option value="divorced" {{ $sel('marital_status', 'divorced') }}>Divorced</option>
                        <option value="widowed" {{ $sel('marital_status', 'widowed') }}>Widowed</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Profession</label>
                    <input type="text" name="profession" class="form-control" value="{{ $val('profession') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Nationality</label>
                    <input type="text" name="nationality" class="form-control" value="{{ $val('nationality') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Religion</label>
                    <input type="text" name="religion" class="form-control" value="{{ $val('religion') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Spouse Name</label>
                    <input type="text" name="spouse_name" class="form-control" value="{{ $val('spouse_name') }}">
                </div>
            </div>
        </div>

        {{-- Contact --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold">2. Contact Information</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Mobile <span class="text-danger">*</span></label>
                    <input type="text" name="phone" class="form-control" value="{{ $val('phone') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Alternative Mobile</label>
                    <input type="text" name="alternative_mobile" class="form-control" value="{{ $val('alternative_mobile') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Email</label>
                    <input type="email" name="email" class="form-control" value="{{ $val('email') }}">
                </div>
                <div class="col-md-6"></div>
                <div class="col-md-6">
                    <label class="form-label">Present Address <span class="text-danger">*</span></label>
                    <input type="text" name="present_address" class="form-control" value="{{ $val('present_address') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Permanent Address <span class="text-danger">*</span></label>
                    <input type="text" name="permanent_address" class="form-control" value="{{ $val('permanent_address') }}" required>
                </div>
            </div>
        </div>

        {{-- Identity --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold">3. Identity Information</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-3">
                    <label class="form-label">NID Number <span class="text-danger">*</span></label>
                    <input type="text" name="nid_number" class="form-control" value="{{ $val('nid_number') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">TIN Number <span class="text-danger">*</span></label>
                    <input type="text" name="tin_number" class="form-control" value="{{ $val('tin_number') }}" required>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Passport Number</label>
                    <input type="text" name="passport_number" class="form-control" value="{{ $val('passport_number') }}">
                </div>
                <div class="col-md-3">
                    <label class="form-label">Driving License Number</label>
                    <input type="text" name="driving_license_number" class="form-control" value="{{ $val('driving_license_number') }}">
                </div>
            </div>
        </div>

        {{-- Nominee --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold">4. Nominee Information</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Nominee Name <span class="text-danger">*</span></label>
                    <input type="text" name="nominee_name" class="form-control" value="{{ $val('nominee_name') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Relationship <span class="text-danger">*</span></label>
                    <input type="text" name="nominee_relationship" class="form-control" value="{{ $val('nominee_relationship') }}" required>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Mobile <span class="text-danger">*</span></label>
                    <input type="text" name="nominee_mobile" class="form-control" value="{{ $val('nominee_mobile') }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label">Address</label>
                    <input type="text" name="nominee_address" class="form-control" value="{{ $val('nominee_address') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">NID Number</label>
                    <input type="text" name="nominee_nid_number" class="form-control" value="{{ $val('nominee_nid_number') }}">
                </div>
            </div>
        </div>

        {{-- Financial --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold">5. Financial Information</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-4">
                    <label class="form-label">Bank Name</label>
                    <input type="text" name="bank_name" class="form-control" value="{{ $val('bank_name') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Name</label>
                    <input type="text" name="bank_account_name" class="form-control" value="{{ $val('bank_account_name') }}">
                </div>
                <div class="col-md-4">
                    <label class="form-label">Account Number</label>
                    <input type="text" name="bank_account_number" class="form-control" value="{{ $val('bank_account_number') }}">
                </div>
            </div>
        </div>

        {{-- Emergency Contact --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold">6. Emergency Contact</h6></div>
            <div class="card-body row g-3">
                <div class="col-md-6">
                    <label class="form-label">Name</label>
                    <input type="text" name="emergency_contact_name" class="form-control" value="{{ $val('emergency_contact_name') }}">
                </div>
                <div class="col-md-6">
                    <label class="form-label">Mobile</label>
                    <input type="text" name="emergency_contact_mobile" class="form-control" value="{{ $val('emergency_contact_mobile') }}">
                </div>
            </div>
        </div>

        {{-- Documents --}}
        <div class="card mb-3">
            <div class="card-header"><h6 class="mb-0 fw-bold">7. Documents</h6></div>
            <div class="card-body">
                <p class="text-muted small mb-3">Image or PDF only, max 3 MB per file.</p>
                <div class="row g-3">
                    @php
                        $requiredDocs = ['photo' => 'Photo', 'nid_front' => 'NID Front', 'nid_back' => 'NID Back'];
                        $optionalDocs = ['tin' => 'TIN Copy', 'passport' => 'Passport Copy', 'nominee_nid' => 'Nominee NID'];
                    @endphp
                    @foreach ($requiredDocs as $type => $label)
                        @php $existing = $customer->documentOfType($type); @endphp
                        <div class="col-md-4">
                            <label class="form-label">{{ $label }} <span class="text-danger">*</span></label>
                            <input type="file" name="documents[{{ $type }}]" class="form-control"
                                accept="image/*,application/pdf" {{ $existing ? '' : 'required' }}>
                            @if ($existing)
                                <small class="text-success"><i class="mdi mdi-check"></i> Already uploaded — upload again to replace.</small>
                            @endif
                        </div>
                    @endforeach
                    @foreach ($optionalDocs as $type => $label)
                        <div class="col-md-4">
                            <label class="form-label">{{ $label }}</label>
                            <input type="file" name="documents[{{ $type }}]" class="form-control"
                                accept="image/*,application/pdf">
                            @if ($customer->documentOfType($type))
                                <small class="text-success"><i class="mdi mdi-check"></i> Already uploaded — upload again to replace.</small>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- ═══════════════ JOINT OWNER (hidden until enabled) ═══════════════ --}}
        <div class="card mb-3">
            <div class="card-body">
                <div class="form-check form-switch">
                    <input class="form-check-input" type="checkbox" role="switch" id="has_joint_owner"
                        name="has_joint_owner" value="1"
                        {{ old('has_joint_owner', $customer->has_joint_owner) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold" for="has_joint_owner">Has Joint Owner</label>
                </div>

                <div id="jointOwnerFields" class="row g-3 mt-1" style="display: none;">
                    <div class="col-md-6">
                        <label class="form-label">Joint Owner Name <span class="text-danger">*</span></label>
                        <input type="text" name="joint_owner_name" class="form-control" value="{{ $val('joint_owner_name') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Joint Owner Mobile <span class="text-danger">*</span></label>
                        <input type="text" name="joint_owner_mobile" class="form-control" value="{{ $val('joint_owner_mobile') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Joint Owner NID <span class="text-danger">*</span></label>
                        <input type="text" name="joint_owner_nid" class="form-control" value="{{ $val('joint_owner_nid') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Joint Owner Address</label>
                        <input type="text" name="joint_owner_address" class="form-control" value="{{ $val('joint_owner_address') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Joint Owner Photo</label>
                        <input type="file" name="documents[joint_owner_photo]" class="form-control"
                            accept="image/*,application/pdf">
                        @if ($customer->documentOfType('joint_owner_photo'))
                            <small class="text-success"><i class="mdi mdi-check"></i> Already uploaded — upload again to replace.</small>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <div class="alert alert-warning">
            <i class="mdi mdi-alert-outline me-1"></i>
            Please carefully review all information before submission. After submission, you will not be able to edit the information yourself.
        </div>

        <div class="d-flex justify-content-end mb-5">
            <button type="submit" class="btn btn-primary btn-lg"
                onclick="return confirm('Submit your profile? You will not be able to edit it afterwards.')">
                Submit Profile
            </button>
        </div>
    </form>

    @push('scripts')
    <script>
        (function () {
            var toggle = document.getElementById('has_joint_owner');
            var fields = document.getElementById('jointOwnerFields');
            if (!toggle || !fields) return;

            var required = ['joint_owner_name', 'joint_owner_mobile', 'joint_owner_nid'];

            function sync() {
                var on = toggle.checked;
                fields.style.display = on ? '' : 'none';
                required.forEach(function (name) {
                    var el = fields.querySelector('[name="' + name + '"]');
                    if (el) { on ? el.setAttribute('required', 'required') : el.removeAttribute('required'); }
                });
            }
            toggle.addEventListener('change', sync);
            sync();
        })();
    </script>
    @endpush
@endif
@endsection
