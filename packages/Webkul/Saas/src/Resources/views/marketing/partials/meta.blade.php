<title>{{ $meta['title'] }}</title>

<meta name="description" content="{{ $meta['description'] }}">

<link rel="canonical" href="{{ $meta['canonical'] }}">

<meta name="robots" content="@yield('robots', 'index,follow')">

<meta property="og:type" content="website">

<meta property="og:site_name" content="{{ $meta['platform'] }}">

<meta property="og:title" content="{{ $meta['title'] }}">

<meta property="og:description" content="{{ $meta['description'] }}">

<meta property="og:url" content="{{ $meta['canonical'] }}">

<meta property="og:locale" content="{{ ($marketingLocale ?? app()->getLocale()) === 'ar' ? 'ar_AR' : 'en_US' }}">

<meta name="twitter:card" content="summary">

<meta name="twitter:title" content="{{ $meta['title'] }}">

<meta name="twitter:description" content="{{ $meta['description'] }}">

<link rel="alternate" hreflang="en" href="{{ $meta['canonical'] }}">

<link rel="alternate" hreflang="ar" href="{{ $meta['canonical'] }}">

<link rel="alternate" hreflang="x-default" href="{{ $meta['canonical'] }}">

@stack('marketing-meta')
