<aside id="layout-menu" class="layout-menu menu-vertical menu bg-menu-theme">
    <div class="app-brand demo">
        <a href="{{ url('/dashboard') }}" class="app-brand-link">
            <span class="app-brand-logo demo">
                <img src="{{ asset('assets/img/project/logo.svg') }}" alt="{{ config('app.name') }}" width="35" height="35" style="border-radius:8px;">
            </span>
            <span class="app-brand-text demo menu-text fw-bold ms-2">{{ config('app.name') }}</span>
        </a>

        <a href="javascript:void(0);" class="layout-menu-toggle menu-link text-large ms-auto">
            <i class="mdi mdi-close mdi-24px d-block d-xl-none"></i>
            <i class="mdi mdi-chevron-double-left mdi-24px d-none d-xl-block"></i>
        </a>
    </div>

    <div class="menu-inner-shadow"></div>

    <ul class="menu-inner py-1">
        {{-- Dashboard --}}
        <li class="menu-item {{ request()->is('dashboard') ? 'active' : '' }}">
            <a href="{{ url('/dashboard') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-view-dashboard-outline"></i>
                <div>Dashboard</div>
            </a>
        </li>

        {{-- Projects --}}
        {{-- <li class="menu-item {{ request()->is('projects*') ? 'active' : '' }}">
            <a href="{{ url('/projects') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-city-variant-outline"></i>
                <div>Projects</div>
            </a>
        </li> --}}

        {{-- Property --}}
        @php
            $propertyActive = request()->is('unit-types*') || request()->is('buildings*')
                || request()->is('floors*') || request()->is('units*') || request()->is('projects*')
                || request()->is('plots*') || request()->is('bookings*');
        @endphp
        <li class="menu-item {{ $propertyActive ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-office-building-outline"></i>
                <div>Property</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('projects*') ? 'active' : '' }}">
                    <a href="{{ url('/projects') }}" class="menu-link">
                        <div>Projects</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('plots*') ? 'active' : '' }}">
                    <a href="{{ url('/plots') }}" class="menu-link">
                        <div>Plots</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('bookings*') ? 'active' : '' }}">
                    <a href="{{ url('/bookings') }}" class="menu-link">
                        <div>Bookings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('unit-types*') ? 'active' : '' }}">
                    <a href="{{ url('/unit-types') }}" class="menu-link">
                        <div>Unit Types</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('buildings*') ? 'active' : '' }}">
                    <a href="{{ url('/buildings') }}" class="menu-link">
                        <div>Buildings</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('floors*') ? 'active' : '' }}">
                    <a href="{{ url('/floors') }}" class="menu-link">
                        <div>Floors</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('units*') ? 'active' : '' }}">
                    <a href="{{ url('/units') }}" class="menu-link">
                        <div>Units</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Members --}}
        <li class="menu-item {{ request()->is('members*') ? 'active' : '' }}">
            <a href="{{ url('/members') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-account-group-outline"></i>
                <div>Members</div>
            </a>
        </li>

        {{-- Shares & Holders --}}
        @php
            $sharesActive = request()->is('shareholders*') || request()->is('investments*');
        @endphp
        <li class="menu-item {{ $sharesActive ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-account-cash-outline"></i>
                <div>Shares &amp; Holders</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('shareholders*') ? 'active' : '' }}">
                    <a href="{{ url('/shareholders') }}" class="menu-link">
                        <div>Partners</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('investments*') ? 'active' : '' }}">
                    <a href="{{ url('/investments') }}" class="menu-link">
                        <div>Investments</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Loans --}}
        @php
            $loansActive = request()->is('loans*');
        @endphp
        <li class="menu-item {{ $loansActive ? 'active open' : '' }}">
            <a href="javascript:void(0);" class="menu-link menu-toggle">
                <i class="menu-icon tf-icons mdi mdi-bank-outline"></i>
                <div>Loans</div>
            </a>
            <ul class="menu-sub">
                <li class="menu-item {{ request()->is('loans') || (request()->is('loans/*') && ! request()->is('loans/reports*')) ? 'active' : '' }}">
                    <a href="{{ url('/loans') }}" class="menu-link">
                        <div>All Loans</div>
                    </a>
                </li>
                <li class="menu-item {{ request()->is('loans/reports*') ? 'active' : '' }}">
                    <a href="{{ url('/loans/reports') }}" class="menu-link">
                        <div>Reports</div>
                    </a>
                </li>
            </ul>
        </li>

        {{-- Expenses --}}
        <li class="menu-item {{ request()->is('expenses*') ? 'active' : '' }}">
            <a href="{{ url('/expenses') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-cash-minus"></i>
                <div>Expenses</div>
            </a>
        </li>

        {{-- Investors --}}
        {{-- <li class="menu-item {{ request()->is('investors*') ? 'active' : '' }}">
            <a href="{{ url('/investors') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-cash-multiple"></i>
                <div>Investors</div>
            </a>
        </li> --}}

        {{-- Customers --}}
        <li class="menu-item {{ request()->is('customers*') ? 'active' : '' }}">
            <a href="{{ url('/customers') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-account-outline"></i>
                <div>Customers</div>
            </a>
        </li>

        {{-- Settings --}}
        <li class="menu-item {{ request()->is('settings*') ? 'active' : '' }}">
            <a href="{{ url('/settings') }}" class="menu-link">
                <i class="menu-icon tf-icons mdi mdi-cog-outline"></i>
                <div>Settings</div>
            </a>
        </li>
    </ul>
</aside>
