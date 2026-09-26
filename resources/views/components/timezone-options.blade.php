@props(['selected' => null])
{{--
    The <option>s of a timezone <select>, with the stored value selected.

    The stored value is compared in its canonical form: a user or schedule saved from a browser that
    reported a backward-compat alias (Chrome: Asia/Calcutta) matched no option, so the browser fell
    back to the first one and any save silently rewrote the timezone to Africa/Abidjan. A usable zone
    that has no listed name at all (Etc/GMT-3) gets an option of its own, so it round-trips too.
--}}
@php
    $listedTimezones = \App\Utils\TimezoneUtils::listed();
    $selectedTimezone = \App\Utils\TimezoneUtils::canonicalize(is_string($selected) ? $selected : null);
@endphp
@if ($selectedTimezone && ! in_array($selectedTimezone, $listedTimezones, true))
<option value="{{ $selectedTimezone }}" selected>{{ $selectedTimezone }}</option>
@endif
@foreach ($listedTimezones as $timezone)
<option value="{{ $timezone }}" @selected($selectedTimezone === $timezone)>{{ $timezone }}</option>
@endforeach
