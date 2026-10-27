@extends('saas::emails.layout')

@section('title', trans('saas::marketing.contact.mail_subject', ['subject' => mb_substr((string) $inquiry->subject, 0, 80)]))

@section('content')
    <p style="margin: 0 0 12px; font-weight: 600;">
        @lang('saas::marketing.contact.mail.intro')
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="font-size: 13px; line-height: 1.6;">
        <tr>
            <td style="padding: 4px 0; color: #6b7280; width: 130px;">@lang('saas::marketing.contact.form.name')</td>
            <td style="padding: 4px 0;">{{ $inquiry->name }}</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #6b7280;">@lang('saas::marketing.contact.form.email')</td>
            <td style="padding: 4px 0;" dir="ltr">{{ $inquiry->email }}</td>
        </tr>
        @if ($inquiry->phone)
            <tr>
                <td style="padding: 4px 0; color: #6b7280;">@lang('saas::marketing.contact.form.phone')</td>
                <td style="padding: 4px 0;" dir="ltr">{{ $inquiry->phone }}</td>
            </tr>
        @endif
        @if ($inquiry->company)
            <tr>
                <td style="padding: 4px 0; color: #6b7280;">@lang('saas::marketing.contact.form.company')</td>
                <td style="padding: 4px 0;">{{ $inquiry->company }}</td>
            </tr>
        @endif
        <tr>
            <td style="padding: 4px 0; color: #6b7280;">@lang('saas::marketing.contact.form.inquiry_type')</td>
            <td style="padding: 4px 0;">@lang('saas::marketing.contact.inquiry_types.'.$inquiry->inquiry_type)</td>
        </tr>
        <tr>
            <td style="padding: 4px 0; color: #6b7280;">@lang('saas::marketing.contact.form.subject')</td>
            <td style="padding: 4px 0;">{{ $inquiry->subject }}</td>
        </tr>
    </table>

    <div style="margin-top: 16px; padding: 14px 16px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 8px;">
        {!! nl2br(e($inquiry->message)) !!}
    </div>

    <p style="margin: 18px 0 0; font-size: 12px; color: #6b7280;">
        @lang('saas::marketing.contact.mail.footer', ['uuid' => $inquiry->uuid])
    </p>
@endsection
