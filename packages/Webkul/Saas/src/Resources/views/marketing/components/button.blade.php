@props(['href' => null, 'variant' => 'primary', 'size' => null, 'type' => 'button'])

@php
    $classes = trim('btn btn-'.$variant.($size ? ' btn-'.$size : ''));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->class($classes) }}>{{ $slot }}</a>
@else
    <button type="{{ $type }}" {{ $attributes->class($classes) }}>{{ $slot }}</button>
@endif
