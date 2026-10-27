@props(['type'])

@php
    $rows = trans('saas::marketing.features.previews.'.$type.'.rows');
    $rows = is_array($rows) ? $rows : [];
@endphp

<div class="showcase-card" style="margin-top: 20px;">
    <h3>@lang('saas::marketing.features.previews.'.$type.'.title')</h3>

    <p>@lang('saas::marketing.features.previews.'.$type.'.description')</p>

    @if ($type === 'mobile')
        <div class="phone-ui" aria-hidden="true">
            <span class="ph-img"></span>
            <span class="ph-line"></span>
            <span class="ph-line short"></span>
            <span class="ph-btn"></span>
        </div>
    @else
        <div class="mini-ui" aria-hidden="true">
            @foreach ($rows as $row)
                <div class="mini-row">
                    <span class="swatch"></span>
                    <span class="grow">{{ $row }}</span>
                    <span class="pill">@lang('saas::marketing.features.previews.'.$type.'.status')</span>
                </div>
            @endforeach
        </div>
    @endif
</div>
