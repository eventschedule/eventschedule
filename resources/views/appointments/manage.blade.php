<x-app-guest-layout :role="$role">
    @php
        $manageParams = ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret];
    @endphp

    <div class="max-w-xl mx-auto px-4 py-10">
        @if (session('message'))
            <div class="mb-4 p-3 rounded-lg bg-green-50 dark:bg-green-900/20 text-green-700 dark:text-green-300">{{ session('message') }}</div>
        @endif
        @if (session('error'))
            <div class="mb-4 p-3 rounded-lg bg-red-50 dark:bg-red-900/20 text-red-700 dark:text-red-300">{{ session('error') }}</div>
        @endif

        <div class="rounded-xl border border-gray-200 dark:border-[#2d2d30] p-6">
            @switch($state)
                @case('pending')
                    <h1 class="text-xl font-bold">{{ __('messages.appointments_request_sent') }}</h1>
                    <p class="text-gray-500 mt-1">{{ __('messages.appointments_pending_note', ['schedule' => $role?->name ?? '']) }}</p>
                    @break
                @case('awaiting_payment')
                    <h1 class="text-xl font-bold">{{ __('messages.appointments_awaiting_payment') }}</h1>
                    <form method="POST" action="{{ route('appointments.pay', $manageParams) }}" class="mt-3">
                        @csrf
                        <button type="submit" class="px-4 py-3 text-base rounded-lg text-white" style="background: {{ $role->accent_color ?? '#4E81FA' }}">{{ __('messages.appointments_complete_payment') }}</button>
                    </form>
                    @break
                @case('cancelled')
                    <h1 class="text-xl font-bold">{{ __('messages.appointments_cancelled') }}</h1>
                    @break
                @case('passed')
                    <h1 class="text-xl font-bold">{{ __('messages.appointments_passed') }}</h1>
                    @break
                @default
                    <h1 class="text-xl font-bold">{{ __('messages.appointments_youre_booked') }}</h1>
            @endswitch

            <div class="mt-4 space-y-1">
                <div class="font-semibold text-lg">{{ $type?->name ?? $event->name }}</div>
                <div>{{ $event->getStartDateTime($sale->event_date, true, $event->timezone)->format('l, F j, Y') }}</div>
                <div>{{ $event->getStartEndTime($sale->event_date) }} ({{ $event->timezone }})</div>
                @if ($event->event_url)
                    <div><a href="{{ $event->event_url }}" class="text-blue-600 dark:text-blue-400 break-all">{{ $event->event_url }}</a></div>
                @elseif ($type && $type->location_type === 'in_person' && $type->location_address)
                    <div class="text-gray-500">{{ $type->location_address }}</div>
                @endif
            </div>

            @if (in_array($state, ['confirmed', 'pending', 'awaiting_payment']))
                <form method="POST" action="{{ route('appointments.manage_cancel', $manageParams) }}" class="mt-6">
                    @csrf
                    <button type="submit" class="text-red-600 dark:text-red-400 text-sm hover:underline">{{ __('messages.appointments_cancel_booking') }}</button>
                </form>
            @endif

            @if ($state === 'cancelled')
                <a href="{{ route('appointments.book', ['subdomain' => $role->subdomain]) }}" class="inline-block mt-6 text-sm" style="color: {{ $role->accent_color ?? '#4E81FA' }}">{{ __('messages.appointments_book_again') }}</a>
            @endif

            <p class="mt-6 text-xs text-gray-400">{{ __('messages.appointments_manage_link_hint') }}</p>
        </div>
    </div>
</x-app-guest-layout>
