@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.settings.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.configure.index') }}">@lang('saas::app.super-admin.nav.configuration')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.settings.title')</span>
            </div>

            <h1>@lang('saas::app.super-admin.settings.title')</h1>

            <p>@lang('saas::app.super-admin.settings.subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.configure.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.super-admin.nav.configuration')
            </a>
        </div>
    </div>

    <form method="POST" action="{{ route('saas.super-admin.settings.update') }}">
        @csrf
        @method('PUT')

        <div class="grid cols-2 start">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.settings.identity')</h2>

                        <p>@lang('saas::app.super-admin.configure.identity-help')</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label for="platform_name">@lang('saas::app.super-admin.settings.platform-name')</label>

                            <input
                                id="platform_name"
                                type="text"
                                name="platform_name"
                                maxlength="120"
                                value="{{ old('platform_name', $settings['platform_name'] ?? $defaults['platform_name']) }}"
                                required
                            >

                            @error('platform_name')
                                <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field span-2">
                            <label for="support_email">@lang('saas::app.super-admin.settings.support-email')</label>

                            <input
                                id="support_email"
                                type="email"
                                name="support_email"
                                maxlength="191"
                                value="{{ old('support_email', $settings['support_email'] ?? $defaults['support_email']) }}"
                                required
                            >

                            @error('support_email')
                                <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field span-2">
                            <label for="maintenance_message">@lang('saas::app.super-admin.settings.maintenance-message')</label>

                            <textarea id="maintenance_message" name="maintenance_message" rows="3" maxlength="500">{{ old('maintenance_message', $settings['maintenance_message'] ?? $defaults['maintenance_message']) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.settings.registration')</h2>

                        <p>@lang('saas::app.super-admin.configure.policies-help')</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-grid">
                        <div class="field span-2">
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    name="registration_enabled"
                                    value="1"
                                    @checked((bool) old('registration_enabled', $settings['registration_enabled'] ?? $defaults['registration_enabled']))
                                >

                                <span class="switch-track"></span>

                                <span>@lang('saas::app.super-admin.settings.registration-enabled')</span>
                            </label>
                        </div>

                        <div class="field span-2">
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    name="require_approval"
                                    value="1"
                                    @checked((bool) old('require_approval', $settings['require_approval'] ?? $defaults['require_approval']))
                                >

                                <span class="switch-track"></span>

                                <span>@lang('saas::app.super-admin.settings.require-approval')</span>
                            </label>
                        </div>

                        <div class="field span-2">
                            <label class="switch">
                                <input
                                    type="checkbox"
                                    name="require_email_verification"
                                    value="1"
                                    @checked((bool) old('require_email_verification', $settings['require_email_verification'] ?? $defaults['require_email_verification']))
                                >

                                <span class="switch-track"></span>

                                <span>@lang('saas::app.super-admin.settings.require-verification')</span>
                            </label>
                        </div>

                        <div class="field">
                            <label for="default_trial_days">@lang('saas::app.super-admin.settings.trial-days')</label>

                            <input
                                id="default_trial_days"
                                type="number"
                                name="default_trial_days"
                                min="0"
                                max="365"
                                value="{{ old('default_trial_days', $settings['default_trial_days'] ?? $defaults['default_trial_days']) }}"
                                required
                            >

                            @error('default_trial_days')
                                <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="default_plan_slug">@lang('saas::app.super-admin.settings.default-plan')</label>

                            <input
                                id="default_plan_slug"
                                type="text"
                                name="default_plan_slug"
                                maxlength="60"
                                value="{{ old('default_plan_slug', $settings['default_plan_slug'] ?? $defaults['default_plan_slug']) }}"
                                required
                            >

                            @error('default_plan_slug')
                                <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="grid cols-2 start" style="margin-top: 16px;">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.settings.data-retention')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <div class="field" style="margin: 0;">
                        <label for="data_retention_days">@lang('saas::app.super-admin.settings.retention-days')</label>

                        <input
                            id="data_retention_days"
                            type="number"
                            name="data_retention_days"
                            min="1"
                            max="3650"
                            value="{{ old('data_retention_days', $settings['data_retention_days'] ?? $defaults['data_retention_days']) }}"
                            required
                        >

                        @error('data_retention_days')
                            <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                        @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.settings.defaults')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-grid">
                        <div class="field">
                            <label for="default_product_limit">@lang('saas::app.super-admin.settings.product-limit')</label>

                            <input
                                id="default_product_limit"
                                type="number"
                                name="default_product_limit"
                                min="0"
                                max="1000000"
                                value="{{ old('default_product_limit', $settings['default_product_limit'] ?? $defaults['default_product_limit']) }}"
                                required
                            >

                            @error('default_product_limit')
                                <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                            @enderror
                        </div>

                        <div class="field">
                            <label for="default_staff_limit">@lang('saas::app.super-admin.settings.staff-limit')</label>

                            <input
                                id="default_staff_limit"
                                type="number"
                                name="default_staff_limit"
                                min="0"
                                max="10000"
                                value="{{ old('default_staff_limit', $settings['default_staff_limit'] ?? $defaults['default_staff_limit']) }}"
                                required
                            >

                            @error('default_staff_limit')
                                <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                            @enderror
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn ghost" href="{{ route('saas.super-admin.configure.index') }}">
                @lang('saas::app.common.cancel')
            </a>

            <button type="submit" class="btn">@lang('saas::app.super-admin.settings.save')</button>
        </div>
    </form>
@endsection
