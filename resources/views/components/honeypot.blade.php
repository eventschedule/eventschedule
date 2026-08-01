{{--
    Hidden decoy field for bot filtering. A real visitor never sees or focuses it, so a
    non-empty value means a bot. Checked server-side with HoneypotUtils::isTripped().

    Pair every use with a check in the controller, and make the bail match what the
    surface can display: guest pages render session('error'), but x-auth-layout renders
    only per-field errors, so those must throw a ValidationException instead.

    No id attribute: role/edit.blade.php has a real id="website" field that Dusk selects
    by id, and some pages render this more than once (two-factor-challenge has two forms).

    display:none is inline rather than only Tailwind's .hidden so the field stays hidden
    even if the stylesheet fails to load, which would otherwise show a stray text input.

    Pass vmodel to bind it into a Vue app, for forms whose payload is built by hand:
    <x-honeypot vmodel="website" /> then send website: this.website with the request.
--}}
@props(['vmodel' => null])
<div style="display:none" aria-hidden="true">
    <input type="text" name="{{ \App\Utils\HoneypotUtils::FIELD }}" value=""
        tabindex="-1" autocomplete="off"@if ($vmodel) v-model="{{ $vmodel }}"@endif>
</div>
