@extends('saas::marketing.layouts.app')

@section('content')
    <section class="page-hero">
        <div class="shell">
            <span class="eyebrow">@lang('saas::marketing.pricing.eyebrow')</span>

            <h1>@lang('saas::marketing.pricing.title')</h1>

            <p>@lang('saas::marketing.pricing.description')</p>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            @if ($plans->isEmpty())
                <div class="empty-state">
                    <h2>@lang('saas::marketing.pricing.no_plans')</h2>
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

                <p class="muted small center mt-3" style="max-width: 720px; margin-inline: auto;">
                    @lang('saas::marketing.pricing.billing_note')
                </p>
            @endif
        </div>
    </section>

    <section class="section" style="background: #fff; border-block: 1px solid var(--line);">
        <div class="shell">
            <x-marketing::section-heading
                :title="trans('saas::marketing.pricing.faq_title')"
                :description="trans('saas::marketing.pricing.faq_description')"
                center
            />

            <x-marketing::faq :items="$faqs" />
        </div>
    </section>

    <section class="section tight">
        <div class="shell">
            <div class="cta-panel">
                <div>
                    <h2>@lang('saas::marketing.pricing.cta.title')</h2>
                    <p>@lang('saas::marketing.pricing.cta.description')</p>
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

                    <x-marketing::button :href="route('saas.marketing.features', [], false)" variant="outline-light">
                        @lang('saas::marketing.common.explore_features')
                    </x-marketing::button>
                </div>
            </div>
        </div>
    </section>
@endsection
