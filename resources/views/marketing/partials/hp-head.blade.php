{{-- The house style's typeface (see partials/hp-kit): one variable file for the Latin alphabet,
     from the fonts the app already bundles, never a CDN. The file's name is read out of the
     bundled stylesheet rather than typed here, so re-downloading the fonts cannot leave a
     preload pointing at a file that is gone. Printed by layouts/marketing for a page that
     passes :hp="true". --}}
@php
    $hpFontDir = 'vendor/fonts/Red_Hat_Display/';
    $hpFontCss = (string) @file_get_contents(public_path($hpFontDir . 'font.css'));
    $hpFontFile = preg_match('~/\* latin \*/.*?url\(([^)]+\.woff2)\)~s', $hpFontCss, $hpFontMatch) ? $hpFontMatch[1] : null;
@endphp
@if ($hpFontFile)
    <link rel="preload" as="font" type="font/woff2" href="{{ asset($hpFontDir . $hpFontFile) }}" crossorigin>
@endif
@if (font_stylesheet_url('Red Hat Display'))
    <link rel="stylesheet" href="{{ font_stylesheet_url('Red Hat Display') }}">
@endif
