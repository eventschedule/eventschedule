{{--
    The GET half renders a button; the POST is what deletes. See EventInterestController for why a
    mutating GET is not an option (mail scanners fetch every URL in an inbound message).

    Deliberately does NOT reuse messages.unsubscribed: that key is declared twice in
    resources/lang/en/messages.php and PHP keeps the last, so it renders as the bare word
    "unsubscribed". Same reason subscriber/unsubscribe.blade.php avoids it.
--}}
<x-auth-layout>
    @php
        $eventName = $event?->name ?: __('messages.event');
    @endphp
    <div class="text-center">
        @if ($done ?? false)
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.event_interest_unsubscribed_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.event_interest_unsubscribed_body', ['event' => $eventName]) }}
            </p>
        @else
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.event_interest_unsubscribe_heading', ['event' => $eventName]) }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.event_interest_unsubscribe_body') }}
            </p>
            <form method="POST" action="{{ url('/int/u/' . $token) }}" class="mt-6">
                @csrf
                <x-primary-button class="w-full">
                    {{ __('messages.event_interest_unsubscribe_confirm') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</x-auth-layout>
