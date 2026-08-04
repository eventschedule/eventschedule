<x-app-layout :title="__('messages.your_tickets') . ($role ? ' | ' . $role->translatedName() : '')">

    <x-slot name="footCode">@include('partials.site-foot-code')</x-slot>

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
                @endphp
                <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($legEvent->id), 'secret' => $sale->secret]) }}"
                   class="ap-card rounded-xl p-5 flex flex-col sm:flex-row sm:items-center gap-3 hover:shadow-md transition-all duration-200">
                    <div class="flex-1">
                        <div class="font-medium text-gray-900 dark:text-gray-100">
                            {{ $legEvent->translatedName() }}
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                            {{ $legEvent->getDateRangeDisplay($sale->event_date) }}
                        </div>
                    </div>
                    <div class="sm:mt-0 mt-2 sm:text-end">
                        <span class="inline-flex items-center gap-1 text-sm font-medium text-[var(--brand-blue)]">
                            {{ __('messages.view_ticket') }}
                            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </span>
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
