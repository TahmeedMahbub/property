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
            <div class="card-header"><h6 class="mb-0">Record Payment</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ url("/bookings/{$booking->uuid}/payments") }}" class="row g-2 mb-4">
                    @csrf
                    <div class="col-md-3">
                        <label class="form-label small">Payment Type <span class="text-danger">*</span></label>
                        <select name="payment_type" class="form-select" required>
                            @foreach (\App\Models\PlotBookingPayment::TYPES as $v => $l)
                                <option value="{{ $v }}" {{ old('payment_type') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Installment</label>
                        <select name="installment_id" class="form-select">
                            <option value="">— Not linked —</option>
                            @foreach ($booking->installments as $inst)
                                <option value="{{ $inst->id }}" {{ (string) old('installment_id') === (string) $inst->id ? 'selected' : '' }}>
                                    #{{ $inst->installment_no }} {{ $inst->title ? '- ' . $inst->title : '' }} (৳{{ number_format($inst->due_amount, 2) }} due)
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Amount <span class="text-danger">*</span></label>
                        <div class="input-group">
                            <span class="input-group-text">৳</span>
                            <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Date <span class="text-danger">*</span></label>
                        <input type="date" name="payment_date" class="form-control" value="{{ old('payment_date', date('Y-m-d')) }}" required>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Method</label>
                        <select name="payment_method" class="form-select">
                            @foreach (['cash' => 'Cash', 'cheque' => 'Cheque', 'bank_transfer' => 'Bank Transfer', 'mobile_banking' => 'Mobile Banking', 'other' => 'Other'] as $v => $l)
                                <option value="{{ $v }}" {{ old('payment_method', 'cash') === $v ? 'selected' : '' }}>{{ $l }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label small">Reference / Cheque No</label>
                        <input type="text" name="reference_no" class="form-control" value="{{ old('reference_no') }}">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small">Notes</label>
                        <input type="text" name="notes" class="form-control" value="{{ old('notes') }}">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary"><i class="mdi mdi-cash-plus me-1"></i>Save Payment</button>
                    </div>
                </form>

                <h6 class="mb-2">Payment History</h6>
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
                        <thead><tr><th>Title</th><th>Category</th><th>Description</th><th>File</th><th class="text-end">Actions</th></tr></thead>
                        <tbody>
                            @forelse ($booking->documents as $doc)
                                <tr>
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
                                <tr><td colspan="5" class="text-center text-muted py-3">No documents uploaded yet.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
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
</script>
@endpush
