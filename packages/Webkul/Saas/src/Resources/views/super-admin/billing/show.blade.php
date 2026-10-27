@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.billing.invoice', ['number' => $invoice->number]))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.billing.index') }}">@lang('saas::app.super-admin.nav.subscription-invoices')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>{{ $invoice->number }}</span>
            </div>

            <h1>
                @lang('saas::app.super-admin.billing.invoice', ['number' => $invoice->number])

                <span class="badge {{ $invoice->status->value }}">{{ $invoice->status->value }}</span>
            </h1>

            <p>
                @if ($invoice->tenant)
                    {{ $invoice->tenant->name }} &middot;
                @endif

                {{ $invoice->created_at?->format('M d, Y') }}
            </p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.billing.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.super-admin.billing.back')
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-value">{{ number_format((float) $invoice->total, 2) }} {{ $invoice->currency }}</span>

                <span class="stat-label">@lang('saas::app.common.total')</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-value">{{ number_format((float) $invoice->amount_paid, 2) }} {{ $invoice->currency }}</span>

                <span class="stat-label">@lang('saas::app.super-admin.billing.amount-paid')</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-value">{{ number_format($invoice->balance(), 2) }} {{ $invoice->currency }}</span>

                <span class="stat-label">@lang('saas::app.common.balance')</span>
            </div>
        </div>

        <div class="stat-card">
            <div class="stat-content">
                <span class="stat-value">{{ $invoice->due_at?->format('M d, Y') ?? trans('saas::app.common.none') }}</span>

                <span class="stat-label">@lang('saas::app.super-admin.billing.due')</span>
            </div>
        </div>
    </div>

    <div class="detail-grid" style="margin-top: 16px;">
        <div class="stack">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.billing.summary')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="kv">
                        <dt>@lang('saas::app.common.number')</dt>
                        <dd>{{ $invoice->number }}</dd>

                        <dt>@lang('saas::app.common.tenant')</dt>
                        <dd>
                            @if ($invoice->tenant)
                                <a href="{{ route('saas.super-admin.tenants.show', $invoice->tenant->getKey()) }}">
                                    {{ $invoice->tenant->name }}
                                </a>
                            @else
                                #{{ $invoice->tenant_id }}
                            @endif
                        </dd>

                        @if ($invoice->subscription?->plan)
                            <dt>@lang('saas::app.common.plan')</dt>
                            <dd>{{ $invoice->subscription->plan->name }}</dd>
                        @endif

                        <dt>@lang('saas::app.super-admin.billing.period')</dt>
                        <dd>
                            {{ $invoice->period_start?->format('M d, Y') ?? trans('saas::app.common.none') }}
                            &ndash;
                            {{ $invoice->period_end?->format('M d, Y') ?? trans('saas::app.common.none') }}
                        </dd>

                        <dt>@lang('saas::app.super-admin.billing.due')</dt>
                        <dd>{{ $invoice->due_at?->format('M d, Y') ?? trans('saas::app.common.none') }}</dd>

                        <dt>@lang('saas::app.super-admin.billing.provider')</dt>
                        <dd>{{ $invoice->provider ?: $provider->name() }}</dd>

                        @if ($invoice->paid_at)
                            <dt>@lang('saas::app.super-admin.billing.paid-at')</dt>
                            <dd>{{ $invoice->paid_at->format('M d, Y H:i') }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.billing.payments')</h2>
                    </div>
                </div>

                <div style="overflow-x: auto;">
                    <table class="dg-table">
                        <thead>
                            <tr>
                                <th>@lang('saas::app.super-admin.billing.reference')</th>
                                <th>@lang('saas::app.common.status')</th>
                                <th>@lang('saas::app.common.total')</th>
                                <th>@lang('saas::app.super-admin.billing.processed-at')</th>
                            </tr>
                        </thead>

                        <tbody>
                            @forelse ($invoice->transactions as $transaction)
                                <tr>
                                    <td data-label="@lang('saas::app.super-admin.billing.reference')">
                                        <span class="cell-title" dir="ltr">{{ $transaction->provider_transaction_id ?: trans('saas::app.common.none') }}</span>
                                    </td>

                                    <td data-label="@lang('saas::app.common.status')">
                                        <span class="badge {{ $transaction->status->value }}">{{ $transaction->status->value }}</span>
                                    </td>

                                    <td data-label="@lang('saas::app.common.total')" class="nowrap">
                                        {{ number_format((float) $transaction->amount, 2) }} {{ $transaction->currency }}
                                    </td>

                                    <td data-label="@lang('saas::app.super-admin.billing.processed-at')" class="nowrap">
                                        {{ $transaction->processed_at?->format('M d, Y H:i') ?? trans('saas::app.common.none') }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4">
                                        <div class="dg-empty">
                                            @include('saas::layouts.partials.icon', ['name' => 'invoices'])

                                            <p>@lang('saas::app.super-admin.billing.no-payments')</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="stack">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.billing.amounts')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="kv">
                        <dt>@lang('saas::app.super-admin.billing.subtotal')</dt>
                        <dd>{{ number_format((float) $invoice->subtotal, 2) }} {{ $invoice->currency }}</dd>

                        <dt>@lang('saas::app.super-admin.billing.tax')</dt>
                        <dd>{{ number_format((float) $invoice->tax, 2) }} {{ $invoice->currency }}</dd>

                        <dt>@lang('saas::app.common.total')</dt>
                        <dd><strong>{{ number_format((float) $invoice->total, 2) }} {{ $invoice->currency }}</strong></dd>

                        <dt>@lang('saas::app.super-admin.billing.amount-paid')</dt>
                        <dd>{{ number_format((float) $invoice->amount_paid, 2) }} {{ $invoice->currency }}</dd>

                        <dt>@lang('saas::app.common.balance')</dt>
                        <dd><strong>{{ number_format($invoice->balance(), 2) }} {{ $invoice->currency }}</strong></dd>
                    </dl>
                </div>
            </div>

            @if ($invoice->balance() > 0 && ! in_array($invoice->status->value, ['paid', 'void', 'refunded'], true))
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2>@lang('saas::app.super-admin.billing.record-payment')</h2>

                            <p>@lang('saas::app.super-admin.billing.record-payment-help')</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <form method="POST" action="{{ route('saas.super-admin.billing.payments.store', $invoice->getKey()) }}">
                            @csrf

                            <div class="form-grid">
                                <div class="field">
                                    <label for="amount">@lang('saas::app.super-admin.billing.amount')</label>

                                    <input
                                        id="amount"
                                        type="number"
                                        name="amount"
                                        step="0.01"
                                        min="0.01"
                                        max="{{ $invoice->balance() }}"
                                        value="{{ old('amount', number_format($invoice->balance(), 2, '.', '')) }}"
                                        required
                                    >

                                    @error('amount')
                                        <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field">
                                    <label for="currency">@lang('saas::app.super-admin.billing.currency')</label>

                                    <input id="currency" type="text" name="currency" maxlength="3" value="{{ old('currency', $invoice->currency) }}" required>

                                    @error('currency')
                                        <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field">
                                    <label for="method">@lang('saas::app.super-admin.billing.method')</label>

                                    <input id="method" type="text" name="method" maxlength="40" value="{{ old('method', 'bank_transfer') }}">

                                    @error('method')
                                        <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field">
                                    <label for="reference">@lang('saas::app.super-admin.billing.reference')</label>

                                    <input id="reference" type="text" name="reference" maxlength="191" value="{{ old('reference') }}" required>

                                    @error('reference')
                                        <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                                    @enderror
                                </div>

                                <div class="field span-2">
                                    <label for="notes">@lang('saas::app.super-admin.billing.notes')</label>

                                    <textarea id="notes" name="notes" rows="2" maxlength="500">{{ old('notes') }}</textarea>
                                </div>
                            </div>

                            <div class="field">
                                <label class="check">
                                    <input type="checkbox" name="confirmed" value="1" required>

                                    <span>@lang('saas::app.super-admin.billing.confirm-payment')</span>
                                </label>

                                @error('confirmed')
                                    <span class="alert error" style="margin: 8px 0 0;">{{ $message }}</span>
                                @enderror
                            </div>

                            @error('payment')
                                <span class="alert error">{{ $message }}</span>
                            @enderror

                            <div class="form-actions">
                                <button type="submit" class="btn">@lang('saas::app.super-admin.billing.record-payment')</button>
                            </div>
                        </form>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection
