@extends('contents.body')

@section('title', 'Booking Details')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/bookings') }}">Bookings</a></li>
                    <li class="breadcrumb-item active">{{ $booking->booking_no }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="{{ url("/bookings/{$booking->uuid}/payments/create") }}" class="btn btn-sm btn-primary">
                    <i class="mdi mdi-cash-plus me-1"></i>Record Payment
                </a>
                <a href="{{ url("/bookings/{$booking->uuid}/expenses/create") }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-cash-minus me-1"></i>Record Expense
                </a>
                <a href="{{ url("/bookings/{$booking->uuid}/edit") }}" class="btn btn-sm btn-outline-primary">
                    <i class="mdi mdi-pencil-outline me-1"></i>Edit
                </a>
                <a href="{{ url('/bookings') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        @php
            $statusColors = ['booked' => 'info', 'active' => 'primary', 'completed' => 'success', 'cancelled' => 'secondary'];
        @endphp
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2 mb-3">
            <h4 class="fw-bold mb-0">
                {{ $booking->booking_no }}
                <span class="badge bg-label-secondary align-middle">{{ $booking->customer?->name }}</span>
            </h4>
            <span class="badge bg-label-{{ $statusColors[$booking->status] ?? 'secondary' }} fs-6">{{ ucfirst($booking->status) }}</span>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-danger py-2">
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- Summary stat cards --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Shares</div>
                    <h5 class="mb-0">{{ $booking->shares_count }} <small class="text-muted">× ৳{{ number_format($booking->share_price, 2) }}</small></h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Payable</div>
                    <h5 class="mb-0">৳{{ number_format($booking->total_payable, 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Paid</div>
                    <h5 class="mb-0 text-success">৳{{ number_format($booking->total_paid, 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Due</div>
                    <h5 class="mb-0 {{ $booking->total_due > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($booking->total_due, 2) }}</h5>
                </div></div>
            </div>
        </div>
    </div>

    {{-- Booking overview --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Booking &amp; Customer</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Customer</dt><dd class="col-7">{{ $booking->customer?->name ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Customer Phone</dt><dd class="col-7">{{ $booking->customer?->phone ?? '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Plot</dt><dd class="col-7">{{ $booking->plot?->plot_name ?? '—' }} <span class="text-muted">({{ $booking->plot?->plot_code }})</span></dd>
                    <dt class="col-5 text-muted fw-normal">Booking Date</dt><dd class="col-7">{{ optional($booking->booking_date)->format('d M Y') ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Booking Money</dt><dd class="col-7">৳{{ number_format($booking->booking_money, 2) }}</dd>
                    <dt class="col-5 text-muted fw-normal">Status</dt><dd class="col-7">{{ ucfirst($booking->status) }}</dd>
                    <dt class="col-5 text-muted fw-normal">Created By</dt><dd class="col-7">{{ $booking->creator?->name ?? '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Pricing breakdown --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Pricing Breakdown</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-7 text-muted fw-normal">Share Value ({{ $booking->shares_count }} × ৳{{ number_format($booking->share_price, 2) }})</dt><dd class="col-5 text-end">৳{{ number_format($booking->share_value, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Registration Fee</dt><dd class="col-5 text-end">৳{{ number_format($booking->registration_fee, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Other Fee</dt><dd class="col-5 text-end">৳{{ number_format($booking->other_fee, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Discount</dt><dd class="col-5 text-end">− ৳{{ number_format($booking->discount, 2) }}</dd>
                    <dt class="col-7 fw-bold border-top pt-2 mt-1">Total Payable</dt><dd class="col-5 text-end fw-bold border-top pt-2 mt-1">৳{{ number_format($booking->total_payable, 2) }}</dd>
                </dl>
                @if ($booking->other_info)
                    <hr>
                    <div class="small"><span class="text-muted">Extra Info:</span> {{ $booking->other_info }}</div>
                @endif
            </div>
        </div>
    </div>

    {{-- Installment schedule --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Installment Schedule</h6>
                <span class="badge bg-label-secondary">{{ $booking->installments->count() }} installment(s)</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Title</th>
                            <th>Due Date</th>
                            <th class="text-end">Amount</th>
                            <th class="text-end">Paid</th>
                            <th class="text-end">Due</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
                            $instStatusColors = ['paid' => 'success', 'partial' => 'warning', 'overdue' => 'danger', 'pending' => 'secondary'];
                        @endphp
                        @forelse ($booking->installments as $inst)
                            <tr>
                                <td>{{ $inst->installment_no }}</td>
                                <td class="fw-medium">{{ $inst->title ?: '—' }}</td>
                                <td class="text-nowrap">{{ optional($inst->due_date)->format('d M Y') ?: '—' }}</td>
                                <td class="text-end">৳{{ number_format($inst->amount, 2) }}</td>
                                <td class="text-end text-success">৳{{ number_format($inst->paid_amount, 2) }}</td>
                                <td class="text-end {{ $inst->due_amount > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($inst->due_amount, 2) }}</td>
                                <td><span class="badge bg-label-{{ $instStatusColors[$inst->status] ?? 'secondary' }}">{{ ucfirst($inst->status) }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-3">No installment schedule defined.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Payments --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Payment History</h6>
                <a href="{{ url("/bookings/{$booking->uuid}/payments/create") }}" class="btn btn-sm btn-primary">
                    <i class="mdi mdi-cash-plus me-1"></i>Record Payment
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Type</th>
                                <th class="d-none d-md-table-cell">Installment</th>
                                <th class="text-end">Amount</th>
                                <th class="d-none d-md-table-cell">Method</th>
                                <th class="d-none d-md-table-cell">Reference</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($booking->payments as $payment)
                                <tr>
                                    <td class="text-nowrap">{{ $payment->payment_date->format('d M Y') }}</td>
                                    <td><span class="badge bg-label-primary">{{ \App\Models\PlotBookingPayment::TYPES[$payment->payment_type] ?? ucfirst($payment->payment_type) }}</span></td>
                                    <td class="d-none d-md-table-cell">{{ $payment->installment ? '#' . $payment->installment->installment_no : '—' }}</td>
                                    <td class="text-end fw-medium">৳{{ number_format($payment->amount, 2) }}</td>
                                    <td class="d-none d-md-table-cell">{{ $payment->payment_method ? ucwords(str_replace('_', ' ', $payment->payment_method)) : '—' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $payment->reference_no ?: '—' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ url("/bookings/{$booking->uuid}/payments/{$payment->uuid}") }}"
                                            onsubmit="return confirm('Delete this payment?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill">
                                                <i class="mdi mdi-delete-outline"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Expenses --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Expense History</h6>
                <a href="{{ url("/bookings/{$booking->uuid}/expenses/create") }}" class="btn btn-sm btn-outline-danger">
                    <i class="mdi mdi-cash-minus me-1"></i>Record Expense
                </a>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Category</th>
                                <th class="d-none d-md-table-cell">Title</th>
                                <th class="text-end">Amount</th>
                                <th class="d-none d-md-table-cell">Method</th>
                                <th class="d-none d-md-table-cell">Reference</th>
                                <th class="text-end">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($booking->expenses as $expense)
                                <tr>
                                    <td class="text-nowrap">{{ $expense->expense_date->format('d M Y') }}</td>
                                    <td><span class="badge bg-label-danger">{{ $expense->category_name }}</span></td>
                                    <td class="d-none d-md-table-cell">{{ $expense->title ?: '—' }}</td>
                                    <td class="text-end fw-medium">৳{{ number_format($expense->amount, 2) }}</td>
                                    <td class="d-none d-md-table-cell">{{ $expense->payment_method ? ucwords(str_replace('_', ' ', $expense->payment_method)) : '—' }}</td>
                                    <td class="d-none d-md-table-cell">{{ $expense->reference_no ?: '—' }}</td>
                                    <td class="text-end">
                                        <form method="POST" action="{{ url("/bookings/{$booking->uuid}/expenses/{$expense->uuid}") }}"
                                            onsubmit="return confirm('Delete this expense?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill">
                                                <i class="mdi mdi-delete-outline"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="text-center text-muted py-4">No expenses recorded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Certificates &amp; Documents</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ url("/bookings/{$booking->uuid}/documents") }}" enctype="multipart/form-data" class="row g-2 mb-3">
                    @csrf
                    <div class="col-md-4">
                        <label class="form-label small">Category <span class="text-danger">*</span></label>
                        <select name="category_id" id="doc-category" class="form-select" required>
                            <option value="">— Select category —</option>
                            @foreach ($documentCategories as $cat)
                                @if ($cat->children->isNotEmpty())
                                    <optgroup label="{{ $cat->name }}">
                                        @foreach ($cat->children as $child)
                                            <option value="{{ $child->id }}">{{ $child->name }}</option>
                                        @endforeach
                                    </optgroup>
                                @else
                                    <option value="{{ $cat->id }}" data-other="{{ $cat->slug === 'other-document' ? '1' : '0' }}">{{ $cat->name }}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">Title <span class="text-danger d-none" id="doc-title-req">*</span></label>
                        <input type="text" name="title" id="doc-title" class="form-control @error('title') is-invalid @enderror" value="{{ old('title') }}" placeholder="Defaults to category name">
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small">File <span class="text-danger">*</span></label>
                        <input type="file" name="file" class="form-control @error('file') is-invalid @enderror" accept="image/*,application/pdf" required>
                        <small class="text-muted">Image or PDF, max 3 MB.</small>
                        @error('file') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <label class="form-label small">Description <span class="text-danger d-none" id="doc-desc-req">*</span></label>
                        <textarea name="description" id="doc-description" rows="2" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="mdi mdi-upload me-1"></i>Upload</button>
                    </div>
                </form>

                <div class="table-responsive">
                    <table class="table table-sm mb-0">
                        <thead><tr><th>Preview</th><th>Title</th><th>Category</th><th>Description</th><th>File</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            @forelse ($booking->documents as $doc)
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
                                    <td>{{ \Illuminate\Support\Str::limit($doc->description, 60) ?: '—' }}</td>
                                    <td>{{ $doc->file_name }}</td>
                                    <td class="text-end">
                                        <a href="{{ url("/documents/{$doc->uuid}/download") }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Download">
                                            <i class="mdi mdi-download-outline"></i>
                                        </a>
                                        <form method="POST" action="{{ url("/bookings/{$booking->uuid}/documents/{$doc->uuid}") }}" class="d-inline"
                                            onsubmit="return confirm('Delete this document?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-icon btn-text-danger rounded-pill" title="Delete">
                                                <i class="mdi mdi-delete-outline"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="6" class="text-center text-muted py-3">No documents uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Document preview modal --}}
<div class="modal fade" id="docPreviewModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-truncate" id="docPreviewTitle">Preview</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body text-center" id="docPreviewBody" style="min-height:60vh;">
                {{-- injected by JS --}}
            </div>
            <div class="modal-footer">
                <a href="#" id="docPreviewDownload" class="btn btn-outline-primary" download>
                    <i class="mdi mdi-download-outline me-1"></i>Download
                </a>
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var sel = document.getElementById('doc-category');
    if (!sel) { return; }
    var title = document.getElementById('doc-title');
    var desc = document.getElementById('doc-description');
    var titleReq = document.getElementById('doc-title-req');
    var descReq = document.getElementById('doc-desc-req');

    function sync() {
        var opt = sel.options[sel.selectedIndex];
        var isOther = !!(opt && opt.getAttribute('data-other') === '1');
        title.required = isOther;
        desc.required = isOther;
        titleReq.classList.toggle('d-none', !isOther);
        descReq.classList.toggle('d-none', !isOther);
    }

    sel.addEventListener('change', sync);
    sync();
})();

// Document preview modal.
(function () {
    var modalEl = document.getElementById('docPreviewModal');
    if (!modalEl || typeof bootstrap === 'undefined') { return; }
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

        if (isImage) {
            body.innerHTML = '<img src="' + url + '" alt="preview" class="img-fluid rounded" style="max-height:75vh;">';
        } else {
            body.innerHTML = '<iframe src="' + url + '" style="width:100%;height:75vh;border:0;" title="preview"></iframe>';
        }
        modal.show();
    });

    modalEl.addEventListener('hidden.bs.modal', function () {
        body.innerHTML = '';
    });
})();
</script>
@endpush
