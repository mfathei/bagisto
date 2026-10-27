@props(['name' => 'check', 'size' => null])

@php
    $icons = [
        'storefront' => '<path d="M3 9.5 4.5 4h15L21 9.5"/><path d="M4 9.5V20h16V9.5"/><path d="M9 20v-5h6v5"/><path d="M3 9.5h18"/>',
        'catalog' => '<path d="m12 3 8 4.5v9L12 21l-8-4.5v-9L12 3Z"/><path d="m4 7.5 8 4.5 8-4.5"/><path d="M12 12v9"/>',
        'orders' => '<path d="M7 3h10a2 2 0 0 1 2 2v16l-3-2-2 2-2-2-2 2-3-2V5a2 2 0 0 1 2-2Z"/><path d="M9 8h6"/><path d="M9 12h6"/>',
        'inventory' => '<path d="M3 7h18v13H3z"/><path d="M3 7l2-4h14l2 4"/><path d="M10 11h4"/>',
        'customers' => '<path d="M16 20v-2a4 4 0 0 0-4-4H7a4 4 0 0 0-4 4v2"/><circle cx="9.5" cy="7" r="3.5"/><path d="M21 20v-2a4 4 0 0 0-3-3.87"/><path d="M16.5 3.5a3.5 3.5 0 0 1 0 6.9"/>',
        'payments' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="M3 10h18"/><path d="M7 15h4"/>',
        'analytics' => '<path d="M4 20V10"/><path d="M10 20V4"/><path d="M16 20v-7"/><path d="M22 20H2"/>',
        'domains' => '<circle cx="12" cy="12" r="9"/><path d="M3 12h18"/><path d="M12 3a15 15 0 0 1 0 18 15 15 0 0 1 0-18Z"/>',
        'team' => '<circle cx="9" cy="8" r="3.5"/><path d="M2.5 20v-1.5A5.5 5.5 0 0 1 8 13h2a5.5 5.5 0 0 1 5.5 5.5V20"/><path d="M17 4.5v5"/><path d="M14.5 7h5"/>',
        'languages' => '<path d="M3 5h10"/><path d="M8 3v2c0 4-2.5 7-5 8"/><path d="M4 9c1.5 2.5 4 4.5 7 5.5"/><path d="m13 21 4-10 4 10"/><path d="M14.5 17h5"/>',
        'mobile' => '<rect x="7" y="2" width="10" height="20" rx="2.5"/><path d="M11 18.5h2"/>',
        'subscriptions' => '<path d="M21 12a9 9 0 1 1-2.64-6.36"/><path d="M21 3v6h-6"/>',
        'check' => '<path d="m5 12 4 4L19 6"/>',
        'check-circle' => '<circle cx="12" cy="12" r="9"/><path d="m8.5 12 2.5 2.5 4.5-5"/>',
        'cross' => '<path d="M6 6l12 12"/><path d="M18 6 6 18"/>',
        'cross-circle' => '<circle cx="12" cy="12" r="9"/><path d="m9 9 6 6"/><path d="m15 9-6 6"/>',
        'arrow-right' => '<path d="M5 12h14"/><path d="m13 6 6 6-6 6"/>',
        'arrow-left' => '<path d="M19 12H5"/><path d="m11 18-6-6 6-6"/>',
        'mail' => '<rect x="3" y="5" width="18" height="14" rx="2"/><path d="m3 7 9 6 9-6"/>',
        'phone' => '<path d="M5 4h4l2 5-2.5 1.5a11 11 0 0 0 5 5L15 13l5 2v4a2 2 0 0 1-2 2A16 16 0 0 1 3 6a2 2 0 0 1 2-2Z"/>',
        'map-pin' => '<path d="M12 21s7-5.5 7-11a7 7 0 1 0-14 0c0 5.5 7 11 7 11Z"/><circle cx="12" cy="10" r="2.5"/>',
        'lock' => '<rect x="4" y="10" width="16" height="11" rx="2"/><path d="M8 10V7a4 4 0 0 1 8 0v3"/>',
        'sparkle' => '<path d="M12 3l1.8 5.2L19 10l-5.2 1.8L12 17l-1.8-5.2L5 10l5.2-1.8L12 3Z"/><path d="M19 16l.9 2.1L22 19l-2.1.9L19 22l-.9-2.1L16 19l2.1-.9L19 16Z"/>',
        'shield' => '<path d="M12 3 5 6v5c0 4.5 3 8.4 7 10 4-1.6 7-5.5 7-10V6l-7-3Z"/><path d="m9 12 2 2 4-4"/>',
        'rocket' => '<path d="M5 15c-1.5 1.5-2 5-2 5s3.5-.5 5-2"/><path d="M14 4c3.5-1 6 0 6 0s1 2.5 0 6c-1.2 4.2-5 8-8 10l-4-4c2-3 5.8-6.8 6-12Z"/><circle cx="15" cy="9" r="1.6"/>',
        'facebook' => '<path d="M14 8h3V4h-3a5 5 0 0 0-5 5v3H6v4h3v6h4v-6h3l1-4h-4V9a1 1 0 0 1 1-1Z"/>',
        'x' => '<path d="M4 4h4l4.5 6L17 4h3l-6.8 8.2L21 20h-4l-5-6.6L6.5 20H3l7.4-8.9L4 4Z"/>',
        'linkedin' => '<rect x="3" y="3" width="18" height="18" rx="3"/><path d="M8 10v7"/><path d="M8 7v.01"/><path d="M12 17v-4a2.5 2.5 0 0 1 5 0v4"/>',
        'instagram' => '<rect x="3" y="3" width="18" height="18" rx="5"/><circle cx="12" cy="12" r="4"/><path d="M17 7v.01"/>',
    ];
@endphp

<svg
    @if ($size) width="{{ $size }}" height="{{ $size }}" @endif
    class="icon-{{ $name }}"
    viewBox="0 0 24 24"
    fill="none"
    stroke="currentColor"
    stroke-width="1.8"
    stroke-linecap="round"
    stroke-linejoin="round"
    aria-hidden="true"
    focusable="false"
>{!! $icons[$name] ?? $icons['check'] !!}</svg>
