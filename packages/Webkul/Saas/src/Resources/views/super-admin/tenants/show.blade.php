@extends('saas::layouts.master')

@section('title', $tenant->name)

@section('content')
    @php
        $logoUrl = $tenant->logoUrl();

        $subscriptions = $tenant->subscriptions->sortByDesc('id');

        $initials = str($tenant->name)->substr(0, 1)->upper();
    @endphp

    <div class="page-head">
        <div class="identity">
            <span class="identity-logo">
                @if ($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $tenant->name }}">
                @else
                    {{ $initials }}
                @endif
            </span>

            <div>
                <div class="breadcrumb">
                    <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                    @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                    <a href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.super-admin.tenants.title')</a>

                    @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                    <span>{{ $tenant->name }}</span>
                </div>

                <h1>
                    @lang('saas::app.super-admin.tenants.detail-title', ['name' => $tenant->name])

                    <span class="badge {{ $tenant->status->value }}">{{ $tenant->status->label() }}</span>
                </h1>

                <p>
                    {{ $tenant->slug }}

                    &middot;

                    {{ $tenant->legal_name ?: $tenant->name }}

                    @if ($tenant->owner?->email)
                        &middot; {{ $tenant->owner->email }}
                    @endif
                </p>
            </div>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.tenants.index') }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.common.back')
            </a>

            @if ($tenant->adminPanelUrl())
                <a class="btn" href="{{ $tenant->adminPanelUrl() }}" target="_blank" rel="noopener">
                    @include('saas::layouts.partials.icon', ['name' => 'external'])

                    @lang('saas::app.super-admin.tenants.login-as-tenant')
                </a>
            @endif

            <button type="button" class="btn" data-modal-open="assign-plan-modal">
                @include('saas::layouts.partials.icon', ['name' => 'plans'])

                @lang('saas::app.super-admin.tenants.assign')
            </button>

            <a class="btn secondary" href="{{ route('saas.super-admin.tenants.edit', $tenant->getKey()) }}">
                @include('saas::layouts.partials.icon', ['name' => 'edit'])

                @lang('saas::app.common.edit')
            </a>
        </div>
    </div>

    <div class="tiles-grid">
        @foreach ([
            ['key' => 'attributes', 'icon' => 'settings', 'label' => trans('saas::app.super-admin.tenants.attributes')],
            ['key' => 'attribute_families', 'icon' => 'plans', 'label' => trans('saas::app.super-admin.tenants.attribute-families')],
            ['key' => 'products', 'icon' => 'products', 'label' => trans('saas::app.super-admin.tenants.products')],
            ['key' => 'categories', 'icon' => 'cms', 'label' => trans('saas::app.super-admin.tenants.categories')],
            ['key' => 'customers', 'icon' => 'customers', 'label' => trans('saas::app.super-admin.tenants.customers')],
            ['key' => 'orders', 'icon' => 'orders', 'label' => trans('saas::app.super-admin.tenants.orders')],
        ] as $tile)
            <div class="card info-tile">
                <span class="info-label">{{ $tile['label'] }}</span>

                <p class="info-value">{{ number_format((int) ($statistics[$tile['key']] ?? 0)) }}</p>

                <p class="info-foot">@lang('saas::app.super-admin.tenants.information')</p>
            </div>
        @endforeach
    </div>

    <div class="detail-grid" style="margin-top: 16px;">
        <div class="stack">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.subscription-plans')</h2>

                        <p>@lang('saas::app.super-admin.tenants.subscription-plans-help')</p>
                    </div>
                </div>

                @if ($subscriptions->isEmpty())
                    <div class="card-body">
                        <p class="muted">@lang('saas::app.super-admin.tenants.no-subscription')</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>@lang('saas::app.common.plan')</th>
                                    <th>@lang('saas::app.super-admin.tenants.state')</th>
                                    <th>@lang('saas::app.super-admin.tenants.billing-cycle')</th>
                                    <th>@lang('saas::app.super-admin.tenants.amount')</th>
                                    <th>@lang('saas::app.super-admin.tenants.monthly-price')</th>
                                    <th>@lang('saas::app.super-admin.tenants.yearly-price')</th>
                                    <th>@lang('saas::app.super-admin.tenants.expiration-date')</th>
                                    <th>@lang('saas::app.super-admin.tenants.next-billing-date')</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($subscriptions as $subscription)
                                    <tr>
                                        <td class="strong">
                                            @if ($subscription->plan)
                                                <a href="{{ route('saas.super-admin.plans.show', $subscription->plan->getKey()) }}">
                                                    {{ $subscription->plan->name }}
                                                </a>
                                            @else
                                                {{ trans('saas::app.common.none') }}
                                            @endif
                                        </td>
                                        <td>
                                            <span class="badge {{ $subscription->status->value }}">{{ $subscription->status->value }}</span>
                                        </td>
                                        <td>{{ $subscription->plan?->billing_interval ?? trans('saas::app.common.none') }}</td>
                                        <td class="nowrap">
                                            {{ $subscription->plan?->price ?? '0.00' }}
                                            {{ $subscription->plan?->currency }}
                                        </td>
                                        <td class="nowrap">{{ number_format($subscription->plan?->monthlyPrice() ?? 0, 2) }}</td>
                                        <td class="nowrap">{{ number_format($subscription->plan?->yearlyAmount() ?? 0, 2) }}</td>
                                        <td class="nowrap">{{ ($subscription->ends_at ?? $subscription->current_period_end)?->format('M d, Y') ?? trans('saas::app.common.none') }}</td>
                                        <td class="nowrap">{{ $subscription->current_period_end?->format('M d, Y') ?? trans('saas::app.common.none') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.domain-information')</h2>

                        <p>@lang('saas::app.super-admin.tenants.domain-information-help')</p>
                    </div>

                    <a href="{{ route('saas.super-admin.domains.index') }}">@lang('saas::app.super-admin.domains.title')</a>
                </div>

                @if ($tenant->domains->isEmpty())
                    <div class="card-body">
                        <p class="muted">@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>@lang('saas::app.super-admin.domains.hostname')</th>
                                    <th>@lang('saas::app.super-admin.domains.type')</th>
                                    <th>@lang('saas::app.common.status')</th>
                                    <th>@lang('saas::app.super-admin.domains.primary')</th>
                                    <th class="end">@lang('saas::app.common.actions')</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($tenant->domains as $domain)
                                    <tr>
                                        <td class="strong">
                                            <a href="{{ $tenant->storefrontUrl() ?: '#' }}" target="_blank" rel="noopener">{{ $domain->hostname }}</a>
                                        </td>
                                        <td>{{ $domain->type->value }}</td>
                                        <td>
                                            <span class="badge {{ $domain->status->value }}">{{ $domain->status->value }}</span>
                                        </td>
                                        <td>{{ $domain->is_primary ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</td>
                                        <td class="end">
                                            <div class="actions" style="justify-content: flex-end;">
                                                @if ($domain->status->value !== 'verified')
                                                    <form class="inline-form" method="POST" action="{{ route('saas.super-admin.domains.verify', $domain->getKey()) }}">
                                                        @csrf

                                                        <button type="submit" class="btn small secondary">@lang('saas::app.super-admin.domains.verify')</button>
                                                    </form>
                                                @endif

                                                @if ($domain->status->value === 'verified' && ! $domain->is_primary)
                                                    <form class="inline-form" method="POST" action="{{ route('saas.super-admin.domains.primary', $domain->getKey()) }}">
                                                        @csrf

                                                        <button type="submit" class="btn small secondary">@lang('saas::app.super-admin.domains.make-primary')</button>
                                                    </form>
                                                @endif
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.billing-records')</h2>
                    </div>
                </div>

                @if ($tenant->billingRecords->isEmpty())
                    <div class="card-body">
                        <p class="muted">@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="table">
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
                                @foreach ($tenant->billingRecords->sortByDesc('id') as $invoice)
                                    <tr>
                                        <td class="strong">
                                            <a href="{{ route('saas.super-admin.billing.show', $invoice->getKey()) }}">{{ $invoice->number }}</a>
                                        </td>
                                        <td>
                                            <span class="badge {{ $invoice->status->value }}">{{ $invoice->status->value }}</span>
                                        </td>
                                        <td>{{ $invoice->total }} {{ $invoice->currency }}</td>
                                        <td>{{ $invoice->amount_paid }} {{ $invoice->currency }}</td>
                                        <td>{{ $invoice->due_at?->format('M d, Y') ?? trans('saas::app.common.none') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.provisioning-operations')</h2>
                    </div>
                </div>

                @if ($operations->isEmpty())
                    <div class="card-body">
                        <p class="muted">@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <div class="table-wrap">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>@lang('saas::app.common.status')</th>
                                    <th>@lang('saas::app.super-admin.provisioning.step')</th>
                                    <th>@lang('saas::app.super-admin.provisioning.attempts')</th>
                                    <th>@lang('saas::app.super-admin.provisioning.started')</th>
                                    <th>@lang('saas::app.super-admin.provisioning.error')</th>
                                </tr>
                            </thead>

                            <tbody>
                                @foreach ($operations as $operation)
                                    <tr>
                                        <td>
                                            <span class="badge {{ $operation->status->value }}">{{ $operation->status->value }}</span>
                                        </td>
                                        <td>{{ $operation->step }}</td>
                                        <td>{{ $operation->attempts }}</td>
                                        <td class="nowrap">{{ $operation->started_at?->format('M d, Y H:i') }}</td>
                                        <td class="muted">{{ $operation->error }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.audit')</h2>
                    </div>
                </div>

                @if ($recentAudit->isEmpty())
                    <div class="card-body">
                        <p class="muted">@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <ul class="checklist">
                        @foreach ($recentAudit as $entry)
                            <li>
                                <span>
                                    <strong>{{ $entry->action }}</strong>

                                    <span class="muted">{{ $entry->description }}</span>
                                </span>

                                <span class="muted">{{ $entry->created_at?->format('M d, Y H:i') }}</span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        <div class="stack">
            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.details')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="kv">
                        <dt>@lang('saas::app.common.email')</dt>
                        <dd>{{ $tenant->email ?? trans('saas::app.common.none') }}</dd>

                        <dt>@lang('saas::app.tenant.settings.contact-phone')</dt>
                        <dd>{{ $tenant->phone ?? trans('saas::app.common.none') }}</dd>

                        <dt>@lang('saas::app.super-admin.tenants.cname')</dt>
                        <dd>{{ $tenant->cname ?? trans('saas::app.common.none') }}</dd>

                        <dt>@lang('saas::app.tenant.settings.timezone')</dt>
                        <dd>{{ $tenant->timezone }}</dd>

                        <dt>@lang('saas::app.tenant.settings.locale')</dt>
                        <dd>{{ $tenant->default_locale }}</dd>

                        <dt>@lang('saas::app.tenant.settings.currency')</dt>
                        <dd>{{ $tenant->default_currency }}</dd>

                        <dt>@lang('saas::app.super-admin.tenants.owner')</dt>
                        <dd>{{ $tenant->owner?->name ?? trans('saas::app.common.none') }}</dd>

                        <dt>@lang('saas::app.common.created')</dt>
                        <dd>{{ $tenant->created_at?->format('M d, Y H:i') }}</dd>

                        <dt>@lang('saas::app.super-admin.tenants.verified-at')</dt>
                        <dd>{{ $tenant->email_verified_at?->format('M d, Y H:i') ?? trans('saas::app.common.no') }}</dd>

                        <dt>@lang('saas::app.super-admin.tenants.approved-at')</dt>
                        <dd>{{ $tenant->approved_at?->format('M d, Y H:i') ?? trans('saas::app.common.no') }}</dd>

                        <dt>@lang('saas::app.super-admin.tenants.activated-at')</dt>
                        <dd>{{ $tenant->activated_at?->format('M d, Y H:i') ?? trans('saas::app.common.no') }}</dd>

                        @if ($tenant->suspension_reason)
                            <dt>@lang('saas::app.common.reason')</dt>
                            <dd>{{ $tenant->suspension_reason }}</dd>
                        @endif
                    </dl>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.lifecycle')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <div class="actions">
                        @if (! $tenant->approved_at)
                            <form class="inline-form" method="POST" action="{{ route('saas.super-admin.tenants.approve', $tenant->getKey()) }}">
                                @csrf

                                <button type="submit" class="btn small">@lang('saas::app.super-admin.tenants.approve')</button>
                            </form>
                        @endif

                        @if (! $tenant->isServing() && $tenant->status->value !== 'cancelled')
                            <form class="inline-form" method="POST" action="{{ route('saas.super-admin.tenants.activate', $tenant->getKey()) }}">
                                @csrf

                                <button type="submit" class="btn small">@lang('saas::app.super-admin.tenants.activate')</button>
                            </form>
                        @endif

                        @if ($tenant->status->value === 'suspended')
                            <form class="inline-form" method="POST" action="{{ route('saas.super-admin.tenants.reactivate', $tenant->getKey()) }}">
                                @csrf

                                <button type="submit" class="btn small">@lang('saas::app.super-admin.tenants.reactivate')</button>
                            </form>
                        @endif

                        <form class="inline-form" method="POST" action="{{ route('saas.super-admin.tenants.export', $tenant->getKey()) }}">
                            @csrf

                            <button type="submit" class="btn small ghost">@lang('saas::app.super-admin.tenants.export')</button>
                        </form>

                        @if ($tenant->status->value !== 'cancelled')
                            <form
                                class="inline-form"
                                method="POST"
                                action="{{ route('saas.super-admin.tenants.provision', $tenant->getKey()) }}"
                            >
                                @csrf

                                <button type="submit" class="btn small ghost">@lang('saas::app.super-admin.tenants.provision')</button>
                            </form>
                        @endif
                    </div>

                    @if ($tenant->status->value !== 'suspended' && $tenant->status->value !== 'cancelled')
                        <form method="POST" action="{{ route('saas.super-admin.tenants.suspend', $tenant->getKey()) }}" style="margin-top: 16px;">
                            @csrf

                            <div class="field">
                                <label for="suspend_reason">@lang('saas::app.super-admin.tenants.suspend')</label>

                                <input id="suspend_reason" name="reason" type="text" required>
                            </div>

                            <div class="form-actions start">
                                <button type="submit" class="btn small danger">@lang('saas::app.super-admin.tenants.suspend')</button>
                            </div>
                        </form>
                    @endif

                    @if ($tenant->status->value !== 'cancelled')
                        <form method="POST" action="{{ route('saas.super-admin.tenants.cancel', $tenant->getKey()) }}" style="margin-top: 12px;">
                            @csrf

                            <div class="field">
                                <label for="cancel_reason">@lang('saas::app.super-admin.tenants.cancel')</label>

                                <input id="cancel_reason" name="reason" type="text" required>
                            </div>

                            <div class="form-actions start">
                                <button type="submit" class="btn small danger">@lang('saas::app.super-admin.tenants.cancel')</button>
                            </div>
                        </form>
                    @endif
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>
                            @lang('saas::app.super-admin.tenants.address-list')

                            <span class="badge neutral no-dot">0</span>
                        </h2>
                    </div>
                </div>

                <div class="card-body">
                    <p class="muted">@lang('saas::app.super-admin.tenants.no-addresses')</p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.members')</h2>
                    </div>
                </div>

                @if ($tenant->users->isEmpty())
                    <div class="card-body">
                        <p class="muted">@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <ul class="checklist">
                        @foreach ($tenant->users as $membership)
                            <li>
                                <span>
                                    {{ $membership->admin?->name ?? '#'.$membership->admin_id }}

                                    <span class="muted">{{ $membership->admin?->email }}</span>
                                </span>

                                <span>
                                    <span class="badge neutral no-dot">{{ $membership->role->value }}</span>

                                    <span class="badge {{ $membership->status->value }}">{{ $membership->status->value }}</span>
                                </span>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.usage')</h2>
                    </div>
                </div>

                @if ($usage === [])
                    <div class="card-body">
                        <p class="muted">@lang('saas::app.common.none')</p>
                    </div>
                @else
                    <dl class="kv" style="padding: 18px;">
                        @foreach ($usage as $metric => $value)
                            <dt>{{ \Illuminate\Support\Str::headline($metric) }}</dt>
                            <dd>{{ rtrim(rtrim(number_format((float) $value, 2), '0'), '.') }}</dd>
                        @endforeach
                    </dl>
                @endif
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <h2>@lang('saas::app.super-admin.tenants.delete-title')</h2>
                    </div>
                </div>

                <div class="card-body">
                    <p class="muted">@lang('saas::app.super-admin.tenants.delete-hint')</p>

                    <form
                        method="POST"
                        action="{{ route('saas.super-admin.tenants.destroy', $tenant->getKey()) }}"
                        data-confirm="@lang('saas::app.super-admin.tenants.delete-confirmation')"
                    >
                        @csrf
                        @method('DELETE')

                        <div class="field">
                            <label for="confirmation">@lang('saas::app.super-admin.tenants.delete-confirmation')</label>

                            <input id="confirmation" name="confirmation" type="text" required>
                        </div>

                        <div class="form-actions start">
                            <button type="submit" class="btn small danger">@lang('saas::app.super-admin.tenants.delete')</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <dialog class="modal" id="assign-plan-modal">
        <form method="POST" action="{{ route('saas.super-admin.tenants.assign_plan', $tenant->getKey()) }}">
            @csrf

            <div class="modal-head">
                <div>
                    <h2>@lang('saas::app.super-admin.tenants.assign-plan')</h2>

                    <p>@lang('saas::app.super-admin.tenants.assign-plan-help')</p>
                </div>

                <button type="button" class="icon-btn" data-modal-close aria-label="@lang('saas::app.common.cancel')">
                    @include('saas::layouts.partials.icon', ['name' => 'close'])
                </button>
            </div>

            <div class="modal-body">
                <div class="field">
                    <label for="assign_plan_id">@lang('saas::app.common.plan')</label>

                    <select id="assign_plan_id" name="plan_id" required>
                        @foreach ($plans as $plan)
                            <option value="{{ $plan->getKey() }}" @selected((string) $tenant->plan_id === (string) $plan->getKey())>
                                {{ $plan->name }} &mdash; {{ $plan->price }} {{ $plan->currency }} / {{ $plan->billing_interval }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="modal-foot">
                <button type="button" class="btn ghost" data-modal-close>@lang('saas::app.common.cancel')</button>

                <button type="submit" class="btn">@lang('saas::app.super-admin.tenants.assign')</button>
            </div>
        </form>
    </dialog>
@endsection
