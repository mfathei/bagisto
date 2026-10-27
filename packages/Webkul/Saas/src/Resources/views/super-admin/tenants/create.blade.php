@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.tenants.create-title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.tenants.create-title')</h1>

            <p>@lang('saas::app.super-admin.tenants.create-subtitle')</p>
        </div>

        <a class="btn secondary" href="{{ route('saas.super-admin.tenants.index') }}">
            @lang('saas::app.common.back')
        </a>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('saas.super-admin.tenants.store') }}">
            @csrf

            <div class="grid cols-2">
                <div class="field">
                    <label for="name">@lang('saas::app.common.name')</label>

                    <input id="name" name="name" type="text" value="{{ old('name') }}" required>
                </div>

                <div class="field">
                    <label for="slug">@lang('saas::app.super-admin.tenants.slug')</label>

                    <input id="slug" name="slug" type="text" value="{{ old('slug') }}" required>

                    <p class="muted">@lang('saas::app.super-admin.tenants.slug-hint', ['suffix' => config('saas.domains.suffix')])</p>
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
                            >{{ $plan->name }} &mdash; {{ $plan->price }} {{ $plan->currency }}</option>
                        @endforeach
                    </select>
                </div>

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

                <div class="field">
                    <label for="timezone">@lang('saas::app.tenant.settings.timezone')</label>

                    <input id="timezone" name="timezone" type="text" value="{{ old('timezone', config('saas.tenants.default_timezone')) }}">
                </div>

                <div class="field">
                    <label for="locale">@lang('saas::app.tenant.settings.locale')</label>

                    <input id="locale" name="locale" type="text" value="{{ old('locale', config('saas.tenants.default_locale')) }}">
                </div>

                <div class="field">
                    <label for="currency">@lang('saas::app.tenant.settings.currency')</label>

                    <input id="currency" name="currency" type="text" value="{{ old('currency', config('saas.tenants.default_currency')) }}" maxlength="3">
                </div>
            </div>

            <button type="submit" class="btn">@lang('saas::app.super-admin.tenants.create-submit')</button>
        </form>
    </div>
@endsection
