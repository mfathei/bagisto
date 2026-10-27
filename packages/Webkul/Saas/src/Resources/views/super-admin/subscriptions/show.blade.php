@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.subscriptions.show-title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.subscriptions.index') }}">@lang('saas::app.super-admin.nav.purchased-plans')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>#{{ $subscription->getKey() }}</span>
            </div>

            <h1>
                {{ $subscription->tenant?->name ?? '#'.$subscription->tenant_id }}

                <span class="badge {{ $subscription->status->value }}">{{ $subscription->status->value }}</span>
            </h1>

            <p>@lang('saas::app.super-admin.subscriptions.details')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.subscriptions.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.common.back')
            </a>

            @if ($subscription->tenant)
                <a class="btn" href="{{ route('saas.super-admin.tenants.show', $subscription->tenant_id) }}">
                    @include('saas::layouts.partials.icon', ['name' => 'view'])

                    @lang('saas::app.common.tenant')
                </a>
            @endif
        </div>
    </div>

    <div class="detail-grid">
        <div class="stack">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.subscriptions.details')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="kv">
                        <dt>@lang('saas::app.common.tenant')</dt>
                        <dd>
                            <a href="{{ route('saas.super-admin.tenants.show', $subscription->tenant_id) }}">
                                {{ $subscription->tenant?->name ?? '#'.$subscription->tenant_id }}
                            </a>
                        </dd>

                        <dt>@lang('saas::app.common.plan')</dt>
                        <dd>
                            @if ($subscription->plan)
                                <a href="{{ route('saas.super-admin.plans.show', $subscription->plan_id) }}">
                                    {{ $subscription->plan->name }}
                                </a>
                            @else
                                {{ trans('saas::app.common.none') }}
                            @endif
                        </dd>

                        <dt>@lang('saas::app.super-admin.plans.price')</dt>
                        <dd>
                            @if ($subscription->plan)
                                {{ number_format((float) $subscription->plan->price, 2) }} {{ $subscription->plan->currency }}
                            @else
                                {{ trans('saas::app.common.none') }}
                            @endif
                        </dd>

                        <dt>@lang('saas::app.super-admin.subscriptions.starts')</dt>
                        <dd>{{ $subscription->starts_at?->format('M d, Y') ?? trans('saas::app.common.none') }}</dd>

                        <dt>@lang('saas::app.super-admin.subscriptions.trial-ends')</dt>
                        <dd>{{ $subscription->trial_ends_at?->format('M d, Y H:i') ?? trans('saas::app.common.none') }}</dd>

                        <dt>@lang('saas::app.super-admin.subscriptions.period-end')</dt>
                        <dd>{{ $subscription->current_period_end?->format('M d, Y H:i') ?? trans('saas::app.common.none') }}</dd>

                        <dt>@lang('saas::app.super-admin.subscriptions.cancel-at-period-end')</dt>
                        <dd>{{ $subscription->cancel_at_period_end ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</dd>

                        <dt>@lang('saas::app.super-admin.subscriptions.grace-ends')</dt>
                        <dd>{{ $subscription->grace_ends_at?->format('M d, Y H:i') ?? trans('saas::app.common.none') }}</dd>
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.subscriptions.billing-records')</h2>
                    </div>
                </div>

                @if ($subscription->billingRecords->isEmpty())
                    <div class="card-body">
                        <div class="empty-block">
                            @include('saas::layouts.partials.icon', ['name' => 'invoices'])

                            <p>@lang('saas::app.super-admin.subscriptions.no-records')</p>
                        </div>
                    </div>
                @else
                    <div style="overflow-x: auto;">
                        <table class="dg-table">
                            <thead>
                                <tr>
                                    <th>@lang('saas::app.super-admin.billing.number')</th>
                                    <th>@lang('saas::app.common.status')</th>
                                    <th>@lang('saas::app.super-admin.billing.total')</th>
                                    <th>@lang('saas::app.super-admin.billing.paid')</th>
                                    <th>@lang('saas::app.super-admin.billing.due')</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($subscription->billingRecords->sortByDesc('id') as $invoice)
                                    <tr>
                                        <td data-label="@lang('saas::app.super-admin.billing.number')">
                                            <a class="cell-title" href="{{ route('saas.super-admin.billing.show', $invoice->getKey()) }}">
                                                {{ $invoice->number }}
                                            </a>
                                        </td>

                                        <td data-label="@lang('saas::app.common.status')">
                                            <span class="badge {{ $invoice->status->value }}">{{ $invoice->status->value }}</span>
                                        </td>

                                        <td data-label="@lang('saas::app.super-admin.billing.total')" class="nowrap">
                                            {{ number_format((float) $invoice->total, 2) }} {{ $invoice->currency }}
                                        </td>

                                        <td data-label="@lang('saas::app.super-admin.billing.paid')" class="nowrap">
                                            {{ number_format((float) $invoice->amount_paid, 2) }} {{ $invoice->currency }}
                                        </td>

                                        <td data-label="@lang('saas::app.super-admin.billing.due')" class="nowrap">
                                            {{ $invoice->due_at?->format('M d, Y') ?? trans('saas::app.common.none') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>

        <div class="stack">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.subscriptions.extend-trial')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('saas.super-admin.subscriptions.extend_trial', $subscription->getKey()) }}">
                        @csrf

                        <div class="field">
                            <label for="days">@lang('saas::app.super-admin.subscriptions.days')</label>

                            <input id="days" name="days" type="number" min="1" max="365" required>
                        </div>

                        <div class="field">
                            <label for="reason">@lang('saas::app.common.reason')</label>

                            <input id="reason" name="reason" type="text" required>
                        </div>

                        <button type="submit" class="btn small">@lang('saas::app.super-admin.subscriptions.extend-trial')</button>
                    </form>

                    <div class="form-actions start" style="margin-top: 18px;">
                        <form method="POST" action="{{ route('saas.super-admin.subscriptions.change_plan', $subscription->getKey()) }}">
                            @csrf

                            <div class="field" style="margin-bottom: 10px;">
                                <label for="plan_id">@lang('saas::app.super-admin.subscriptions.change-plan')</label>

                                <select id="plan_id" name="plan_id">
                                    @foreach ($plans as $plan)
                                        <option
                                            value="{{ $plan->getKey() }}"
                                            @selected((int) $subscription->plan_id === (int) $plan->getKey())
                                        >{{ $plan->name }} &mdash; {{ number_format((float) $plan->price, 2) }} {{ $plan->currency }}</option>
                                    @endforeach
                                </select>
                            </div>

                            <button type="submit" class="btn small secondary">@lang('saas::app.super-admin.subscriptions.change-plan')</button>
                        </form>
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.subscriptions.reactivate')</h2>

                        <p>@lang('saas::app.super-admin.subscriptions.reactivate-hint')</p>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('saas.super-admin.subscriptions.reactivate', $subscription->getKey()) }}">
                        @csrf

                        <button type="submit" class="btn small">@lang('saas::app.super-admin.subscriptions.reactivate')</button>
                    </form>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.subscriptions.cancel')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <form method="POST" action="{{ route('saas.super-admin.subscriptions.cancel', $subscription->getKey()) }}">
                        @csrf

                        <div class="field">
                            <label for="cancel_reason">@lang('saas::app.common.reason')</label>

                            <input id="cancel_reason" name="reason" type="text">
                        </div>

                        <div class="field">
                            <label class="check">
                                <input name="immediate" type="checkbox" value="1">

                                <span>@lang('saas::app.super-admin.subscriptions.immediate')</span>
                            </label>
                        </div>

                        <button type="submit" class="btn small danger">@lang('saas::app.super-admin.subscriptions.cancel')</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
