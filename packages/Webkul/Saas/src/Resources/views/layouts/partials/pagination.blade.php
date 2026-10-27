@if ($paginator->hasPages())
    <nav class="pagination-nav" role="navigation" aria-label="Pagination">
        @if ($paginator->onFirstPage())
            <span class="page-link prev disabled" aria-disabled="true">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])
            </span>
        @else
            <a class="page-link prev" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">
                @include('saas::layouts.partials.icon', ['name' => 'arrow-left'])
            </a>
        @endif

        @foreach ($elements as $element)
            @if (is_string($element))
                <span class="page-link dots">{{ $element }}</span>
            @endif

            @if (is_array($element))
                @foreach ($element as $page => $url)
                    @if ($page == $paginator->currentPage())
                        <span class="page-link current" aria-current="page">{{ $page }}</span>
                    @else
                        <a class="page-link" href="{{ $url }}">{{ $page }}</a>
                    @endif
                @endforeach
            @endif
        @endforeach

        @if ($paginator->hasMorePages())
            <a class="page-link next" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">
                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])
            </a>
        @else
            <span class="page-link next disabled" aria-disabled="true">
                @include('saas::layouts.partials.icon', ['name' => 'chevron-right'])
            </span>
        @endif
    </nav>
@endif
