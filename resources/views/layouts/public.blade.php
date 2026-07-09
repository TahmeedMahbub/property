<!DOCTYPE html>
<html lang="en" class="light-style" dir="ltr" data-theme="theme-default" data-template="vertical-menu-template">
<head>
    @include('contents.head-section')
    <title>@yield('title', 'Customer Profile')</title>
    <style>
        :root {
            --hk-primary: #18223B;
            --hk-primary-dark: #0f1628;
        }
        .hk-public-topbar {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            z-index: 1030;
            background: linear-gradient(90deg, var(--hk-primary) 0%, var(--hk-primary-dark) 100%);
            box-shadow: 0 2px 12px rgba(24, 34, 59, 0.35);
        }
        .hk-public-topbar .hk-brand {
            color: #fff;
        }
        .hk-public-topbar .hk-brand span {
            color: #fff;
        }
        .hk-public-body {
            padding-top: 76px;
        }
        .hk-public-body .btn-primary {
            background-color: var(--hk-primary);
            border-color: var(--hk-primary);
        }
        .hk-public-body .btn-primary:hover,
        .hk-public-body .btn-primary:focus,
        .hk-public-body .btn-primary:active {
            background-color: var(--hk-primary-dark) !important;
            border-color: var(--hk-primary-dark) !important;
        }
    </style>
</head>
<body>
    <div class="d-flex flex-column min-vh-100 hk-public-body">
        {{-- Fixed gradient branding topbar --}}
        <header class="hk-public-topbar py-3">
            <div class="container" style="max-width: 900px;">
                <a href="{{ url('/') }}" class="hk-brand d-inline-flex align-items-center text-decoration-none">
                    <img src="{{ asset('assets/img/project/logo.svg') }}" alt="{{ config('app.name') }}"
                        width="38" height="38" style="border-radius: 8px; background:#fff; padding:2px;">
                    <span class="ms-2 fw-bold fs-5">{{ config('app.name') }}</span>
                </a>
            </div>
        </header>

        <main class="container flex-grow-1 py-4 py-md-5" style="max-width: 900px;">
            @yield('content')
        </main>

        {{-- Branding footer --}}
        <footer class="py-3 border-top bg-white mt-auto">
            <div class="container text-center" style="max-width: 900px;">
                <div class="d-flex align-items-center justify-content-center mb-1">
                    <img src="{{ asset('assets/img/project/logo.svg') }}" alt="{{ config('app.name') }}"
                        width="22" height="22" style="border-radius: 5px;">
                    <span class="ms-2 fw-semibold text-dark">{{ config('app.name') }}</span>
                </div>
                <small class="text-muted">
                    &copy; {{ date('Y') }} {{ config('app.name') }}. {{ t('footer.rights') }}
                </small>
                <small class="text-muted d-block">Powered by {{ config('app.name') }}</small>
            </div>
        </footer>
    </div>

    @include('contents.end-section')
    @stack('scripts')
</body>
</html>
