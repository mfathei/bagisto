@extends('saas::emails.layout')

@section('title', trans('saas::app.emails.trial-ending.subject', ['days' => $daysRemaining]))

@section('content')
    <p style="margin: 0 0 12px;">@lang('saas::app.emails.trial-ending.greeting', ['name' => $tenant->name])</p>

    <p style="margin: 0 0 12px;">
        @lang('saas::app.emails.trial-ending.intro', ['days' => $daysRemaining, 'plan' => $tenant->plan?->name ?? config('saas.platform_name')])
    </p>

    <p style="margin: 0 0 12px; font-size: 13px; color: #6b7280;">
        @lang('saas::app.emails.trial-ending.help', ['email' => config('saas.settings.support_email')])
    </p>
@endsection
