@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.subscriptions.title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.subscriptions.title')</h1>

            <p>@lang('saas::app.super-admin.subscriptions.subtitle')</p>
        </div>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('saas.super-admin.subscriptions.index') }}" class="grid cols-3">
            <div class="field" style="margin: 0;">
                <label for="status">@lang('saas::app.common.status')</label>

                <select id="status" name="status">
                    <option value="">@lang('saas::app.super-admin.subscriptions.all-statuses')</option>

                    @foreach (\Webkul\Saas\Enums\SubscriptionStatus::cases() as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(($filters['status'] ?? '') === $status->value)
                        >{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin: 0;">
                <label for="plan_id">@lang('saas::app.common.plan')</label>

                <select id="plan_id" name="plan_id">
                    <option value="">@lang('saas::app.super-admin.tenants.all-plans')</option>

                    @foreach ($plans as $plan)
                        <option
                            value="{{ $plan->getKey() }}"
                            @selected((string) ($filters['plan_id'] ?? '') === (string) $plan->getKey())
                        >{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="actions" style="align-items: end;">
                <button type="submit" class="btn">@lang('saas::app.common.filter')</button>

                <a class="btn secondary" href="{{ route('saas.super-admin.subscriptions.index') }}">
                    @lang('saas::app.common.reset')
                </a>
            </div>
        </form>
    </div>

    <div class="table-wrap" style="margin-top: 16px;">
        <table>
            <thead>
                <tr>
                    <th>@lang('saas::app.common.tenant')</th>
                    <th>@lang('saas::app.common.plan')</th>
                    <th>@lang('saas::app.common.status')</th>
                    <th>@lang('saas::app.super-admin.subscriptions.trial-ends')</th>
                    <th>@lang('saas::app.super-admin.subscriptions.period-end')</th>
                    <th>@lang('saas::app.common.created')</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($subscriptions as $subscription)
                    <tr>
                        <td>
                            <a href="{{ route('saas.super-admin.subscriptions.show', $subscription->getKey()) }}">
                                {{ $subscription->tenant?->name ?? '#'.$subscription->tenant_id }}
                            </a>
                        </td>
                        <td>{{ $subscription->plan?->name ?? trans('saas::app.common.none') }}</td>
                        <td><span class="badge {{ $subscription->status->value }}">{{ $subscription->status->value }}</span></td>
                        <td>{{ $subscription->trial_ends_at?->format('Y-m-d') ?? trans('saas::app.common.none') }}</td>
                        <td>{{ $subscription->current_period_end?->format('Y-m-d') ?? trans('saas::app.common.none') }}</td>
                        <td>{{ $subscription->created_at?->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="muted">@lang('saas::app.super-admin.subscriptions.empty')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $subscriptions->links() }}</div>
@endsection
