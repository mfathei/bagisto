@extends('saas::layouts.master')

@section('title', trans('saas::app.super-admin.cms.title'))

@section('content')
    <div class="page-head">
        <div>
            <div class="breadcrumb">
                <a href="{{ route('saas.super-admin.dashboard') }}">@lang('saas::app.super-admin.nav.dashboard')</a>

                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])

                <span>@lang('saas::app.super-admin.nav.cms')</span>
            </div>

            <h1>@lang('saas::app.super-admin.cms.title')</h1>

            <p>@lang('saas::app.super-admin.cms.subtitle')</p>
        </div>

        <div class="page-actions">
            <a class="btn secondary" href="{{ route('saas.marketing.home') }}" target="_blank" rel="noopener">
                @include('saas::layouts.partials.icon', ['name' => 'external'])

                @lang('saas::app.super-admin.cms.open-website')
            </a>
        </div>
    </div>

    <div class="stats-grid">
        <div class="stat-card">
            <span class="stat-icon">
                @include('saas::layouts.partials.icon', ['name' => 'cms'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ count($pages) }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.cms.pages')</span>
            </div>
        </div>

        <div class="stat-card">
            <span class="stat-icon amber">
                @include('saas::layouts.partials.icon', ['name' => 'info'])
            </span>

            <div class="stat-content">
                <p class="stat-value">{{ $openInquiries }}</p>

                <span class="stat-label">@lang('saas::app.super-admin.cms.open-inquiries')</span>
            </div>
        </div>
    </div>

    <div class="card" style="margin-top: 16px;">
        <div class="card-header">
            <div>
                <h2>@lang('saas::app.super-admin.cms.pages-title')</h2>

                <p>@lang('saas::app.super-admin.cms.pages-help')</p>
            </div>
        </div>

        @if ($pages === [])
            <div class="empty-block">
                @include('saas::layouts.partials.icon', ['name' => 'cms'])

                <p>@lang('saas::app.common.none')</p>
            </div>
        @else
            <div class="table-wrap" style="border: 0; border-radius: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>@lang('saas::app.super-admin.cms.page')</th>
                            <th>@lang('saas::app.super-admin.cms.path')</th>
                            <th>@lang('saas::app.super-admin.cms.group')</th>
                            <th class="end">@lang('saas::app.common.actions')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($pages as $page)
                            <tr>
                                <td class="strong">{{ $page['title'] }}</td>
                                <td><code>{{ $page['path'] }}</code></td>
                                <td>
                                    <span class="badge {{ $page['group'] === 'Legal' ? 'neutral' : 'info' }} no-dot">{{ $page['group'] }}</span>
                                </td>
                                <td class="end">
                                    <a class="btn small secondary" href="{{ $page['url'] }}" target="_blank" rel="noopener">
                                        @lang('saas::app.common.view')
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <div class="card" style="margin-top: 16px;">
        <div class="card-header">
            <div>
                <h2>@lang('saas::app.super-admin.cms.inquiries')</h2>

                <p>@lang('saas::app.super-admin.cms.inquiries-help')</p>
            </div>
        </div>

        @if ($recentInquiries->isEmpty())
            <div class="empty-block">
                @include('saas::layouts.partials.icon', ['name' => 'info'])

                <p>@lang('saas::app.super-admin.cms.no-inquiries')</p>
            </div>
        @else
            <div class="table-wrap" style="border: 0; border-radius: 0;">
                <table class="table">
                    <thead>
                        <tr>
                            <th>@lang('saas::app.super-admin.cms.from')</th>
                            <th>@lang('saas::app.super-admin.cms.subject')</th>
                            <th>@lang('saas::app.common.status')</th>
                            <th>@lang('saas::app.common.created')</th>
                        </tr>
                    </thead>

                    <tbody>
                        @foreach ($recentInquiries as $inquiry)
                            <tr>
                                <td>
                                    <span class="strong">{{ $inquiry->name }}</span>

                                    <div class="cell-sub">{{ $inquiry->email }}</div>

                                    @if ($inquiry->company)
                                        <div class="cell-sub">{{ $inquiry->company }}</div>
                                    @endif
                                </td>
                                <td>{{ $inquiry->subject ?: \Illuminate\Support\Str::limit($inquiry->message, 60) }}</td>
                                <td>
                                    <span class="badge {{ $inquiry->status->value }}">{{ $inquiry->status->value }}</span>
                                </td>
                                <td class="nowrap">{{ $inquiry->created_at?->format('M d, Y H:i') }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
