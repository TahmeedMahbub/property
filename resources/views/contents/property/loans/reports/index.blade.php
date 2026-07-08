@extends('contents.body')

@section('title', 'Loan Reports')

@section('content')
<div class="row gy-4">
    <div class="col-12">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ url('/dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item"><a href="{{ url('/loans') }}">Loans</a></li>
                <li class="breadcrumb-item active">Reports</li>
            </ol>
        </nav>

        <h4 class="fw-bold mb-3">Loan Reports</h4>

        <div class="row g-3">
            @php
                $reports = [
                    ['summary', 'Loan Summary Report', 'All loans with principal, repaid, outstanding and interest.', 'mdi-file-document-outline', 'primary'],
                    ['outstanding', 'Outstanding Loan Report', 'Loans that still carry a balance.', 'mdi-cash-remove', 'danger'],
                    ['repayment', 'Loan Repayment Report', 'Every repayment transaction across all loans.', 'mdi-cash-multiple', 'success'],
                    ['interest', 'Interest Expense Report', 'Interest paid per loan.', 'mdi-percent-outline', 'warning'],
                    ['project', 'Project Wise Loan Report', 'Loans, outstanding and net profit per project.', 'mdi-office-building-outline', 'info'],
                ];
            @endphp
            @foreach ($reports as [$slug, $name, $desc, $icon, $color])
                <div class="col-12 col-md-6 col-lg-4">
                    <a href="{{ url("/loans/reports/{$slug}") }}" class="card h-100 text-decoration-none">
                        <div class="card-body">
                            <span class="badge bg-label-{{ $color }} rounded p-2 mb-2"><i class="mdi {{ $icon }} mdi-24px"></i></span>
                            <h6 class="mb-1">{{ $name }}</h6>
                            <p class="text-muted small mb-0">{{ $desc }}</p>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
