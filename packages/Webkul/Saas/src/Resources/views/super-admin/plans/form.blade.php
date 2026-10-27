@extends('saas::layouts.master')

@section('title', $plan ? trans('saas::app.super-admin.plans.edit-title') : trans('saas::app.super-admin.plans.create-title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $plan ? trans('saas::app.super-admin.plans.edit-title') : trans('saas::app.super-admin.plans.create-title') }}</h1>
        </div>

        <a class="btn secondary" href="{{ route('saas.super-admin.plans.index') }}">
            @lang('saas::app.common.back')
        </a>
    </div>

    <form
        method="POST"
        action="{{ $plan ? route('saas.super-admin.plans.update', $plan->getKey()) : route('saas.super-admin.plans.store') }}"
    >
        @csrf

        @if ($plan)
            @method('PUT')
        @endif

        <div class="card">
            <div class="grid cols-2">
                <div class="field">
                    <label for="name">@lang('saas::app.common.name')</label>

                    <input id="name" name="name" type="text" value="{{ old('name', $plan?->name) }}" required>
                </div>

                <div class="field">
                    <label for="slug">@lang('saas::app.super-admin.plans.slug')</label>

                    <input id="slug" name="slug" type="text" value="{{ old('slug', $plan?->slug) }}" required>
                </div>

                <div class="field">
                    <label for="price">@lang('saas::app.super-admin.plans.price')</label>

                    <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $plan?->price ?? '0.00') }}" required>
                </div>

                <div class="field">
                    <label for="currency">@lang('saas::app.tenant.settings.currency')</label>

                    <input id="currency" name="currency" type="text" maxlength="3" value="{{ old('currency', $plan?->currency ?? config('saas.billing.currency')) }}" required>
                </div>

                <div class="field">
                    <label for="billing_interval">@lang('saas::app.super-admin.plans.interval')</label>

                    <select id="billing_interval" name="billing_interval">
                        @foreach (['monthly', 'quarterly', 'yearly'] as $interval)
                            <option
                                value="{{ $interval }}"
                                @selected(old('billing_interval', $plan?->billing_interval ?? 'monthly') === $interval)
                            >{{ $interval }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="field">
                    <label for="trial_days">@lang('saas::app.super-admin.plans.trial-days')</label>

                    <input id="trial_days" name="trial_days" type="number" min="0" max="365" value="{{ old('trial_days', $plan?->trial_days ?? config('saas.registration.trial_days')) }}" required>
                </div>

                <div class="field">
                    <label for="sort_order">@lang('saas::app.super-admin.plans.sort-order')</label>

                    <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}">
                </div>
            </div>

            <div class="field">
                <label for="description">@lang('saas::app.common.description')</label>

                <textarea id="description" name="description" rows="3">{{ old('description', $plan?->description) }}</textarea>
            </div>

            <div class="grid cols-2">
                <div class="field">
                    <label class="check">
                        <input
                            name="is_active"
                            type="checkbox"
                            value="1"
                            @checked(old('is_active', $plan?->is_active ?? true))
                        >

                        @lang('saas::app.super-admin.plans.active')
                    </label>
                </div>

                <div class="field">
                    <label class="check">
                        <input
                            name="is_public"
                            type="checkbox"
                            value="1"
                            @checked(old('is_public', $plan?->is_public ?? true))
                        >

                        @lang('saas::app.super-admin.plans.public')
                    </label>
                </div>
            </div>
        </div>

        <div class="card" style="margin-top: 16px;">
            <h2>@lang('saas::app.super-admin.plans.limits')</h2>

            <p class="muted">@lang('saas::app.super-admin.plans.limits-hint')</p>

            <div class="grid cols-2">
                @foreach ($features as $feature)
                    @if ($feature->isQuota())
                        <div class="field">
                            <label for="limit_{{ $feature->value }}">{{ $feature->label() }}</label>

                            <input
                                id="limit_{{ $feature->value }}"
                                name="limits[{{ $feature->value }}]"
                                type="number"
                                min="0"
                                placeholder="@lang('saas::app.super-admin.plans.unlimited')"
                                value="{{ old('limits.'.$feature->value, $plan?->limit($feature)) }}"
                            >
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="card" style="margin-top: 16px;">
            <h2>@lang('saas::app.super-admin.plans.features')</h2>

            <div class="grid cols-3">
                @foreach ($features as $feature)
                    @if (! $feature->isQuota())
                        <div class="field">
                            <label class="check">
                                <input
                                    name="features[{{ $feature->value }}]"
                                    type="checkbox"
                                    value="1"
                                    @checked(old('features.'.$feature->value, $plan?->includes($feature)))
                                >

                                {{ $feature->label() }}
                            </label>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <div class="actions" style="margin-top: 16px;">
            <button type="submit" class="btn">@lang('saas::app.common.save')</button>
        </div>
    </form>
@endsection
