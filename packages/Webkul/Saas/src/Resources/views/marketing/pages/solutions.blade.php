@extends('saas::marketing.layouts.app')

@section('content')
    <section class="page-hero">
        <div class="shell">
            <span class="eyebrow">@lang('saas::marketing.solutions.eyebrow')</span>

            <h1>@lang('saas::marketing.solutions.title')</h1>

            <p>@lang('saas::marketing.solutions.description')</p>

            <div class="hero-actions">
                @if ($site['registration_enabled'])
                    <x-marketing::button :href="route('saas.registration.create', [], false)">
                        @lang('saas::marketing.nav.start')
                        <x-marketing::icon name="arrow-right" size="16" />
                    </x-marketing::button>
                @endif

                <x-marketing::button :href="route('saas.marketing.contact', [], false)" variant="ghost">
                    @lang('saas::marketing.common.contact_sales')
                </x-marketing::button>
            </div>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <div class="grid grid-2">
                @foreach ($solutions as $solution)
                    <article class="card">
                        <span class="icon-badge">
                            <x-marketing::icon :name="$solution['icon']" />
                        </span>

                        <h2 style="font-size: 22px;">{{ $solution['title'] }}</h2>

                        <p class="muted mt-1">{{ $solution['description'] }}</p>

                        @if (is_array($solution['points']))
                            <ul class="list-check mt-2">
                                @foreach ($solution['points'] as $point)
                                    <li>
                                        <span class="tick"><x-marketing::icon name="check" /></span>
                                        {{ $point }}
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                        <div class="mt-2">
                            <a href="{{ route('saas.marketing.features', [], false) }}">
                                @lang('saas::marketing.solutions.explore')
                                <x-marketing::icon name="arrow-right" size="14" />
                            </a>
                        </div>
                    </article>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section tight" style="background: #fff; border-top: 1px solid var(--line);">
        <div class="shell">
            <div class="cta-panel">
                <div>
                    <h2>@lang('saas::marketing.solutions.cta.title')</h2>
                    <p>@lang('saas::marketing.solutions.cta.description')</p>
                </div>

                <div class="cta-actions">
                    @if ($site['registration_enabled'])
                        <x-marketing::button :href="route('saas.registration.create', [], false)" variant="light">
                            @lang('saas::marketing.nav.start')
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
