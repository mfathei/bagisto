@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.configure.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.nav.configuration')</span>
            </div>

            <h1>@lang('saas::app.super-admin.configure.title')</h1>

            <p>@lang('saas::app.super-admin.configure.subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn" href="{{ route('saas.super-admin.settings.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'settings'])

                @lang('saas::app.super-admin.settings.title')
            </a>
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.configure.identity')</h2>

                    <p>@lang('saas::app.super-admin.configure.identity-help')</p>
                </div>
            </div>

            <div class="card-body">
                <dl class="kv">
                    <dt>@lang('saas::app.super-admin.settings.platform-name')</dt>
                    <dd>{{ $platformName }}</dd>

                    <dt>@lang('saas::app.super-admin.settings.support-email')</dt>
                    <dd>{{ $supportEmail }}</dd>

                    <dt>@lang('saas::app.super-admin.settings.registration-enabled')</dt>
                    <dd>
                        @if ($registrationEnabled)
                            <span class="badge active">@lang('saas::app.common.yes')</span>
                        @else
                            <span class="badge neutral">@lang('saas::app.common.no')</span>
                        @endif
                    </dd>

                    <dt>@lang('saas::app.super-admin.settings.require-approval')</dt>
                    <dd>
                        @if ($requiresApproval)
                            <span class="badge active">@lang('saas::app.common.yes')</span>
                        @else
                            <span class="badge neutral">@lang('saas::app.common.no')</span>
                        @endif
                    </dd>

                    <dt>@lang('saas::app.super-admin.settings.trial-days')</dt>
                    <dd>{{ $defaultTrialDays }}</dd>
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.configure.shortcuts')</h2>

                    <p>@lang('saas::app.super-admin.configure.shortcuts-help')</p>
                </div>
            </div>

            <div class="card-body">
                <ul class="checklist">
                    <li>
                        <a class="strong" href="{{ route('saas.super-admin.domains.index') }}">
                            @lang('saas::app.super-admin.domains.title')
                        </a>

                        <span class="badge neutral no-dot">
                            {{ $domainCount }}

                            @if ($pendingDomains > 0)
                                &middot; {{ trans_choice('saas::app.super-admin.configure.pending', $pendingDomains, ['count' => $pendingDomains]) }}
                            @endif
                        </span>
                    </li>

                    <li>
                        <a class="strong" href="{{ route('saas.super-admin.provisioning.index') }}">
                            @lang('saas::app.super-admin.provisioning.title')
                        </a>

                        <span class="badge {{ $failedOperations > 0 ? 'failed' : 'active' }} no-dot">
                            {{ trans_choice('saas::app.super-admin.configure.failed', $failedOperations, ['count' => $failedOperations]) }}
                        </span>
                    </li>

                    <li>
                        <a class="strong" href="{{ route('saas.super-admin.audit.index') }}">
                            @lang('saas::app.super-admin.audit.title')
                        </a>

                        <span class="badge neutral no-dot">{{ $auditCount }}</span>
                    </li>

                    <li>
                        <a class="strong" href="{{ route('saas.super-admin.settings.index') }}">
                            @lang('saas::app.super-admin.settings.title')
                        </a>

                        <span class="badge info no-dot">@lang('saas::app.super-admin.configure.open')</span>
                    </li>
                </ul>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <div class="card-header">
            <div>
                <h2>@lang('saas::app.super-admin.configure.policies')</h2>

                <p>@lang('saas::app.super-admin.configure.policies-help')</p>
            </div>
        </div>

        <div class="card-body">
            <div class="grid cols-3">
                <div class="info-tile card">
                    <span class="info-label">@lang('saas::app.super-admin.settings.registration-enabled')</span>

                    <p class="info-value">
                        @if ($registrationEnabled)
                            @lang('saas::app.common.yes')
                        @else
                            @lang('saas::app.common.no')
                        @endif
                    </p>

                    <p class="info-foot">@lang('saas::app.super-admin.configure.registration-foot')</p>
                </div>

                <div class="info-tile card">
                    <span class="info-label">@lang('saas::app.super-admin.settings.require-approval')</span>

                    <p class="info-value">
                        @if ($requiresApproval)
                            @lang('saas::app.common.yes')
                        @else
                            @lang('saas::app.common.no')
                        @endif
                    </p>

                    <p class="info-foot">@lang('saas::app.super-admin.configure.approval-foot')</p>
                </div>

                <div class="info-tile card">
                    <span class="info-label">@lang('saas::app.super-admin.settings.trial-days')</span>

                    <p class="info-value">{{ $defaultTrialDays }}</p>

                    <p class="info-foot">@lang('saas::app.super-admin.configure.trial-foot')</p>
                </div>
            </div>
        </div>
    </div>
@endsection
