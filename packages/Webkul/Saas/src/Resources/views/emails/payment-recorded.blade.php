@extends('saas::emails.layout')

@section('title', trans('saas::app.emails.payment-recorded.subject', ['number' => $invoice->number]))

@section('content')
    <p style="margin: 0 0 12px;">@lang('saas::app.emails.payment-recorded.greeting', ['name' => $tenant->name])</p>

    <p style="margin: 0 0 12px">
        @lang('saas::app.emails.payment-recorded.intro', ['number' => $invoice->number])
    </p>

    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="margin: 0 0 16px; border-collapse: collapse;">
        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #6b7280;">@lang('saas::app.emails.payment-recorded.amount')</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; text-align: end; font-weight: 600;">
                {{ number_format($amount, 2) }} {{ $invoice->currency }}
            </td>
        </tr>

        <tr>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; color: #6b7280;">@lang('saas::app.common.total')</td>
            <td style="padding: 8px 0; border-bottom: 1px solid #e2e8f0; text-align: end;">
                {{ number_format((float) $invoice->total, 2) }} {{ $invoice->currency }}
            </td>
        </tr>

        <tr>
            <td style="padding: 8px 0; color: #6b7280;">@lang('saas::app.common.balance')</td>
            <td style="padding: 8px 0; text-align: end; font-weight: 600;">
                {{ number_format($invoice->balance(), 2) }} {{ $invoice->currency }}
            </td>
        </tr>
    </table>

    <p style="margin: 0; font-size: 13px; color: #6b7280;">
        @lang('saas::app.emails.payment-recorded.help')
    </p>
@endsection
