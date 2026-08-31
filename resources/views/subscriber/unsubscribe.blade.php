{{--
    Deliberately does NOT reuse messages.unsubscribed. That key is declared twice in
    resources/lang/en/messages.php - ':395' ("Successfully unsubscribed from emails") and ':2985'
    ("unsubscribed", an admin stat label) - and PHP keeps the last, so both existing unsubscribe
    pages render a heading reading, in full, "unsubscribed". It sits in
    LanguageFileIntegrityTest::KNOWN_DUPLICATES, so the test passes and always will.
--}}
<x-auth-layout>
    @php
        $scheduleName = $role?->name ?? config('app.name');
    @endphp
    <div class="text-center">
        @if ($done ?? false)
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.subscription_unsubscribed_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                @if ($all ?? false)
                    {{ __('messages.subscription_unsubscribed_all_body') }}
                @else
                    {{ __('messages.subscription_unsubscribed_body', ['schedule' => $scheduleName]) }}
                @endif
            </p>

            @unless ($all ?? false)
                {{-- A fan following six schedules otherwise needs six links, and what they will
                     actually do is press Report spam once, against a From address shared by every
                     schedule on the platform. --}}
                <form method="POST" action="{{ url('/sub/u/' . $subscriber->token) }}" class="mt-6">
                    @csrf
                    <input type="hidden" name="all" value="1">
                    <button type="submit"
                        class="text-sm text-[var(--brand-blue)] hover:underline">
                        {{ __('messages.subscription_unsubscribe_all') }}
                    </button>
                </form>
            @endunless
        @else
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.subscription_unsubscribe_heading', ['schedule' => $scheduleName]) }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.subscription_unsubscribe_body') }}
            </p>
            <form method="POST" action="{{ url('/sub/u/' . $subscriber->token) }}" class="mt-6">
                @csrf
                <x-primary-button class="w-full">
                    {{ __('messages.subscription_unsubscribe_confirm') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</x-auth-layout>
