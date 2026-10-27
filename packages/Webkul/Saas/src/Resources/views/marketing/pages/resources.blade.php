@extends('saas::marketing.layouts.app')

@section('content')
    <section class="page-hero">
        <div class="shell">
            <span class="eyebrow">@lang('saas::marketing.resources.eyebrow')</span>

            <h1>@lang('saas::marketing.resources.title')</h1>

            <p>@lang('saas::marketing.resources.description')</p>
        </div>
    </section>

    <section class="section" id="guides">
        <div class="shell">
            <x-marketing::section-heading
                :title="trans('saas::marketing.resources.guides_title')"
                :description="trans('saas::marketing.resources.guides_description')"
            />

            <div class="grid grid-3">
                @foreach (trans('saas::marketing.resources.guides') as $guide)
                    @php
                        $href = match ($guide['key']) {
                            'getting-started' => $site['registration_enabled']
                                ? route('saas.registration.create', [], false)
                                : route('saas.marketing.about', [], false),
                            'store-setup' => route('saas.marketing.features', [], false).'#store-management',
                            'products' => route('saas.marketing.features', [], false).'#catalog-inventory',
                            'orders' => route('saas.marketing.features', [], false).'#orders-fulfilment',
                            'domains' => route('saas.marketing.features', [], false).'#store-management',
                            'billing' => route('saas.marketing.pricing', [], false),
                            default => route('saas.marketing.features', [], false),
                        };
                    @endphp

                    <a class="feature-card" href="{{ $href }}">
                        <span class="icon-badge">
                            <x-marketing::icon name="catalog" />
                        </span>

                        <h3>{{ $guide['title'] }}</h3>

                        <p>{{ $guide['description'] }}</p>

                        <span class="benefit">
                            @lang('saas::marketing.resources.read_guide')
                            <x-marketing::icon name="arrow-right" />
                        </span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" id="faq" style="background: #fff; border-block: 1px solid var(--line);">
        <div class="shell">
            <x-marketing::section-heading
                :title="trans('saas::marketing.resources.faq_title')"
                :description="trans('saas::marketing.resources.faq_description')"
                center
            />

            <x-marketing::faq :items="$faqs" />
        </div>
    </section>

    <section class="section tight">
        <div class="shell">
            <div class="cta-panel">
                <div>
                    <h2>@lang('saas::marketing.resources.cta.title')</h2>
                    <p>@lang('saas::marketing.resources.cta.description')</p>
                </div>

                <div class="cta-actions">
                    <x-marketing::button :href="route('saas.marketing.contact', [], false)" variant="light">
                        @lang('saas::marketing.common.contact_us')
                    </x-marketing::button>

                    @if ($site['registration_enabled'])
                        <x-marketing::button :href="route('saas.registration.create', [], false)" variant="outline-light">
                            @lang('saas::marketing.nav.start')
                        </x-marketing::button>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
