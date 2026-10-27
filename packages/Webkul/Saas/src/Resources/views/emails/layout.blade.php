<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('saas.platform_name'))</title>
</head>

<body style="margin: 0; padding: 0; background: #f5f7fb; font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Tahoma, Arial, sans-serif; color: #1f2937;">
    <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="background: #f5f7fb; padding: 24px 0;">
        <tr>
            <td align="center">
                <table role="presentation" width="100%" cellpadding="0" cellspacing="0" style="max-width: 560px; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 10px;">
                    <tr>
                        <td style="padding: 20px 24px; border-bottom: 1px solid #e2e8f0; font-weight: 700; font-size: 16px; color: #111827;">
                            {{ config('saas.platform_name') }}
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 24px; font-size: 14px; line-height: 1.6;">
                            @yield('content')
                        </td>
                    </tr>

                    <tr>
                        <td style="padding: 16px 24px; border-top: 1px solid #e2e8f0; font-size: 12px; color: #6b7280;">
                            @lang('saas::app.emails.footer')
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
