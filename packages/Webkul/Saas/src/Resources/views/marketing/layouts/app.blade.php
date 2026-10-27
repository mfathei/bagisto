<!DOCTYPE html>

<html
    lang="{{ $marketingLocale ?? app()->getLocale() }}"
    dir="{{ $marketingDirection ?? 'ltr' }}"
    style="--brand: {{ $site['brand_color'] }}"
>
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    @include('saas::marketing.partials.meta')

    @include('saas::marketing.partials.styles')

    @stack('marketing-styles')
</head>

<body class="site">
    <a class="skip-link" href="#main">@lang('saas::marketing.common.skip_to_content')</a>

    @if (! empty($site['banner']))
        <div class="announcement" role="status">{{ $site['banner'] }}</div>
    @endif

    @include('saas::marketing.partials.nav')

    <main id="main">
        @if (session('success'))
            <div class="shell" style="padding-top: 20px;">
                <div class="alert success" role="status">{{ session('success') }}</div>
            </div>
        @endif

        @if (session('error'))
            <div class="shell" style="padding-top: 20px;">
                <div class="alert error" role="alert">{{ session('error') }}</div>
            </div>
        @endif

        @yield('content')
    </main>

    @include('saas::marketing.partials.footer')

    <script>
        document.querySelectorAll('.mobile-nav').forEach((menu) => {
            menu.querySelectorAll('a').forEach((link) => {
                link.addEventListener('click', () => {
                    menu.open = false;
                });
            });
        });

        document.addEventListener('pointerdown', (event) => {
            document.querySelectorAll('.mobile-nav[open]').forEach((menu) => {
                if (! menu.contains(event.target)) {
                    menu.open = false;
                }
            });
        });

        document.addEventListener('keydown', (event) => {
            if (event.key !== 'Escape') {
                return;
            }

            const menu = document.querySelector('.mobile-nav[open]');

            if (menu) {
                menu.open = false;
                menu.querySelector('summary').focus();
            }
        });
    </script>
</body>
</html>
