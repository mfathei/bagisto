<footer class="site-footer">
    <div class="shell">
        <div class="footer-grid">
            <div class="footer-about">
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

                <p>@lang('saas::marketing.footer.description', ['platform' => $site['platform_name']])</p>

                @if ($site['social'] !== [])
                    <div class="social-links">
                        @foreach ($site['social'] as $network => $url)
                            <a
                                href="{{ $url }}"
                                target="_blank"
                                rel="noopener noreferrer nofollow"
                                aria-label="{{ trans('saas::marketing.social.'.$network) }}"
                            >
                                <x-marketing::icon :name="$network" />
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <h3>@lang('saas::marketing.footer.product')</h3>

                <ul>
                    <li><a href="{{ route('saas.marketing.features', [], false) }}">@lang('saas::marketing.nav.features')</a></li>
                    <li><a href="{{ route('saas.marketing.solutions', [], false) }}">@lang('saas::marketing.nav.solutions')</a></li>
                    <li><a href="{{ route('saas.marketing.pricing', [], false) }}">@lang('saas::marketing.nav.pricing')</a></li>
                    <li><a href="{{ route('saas.marketing.resources', [], false) }}">@lang('saas::marketing.nav.resources')</a></li>
                </ul>
            </div>

            <div>
                <h3>@lang('saas::marketing.footer.company')</h3>

                <ul>
                    <li><a href="{{ route('saas.marketing.about', [], false) }}">@lang('saas::marketing.nav.about')</a></li>
                    <li><a href="{{ route('saas.marketing.contact', [], false) }}">@lang('saas::marketing.nav.contact')</a></li>
                    <li><a href="{{ route('saas.marketing.merchant-login', [], false) }}">@lang('saas::marketing.footer.merchant_login')</a></li>

                    @if ($site['registration_enabled'])
                        <li><a href="{{ route('saas.registration.create', [], false) }}">@lang('saas::marketing.nav.start')</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <h3>@lang('saas::marketing.footer.help')</h3>

                <ul>
                    <li><a href="{{ route('saas.marketing.resources', [], false) }}#faq">@lang('saas::marketing.footer.faq')</a></li>
                    <li><a href="{{ route('saas.marketing.resources', [], false) }}#guides">@lang('saas::marketing.footer.guides')</a></li>

                    @if ($site['contact_email'])
                        <li><a href="mailto:{{ $site['contact_email'] }}">{{ $site['contact_email'] }}</a></li>
                    @endif

                    @if ($site['contact_phone'])
                        <li><a href="tel:{{ preg_replace('/[^0-9+]/', '', $site['contact_phone']) }}" dir="ltr">{{ $site['contact_phone'] }}</a></li>
                    @endif
                </ul>
            </div>

            <div>
                <h3>@lang('saas::marketing.footer.legal')</h3>

                <ul>
                    <li><a href="{{ route('saas.marketing.privacy', [], false) }}">@lang('saas::marketing.legal.privacy.title')</a></li>
                    <li><a href="{{ route('saas.marketing.terms', [], false) }}">@lang('saas::marketing.legal.terms.title')</a></li>
                    <li><a href="{{ route('saas.marketing.refund-policy', [], false) }}">@lang('saas::marketing.legal.refund-policy.title')</a></li>
                </ul>
            </div>
        </div>

        <div class="footer-bottom">
            <span>&copy; {{ now()->year }} {{ $site['platform_name'] }}. @lang('saas::marketing.footer.rights')</span>

            <span>
                <a class="admin-link" href="{{ route('saas.super-admin.login', [], false) }}">@lang('saas::marketing.footer.admin')</a>
            </span>
        </div>
    </div>
</footer>
