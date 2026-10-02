{{--
    The button's address written out, for a client that strips or fails to render the button.
    Always left to right, and allowed to break anywhere so a long signed URL never widens the card.
--}}
@aware(['theme' => null])
@props(['href', 'intro' => null])
@php($theme ??= \App\Utils\EmailTheme::account())
<p class="es-ink-3" style="margin: 0 0 16px; font-size: 13px; line-height: 20px; color: #64748b; word-break: break-all;">@if (filled($intro)){{ $intro }}<br>@endif<a href="{{ $href }}" target="_blank" rel="noopener" dir="ltr" class="es-link" style="color: {{ $theme->accentInk }}; text-decoration: underline; word-break: break-all;">{{ $href }}</a></p>
