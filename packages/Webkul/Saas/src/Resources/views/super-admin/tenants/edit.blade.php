@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.tenants.edit-title'))

@section('content')
    @php
        $ownerParts = preg_split('/\s+/', trim((string) ($tenant->owner?->name ?? '')), 2);

        $firstName = $ownerParts[0] ?? '';

        $lastName = $ownerParts[1] ?? '';

        $statusActive = $tenant->status === \Webkul\Saas\Enums\TenantStatus::Active;

        $logoUrl = $tenant->logoUrl();
    @endphp

    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.tenants.index') }}">@lang('saas::app.super-admin.nav.tenants-list')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <a href="{{ route('saas.super-admin.tenants.show', $tenant->getKey()) }}">{{ $tenant->name }}</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.tenants.edit-title')</span>
            </div>

            <h1>@lang('saas::app.super-admin.tenants.edit-title')</h1>

            <p>@lang('saas::app.super-admin.tenants.edit-subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.super-admin.tenants.show', $tenant->getKey()) }}">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])

                @lang('saas::app.common.back')
            </a>
        </div>
    </div>

    <form
        method="POST"
        action="{{ route('saas.super-admin.tenants.update', $tenant->getKey()) }}"
        enctype="multipart/form-data"
    >
        @csrf
        @method('PUT')

        <div class="detail-grid">
            <div class="stack">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2>@lang('saas::app.super-admin.tenants.general')</h2>

                            <p>@lang('saas::app.super-admin.tenants.general-help')</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="first_name">@lang('saas::app.super-admin.tenants.first-name')</label>

                                <input id="first_name" name="first_name" type="text" value="{{ old('first_name', $firstName) }}" required>
                            </div>

                            <div class="field">
                                <label for="last_name">@lang('saas::app.super-admin.tenants.last-name')</label>

                                <input id="last_name" name="last_name" type="text" value="{{ old('last_name', $lastName) }}" required>
                            </div>

                            <div class="field">
                                <label for="email">@lang('saas::app.tenant.settings.contact-email')</label>

                                <input id="email" name="email" type="email" value="{{ old('email', $tenant->email) }}">
                            </div>

                            <div class="field">
                                <label for="phone">@lang('saas::app.tenant.settings.contact-phone')</label>

                                <input id="phone" name="phone" type="text" value="{{ old('phone', $tenant->phone) }}">
                            </div>

                            <div class="field span-2">
                                <label for="name">@lang('saas::app.super-admin.tenants.organization-name')</label>

                                <input id="name" name="name" type="text" value="{{ old('name', $tenant->name) }}" required>
                            </div>

                            <div class="field span-2">
                                <label for="legal_name">@lang('saas::app.super-admin.tenants.legal-name')</label>

                                <input id="legal_name" name="legal_name" type="text" value="{{ old('legal_name', $tenant->legal_name) }}">
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2>@lang('saas::app.tenant.settings.localization')</h2>

                            <p>@lang('saas::app.super-admin.tenants.localization-help')</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field">
                                <label for="timezone">@lang('saas::app.tenant.settings.timezone')</label>

                                <select id="timezone" name="timezone">
                                    @foreach ($timezones as $timezone)
                                        <option value="{{ $timezone }}" @selected(old('timezone', $tenant->timezone) === $timezone)>
                                            {{ $timezone }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field">
                                <label for="locale">@lang('saas::app.tenant.settings.locale')</label>

                                <select id="locale" name="locale">
                                    @foreach ($locales as $locale)
                                        <option value="{{ $locale->code }}" @selected(old('locale', $tenant->default_locale) === $locale->code)>
                                            {{ $locale->name }} ({{ $locale->code }})
                                        </option>
                                    @endforeach
                                </select>
                            </div>

                            <div class="field">
                                <label for="currency">@lang('saas::app.tenant.settings.currency')</label>

                                <select id="currency" name="currency">
                                    @foreach ($currencies as $currency)
                                        <option value="{{ $currency->code }}" @selected(old('currency', $tenant->default_currency) === $currency->code)>
                                            {{ $currency->code }} &mdash; {{ $currency->name }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="stack">
                <div class="card">
                    <div class="card-header">
                        <div>
                            <h2>@lang('saas::app.super-admin.tenants.settings')</h2>

                            <p>@lang('saas::app.super-admin.tenants.settings-help')</p>
                        </div>
                    </div>

                    <div class="card-body">
                        <div class="form-grid">
                            <div class="field span-2">
                                <label>@lang('saas::app.common.status')</label>

                                <label class="switch">
                                    <input type="hidden" name="is_active" value="0">

                                    <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $statusActive))>

                                    <span class="switch-track"></span>

                                    <span>
                                        @if ($statusActive)
                                            @lang('saas::app.super-admin.tenants.status-active')
                                        @else
                                            @lang('saas::app.super-admin.tenants.status-inactive')
                                        @endif
                                    </span>
                                </label>

                                <span class="hint">@lang('saas::app.super-admin.tenants.status-help')</span>
                            </div>

                            <div class="field span-2">
                                <label for="slug">@lang('saas::app.super-admin.tenants.user-name')</label>

                                <input id="slug" name="slug" type="text" value="{{ old('slug', $tenant->slug) }}" required>

                                <span class="hint">@lang('saas::app.super-admin.tenants.slug-hint', ['suffix' => config('saas.domains.suffix')])</span>
                            </div>

                            <div class="field span-2">
                                <label for="cname">@lang('saas::app.super-admin.tenants.cname')</label>

                                <input id="cname" name="cname" type="text" value="{{ old('cname', $tenant->cname) }}" placeholder="shop.example.com">

                                <span class="hint">@lang('saas::app.super-admin.tenants.cname-help')</span>
                            </div>

                            <div class="field span-2">
                                <label for="logo">@lang('saas::app.super-admin.tenants.add-image')</label>

                                <div class="image-upload">
                                    <span class="image-preview">
                                        @if ($logoUrl)
                                            <img src="{{ $logoUrl }}" alt="{{ $tenant->name }}">
                                        @else
                                            @include('saas::layouts.partials.icon', ['name' => 'image'])
                                        @endif
                                    </span>

                                    <input id="logo" name="logo" type="file" accept="image/png,image/jpeg,image/webp">
                                </div>

                                @if ($logoUrl)
                                    <label class="check">
                                        <input type="checkbox" name="remove_logo" value="1">

                                        <span>@lang('saas::app.super-admin.tenants.remove-image')</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card">
                    <div class="card-body">
                        <dl class="kv">
                            <dt>@lang('saas::app.common.plan')</dt>
                            <dd>{{ $tenant->plan?->name ?? trans('saas::app.common.none') }}</dd>

                            <dt>@lang('saas::app.super-admin.tenants.owner')</dt>
                            <dd>{{ $tenant->owner?->email ?? trans('saas::app.common.none') }}</dd>

                            <dt>@lang('saas::app.common.created')</dt>
                            <dd>{{ $tenant->created_at?->format('M d, Y H:i') }}</dd>
                        </dl>
                    </div>
                </div>
            </div>
        </div>

        <div class="form-actions">
            <a class="btn ghost" href="{{ route('saas.super-admin.tenants.show', $tenant->getKey()) }}">
                @lang('saas::app.common.cancel')
            </a>

            <button type="submit" class="btn">@lang('saas::app.common.save')</button>
        </div>
    </form>
@endsection
