@extends('saas::layouts.master')

@section('title', $plan->name)

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $plan->name }}</h1>

            <p class="muted">{{ $plan->slug }}</p>
        </div>

        <div class="actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.plans.index') }}">
                @lang('saas::app.common.back')
            </a>

            <a class="btn" href="{{ route('saas.super-admin.plans.edit', $plan->getKey()) }}">
                @lang('saas::app.common.edit')
            </a>
        </div>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h2>@lang('saas::app.super-admin.plans.plan-preview')</h2>

            <p class="stat">{{ $plan->price }} {{ $plan->currency }}</p>

            <p class="muted">@lang('saas::app.super-admin.plans.per-interval', ['interval' => $plan->billing_interval])</p>

            <p>{{ $plan->description }}</p>

            <dl class="kv">
                <dt>@lang('saas::app.super-admin.plans.trial-days')</dt>
                <dd>{{ $plan->trial_days }}</dd>

                <dt>@lang('saas::app.super-admin.plans.active')</dt>
                <dd>{{ $plan->is_active ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</dd>

                <dt>@lang('saas::app.super-admin.plans.public')</dt>
                <dd>{{ $plan->is_public ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</dd>

                <dt>@lang('saas::app.super-admin.plans.sort-order')</dt>
                <dd>{{ $plan->sort_order }}</dd>
            </dl>
        </div>

        <div class="card">
            <h2>@lang('saas::app.super-admin.plans.limits')</h2>

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
@endsection
