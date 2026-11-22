{{-- A schedule's colours as CSS custom properties on the guest layout's body (App\Utils\GuestTheme):
     --es-accent, --es-accent-text, --es-accent-edge, --es-accent-readable, --es-accent-tint and
     --es-glow, with their dark values under .dark.

     Printed BEFORE the layout's own style block, which ends with the owner's custom CSS, so an
     owner's rule for any of them still wins. Every value is a colour GuestTheme made, never the
     owner's own string. A place that sets --es-accent on an element of its own (the list's
     reveal animations, the booking pages) keeps doing so and is unaffected.

     $role may be null (a ticket whose schedule is gone): the default accent then. --}}
@php
    $guestTheme = \App\Utils\GuestTheme::for(($role ?? null) ? \App\Utils\GuestTheme::lookRole($role, $otherRole ?? null, $selectedGroup ?? null) : null);
    // A palette the owner picked (Style > Background > Page style) lays its own ground and inks
    // over the accent-tinted ones; never in an embed, which keeps the look of the site it is on.
    $guestPalette = ($role ?? null) && ! request()->embed ? \App\Utils\GuestTheme::pagePalette($role, $otherRole ?? null, $event ?? null) : null;
@endphp
<style {!! nonce_attr() !!}>
{!! $guestTheme->css() !!}
@if ($guestPalette)
{!! $guestTheme->paletteCss($guestPalette) !!}
@endif
</style>
