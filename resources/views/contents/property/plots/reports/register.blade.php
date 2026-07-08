@extends('contents.body')

@section('title', $title)

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/plots') }}">Plots</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/plots/reports') }}">Reports</a></li>
                <li class="breadcrumb-item active">{{ $title }}</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">{{ $title }}</h4>

        <div class="card">
            <div class="table-responsive">
                <table class="table mb-0">
                    <thead>
                        <tr>
                            <th>Code</th>
                            <th>Name</th>
                            <th>Location</th>
                            <th class="text-end">Land</th>
                            <th>Status</th>
                            <th class="text-end">Sellers</th>
                            <th class="text-end">Owners</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $plot)
                            <tr>
                                <td class="fw-medium"><a href="{{ url("/plots/{$plot->uuid}") }}">{{ $plot->plot_code }}</a></td>
                                <td>{{ $plot->plot_name }}</td>
                                <td>{{ collect([$plot->area, $plot->upazila, $plot->district])->filter()->implode(', ') ?: '—' }}</td>
                                <td class="text-end text-nowrap">{{ rtrim(rtrim(number_format($plot->land_size, 4), '0'), '.') }} {{ $plot->land_unit }}</td>
                                <td><span class="badge bg-label-info">{{ ucwords(str_replace('_', ' ', $plot->status)) }}</span></td>
                                <td class="text-end">{{ $plot->sellers_count }}</td>
                                <td class="text-end">{{ $plot->owners_count }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="text-center text-muted py-4">No plots found.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
