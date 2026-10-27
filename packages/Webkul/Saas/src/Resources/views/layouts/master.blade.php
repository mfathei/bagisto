<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>@yield('title', trans('saas::app.super-admin.title'))</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">

    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>

    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700;800&display=swap" rel="stylesheet">

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

    @stack('styles')
</head>

<body>
    @php
        $superAdmin = auth('super_admin')->user();

        $tenantsListActive = request()->routeIs(
            'saas.super-admin.tenants.index',
            'saas.super-admin.tenants.show',
            'saas.super-admin.tenants.edit',
            'saas.super-admin.tenants.create'
        );

        $plansActive = request()->routeIs('saas.super-admin.plans.index', 'saas.super-admin.plans.show', 'saas.super-admin.plans.edit');

        $subscriptionsActive = request()->routeIs('saas.super-admin.subscriptions.*');

        $settingsActive = request()->routeIs('saas.super-admin.settings.*');
        $domainsActive = request()->routeIs('saas.super-admin.domains.*');
        $provisioningActive = request()->routeIs('saas.super-admin.provisioning.*');
        $auditActive = request()->routeIs('saas.super-admin.audit.*');
        $configureActive = request()->routeIs('saas.super-admin.configure.*')
            || $domainsActive
            || $provisioningActive
            || $auditActive;
    @endphp

    <header class="app-header">
        <div class="header-left">
            <button
                type="button"
                class="icon-btn"
                id="sidebar-toggle"
                aria-label="@lang('saas::app.super-admin.nav.toggle-menu')"
            >
                @include('saas::layouts.partials.icon', ['name' => 'menu'])
            </button>

            <a class="brand" href="{{ route('saas.super-admin.dashboard') }}">
                <img src="{{ bagisto_asset('images/logo.svg', 'admin') }}" alt="Bagisto">

                <span class="brand-divider"></span>

                <span class="brand-suffix">SaaS Console</span>
            </a>
        </div>

        <div class="header-right">
            <form
                class="header-search"
                method="GET"
                action="{{ route('saas.super-admin.tenants.index') }}"
            >
                @include('saas::layouts.partials.icon', ['name' => 'search'])

                <input
                    type="search"
                    name="search"
                    id="header-search-input"
                    placeholder="@lang('saas::app.super-admin.nav.search-placeholder')"
                    autocomplete="off"
                >

                <kbd>/</kbd>
            </form>

            <a
                class="icon-btn bordered"
                href="{{ route('saas.marketing.home') }}"
                target="_blank"
                rel="noopener"
                title="@lang('saas::app.super-admin.nav.view-website')"
            >
                @include('saas::layouts.partials.icon', ['name' => 'globe'])
            </a>

            <button
                type="button"
                class="icon-btn bordered"
                id="theme-toggle"
                aria-label="@lang('saas::app.super-admin.nav.toggle-theme')"
            >
                <span class="theme-icon-sun">@include('saas::layouts.partials.icon', ['name' => 'sun'])</span>

                <span class="theme-icon-moon">@include('saas::layouts.partials.icon', ['name' => 'moon'])</span>
            </button>

            <details class="user-menu">
                <summary>
                    <span class="avatar">{{ str($superAdmin->name ?? 'A')->substr(0, 1)->upper() }}</span>

                    <span class="user-meta">
                        <span class="user-name">{{ $superAdmin->name ?? '' }}</span>

                        <span class="user-role">@lang('saas::app.super-admin.nav.super-admin')</span>
                    </span>

                    @include('saas::layouts.partials.icon', ['name' => 'chevron-down'])
                </summary>

                <div class="user-dropdown">
                    <div class="dropdown-user">
                        <p>{{ $superAdmin->name ?? '' }}</p>

                        <span>{{ $superAdmin->email ?? '' }}</span>
                    </div>

                    <a class="dropdown-link" href="{{ route('saas.super-admin.settings.index') }}">
                        @include('saas::layouts.partials.icon', ['name' => 'settings'])

                        @lang('saas::app.super-admin.nav.settings')
                    </a>

                    <form method="POST" action="{{ route('saas.super-admin.logout') }}">
                        @csrf

                        <button type="submit" class="dropdown-button danger">
                            @include('saas::layouts.partials.icon', ['name' => 'logout'])

                            @lang('saas::app.super-admin.nav.logout')
                        </button>
                    </form>
                </div>
            </details>
        </div>
    </header>

    <div class="app-body">
        <div class="sidebar-backdrop" id="sidebar-backdrop"></div>

        <aside class="app-sidebar" id="app-sidebar">
            <p class="nav-section">@lang('saas::app.super-admin.nav.overview')</p>

            <nav class="side-nav">
                <a
                    href="{{ route('saas.super-admin.dashboard') }}"
                    class="{{ request()->routeIs('saas.super-admin.dashboard') ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'dashboard'])

                    @lang('saas::app.super-admin.nav.dashboard')
                </a>
            </nav>

            <p class="nav-section">@lang('saas::app.super-admin.nav.tenants')</p>

            <nav class="side-nav">
                <a
                    href="{{ route('saas.super-admin.tenants.index') }}"
                    class="{{ $tenantsListActive ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'tenants'])

                    @lang('saas::app.super-admin.nav.tenants-list')
                </a>

                <a
                    href="{{ route('saas.super-admin.tenants.orders') }}"
                    class="{{ request()->routeIs('saas.super-admin.tenants.orders') ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'orders'])

                    @lang('saas::app.super-admin.nav.orders-list')
                </a>

                <a
                    href="{{ route('saas.super-admin.tenants.products') }}"
                    class="{{ request()->routeIs('saas.super-admin.tenants.products') ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'products'])

                    @lang('saas::app.super-admin.nav.products-list')
                </a>

                <a
                    href="{{ route('saas.super-admin.tenants.customers') }}"
                    class="{{ request()->routeIs('saas.super-admin.tenants.customers') ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'customers'])

                    @lang('saas::app.super-admin.nav.customers-list')
                </a>
            </nav>

            <p class="nav-section">@lang('saas::app.super-admin.nav.subscription')</p>

            <nav class="side-nav">
                <a
                    href="{{ route('saas.super-admin.plans.index') }}"
                    class="{{ $plansActive ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'plans'])

                    @lang('saas::app.super-admin.nav.plans-list')
                </a>

                <a
                    href="{{ route('saas.super-admin.plans.create') }}"
                    class="{{ request()->routeIs('saas.super-admin.plans.create') ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'add-plan'])

                    @lang('saas::app.super-admin.nav.add-plan')
                </a>

                <a
                    href="{{ route('saas.super-admin.subscriptions.index') }}"
                    class="{{ $subscriptionsActive ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'subscriptions'])

                    @lang('saas::app.super-admin.nav.purchased-plans')
                </a>

                <a
                    href="{{ route('saas.super-admin.billing.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.billing.*') ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'invoices'])

                    @lang('saas::app.super-admin.nav.subscription-invoices')
                </a>
            </nav>

            <p class="nav-section">@lang('saas::app.super-admin.nav.platform')</p>

            <nav class="side-nav">
                <a
                    href="{{ route('saas.super-admin.cms.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.cms.*') ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'cms'])

                    @lang('saas::app.super-admin.nav.cms')
                </a>

                <a
                    href="{{ route('saas.super-admin.insights.index') }}"
                    class="{{ request()->routeIs('saas.super-admin.insights.*') ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'insights'])

                    @lang('saas::app.super-admin.nav.insights')
                </a>
            </nav>

            <p class="nav-section">@lang('saas::app.super-admin.nav.configure')</p>

            <nav class="side-nav">
                <a
                    href="{{ route('saas.super-admin.configure.index') }}"
                    class="{{ $configureActive ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'configure'])

                    @lang('saas::app.super-admin.nav.configuration')
                </a>

                <a
                    href="{{ route('saas.super-admin.settings.index') }}"
                    class="{{ $settingsActive ? 'active' : '' }}"
                >
                    @include('saas::layouts.partials.icon', ['name' => 'settings'])

                    @lang('saas::app.super-admin.nav.settings')
                </a>
            </nav>

            <div class="sidebar-footer">
                <p>{{ trans('saas::app.super-admin.brand', ['platform' => config('saas.platform_name')]) }}</p>
            </div>
        </aside>

        <main class="app-main">
            <div class="content">
                @if (session('success'))
                    <div class="alert success">
                        @include('saas::layouts.partials.icon', ['name' => 'check'])

                        <div>{{ session('success') }}</div>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert error">
                        @include('saas::layouts.partials.icon', ['name' => 'alert'])

                        <div>{{ session('error') }}</div>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert error">
                        @include('saas::layouts.partials.icon', ['name' => 'alert'])

                        <div>
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endif

                @yield('content')
            </div>

            <footer class="content-footer">
                @lang('saas::app.super-admin.footer', ['year' => now()->year])
            </footer>
        </main>
    </div>

    <script>
        (function () {
            var root = document.documentElement;

            var themeToggle = document.getElementById('theme-toggle');

            if (themeToggle) {
                themeToggle.addEventListener('click', function () {
                    if (root.getAttribute('data-theme') === 'dark') {
                        root.removeAttribute('data-theme');

                        localStorage.setItem('saas-theme', 'light');
                    } else {
                        root.setAttribute('data-theme', 'dark');

                        localStorage.setItem('saas-theme', 'dark');
                    }
                });
            }

            var sidebar = document.getElementById('app-sidebar');
            var sidebarToggle = document.getElementById('sidebar-toggle');
            var backdrop = document.getElementById('sidebar-backdrop');

            function closeSidebar() {
                sidebar.classList.remove('open');

                backdrop.classList.remove('open');
            }

            if (sidebarToggle && sidebar && backdrop) {
                sidebarToggle.addEventListener('click', function () {
                    sidebar.classList.toggle('open');

                    backdrop.classList.toggle('open');
                });

                backdrop.addEventListener('click', closeSidebar);
            }

            document.querySelectorAll('[data-modal-open]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var modal = document.getElementById(trigger.getAttribute('data-modal-open'));

                    if (modal && typeof modal.showModal === 'function') {
                        modal.showModal();
                    }
                });
            });

            document.querySelectorAll('[data-modal-close]').forEach(function (trigger) {
                trigger.addEventListener('click', function () {
                    var modal = trigger.closest('dialog');

                    if (modal) {
                        modal.close();
                    }
                });
            });

            document.querySelectorAll('dialog.modal').forEach(function (modal) {
                modal.addEventListener('click', function (event) {
                    if (event.target === modal) {
                        modal.close();
                    }
                });
            });

            document.querySelectorAll('form[data-confirm]').forEach(function (form) {
                form.addEventListener('submit', function (event) {
                    if (! window.confirm(form.getAttribute('data-confirm'))) {
                        event.preventDefault();
                    }
                });
            });

            document.querySelectorAll('[data-auto-submit]').forEach(function (control) {
                control.addEventListener('change', function () {
                    if (control.form) {
                        control.form.submit();
                    }
                });
            });

            document.querySelectorAll('.dg-check-all').forEach(function (master) {
                master.addEventListener('change', function () {
                    var table = master.closest('table');

                    if (! table) {
                        return;
                    }

                    table.querySelectorAll('.dg-check-row').forEach(function (checkbox) {
                        checkbox.checked = master.checked;
                    });
                });
            });

            var searchInput = document.getElementById('header-search-input');

            document.addEventListener('keydown', function (event) {
                if (event.key === '/' && document.activeElement !== searchInput && ! ['INPUT', 'TEXTAREA', 'SELECT'].includes(document.activeElement.tagName)) {
                    event.preventDefault();

                    if (searchInput) {
                        searchInput.focus();
                    }
                }
            });

            document.addEventListener('click', function (event) {
                document.querySelectorAll('details.user-menu[open]').forEach(function (menu) {
                    if (! menu.contains(event.target)) {
                        menu.removeAttribute('open');
                    }
                });
            });
        })();
    </script>

    @stack('scripts')
</body>
</html>
