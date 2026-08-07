<x-app-layout :title="__('messages.your_tickets') . ($role ? ' | ' . $role->translatedName() : '')">

    <x-slot name="footCode">
        @include('partials.site-foot-code')
        @include('partials.cart-clear')
    </x-slot>

    <x-slot name="head">
        @include('partials.site-head-code')
    </x-slot>

    <div class="max-w-2xl mx-auto px-4 py-10">
        <h1 class="text-2xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ __('messages.your_tickets') }}
        </h1>
        <p class="text-gray-600 dark:text-gray-400 mb-8">
            {{ __('messages.order_includes_events', ['count' => $sales->count()]) }}
        </p>

        <div class="space-y-4">
            @foreach ($sales as $sale)
                @php
                    $legEvent = $sale->event;
                    // A leg the organizer cancelled, refunded or let expire is still part of what
                    // the buyer purchased, so it stays listed - but it is no longer a ticket, and
                    // linking it would hand out a QR for a seat that has already been released.
                    // A leg is deleted outright rather than released is not listed at all: both
                    // queries above filter is_deleted, which is the owner erasing the record.
                    //
                    // A cancelled EVENT counts too: the sale keeps its paid status, so without this
                    // the buyer saw a live ticket with a working code for an event that is not
                    // happening.
                    $isReleased = in_array($sale->status, ['cancelled', 'refunded', 'expired'])
                        || $legEvent->is_cancelled;
                @endphp
                <a @if (! $isReleased) href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($legEvent->id), 'secret' => $sale->secret]) }}" @endif
                   class="ap-card rounded-xl p-5 flex flex-col sm:flex-row sm:items-center gap-3 transition-all duration-200 {{ $isReleased ? 'opacity-60' : 'hover:shadow-md' }}">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $legEvent->translatedName() }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $legEvent->getDateRangeDisplay($sale->event_date) }}
                        </div>
                    </div>
                    <div class="sm:mt-0 mt-2 sm:text-end">
                        @if ($isReleased)
                            <span class="inline-flex items-center text-sm font-medium text-gray-500 dark:text-gray-400">
                                {{ $legEvent->is_cancelled && $sale->status === 'paid'
                                    ? __('messages.event_cancelled_heading')
                                    : __('messages.'.$sale->status) }}
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1 text-sm font-medium text-[var(--brand-blue)]">
                                {{ __('messages.view_ticket') }}
                                <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                                </svg>
                            </span>
                        @endif
                    </div>
                </a>
            @endforeach
        </div>

        {{-- Each event is scanned with its own code, so there is no single QR for the order. --}}
        <p class="text-sm text-gray-500 dark:text-gray-400 mt-8">
            {{ __('messages.order_ticket_per_event_help') }}
        </p>
    </div>

</x-app-layout>
