@extends('contents.body')

@section('title', 'Plot Details')

@section('content')
<style>
    .plot-table { overflow: visible; }
</style>
<div class="row gy-4">
    <div class="col-12">
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-0">
                    <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                    <li class="breadcrumb-item"><a href="{{ url('/plots') }}">Plots</a></li>
                    <li class="breadcrumb-item active">{{ $plot->plot_code }}</li>
                </ol>
            </nav>
            <div class="d-flex gap-2">
                <a href="{{ url("/plots/{$plot->uuid}/payments/create") }}" class="btn btn-sm btn-primary">
                    <i class="mdi mdi-cash-plus me-1"></i>Add Payment
                </a>
                <a href="{{ url("/plots/{$plot->uuid}/edit") }}" class="btn btn-sm btn-outline-primary">
                    <i class="mdi mdi-pencil-outline me-1"></i>Edit
                </a>
                <a href="{{ url('/plots') }}" class="btn btn-sm btn-outline-secondary">
                    <i class="mdi mdi-arrow-left me-1"></i>Back
                </a>
            </div>
        </div>

        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mt-2 mb-3">
            <h4 class="fw-bold mb-0">
                {{ $plot->plot_name }}
                <span class="badge bg-label-secondary align-middle">{{ $plot->plot_code }}</span>
            </h4>
            <span class="badge bg-label-info fs-6">{{ ucwords(str_replace('_', ' ', $plot->status)) }}</span>
        </div>

        @if (session('success'))
            <div class="alert alert-success alert-dismissible" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        @endif
    </div>

    {{-- Summary stat cards --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Acquisition Cost</div>
                    <h5 class="mb-0">৳{{ number_format($plot->total_acquisition_cost, 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Paid</div>
                    <h5 class="mb-0 text-success">৳{{ number_format($plot->total_paid, 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Total Due</div>
                    <h5 class="mb-0 {{ $plot->total_due > 0 ? 'text-danger' : 'text-success' }}">৳{{ number_format($plot->total_due, 2) }}</h5>
                </div></div>
            </div>
            <div class="col-6 col-lg-3">
                <div class="card h-100"><div class="card-body">
                    <div class="text-muted small">Land Size</div>
                    <h5 class="mb-0">{{ rtrim(rtrim(number_format($plot->land_size, 4), '0'), '.') }} <small class="text-muted">{{ $plot->land_unit }}</small></h5>
                </div></div>
            </div>
        </div>
    </div>

    {{-- Overview --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Location &amp; Land Records</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-5 text-muted fw-normal">Division</dt><dd class="col-7">{{ $plot->division ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">District</dt><dd class="col-7">{{ $plot->district ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Upazila</dt><dd class="col-7">{{ $plot->upazila ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Area</dt><dd class="col-7">{{ $plot->area ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Address</dt><dd class="col-7">{{ $plot->address ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Mouza</dt><dd class="col-7">{{ $plot->mouza ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">JL No</dt><dd class="col-7">{{ $plot->jl_no ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Khatian No</dt><dd class="col-7">{{ $plot->khatian_no ?: '—' }}</dd>
                    <dt class="col-5 text-muted fw-normal">Dag No</dt><dd class="col-7">{{ $plot->dag_no ?: '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Cost breakdown --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header"><h6 class="mb-0">Cost Breakdown</h6></div>
            <div class="card-body">
                <dl class="row mb-0 small">
                    <dt class="col-7 text-muted fw-normal">Purchase Price</dt><dd class="col-5 text-end">৳{{ number_format($plot->purchase_price, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Bayna Amount (advance)</dt><dd class="col-5 text-end">৳{{ number_format($plot->bayna_amount, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Registration Cost</dt><dd class="col-5 text-end">৳{{ number_format($plot->registration_cost, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Mutation Cost</dt><dd class="col-5 text-end">৳{{ number_format($plot->mutation_cost, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Legal Cost</dt><dd class="col-5 text-end">৳{{ number_format($plot->legal_cost, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Broker Cost</dt><dd class="col-5 text-end">৳{{ number_format($plot->broker_cost, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Other Cost</dt><dd class="col-5 text-end">৳{{ number_format($plot->other_cost, 2) }}</dd>
                    <dt class="col-7 fw-bold border-top pt-2 mt-1">Total Acquisition Cost</dt><dd class="col-5 text-end fw-bold border-top pt-2 mt-1">৳{{ number_format($plot->total_acquisition_cost, 2) }}</dd>
                    <dt class="col-7 text-muted fw-normal">Price / Katha</dt><dd class="col-5 text-end">{{ $plot->price_per_katha ? '৳' . number_format($plot->price_per_katha, 2) : '—' }}</dd>
                </dl>
            </div>
        </div>
    </div>

    {{-- Sellers --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Sellers</h6>
                <span class="badge bg-label-secondary">{{ $plot->sellers->count() }}</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Name</th><th>Phone</th><th>NID</th><th>Images</th></tr></thead>
                    <tbody>
                        @forelse ($plot->sellers as $seller)
                            <tr>
                                <td class="fw-medium">{{ $seller->name }}</td>
                                <td>{{ $seller->phone ?: '—' }}</td>
                                <td>{{ $seller->nid ?: '—' }}</td>
                                <td>
                                    @include('contents.property.plots._person_thumbs', ['type' => 'seller', 'person' => $seller])
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No sellers recorded.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Legal owners --}}
    <div class="col-12 col-lg-6">
        <div class="card h-100">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h6 class="mb-0">Legal Land Owners</h6>
                <span class="badge bg-label-secondary">{{ rtrim(rtrim(number_format($plot->owners->sum('ownership_percentage'), 4), '0'), '.') }}% total</span>
            </div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th>Name</th><th>Phone</th><th>Images</th><th class="text-end">Ownership %</th></tr></thead>
                    <tbody>
                        @forelse ($plot->owners as $owner)
                            <tr>
                                <td class="fw-medium">{{ $owner->name }}</td>
                                <td>{{ $owner->phone ?: '—' }}</td>
                                <td>
                                    @include('contents.property.plots._person_thumbs', ['type' => 'owner', 'person' => $owner])
                                </td>
                                <td class="text-end">{{ rtrim(rtrim(number_format($owner->ownership_percentage, 4), '0'), '.') }}%</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="text-center text-muted py-3">No legal owners recorded.</td></tr>
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
                <a href="{{ url("/plots/{$plot->uuid}/payments/create") }}" class="btn btn-sm btn-primary">
                    <i class="mdi mdi-cash-plus me-1"></i>Add Payment
                </a>
            </div>
            <div class="table-responsive plot-table">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th class="text-end">Amount</th>
                            <th class="d-none d-md-table-cell">Method</th>
                            <th class="d-none d-md-table-cell">Reference</th>
                            <th class="text-end">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($plot->payments as $payment)
                            <tr>
                                <td class="text-nowrap">{{ $payment->payment_date->format('d M Y') }}</td>
                                <td><span class="badge bg-label-primary">{{ \App\Models\PlotPayment::TYPES[$payment->payment_type] ?? ucfirst($payment->payment_type) }}</span></td>
                                <td class="text-end fw-medium">৳{{ number_format($payment->amount, 2) }}</td>
                                <td class="d-none d-md-table-cell">{{ ucwords(str_replace('_', ' ', $payment->payment_method)) }}</td>
                                <td class="d-none d-md-table-cell">{{ $payment->reference_no ?: '—' }}</td>
                                <td class="text-end">
                                    <form method="POST" action="{{ url("/plots/{$plot->uuid}/payments/{$payment->uuid}") }}"
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
                            <tr><td colspan="6" class="text-center text-muted py-4">No payments recorded yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Documents --}}
    <div class="col-12">
        <div class="card">
            <div class="card-header"><h6 class="mb-0">Documents</h6></div>
            <div class="card-body">
                <form method="POST" action="{{ url("/plots/{$plot->uuid}/documents") }}" enctype="multipart/form-data" class="row g-2 mb-3">
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
                        <textarea name="description" id="doc-description" rows="2" class="form-control @error('description') is-invalid @enderror" placeholder="Notes about this document">{{ old('description') }}</textarea>
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
                            @forelse ($plot->documents as $doc)
                                <tr>
                                    <td>@include('contents.property.plots._document_thumb', ['doc' => $doc])</td>
                                    <td class="fw-medium">{{ $doc->title }}</td>
                                    <td>{{ $doc->category?->name ?? '—' }}</td>
                                    <td>{{ \Illuminate\Support\Str::limit($doc->description, 60) ?: '—' }}</td>
                                    <td>{{ $doc->file_name }}</td>
                                    <td class="text-end">
                                        <a href="{{ url("/documents/{$doc->uuid}/download") }}" class="btn btn-sm btn-icon btn-text-secondary rounded-pill" title="Download">
                                            <i class="mdi mdi-download-outline"></i>
                                        </a>
                                        <form method="POST" action="{{ url("/plots/{$plot->uuid}/documents/{$doc->uuid}") }}" class="d-inline"
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
