@extends('saas::layouts.master')

@section('title', $plan->name)

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.plans.index') }}">@lang('saas::app.super-admin.plans.title')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>{{ $plan->name }}</span>
            </div>

            <h1>
                {{ $plan->name }}

                <span class="badge {{ $plan->is_active ? 'active' : 'inactive' }}">
                    {{ $plan->is_active ? trans('saas::app.super-admin.plans.active') : trans('saas::app.super-admin.plans.inactive') }}
                </span>
            </h1>

            <p><span class="badge">{{ $plan->slug }}</span></p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.plans.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.common.back')
            </a>

            <a class="btn" href="{{ route('saas.super-admin.plans.edit', $plan->getKey()) }}">
                @include('saas::layouts.partials.icon', ['name' => 'edit'])

                @lang('saas::app.common.edit')
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-value">{{ number_format((float) $plan->price, 2) }} {{ $plan->currency }}</span>

                <span class="stat-label">@lang('saas::app.super-admin.plans.monthly-amount')</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-value">{{ number_format($plan->yearlyAmount(), 2) }} {{ $plan->currency }}</span>

                <span class="stat-label">@lang('saas::app.super-admin.plans.yearly-amount')</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-value">{{ $plan->trial_days }}</span>

                <span class="stat-label">@lang('saas::app.super-admin.plans.trial-days')</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-value">{{ ucfirst($plan->billing_interval) }}</span>

                <span class="stat-label">@lang('saas::app.super-admin.plans.interval')</span>
            </div>
        </div>
    </div>

    <div class="grid cols-2" style="margin-top: 16px;">
        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.plans.plan-preview')</h2>

                    <p>@lang('saas::app.super-admin.plans.general-help')</p>
                </div>
            </div>

            <div class="card-body">
                @if ($plan->description)
                    <p style="margin-bottom: 14px;">{{ $plan->description }}</p>
                @endif

                <dl class="kv">
                    <dt>@lang('saas::app.common.status')</dt>
                    <dd>
                        <span class="badge {{ $plan->is_active ? 'active' : 'inactive' }}">
                            {{ $plan->is_active ? trans('saas::app.super-admin.plans.active') : trans('saas::app.super-admin.plans.inactive') }}
                        </span>
                    </dd>

                    <dt>@lang('saas::app.super-admin.plans.public')</dt>
                    <dd>{{ $plan->is_public ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</dd>

                    <dt>@lang('saas::app.super-admin.plans.sort-order')</dt>
                    <dd>{{ $plan->sort_order }}</dd>

                    <dt>@lang('saas::app.super-admin.plans.trial-days')</dt>
                    <dd>{{ $plan->trial_days }}</dd>

                    @if ($plan->offer_status === 'active')
                        <dt>@lang('saas::app.super-admin.plans.offer')</dt>
                        <dd>
                            <span class="badge warning">{{ $plan->offer_title ?: trans('saas::app.super-admin.plans.offers') }}</span>

                            @if ($plan->offer_discount)
                                <span class="muted">
                                    {{ $plan->offer_type === 'percentage' ? $plan->offer_discount.'%' : number_format((float) $plan->offer_discount, 2).' '.$plan->currency }}
                                </span>
                            @endif
                        </dd>
                    @endif
                </dl>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.plans.limits')</h2>

                    <p>@lang('saas::app.super-admin.plans.limits-hint')</p>
                </div>
            </div>

            <div class="card-body">
                <ul class="checklist">
                    @foreach ($features as $feature)
                        <li>
                            <span>{{ $feature->label() }}</span>

                            @if ($feature->isQuota())
                                <strong>
                                    {{ $plan->limit($feature) === null ? trans('saas::app.super-admin.plans.unlimited') : $plan->limit($feature) }}
                                </strong>
                            @else
                                <span class="badge {{ $plan->includes($feature) ? 'active' : '' }}">
                                    {{ $plan->includes($feature) ? trans('saas::app.super-admin.plans.included') : trans('saas::app.super-admin.plans.not-included') }}
                                </span>
                            @endif
                        </li>
                    @endforeach
                </ul>
            </div>
        </div>
    </div>
@endsection
