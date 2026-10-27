@extends('saas::marketing.layouts.app')

@section('content')
    <section class="hero">
        <div class="shell hero-inner">
            <div class="hero-copy">
                <span class="eyebrow">
                    <x-marketing::icon name="sparkle" size="14" />
                    @lang('saas::marketing.home.hero.eyebrow')
                </span>

                <h1>{{ $hero['title'] }}</h1>

                <p>{{ $hero['description'] }}</p>

                <div class="hero-actions">
                    @if ($site['registration_enabled'])
                        <x-marketing::button :href="route('saas.registration.create', [], false)">
                            {{ $hero['primary'] }}
                            <x-marketing::icon name="arrow-right" size="16" />
                        </x-marketing::button>
                    @else
                        <x-marketing::button :href="route('saas.marketing.contact', [], false)">
                            @lang('saas::marketing.common.contact_sales')
                        </x-marketing::button>
                    @endif

                    <x-marketing::button :href="route('saas.marketing.features', [], false)" variant="ghost">
                        {{ $hero['secondary'] }}
                    </x-marketing::button>
                </div>

                <div class="hero-note">
                    @foreach (trans('saas::marketing.home.hero.notes') as $note)
                        <span>
                            <span class="tick"><x-marketing::icon name="check" /></span>
                            {{ $note }}
                        </span>
                    @endforeach
                </div>
            </div>

            <div class="hero-visual">
                <div class="mock" role="img" aria-label="@lang('saas::marketing.home.hero.preview_alt')">
                    <div class="mock-bar" aria-hidden="true">
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="dot"></span>
                        <span class="mock-url"></span>
                    </div>

                    <div class="mock-body" aria-hidden="true">
                        <div class="mock-side">
                            <span class="side-brand"></span>
                            <span class="active"></span>
                            <span></span>
                            <span></span>
                            <span></span>
                            <span></span>
                        </div>

                        <div class="mock-main">
                            <div class="mock-kpis">
                                @foreach (trans('saas::marketing.home.hero.kpis') as $kpi)
                                    <div class="kpi">
                                        <small>{{ $kpi }}</small>
                                        <span class="kpi-placeholder"></span>
                                        <span class="bar"><i></i></span>
                                    </div>
                                @endforeach
                            </div>

                            <div class="mock-grid">
                                @foreach (range(1, 4) as $item)
                                    <div class="mock-product">
                                        <div class="thumb"></div>
                                        <div class="lines">
                                            <i></i>
                                            <i></i>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>

                <div class="float-card">
                    <span class="tick"><x-marketing::icon name="check" /></span>
                    @lang('saas::marketing.home.hero.published')
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <x-marketing::section-heading
                :eyebrow="trans('saas::marketing.home.trust.eyebrow')"
                :title="trans('saas::marketing.home.trust.title')"
                :description="trans('saas::marketing.home.trust.description')"
                center
            />

            <div class="grid grid-3">
                @foreach (trans('saas::marketing.home.trust.items') as $item)
                    <div class="value-item">
                        <span class="icon-badge">
                            <x-marketing::icon name="check" />
                        </span>

                        <div>
                            <h3>{{ $item['title'] }}</h3>
                            <p>{{ $item['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section section-surface">
        <div class="shell">
            <x-marketing::section-heading
                :eyebrow="trans('saas::marketing.home.features.eyebrow')"
                :title="trans('saas::marketing.home.features.title')"
                :description="trans('saas::marketing.home.features.description')"
                center
            />

            <div class="grid grid-3">
                @foreach ($features as $feature)
                    <x-marketing::feature-card :feature="$feature" />
                @endforeach
            </div>

            <div class="center mt-3">
                <x-marketing::button :href="route('saas.marketing.features', [], false)" variant="ghost">
                    @lang('saas::marketing.common.explore_features')
                    <x-marketing::icon name="arrow-right" size="16" />
                </x-marketing::button>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <x-marketing::section-heading
                :eyebrow="trans('saas::marketing.home.how.eyebrow')"
                :title="trans('saas::marketing.home.how.title')"
                :description="trans('saas::marketing.home.how.description')"
                center
            />

            <div class="steps">
                @foreach (trans('saas::marketing.home.how.steps') as $index => $step)
                    <div class="step">
                        <span class="step-number">{{ $index + 1 }}</span>
                        <h3>{{ $step['title'] }}</h3>
                        <p>{{ $step['description'] }}</p>
                    </div>
                @endforeach
            </div>

            @if ($site['registration_enabled'])
                <div class="center mt-3">
                    <x-marketing::button :href="route('saas.registration.create', [], false)">
                        @lang('saas::marketing.nav.start')
                        <x-marketing::icon name="arrow-right" size="16" />
                    </x-marketing::button>
                </div>
            @endif
        </div>
    </section>

    <section class="section section-surface">
        <div class="shell">
            <x-marketing::section-heading
                :eyebrow="trans('saas::marketing.home.showcase.eyebrow')"
                :title="trans('saas::marketing.home.showcase.title')"
                :description="trans('saas::marketing.home.showcase.description')"
                center
            />

            <div class="showcase">
                <div class="showcase-card">
                    <h3>@lang('saas::marketing.home.showcase.storefront.title')</h3>
                    <p>@lang('saas::marketing.home.showcase.storefront.description')</p>

                    <div class="mini-ui" aria-hidden="true">
                        @foreach (trans('saas::marketing.home.showcase.storefront.rows') as $row)
                            <div class="mini-row">
                                <span class="swatch"></span>
                                <span class="grow">{{ $row }}</span>
                                <x-marketing::icon name="arrow-right" size="13" />
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="showcase-card">
                    <h3>@lang('saas::marketing.home.showcase.products.title')</h3>
                    <p>@lang('saas::marketing.home.showcase.products.description')</p>

                    <div class="mini-ui" aria-hidden="true">
                        @foreach (trans('saas::marketing.home.showcase.products.rows') as $row)
                            <div class="mini-row">
                                <span class="swatch"></span>
                                <span class="grow">{{ $row }}</span>
                                <span class="pill">@lang('saas::marketing.home.showcase.products.active')</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="showcase-card">
                    <h3>@lang('saas::marketing.home.showcase.orders.title')</h3>
                    <p>@lang('saas::marketing.home.showcase.orders.description')</p>

                    <div class="mini-ui" aria-hidden="true">
                        @foreach (trans('saas::marketing.home.showcase.orders.rows') as $row)
                            <div class="mini-row">
                                <span class="grow">{{ $row }}</span>
                                <span class="pill">@lang('saas::marketing.home.showcase.orders.status')</span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="showcase-card">
                    <h3>@lang('saas::marketing.home.showcase.mobile.title')</h3>
                    <p>@lang('saas::marketing.home.showcase.mobile.description')</p>

                    <div class="phone-ui" aria-hidden="true">
                        <span class="ph-img"></span>
                        <span class="ph-line"></span>
                        <span class="ph-line short"></span>
                        <span class="ph-btn"></span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <x-marketing::section-heading
                :eyebrow="trans('saas::marketing.home.pricing.eyebrow')"
                :title="trans('saas::marketing.home.pricing.title')"
                :description="trans('saas::marketing.home.pricing.description')"
                center
            />

            @if ($plans->isEmpty())
                <div class="empty-state">
                    <h3>@lang('saas::marketing.pricing.no_plans')</h3>
                    <p>@lang('saas::marketing.pricing.no_plans_help')</p>

                    <div class="mt-2">
                        <x-marketing::button :href="route('saas.marketing.contact', [], false)" variant="ghost">
                            @lang('saas::marketing.common.contact_sales')
                        </x-marketing::button>
                    </div>
                </div>
            @else
                <div class="pricing-grid">
                    @foreach ($plans as $plan)
                        <x-marketing::pricing-card :plan="$plan" :registration-enabled="$site['registration_enabled']" />
                    @endforeach
                </div>
            @endif

            <div class="center mt-3">
                <x-marketing::button :href="route('saas.marketing.pricing', [], false)" variant="ghost">
                    @lang('saas::marketing.home.pricing.cta')
                    <x-marketing::icon name="arrow-right" size="16" />
                </x-marketing::button>
            </div>
        </div>
    </section>

    <section class="section section-surface">
        <div class="shell">
            <x-marketing::section-heading
                :eyebrow="trans('saas::marketing.home.why.eyebrow')"
                :title="trans('saas::marketing.home.why.title')"
                :description="trans('saas::marketing.home.why.description')"
                center
            />

            <div class="grid grid-2">
                @foreach (trans('saas::marketing.home.why.items') as $item)
                    <div class="card">
                        <span class="icon-badge">
                            <x-marketing::icon :name="$item['icon']" />
                        </span>

                        <h3>{{ $item['title'] }}</h3>
                        <p class="muted mt-1">{{ $item['description'] }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <x-marketing::section-heading
                :eyebrow="trans('saas::marketing.home.faq.eyebrow')"
                :title="trans('saas::marketing.home.faq.title')"
                :description="trans('saas::marketing.home.faq.description')"
                center
            />

            <x-marketing::faq :items="$faqs" />
        </div>
    </section>

    <section class="section tight">
        <div class="shell">
            <div class="cta-panel">
                <div>
                    <h2>@lang('saas::marketing.home.cta.title')</h2>
                    <p>@lang('saas::marketing.home.cta.description')</p>
                </div>

                <div class="cta-actions">
                    @if ($site['registration_enabled'])
                        <x-marketing::button :href="route('saas.registration.create', [], false)" variant="light">
                            @lang('saas::marketing.nav.start')
                        </x-marketing::button>
                    @else
                        <x-marketing::button :href="route('saas.marketing.contact', [], false)" variant="light">
                            @lang('saas::marketing.common.contact_sales')
                        </x-marketing::button>
                    @endif

                    <x-marketing::button :href="route('saas.marketing.pricing', [], false)" variant="outline-light">
                        @lang('saas::marketing.common.view_pricing')
                    </x-marketing::button>
                </div>
            </div>
        </div>
    </section>
@endsection
