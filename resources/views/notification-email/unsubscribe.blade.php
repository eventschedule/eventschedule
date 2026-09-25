{{--
    The GET half renders a button; the POST is what removes the address. See
    NotificationEmailController for why a mutating GET is not an option. The form posts back to
    the URL it was opened at, which carries the signature.
--}}
<x-auth-layout>
    @php
        $scheduleName = $role?->name ?: __('messages.schedule');
    @endphp
    <div class="text-center">
        @if ($done)
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.notification_email_unsubscribed_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.notification_email_unsubscribed_body', ['schedule' => $scheduleName]) }}
            </p>
        @else
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.notification_email_unsubscribe_heading', ['schedule' => $scheduleName]) }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.notification_email_unsubscribe_body') }}
            </p>
            <form method="POST" action="{{ request()->getRequestUri() }}" class="mt-6">
                @csrf
                <x-primary-button class="w-full justify-center">
                    {{ __('messages.notification_email_unsubscribe_button') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</x-auth-layout>
