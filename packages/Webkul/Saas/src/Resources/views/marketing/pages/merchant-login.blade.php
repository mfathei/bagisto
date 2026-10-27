@extends('saas::marketing.layouts.app')

@section('content')
    <section class="page-hero">
        <div class="shell">
            <span class="eyebrow">@lang('saas::marketing.merchant_login.eyebrow')</span>

            <h1>@lang('saas::marketing.merchant_login.title')</h1>

            <p>@lang('saas::marketing.merchant_login.description')</p>
        </div>
    </section>

    <section class="section">
        <div class="shell">
            <div class="form-grid">
                <div class="card">
                    <form method="POST" action="{{ route('saas.marketing.merchant-login.store', [], false) }}">
                        @csrf

                        <div class="field">
                            <label for="store">@lang('saas::marketing.merchant_login.store_label')</label>

                            <input
                                id="store"
                                type="text"
                                name="store"
                                value="{{ old('store') }}"
                                maxlength="191"
                                required
                                autofocus
                                autocomplete="url"
                                dir="ltr"
                                placeholder="{{ trans('saas::marketing.merchant_login.store_placeholder') }}"
                            >

                            <p class="muted small mt-1">@lang('saas::marketing.merchant_login.store_help')</p>

                            @error('store')
                                <p class="error">{{ $message }}</p>
                            @enderror
                        </div>

                        <x-marketing::button type="submit">
                            @lang('saas::marketing.merchant_login.submit')
                            <x-marketing::icon name="arrow-right" size="16" />
                        </x-marketing::button>
                    </form>
                </div>

                <aside class="side-panel">
                    <h3>@lang('saas::marketing.merchant_login.side_title')</h3>

                    <ul>
                        <li>@lang('saas::marketing.merchant_login.side_domain')</li>
                        <li>@lang('saas::marketing.merchant_login.side_handle')</li>
                        <li>@lang('saas::marketing.merchant_login.side_security')</li>
                    </ul>

                    @if ($site['registration_enabled'])
                        <h3 class="mt-3">@lang('saas::marketing.merchant_login.new_title')</h3>

                        <p class="mt-1">@lang('saas::marketing.merchant_login.new_description')</p>

                        <div class="mt-2">
                            <x-marketing::button :href="route('saas.registration.create', [], false)" variant="ghost">
                                @lang('saas::marketing.nav.start')
                            </x-marketing::button>
                        </div>
                    @endif
                </aside>
            </div>
        </div>
    </section>
@endsection
