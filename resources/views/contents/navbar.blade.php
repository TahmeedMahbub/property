<nav
    class="layout-navbar container-xxl navbar navbar-expand-xl navbar-detached align-items-center bg-navbar-theme"
    id="layout-navbar">
    <div class="layout-menu-toggle navbar-nav align-items-xl-center me-3 me-xl-0 d-xl-none">
        <a class="nav-item nav-link px-0 me-xl-4" href="javascript:void(0)">
            <i class="mdi mdi-menu mdi-24px"></i>
        </a>
    </div>

    <div class="navbar-nav-right d-flex align-items-center" id="navbar-collapse">
        {{-- App name --}}
        <div class="navbar-nav align-items-center">
            <a href="{{ url('/dashboard') }}" class="fw-medium text-heading text-decoration-none hk-topbar-title">
                {{ app()->bound('currentCompany') && app('currentCompany') ? app('currentCompany')->name : config('app.name') }}
            </a>
        </div>

        <ul class="navbar-nav flex-row align-items-center ms-auto">
            @auth
            {{-- User dropdown --}}
            <li class="nav-item navbar-dropdown dropdown-user dropdown">
                <a class="nav-link dropdown-toggle hide-arrow" href="javascript:void(0);"
                    data-bs-toggle="dropdown">
                    <div class="avatar avatar-online">
                        <span class="avatar-initial rounded-circle bg-label-primary">
                            {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                        </span>
                    </div>
                </a>
                <ul class="dropdown-menu dropdown-menu-end">
                    <li>
                        <div class="dropdown-item">
                            <div class="d-flex">
                                <div class="flex-grow-1">
                                    <span class="fw-medium d-block">{{ auth()->user()->name }}</span>
                                    <small class="text-muted">{{ auth()->user()->email }}</small>
                                </div>
                            </div>
                        </div>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                        <a class="dropdown-item" href="{{ url('/profile') }}">
                            <i class="mdi mdi-account-outline me-2"></i> {{ t('nav.profile') }}
                        </a>
                    </li>
                    <li>
                        @php($currentLang = app()->getLocale())
                        <a class="dropdown-item" href="{{ url('?lang=' . ($currentLang === 'bn' ? 'en' : 'bn')) }}">
                            <i class="mdi mdi-translate me-2"></i>
                            {{ $currentLang === 'bn' ? 'English' : 'বাংলা' }}
                        </a>
                    </li>
                    <li><div class="dropdown-divider"></div></li>
                    <li>
                        <form method="POST" action="{{ url('/logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item">
                                <i class="mdi mdi-logout me-2"></i> {{ t('auth.logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </li>
            @endauth
        </ul>
    </div>
</nav>
