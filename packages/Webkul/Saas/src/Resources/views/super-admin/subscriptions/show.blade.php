@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.subscriptions.show-title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.subscriptions.show-title')</h1>

            <p class="muted">
                {{ $subscription->tenant?->name }}
                &middot;
                <span class="badge {{ $subscription->status->value }}">{{ $subscription->status->value }}</span>
            </p>
        </div>

        <a class="btn secondary" href="{{ route('saas.super-admin.subscriptions.index') }}">
            @lang('saas::app.common.back')
        </a>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h2>@lang('saas::app.super-admin.subscriptions.details')</h2>

            <dl class="kv">
                <dt>@lang('saas::app.common.tenant')</dt>
                <dd>
                    <a href="{{ route('saas.super-admin.tenants.show', $subscription->tenant_id) }}">
                        {{ $subscription->tenant?->name ?? '#'.$subscription->tenant_id }}
                    </a>
                </dd>

                <dt>@lang('saas::app.common.plan')</dt>
                <dd>{{ $subscription->plan?->name ?? trans('saas::app.common.none') }}</dd>

                <dt>@lang('saas::app.super-admin.plans.price')</dt>
                <dd>
                    @if ($subscription->plan)
                        {{ $subscription->plan->price }} {{ $subscription->plan->currency }}
                    @else
                        {{ trans('saas::app.common.none') }}
                    @endif
                </dd>

                <dt>@lang('saas::app.super-admin.subscriptions.starts')</dt>
                <dd>{{ $subscription->starts_at?->format('Y-m-d') ?? trans('saas::app.common.none') }}</dd>

                <dt>@lang('saas::app.super-admin.subscriptions.trial-ends')</dt>
                <dd>{{ $subscription->trial_ends_at?->format('Y-m-d H:i') ?? trans('saas::app.common.none') }}</dd>

                <dt>@lang('saas::app.super-admin.subscriptions.period-end')</dt>
                <dd>{{ $subscription->current_period_end?->format('Y-m-d H:i') ?? trans('saas::app.common.none') }}</dd>

                <dt>@lang('saas::app.super-admin.subscriptions.cancel-at-period-end')</dt>
                <dd>{{ $subscription->cancel_at_period_end ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</dd>

                <dt>@lang('saas::app.super-admin.subscriptions.grace-ends')</dt>
                <dd>{{ $subscription->grace_ends_at?->format('Y-m-d H:i') ?? trans('saas::app.common.none') }}</dd>
            </dl>
        </div>

        <div class="card">
            <h2>@lang('saas::app.super-admin.subscriptions.extend-trial')</h2>

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

                <button type="submit" class="btn">@lang('saas::app.super-admin.subscriptions.extend-trial')</button>
            </form>

            <h2 style="margin-top: 22px;">@lang('saas::app.super-admin.subscriptions.change-plan')</h2>

            <form method="POST" action="{{ route('saas.super-admin.subscriptions.change_plan', $subscription->getKey()) }}">
                @csrf

                <div class="field">
                    <label for="plan_id">@lang('saas::app.common.plan')</label>

                    <select id="plan_id" name="plan_id">
                        @foreach ($plans as $plan)
                            <option
                                value="{{ $plan->getKey() }}"
                                @selected((int) $subscription->plan_id === (int) $plan->getKey())
                            >{{ $plan->name }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="btn small">@lang('saas::app.super-admin.subscriptions.change-plan')</button>
            </form>
        </div>
    </div>

    <div class="grid cols-2" style="margin-top: 16px;">
        <div class="card">
            <h2>@lang('saas::app.super-admin.subscriptions.cancel')</h2>

            <form method="POST" action="{{ route('saas.super-admin.subscriptions.cancel', $subscription->getKey()) }}">
                @csrf

                <div class="field">
                    <label for="cancel_reason">@lang('saas::app.common.reason')</label>

                    <input id="cancel_reason" name="reason" type="text">
                </div>

                <div class="field">
                    <label class="check">
                        <input name="immediate" type="checkbox" value="1">

                        @lang('saas::app.super-admin.subscriptions.immediate')
                    </label>
                </div>

                <button type="submit" class="btn small danger">@lang('saas::app.super-admin.subscriptions.cancel')</button>
            </form>
        </div>

        <div class="card">
            <h2>@lang('saas::app.super-admin.subscriptions.reactivate')</h2>

            <p class="muted">@lang('saas::app.super-admin.subscriptions.reactivate-hint')</p>

            <form method="POST" action="{{ route('saas.super-admin.subscriptions.reactivate', $subscription->getKey()) }}">
                @csrf

                <button type="submit" class="btn small">@lang('saas::app.super-admin.subscriptions.reactivate')</button>
            </form>
        </div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.subscriptions.billing-records')</h2>

        @if ($subscription->billingRecords->isEmpty())
            <p class="muted">@lang('saas::app.super-admin.subscriptions.no-records')</p>
        @else
            <div class="table-wrap">
                <table>
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
                                <td>
                                    <a href="{{ route('saas.super-admin.billing.show', $invoice->getKey()) }}">
                                        {{ $invoice->number }}
                                    </a>
                                </td>
                                <td><span class="badge {{ $invoice->status->value }}">{{ $invoice->status->value }}</span></td>
                                <td>{{ $invoice->total }} {{ $invoice->currency }}</td>
                                <td>{{ $invoice->amount_paid }} {{ $invoice->currency }}</td>
                                <td>{{ $invoice->due_at?->format('Y-m-d') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
