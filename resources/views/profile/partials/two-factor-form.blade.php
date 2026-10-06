<section>
    @include('profile.partials.heading')
    {{-- In plain words: the old lead said "time-based one-time password (TOTP)". --}}
    @php
        $twoFactorUser = auth()->user();
        // The code is asked for by the password sign-in, and by nothing else: someone who signs in
        // with Google or Facebook is let straight in (SocialAuthController::completeLogin). Said
        // wherever that is a way into this account: the install offers one, or it is the only way.
        $twoFactorSaysSocial = ! $twoFactorUser->hasPassword() || config('services.google.client_id') || facebook_login_enabled();
    @endphp
    <p class="form-kit-lead">{{ __('messages.settings_two_factor_lead') }}@if ($twoFactorSaysSocial) {{ __('messages.settings_two_factor_not_social') }}@endif</p>

    @include('profile.partials.notice', ['noticeDemo' => true])

    @php
        $twoFactorPending = $twoFactorUser->two_factor_secret && ! $twoFactorUser->two_factor_confirmed_at;
    @endphp

    {{-- What the last action did, said once at the top of the section. --}}
    @if (session('status') === 'two-factor-confirmed')
        <p class="mb-4"><span class="event-status is-on" role="status">{{ __('messages.two_factor_confirmed_message') }}</span></p>
    @elseif (session('status') === 'two-factor-disabled')
        <p class="mb-4"><span class="event-status" role="status">{{ __('messages.two_factor_disabled_message') }}</span></p>
    @elseif (session('status') === 'recovery-codes-regenerated')
        <p class="mb-4"><span class="event-status is-on" role="status">{{ __('messages.two_factor_codes_regenerated') }}</span></p>
    @endif

    <div class="form-kit-fields">
    {{-- State 1: Not enabled --}}
    @if (! $twoFactorUser->two_factor_secret)
        {{-- Off: said, with what switching it on involves. The section used to open on a bare
             "Current Password" field. --}}
        <div class="event-picked mb-5">
            <div class="settings-picked-main">
                <span class="event-status">{{ __('messages.disabled') }}</span>
            </div>
        </div>
        @if ($twoFactorUser->hasPassword())
        <p class="event-hint">{{ __('messages.settings_two_factor_how') }}</p>
        @else
        {{-- No password to confirm, and none for the code to be asked beside. --}}
        <p class="event-hint">{{ __('messages.settings_two_factor_how_no_password') }}</p>
        @endif
        <form method="POST" action="{{ route('two-factor.enable') }}" class="space-y-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}" data-no-dirty>
            @csrf

            @if ($twoFactorUser->hasPassword())
            <div>
                <x-input-label for="2fa_current_password" :value="__('messages.current_password')" />
                <x-password-input id="2fa_current_password" name="current_password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
            </div>
            @endif

            <x-brand-button type="submit" :disabled="is_demo_mode()">{{ __('messages.two_factor_enable') }}</x-brand-button>
        </form>

    {{-- State 2: Enabled but not confirmed (pending) --}}
    @elseif ($twoFactorPending)
        <div class="space-y-6">
            @include('profile.partials.notice', ['noticeText' => __('messages.two_factor_confirm_instructions'), 'noticeClass' => 'mb-0'])

            {{-- QR Code --}}
            <div class="flex justify-center">
                @php
                    $google2fa = new \PragmaRX\Google2FA\Google2FA;
                    $appName = config('app.name', 'Event Schedule');
                    $qrCodeUrl = $google2fa->getQRCodeUrl($appName, $twoFactorUser->email, $twoFactorUser->two_factor_secret);

                    $dataUri = \App\Utils\QrCodeUtils::dataUri($qrCodeUrl);
                @endphp
                <img src="{{ $dataUri }}" alt="QR Code" class="rounded-lg">
            </div>

            {{-- Manual entry key --}}
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.two_factor_manual_entry') }}</p>
                <code class="text-sm font-mono bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded select-all text-gray-900 dark:text-gray-100">{{ $twoFactorUser->two_factor_secret }}</code>
            </div>

            @include('profile.partials.two-factor-codes')

            {{-- Confirm form --}}
            <form method="POST" action="{{ route('two-factor.confirm') }}" data-no-dirty>
                @csrf

                <div>
                    <x-input-label for="2fa_confirm_code" :value="__('messages.two_factor_code')" />
                    <x-text-input id="2fa_confirm_code" class="block mt-1 w-full" type="text" name="code" autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]*" maxlength="6" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                {{-- Setting up could not be backed out of: Disable was only offered once it was
                     confirmed. Cancel goes through the same door as Disable. --}}
                <div class="flex flex-wrap items-center gap-4 mt-4">
                    @if (! is_demo_mode())
                        @if ($twoFactorUser->hasPassword())
                        <button type="button" class="event-link event-link-quiet" data-reveal="two-factor-disable" aria-expanded="{{ $errors->has('current_password') ? 'true' : 'false' }}">{{ __('messages.cancel') }}</button>
                        @else
                        <button type="submit" form="two-factor-disable" class="event-link event-link-quiet">{{ __('messages.cancel') }}</button>
                        @endif
                    @endif
                    <x-brand-button type="submit" :disabled="is_demo_mode()">{{ __('messages.two_factor_confirm') }}</x-brand-button>
                </div>
            </form>
        </div>

    {{-- State 3: Enabled and confirmed --}}
    @else
        <div class="event-picked">
            <div class="settings-picked-main">
                <span class="event-status is-on">{{ __('messages.two_factor_enabled') }}</span>
            </div>
            @if (! is_demo_mode())
            <div class="settings-picked-actions">
                {{-- The codes in use stop working the moment new ones are made, so it asks first. --}}
                <form method="POST" action="{{ route('two-factor.recovery-codes') }}" data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <button type="submit" class="event-link">{{ __('messages.settings_regenerate_recovery_codes') }}</button>
                </form>
                @if ($twoFactorUser->hasPassword())
                <button type="button" id="2fa-disable-btn" class="event-link is-danger" data-reveal="two-factor-disable" aria-expanded="{{ $errors->has('current_password') ? 'true' : 'false' }}">{{ __('messages.two_factor_disable') }}</button>
                @else
                <button type="submit" form="two-factor-disable" id="2fa-disable-btn" class="event-link is-danger">{{ __('messages.two_factor_disable') }}</button>
                @endif
            </div>
            @endif
        </div>

        @include('profile.partials.two-factor-codes')
    @endif

    {{-- Switching it off (and backing out of a setup). With a password it asks for it here, in the
         page, with the message beside the field when it is wrong: this was a browser prompt whose
         refusal showed nowhere. Without one there is nothing to ask for, so it confirms instead of
         going through on the click. --}}
    @if ($twoFactorUser->two_factor_secret && ! is_demo_mode())
        @if ($twoFactorUser->hasPassword())
        <form id="two-factor-disable" method="POST" action="{{ route('two-factor.disable') }}" class="event-add-box" data-no-dirty @unless ($errors->has('current_password')) hidden @endunless>
            @csrf
            <x-input-label for="2fa_disable_password" :value="__('messages.two_factor_enter_password_to_disable')" />
            <x-password-input id="2fa_disable_password" name="current_password" class="mt-1 block w-full" autocomplete="current-password" />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
            <div class="mt-3">
                <x-danger-button class="settings-danger">{{ __('messages.two_factor_disable') }}</x-danger-button>
            </div>
        </form>
        @else
        <form id="two-factor-disable" method="POST" action="{{ route('two-factor.disable') }}" data-confirm="{{ __('messages.are_you_sure') }}" class="hidden">
            @csrf
        </form>
        @endif
    @endif
    </div>
</section>
