@props(['eyebrow' => null, 'title', 'description' => null, 'center' => false])

<div @class(['section-head', 'center' => $center])>
    @if ($eyebrow)
        <span class="eyebrow">{{ $eyebrow }}</span>
    @endif

    <h2>{{ $title }}</h2>

    @if ($description)
        <p>{{ $description }}</p>
    @endif
</div>
