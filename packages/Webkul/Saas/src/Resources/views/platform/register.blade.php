@extends('saas::layouts.public')

@section('title', trans('saas::app.registration.title', ['platform' => $platformName]))
@section('body-class', 'registration-body')
@section('container-class', 'registration-container')

@section('nav')
    <a class="registration-signin" href="{{ route('saas.marketing.merchant-login', [], false) }}">
        @lang('saas::app.registration.sign-in')
    </a>
@endsection

@section('content')
    <div class="registration-heading">
        <div>
            <h1>@lang('saas::app.registration.title', ['platform' => $platformName])</h1>

            <p>@lang('saas::app.registration.subtitle', ['days' => $trialDays])</p>
        </div>
    </div>

    <div class="registration-grid">
        <div class="card registration-card">
            <form method="POST" action="{{ route('saas.registration.store') }}">
                @csrf

                <div class="registration-form-heading">
                    <span class="registration-step" aria-hidden="true">01</span>
                    <h2>@lang('saas::app.registration.store-details')</h2>
                </div>

                <div class="field">
                    <label for="name">@lang('saas::app.registration.store-name')</label>

                    <input id="name" type="text" name="name" maxlength="150" value="{{ old('name') }}" autocomplete="organization" required autofocus>

                    @error('name')
                        <p class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="slug">@lang('saas::app.registration.store-slug')</label>

                    <input id="slug" type="text" name="slug" maxlength="30" value="{{ old('slug') }}" autocomplete="off" autocapitalize="none" spellcheck="false" required>

                    <p class="field-hint">@lang('saas::app.registration.store-slug-help')</p>

                    @error('slug')
                        <p class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid cols-2 registration-fields">
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
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="currency">@lang('saas::app.registration.currency')</label>

                        <input id="currency" type="text" name="currency" maxlength="3" value="{{ old('currency', 'USD') }}">

                        @error('currency')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="registration-form-heading registration-form-heading-spaced">
                    <span class="registration-step" aria-hidden="true">02</span>
                    <h2>@lang('saas::app.registration.owner-details')</h2>
                </div>

                <div class="field">
                    <label for="owner_name">@lang('saas::app.registration.owner-name')</label>

                    <input id="owner_name" type="text" name="owner_name" maxlength="150" value="{{ old('owner_name') }}" autocomplete="name" required>

                    @error('owner_name')
                        <p class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="field">
                    <label for="owner_email">@lang('saas::app.registration.owner-email')</label>

                    <input id="owner_email" type="email" name="owner_email" maxlength="191" value="{{ old('owner_email') }}" autocomplete="email" required>

                    @error('owner_email')
                        <p class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid cols-2 registration-fields">
                    <div class="field">
                        <label for="owner_password">@lang('saas::app.registration.password')</label>

                        <input id="owner_password" type="password" name="owner_password" required autocomplete="new-password">

                        <p class="field-hint">@lang('saas::app.registration.password-help', ['min' => config('saas.registration.password_min_length', 12)])</p>

                        @error('owner_password')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="owner_password_confirmation">@lang('saas::app.registration.password-confirmation')</label>

                        <input id="owner_password_confirmation" type="password" name="owner_password_confirmation" required autocomplete="new-password">
                    </div>
                </div>

                <div class="grid cols-2 registration-fields">
                    <div class="field">
                        <label for="timezone">@lang('saas::app.registration.timezone')</label>

                        <input id="timezone" type="text" name="timezone" maxlength="64" value="{{ old('timezone', config('app.timezone')) }}">

                        @error('timezone')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="locale">@lang('saas::app.registration.locale')</label>

                        <input id="locale" type="text" name="locale" maxlength="12" value="{{ old('locale', config('saas.tenants.default_locale')) }}">

                        @error('locale')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="grid cols-2 registration-fields">
                    <div class="field">
                        <label for="email">@lang('saas::app.registration.contact-email')</label>

                        <input id="email" type="email" name="email" maxlength="191" value="{{ old('email') }}">

                        @error('email')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="phone">@lang('saas::app.registration.phone')</label>

                        <input id="phone" type="text" name="phone" maxlength="40" value="{{ old('phone') }}">

                        @error('phone')
                            <p class="field-error" role="alert">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div class="field">
                    <label class="check">
                        <input id="terms" type="checkbox" name="terms" value="1" required @checked(old('terms'))>

                        <span>
                            @lang('saas::app.registration.terms')
                            <a href="{{ route('saas.marketing.terms', [], false) }}" target="_blank" rel="noopener">@lang('saas::marketing.legal.terms.title')</a>
                            &middot;
                            <a href="{{ route('saas.marketing.privacy', [], false) }}" target="_blank" rel="noopener">@lang('saas::marketing.legal.privacy.title')</a>
                        </span>
                    </label>

                    @error('terms')
                        <p class="field-error" role="alert">{{ $message }}</p>
                    @enderror
                </div>

                <button type="submit" class="btn registration-submit">@lang('saas::app.registration.submit')</button>
            </form>
        </div>

        <aside class="registration-aside">
            <div class="registration-aside-mark" aria-hidden="true">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3 3 8l9 5 9-5-9-5Z" />
                    <path d="m3 13 9 5 9-5" />
                    <path d="m3 18 9 5 9-5" />
                </svg>
            </div>

            <p class="registration-aside-label">@lang('saas::app.common.plan')</p>

            <h2 id="registration-plan-summary">{{ $plans->firstWhere('id', $selectedPlanId)?->name ?? trans('saas::app.common.plan') }}</h2>

            <p class="registration-aside-trial">@lang('saas::app.registration.subtitle', ['days' => $trialDays])</p>

            <a href="{{ route('saas.marketing.pricing', [], false) }}" class="registration-pricing-link">
                @lang('saas::marketing.common.view_pricing')
                <span aria-hidden="true">→</span>
            </a>
        </aside>
    </div>

    <p class="registration-signin-mobile">
        @lang('saas::app.registration.have-account')

        <a href="{{ route('saas.marketing.merchant-login', [], false) }}">@lang('saas::app.registration.sign-in')</a>
    </p>

    @push('scripts')
        <script>
            const planSelect = document.getElementById('plan_id');
            const planSummary = document.getElementById('registration-plan-summary');

            if (planSelect && planSummary) {
                const updatePlanSummary = () => {
                    planSummary.textContent = planSelect.options[planSelect.selectedIndex].textContent.trim();
                };

                planSelect.addEventListener('change', updatePlanSummary);

                updatePlanSummary();
            }
        </script>
    @endpush
@endsection
