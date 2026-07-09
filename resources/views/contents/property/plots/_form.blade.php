@php
    $plot = $plot ?? null;
    $val = fn ($field, $default = null) => old($field, $plot->$field ?? $default);
    $documentCategories = $documentCategories ?? collect();
    $oldDocuments = old('documents', []);
    if (empty($oldDocuments)) { $oldDocuments = [[]]; }
    $categoryOptionsHtml = $documentCategories->isNotEmpty()
        ? view('contents.property.plots._document_options', ['documentCategories' => $documentCategories])->render()
        : '';
    $oldSellers = old('sellers', $plot?->sellers?->map(fn ($s) => [
        'uuid' => $s->uuid, 'name' => $s->name, 'phone' => $s->phone, 'nid' => $s->nid, 'address' => $s->address,
        'nid_front' => $s->nid_front, 'nid_back' => $s->nid_back, 'photo' => $s->photo,
    ])->values()->all() ?? []);
    $oldOwners = old('owners', $plot?->owners?->map(fn ($o) => [
        'uuid' => $o->uuid, 'name' => $o->name, 'phone' => $o->phone, 'nid' => $o->nid,
        'address' => $o->address, 'ownership_percentage' => $o->ownership_percentage,
        'nid_front' => $o->nid_front, 'nid_back' => $o->nid_back, 'photo' => $o->photo,
    ])->values()->all() ?? []);
    if (empty($oldSellers)) { $oldSellers = [['name' => '', 'phone' => '', 'nid' => '', 'address' => '']]; }
    if (empty($oldOwners)) { $oldOwners = [['name' => '', 'phone' => '', 'nid' => '', 'address' => '', 'ownership_percentage' => '']]; }

    // Existing stored path for a person's image field (survives validation failures).
    $existingImage = fn ($person, $field) => $person[$field . '_existing'] ?? $person[$field] ?? null;

    // "Paid" checkboxes: which cost fields already have an auto-generated cash-out payment.
    $fieldPaymentTypes = \App\Domains\Plot\Services\PlotService::FIELD_PAYMENT_TYPES;
    $paidTypes = $plot?->payments?->where('auto_generated', true)->pluck('payment_type')->all() ?? [];
    $isPaid = function ($field) use ($fieldPaymentTypes, $paidTypes) {
        $old = old('paid');
        if (is_array($old)) {
            return ! empty($old[$field]);
        }
        return in_array($fieldPaymentTypes[$field] ?? null, $paidTypes, true);
    };
@endphp

@php
    // Reusable NID/photo upload sub-row for a seller or owner.
    // $group = 'sellers' | 'owners', $type = 'seller' | 'owner'
@endphp
@php
    $imageRow = function ($group, $type, $i, $person) use ($existingImage) {
        return view('contents.property.plots._person_images', [
            'group' => $group, 'type' => $type, 'i' => $i, 'person' => $person, 'existingImage' => $existingImage,
        ])->render();
    };
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

{{-- Basic information --}}
<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-information-outline me-1"></i>Basic Information</h6>
<div class="row">
    <div class="col-md-5 mb-3">
        <label for="plot_name" class="form-label">Plot Name <span class="text-danger">*</span></label>
        <input type="text" class="form-control" id="plot_name" name="plot_name" value="{{ $val('plot_name') }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="plot_code" class="form-label">Plot Code</label>
        <input type="text" class="form-control" id="plot_code" name="plot_code" value="{{ $val('plot_code') }}" placeholder="Auto-generated if left blank">
    </div>
    <div class="col-md-3 mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select" id="status" name="status" required>
            @foreach (\App\Models\Plot::STATUSES as $st)
                <option value="{{ $st }}" {{ $val('status', 'prospect') === $st ? 'selected' : '' }}>
                    {{ ucwords(str_replace('_', ' ', $st)) }}
                </option>
            @endforeach
        </select>
    </div>
</div>

{{-- Location --}}
<hr>
<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-map-marker-outline me-1"></i>Location</h6>
<div class="row">
    <div class="col-md-3 mb-3">
        <label for="division" class="form-label">Division</label>
        <input type="text" class="form-control" id="division" name="division" value="{{ $val('division') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="district" class="form-label">District</label>
        <input type="text" class="form-control" id="district" name="district" value="{{ $val('district') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="upazila" class="form-label">Upazila</label>
        <input type="text" class="form-control" id="upazila" name="upazila" value="{{ $val('upazila') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="area" class="form-label">Area</label>
        <input type="text" class="form-control" id="area" name="area" value="{{ $val('area') }}">
    </div>
    <div class="col-12 mb-3">
        <label for="address" class="form-label">Address</label>
        <textarea class="form-control" id="address" name="address" rows="2">{{ $val('address') }}</textarea>
    </div>
</div>

{{-- Land records --}}
<hr>
<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-file-document-outline me-1"></i>Land Records</h6>
<div class="row">
    <div class="col-md-3 mb-3">
        <label for="mouza" class="form-label">Mouza</label>
        <input type="text" class="form-control" id="mouza" name="mouza" value="{{ $val('mouza') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="jl_no" class="form-label">JL No</label>
        <input type="text" class="form-control" id="jl_no" name="jl_no" value="{{ $val('jl_no') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="khatian_no" class="form-label">Khatian No</label>
        <input type="text" class="form-control" id="khatian_no" name="khatian_no" value="{{ $val('khatian_no') }}">
    </div>
    <div class="col-md-3 mb-3">
        <label for="dag_no" class="form-label">Dag No</label>
        <input type="text" class="form-control" id="dag_no" name="dag_no" value="{{ $val('dag_no') }}">
    </div>
</div>

{{-- Land details --}}
<hr>
<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-vector-square me-1"></i>Land Details</h6>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="land_size" class="form-label">Land Size</label>
        <input type="number" step="0.0001" min="0" class="form-control" id="land_size" name="land_size" value="{{ $val('land_size') }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="land_unit" class="form-label">Land Unit <span class="text-danger">*</span></label>
        <select class="form-select" id="land_unit" name="land_unit" required>
            @foreach (['katha' => 'Katha', 'decimal' => 'Decimal', 'acre' => 'Acre'] as $v => $l)
                <option value="{{ $v }}" {{ $val('land_unit', 'katha') === $v ? 'selected' : '' }}>{{ $l }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="price_per_katha" class="form-label">Price / <span id="price-per-unit-label">{{ ucfirst($val('land_unit', 'katha')) }}</span></label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control" id="price_per_katha" name="price_per_katha" value="{{ $val('price_per_katha') }}">
        </div>
    </div>
</div>

{{-- Share division (for customer bookings) --}}
<hr>
<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-home-city-outline me-1"></i>Share Division</h6>
<p class="text-muted small mb-3">Divide this plot into predefined shares (per share = per flat) so customers can book them. The price per share is set later at booking time.</p>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="total_shares" class="form-label">Total Shares</label>
        <input type="number" min="0" step="1" class="form-control" id="total_shares" name="total_shares" value="{{ $val('total_shares') }}" placeholder="e.g. 8">
        @if ($plot && $plot->exists && $plot->total_shares)
            <div class="form-text">{{ $plot->shares_sold }} booked · {{ $plot->shares_available }} available</div>
        @endif
    </div>
</div>


{{-- Purchase information --}}
<hr>
<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-cash-multiple me-1"></i>Purchase Information</h6>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="purchase_price" class="form-label">Purchase Price</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control cost-input" id="purchase_price" name="purchase_price" value="{{ $val('purchase_price', '0') }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1" type="checkbox" name="paid[purchase_price]" value="1" title="Mark as paid" {{ $isPaid('purchase_price') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="bayna_amount" class="form-label">Bayna Amount</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control" id="bayna_amount" name="bayna_amount" value="{{ $val('bayna_amount', '0') }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1" type="checkbox" name="paid[bayna_amount]" value="1" title="Mark as paid" {{ $isPaid('bayna_amount') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="registration_cost" class="form-label">Registration Cost</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control cost-input" id="registration_cost" name="registration_cost" value="{{ $val('registration_cost', '0') }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1" type="checkbox" name="paid[registration_cost]" value="1" title="Mark as paid" {{ $isPaid('registration_cost') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="mutation_cost" class="form-label">Mutation Cost</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control cost-input" id="mutation_cost" name="mutation_cost" value="{{ $val('mutation_cost', '0') }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1" type="checkbox" name="paid[mutation_cost]" value="1" title="Mark as paid" {{ $isPaid('mutation_cost') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="legal_cost" class="form-label">Legal Cost</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control cost-input" id="legal_cost" name="legal_cost" value="{{ $val('legal_cost', '0') }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1" type="checkbox" name="paid[legal_cost]" value="1" title="Mark as paid" {{ $isPaid('legal_cost') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="broker_cost" class="form-label">Broker Cost</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control cost-input" id="broker_cost" name="broker_cost" value="{{ $val('broker_cost', '0') }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1" type="checkbox" name="paid[broker_cost]" value="1" title="Mark as paid" {{ $isPaid('broker_cost') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="other_cost" class="form-label">Other Cost</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" step="0.01" min="0" class="form-control cost-input" id="other_cost" name="other_cost" value="{{ $val('other_cost', '0') }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1" type="checkbox" name="paid[other_cost]" value="1" title="Mark as paid" {{ $isPaid('other_cost') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Total Acquisition Cost <small class="text-muted">(auto-calculated)</small></label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="text" class="form-control fw-bold" id="total_acquisition_cost" value="0.00" readonly>
        </div>
    </div>
</div>

{{-- Sellers --}}
<hr>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h6 class="fw-bold text-primary mb-0"><i class="mdi mdi-account-arrow-right-outline me-1"></i>Sellers</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-seller"><i class="mdi mdi-plus"></i> Add Seller</button>
</div>
<div id="sellers-wrapper">
    @foreach ($oldSellers as $i => $seller)
        <div class="seller-row border rounded p-2 mb-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label small">Name</label><input type="text" class="form-control" name="sellers[{{ $i }}][name]" value="{{ $seller['name'] ?? '' }}"></div>
                <div class="col-md-2"><label class="form-label small">Phone</label><input type="text" class="form-control" name="sellers[{{ $i }}][phone]" value="{{ $seller['phone'] ?? '' }}"></div>
                <div class="col-md-3"><label class="form-label small">NID</label><input type="text" class="form-control" name="sellers[{{ $i }}][nid]" value="{{ $seller['nid'] ?? '' }}"></div>
                <div class="col-md-3"><label class="form-label small">Address</label><input type="text" class="form-control" name="sellers[{{ $i }}][address]" value="{{ $seller['address'] ?? '' }}"></div>
                <div class="col-md-1"><button type="button" class="btn btn-icon btn-outline-danger remove-row"><i class="mdi mdi-delete-outline"></i></button></div>
            </div>
            {!! $imageRow('sellers', 'seller', $i, $seller) !!}
        </div>
    @endforeach
</div>

{{-- Legal owners --}}
<hr>
<div class="d-flex justify-content-between align-items-center mb-1">
    <h6 class="fw-bold text-primary mb-0"><i class="mdi mdi-account-key-outline me-1"></i>Legal Land Owners</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-owner"><i class="mdi mdi-plus"></i> Add Owner</button>
</div>
<p class="text-muted small mb-3">Legal owners of the land with their ownership share. This is separate from company shareholders.</p>
<div id="owners-wrapper">
    @foreach ($oldOwners as $i => $owner)
        <div class="owner-row border rounded p-2 mb-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label small">Name</label><input type="text" class="form-control" name="owners[{{ $i }}][name]" value="{{ $owner['name'] ?? '' }}"></div>
                <div class="col-md-2"><label class="form-label small">Phone</label><input type="text" class="form-control" name="owners[{{ $i }}][phone]" value="{{ $owner['phone'] ?? '' }}"></div>
                <div class="col-md-2"><label class="form-label small">NID</label><input type="text" class="form-control" name="owners[{{ $i }}][nid]" value="{{ $owner['nid'] ?? '' }}"></div>
                <div class="col-md-2"><label class="form-label small">Address</label><input type="text" class="form-control" name="owners[{{ $i }}][address]" value="{{ $owner['address'] ?? '' }}"></div>
                <div class="col-md-2"><label class="form-label small">Ownership %</label><input type="number" step="0.0001" min="0" max="100" class="form-control" name="owners[{{ $i }}][ownership_percentage]" value="{{ $owner['ownership_percentage'] ?? '' }}"></div>
                <div class="col-md-1"><button type="button" class="btn btn-icon btn-outline-danger remove-row"><i class="mdi mdi-delete-outline"></i></button></div>
            </div>
            {!! $imageRow('owners', 'owner', $i, $owner) !!}
        </div>
    @endforeach
</div>

{{-- Notes --}}
<hr>
<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea class="form-control" id="notes" name="notes" rows="2">{{ $val('notes') }}</textarea>
</div>

{{-- Documents --}}
<hr>
<div class="d-flex justify-content-between align-items-center mb-1">
    <h6 class="fw-bold text-primary mb-0"><i class="mdi mdi-file-upload-outline me-1"></i>Documents</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-document"><i class="mdi mdi-plus"></i> Add Document</button>
</div>
<p class="text-muted small mb-3">Image or PDF, max 3&nbsp;MB each. Selecting <em>Other Document</em> requires a title and description.</p>

@if ($plot && $plot->relationLoaded('documents') && $plot->documents->isNotEmpty())
    <div class="table-responsive mb-3">
        <table class="table table-sm mb-0">
            <thead><tr><th>Preview</th><th>Title</th><th>Category</th><th>File</th><th class="text-end">Download</th></tr></thead>
            <tbody>
                @foreach ($plot->documents as $doc)
                    <tr>
                        <td>@include('contents.property.plots._document_thumb', ['doc' => $doc])</td>
                        <td class="fw-medium">{{ $doc->title }}</td>
                        <td>{{ $doc->category?->name ?? '—' }}</td>
                        <td>{{ $doc->file_name }}</td>
                        <td class="text-end">
                            <a href="{{ url("/documents/{$doc->uuid}/download") }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Download">
                                <i class="mdi mdi-download-outline"></i>
                            </a>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@endif

<div id="documents-wrapper">
    @foreach ($oldDocuments as $i => $doc)
        <div class="document-row border rounded p-2 mb-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label class="form-label small">Category</label>
                    <select class="form-select" name="documents[{{ $i }}][category_id]">
                        <option value="">— Select —</option>
                        {!! view('contents.property.plots._document_options', ['documentCategories' => $documentCategories, 'selected' => $doc['category_id'] ?? null])->render() !!}
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label small">Title</label>
                    <input type="text" class="form-control" name="documents[{{ $i }}][title]" value="{{ $doc['title'] ?? '' }}" placeholder="Defaults to category name">
                </div>
                <div class="col-md-5">
                    <label class="form-label small">File</label>
                    <input type="file" class="form-control" name="documents[{{ $i }}][file]" accept="image/*,application/pdf">
                </div>
                <div class="col-md-1">
                    <button type="button" class="btn btn-icon btn-outline-danger remove-row"><i class="mdi mdi-delete-outline"></i></button>
                </div>
                <div class="col-12">
                    <label class="form-label small">Description</label>
                    <textarea class="form-control" name="documents[{{ $i }}][description]" rows="1" placeholder="Notes about this document">{{ $doc['description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    @endforeach
</div>

@push('scripts')
<script>
(function () {
    // Auto-calculate total acquisition cost (excludes bayna advance).
    function recalcTotal() {
        var total = 0;
        document.querySelectorAll('.cost-input').forEach(function (el) {
            total += parseFloat(el.value) || 0;
        });
        document.getElementById('total_acquisition_cost').value = total.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    document.querySelectorAll('.cost-input').forEach(function (el) {
        el.addEventListener('input', recalcTotal);
    });
    recalcTotal();

    // Dynamic "Price / <unit>" label + auto-calculate purchase price.
    var unitLabels = { katha: 'Katha', decimal: 'Decimal', acre: 'Acre' };
    var landSizeEl = document.getElementById('land_size');
    var landUnitEl = document.getElementById('land_unit');
    var pricePerUnitEl = document.getElementById('price_per_katha');
    var purchasePriceEl = document.getElementById('purchase_price');
    var unitLabelEl = document.getElementById('price-per-unit-label');

    function updateUnitLabel() {
        if (unitLabelEl) {
            unitLabelEl.textContent = unitLabels[landUnitEl.value] || 'Unit';
        }
    }

    // Purchase price = land size × price per unit. Overwrites the field so it
    // stays in sync while a source value changes, but remains freely editable.
    function recalcPurchasePrice() {
        var size = parseFloat(landSizeEl.value) || 0;
        var rate = parseFloat(pricePerUnitEl.value) || 0;
        purchasePriceEl.value = (size * rate).toFixed(2);
        recalcTotal();
    }

    landUnitEl.addEventListener('change', function () {
        updateUnitLabel();
        recalcPurchasePrice();
    });
    landSizeEl.addEventListener('input', recalcPurchasePrice);
    pricePerUnitEl.addEventListener('input', recalcPurchasePrice);
    updateUnitLabel();

    // Repeater rows for sellers and owners.
    function nextIndex(wrapper, rowClass) {
        return wrapper.querySelectorAll('.' + rowClass).length;
    }

    function personImagesHtml(group, i) {
        var fields = [['nid_front', 'NID Front'], ['nid_back', 'NID Back'], ['photo', 'Photo']];
        var cols = fields.map(function (f) {
            return '<div class="col-md-4">' +
                '<label class="form-label small text-muted">' + f[1] + '</label>' +
                '<input type="hidden" name="' + group + '[' + i + '][uuid]" value="">' +
                '<input type="file" accept="image/*" class="form-control form-control-sm" name="' + group + '[' + i + '][' + f[0] + ']">' +
                '</div>';
        }).join('');
        return '<div class="row g-2 mt-1">' + cols + '</div>';
    }

    document.getElementById('add-seller').addEventListener('click', function () {
        var wrapper = document.getElementById('sellers-wrapper');
        var i = nextIndex(wrapper, 'seller-row');
        var row = document.createElement('div');
        row.className = 'seller-row border rounded p-2 mb-2';
        row.innerHTML =
            '<div class="row g-2 align-items-end">' +
            '<div class="col-md-3"><input type="text" class="form-control" name="sellers[' + i + '][name]" placeholder="Name"></div>' +
            '<div class="col-md-2"><input type="text" class="form-control" name="sellers[' + i + '][phone]" placeholder="Phone"></div>' +
            '<div class="col-md-3"><input type="text" class="form-control" name="sellers[' + i + '][nid]" placeholder="NID"></div>' +
            '<div class="col-md-3"><input type="text" class="form-control" name="sellers[' + i + '][address]" placeholder="Address"></div>' +
            '<div class="col-md-1"><button type="button" class="btn btn-icon btn-outline-danger remove-row"><i class="mdi mdi-delete-outline"></i></button></div>' +
            '</div>' +
            personImagesHtml('sellers', i);
        wrapper.appendChild(row);
    });

    document.getElementById('add-owner').addEventListener('click', function () {
        var wrapper = document.getElementById('owners-wrapper');
        var i = nextIndex(wrapper, 'owner-row');
        var row = document.createElement('div');
        row.className = 'owner-row border rounded p-2 mb-2';
        row.innerHTML =
            '<div class="row g-2 align-items-end">' +
            '<div class="col-md-3"><input type="text" class="form-control" name="owners[' + i + '][name]" placeholder="Name"></div>' +
            '<div class="col-md-2"><input type="text" class="form-control" name="owners[' + i + '][phone]" placeholder="Phone"></div>' +
            '<div class="col-md-2"><input type="text" class="form-control" name="owners[' + i + '][nid]" placeholder="NID"></div>' +
            '<div class="col-md-2"><input type="text" class="form-control" name="owners[' + i + '][address]" placeholder="Address"></div>' +
            '<div class="col-md-2"><input type="number" step="0.0001" min="0" max="100" class="form-control" name="owners[' + i + '][ownership_percentage]" placeholder="Ownership %"></div>' +
            '<div class="col-md-1"><button type="button" class="btn btn-icon btn-outline-danger remove-row"><i class="mdi mdi-delete-outline"></i></button></div>' +
            '</div>' +
            personImagesHtml('owners', i);
        wrapper.appendChild(row);
    });

    document.addEventListener('click', function (e) {
        var btn = e.target.closest('.remove-row');
        if (btn) {
            var row = btn.closest('.seller-row, .owner-row, .document-row');
            if (row) row.remove();
        }
    });

    // Repeater rows for documents.
    var docCategoryOptions = @json($categoryOptionsHtml ?? '');
    var addDocumentBtn = document.getElementById('add-document');
    if (addDocumentBtn) {
        addDocumentBtn.addEventListener('click', function () {
            var wrapper = document.getElementById('documents-wrapper');
            var i = wrapper.querySelectorAll('.document-row').length;
            var row = document.createElement('div');
            row.className = 'document-row border rounded p-2 mb-2';
            row.innerHTML =
                '<div class="row g-2 align-items-end">' +
                '<div class="col-md-3"><label class="form-label small">Category</label>' +
                '<select class="form-select" name="documents[' + i + '][category_id]"><option value="">— Select —</option>' + docCategoryOptions + '</select></div>' +
                '<div class="col-md-3"><label class="form-label small">Title</label>' +
                '<input type="text" class="form-control" name="documents[' + i + '][title]" placeholder="Defaults to category name"></div>' +
                '<div class="col-md-5"><label class="form-label small">File</label>' +
                '<input type="file" class="form-control" name="documents[' + i + '][file]" accept="image/*,application/pdf"></div>' +
                '<div class="col-md-1"><button type="button" class="btn btn-icon btn-outline-danger remove-row"><i class="mdi mdi-delete-outline"></i></button></div>' +
                '<div class="col-12"><label class="form-label small">Description</label>' +
                '<textarea class="form-control" name="documents[' + i + '][description]" rows="1" placeholder="Notes about this document"></textarea></div>' +
                '</div>';
            wrapper.appendChild(row);
        });
    }
})();
</script>
@endpush
