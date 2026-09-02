{{--
    Two states, like subscriber/unsubscribe.blade.php: the GET renders a button, the POST confirms.

    A GET that confirmed was fetched by corporate mail gateways before the recipient ever saw it,
    which completed the subscription and deleted their newsletter suppression row on their behalf.
    See RoleSubscriberController::showConfirm().
--}}
<x-auth-layout>
    <div class="text-center">
        @if ($done ?? true)
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.subscription_confirmed_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.subscription_confirmed_body', ['schedule' => $role->name]) }}
            </p>
            <div class="mt-6 text-sm">
                <x-link href="{{ $role->getGuestUrl() }}">
                    {{ __('messages.back_to_schedule') }}
                </x-link>
            </div>
        @else
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.subscription_confirm_heading') }}
            </h2>
            {{-- Same two strings the confirmation email uses: it says "Confirm below and we will
                 start", which is now literally true of this page rather than of the email. Only
                 the body carries :schedule. --}}
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.subscription_confirm_body', ['schedule' => $role->name]) }}
            </p>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">
                {{ __('messages.subscription_confirm_cadence') }}
            </p>
            {{-- url() rather than route(): the POST shares its name with nothing else, and the
                 token is already in hand. --}}
            <form method="POST" action="{{ url('/sub/c/' . $subscriber->confirm_token) }}" class="mt-6">
                @csrf
                <x-primary-button class="w-full justify-center">
                    {{ __('messages.subscription_confirm_button') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</x-auth-layout>
