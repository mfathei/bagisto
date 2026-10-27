<header class="site-header">
    <div class="shell nav-shell">
        <a class="brand" href="{{ route('saas.marketing.home', [], false) }}">
            <span class="brand-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3 3 8l9 5 9-5-9-5Z" />
                    <path d="m3 13 9 5 9-5" />
                    <path d="m3 18 9 5 9-5" />
                </svg>
            </span>

            <span class="brand-name">{{ $site['platform_name'] }}</span>
        </a>

        <nav class="desktop-nav" aria-label="@lang('saas::marketing.nav.primary')">
            @foreach (config('saas.marketing.navigation', []) as $item)
                <a
                    href="{{ route($item['route'], [], false) }}"
                    @class(['active' => request()->routeIs($item['route'])])
                    @if (request()->routeIs($item['route'])) aria-current="page" @endif
                >{{ __($item['label']) }}</a>
            @endforeach
        </nav>

        <div class="nav-actions">
            <div class="lang-switch" role="group" aria-label="@lang('saas::marketing.nav.language')">
                <a
                    href="{{ route('saas.marketing.locale', ['locale' => 'en', 'redirect' => request()->getRequestUri()], false) }}"
                    aria-current="{{ ($marketingLocale ?? 'en') === 'en' ? 'true' : 'false' }}"
                    lang="en"
                >EN</a>

                <a
                    href="{{ route('saas.marketing.locale', ['locale' => 'ar', 'redirect' => request()->getRequestUri()], false) }}"
                    aria-current="{{ ($marketingLocale ?? 'en') === 'ar' ? 'true' : 'false' }}"
                    lang="ar"
                >عربي</a>
            </div>

            <a class="btn btn-ghost btn-sm" href="{{ route('saas.marketing.merchant-login', [], false) }}">
                @lang('saas::marketing.nav.merchant_login')
            </a>

            @if ($site['registration_enabled'])
                <a class="btn btn-primary btn-sm" href="{{ route('saas.registration.create', [], false) }}">
                    @lang('saas::marketing.nav.start')
                </a>
            @endif
        </div>

        <details class="mobile-nav">
            <summary aria-label="@lang('saas::marketing.nav.menu')">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" aria-hidden="true">
                    <path d="M4 7h16M4 12h16M4 17h16" />
                </svg>
            </summary>

            <div class="mobile-panel">
                @foreach (config('saas.marketing.navigation', []) as $item)
                    <a href="{{ route($item['route'], [], false) }}">{{ __($item['label']) }}</a>
                @endforeach

                <a href="{{ route('saas.marketing.merchant-login', [], false) }}">
                    @lang('saas::marketing.nav.merchant_login')
                </a>

                <div class="lang-switch" role="group" aria-label="@lang('saas::marketing.nav.language')" style="margin-top: 8px;">
                    <a href="{{ route('saas.marketing.locale', ['locale' => 'en', 'redirect' => request()->getRequestUri()], false) }}" lang="en" aria-current="{{ ($marketingLocale ?? 'en') === 'en' ? 'true' : 'false' }}">EN</a>
                    <a href="{{ route('saas.marketing.locale', ['locale' => 'ar', 'redirect' => request()->getRequestUri()], false) }}" lang="ar" aria-current="{{ ($marketingLocale ?? 'en') === 'ar' ? 'true' : 'false' }}">عربي</a>
                </div>

                @if ($site['registration_enabled'])
                    <a class="btn btn-primary mobile-cta" href="{{ route('saas.registration.create', [], false) }}">
                        @lang('saas::marketing.nav.start')
                    </a>
                @endif
            </div>
        </details>
    </div>
</header>
