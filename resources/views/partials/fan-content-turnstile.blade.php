{{--
    One shared Turnstile widget for every fan-content form on the page.

    Include once, outside any form: the submit forms repeat per event part, so a widget
    inside each would mean dozens per page, and Turnstile only auto-renders elements that
    are in the DOM at load. This sits in the DOM from the start and its token is copied
    into whichever form is submitted (see partials.fan-content-guest-fields).
--}}
@php $fanGuestRole = $role ?? null; @endphp
@if ($fanGuestRole && ! auth()->check() && ! $fanGuestRole->fan_content_require_account && \App\Utils\TurnstileUtils::isEnabled())
@once
<div id="fan-content-turnstile" class="mt-2">
    <x-turnstile />
</div>
<script {!! nonce_attr() !!}>
    document.addEventListener('submit', function (e) {
        const form = e.target;
        if (!form || !form.querySelector) return;

        const token = form.querySelector('.fan-content-turnstile-token');
        if (!token) return;

        const widget = document.querySelector('#fan-content-turnstile [name="cf-turnstile-response"]');
        if (widget) token.value = widget.value;
    }, true);
</script>
@endonce
@endif
