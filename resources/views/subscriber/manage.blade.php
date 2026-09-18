{{--
    The durable "Manage your account" page, reached from a footer link in every announcement and
    newsletter.

    Four states, and this page is the only surface that can tell them apart - which is exactly why
    the footer link says "Manage your account" rather than "Set a password". One footer serves
    recipients who have a passwordless account, a real account, or no account at all, and on a
    selfhost install with registration closed there is no account to be had.

    The GET mutates nothing: Safe Links, Proofpoint and Barracuda dereference footer links before a
    human ever sees them. The POST only ever mails the address on the token's row.
--}}
<x-auth-layout>
    <div class="text-center">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.subscription_manage_heading') }}
        </h2>

        {{-- bdi: an LTR address inside RTL prose reorders around the surrounding punctuation in
             ar/he without isolation. --}}
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {!! __('messages.subscription_manage_body', [
                'email' => '<bdi dir="ltr" class="font-medium text-gray-900 dark:text-gray-100">'.e($email).'</bdi>',
            ]) !!}
        </p>
    </div>

    <x-auth-session-status class="mt-4" :status="session('status')" />

    <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
        @if ($isStub)
            {{-- An account with no password. The button mails a 60-minute link; the token that got
                 them here is NOT a credential for setting one, because it ships in every
                 List-Unsubscribe header and gateways dereference it. --}}
            <p class="text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.subscription_manage_set_password_body') }}
            </p>

            <form method="POST" action="{{ route('subscriber.send_set_password', ['token' => $token]) }}" class="mt-4">
                @csrf
                <x-honeypot />
                {{-- No email input, deliberately: sendSetPassword() ignores anything posted and
                     mails the address on the row, so an editable-looking field would be a lie.
                     The address is shown as text above. --}}
                <x-input-error :messages="$errors->get('email')" class="mb-2" />
                <x-primary-button class="w-full justify-center">
                    {{ __('messages.subscription_manage_email_me_a_link') }}
                </x-primary-button>
            </form>

            @if (config('services.google.client_id'))
            <div class="mt-6">
                <div class="relative mb-4">
                    <div class="absolute inset-0 flex items-center">
                        <div class="w-full border-t border-gray-300 dark:border-gray-600"></div>
                    </div>
                    <div class="relative flex justify-center text-sm">
                        <span class="px-2 bg-white dark:bg-gray-900 text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
                    </div>
                </div>

                {{-- No mail round trip at all: a stub has no password, so SocialAuthController
                     adopts it, stamps email_verified_at and signs them in. --}}
                <x-google-button>{{ __('messages.continue_with_google') }}</x-google-button>
            </div>
            @endif
        @elseif ($hasAccount)
            <div class="text-center">
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.subscription_manage_has_account_body') }}
                </p>
                <div class="mt-4">
                    {{-- app_url(): /sub/* is domain-less, so this page can be served on a tenant
                         host, where a bare route() is a URL app_subdomain only has to bounce. --}}
                    <a href="{{ app_url(route('login', [], false)) }}">
                        <x-primary-button type="button">{{ __('messages.log_in') }}</x-primary-button>
                    </a>
                </div>
            </div>
        @elseif (public_registration_enabled())
            <div class="text-center">
                {{-- A checkout opt-in creates a subscriber row and no account at all, so these
                     people are being offered something new rather than handed something dormant.
                     They also have the most to gain: claimSalesByEmail() attaches the guest
                     purchases they already made. --}}
                <p class="text-sm text-gray-600 dark:text-gray-400">
                    {{ $fromCheckout
                        ? __('messages.subscription_manage_no_account_checkout_body')
                        : __('messages.subscription_manage_no_account_body') }}
                </p>
                <div class="mt-4">
                    <a href="{{ app_url(route('sign_up', [], false)) }}">
                        <x-primary-button type="button">{{ __('messages.create_new_account') }}</x-primary-button>
                    </a>
                </div>
            </div>
        @else
            {{-- Registration is closed, so there is no account to offer and no user to mint a token
                 for. RegisteredUserController::create() would bounce a sign-up link to /login, so
                 linking one would be a dead end. Say what is true instead. --}}
            <p class="text-sm text-gray-600 dark:text-gray-400 text-center">
                {{ __('messages.subscription_manage_registration_closed_body') }}
            </p>
        @endif
    </div>

    @if ($role && $role->getGuestUrl())
    <div class="mt-6 text-center text-sm">
        <x-link href="{{ $role->getGuestUrl() }}">{{ __('messages.back_to_schedule') }}</x-link>
    </div>
    @endif
</x-auth-layout>
