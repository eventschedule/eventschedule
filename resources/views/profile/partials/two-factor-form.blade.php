<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.two_factor_authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.two_factor_description') }}
        </p>
    </header>

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif

    @php $user = auth()->user(); @endphp

    {{-- State 1: Not enabled --}}
    @if (! $user->two_factor_secret)
        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-6 space-y-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
            @csrf

            @if ($user->hasPassword())
            <div>
                <x-input-label for="2fa_current_password" :value="__('messages.current_password')" />
                <x-password-input id="2fa_current_password" name="current_password" class="mt-1 block w-full" autocomplete="current-password" />
                <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
            </div>
            @endif

            <x-primary-button>{{ __('messages.two_factor_enable') }}</x-primary-button>
        </form>

    {{-- State 2: Enabled but not confirmed (pending) --}}
    @elseif (! $user->two_factor_confirmed_at)
        <div class="mt-6 space-y-6">
            <div class="p-4 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded-lg">
                <p class="text-sm text-yellow-800 dark:text-yellow-200">
                    {{ __('messages.two_factor_confirm_instructions') }}
                </p>
            </div>

            {{-- QR Code --}}
            <div class="flex justify-center">
                @php
                    $google2fa = new \PragmaRX\Google2FA\Google2FA;
                    $appName = config('app.name', 'Event Schedule');
                    $qrCodeUrl = $google2fa->getQRCodeUrl($appName, $user->email, $user->two_factor_secret);

                    $qrCode = new \Endroid\QrCode\QrCode($qrCodeUrl);
                    $qrCode->setSize(200);
                    $qrCode->setMargin(10);
                    $writer = new \Endroid\QrCode\Writer\PngWriter;
                    $result = $writer->write($qrCode);
                    $dataUri = $result->getDataUri();
                @endphp
                <img src="{{ $dataUri }}" alt="QR Code" class="rounded-lg">
            </div>

            {{-- Manual entry key --}}
            <div class="text-center">
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.two_factor_manual_entry') }}</p>
                <code class="text-sm font-mono bg-gray-100 dark:bg-gray-700 px-3 py-1.5 rounded select-all text-gray-900 dark:text-gray-100">{{ $user->two_factor_secret }}</code>
            </div>

            {{-- Recovery codes (shown once via flash) --}}
            @if (session('two_factor_recovery_codes'))
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.two_factor_recovery_codes_title') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.two_factor_recovery_codes_warning') }}</p>
                <div class="grid grid-cols-2 gap-1">
                    @foreach (session('two_factor_recovery_codes') as $code)
                        <code class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ $code }}</code>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Confirm form --}}
            <form method="POST" action="{{ route('two-factor.confirm') }}">
                @csrf

                <div>
                    <x-input-label for="2fa_confirm_code" :value="__('messages.two_factor_code')" />
                    <x-text-input id="2fa_confirm_code" class="block mt-1 w-full" type="text" name="code" autofocus autocomplete="one-time-code" inputmode="numeric" pattern="[0-9]*" maxlength="6" />
                    <x-input-error :messages="$errors->get('code')" class="mt-2" />
                </div>

                <div class="flex items-center gap-4 mt-4">
                    <x-primary-button>{{ __('messages.two_factor_confirm') }}</x-primary-button>
                </div>
            </form>
        </div>

    {{-- State 3: Enabled and confirmed --}}
    @else
        <div class="mt-6 space-y-6">
            <div class="p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg flex items-center gap-3">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                </svg>
                <p class="text-sm text-green-800 dark:text-green-200">
                    {{ __('messages.two_factor_enabled') }}
                </p>
            </div>

            {{-- Recovery codes (shown when regenerated) --}}
            @if (session('two_factor_recovery_codes'))
            <div class="p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.two_factor_recovery_codes_title') }}</p>
                <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.two_factor_recovery_codes_warning') }}</p>
                <div class="grid grid-cols-2 gap-1">
                    @foreach (session('two_factor_recovery_codes') as $code)
                        <code class="text-sm font-mono text-gray-700 dark:text-gray-300">{{ $code }}</code>
                    @endforeach
                </div>
            </div>
            @endif

            @if (! is_demo_mode())
            <div class="flex items-center gap-4">
                {{-- Regenerate recovery codes --}}
                <form method="POST" action="{{ route('two-factor.recovery-codes') }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('messages.two_factor_regenerate_codes') }}
                    </button>
                </form>

                {{-- Disable 2FA --}}
                <form method="POST" action="{{ route('two-factor.disable') }}">
                    @csrf

                    @if ($user->hasPassword())
                    <input type="hidden" name="current_password" id="2fa_disable_password_value">
                    @endif

                    <button type="submit" id="2fa-disable-btn" class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-500 active:bg-red-700 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                        {{ __('messages.two_factor_disable') }}
                    </button>
                </form>
            </div>

            @if ($user->hasPassword())
            <script {!! nonce_attr() !!}>
            document.getElementById('2fa-disable-btn').addEventListener('click', function(e) {
                e.preventDefault();
                var pw = prompt('{{ __("messages.two_factor_enter_password_to_disable") }}');
                if (pw !== null) {
                    document.getElementById('2fa_disable_password_value').value = pw;
                    this.closest('form').submit();
                }
            });
            </script>
            @endif
            @endif
        </div>
    @endif

    @if (session('status') === 'two-factor-confirmed')
        <p class="mt-4 text-sm text-green-600 dark:text-green-400">{{ __('messages.two_factor_confirmed_message') }}</p>
    @elseif (session('status') === 'two-factor-disabled')
        <p class="mt-4 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.two_factor_disabled_message') }}</p>
    @elseif (session('status') === 'recovery-codes-regenerated')
        <p class="mt-4 text-sm text-green-600 dark:text-green-400">{{ __('messages.two_factor_codes_regenerated') }}</p>
    @endif
</section>
