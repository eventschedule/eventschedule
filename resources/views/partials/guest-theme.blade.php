{{-- A schedule's colours as CSS custom properties on the guest layout's body (App\Utils\GuestTheme):
     --es-accent, --es-accent-text, --es-accent-edge, --es-accent-readable, --es-accent-tint and
     --es-glow, with their dark values under .dark.

     Printed BEFORE the layout's own style block, which ends with the owner's custom CSS, so an
     owner's rule for any of them still wins. Every value is a colour GuestTheme made, never the
     owner's own string. A place that sets --es-accent on an element of its own (the list's
     reveal animations, the booking pages) keeps doing so and is unaffected.

     $role may be null (a ticket whose schedule is gone): the default accent then. --}}
<style {!! nonce_attr() !!}>
{!! \App\Utils\GuestTheme::for(($role ?? null) ? \App\Utils\GuestTheme::lookRole($role, $otherRole ?? null, $selectedGroup ?? null) : null)->css() !!}
</style>
