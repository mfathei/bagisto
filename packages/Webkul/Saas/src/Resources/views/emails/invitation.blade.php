@extends('saas::emails.layout')

@section('title', trans('saas::app.emails.invitation.subject', ['store' => $tenant->name]))

@section('content')
    <p style="margin: 0 0 12px;">@lang('saas::app.emails.invitation.greeting')</p>

    <p style="margin: 0 0 12px;">
        @lang('saas::app.emails.invitation.intro', ['inviter' => $inviterName, 'store' => $tenant->name])
    </p>

    <p style="margin: 0 0 20px;">
        <a
            href="{{ $acceptUrl }}"
            style="display: inline-block; padding: 10px 18px; background: #2563eb; color: #ffffff; border-radius: 8px; font-weight: 600; text-decoration: none;"
        >@lang('saas::app.emails.invitation.action')</a>
    </p>

    <p style="margin: 0 0 12px; font-size: 13px; color: #6b7280;">
        @lang('saas::app.emails.invitation.expires', ['date' => $invitation->expires_at?->format('Y-m-d')])
    </p>

    <p style="margin: 0; font-size: 13px; color: #6b7280;">
        @lang('saas::app.emails.invitation.ignore')
    </p>
@endsection
