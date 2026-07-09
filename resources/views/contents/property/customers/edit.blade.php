@extends('contents.body')

@section('title', 'Edit Customer')

@section('content')
@php
    $completion = $customer->profile_completion;
    $barColor = $completion >= 100 ? 'success' : ($completion >= 50 ? 'info' : 'warning');
@endphp
<div class="row gy-4 justify-content-center">
    <div class="col-12 col-lg-8">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/customers') }}">Customers</a></li>
                <li class="breadcrumb-item active">Edit</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Edit Customer: {{ $customer->name }}</h4>

        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex justify-content-between align-items-center mb-1">
                    <span class="fw-medium">Profile Completion</span>
                    <span class="fw-bold text-{{ $barColor }}">{{ $completion }}%</span>
                </div>
                <div class="progress" style="height: 8px;">
                    <div class="progress-bar bg-{{ $barColor }}" role="progressbar"
                        style="width: {{ $completion }}%;" aria-valuenow="{{ $completion }}"
                        aria-valuemin="0" aria-valuemax="100"></div>
                </div>
            </div>
        </div>

        {{-- Profile completion link management --}}
        <div class="card mb-3">
            <div class="card-body py-3">
                <div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
                    <div>
                        <span class="fw-medium d-block">Profile Completion Link</span>
                        @if ($customer->profile_locked)
                            <span class="badge bg-label-success">Verified &amp; Locked</span>
                        @elseif ($customer->isProfileCompleted())
                            <span class="badge bg-label-info">Submitted</span>
                        @elseif (! $customer->hasProfileLink())
                            <span class="badge bg-label-secondary">Not generated</span>
                        @elseif ($customer->isProfileLinkExpired())
                            <span class="badge bg-label-danger">Expired</span>
                        @else
                            <span class="badge bg-label-primary">Active</span>
                        @endif
                        @if ($customer->profile_link_expires_at)
                            <small class="text-muted ms-1">
                                Expires: {{ $customer->profile_link_expires_at->format('d M Y, h:i A') }}
                            </small>
                        @endif
                    </div>
                    <div class="d-flex gap-2">
                        @if ($customer->hasProfileLink())
                            <button type="button" class="btn btn-sm btn-outline-primary"
                                data-copy-link="{{ $customer->profile_link }}">
                                <i class="mdi mdi-content-copy me-1"></i> Copy Profile Link
                            </button>
                        @endif
                        <form method="POST"
                            action="{{ route('customers.profile-link.regenerate', $customer->uuid) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="mdi mdi-refresh me-1"></i>
                                {{ $customer->hasProfileLink() ? 'Regenerate Profile Link' : 'Generate Profile Link' }}
                            </button>
                        </form>
                    </div>
                </div>
                @if ($customer->hasProfileLink())
                    <div class="input-group input-group-sm mt-2">
                        <input type="text" class="form-control" value="{{ $customer->profile_link }}" readonly
                            onclick="this.select()">
                    </div>
                @endif
            </div>
        </div>

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

                <form method="POST" action="{{ url("/customers/{$customer->uuid}") }}" enctype="multipart/form-data">
                    @csrf
                    @method('PUT')
                    @include('contents.property.customers._form', ['customer' => $customer])

                    <div class="d-flex gap-2 mt-4">
                        <button type="submit" class="btn btn-primary">Update</button>
                        <a href="{{ url('/customers') }}" class="btn btn-outline-secondary">Cancel</a>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Document removal forms (kept outside the main form to avoid nesting) --}}
@foreach (\App\Models\Customer::DOCUMENT_TYPES as $type => $label)
    @php $existingDoc = $customer->documentOfType($type); @endphp
    @if ($existingDoc)
        <form id="del_doc_{{ $type }}" method="POST"
            action="{{ route('customers.documents.destroy', [$customer->uuid, $existingDoc->uuid]) }}"
            class="d-none">
            @csrf
            @method('DELETE')
        </form>
    @endif
@endforeach

@include('contents.property.customers._link_scripts')
@endsection

