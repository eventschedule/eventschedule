{{--
    The GET half renders a button; the POST is what confirms. See NotificationEmailController for
    why a confirming GET is not an option (mail scanners fetch every URL in an inbound message).
    The form posts back to the URL it was opened at, which carries the signature.
--}}
<x-auth-layout>
    <div class="text-center">
        @if ($state === 'invalid')
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.notification_email_link_invalid_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.notification_email_link_invalid') }}
            </p>
        @elseif ($state === 'done')
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.notification_email_confirmed_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.notification_email_confirmed_body', ['schedule' => $role->name]) }}
            </p>
        @else
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.notification_email_confirm_heading', ['schedule' => $role->name]) }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ $role->notification_email }}
            </p>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.notification_email_confirm_body') }}
            </p>
            <form method="POST" action="{{ request()->getRequestUri() }}" class="mt-6">
                @csrf
                <x-honeypot />
                <x-input-error :messages="$errors->get('email')" class="mb-4" />
                <x-primary-button class="w-full justify-center">
                    {{ __('messages.notification_email_confirm_button') }}
                </x-primary-button>
            </form>
        @endif
    </div>
</x-auth-layout>
