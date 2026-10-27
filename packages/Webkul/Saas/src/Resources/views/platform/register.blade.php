@extends('saas::layouts.public')

@section('title', trans('saas::app.registration.title', ['platform' => $platformName]))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.registration.title', ['platform' => $platformName])</h1>

            <p>@lang('saas::app.registration.subtitle', ['days' => $trialDays])</p>
        </div>
    </div>

    <div class="card">
        <form method="POST" action="{{ route('saas.registration.store') }}">
            @csrf

            <h2>@lang('saas::app.registration.store-details')</h2>

            <div class="field">
                <label for="name">@lang('saas::app.registration.store-name')</label>

                <input id="name" type="text" name="name" maxlength="150" value="{{ old('name') }}" required autofocus>

                @error('name')
                    <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="slug">@lang('saas::app.registration.store-slug')</label>

                <input id="slug" type="text" name="slug" maxlength="30" value="{{ old('slug') }}" required>

                <p class="muted" style="margin: 6px 0 0;">@lang('saas::app.registration.store-slug-help')</p>

                @error('slug')
                    <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid cols-2">
                <div class="field">
                    <label for="plan_id">@lang('saas::app.common.plan')</label>

                    <select id="plan_id" name="plan_id" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->getKey() }}" @selected((string) old('plan_id', $selectedPlanId) === (string) $plan->getKey())>
                                {{ $plan->name }}
                                @if ((float) $plan->price > 0)
                                    &mdash; {{ number_format((float) $plan->price, 2) }} {{ $plan->currency }}/{{ $plan->billing_interval }}
                                @else
                                    &mdash; @lang('saas::app.registration.free')
                                @endif
                            </option>
                        @endforeach
                    </select>

                    @error('plan_id')
                        <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="currency">@lang('saas::app.registration.currency')</label>

                    <input id="currency" type="text" name="currency" maxlength="3" value="{{ old('currency', 'USD') }}">

                    @error('currency')
                        <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <h2 style="margin-top: 8px;">@lang('saas::app.registration.owner-details')</h2>

            <div class="field">
                <label for="owner_name">@lang('saas::app.registration.owner-name')</label>

                <input id="owner_name" type="text" name="owner_name" maxlength="150" value="{{ old('owner_name') }}" required>

                @error('owner_name')
                    <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div class="field">
                <label for="owner_email">@lang('saas::app.registration.owner-email')</label>

                <input id="owner_email" type="email" name="owner_email" maxlength="191" value="{{ old('owner_email') }}" required>

                @error('owner_email')
                    <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid cols-2">
                <div class="field">
                    <label for="owner_password">@lang('saas::app.registration.password')</label>

                    <input id="owner_password" type="password" name="owner_password" required autocomplete="new-password">

                    <p class="muted" style="margin: 6px 0 0;">@lang('saas::app.registration.password-help', ['min' => config('saas.registration.password_min_length', 12)])</p>

                    @error('owner_password')
                        <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="owner_password_confirmation">@lang('saas::app.registration.password-confirmation')</label>

                    <input id="owner_password_confirmation" type="password" name="owner_password_confirmation" required autocomplete="new-password">
                </div>
            </div>

            <div class="grid cols-2">
                <div class="field">
                    <label for="timezone">@lang('saas::app.registration.timezone')</label>

                    <input id="timezone" type="text" name="timezone" maxlength="64" value="{{ old('timezone', config('app.timezone')) }}">

                    @error('timezone')
                        <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="locale">@lang('saas::app.registration.locale')</label>

                    <input id="locale" type="text" name="locale" maxlength="12" value="{{ old('locale', config('saas.tenants.default_locale')) }}">

                    @error('locale')
                        <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="grid cols-2">
                <div class="field">
                    <label for="email">@lang('saas::app.registration.contact-email')</label>

                    <input id="email" type="email" name="email" maxlength="191" value="{{ old('email') }}">

                    @error('email')
                        <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="phone">@lang('saas::app.registration.phone')</label>

                    <input id="phone" type="text" name="phone" maxlength="40" value="{{ old('phone') }}">

                    @error('phone')
                        <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            <div class="field">
                <label class="check">
                    <input type="checkbox" name="terms" value="1" required @checked(old('terms'))>

                    <span>
                        @lang('saas::app.registration.terms')
                        <a href="{{ route('saas.marketing.terms', [], false) }}" target="_blank" rel="noopener">@lang('saas::marketing.legal.terms.title')</a>
                        &middot;
                        <a href="{{ route('saas.marketing.privacy', [], false) }}" target="_blank" rel="noopener">@lang('saas::marketing.legal.privacy.title')</a>
                    </span>
                </label>

                @error('terms')
                    <p class="alert error" style="margin: 8px 0 0;">{{ $message }}</p>
                @enderror
            </div>

            <button type="submit" class="btn">@lang('saas::app.registration.submit')</button>
        </form>
    </div>

    <p class="muted" style="margin-top: 16px;">
        @lang('saas::app.registration.have-account')

        <a href="{{ route('saas.marketing.merchant-login', [], false) }}">@lang('saas::app.registration.sign-in')</a>
    </p>
@endsection
