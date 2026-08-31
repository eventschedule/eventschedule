<x-auth-layout>
    <div class="text-center">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.subscription_confirmed_heading') }}
        </h2>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.subscription_confirmed_body', ['schedule' => $role->name]) }}
        </p>
        <a href="{{ $role->getGuestUrl() }}"
            class="mt-6 inline-block text-sm text-[var(--brand-blue)] hover:underline">
            {{ $role->name }}
        </a>
    </div>
</x-auth-layout>
