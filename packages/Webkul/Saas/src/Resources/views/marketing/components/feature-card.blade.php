@props(['feature'])

<article class="feature-card" id="feature-{{ $feature['key'] }}">
    <span class="icon-badge">
        <x-marketing::icon :name="$feature['icon']" />
    </span>

    <h3>{{ $feature['title'] }}</h3>

    <p>{{ $feature['description'] }}</p>

    @if (! empty($feature['benefit']))
        <span class="benefit">
            <x-marketing::icon name="check" />
            {{ $feature['benefit'] }}
        </span>
    @endif
</article>
