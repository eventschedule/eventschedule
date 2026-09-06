{{--
    Two states, like subscriber/unsubscribe.blade.php: the GET renders a button, the POST confirms.

    A GET that confirmed was fetched by corporate mail gateways before the recipient ever saw it,
    which completed the subscription and deleted their newsletter suppression row on their behalf.
    See RoleSubscriberController::showConfirm().

    The confirmed half is reached by REDIRECT (GET /sub/done), not straight from the POST, so a
    refresh re-renders it from the session instead of replaying a token confirm() has already burned
    and landing on the 410. That matters here because this page now carries a password form.
--}}
<x-auth-layout>
    @if (($claimToken ?? null) && ($claimEmail ?? null))
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            var el = document.getElementById('claim_timezone');
            if (el) {
                el.value = Intl.DateTimeFormat().resolvedOptions().timeZone;
            }
        });
        </script>
    </x-slot>
    @endif

    <div class="text-center">
        @if ($done ?? true)
            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                {{ __('messages.subscription_confirmed_heading') }}
            </h2>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.subscription_confirmed_body', ['schedule' => $role->name]) }}
            </p>
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

    @if ($done ?? true)
        @if (($claimToken ?? null) && ($claimEmail ?? null))
            {{-- text-start, not the inherited text-center: this is a form, and centred labels,
                 centred errors and a centred button row are unscannable in a max-w-md card. --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-start">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('messages.subscription_account_heading') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.subscription_account_body', ['email' => $claimEmail]) }}
                </p>

                <ul class="mt-3 space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                    @foreach ([__('messages.subscription_account_benefit_following'), __('messages.subscription_account_benefit_tickets')] as $benefit)
                    <li class="flex items-start gap-2">
                        <svg class="h-4 w-4 shrink-0 mt-0.5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" />
                        </svg>
                        <span>{{ $benefit }}</span>
                    </li>
                    @endforeach
                </ul>

                <form method="POST" action="{{ route('subscriber.claim_account') }}" class="mt-4">
                    @csrf
                    <x-honeypot />
                    <input type="hidden" id="claim_timezone" name="timezone">

                    {{-- Visible and readonly rather than hidden: a hidden username field is not
                         reliably picked up by password managers, so the saved credential ends up
                         unattached to an account - and it shows which address was just confirmed.
                         The submitted value is ignored; claimAccount() reads the session. --}}
                    <div>
                        <x-input-label for="claim_email" :value="__('messages.email')" />
                        <x-text-input id="claim_email" name="email" type="email" readonly
                            class="block mt-1 w-full bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300"
                            autocomplete="username" :value="$claimEmail" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="claim_password" :value="__('messages.subscription_account_password')" />
                        <x-password-input id="claim_password" name="password" required autofocus
                            minlength="8" autocomplete="new-password"
                            aria-describedby="claim_password_hint" class="block mt-1 w-full" />
                        <p id="claim_password_hint" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                            {{ __('messages.subscription_account_password_hint', ['min' => 8]) }}
                        </p>
                        {{-- layouts/auth.blade.php renders {{ $slot }} and no $errors block, so a
                             per-field error is invisible unless the field renders it itself. --}}
                        <x-input-error :messages="$errors->get('password')" class="mt-2" />
                    </div>

                    {{-- Forward action last. --}}
                    <div class="mt-6 flex items-center justify-end gap-4">
                        @if ($role->getGuestUrl())
                        <x-link href="{{ $role->getGuestUrl() }}" class="text-sm">{{ __('messages.not_now') }}</x-link>
                        @endif
                        <x-primary-button>{{ __('messages.subscription_account_button') }}</x-primary-button>
                    </div>

                    {{-- The form is one-shot: confirm_token is burned, and the claim token dies with
                         the session. Saying the fallback out loud is what stops that being a trap. --}}
                    <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                        {{ __('messages.subscription_account_skip_note') }}
                    </p>
                </form>
            </div>
        @elseif ($existingEmail ?? null)
            {{-- A real account, signed out. Dropping them at "back to schedule" means they never
                 find out the schedule is now on their Following list. --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('messages.subscription_account_existing_heading') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.subscription_account_existing_body', ['schedule' => $role->name]) }}
                </p>
                <div class="mt-4 flex items-center justify-center gap-4">
                    @if ($role->getGuestUrl())
                    <x-link href="{{ $role->getGuestUrl() }}" class="text-sm">{{ __('messages.back_to_schedule') }}</x-link>
                    @endif
                    {{-- app_url(): /sub/* is domain-less, so on hosted this page can be served on
                         the tenant host, where a bare route('login') is a URL the app_subdomain
                         middleware only has to bounce. No ?email=: the login form reads old() and
                         would ignore it anyway, and it is a follower address in a URL that lands in
                         access logs and any outbound Referer. --}}
                    <a href="{{ app_url(route('login', [], false)) }}">
                        <x-primary-button type="button">{{ __('messages.log_in') }}</x-primary-button>
                    </a>
                </div>
            </div>
        @else
            {{-- getGuestUrl() is '' for an unclaimed schedule, and the panel has no isClaimed()
                 gate - so an unguarded link here rendered href="" and reloaded this page. Falling
                 back to the guest route rather than dropping the link entirely: an unclaimed
                 schedule still has a public page, and confirming there otherwise left the visitor
                 on a page with no way onward at all. --}}
            <div class="mt-6 text-center text-sm">
                <x-link href="{{ $role->getGuestUrl() ?: route('role.view_guest', ['subdomain' => $role->subdomain]) }}">
                    {{ __('messages.back_to_schedule') }}
                </x-link>
            </div>
        @endif
    @endif
</x-auth-layout>
