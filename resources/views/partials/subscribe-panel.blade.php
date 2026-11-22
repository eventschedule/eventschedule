{{--
    Account-less audience capture for the schedule that PUBLISHED this page.

    Not the same thing as the header Follow button, which loops the event's claimed performers, is
    gated on isClaimed() and on config('app.hosted'), and whose $hasSubmitButton arm requires
    auth()->user(). On selfhost, and on any schedule that accepts submissions, this panel is the
    only capture surface a signed-out visitor ever sees.

    Plain <form method="POST">, so it works with JavaScript off: RoleSubscriberController::store()'s
    non-JSON branch redirects back to #gp-subscribe with a flash that this file renders INLINE.
    It used to rely on the layout's Toastify toast, which is invisible without JavaScript and, with
    it, is a three-second bar at the top of a page whose panel is two thousand pixels further down
    and still showing an empty form.

    Expects: $role, and optionally $panelClass to vary the wrapper between the event page (which
    supplies its own container) and the schedule page.

    $subscribeEmbed (default false) is the signup form a schedule embeds on its own website
    (role/show-guest-subscribe-embed). That page runs in a cross-site iframe with no session, so it
    posts to the CSRF-exempt role.audience.join_embed and its outcome arrives in
    $subscribeEmbedState rather than the flash. See RoleSubscriberController::storeEmbed().

    Deliberately NOT rendered inside a Vue mount. The schedule name is user-controlled text and the
    app runs Vue's full build, so a mustache in the value would be compiled as a template.
--}}
@php
    $subscribePanelRole = $role ?? null;
    $subscribeEmbed = $subscribeEmbed ?? false;
    $subscribeEmbedState = $subscribeEmbedState ?? [];

    if ($subscribeEmbed) {
        $subscribeDone = (bool) ($subscribeEmbedState['done'] ?? false);
        $subscribeError = $subscribeEmbedState['error'] ?? null;
        $subscribeEmailValue = $subscribeEmbedState['email'] ?? '';
        $subscribeNameValue = $subscribeEmbedState['name'] ?? '';
        $subscribeErrorFieldValue = $subscribeEmbedState['errorField'] ?? 'email';
        // A pinned theme (?dark=true|false) has to survive the POST and the way back.
        // theme-script reads it from window.location.search, so it rides in the URLs themselves;
        // a hidden field would never reach the script.
        $subscribeDarkParam = in_array(request()->query('dark'), ['true', 'false'], true) ? request()->query('dark') : null;
        $subscribeAgainUrl = route('role.view_guest', array_filter([
            'subdomain' => $subscribePanelRole->subdomain,
            'embed' => 'true',
            'form' => 'subscribe',
            'dark' => $subscribeDarkParam,
        ]));
        $subscribeFormAction = route('role.audience.join_embed', array_filter([
            'subdomain' => $subscribePanelRole->subdomain,
            'dark' => $subscribeDarkParam,
        ]));
    } else {
        $subscribeDone = $subscribePanelRole && session('subscribe_done') === $subscribePanelRole->subdomain;
        $subscribeError = $subscribePanelRole && session('subscribe_error_for') === $subscribePanelRole->subdomain
            ? session('subscribe_error')
            : null;
        // session(), not old(): this page's ticket and RSVP forms also post a field called
        // `email`, so old('email') would cross-fill between them.
        $subscribeEmailValue = session('subscribe_email');
        $subscribeNameValue = session('subscribe_name');
        $subscribeErrorFieldValue = session('subscribe_error_field', 'email');
        $subscribeAgainUrl = request()->fullUrlWithQuery(['subscribe' => 1]);
        $subscribeFormAction = $subscribePanelRole
            ? route('role.audience.join', ['subdomain' => $subscribePanelRole->subdomain])
            : null;
    }

    // The owner's switch (Engagement > Mailing list, on by default), with two exceptions that both
    // outrank it:
    //  - ?subscribe=1 is what the Followers tab's QR code and "Your follow link" open. The owner
    //    printed or shared that on purpose, so it keeps working with the panel otherwise hidden.
    //  - A result waiting to be shown. role.audience.join stays open whatever this says (the
    //    Follow modal posts to it too), so a submission from a tab opened before the switch, or
    //    from a custom domain whose cross-origin referer drops ?subscribe=1, still succeeds - and
    //    this panel is the only thing that renders its outcome.
    //
    // !== false, not a truthy test: only an explicit off hides the panel. A Role that was never
    // read from the table (or was read before the column existed, between a deploy and its
    // migration) has no value at all, and the column's default is on.
    //
    // The embedded form outranks it too: pasting the snippet onto a website is the owner asking
    // for the form, exactly as sharing ?subscribe=1 is.
    $subscribePanelOn = $subscribePanelRole
        && ($subscribeEmbed
            || $subscribePanelRole->show_subscribe_panel !== false
            || request()->boolean('subscribe')
            || $subscribeDone
            || $subscribeError);
@endphp

{{-- Signed-out only. Two reasons, and the second is a repo rule: an account holder should be
     following with their account rather than creating a parallel account-less row, and a honeypot
     must never be rendered into an authenticated page where a password manager could fill it. --}}
{{-- The embedded form drops both of the first two conditions. It IS the embed, and inside a
     cross-site iframe nobody is signed in - while the owner's own preview in the Embed dialog is,
     and would otherwise be blank. --}}
@if ($subscribePanelOn && ($subscribeEmbed || (! auth()->user() && ! request()->embed)) && ! is_demo_mode() && ! is_demo_role($subscribePanelRole))
{{-- v-pre: the schedule name is user-controlled text rendered server-side, and the app runs Vue's
     full build, so anything Vue mounts has its markup compiled as a template. On the schedule page
     this panel sits just after #calendar-app closes - one careless move inside and an unguarded
     name like "{{constructor.constructor('...')()}}" would execute. v-pre is inert where there is
     no mount and correct where there is, so it costs nothing to keep.
     AudienceTemplateInjectionTest pins that it appears within 200 characters of the id. --}}
{{-- Padding lives INSIDE $panelClass, not beside it: each include site supplies its own card.
     scroll-mt-24 is outside it so neither include site has to know about the fragment redirect,
     and tabindex="-1" is what makes that fragment actually move FOCUS rather than dumping a
     keyboard user back at <body>. --}}
<div v-pre id="gp-subscribe" tabindex="-1" role="region" aria-labelledby="gp-subscribe-heading"
    class="gk-sub scroll-mt-24 {{ $panelClass ?? 'gk-card gk-band' }}">
    <div class="gk-sub-head">
        <h2 id="gp-subscribe-heading" class="gk-sub-title">{{ __('messages.subscribe_panel_heading') }}</h2>
        {{-- v-pre again, on the element that actually carries the name: a guard that only a
             heuristic can see is one refactor away from being silently lost. --}}
        <p v-pre class="gk-sub-lead">{{ __('messages.subscribe_panel_body', ['schedule' => $subscribePanelRole->name]) }}</p>
    </div>

    @if ($subscribeDone)
    {{-- Replaces the form rather than sitting above it. An empty form under "check your email"
         invites a second submit, which is rate limited and sends a second email. --}}
    <div role="status" class="gk-note gk-note-ok">
        <svg class="gk-note-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
        </svg>
        <div class="gk-note-body">
            <p style="font-weight: 650">{{ __('messages.subscribe_done_heading') }}</p>
            {{-- Deliberately does NOT name the address, even though it is the visitor's own and
                 would help them spot a typo. CLAUDE.md's rule is that a follower email never
                 appears on a guest-facing surface, and this panel is one;
                 RoleSubscriberTest::test_subscriber_emails_never_reach_a_guest_surface pins it.
                 The "use a different address" link below covers the typo case instead. --}}
            <p style="margin-top: .25rem">{{ __('messages.subscribe_done_body') }}</p>
            <p style="margin-top: .25rem; font-size: .8125rem">{{ __('messages.subscribe_done_note') }}</p>
            {{-- The no-JS way back to the form: ?subscribe=1 re-renders it and the deep-link script
                 below scrolls and focuses it. --}}
            <p style="margin-top: .5rem; font-size: .8125rem">
                <x-link href="{{ $subscribeAgainUrl }}">{{ __('messages.subscribe_use_another_email') }}</x-link>
            </p>
        </div>
    </div>
    @else
    @php
        // Which input the rejection is about. respond() flashes it; the fallback keeps the
        // pre-existing behaviour for bails that name no field (honeypot, rate limit, mailer).
        $subscribeInvalidField = $subscribeError ? $subscribeErrorFieldValue : null;
    @endphp
    {{-- max-w-4xl caps the row: the schedule page's band runs as wide as its calendar, and both
         fields grow, so without a cap each would stretch to roughly 650px. --}}
    <form method="POST"
        action="{{ $subscribeFormAction }}"
        class="gk-sub-form max-w-4xl">
        @csrf
        <x-honeypot />
        @if ($subscribeEmbed)
        {{-- embed=true keeps the response frameable (SecurityHeaders). The language rides along
             because SetUserLanguage only reads ?lang= on a GET and the POST has no session. --}}
        <input type="hidden" name="embed" value="true">
        <input type="hidden" name="source" value="embed">
        <input type="hidden" name="lang" value="{{ app()->getLocale() }}">
        @else
        <input type="hidden" name="source" value="panel">
        @endif

        {{-- Visible labels, not sr-only + placeholder. A placeholder is the only label these fields
             had, and it disappears the moment the visitor types. --}}
        <div class="gk-sub-field">
            <label for="subscribe_email_{{ $subscribePanelRole->id }}" class="gk-sub-label">
                {{ __('messages.subscribe_your_email') }}
            </label>
            {{-- $subscribeEmailValue is the session flash on the guest page, not old() - see the
                 @php block at the top. --}}
            <input type="email" name="email" id="subscribe_email_{{ $subscribePanelRole->id }}" required
                autocomplete="email" dir="ltr"
                value="{{ $subscribeEmailValue }}"
                @if ($subscribeInvalidField === 'email') aria-invalid="true" aria-describedby="subscribe_error_{{ $subscribePanelRole->id }}" autofocus @endif
                class="gk-sub-input" />
        </div>

        <div class="gk-sub-field">
            <label for="subscribe_name_{{ $subscribePanelRole->id }}" class="gk-sub-label">
                {{ __('messages.subscribe_your_name') }}
            </label>
            {{-- Required, like the address. The schedule owner sees this name on the followers tab
                 and the newsletter segments, and "(optional)" left most rows there identified only
                 by an email address.

                 RoleSubscriberController::store() is what actually enforces it. The modal in
                 follow-consent-modal.blade.php carries this same attribute but gets nothing from
                 it: its inputs are not inside a <form>, so constraint validation never runs and
                 only its own JS guard plus the server stand between an empty name and a request.

                 Repopulated for the same reason the address is: `required` means the browser
                 blocks resubmission until this box is filled, so dropping what was typed turns
                 any rejection into retyping. --}}
            <input type="text" name="name" id="subscribe_name_{{ $subscribePanelRole->id }}" required
                autocomplete="name"
                value="{{ $subscribeNameValue }}"
                @if ($subscribeInvalidField === 'name') aria-invalid="true" aria-describedby="subscribe_error_{{ $subscribePanelRole->id }}" autofocus @endif
                class="gk-sub-input" />
        </div>

        {{-- The schedule's colour, not brand blue: the guest portal is the schedule's surface. --}}
        <button type="submit" class="gk-sub-btn">
            {{ $subscribePanelRole->customLabel('email_me_new_events') }}
        </button>
    </form>

    @if ($subscribeError)
    <p id="subscribe_error_{{ $subscribePanelRole->id }}" role="alert" class="gk-sub-err">
        {{ $subscribeError }}
    </p>
    @endif
    @endif

    <div class="gk-sub-fine">
        {{-- Cadence first, then what the schedule gets. subscription_confirm_cadence is
             deliberately vague about the number, because usage.audience_announcement_min_hours is
             configurable. --}}
        <p>
            {{ __('messages.subscription_confirm_cadence') }}
            {{ __('messages.subscribe_privacy_note') }}
            <x-link href="{{ policy_url('privacy') }}" target="_blank">{{ __('messages.privacy_policy') }}</x-link>
        </p>

        {{-- willCreateAccountOnConfirm() is the same predicate linkAccount() refuses on, which is
             the only way a sentence like this stays true. --}}
        @if ($subscribePanelRole->willCreateAccountOnConfirm())
        <p>{{ __('messages.subscribe_account_note') }}</p>
        @endif
    </div>

    {{-- The no-address alternative, and for a lot of people the better one: the schedule's
         calendar feed (FeedController::icalFeed, public and unauthenticated), which updates itself
         when a date moves. It costs no email address, no consent and nothing against the
         platform's sending reputation. The feed already restricts to accepted, upcoming,
         non-private, non-draft, non-cancelled, password-free events. --}}
    {{-- v-pre: the schedule name is user-controlled and this panel may sit inside a Vue mount.
         Blade's {{ }} escaping does NOT stop Vue compiling a mustache in the VALUE.
         AudienceTemplateInjectionTest fails the build if it goes missing. --}}
    {{-- Not in the embedded form, which is kept to the one ask so it stays short on someone
         else's page; the calendar feed is a click away on the schedule itself. --}}
    @if (! $subscribeEmbed)
    <p v-pre class="gk-sub-feed">
        <a href="{{ route('feed.ical', ['subdomain' => $subscribePanelRole->subdomain]) }}">{{ __('messages.event_interest_subscribe_feed', ['schedule' => $subscribePanelRole->translatedName()]) }}</a>
        <span class="ms-1">{{ __('messages.event_interest_subscribe_feed_help') }}</span>
    </p>
    @endif
</div>

{{-- The band's own look (the mock's "stay updated" band): a heading, one line, the address, the
     name and the button in one row from a tablet up, the fine print small below. Every colour is a
     kit token, which the stage (partials/guest-stage-styles) re-points at its own inks, so the band
     reads on the event page's sheet, the schedule page's and the embedded form's alike. Printed
     after the panel, outside any Vue mount (the schedule page's #calendar-app closes above it),
     and never between the panel and the wrapper before it, which RoleSubscriberTest reads back
     from the panel's id. --}}
<style {!! nonce_attr() !!}>
    .gk-sub { display: flex; flex-direction: column; gap: 1.125rem; color: var(--gk-ink); }
    .gk-sub-head { display: flex; flex-direction: column; gap: .5rem; }
    .gk-sub-title { margin: 0; font-size: clamp(1.375rem, 1.15rem + .8vw, 1.625rem); font-weight: 750; line-height: 1.2; letter-spacing: -.01em; color: var(--gk-ink); }
    .gk-sub-lead { margin: 0; max-width: 62ch; font-size: 1rem; line-height: 1.55; color: var(--gk-ink-2); text-wrap: pretty; }
    .gk-sub-form { display: flex; flex-wrap: wrap; align-items: flex-end; gap: .75rem; margin: 0; }
    .gk-sub-field { display: flex; flex: 1 1 11.25rem; flex-direction: column; gap: .375rem; min-width: 0; }
    .gk-sub-label { font-size: .875rem; font-weight: 500; color: var(--gk-ink-2); }
    .gk-sub .gk-sub-input { box-sizing: border-box; width: 100%; height: 3.125rem; padding: 0 .875rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-stage, var(--gk-solid)); color: var(--gk-ink); font: inherit; font-size: 1rem; box-shadow: none; }
    .gk-sub .gk-sub-input:focus { border-color: var(--es-accent-readable); outline: none; box-shadow: 0 0 0 1px var(--es-accent-readable); }
    .gk-sub .gk-sub-input[aria-invalid="true"] { border-color: var(--gk-bad); }
    .gk-sub-btn { flex: 0 0 auto; height: 3.125rem; padding: 0 1.5rem; border: 1px solid var(--es-accent-edge, transparent); border-radius: .75rem; background: var(--es-accent); color: var(--es-accent-text); font: inherit; font-size: 1rem; font-weight: 700; cursor: pointer; transition: filter var(--gk-swap), transform var(--gk-press); }
    .gk-sub-btn:hover { filter: brightness(1.08); }
    .gk-sub-btn:active { transform: scale(.985); }
    .gk-sub-btn:focus-visible { outline: 2px solid var(--gk-ink); outline-offset: 2px; }
    @media (max-width: 39.99rem) { .gk-sub-btn { flex: 1 1 100%; } }
    .gk-sub-fine { display: flex; flex-direction: column; gap: .625rem; font-size: .8125rem; line-height: 1.6; color: var(--gk-ink-3); }
    .gk-sub-fine p { margin: 0; text-wrap: pretty; }
    .gk-sub-feed { margin: 0; padding-top: 1rem; border-top: 1px solid var(--gk-line); font-size: .875rem; line-height: 1.6; color: var(--gk-ink-3); text-wrap: pretty; }
    .gk-sub-feed a { font-weight: 600; color: var(--es-accent-readable); text-decoration: underline; text-underline-offset: 3px; }
    .gk-sub-err { margin: 0; font-size: .9375rem; font-weight: 600; color: var(--gk-bad); }
    .gk-sub .gk-note p { margin: 0; }
</style>

@if (request()->boolean('subscribe') && ! $subscribeEmbed)
{{-- Arrived from a scanned QR code or a shared link. Scroll rather than anchor-jump so the
     heading is not pinned under the sticky header. --}}
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function () {
    var panel = document.getElementById('gp-subscribe');
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
