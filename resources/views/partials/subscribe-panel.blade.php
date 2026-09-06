{{--
    Account-less audience capture for the schedule that PUBLISHED this page.

    Not the same thing as the header Follow button, which loops the event's claimed performers, is
    gated on isClaimed() and on config('app.hosted'), and whose $hasSubmitButton arm requires
    auth()->user(). On selfhost, and on any schedule that accepts submissions, this panel is the
    only capture surface a signed-out visitor ever sees.

    Plain <form method="POST">, so it works with JavaScript off: RoleSubscriberController::store()'s
    non-JSON branch redirects back to #subscribe-panel with a flash that this file renders INLINE.
    It used to rely on the layout's Toastify toast, which is invisible without JavaScript and, with
    it, is a three-second bar at the top of a page whose panel is two thousand pixels further down
    and still showing an empty form.

    Expects: $role, and optionally $panelClass to vary the wrapper between the event page (which
    supplies its own container) and the schedule page.

    Deliberately NOT rendered inside a Vue mount. The schedule name is user-controlled text and the
    app runs Vue's full build, so a mustache in the value would be compiled as a template.
--}}
@php
    $subscribePanelRole = $role ?? null;
    $subscribeDone = $subscribePanelRole && session('subscribe_done') === $subscribePanelRole->subdomain;
    $subscribeError = $subscribePanelRole && session('subscribe_error_for') === $subscribePanelRole->subdomain
        ? session('subscribe_error')
        : null;
@endphp

{{-- Signed-out only. Two reasons, and the second is a repo rule: an account holder should be
     following with their account rather than creating a parallel account-less row, and a honeypot
     must never be rendered into an authenticated page where a password manager could fill it. --}}
@if ($subscribePanelRole && ! auth()->user() && ! request()->embed && ! is_demo_mode() && ! is_demo_role($subscribePanelRole))
{{-- v-pre: the schedule name is user-controlled text rendered server-side, and the app runs Vue's
     full build, so anything Vue mounts has its markup compiled as a template. On the schedule page
     this panel currently sits 244 characters after #calendar-app closes - one careless move inside
     and an unguarded name like "{{constructor.constructor('...')()}}" would execute. v-pre is inert
     where there is no mount and correct where there is, so it costs nothing to keep.
     AudienceTemplateInjectionTest pins that it appears within 200 characters of the id. --}}
{{-- Padding lives INSIDE $panelClass, not beside it: the event page includes this within a
     container that already pads, and a hardcoded p-6 sm:p-8 there could only be fought with
     conflicting utilities whose winner is decided by stylesheet order. scroll-mt-24 is outside it
     so neither include site has to know about the fragment redirect, and tabindex="-1" is what
     makes that fragment actually move FOCUS rather than dumping a keyboard user back at <body>. --}}
<div v-pre id="subscribe-panel" tabindex="-1" role="region" aria-labelledby="subscribe-panel-heading"
    class="scroll-mt-24 {{ $panelClass ?? 'bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8' }}">
    <h2 id="subscribe-panel-heading" class="text-lg font-semibold text-gray-900 dark:text-gray-100">
        {{ __('messages.subscribe_panel_heading') }}
    </h2>
    {{-- v-pre again, on the element that actually carries the name. The wrapper's v-pre already
         covers this subtree, but the panel's attributes and heading push this line past the
         400-character window AudienceTemplateInjectionTest looks back through - and a guard that
         only a heuristic can see is one refactor away from being silently lost. --}}
    <p v-pre class="mt-1 text-sm text-gray-600 dark:text-gray-400">
        {{ __('messages.subscribe_panel_body', ['schedule' => $subscribePanelRole->name]) }}
    </p>

    @if ($subscribeDone)
    {{-- Replaces the form rather than sitting above it. An empty form under "check your email"
         invites a second submit, which is rate limited and sends a second email. --}}
    <div role="status" class="mt-4 max-w-4xl flex items-start gap-3 rounded-lg border border-green-200 dark:border-green-700/50 bg-green-50 dark:bg-green-500/10 p-4">
        <svg class="h-5 w-5 shrink-0 mt-0.5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <div>
            <p class="text-sm font-semibold text-green-800 dark:text-green-300">
                {{ __('messages.subscribe_done_heading') }}
            </p>
            {{-- Deliberately does NOT name the address, even though it is the visitor's own and
                 would help them spot a typo. CLAUDE.md's rule is that a follower email never
                 appears on a guest-facing surface, and this panel is one;
                 RoleSubscriberTest::test_subscriber_emails_never_reach_a_guest_surface pins it.
                 The "use a different address" link below covers the typo case instead. --}}
            <p class="mt-1 text-sm text-green-700 dark:text-green-400">
                {{ __('messages.subscribe_done_body') }}
            </p>
            <p class="mt-1 text-xs text-green-700/80 dark:text-green-400/80">
                {{ __('messages.subscribe_done_note') }}
            </p>
            {{-- The no-JS way back to the form: ?subscribe=1 re-renders it and the deep-link script
                 below scrolls and focuses it. --}}
            <p class="mt-2 text-xs">
                <x-link href="{{ request()->fullUrlWithQuery(['subscribe' => 1]) }}">{{ __('messages.subscribe_use_another_email') }}</x-link>
            </p>
        </div>
    </div>
    @else
    {{-- max-w-4xl is what lets the schedule page's wrapper track the calendar's width: that
         card runs to the full container in calendar view, and both inputs below are flex-1
         min-w-0, so without a cap here each would stretch to roughly 650px. The heading, body and
         the cadence/privacy footnote still span the card - only the form slot is capped. On the
         event page the column is narrower than 56rem, so this is a no-op there. --}}
    <form method="POST"
        action="{{ route('role.audience.join', ['subdomain' => $subscribePanelRole->subdomain]) }}"
        class="mt-4 max-w-4xl flex flex-col sm:flex-row gap-3">
        @csrf
        <x-honeypot />
        <input type="hidden" name="source" value="panel">

        {{-- Visible labels, not sr-only + placeholder. A placeholder is the only label these fields
             had, and it disappears the moment the visitor types - taking "(optional)" with it,
             exactly when that matters. --}}
        <div class="flex-1 min-w-0">
            <label for="subscribe_email_{{ $subscribePanelRole->id }}" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('messages.subscribe_your_email') }}
            </label>
            {{-- session(), not old(): this page's ticket and RSVP forms also post a field called
                 `email`, so old('email') would cross-fill between them. --}}
            <input type="email" name="email" id="subscribe_email_{{ $subscribePanelRole->id }}" required
                autocomplete="email"
                value="{{ session('subscribe_email') }}"
                @if ($subscribeError) aria-invalid="true" aria-describedby="subscribe_error_{{ $subscribePanelRole->id }}" autofocus @endif
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
        </div>

        <div class="flex-1 min-w-0">
            <label for="subscribe_name_{{ $subscribePanelRole->id }}" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                {{ __('messages.subscribe_your_name_optional') }}
            </label>
            <input type="text" name="name" id="subscribe_name_{{ $subscribePanelRole->id }}"
                autocomplete="name"
                class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
        </div>

        {{-- Accent colour, not brand blue: the guest portal is the schedule's surface, and every
             other primary button on these pages follows the schedule's accent. sm:self-end keeps it
             on the inputs' baseline now that they carry labels. --}}
        <button type="submit"
            style="background-color: {{ $accentColor ?? '#4E81FA' }}; color: {{ $contrastColor ?? '#ffffff' }}"
            class="shrink-0 sm:self-end inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-md">
            {{ $subscribePanelRole->customLabel('email_me_new_events') }}
        </button>
    </form>

    @if ($subscribeError)
    <p id="subscribe_error_{{ $subscribePanelRole->id }}" role="alert" class="mt-2 max-w-4xl text-sm text-red-600 dark:text-red-400">
        {{ $subscribeError }}
    </p>
    @endif
    @endif

    {{-- Cadence first, then what the schedule gets. The cadence line answers the bigger objection
         and it is the concrete reassurance that replaces the per-event promise this copy used to
         make; it lives in one block with the privacy note so the footer stays two lines rather than
         three. subscription_confirm_cadence is deliberately vague about the number, because
         usage.audience_announcement_min_hours is configurable. --}}
    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        {{ __('messages.subscription_confirm_cadence') }}
        {{ __('messages.subscribe_privacy_note') }}
        <x-link href="{{ policy_url('privacy') }}" target="_blank">{{ __('messages.privacy_policy') }}</x-link>
    </p>

    {{-- There is no second path any more. This used to offer "Prefer an account? Sign up and follow
         instead", which was confusing for the good reason that it was a choice between a thing and
         the same thing: RoleSubscriberController::linkAccount() has minted an account on confirm
         since bdf545417, and /sub/done then offers a password for it.

         willCreateAccountOnConfirm() is the same predicate linkAccount() refuses on, which is the
         only way a sentence like this stays true. No border-t - the rule that was here separated
         an alternative, and a statement of what happens is not one. --}}
    @if ($subscribePanelRole->willCreateAccountOnConfirm())
    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
        {{ __('messages.subscribe_account_note') }}
    </p>
    @endif
</div>

@if (request()->boolean('subscribe'))
{{-- Arrived from a scanned QR code or a shared link. Scroll rather than anchor-jump so the
     heading is not pinned under the sticky header. --}}
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function () {
    var panel = document.getElementById('subscribe-panel');
    if (!panel) return;
    // Nobody asked for this scroll, so honour the OS preference. The focus move still happens;
    // only the animation is the part that is disorienting under magnification.
    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
    panel.scrollIntoView({ behavior: reduce ? 'auto' : 'smooth', block: 'center' });
    var email = panel.querySelector('input[type="email"]');
    if (email) setTimeout(function () { email.focus({ preventScroll: true }); }, 400);
});
</script>
@endif
@endif
