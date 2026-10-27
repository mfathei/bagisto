@extends('saas::marketing.layouts.app')

@section('content')
    <section class="page-hero">
        <div class="shell">
            <span class="eyebrow">@lang('saas::marketing.about.eyebrow')</span>

            <h1>@lang('saas::marketing.about.title')</h1>

            <p>{{ $aboutIntro ?? trans('saas::marketing.about.intro', ['platform' => $site['platform_name']]) }}</p>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <div class="grid grid-3">
                @foreach (['mission', 'vision', 'philosophy'] as $pillar)
                    <div class="card">
                        <span class="icon-badge">
                            <x-marketing::icon :name="match ($pillar) {
                                'mission' => 'rocket',
                                'vision' => 'sparkle',
                                default => 'shield',
                            }" />
                        </span>

                        <h2 style="font-size: 21px;">@lang('saas::marketing.about.'.$pillar.'.title')</h2>

                        <p class="muted mt-1">@lang('saas::marketing.about.'.$pillar.'.description')</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section" style="background: #fff; border-block: 1px solid var(--line);">
        <div class="shell">
            <x-marketing::section-heading
                :title="trans('saas::marketing.about.values_title')"
                :description="trans('saas::marketing.about.values_description')"
                center
            />

            <div class="values">
                @foreach (trans('saas::marketing.about.values') as $value)
                    <div class="value-item">
                        <span class="icon-badge">
                            <x-marketing::icon name="check" />
                        </span>

                        <div>
                            <h3>{{ $value['title'] }}</h3>
                            <p>{{ $value['description'] }}</p>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <div class="grid grid-2" style="align-items: start;">
                <div>
                    <x-marketing::section-heading
                        :title="trans('saas::marketing.about.capabilities_title')"
                        :description="trans('saas::marketing.about.capabilities_description')"
                    />
                </div>

                <ul class="list-check">
                    @foreach (trans('saas::marketing.about.capabilities') as $capability)
                        <li>
                            <span class="tick"><x-marketing::icon name="check" /></span>
                            {{ $capability }}
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </section>

    <section class="section tight" style="background: #fff; border-top: 1px solid var(--line);">
        <div class="shell">
            <div class="cta-panel">
                <div>
                    <h2>@lang('saas::marketing.about.cta.title')</h2>
                    <p>@lang('saas::marketing.about.cta.description')</p>
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
