{{--
    The account-wide email opt-out (users.is_subscribed). The GET renders a button; the POST is
    what writes. See the /user/unsubscribe route for why a mutating GET is not an option (mail
    scanners fetch every URL in an inbound message).

    The body says what stops AND what keeps arriving, on both halves: the flag is account-wide, and
    somebody who clicked it from one kind of email should not be surprised by what else went quiet.
--}}
<x-auth-layout>
    <div class="text-center">
        @if ($done)
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.subscription_unsubscribed_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.account_unsubscribe_body') }}
            </p>
            <p class="mt-4 text-sm">
                <x-link href="{{ route('profile.edit') }}">{{ __('messages.account_resubscribe_link') }}</x-link>
            </p>
        @else
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.account_unsubscribe_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.account_unsubscribe_body') }}
            </p>
            <form method="POST" action="{{ $action }}" class="mt-6">
                @csrf
                <x-honeypot />
                <x-primary-button class="w-full">
                    {{ __('messages.subscription_unsubscribe_confirm') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</x-auth-layout>
