@props(['color' => 'white'])

@php
    $platform = \App\Utils\UrlUtils::detectPlatform(trim($slot));
    $svgPath = \App\Utils\UrlUtils::getSocialSvgPath($platform);
@endphp

<svg {{ $attributes->merge(['class' => '']) }} preserveAspectRatio="none"
    fill="none" viewBox="0 0 24 24" aria-hidden="true">
    <path fill="{{ $color }}" d="{{ $svgPath }}"/>
</svg>
