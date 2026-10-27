@extends('saas::layouts.master')

@section('title', $tenant->name)

@section('content')
    <div class="page-head">
        <div>
            <h1>{{ $tenant->name }}</h1>

            <p>
                <span class="badge {{ $tenant->status->value }}">{{ $tenant->status->value }}</span>

                <span class="muted">{{ $tenant->slug }}</span>
            </p>
        </div>

        <a class="btn secondary" href="{{ route('saas.super-admin.tenants.index') }}">
            @lang('saas::app.common.back')
        </a>
    </div>

    <div class="grid cols-2">
        <div class="card">
            <h2>@lang('saas::app.super-admin.tenants.details')</h2>

            <dl class="kv">
                <dt>@lang('saas::app.common.email')</dt>
                <dd>{{ $tenant->email ?? trans('saas::app.common.none') }}</dd>

                <dt>@lang('saas::app.tenant.settings.contact-phone')</dt>
                <dd>{{ $tenant->phone ?? trans('saas::app.common.none') }}</dd>

                <dt>@lang('saas::app.tenant.settings.timezone')</dt>
                <dd>{{ $tenant->timezone }}</dd>

                <dt>@lang('saas::app.tenant.settings.locale')</dt>
                <dd>{{ $tenant->default_locale }}</dd>

                <dt>@lang('saas::app.tenant.settings.currency')</dt>
                <dd>{{ $tenant->default_currency }}</dd>

                <dt>@lang('saas::app.super-admin.tenants.owner')</dt>
                <dd>{{ $tenant->owner?->name ?? trans('saas::app.common.none') }}</dd>

                <dt>@lang('saas::app.common.created')</dt>
                <dd>{{ $tenant->created_at?->format('Y-m-d H:i') }}</dd>

                <dt>@lang('saas::app.super-admin.tenants.verified-at')</dt>
                <dd>{{ $tenant->email_verified_at?->format('Y-m-d H:i') ?? trans('saas::app.common.no') }}</dd>

                <dt>@lang('saas::app.super-admin.tenants.approved-at')</dt>
                <dd>{{ $tenant->approved_at?->format('Y-m-d H:i') ?? trans('saas::app.common.no') }}</dd>

                <dt>@lang('saas::app.super-admin.tenants.activated-at')</dt>
                <dd>{{ $tenant->activated_at?->format('Y-m-d H:i') ?? trans('saas::app.common.no') }}</dd>

                @if ($tenant->suspension_reason)
                    <dt>@lang('saas::app.common.reason')</dt>
                    <dd>{{ $tenant->suspension_reason }}</dd>
                @endif
            </dl>
        </div>

        <div class="card">
            <h2>@lang('saas::app.super-admin.tenants.plan-details')</h2>

            @if ($tenant->plan)
                <dl class="kv">
                    <dt>@lang('saas::app.common.plan')</dt>
                    <dd>{{ $tenant->plan->name }}</dd>

                    <dt>@lang('saas::app.super-admin.plans.price')</dt>
                    <dd>{{ $tenant->plan->price }} {{ $tenant->plan->currency }}</dd>

                    <dt>@lang('saas::app.super-admin.plans.interval')</dt>
                    <dd>{{ $tenant->plan->billing_interval }}</dd>

                    <dt>@lang('saas::app.super-admin.plans.trial-days')</dt>
                    <dd>{{ $tenant->plan->trial_days }}</dd>
                </dl>
            @else
                <p class="muted">@lang('saas::app.common.none')</p>
            @endif

            @if ($tenant->currentSubscription)
                <h3 style="margin-top: 18px;">@lang('saas::app.super-admin.tenants.subscription-details')</h3>

                <dl class="kv">
                    <dt>@lang('saas::app.common.status')</dt>
                    <dd>{{ $tenant->currentSubscription->status->value }}</dd>

                    <dt>@lang('saas::app.super-admin.subscriptions.trial-ends')</dt>
                    <dd>{{ $tenant->currentSubscription->trial_ends_at?->format('Y-m-d') ?? trans('saas::app.common.none') }}</dd>

                    <dt>@lang('saas::app.super-admin.subscriptions.period-end')</dt>
                    <dd>{{ $tenant->currentSubscription->current_period_end?->format('Y-m-d') ?? trans('saas::app.common.none') }}</dd>
                </dl>

                <a
                    class="btn small secondary"
                    href="{{ route('saas.super-admin.subscriptions.show', $tenant->currentSubscription->getKey()) }}"
                >@lang('saas::app.common.view')</a>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.tenants.lifecycle')</h2>

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

                <form class="inline-form" method="POST" action="{{ route('saas.super-admin.tenants.provision', $tenant->getKey()) }}">
                    @csrf

                    <button type="submit" class="btn small secondary">@lang('saas::app.super-admin.tenants.provision')</button>
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

                <button type="submit" class="btn small secondary">@lang('saas::app.super-admin.tenants.export')</button>
            </form>
        </div>

        <div class="grid cols-2" style="margin-top: 16px;">
            @if ($tenant->status->value !== 'suspended' && $tenant->status->value !== 'cancelled')
                <form method="POST" action="{{ route('saas.super-admin.tenants.suspend', $tenant->getKey()) }}">
                    @csrf

                    <div class="field">
                        <label for="suspend_reason">@lang('saas::app.super-admin.tenants.suspend')</label>

                        <input id="suspend_reason" name="reason" type="text" required>
                    </div>

                    <button type="submit" class="btn small danger">@lang('saas::app.super-admin.tenants.suspend')</button>
                </form>
            @endif

            @if ($tenant->status->value !== 'cancelled')
                <form method="POST" action="{{ route('saas.super-admin.tenants.cancel', $tenant->getKey()) }}">
                    @csrf

                    <div class="field">
                        <label for="cancel_reason">@lang('saas::app.super-admin.tenants.cancel')</label>

                        <input id="cancel_reason" name="reason" type="text" required>
                    </div>

                    <button type="submit" class="btn small danger">@lang('saas::app.super-admin.tenants.cancel')</button>
                </form>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.tenants.domains-title')</h2>

        @if ($tenant->domains->isEmpty())
            <p class="muted">@lang('saas::app.common.none')</p>
        @else
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>@lang('saas::app.super-admin.domains.hostname')</th>
                            <th>@lang('saas::app.super-admin.domains.type')</th>
                            <th>@lang('saas::app.common.status')</th>
                            <th>@lang('saas::app.super-admin.domains.primary')</th>
                            <th>@lang('saas::app.common.actions')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($tenant->domains as $domain)
                            <tr>
                                <td>{{ $domain->hostname }}</td>
                                <td>{{ $domain->type->value }}</td>
                                <td><span class="badge {{ $domain->status->value }}">{{ $domain->status->value }}</span></td>
                                <td>{{ $domain->is_primary ? trans('saas::app.common.yes') : trans('saas::app.common.no') }}</td>
                                <td>
                                    <div class="actions">
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

    <div class="grid cols-2" style="margin-top: 16px;">
        <div class="card">
            <h2>@lang('saas::app.super-admin.tenants.members')</h2>

            @if ($tenant->users->isEmpty())
                <p class="muted">@lang('saas::app.common.none')</p>
            @else
                <ul class="checklist">
                    @foreach ($tenant->users as $membership)
                        <li>
                            <span>
                                {{ $membership->admin?->name ?? '#'.$membership->admin_id }}
                                <span class="muted">{{ $membership->admin?->email }}</span>
                            </span>

                            <span>
                                <span class="badge">{{ $membership->role->value }}</span>
                                <span class="badge {{ $membership->status->value }}">{{ $membership->status->value }}</span>
                            </span>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="card">
            <h2>@lang('saas::app.super-admin.tenants.usage')</h2>

            @if ($usage === [])
                <p class="muted">@lang('saas::app.common.none')</p>
            @else
                <dl class="kv">
                    @foreach ($usage as $metric => $value)
                        <dt>{{ $metric }}</dt>
                        <dd>{{ $value }}</dd>
                    @endforeach
                </dl>
            @endif
        </div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.tenants.billing-records')</h2>

        @if ($tenant->billingRecords->isEmpty())
            <p class="muted">@lang('saas::app.common.none')</p>
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
                        @foreach ($tenant->billingRecords->sortByDesc('id') as $invoice)
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

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.tenants.provisioning-operations')</h2>

        @if ($operations->isEmpty())
            <p class="muted">@lang('saas::app.common.none')</p>
        @else
            <div class="table-wrap">
                <table>
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
                                <td><span class="badge {{ $operation->status->value }}">{{ $operation->status->value }}</span></td>
                                <td>{{ $operation->step }}</td>
                                <td>{{ $operation->attempts }}</td>
                                <td>{{ $operation->started_at?->format('Y-m-d H:i') }}</td>
                                <td class="muted">{{ $operation->error }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.tenants.audit')</h2>

        @if ($recentAudit->isEmpty())
            <p class="muted">@lang('saas::app.common.none')</p>
        @else
            <ul class="checklist">
                @foreach ($recentAudit as $entry)
                    <li>
                        <span>
                            {{ $entry->action }}
                            <span class="muted">{{ $entry->description }}</span>
                        </span>

                        <span class="muted">{{ $entry->created_at?->format('Y-m-d H:i') }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.tenants.update-details')</h2>

        <form method="POST" action="{{ route('saas.super-admin.tenants.update', $tenant->getKey()) }}">
            @csrf
            @method('PUT')

            <div class="grid cols-2">
                <div class="field">
                    <label for="edit_name">@lang('saas::app.common.name')</label>

                    <input id="edit_name" name="name" type="text" value="{{ $tenant->name }}" required>
                </div>

                <div class="field">
                    <label for="edit_legal_name">@lang('saas::app.super-admin.tenants.legal-name')</label>

                    <input id="edit_legal_name" name="legal_name" type="text" value="{{ $tenant->legal_name }}">
                </div>

                <div class="field">
                    <label for="edit_email">@lang('saas::app.common.email')</label>

                    <input id="edit_email" name="email" type="email" value="{{ $tenant->email }}">
                </div>

                <div class="field">
                    <label for="edit_phone">@lang('saas::app.tenant.settings.contact-phone')</label>

                    <input id="edit_phone" name="phone" type="text" value="{{ $tenant->phone }}">
                </div>

                <div class="field">
                    <label for="edit_timezone">@lang('saas::app.tenant.settings.timezone')</label>

                    <input id="edit_timezone" name="timezone" type="text" value="{{ $tenant->timezone }}">
                </div>

                <div class="field">
                    <label for="edit_locale">@lang('saas::app.tenant.settings.locale')</label>

                    <input id="edit_locale" name="locale" type="text" value="{{ $tenant->default_locale }}">
                </div>

                <div class="field">
                    <label for="edit_currency">@lang('saas::app.tenant.settings.currency')</label>

                    <input id="edit_currency" name="currency" type="text" value="{{ $tenant->default_currency }}" maxlength="3">
                </div>
            </div>

            <button type="submit" class="btn">@lang('saas::app.common.save')</button>
        </form>
    </div>

    <div class="card" style="margin-top: 16px;">
        <h2>@lang('saas::app.super-admin.tenants.delete-title')</h2>

        <p class="muted">@lang('saas::app.super-admin.tenants.delete-hint', ['slug' => $tenant->slug])</p>

        <form method="POST" action="{{ route('saas.super-admin.tenants.destroy', $tenant->getKey()) }}">
            @csrf
            @method('DELETE')

            <div class="field">
                <label for="confirmation">@lang('saas::app.super-admin.tenants.delete-confirmation')</label>

                <input id="confirmation" name="confirmation" type="text" required>
            </div>

            <button type="submit" class="btn danger">@lang('saas::app.super-admin.tenants.delete')</button>
        </form>
    </div>
@endsection
