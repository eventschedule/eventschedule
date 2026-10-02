{{-- An inline link in body copy: underlined, because colour alone does not mark a link (WCAG 1.4.1). muted is for the footer. The file has no trailing newline on purpose: one would print a space before the punctuation that follows the link. --}}
@aware(['theme' => null])
@props(['href', 'muted' => false])
@php($theme ??= \App\Utils\EmailTheme::account())
<a href="{{ $href }}" target="_blank" rel="noopener" class="{{ $muted ? 'es-foot' : 'es-link' }}" style="color: {{ $muted ? '#475569' : $theme->accentInk }}; text-decoration: underline;">{{ $slot }}</a>