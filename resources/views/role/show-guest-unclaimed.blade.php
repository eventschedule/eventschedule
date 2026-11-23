{{--
    The page for a schedule the app invented while somebody entered an event.

    Deliberately NOT role/show-guest.blade.php, which opens :ad-slot :banner-bar :cart all true and
    includes the subscribe panel, the follow modal and the 4,000-line Vue calendar. Every one of
    those belongs to a schedule owner, and this page exists precisely because there is not one.
    Making show-guest's slot conditional would also break PromotionSlotRenderTest, which walks the
    view tree looking for the ad-slot opt-in as a literal string and asserts the exact list of
    files carrying it - so do not write that opt-in here even inside a comment, which is a mistake
    this file has already made once.

    Everything the layout can monetize, personalise or attach a visitor to is left at its default of
    false. :no-index is passed explicitly rather than relied on: app-guest computes noindex from the
    verified-contact columns and user_id, and an admin verifying a placeholder's address by hand
    would otherwise be one click from indexing the whole population.

    The row's email and phone are NEVER printed. They were typed by a third party about another
    third party, show_email and show_phone both default to false, and this is a public URL.

    Order of information is the whole point: who made this, that it is unclaimed, where the dates
    came from, and only then the two things the person it describes might want to do. A visitor who
    is not the act needs the first three to correctly discount everything below them.
--}}
{{-- page-title, like both sibling views: a tab or a bookmark carries no disclosure strip, so
     without it the tab reads as the act's own name with no hint the page is unclaimed. --}}
<x-app-guest-layout :role="$role" :fonts="$fonts" :no-index="true" :page-title="__('messages.unclaimed')">

@php
    $accent = '#2563eb'; // blue-600. The schedule's own accent is not used: this is the platform
                         // speaking about a page it generated, not the schedule speaking. It also
                         // clears 4.5:1 against white in both colour modes, which the brand blue
                         // does not - the same reasoning components/needs-attention.blade.php uses.
    $canClaim = (bool) ($role->email || $role->phone);
    // Page direction follows the schedule's language, but a name does not have to be in it: an
    // English-language promoter listing a Hebrew act is the ordinary case here, and without a per
    // element dir that name renders left to right. Same treatment every other guest surface gives
    // user text - see role/show-guest.blade.php and event/show-guest.blade.php.
    $lang = $role->displayLanguageCode();
@endphp

@include('partials.guest-form-pages-styles')
<style {!! nonce_attr() !!}>
    {{-- The platform speaking about a page it made, so not the schedule's colour (see $accent). --}}
    .gk-pg-unclaimed .gk-pg-claim { padding: 1.25rem; }
    @media (min-width: 40rem) { .gk-pg-unclaimed .gk-pg-claim { padding: 1.5rem 1.75rem; } }
    .gk-pg-unclaimed .gk-pg-claim-body { color: var(--gk-ink-2); }
    .gk-pg-unclaimed .gk-pg-claim-icon { flex: none; width: 1.5rem; height: 1.5rem; margin-top: .125rem; color: var(--gk-ink-2); }
    .gk-pg-unclaimed .gk-pg-claim-go { background: {{ $accent }}; color: #ffffff; border-color: {{ $accent }}; }
    .gk-pg-unclaimed .gk-pg-events { margin: 0; padding: 0; list-style: none; border-top: 1px solid var(--gk-line); }
    .gk-pg-unclaimed .gk-pg-events li { display: flex; flex-direction: column; gap: .25rem; padding-block: 1rem; border-bottom: 1px solid var(--gk-line); }
    .gk-pg-unclaimed .gk-pg-event-name { font-size: 1.1875rem; font-weight: 650; line-height: 1.3; color: var(--gk-ink); text-decoration: none; }
    .gk-pg-unclaimed .gk-pg-event-name:hover { text-decoration: underline; text-underline-offset: 3px; }
    .gk-pg-unclaimed .gk-pg-event-when { font-size: .9375rem; color: var(--gk-ink-2); }
</style>

<div class="gk-stage gk-pg gk-pg-md gk-pg-unclaimed">

    {{-- The disclosure strip, above the schedule name, shown to everyone. --}}
    <section role="region" aria-labelledby="claim-strip-heading" class="gk-card gk-pg-claim">
        <div class="flex items-start gap-3">
            <svg class="gk-pg-claim-icon" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <div class="min-w-0">
                <h2 id="claim-strip-heading" class="gk-h">
                    @if ($createdBy)
                        <x-user-text dir="{{ content_dir_for_language($createdBy->translatedName(), $lang) }}">{{ __('messages.claim_strip_title', ['schedule' => $createdBy->translatedName()]) }}</x-user-text>
                    @else
                        {{ __('messages.claim_strip_title_generic') }}
                    @endif
                </h2>
                <p class="gk-pg-fine gk-pg-claim-body">
                    {{-- Two strings, because the one that mentions the dates below is false on a
                         page that has none - and a page whose dates have all passed is a common
                         shape, an act listed once months ago. --}}
                    <x-user-text dir="{{ content_dir_for_language($role->translatedName(), $lang) }}">{{ $events->isEmpty()
                        ? __('messages.claim_strip_body', ['name' => $role->translatedName()])
                        : __('messages.claim_strip_body_dates', ['name' => $role->translatedName()]) }}</x-user-text>
                </p>

                <div class="gk-pg-actions gk-pg-actions-start mt-5">
                    {{-- Forward action last, per the repo's button-pair rule. --}}
                    <a href="{{ route('role.claim.not_me', ['subdomain' => $role->subdomain]) }}" class="gk-pg-quiet">
                        {{ __('messages.claim_strip_not_me') }}
                    </a>
                    @if ($canClaim)
                    <a href="{{ route('role.claim.start', ['subdomain' => $role->subdomain]) }}" class="gk-btn gk-btn-lg gk-pg-claim-go">
                        {{ __('messages.claim_strip_cta') }}
                    </a>
                    @endif
                </div>

                @unless ($canClaim)
                {{-- No contact on the row means there is nothing we could verify control of, and
                     ownership here is granted by verification, never by pressing a button. Say so
                     rather than offering a button that can only refuse. --}}
                <p class="gk-pg-fine mt-3">
                    {{ __('messages.claim_strip_no_contact') }}
                </p>
                @endunless
            </div>
        </div>
    </section>

    <div class="gk-pg-head mt-10">
        <h1 class="gk-title"
            dir="{{ content_dir_for_language($role->translatedName(), $lang) }}"
            style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
            <x-user-text>{{ $role->translatedName() }}</x-user-text>
        </h1>
        <p class="gk-subtitle">
            {{ $unclaimedTypeLine = \App\Utils\ScheduleTypeLabel::describe($role->type) }}@if ($role->city){{ $unclaimedTypeLine !== '' ? ', ' : '' }}<x-user-text dir="{{ content_dir_for_language($role->city, $lang) }}">{{ $role->city }}</x-user-text>@endif
        </p>
    </div>

    <section class="gk-sec mt-10">
        <h2 class="gk-label">
            {{ __('messages.claim_strip_upcoming') }}
        </h2>

        @if ($events->isEmpty())
        <p class="gk-pg-lead">
            {{ __('messages.claim_strip_no_events') }}
        </p>
        @else
        <ul class="gk-pg-events">
            @foreach ($events as $event)
            <li>
                {{-- The canonical, on a schedule that accepted the event: this placeholder cannot
                     serve it (viewGuestUnclaimed() answers only the root), and neither can a
                     performer who has not accepted it yet. A series links at its undated URL, not
                     at its first date, which need not be an occurrence. --}}
                <a href="{{ $event->getCanonicalUrl() }}" class="gk-pg-event-name"
                   dir="{{ content_dir_for_language($event->translatedName(), $lang) }}">
                    <x-user-text>{{ $event->translatedName() }}</x-user-text>
                </a>
                <span class="gk-pg-event-when">
                    {{ $event->localStartsAt(true) }}@if ($event->venue), <x-user-text dir="{{ content_dir_for_language($event->venue->translatedName(), $lang) }}">{{ $event->venue->translatedName() }}</x-user-text>@endif
                </span>
                @if ($event->creatorRole)
                <span class="gk-pg-fine" dir="{{ content_dir_for_language($event->creatorRole->translatedName(), $lang) }}">
                    <x-user-text>{{ __('messages.claim_strip_listed_by', ['schedule' => $event->creatorRole->translatedName()]) }}</x-user-text>
                </span>
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </section>
</div>

</x-app-guest-layout>
