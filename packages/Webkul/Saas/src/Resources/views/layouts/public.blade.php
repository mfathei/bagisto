<!DOCTYPE html>
<html
    lang="{{ app()->getLocale() }}"
    dir="{{ in_array(app()->getLocale(), ['ar', 'fa', 'he', 'ur'], true) ? 'rtl' : 'ltr' }}"
>
<head>
    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>@yield('title', config('saas.platform_name'))</title>

    @include('saas::layouts.styles')
</head>

<body>
    <header class="topbar">
        <span class="brand">{{ config('saas.platform_name') }}</span>

        @hasSection('nav')
            <nav>@yield('nav')</nav>
        @endif
    </header>

    <main class="container narrow">
        @if (session('success'))
            <div class="alert success">{{ session('success') }}</div>
        @endif

        @if (session('error'))
            <div class="alert error">{{ session('error') }}</div>
        @endif

        @if ($errors->any())
            <div class="alert error">
                <ul style="margin: 0; padding-inline-start: 18px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</body>
</html>
