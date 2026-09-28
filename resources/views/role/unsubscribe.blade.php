{{--
    The schedule-level opt-out (roles.is_subscribed, which gates claim invites) and the landing
    page for a bad account-wide link.

    Success is a session flag set by RoleController::unsubscribe(), never the presence of ?email=.
    It used to be ?email=, and the unsigned footer links in the event request emails carried one,
    so they reported an unsubscribe that never happened. Those links still exist in inboxes; they
    now land on the form below.

    Deliberately does NOT reuse messages.unsubscribed as a heading: it is an admin stat label, the
    bare word "unsubscribed". Same reason subscriber/unsubscribe.blade.php avoids it.
--}}
<x-auth-layout>
    @if (session('role_unsubscribed'))
        <div class="text-center">
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.subscription_unsubscribed_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.unsubscribed_message') }}
            </p>
        </div>
    @else
        <!-- Manual unsubscribe form -->
        <form method="POST" action="{{ route('role.unsubscribe') }}">
            @csrf
            <x-honeypot />

            <!-- Email Address -->
            <div>
                <x-input-label for="email" :value="__('messages.email')" />
                <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="off" />
                <x-input-error :messages="$errors->get('email')" class="mt-2" />
            </div>

            <div class="mt-4">
                <x-primary-button class="w-full">
                    {{ __('messages.unsubscribe') }}
                </x-primary-button>
            </div>
        </form>
    @endif
</x-auth-layout>
