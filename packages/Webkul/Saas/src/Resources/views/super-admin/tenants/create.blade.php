@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.tenants.create-title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.super-admin.nav.tenants-list')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.tenants.create-title')</span>
            </div>

            <h1>@lang('saas::app.super-admin.tenants.create-title')</h1>

            <p>@lang('saas::app.super-admin.tenants.create-subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.tenants.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.common.back')
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('saas.super-admin.tenants.store') }}">
        @csrf

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.tenants.organization')</h2>

                    <p>@lang('saas::app.super-admin.tenants.general-help')</p>
                </div>
            </div>

            <div class="card-body">
                <div class="form-grid two">
                    <div class="field">
                        <label for="name">@lang('saas::app.common.name')</label>

                        <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                    </div>

                    <div class="field">
                        <label for="slug">@lang('saas::app.super-admin.tenants.slug')</label>

                        <input id="slug" name="slug" type="text" value="{{ old('slug') }}" required>

                        <span class="hint">@lang('saas::app.super-admin.tenants.slug-hint', ['suffix' => config('saas.domains.suffix')])</span>
                    </div>

                    <div class="field">
                        <label for="legal_name">@lang('saas::app.super-admin.tenants.legal-name')</label>

                        <input id="legal_name" name="legal_name" type="text" value="{{ old('legal_name') }}">
                    </div>

                    <div class="field">
                        <label for="email">@lang('saas::app.tenant.settings.contact-email')</label>

                        <input id="email" name="email" type="email" value="{{ old('email') }}">
                    </div>

                    <div class="field">
                        <label for="phone">@lang('saas::app.tenant.settings.contact-phone')</label>

                        <input id="phone" name="phone" type="text" value="{{ old('phone') }}">
                    </div>

                    <div class="field">
                        <label for="plan_id">@lang('saas::app.common.plan')</label>

                        <select id="plan_id" name="plan_id" required>
                            @foreach ($plans as $plan)
                                <option
                                    value="{{ $plan->getKey() }}"
                                    @selected((string) old('plan_id') === (string) $plan->getKey())
                                >{{ $plan->name }} &mdash; {{ number_format((float) $plan->price, 2) }} {{ $plan->currency }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.tenants.owner')</h2>

                    <p>@lang('saas::app.super-admin.tenants.owner-help')</p>
                </div>
            </div>

            <div class="card-body">
                <div class="form-grid two">
                    <div class="field">
                        <label for="owner_name">@lang('saas::app.super-admin.tenants.owner-name')</label>

                        <input id="owner_name" name="owner_name" type="text" value="{{ old('owner_name') }}" required>
                    </div>

                    <div class="field">
                        <label for="owner_email">@lang('saas::app.super-admin.tenants.owner-email')</label>

                        <input id="owner_email" name="owner_email" type="email" value="{{ old('owner_email') }}" required>
                    </div>

                    <div class="field">
                        <label for="owner_password">@lang('saas::app.super-admin.tenants.owner-password')</label>

                        <input id="owner_password" name="owner_password" type="password" required autocomplete="new-password">
                    </div>

                    <div class="field">
                        <label for="owner_password_confirmation">@lang('saas::app.super-admin.tenants.owner-password-confirmation')</label>

                        <input
                            id="owner_password_confirmation"
                            name="owner_password_confirmation"
                            type="password"
                            required
                            autocomplete="new-password"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.tenant.settings.localization')</h2>

                    <p>@lang('saas::app.super-admin.tenants.localization-help')</p>
                </div>
            </div>

            <div class="card-body">
                <div class="form-grid three">
                    <div class="field">
                        <label for="timezone">@lang('saas::app.tenant.settings.timezone')</label>

                        <select id="timezone" name="timezone">
                            @foreach ($timezones as $timezone)
                                <option value="{{ $timezone }}" @selected(old('timezone', config('saas.tenants.default_timezone')) === $timezone)>
                                    {{ $timezone }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="locale">@lang('saas::app.tenant.settings.locale')</label>

                        <select id="locale" name="locale">
                            @foreach ($locales as $locale)
                                <option value="{{ $locale->code }}" @selected(old('locale', config('saas.tenants.default_locale')) === $locale->code)>
                                    {{ $locale->name }} ({{ $locale->code }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="currency">@lang('saas::app.tenant.settings.currency')</label>

                        <select id="currency" name="currency">
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->code }}" @selected(old('currency', config('saas.tenants.default_currency')) === $currency->code)>
                                    {{ $currency->code }} &mdash; {{ $currency->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn ghost" href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.common.cancel')</a>

            <button type="submit" class="btn">@lang('saas::app.super-admin.tenants.create-submit')</button>
        </div>
    </form>
@endsection
