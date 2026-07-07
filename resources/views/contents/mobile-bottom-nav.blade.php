{{-- Mobile bottom navigation — visible below xl breakpoint --}}
@php
    $isDashboard = request()->is('dashboard');
    $isProjects  = request()->is('projects*');
    $isProperty  = request()->is('unit-types*') || request()->is('buildings*') || request()->is('floors*') || request()->is('units*');
    $isMembers   = request()->is('members*') || request()->is('shareholders*') || request()->is('investors*');
@endphp

<nav class="hk-mnav" aria-label="Main menu">
    <a href="{{ url('/dashboard') }}" class="hk-mnav__item {{ $isDashboard ? 'active' : '' }}">
        <i class="hk-mnav__icon mdi mdi-view-dashboard-outline"></i>
        <span>Dashboard</span>
    </a>

    <a href="{{ url('/projects') }}" class="hk-mnav__item {{ $isProjects ? 'active' : '' }}">
        <i class="hk-mnav__icon mdi mdi-city-variant-outline"></i>
        <span>Projects</span>
    </a>

    <a href="{{ url('/units') }}" class="hk-mnav__item hk-mnav__item--primary {{ $isProperty ? 'active' : '' }}">
        <i class="hk-mnav__icon mdi mdi-office-building-outline"></i>
        <span>Units</span>
    </a>

    <a href="{{ url('/buildings') }}" class="hk-mnav__item {{ request()->is('buildings*') ? 'active' : '' }}">
        <i class="hk-mnav__icon mdi mdi-domain"></i>
        <span>Buildings</span>
    </a>

    <a href="{{ url('/members') }}" class="hk-mnav__item {{ $isMembers ? 'active' : '' }}">
        <i class="hk-mnav__icon mdi mdi-account-group-outline"></i>
        <span>Team</span>
    </a>
</nav>
