{{--
    "Tell me when tickets go on sale, and if anything changes."

    Deliberately NOT worded as a reminder. The control this pairs with is Add to Calendar, and
    somebody who has just added the event to their calendar already HAS a reminder - offering them
    another is offering nothing. What a calendar entry cannot do is tell them tickets went on sale,
    or that the date moved: a downloaded .ics is a snapshot and never updates. That is the whole
    pitch, and it is why this renders on events that sell nothing yet as well as ones that do.

    Plain <form method="POST">, matching partials/subscribe-panel.blade.php on this same page rather
    than the Vue/Alpine components around it: it works with JavaScript off, and
    EventInterestController::store()'s non-JSON branch redirects back to #event-interest with a
    flash this file renders INLINE.

    The flash keys are interest_message / interest_error and NOT session('error'), because
    show-guest.blade.php force-opens the RSVP / ticket-purchase modal on
    `session('error') || $errors->any()` - so a mistyped address here would pop the buy dialog.
    Same trap subscribe-panel.blade.php documents and avoids.
--}}
@php
    $interestEvent = $event;
    $interestRole = $role;

    // Computed ONCE in event/show-guest.blade.php and passed down, so this card and every link that
    // points at it cannot disagree. They used to be gated separately, and on a cancelled event the
    // Add-to-Calendar menu offered "Tell me when tickets go on sale" while this card refused to
    // render, so the link did nothing at all.
    $interestEligible = $showInterestCapture ?? false;

    $interestMessage = session('interest_message');
    $interestError = session('interest_error');
@endphp

@if ($interestEligible)
<div id="event-interest" class="scroll-mt-4 bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl border border-gray-200 dark:border-gray-700 px-5 py-5 sm:px-8 sm:py-6">
    @if ($interestMessage)
        <p class="text-sm font-medium text-green-700 dark:text-green-400">{{ $interestMessage }}</p>
    @else
        <h2 class="text-base font-semibold text-gray-900 dark:text-gray-100">
            {{ $interestEvent->canSellTickets($date ?? null)
                ? __('messages.event_interest_cta_changes')
                : __('messages.event_interest_cta') }}
        </h2>
        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.event_interest_help') }}
            {{-- policy_url(), never marketing_url(): the privacy policy can be replaced by the
                 operator at /admin/legal, and a selfhoster's visitors must not be pointed at
                 eventschedule.com's document. The subscribe panel on this same page and the follow
                 modal both carry this link; this form did not, and it is the one capture surface
                 with no confirmation step - so it IS the consent record. --}}
            <x-link href="{{ policy_url('privacy') }}" target="_blank">{{ __('messages.privacy_policy') }}</x-link>
        </p>

        @if ($interestError)
            <p id="event-interest-error" class="mt-3 text-sm text-red-600 dark:text-red-400">{{ $interestError }}</p>
        @endif

        <form method="POST"
            action="{{ route('event.interest.join', ['subdomain' => $interestRole->subdomain]) }}"
            class="mt-4 flex flex-col sm:flex-row gap-3">
            @csrf
            <x-honeypot />
            <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($interestEvent->id) }}">
            {{-- The occurrence. EventInterestController::resolveDate() checks it against the event
                 with matchesDate() rather than taking it on trust: with a unique key on
                 (event_id, event_date, email), an unchecked date makes one address x 365 days a
                 table-flood primitive on a public page. --}}
            <input type="hidden" name="event_date" value="{{ $date ?? '' }}">
            <input type="hidden" name="source" value="event_page">

            <div class="flex-1 min-w-0">
                <label for="event_interest_email" class="block text-xs font-medium text-gray-700 dark:text-gray-300 mb-1">
                    {{ __('messages.subscribe_your_email') }}
                </label>
                {{-- One field. A name is worth requiring on a standing subscription an owner will
                     mail repeatedly; here it is friction that buys nothing.

                     session(), not old(): the ticket and RSVP forms on this page also post a field
                     called `email`, so old('email') would cross-fill between them. --}}
                <input type="email" name="email" id="event_interest_email" required
                    autocomplete="email"
                    placeholder="{{ __('messages.event_interest_placeholder') }}"
                    value="{{ session('interest_email') }}"
                    @if ($interestError) aria-invalid="true" aria-describedby="event-interest-error" @endif
                    class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" />
            </div>

            {{-- Accent colour, not brand blue: the guest portal is the schedule's surface, and every
                 other primary button on these pages follows the schedule's accent. --}}
            <button type="submit"
                style="background-color: {{ $accentColor ?? '#4E81FA' }}; color: {{ $contrastColor ?? '#ffffff' }}"
                class="shrink-0 sm:self-end inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-md">
                {{ __('messages.event_interest_submit') }}
            </button>
        </form>
    @endif
</div>
@endif
