<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
            {{ auth()->user()->hasPassword() ? __('messages.update_password') : __('messages.set_password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.ensure_your_account_is_using_a_long_random_password_to_stay_secure') }}
        </p>
    </header>

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif

    @if(!auth()->user()->hasPassword() && !session('can_set_password'))
        {{-- A social-only user re-authenticates with a provider they are linked to before
             setting a password. The Google button also stays the fallback for an account linked
             to neither (as it always was), and Facebook's appears only while it is configured. --}}
        @php
            $reauthFacebook = facebook_login_enabled() && auth()->user()->facebook_id;
            $reauthGoogle = auth()->user()->google_oauth_id || ! $reauthFacebook;
        @endphp
        <div class="mt-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ $reauthGoogle ? __('messages.google_reauth_to_set_password') : __('messages.facebook_reauth_to_set_password') }}
            </p>
            <div class="flex flex-wrap gap-3">
            @if ($reauthGoogle)
                <a href="{{ route('auth.google.set_password') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 me-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                        <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                        <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                        <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                    </svg>
                    {{ __('messages.verify_with_google') }}
                </a>
            @endif
            @if ($reauthFacebook)
                <a href="{{ route('auth.facebook.set_password') }}" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-lg font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                    <svg class="w-5 h-5 me-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                        <path fill="#1877F2" d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z"/>
                    </svg>
                    {{ __('messages.verify_with_facebook') }}
                </a>
            @endif
            </div>
        </div>
    @else
        <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6">
            @csrf
            @method('put')

            @if(auth()->user()->hasPassword())
            <div>
                <x-input-label for="update_password_current_password" :value="__('messages.current_password')" />
                <x-password-input id="update_password_current_password" name="current_password" class="mt-1 block w-full" autocomplete="current-password" :disabled="is_demo_mode()" />
                <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
            </div>
            @endif

            <div>
                <x-input-label for="update_password_password" :value="__('messages.new_password')" />
                <x-password-input id="update_password_password" name="password" class="mt-1 block w-full" autocomplete="new-password" :disabled="is_demo_mode()" />
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            <div class="flex items-center gap-4">
                @if (is_demo_mode())
                    <button type="button"
                        data-alert="{{ __('messages.saving_disabled_demo_mode') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                        {{ __('messages.save') }}
                    </button>
                @else
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>
                @endif

                @if (session('status') === 'password-updated')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-sm text-gray-600 dark:text-gray-400"
                    >{{ __('messages.saved') }}</p>
                @endif
            </div>
        </form>
    @endif
</section>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('[data-alert]').forEach(function(btn) {
        btn.addEventListener('click', function() {
            alert(this.dataset.alert);
        });
    });
});
</script>
