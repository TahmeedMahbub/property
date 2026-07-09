@php
    $booking = $booking ?? null;
    $val = fn ($field, $default = null) => old($field, $booking->$field ?? $default);
    $plots = $plots ?? collect();
    $customers = $customers ?? collect();
    $documentCategories = $documentCategories ?? collect();

    $oldInstallments = old('installments', $booking?->installments?->map(fn ($i) => [
        'title' => $i->title,
        'due_date' => optional($i->due_date)->format('Y-m-d'),
        'amount' => $i->amount,
        'notes' => $i->notes,
    ])->values()->all() ?? []);
    if (empty($oldInstallments)) {
        $oldInstallments = [['title' => '', 'due_date' => '', 'amount' => '', 'notes' => '']];
    }

    $oldDocuments = old('documents', []);
    if (empty($oldDocuments)) { $oldDocuments = [[]]; }
    $categoryOptionsHtml = $documentCategories->isNotEmpty()
        ? view('contents.property.plots._document_options', ['documentCategories' => $documentCategories])->render()
        : '';

    $selectedPlot = $val('plot_id');
    $selectedCustomer = $val('customer_id');

    // "Paid" checkboxes: which amount fields already have an auto-generated cash-in payment.
    $fieldPaymentTypes = \App\Domains\Plot\Services\PlotBookingService::FIELD_PAYMENT_TYPES;
    $paidTypes = $booking ? $booking->payments()->where('auto_generated', true)->pluck('payment_type')->all() : [];
    // Cash already received through manually recorded payments (not the checkboxes).
    $manualPaid = $booking ? (float) $booking->payments()->where('auto_generated', false)->sum('amount') : 0.0;
    $isPaid = function ($field) use ($booking, $fieldPaymentTypes, $paidTypes) {
        $old = old('paid');
        if (is_array($old)) {
            return ! empty($old[$field]);
        }
        if (! $booking) {
            return $field === 'booking_money'; // booking money defaults to paid on new bookings
        }
        return in_array($fieldPaymentTypes[$field] ?? null, $paidTypes, true);
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

{{-- Booking details --}}
<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-clipboard-text-outline me-1"></i>Booking Details</h6>
<div class="row">
    <div class="col-md-6 mb-3">
        <label for="plot_id" class="form-label">Plot <span class="text-danger">*</span></label>
        <select class="form-select" id="plot_id" name="plot_id" required>
            <option value="">— Select Plot —</option>
            @foreach ($plots as $plot)
                <option value="{{ $plot->id }}" data-available="{{ $plot->shares_available }}"
                    {{ (string) $selectedPlot === (string) $plot->id ? 'selected' : '' }}>
                    {{ $plot->plot_name }} ({{ $plot->plot_code }}) — {{ $plot->shares_available }} share(s) available
                </option>
            @endforeach
        </select>
        <div class="form-text" id="shares-available-hint"></div>
    </div>
    <div class="col-md-6 mb-3">
        <label for="customer_id" class="form-label">Customer <span class="text-danger">*</span></label>
        <select class="form-select" id="customer_id" name="customer_id" required>
            <option value="">— Select Customer —</option>
            @foreach ($customers as $customer)
                <option value="{{ $customer->id }}" {{ (string) $selectedCustomer === (string) $customer->id ? 'selected' : '' }}>
                    {{ $customer->name }}{{ $customer->phone ? ' — ' . $customer->phone : '' }}
                </option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label for="booking_date" class="form-label">Booking Date</label>
        <input type="date" class="form-control" id="booking_date" name="booking_date"
            value="{{ old('booking_date', optional($booking?->booking_date)->format('Y-m-d') ?? date('Y-m-d')) }}">
    </div>
    <div class="col-md-4 mb-3">
        <label for="status" class="form-label">Status <span class="text-danger">*</span></label>
        <select class="form-select" id="status" name="status" required>
            @foreach (\App\Models\PlotBooking::STATUSES as $st)
                <option value="{{ $st }}" {{ $val('status', 'booked') === $st ? 'selected' : '' }}>{{ ucfirst($st) }}</option>
            @endforeach
        </select>
    </div>
    @if ($booking)
        <div class="col-md-4 mb-3">
            <label class="form-label">Booking No</label>
            <input type="text" class="form-control" value="{{ $booking->booking_no }}" readonly disabled>
        </div>
    @endif
</div>

{{-- Shares & pricing --}}
<hr>
<h6 class="fw-bold text-primary mb-3"><i class="mdi mdi-home-city-outline me-1"></i>Shares &amp; Pricing</h6>
<p class="text-muted small mb-3">Each share represents one flat. Price per share is set at booking time.</p>
<div class="row">
    <div class="col-md-4 mb-3">
        <label for="shares_count" class="form-label">Number of Shares <span class="text-danger">*</span></label>
        <input type="number" min="1" step="1" class="form-control amount-input" id="shares_count" name="shares_count" value="{{ $val('shares_count', 1) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label for="share_price" class="form-label">Price per Share <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" min="0" step="0.01" class="form-control amount-input" id="share_price" name="share_price" value="{{ $val('share_price') }}" required>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Share Value</label>
        <input type="text" class="form-control" id="share_value_display" value="0.00" readonly disabled>
    </div>
    <div class="col-md-4 mb-3">
        <label for="booking_money" class="form-label">Booking Money <span class="text-danger">*</span></label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" min="0" step="0.01" class="form-control amount-input" id="booking_money" name="booking_money" value="{{ $val('booking_money', 0) }}" required>
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1 paid-input" type="checkbox" name="paid[booking_money]" value="1" data-target="booking_money" title="Mark as paid" {{ $isPaid('booking_money') ? 'checked' : '' }}> Paid
            </span>
        </div>
        <div class="form-text">Mandatory to book (0 allowed for edge cases).</div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="registration_fee" class="form-label">Registration Fee</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" min="0" step="0.01" class="form-control amount-input" id="registration_fee" name="registration_fee" value="{{ $val('registration_fee', 0) }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1 paid-input" type="checkbox" name="paid[registration_fee]" value="1" data-target="registration_fee" title="Mark as paid" {{ $isPaid('registration_fee') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="other_fee" class="form-label">Other Fee</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" min="0" step="0.01" class="form-control amount-input" id="other_fee" name="other_fee" value="{{ $val('other_fee', 0) }}">
            <span class="input-group-text">
                <input class="form-check-input mt-0 me-1 paid-input" type="checkbox" name="paid[other_fee]" value="1" data-target="other_fee" title="Mark as paid" {{ $isPaid('other_fee') ? 'checked' : '' }}> Paid
            </span>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label for="discount" class="form-label">Discount</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="number" min="0" step="0.01" class="form-control amount-input" id="discount" name="discount" value="{{ $val('discount', 0) }}">
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Total Payable</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="text" class="form-control fw-bold" id="total_payable_display" value="0.00" readonly disabled>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Total Paid</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="text" class="form-control text-success fw-medium" id="total_paid_display" value="0.00" readonly disabled>
        </div>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label fw-bold">Total Due</label>
        <div class="input-group">
            <span class="input-group-text">৳</span>
            <input type="text" class="form-control fw-bold text-danger" id="total_due_display" value="0.00" readonly disabled>
        </div>
    </div>
    <div class="col-12 mb-3">
        <label for="other_info" class="form-label">Extra Information</label>
        <textarea class="form-control" id="other_info" name="other_info" rows="2" placeholder="Registry details, extra fees, or any other note">{{ $val('other_info') }}</textarea>
    </div>
</div>

{{-- Installment schedule --}}
<hr>
<div class="d-flex justify-content-between align-items-center mb-1">
    <h6 class="fw-bold text-primary mb-0"><i class="mdi mdi-calendar-clock-outline me-1"></i>Installment Schedule</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-installment"><i class="mdi mdi-plus"></i> Add Installment</button>
</div>
<div id="installments-wrapper">
    @foreach ($oldInstallments as $i => $inst)
        <div class="installment-row border rounded p-2 mb-2">
            <div class="row g-2 align-items-end">
                <div class="col-md-3"><label class="form-label small">Title</label><input type="text" class="form-control" name="installments[{{ $i }}][title]" value="{{ $inst['title'] ?? '' }}" placeholder="e.g. Booking Money"></div>
                <div class="col-md-3"><label class="form-label small">Due Date</label><input type="date" class="form-control" name="installments[{{ $i }}][due_date]" value="{{ $inst['due_date'] ?? '' }}"></div>
                <div class="col-md-3"><label class="form-label small">Amount</label><input type="number" min="0" step="0.01" class="form-control" name="installments[{{ $i }}][amount]" value="{{ $inst['amount'] ?? '' }}"></div>
                <div class="col-md-2"><label class="form-label small">Notes</label><input type="text" class="form-control" name="installments[{{ $i }}][notes]" value="{{ $inst['notes'] ?? '' }}"></div>
                <div class="col-md-1"><button type="button" class="btn btn-icon btn-outline-danger remove-installment"><i class="mdi mdi-delete-outline"></i></button></div>
            </div>
        </div>
    @endforeach
</div>

{{-- Notes --}}
<hr>
<div class="mb-3">
    <label for="notes" class="form-label">Notes</label>
    <textarea class="form-control" id="notes" name="notes" rows="2">{{ $val('notes') }}</textarea>
</div>

{{-- Documents / certificates --}}
<hr>
<div class="d-flex justify-content-between align-items-center mb-1">
    <h6 class="fw-bold text-primary mb-0"><i class="mdi mdi-file-upload-outline me-1"></i>Certificates &amp; Documents</h6>
    <button type="button" class="btn btn-sm btn-outline-primary" id="add-document"><i class="mdi mdi-plus"></i> Add Document</button>
</div>
<p class="text-muted small mb-3">Image or PDF, max 3&nbsp;MB each.</p>

@if ($booking && $booking->relationLoaded('documents') && $booking->documents->isNotEmpty())
    <div class="table-responsive mb-3">
        <table class="table table-sm mb-0">
            <thead><tr><th>Preview</th><th>Title</th><th>Category</th><th>File</th><th class="text-end">Download</th></tr></thead>
            <tbody>
                @foreach ($booking->documents as $doc)
                    @php $isImage = str_starts_with((string) $doc->mime_type, 'image/'); @endphp
                    <tr>
                        <td>
                            <button type="button" class="btn p-0 border-0 bg-transparent doc-preview-trigger"
                                data-preview-url="{{ url("/documents/{$doc->uuid}/preview") }}"
                                data-download-url="{{ url("/documents/{$doc->uuid}/download") }}"
                                data-title="{{ $doc->title }}"
                                data-file-name="{{ $doc->file_name }}"
                                data-is-image="{{ $isImage ? '1' : '0' }}"
                                title="Click to preview">
                                @if ($isImage)
                                    <img src="{{ url("/documents/{$doc->uuid}/preview") }}" alt="{{ $doc->title }}"
                                        class="rounded border" style="width:44px;height:44px;object-fit:cover;">
                                @else
                                    <span class="d-inline-flex align-items-center justify-content-center rounded border bg-label-danger"
                                        style="width:44px;height:44px;"><i class="mdi mdi-file-pdf-box mdi-24px"></i></span>
                                @endif
                            </button>
                        </td>
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
                    <button type="button" class="btn btn-icon btn-outline-danger remove-document"><i class="mdi mdi-delete-outline"></i></button>
                </div>
                <div class="col-12">
                    <label class="form-label small">Description</label>
                    <textarea class="form-control" name="documents[{{ $i }}][description]" rows="1">{{ $doc['description'] ?? '' }}</textarea>
                </div>
            </div>
        </div>
    @endforeach
</div>

{{-- Document preview modal --}}
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-truncate" id="docPreviewTitle">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="docPreviewBody" style="min-height:60vh;"></div>
            <div class="modal-footer">
                <a href="#" id="docPreviewDownload" class="btn btn-outline-primary" download>
                    <i class="mdi mdi-download-outline me-1"></i>Download
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    function money(n) {
        return (n || 0).toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }
    // Live totals.
    var sharesEl = document.getElementById('shares_count');
    var priceEl = document.getElementById('share_price');
    var regEl = document.getElementById('registration_fee');
    var otherEl = document.getElementById('other_fee');
    var discountEl = document.getElementById('discount');
    var shareValueEl = document.getElementById('share_value_display');
    var payableEl = document.getElementById('total_payable_display');
    var paidEl = document.getElementById('total_paid_display');
    var dueEl = document.getElementById('total_due_display');

    // Cash already received through manually recorded payments (server-provided).
    var manualPaid = {{ $manualPaid + 0 }};

    // Sum of amount fields whose "Paid" checkbox is ticked, plus manual payments.
    function markedPaid() {
        var sum = manualPaid;
        document.querySelectorAll('.paid-input:checked').forEach(function (cb) {
            var el = document.getElementById(cb.getAttribute('data-target'));
            if (el) { sum += parseFloat(el.value) || 0; }
        });
        return sum;
    }

    function recalc() {
        var shareValue = (parseFloat(sharesEl.value) || 0) * (parseFloat(priceEl.value) || 0);
        var payable = shareValue
            + (parseFloat(regEl.value) || 0)
            + (parseFloat(otherEl.value) || 0)
            - (parseFloat(discountEl.value) || 0);
        payable = payable < 0 ? 0 : payable;

        var paid = markedPaid();
        var due = payable - paid;

        shareValueEl.value = money(shareValue);
        payableEl.value = money(payable);
        paidEl.value = money(paid);
        dueEl.value = money(due < 0 ? 0 : due);
    }
    [sharesEl, priceEl, regEl, otherEl, discountEl, document.getElementById('booking_money')].forEach(function (el) {
        if (el) { el.addEventListener('input', recalc); }
    });
    document.querySelectorAll('.paid-input').forEach(function (cb) {
        cb.addEventListener('change', recalc);
    });
    recalc();

    // Available-shares hint.
    var plotEl = document.getElementById('plot_id');
    var hintEl = document.getElementById('shares-available-hint');
    function updateHint() {
        var opt = plotEl.options[plotEl.selectedIndex];
        var avail = opt ? opt.getAttribute('data-available') : null;
        hintEl.textContent = avail !== null && avail !== '' ? (avail + ' share(s) available on this plot.') : '';
    }
    plotEl.addEventListener('change', updateHint);
    updateHint();

    // Installment repeater.
    document.getElementById('add-installment').addEventListener('click', function () {
        var wrapper = document.getElementById('installments-wrapper');
        var i = wrapper.querySelectorAll('.installment-row').length;
        var row = document.createElement('div');
        row.className = 'installment-row border rounded p-2 mb-2';
        row.innerHTML =
            '<div class="row g-2 align-items-end">' +
            '<div class="col-md-3"><label class="form-label small">Title</label><input type="text" class="form-control" name="installments[' + i + '][title]" placeholder="e.g. 1st Installment"></div>' +
            '<div class="col-md-3"><label class="form-label small">Due Date</label><input type="date" class="form-control" name="installments[' + i + '][due_date]"></div>' +
            '<div class="col-md-3"><label class="form-label small">Amount</label><input type="number" min="0" step="0.01" class="form-control" name="installments[' + i + '][amount]"></div>' +
            '<div class="col-md-2"><label class="form-label small">Notes</label><input type="text" class="form-control" name="installments[' + i + '][notes]"></div>' +
            '<div class="col-md-1"><button type="button" class="btn btn-icon btn-outline-danger remove-installment"><i class="mdi mdi-delete-outline"></i></button></div>' +
            '</div>';
        wrapper.appendChild(row);
    });

    // Document repeater.
    var docCategoryOptions = @json($categoryOptionsHtml ?? '');
    document.getElementById('add-document').addEventListener('click', function () {
        var wrapper = document.getElementById('documents-wrapper');
        var i = wrapper.querySelectorAll('.document-row').length;
        var row = document.createElement('div');
        row.className = 'document-row border rounded p-2 mb-2';
        row.innerHTML =
            '<div class="row g-2 align-items-end">' +
            '<div class="col-md-3"><label class="form-label small">Category</label>' +
            '<select class="form-select" name="documents[' + i + '][category_id]"><option value="">— Select —</option>' + docCategoryOptions + '</select></div>' +
            '<div class="col-md-3"><label class="form-label small">Title</label><input type="text" class="form-control" name="documents[' + i + '][title]" placeholder="Defaults to category name"></div>' +
            '<div class="col-md-5"><label class="form-label small">File</label><input type="file" class="form-control" name="documents[' + i + '][file]" accept="image/*,application/pdf"></div>' +
            '<div class="col-md-1"><button type="button" class="btn btn-icon btn-outline-danger remove-document"><i class="mdi mdi-delete-outline"></i></button></div>' +
            '<div class="col-12"><label class="form-label small">Description</label><textarea class="form-control" name="documents[' + i + '][description]" rows="1"></textarea></div>' +
            '</div>';
        wrapper.appendChild(row);
    });

    // Row removal.
    document.addEventListener('click', function (e) {
        var instBtn = e.target.closest('.remove-installment');
        if (instBtn) { var r = instBtn.closest('.installment-row'); if (r) r.remove(); return; }
        var docBtn = e.target.closest('.remove-document');
        if (docBtn) { var d = docBtn.closest('.document-row'); if (d) d.remove(); }
    });

    // Document preview modal.
    var modalEl = document.getElementById('docPreviewModal');
    if (modalEl && typeof bootstrap !== 'undefined') {
        var modal = new bootstrap.Modal(modalEl);
        var body = document.getElementById('docPreviewBody');
        var titleEl = document.getElementById('docPreviewTitle');
        var downloadEl = document.getElementById('docPreviewDownload');

        document.addEventListener('click', function (e) {
            var trigger = e.target.closest('.doc-preview-trigger');
            if (!trigger) { return; }
            var url = trigger.getAttribute('data-preview-url');
            var isImage = trigger.getAttribute('data-is-image') === '1';
            titleEl.textContent = trigger.getAttribute('data-title') || trigger.getAttribute('data-file-name') || 'Preview';
            downloadEl.setAttribute('href', trigger.getAttribute('data-download-url') || url);
            body.innerHTML = isImage
                ? '<img src="' + url + '" alt="preview" class="img-fluid rounded" style="max-height:75vh;">'
                : '<iframe src="' + url + '" style="width:100%;height:75vh;border:0;" title="preview"></iframe>';
            modal.show();
        });

        modalEl.addEventListener('hidden.bs.modal', function () { body.innerHTML = ''; });
    }
})();
</script>
@endpush
