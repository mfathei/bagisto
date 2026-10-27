@extends('saas::layouts.master')

@section('title', $plan ? trans('saas::app.super-admin.plans.edit-title') : trans('saas::app.super-admin.plans.create-title'))

@section('content')
    @php
        $quotaFields = [
            ['key' => 'max_products', 'label' => trans('saas::app.super-admin.plans.products')],
            ['key' => 'max_categories', 'label' => trans('saas::app.super-admin.plans.categories')],
            ['key' => 'max_attributes', 'label' => trans('saas::app.super-admin.plans.attributes')],
            ['key' => 'max_attribute_families', 'label' => trans('saas::app.super-admin.plans.attribute-families')],
            ['key' => 'max_channels', 'label' => trans('saas::app.super-admin.plans.channels')],
            ['key' => 'monthly_orders', 'label' => trans('saas::app.super-admin.plans.orders')],
            ['key' => 'max_staff', 'label' => trans('saas::app.super-admin.plans.staff')],
            ['key' => 'storage_mb', 'label' => trans('saas::app.super-admin.plans.storage')],
        ];
    @endphp

    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.plans.index') }}">@lang('saas::app.super-admin.plans.title')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>{{ $plan ? trans('saas::app.super-admin.plans.edit-title') : trans('saas::app.super-admin.plans.create-title') }}</span>
            </div>

            <h1>{{ $plan ? trans('saas::app.super-admin.plans.edit-title') : trans('saas::app.super-admin.plans.create-title') }}</h1>

            <p>@lang('saas::app.super-admin.plans.form-subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.plans.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.common.back')
            </a>
        </div>
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
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.plans.general')</h2>

                    <p>@lang('saas::app.super-admin.plans.general-help')</p>
                </div>
            </div>

            <div class="card-body">
                <div class="form-grid">
                    <div class="field">
                        <label for="slug">@lang('saas::app.super-admin.plans.code')</label>

                        <input id="slug" name="slug" type="text" value="{{ old('slug', $plan?->slug) }}" required>

                        <span class="hint">@lang('saas::app.super-admin.plans.code-hint')</span>
                    </div>

                    <div class="field">
                        <label for="name">@lang('saas::app.common.name')</label>

                        <input id="name" name="name" type="text" value="{{ old('name', $plan?->name) }}" required>
                    </div>

                    <div class="field span-2">
                        <label for="description">@lang('saas::app.common.description')</label>

                        <textarea id="description" name="description" rows="3">{{ old('description', $plan?->description) }}</textarea>
                    </div>
                </div>
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
                <div class="form-grid">
                    @foreach ($quotaFields as $quota)
                        <div class="field">
                            <label for="limit_{{ $quota['key'] }}">{{ $quota['label'] }}</label>

                            <input
                                id="limit_{{ $quota['key'] }}"
                                name="limits[{{ $quota['key'] }}]"
                                type="number"
                                min="0"
                                placeholder="@lang('saas::app.super-admin.plans.unlimited')"
                                value="{{ old('limits.'.$quota['key'], $plan?->limit($quota['key'])) }}"
                            >
                        </div>
                    @endforeach

                    <div class="field">
                        <label for="premium_themes">@lang('saas::app.super-admin.plans.premium-themes')</label>

                        <input
                            id="premium_themes"
                            name="premium_themes"
                            type="number"
                            min="0"
                            placeholder="@lang('saas::app.super-admin.plans.unlimited')"
                            value="{{ old('premium_themes', $plan?->premium_themes ?? 0) }}"
                        >
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.plans.billing-amount')</h2>

                    <p>@lang('saas::app.super-admin.plans.billing-amount-help')</p>
                </div>
            </div>

            <div class="card-body">
                <div class="form-grid">
                    <div class="field">
                        <label for="price">@lang('saas::app.super-admin.plans.monthly-amount')</label>

                        <input id="price" name="price" type="number" step="0.01" min="0" value="{{ old('price', $plan?->price ?? '0.00') }}" required>
                    </div>

                    <div class="field">
                        <label for="yearly_price">@lang('saas::app.super-admin.plans.yearly-amount')</label>

                        <input id="yearly_price" name="yearly_price" type="number" step="0.01" min="0" value="{{ old('yearly_price', $plan?->yearly_price) }}">

                        <span class="hint">@lang('saas::app.super-admin.plans.yearly-amount-hint')</span>
                    </div>

                    <div class="field">
                        <label for="currency">@lang('saas::app.tenant.settings.currency')</label>

                        <input id="currency" name="currency" type="text" maxlength="3" value="{{ old('currency', $plan?->currency ?? config('saas.billing.currency')) }}" required>
                    </div>

                    <div class="field">
                        <label for="billing_interval">@lang('saas::app.super-admin.plans.interval')</label>

                        <select id="billing_interval" name="billing_interval">
                            @foreach (['monthly', 'quarterly', 'yearly'] as $interval)
                                <option value="{{ $interval }}" @selected(old('billing_interval', $plan?->billing_interval ?? 'monthly') === $interval)>
                                    {{ ucfirst($interval) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="trial_days">@lang('saas::app.super-admin.plans.trial-days')</label>

                        <input id="trial_days" name="trial_days" type="number" min="0" max="365" value="{{ old('trial_days', $plan?->trial_days ?? config('saas.registration.trial_days')) }}" required>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <div>
                    <h2>@lang('saas::app.super-admin.plans.offers')</h2>

                    <p>@lang('saas::app.super-admin.plans.offers-help')</p>
                </div>
            </div>

            <div class="card-body">
                <div class="form-grid">
                    <div class="field span-2">
                        <input type="hidden" name="offer_status" value="inactive">

                        <label class="switch">
                            <input
                                type="checkbox"
                                name="offer_status"
                                value="active"
                                @checked(old('offer_status', $plan?->offer_status) === 'active')
                            >

                            <span class="switch-track"></span>

                            <span>@lang('saas::app.super-admin.plans.offer-status')</span>
                        </label>
                    </div>

                    <div class="field">
                        <label for="offer_title">@lang('saas::app.super-admin.plans.offer-title')</label>

                        <input id="offer_title" name="offer_title" type="text" value="{{ old('offer_title', $plan?->offer_title) }}">
                    </div>

                    <div class="field">
                        <label for="offer_type">@lang('saas::app.super-admin.plans.offer-type')</label>

                        <select id="offer_type" name="offer_type">
                            @foreach (['percentage', 'fixed'] as $type)
                                <option value="{{ $type }}" @selected(old('offer_type', $plan?->offer_type ?? 'percentage') === $type)>
                                    {{ ucfirst($type) }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="field">
                        <label for="offer_discount">@lang('saas::app.super-admin.plans.offer-discount')</label>

                        <input id="offer_discount" name="offer_discount" type="number" step="0.01" min="0" value="{{ old('offer_discount', $plan?->offer_discount) }}">
                    </div>
                </div>
            </div>
        </div>

        <div class="grid cols-2">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.plans.visibility')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-grid">
                        <div class="field">
                            <label class="check">
                                <input
                                    name="is_active"
                                    type="checkbox"
                                    value="1"
                                    @checked(old('is_active', $plan?->is_active ?? true))
                                >

                                <span>@lang('saas::app.super-admin.plans.active')</span>
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

                                <span>@lang('saas::app.super-admin.plans.public')</span>
                            </label>
                        </div>

                        <div class="field">
                            <label for="sort_order">@lang('saas::app.super-admin.plans.sort-order')</label>

                            <input id="sort_order" name="sort_order" type="number" min="0" value="{{ old('sort_order', $plan?->sort_order ?? 0) }}">
                        </div>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.plans.features')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <div class="form-grid">
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

                                        <span>{{ $feature->label() }}</span>
                                    </label>
                                </div>
                            @endif
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn ghost" href="{{ route('saas.super-admin.plans.index') }}">@lang('saas::app.common.cancel')</a>

            <button type="submit" class="btn">@lang('saas::app.common.save')</button>
        </div>
    </form>
@endsection
