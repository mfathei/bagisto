@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.billing.title'))

@section('content')
    <div class="page-head">
        <div>
            <h1>@lang('saas::app.super-admin.billing.title')</h1>

            <p>@lang('saas::app.super-admin.billing.subtitle', ['provider' => $provider->name()])</p>
        </div>

        <form method="POST" action="{{ route('saas.super-admin.billing.mark_overdue') }}" class="inline-form">
            @csrf

            <button type="submit" class="btn secondary">@lang('saas::app.super-admin.billing.mark-overdue')</button>
        </form>
    </div>

    <div class="card">
        <form method="GET" action="{{ route('saas.super-admin.billing.index') }}" class="grid cols-3">
            <div class="field" style="margin: 0;">
                <label for="status">@lang('saas::app.common.status')</label>

                <select id="status" name="status">
                    <option value="">@lang('saas::app.super-admin.billing.all-statuses')</option>

                    @foreach (\Webkul\Saas\Enums\BillingStatus::cases() as $status)
                        <option
                            value="{{ $status->value }}"
                            @selected(($filters['status'] ?? '') === $status->value)
                        >{{ $status->value }}</option>
                    @endforeach
                </select>
            </div>

            <div class="field" style="margin: 0;">
                <label for="tenant_id">@lang('saas::app.super-admin.billing.tenant-id')</label>

                <input id="tenant_id" type="number" name="tenant_id" min="1" value="{{ $filters['tenant_id'] ?? '' }}">
            </div>

            <div class="actions" style="align-items: end;">
                <button type="submit" class="btn">@lang('saas::app.common.filter')</button>

                <a class="btn secondary" href="{{ route('saas.super-admin.billing.index') }}">
                    @lang('saas::app.common.reset')
                </a>
            </div>
        </form>
    </div>

    <div class="table-wrap" style="margin-top: 16px;">
        <table>
            <thead>
                <tr>
                    <th>@lang('saas::app.common.number')</th>
                    <th>@lang('saas::app.common.tenant')</th>
                    <th>@lang('saas::app.common.status')</th>
                    <th>@lang('saas::app.common.total')</th>
                    <th>@lang('saas::app.common.balance')</th>
                    <th>@lang('saas::app.super-admin.billing.due')</th>
                    <th>@lang('saas::app.common.created')</th>
                </tr>
            </thead>

            <tbody>
                @forelse ($invoices as $invoice)
                    <tr>
                        <td>
                            <a href="{{ route('saas.super-admin.billing.show', $invoice->getKey()) }}">
                                {{ $invoice->number }}
                            </a>
                        </td>
                        <td>
                            @if ($invoice->tenant)
                                <a href="{{ route('saas.super-admin.tenants.show', $invoice->tenant->getKey()) }}">
                                    {{ $invoice->tenant->name }}
                                </a>
                            @else
                                #{{ $invoice->tenant_id }}
                            @endif
                        </td>
                        <td><span class="badge {{ $invoice->status->value }}">{{ $invoice->status->value }}</span></td>
                        <td>{{ number_format((float) $invoice->total, 2) }} {{ $invoice->currency }}</td>
                        <td>{{ number_format($invoice->balance(), 2) }} {{ $invoice->currency }}</td>
                        <td>{{ $invoice->due_at?->format('Y-m-d') ?? trans('saas::app.common.none') }}</td>
                        <td>{{ $invoice->created_at?->format('Y-m-d') }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="muted">@lang('saas::app.super-admin.billing.empty')</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="pagination">{{ $invoices->links() }}</div>
@endsection
