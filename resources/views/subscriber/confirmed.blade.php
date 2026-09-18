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
    {{-- Both password surfaces carry a timezone field, so the script covers both ids. --}}
    @if ((($claimToken ?? null) && ($claimEmail ?? null)) || ($offerPassword ?? false))
    <x-slot name="head">
        <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            var zone = Intl.DateTimeFormat().resolvedOptions().timeZone;

            ['claim_timezone', 'confirm_timezone'].forEach(function(id) {
                var el = document.getElementById(id);
                if (el) {
                    el.value = zone;
                }
            });
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
            {{-- The last thing before the account exists, and the only surface that can say so
                 accurately: the button below POSTs straight into confirm() -> linkAccount(), and
                 this renders on the CLICK, whereas the confirmation email's copy of this line was
                 rendered when the mail was built. A schedule claimed in between would have sent a
                 mail with no note and then made an account anyway. --}}
            @if ($role->willCreateAccountOnConfirm())
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-500">
                {{ __('messages.subscribe_account_note') }}
            </p>
            @endif
            {{-- url() rather than route(): the POST shares its name with nothing else, and the
                 token is already in hand. --}}
            <form method="POST" action="{{ url('/sub/c/' . $subscriber->confirm_token) }}" class="mt-6">
                @csrf
                <x-honeypot />

                @if ($offerPassword ?? false)
                    {{-- Optional, and it has to stay that way. A required field here would turn a
                         working confirmation into a signup form, and everyone it bounced would be
                         somebody who was about to become a confirmed subscriber - the schedule
                         owner's actual product traded away for account conversion.

                         The button label below is deliberately unchanged: it is true whether this
                         is filled in or not, so there is nothing to branch. --}}
                    <div class="text-start mb-6 pb-6 border-b border-gray-200 dark:border-gray-700">
                        <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                            {{ __('messages.subscription_confirm_password_heading') }}
                        </h3>

                        <input type="hidden" id="confirm_timezone" name="timezone">

                        {{-- Visible and readonly rather than hidden: a hidden username field is not
                             reliably picked up by password managers, so the saved credential ends
                             up unattached to an account. The submitted value is ignored - confirm()
                             takes the address from the subscription the token resolves to. --}}
                        <div class="mt-4">
                            <x-input-label for="confirm_email" :value="__('messages.email')" />
                            <x-text-input id="confirm_email" name="email" type="email" readonly
                                class="block mt-1 w-full bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300"
                                autocomplete="username" :value="$subscriber->email" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="confirm_password" :value="__('messages.subscription_confirm_password_label')" />
                            <x-password-input id="confirm_password" name="password"
                                minlength="8" autocomplete="new-password"
                                aria-describedby="confirm_password_hint" class="block mt-1 w-full" />
                            <p id="confirm_password_hint" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                                {{ __('messages.subscription_confirm_password_hint', ['min' => 8]) }}
                            </p>
                            {{-- layouts/auth.blade.php renders {{ $slot }} and no $errors block, so
                                 a per-field error is invisible unless the field renders it itself.
                                 confirm() validates BEFORE burning the token, so a rejected
                                 password lands back here on a link that still works. --}}
                            <x-input-error :messages="$errors->get('password')" class="mt-2" />
                        </div>
                    </div>
                @endif

                <x-primary-button class="w-full justify-center">
                    {{ __('messages.subscription_confirm_button') }}
                </x-primary-button>
            </form>
        @endif
    </div>

    @if ($done ?? true)
        @if ($claimedEmail ?? null)
            {{-- They set a password on the confirm page and confirm() signed them in. Landing here
                 rather than straight on /following is the point: the subscription confirmation
                 above is what they actually came for, and redirecting past it would demote it to a
                 three-second toast. --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('messages.subscription_account_ready_heading') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.subscription_account_ready_body', ['email' => $claimedEmail]) }}
                </p>
                <div class="mt-4 flex items-center justify-center gap-4">
                    @if ($role->getGuestUrl())
                    <x-link href="{{ $role->getGuestUrl() }}" class="text-sm">{{ __('messages.back_to_schedule') }}</x-link>
                    @endif
                    {{-- app_url(): /sub/* is domain-less, so this page can be served on the tenant
                         host, where a bare route() is a URL app_subdomain only has to bounce. --}}
                    <a href="{{ app_url(route('following', [], false)) }}">
                        <x-primary-button type="button">{{ __('messages.following') }}</x-primary-button>
                    </a>
                </div>
            </div>
        @elseif (($claimToken ?? null) && ($claimEmail ?? null))
            {{-- text-start, not the inherited text-center: this is a form, and centred labels,
                 centred errors and a centred button row are unscannable in a max-w-md card. --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-start">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('messages.subscription_account_heading') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.subscription_account_body', ['email' => $claimEmail]) }}
                </p>

                @php
                    // The Tickets page is hosted-only - layouts/navigation.blade.php wraps that nav
                    // item in @if (config('app.hosted')) - so on a selfhost install this bullet was
                    // promising a page with no way to reach it.
                    $benefits = [__('messages.subscription_account_benefit_following')];

                    if (config('app.hosted')) {
                        $benefits[] = __('messages.subscription_account_benefit_tickets');
                    }
                @endphp
                <ul class="mt-3 space-y-1.5 text-sm text-gray-600 dark:text-gray-400">
                    @foreach ($benefits as $benefit)
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

                {{-- Outside the form: this is a link, and nesting it would submit the password.

                     The easiest door of the four, and it needed no new machinery - a stub has no
                     password, so SocialAuthController's "cannot auto-link a password-protected
                     account" guard does not fire, and it adopts the stub, stamps email_verified_at
                     and signs them in. The follower pivot and signup_intent are untouched.
                     confirmed() has put /following in url.intended so the callback lands there. --}}
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

                    <x-google-button>{{ __('messages.continue_with_google') }}</x-google-button>
                </div>
                @endif
            </div>
        @elseif ($errors->has('password'))
            {{-- A claim that failed. forgetClaim() has just nulled the token, so the block above -
                 and the x-input-error inside it - is gone, and without this the page silently
                 degrades to "You are on the list" with the form simply vanished. $errors is shared
                 with every view by ShareErrorsFromSession, so no controller flag is needed.

                 Deliberately not rendering __($status): those strings resolve from the FRAMEWORK's
                 English passwords.php (this repo ships no resources/lang/*/passwords.php), and they
                 talk about a password reset the visitor never asked for. --}}
            <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700 text-center">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('messages.subscription_account_expired_heading') }}
                </h3>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.subscription_account_expired_body') }}
                </p>
                <div class="mt-4 flex items-center justify-center gap-4">
                    @if ($role->getGuestUrl())
                    <x-link href="{{ $role->getGuestUrl() }}" class="text-sm">{{ __('messages.back_to_schedule') }}</x-link>
                    @endif
                    {{-- app_url(): see the log-in link below for why a bare route() is wrong here. --}}
                    <a href="{{ app_url(route('login', [], false)) }}">
                        <x-primary-button type="button">{{ __('messages.log_in') }}</x-primary-button>
                    </a>
                </div>
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
