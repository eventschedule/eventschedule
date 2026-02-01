<x-auth-layout>
    <div class="text-center">
        @if (isset($unsubscribed) && $unsubscribed)
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.unsubscribed') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.newsletter_unsubscribed_message', ['name' => $role->name]) }}
            </p>
        @else
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.unsubscribe_from_newsletter') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.newsletter_unsubscribe_confirm', ['name' => $role->name]) }}
            </p>
            <form method="POST" action="{{ url('/nl/u/' . $recipient->token) }}" class="mt-6">
                @csrf
                <x-primary-button class="w-full">
                    {{ __('messages.unsubscribe') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</x-auth-layout>
