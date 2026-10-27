@extends('saas::marketing.layouts.app')

@section('content')
    <section class="page-hero">
        <div class="shell">
            <span class="eyebrow">@lang('saas::marketing.features.eyebrow')</span>

            <h1>@lang('saas::marketing.features.title')</h1>

            <p>@lang('saas::marketing.features.description')</p>

            <div class="hero-actions">
                @if ($site['registration_enabled'])
                    <x-marketing::button :href="route('saas.registration.create', [], false)">
                        @lang('saas::marketing.nav.start')
                        <x-marketing::icon name="arrow-right" size="16" />
                    </x-marketing::button>
                @endif

                <x-marketing::button :href="route('saas.marketing.pricing', [], false)" variant="ghost">
                    @lang('saas::marketing.common.view_pricing')
                </x-marketing::button>
            </div>
        </div>
    </section>

    @foreach ($groups as $group)
        <section
            class="section"
            id="{{ $group['key'] }}"
            @if ($loop->even) style="background: #fff; border-block: 1px solid var(--line);" @endif
        >
            <div class="shell">
                <x-marketing::section-heading
                    :eyebrow="trans('saas::marketing.features.groups.'.$group['key'].'.eyebrow')"
                    :title="$group['title']"
                    :description="$group['description']"
                />

                <div class="grid grid-3">
                    @foreach ($group['items'] as $feature)
                        <x-marketing::feature-card :feature="$feature" />
                    @endforeach
                </div>

                @if (in_array($group['key'], ['store-management', 'catalog-inventory', 'orders-fulfilment', 'analytics', 'storefront'], true))
                    @php
                        $previewType = match ($group['key']) {
                            'store-management' => 'store',
                            'catalog-inventory' => 'catalog',
                            'orders-fulfilment' => 'orders',
                            'analytics' => 'analytics',
                            'storefront' => 'mobile',
                            default => 'store',
                        };
                    @endphp

                    <div class="mt-3" style="max-width: 560px;">
                        <x-marketing::interface-preview :type="$previewType" />
                    </div>
                @endif
            </div>
        </section>
    @endforeach

    <section class="section tight">
        <div class="shell">
            <div class="cta-panel">
                <div>
                    <h2>@lang('saas::marketing.features.cta.title')</h2>
                    <p>@lang('saas::marketing.features.cta.description')</p>
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
