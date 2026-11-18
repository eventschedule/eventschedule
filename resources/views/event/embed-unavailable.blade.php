{{-- An event that cannot be shown inside an embedded calendar (App\Support\EmbedEventView), said
     quietly in the frame or the overlay it was opened in, with the way back to the calendar:

       missing   the event is not there any more (deleted, a draft now, a stale #es-event= link on
                 the organizer's page). Answered with a 404, as the event's own address would be.
       password  the event has a password. Its form posts to us and would come back to a frame
                 with no session, so it is asked on the event's own page, in a new tab. The name
                 is not printed, as the password page does not print it.

     No event is handed to the layout, so nothing about it reaches the head. --}}
<x-app-guest-layout :role="$role" :fonts="array_filter([$role->font_family])" :embed-event="true" :page-title="__('messages.embed_event_unavailable_title')">
    <div class="px-4 pt-4 pb-6" data-embed-root>
        <div class="mx-auto max-w-xl rounded-2xl bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm p-6 sm:p-8 {{ $role->isRtl() ? 'rtl' : '' }}">
            <a href="{{ $backUrl }}" target="_self" data-embed-back class="inline-block px-3 py-2 -mx-3 text-sm text-gray-500 dark:text-gray-400 hover:underline hover:text-gray-700 dark:hover:text-gray-200">
                {{ $role->isRtl() ? '→' : '←' }} {{ __('messages.embed_back_to_calendar') }}
            </a>
            <h1 tabindex="-1" data-embed-heading class="mt-4 text-xl font-semibold text-gray-900 dark:text-gray-100">
                {{ $reason === 'password' ? __('messages.password_protected') : __('messages.embed_event_unavailable_title') }}
            </h1>
            <p class="mt-2 text-base text-gray-600 dark:text-gray-300" data-embed-unavailable="{{ $reason }}">
                {{ $reason === 'password' ? __('messages.embed_event_password_body') : __('messages.embed_event_unavailable_body') }}
            </p>
            @if ($reason === 'password' && $pageUrl)
            <a href="{{ $pageUrl }}" target="_blank" rel="noopener"
               class="mt-6 inline-flex items-center justify-center gap-x-2 rounded-md px-5 py-3 text-base font-semibold shadow-sm transition-all duration-200 hover:shadow-md"
               style="background-color: var(--es-accent); color: var(--es-accent-text);">
                {{ __('messages.embed_view_full_page') }}<span class="sr-only"> ({{ __('messages.opens_in_new_tab') }})</span>
            </a>
            @endif
        </div>
    </div>
</x-app-guest-layout>
