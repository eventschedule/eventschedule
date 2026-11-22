{{-- An event that cannot be shown inside an embedded calendar (App\Support\EmbedEventView), said
     quietly in the frame or the overlay it was opened in, with the way back to the calendar:

       missing   the event is not there any more (deleted, a draft now, a stale #es-event= link on
                 the organizer's page). Answered with a 404, as the event's own address would be.
       password  the event has a password. Its form posts to us and would come back to a frame
                 with no session, so it is asked on the event's own page, in a new tab. The name
                 is not printed, as the password page does not print it.

     No event is handed to the layout, so nothing about it reaches the head. --}}
<x-app-guest-layout :role="$role" :fonts="array_filter([$role->font_family])" :embed-event="true" :page-title="__('messages.embed_event_unavailable_title')">
    @include('partials.guest-form-pages-styles')
    {{-- In a frame: one small sheet, the way back to the calendar first. --}}
    <div class="gk-stage gk-pg gk-pg-sm gk-pg-embed {{ $role->isRtl() ? 'rtl' : '' }}" data-embed-root>
        <div class="gk-pg-head">
            <a href="{{ $backUrl }}" target="_self" data-embed-back class="gk-pg-quiet gk-pg-back">
                <span aria-hidden="true">{{ $role->isRtl() ? '→' : '←' }}</span> {{ __('messages.embed_back_to_calendar') }}
            </a>
            <h1 tabindex="-1" data-embed-heading class="gk-title">
                {{ $reason === 'password' ? __('messages.password_protected') : __('messages.embed_event_unavailable_title') }}
            </h1>
            <p class="gk-pg-lead" data-embed-unavailable="{{ $reason }}">
                {{ $reason === 'password' ? __('messages.embed_event_password_body') : __('messages.embed_event_unavailable_body') }}
            </p>
        </div>
        @if ($reason === 'password' && $pageUrl)
        <div class="mt-8">
            <a href="{{ $pageUrl }}" target="_blank" rel="noopener" class="gk-btn gk-btn-primary gk-cta-main">
                {{ __('messages.embed_view_full_page') }}<span class="sr-only"> ({{ __('messages.opens_in_new_tab') }})</span>
            </a>
        </div>
        @endif
    </div>
</x-app-guest-layout>
