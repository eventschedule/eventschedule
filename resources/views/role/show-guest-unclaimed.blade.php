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
<x-app-guest-layout :role="$role" :fonts="$fonts" :no-index="true">

@php
    $accent = '#2563eb'; // blue-600. The schedule's own accent is not used: this is the platform
                         // speaking about a page it generated, not the schedule speaking. It also
                         // clears 4.5:1 against white in both colour modes, which the brand blue
                         // does not - the same reasoning components/needs-attention.blade.php uses.
    $appName = config('app.name');
    $canClaim = (bool) ($role->email || $role->phone);
    // Page direction follows the schedule's language, but a name does not have to be in it: an
    // English-language promoter listing a Hebrew act is the ordinary case here, and without a per
    // element dir that name renders left to right. Same treatment every other guest surface gives
    // user text - see role/show-guest.blade.php and event/show-guest.blade.php.
    $lang = $role->displayLanguageCode();
@endphp

<div class="container mx-auto max-w-3xl px-0 sm:px-5 pt-4 pb-20 sm:pb-8">

    {{-- The disclosure strip, above the schedule name, shown to everyone. --}}
    <section role="region" aria-labelledby="claim-strip-heading"
        class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8 mb-4">
        <div class="flex items-start gap-3">
            <svg class="h-6 w-6 shrink-0 mt-0.5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            <div class="min-w-0">
                <h2 id="claim-strip-heading" class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    @if ($createdBy)
                        <x-user-text dir="{{ content_dir_for_language($createdBy->translatedName(), $lang) }}">{{ __('messages.claim_strip_title', ['schedule' => $createdBy->translatedName()]) }}</x-user-text>
                    @else
                        {{ __('messages.claim_strip_title_generic') }}
                    @endif
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    <x-user-text dir="{{ content_dir_for_language($role->translatedName(), $lang) }}">{{ __('messages.claim_strip_body', ['name' => $role->translatedName(), 'app' => $appName]) }}</x-user-text>
                </p>

                <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-3">
                    {{-- Forward action last, per the repo's button-pair rule. --}}
                    <a href="{{ route('role.claim.not_me', ['subdomain' => $role->subdomain]) }}"
                        class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                        {{ __('messages.claim_strip_not_me') }}
                    </a>
                    @if ($canClaim)
                    <a href="{{ route('role.claim.start', ['subdomain' => $role->subdomain]) }}"
                        style="background-color: {{ $accent }};"
                        class="inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:opacity-90 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-600 dark:focus:ring-offset-gray-900">
                        {{ __('messages.claim_strip_cta') }}
                    </a>
                    @endif
                </div>

                @unless ($canClaim)
                {{-- No contact on the row means there is nothing we could verify control of, and
                     ownership here is granted by verification, never by pressing a button. Say so
                     rather than offering a button that can only refuse. --}}
                <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                    {{ __('messages.claim_strip_no_contact') }}
                </p>
                @endunless
            </div>
        </div>
    </section>

    <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100"
            dir="{{ content_dir_for_language($role->translatedName(), $lang) }}"
            style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">
            <x-user-text>{{ $role->translatedName() }}</x-user-text>
        </h1>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
            {{ __('messages.' . $role->type) }}@if ($role->city), <x-user-text dir="{{ content_dir_for_language($role->city, $lang) }}">{{ $role->city }}</x-user-text>@endif
        </p>

        <h2 class="mt-8 text-sm font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">
            {{ __('messages.claim_strip_upcoming') }}
        </h2>

        @if ($events->isEmpty())
        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.claim_strip_no_events') }}
        </p>
        @else
        <ul class="mt-3 divide-y divide-gray-200 dark:divide-gray-700">
            @foreach ($events as $event)
            <li class="py-3 flex flex-col gap-1">
                <a href="{{ $event->getCanonicalUrl() }}" class="text-sm font-medium text-gray-900 dark:text-gray-100 hover:underline"
                   dir="{{ content_dir_for_language($event->translatedName(), $lang) }}">
                    <x-user-text>{{ $event->translatedName() }}</x-user-text>
                </a>
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $event->localStartsAt(true) }}@if ($event->venue), <x-user-text dir="{{ content_dir_for_language($event->venue->translatedName(), $lang) }}">{{ $event->venue->translatedName() }}</x-user-text>@endif
                </span>
                @if ($event->creatorRole)
                <span class="text-xs text-gray-500 dark:text-gray-400" dir="{{ content_dir_for_language($event->creatorRole->translatedName(), $lang) }}">
                    <x-user-text>{{ __('messages.claim_strip_listed_by', ['schedule' => $event->creatorRole->translatedName()]) }}</x-user-text>
                </span>
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </div>
</div>

</x-app-guest-layout>
