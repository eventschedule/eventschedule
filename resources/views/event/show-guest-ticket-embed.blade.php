<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts">

    @php
        $accentColor = $role->accent_color ?? '#4E81FA';
        $contrastColor = accent_contrast_color($accentColor);
        $isTicketMode = request()->get('tickets') === 'true';
        $isRsvpMode = request()->get('rsvp') === 'true';
        $eventDate = $date ?? \Carbon\Carbon::parse($event->starts_at)->format('Y-m-d');
    @endphp

    <style {!! nonce_attr() !!}>
        .ticket-embed-card {
            background: white;
            border-radius: 0.75rem;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05);
            border: 1px solid #d1d5db;
        }
        .dark .ticket-embed-card {
            background: #252526;
            border-color: #2d2d30;
        }
    </style>

    <div class="pt-4 pb-4 px-4">
        <div class="ticket-embed-card max-w-xl mx-auto overflow-hidden">

            {{-- Header --}}
            <div class="px-6 py-4" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
                <h1 class="text-lg font-bold">
                    <a href="{{ $event->getGuestUrl($subdomain, $date) }}" target="_top" style="color: {{ $contrastColor }}; text-decoration: none;">
                        {{ $event->name }}
                    </a>
                </h1>
                <p class="text-sm mt-1" style="opacity: 0.85;">
                    {{ $event->is_multi_day ? $event->getDateRangeDisplay($eventDate) : $event->getStartDateTime($eventDate, true)->format('l, F j, Y') }}
                    @if ($event->getStartEndTime($eventDate))
                        &middot; {{ $event->getStartEndTime($eventDate) }}
                    @endif
                </p>
            </div>

            {{-- Body --}}
            <div class="px-6 py-5">

                @if (session('error'))
                <div class="mb-4 p-3 rounded-lg text-sm bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
                    {{ session('error') }}
                </div>
                @endif

                @if ($isTicketMode)
                    @if ($event->canSellTickets($eventDate))
                        @include('event.tickets', ['accentColor' => $accentColor, 'contrastColor' => $contrastColor])
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">{{ __('messages.tickets_not_available_embed') }}</p>
                            <a href="{{ $event->getGuestUrl($subdomain, $date) }}" target="_top" class="mt-4 inline-block text-sm font-medium" style="color: {{ $accentColor }};">
                                {{ __('messages.view_event') }}
                            </a>
                        </div>
                    @endif
                @elseif ($isRsvpMode)
                    @if ($event->rsvp_enabled)
                        @include('event.rsvp', ['accentColor' => $accentColor, 'contrastColor' => $contrastColor])
                    @else
                        <div class="text-center py-8">
                            <p class="text-gray-500 dark:text-gray-400">{{ __('messages.registration_not_available_embed') }}</p>
                            <a href="{{ $event->getGuestUrl($subdomain, $date) }}" target="_top" class="mt-4 inline-block text-sm font-medium" style="color: {{ $accentColor }};">
                                {{ __('messages.view_event') }}
                            </a>
                        </div>
                    @endif
                @endif

            </div>

            {{-- Powered by footer --}}
            @if ($role->showBranding())
            <div class="px-6 py-3 border-t border-gray-200 dark:border-gray-700 text-end">
                <a href="https://eventschedule.com" target="_blank" rel="noopener noreferrer" class="text-xs text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400 transition-colors" style="text-decoration: none;">
                    Powered by Event Schedule
                </a>
            </div>
            @endif

        </div>
    </div>

</x-app-guest-layout>
