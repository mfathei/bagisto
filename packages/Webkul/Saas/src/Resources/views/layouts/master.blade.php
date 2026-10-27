<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', trans('saas::app.super-admin.title'))</title>

    <script>
        (function () {
            if (localStorage.getItem('saas-theme') === 'dark'
                || (! localStorage.getItem('saas-theme') && window.matchMedia('(prefers-color-scheme: dark)').matches)
            ) {
                document.documentElement.setAttribute('data-theme', 'dark');
            }
        })();
    </script>

    @include('saas::layouts.styles')
</head>

<body>
    <div class="shell">
        <aside class="sidebar">
            <a class="brand" href="{{ route('saas.super-admin.dashboard') }}">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                    <rect x="4" y="11" width="16" height="10" rx="2"></rect>
                    <path d="M8 11V7a4 4 0 0 1 8 0v4"></path>
                </svg>

                <span>{{ trans('saas::app.super-admin.brand', ['platform' => config('saas.platform_name')]) }}</span>
            </a>

            <nav class="side-nav">
                <a
                    href="{{ route('saas.super-admin.dashboard') }}"
                    class="{{ request()->routeIs('saas.super-admin.dashboard') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="3" y="3" width="7" height="9" rx="1"></rect>
                        <rect x="14" y="3" width="7" height="5" rx="1"></rect>
                        <rect x="14" y="12" width="7" height="9" rx="1"></rect>
                        <rect x="3" y="16" width="7" height="5" rx="1"></rect>
                    </svg>

                    @lang('saas::app.super-admin.nav.dashboard')
                </a>

                <a
                    href="{{ route('saas.super-admin.tenants.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.tenants.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M3 21h18"></path>
                        <path d="M5 21V7l7-4 7 4v14"></path>
                        <path d="M9 9h1"></path>
                        <path d="M9 13h1"></path>
                        <path d="M9 17h1"></path>
                        <path d="M14 9h1"></path>
                        <path d="M14 13h1"></path>
                        <path d="M14 17h1"></path>
                    </svg>

                    @lang('saas::app.super-admin.nav.tenants')
                </a>

                <a
                    href="{{ route('saas.super-admin.subscriptions.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.subscriptions.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M21 12a9 9 0 1 1-3-6.7"></path>
                        <path d="M21 3v6h-6"></path>
                    </svg>

                    @lang('saas::app.super-admin.nav.subscriptions')
                </a>

                <a
                    href="{{ route('saas.super-admin.billing.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.billing.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="5" width="20" height="14" rx="2"></rect>
                        <path d="M2 10h20"></path>
                        <path d="M6 15h4"></path>
                    </svg>

                    @lang('saas::app.super-admin.nav.billing')
                </a>

                <a
                    href="{{ route('saas.super-admin.plans.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.plans.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 2 2 7l10 5 10-5-10-5Z"></path>
                        <path d="m2 17 10 5 10-5"></path>
                        <path d="m2 12 10 5 10-5"></path>
                    </svg>

                    @lang('saas::app.super-admin.nav.plans')
                </a>

                <a
                    href="{{ route('saas.super-admin.domains.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.domains.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M2 12h20"></path>
                        <path d="M12 2a15.3 15.3 0 0 1 0 20 15.3 15.3 0 0 1 0-20Z"></path>
                    </svg>

                    @lang('saas::app.super-admin.nav.domains')
                </a>

                <a
                    href="{{ route('saas.super-admin.provisioning.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.provisioning.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <rect x="2" y="3" width="20" height="7" rx="2"></rect>
                        <rect x="2" y="14" width="20" height="7" rx="2"></rect>
                        <path d="M6 6.5h.01"></path>
                        <path d="M6 17.5h.01"></path>
                    </svg>

                    @lang('saas::app.super-admin.nav.provisioning')
                </a>

                <a
                    href="{{ route('saas.super-admin.settings.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.settings.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="3"></circle>
                        <path d="M19.4 15a1.7 1.7 0 0 0 .3 1.9l.1.1a2 2 0 1 1-2.8 2.8l-.1-.1a1.7 1.7 0 0 0-1.9-.3 1.7 1.7 0 0 0-1 1.5V21a2 2 0 1 1-4 0v-.1a1.7 1.7 0 0 0-1-1.6 1.7 1.7 0 0 0-1.9.3l-.1.1a2 2 0 1 1-2.8-2.8l.1-.1a1.7 1.7 0 0 0 .3-1.9 1.7 1.7 0 0 0-1.5-1H3a2 2 0 1 1 0-4h.1a1.7 1.7 0 0 0 1.6-1 1.7 1.7 0 0 0-.3-1.9l-.1-.1a2 2 0 1 1 2.8-2.8l.1.1a1.7 1.7 0 0 0 1.9.3h.1a1.7 1.7 0 0 0 1-1.5V3a2 2 0 1 1 4 0v.1a1.7 1.7 0 0 0 1 1.5h.1a1.7 1.7 0 0 0 1.9-.3l.1-.1a2 2 0 1 1 2.8 2.8l-.1.1a1.7 1.7 0 0 0-.3 1.9v.1a1.7 1.7 0 0 0 1.5 1h.1a2 2 0 1 1 0 4h-.1a1.7 1.7 0 0 0-1.5 1Z"></path>
                    </svg>

                    @lang('saas::app.super-admin.nav.settings')
                </a>

                <a
                    href="{{ route('saas.super-admin.audit.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.audit.*') ? 'active' : '' }}"
                >
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M8 6h13"></path>
                        <path d="M8 12h13"></path>
                        <path d="M8 18h13"></path>
                        <path d="M3 6h.01"></path>
                        <path d="M3 12h.01"></path>
                        <path d="M3 18h.01"></path>
                    </svg>

                    @lang('saas::app.super-admin.nav.audit')
                </a>
            </nav>
        </aside>

        <div class="main">
            <header class="main-top">
                <button type="button" class="icon-btn" id="theme-toggle" aria-label="@lang('saas::app.super-admin.nav.toggle-theme')">
                    <svg class="icon-sun" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <circle cx="12" cy="12" r="4"></circle>
                        <path d="M12 2v2"></path>
                        <path d="M12 20v2"></path>
                        <path d="m4.9 4.9 1.4 1.4"></path>
                        <path d="m17.7 17.7 1.4 1.4"></path>
                        <path d="M2 12h2"></path>
                        <path d="M20 12h2"></path>
                        <path d="m6.3 17.7-1.4 1.4"></path>
                        <path d="m19.1 4.9-1.4 1.4"></path>
                    </svg>

                    <svg class="icon-moon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M12 3a6 6 0 0 0 9 9 9 9 0 1 1-9-9Z"></path>
                    </svg>
                </button>

                <details class="user-menu">
                    <summary>
                        <span class="avatar">{{ str(auth('super_admin')->user()->name ?? 'A')->substr(0, 1)->upper() }}</span>

                        <span class="user-name">{{ auth('super_admin')->user()->name ?? '' }}</span>

                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="m6 9 6 6 6-6"></path>
                        </svg>
                    </summary>

                    <div class="user-dropdown">
                        <p class="user-email">{{ auth('super_admin')->user()->email ?? '' }}</p>

                        <form
                            method="POST"
                            action="{{ route('saas.super-admin.logout') }}"
                        >
                            @csrf

                            <button type="submit" class="btn-link">
                                @lang('saas::app.super-admin.nav.logout')
                            </button>
                        </form>
                    </div>
                </details>
            </header>

            <main class="container">
                @if (session('success'))
                    <div class="alert success">{{ session('success') }}</div>
                @endif

                @if (session('error'))
                    <div class="alert error">{{ session('error') }}</div>
                @endif

                @if ($errors->any())
                    <div class="alert error">
                        <ul style="margin: 0; padding-inline-start: 18px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @yield('content')
            </main>
        </div>
    </div>

    <script>
        document.getElementById('theme-toggle').addEventListener('click', function () {
            var root = document.documentElement;

            if (root.getAttribute('data-theme') === 'dark') {
                root.removeAttribute('data-theme');

                localStorage.setItem('saas-theme', 'light');
            } else {
                root.setAttribute('data-theme', 'dark');

                localStorage.setItem('saas-theme', 'dark');
            }
        });
    </script>
</body>
</html>
