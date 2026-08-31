{{--
    Account-less audience capture for the schedule that PUBLISHED this page.

    Not the same thing as the header Follow button, which loops the event's claimed performers, is
    gated on isClaimed() and on config('app.hosted'), and whose $hasSubmitButton arm requires
    auth()->user(). On selfhost, and on any schedule that accepts submissions, this panel is the
    only capture surface a signed-out visitor ever sees.

    Plain <form method="POST">, so it works with JavaScript off: RoleSubscriberController::store()'s
    non-JSON branch redirects back() with a flash that layouts/app.blade.php toasts.

    Expects: $role, and optionally $panelClass to vary the wrapper between the event page (which
    supplies its own container) and the schedule page.

    Deliberately NOT rendered inside a Vue mount. The schedule name is user-controlled text and the
    app runs Vue's full build, so a mustache in the value would be compiled as a template.
--}}
@php
    $subscribePanelRole = $role ?? null;
@endphp

{{-- Signed-out only. Two reasons, and the second is a repo rule: an account holder should be
     following with their account rather than creating a parallel account-less row, and a honeypot
     must never be rendered into an authenticated page where a password manager could fill it. --}}
@if ($subscribePanelRole && ! auth()->user() && ! request()->embed && ! is_demo_mode() && ! is_demo_role($subscribePanelRole))
{{-- v-pre: the schedule name is user-controlled text rendered server-side, and the app runs Vue's
     full build, so anything Vue mounts has its markup compiled as a template. On the schedule page
     this panel currently sits 244 characters after #calendar-app closes - one careless move inside
     and an unguarded name like "{{constructor.constructor('...')()}}" would execute. v-pre is inert
     where there is no mount and correct where there is, so it costs nothing to keep. --}}
<div v-pre id="subscribe-panel" class="{{ $panelClass ?? 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl' }} p-6 sm:p-8">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
        {{ __('messages.subscribe_panel_heading') }}
    </h2>
    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __('messages.subscribe_panel_body', ['schedule' => $subscribePanelRole->name]) }}
    </p>

    <form method="POST"
        action="{{ route('role.audience.join', ['subdomain' => $subscribePanelRole->subdomain]) }}"
        class="mt-4 flex flex-col sm:flex-row gap-3">
        @csrf
        <x-honeypot />
        <input type="hidden" name="source" value="panel">

        <label for="subscribe_email_{{ $subscribePanelRole->id }}" class="sr-only">{{ __('messages.subscribe_your_email') }}</label>
        <input type="email" name="email" id="subscribe_email_{{ $subscribePanelRole->id }}" required
            autocomplete="email"
            placeholder="{{ __('messages.subscribe_your_email') }}"
            class="flex-1 min-w-0 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />

        <label for="subscribe_name_{{ $subscribePanelRole->id }}" class="sr-only">{{ __('messages.subscribe_your_name_optional') }}</label>
        <input type="text" name="name" id="subscribe_name_{{ $subscribePanelRole->id }}"
            autocomplete="name"
            placeholder="{{ __('messages.subscribe_your_name_optional') }}"
            class="flex-1 min-w-0 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />

        {{-- Accent colour, not brand blue: the guest portal is the schedule's surface, and every
             other primary button on these pages follows the schedule's accent. --}}
        <button type="submit"
            style="background-color: {{ $accentColor ?? '#4E81FA' }}; color: {{ $contrastColor ?? '#ffffff' }}"
            class="shrink-0 inline-flex items-center justify-center rounded-md px-4 py-2 text-sm font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-md">
            {{ $subscribePanelRole->customLabel('email_me_new_events') }}
        </button>
    </form>
</div>

@if (request()->boolean('subscribe'))
{{-- Arrived from a scanned QR code or a shared link. Scroll rather than anchor-jump so the
     heading is not pinned under the sticky header. --}}
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function () {
    var panel = document.getElementById('subscribe-panel');
    if (!panel) return;
    panel.scrollIntoView({ behavior: 'smooth', block: 'center' });
    var email = panel.querySelector('input[type="email"]');
    if (email) setTimeout(function () { email.focus({ preventScroll: true }); }, 400);
});
</script>
@endif
@endif
