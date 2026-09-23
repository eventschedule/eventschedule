{{-- Rendered only when facebook_login_enabled(); see profile/edit.blade.php. --}}
<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg class="w-6 h-6" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                <path fill="#1877F2" d="M12 2.04C6.5 2.04 2 6.53 2 12.06C2 17.06 5.66 21.21 10.44 21.96V14.96H7.9V12.06H10.44V9.85C10.44 7.34 11.93 5.96 14.22 5.96C15.31 5.96 16.45 6.15 16.45 6.15V8.62H15.19C13.95 8.62 13.56 9.39 13.56 10.18V12.06H16.34L15.89 14.96H13.56V21.96A10 10 0 0 0 22 12.06C22 6.53 17.5 2.04 12 2.04Z"/>
            </svg>
            {{ __('messages.facebook_settings') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.facebook_account_description') }}
        </p>
    </header>

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif

    <div class="mt-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
        @if (auth()->user()->facebook_id)
            <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                <div class="flex items-center">
                    <svg class="w-5 h-5 text-green-600 dark:text-green-400 me-2" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                    </svg>
                    <span class="text-sm font-medium text-green-800 dark:text-green-200">
                        {{ __('messages.facebook_account_connected') }}
                    </span>
                </div>
                @if (auth()->user()->canDisconnectSocialLogin('facebook'))
                    <form method="POST" action="{{ route('auth.facebook.disconnect') }}" class="inline">
                        @csrf
                        <button type="submit"
                           data-confirm="{{ __('messages.confirm_disconnect_facebook') }}"
                           class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                            {{ __('messages.disconnect') }}
                        </button>
                    </form>
                @endif
            </div>
            @unless (auth()->user()->canDisconnectSocialLogin('facebook'))
                <div class="mt-3 flex gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                    <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
                    <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.cannot_disconnect_facebook_no_other_login') }}</p>
                </div>
            @endunless
        @else
            <x-facebook-button :href="is_demo_mode() ? '#' : route('auth.facebook.connect')" class="sm:w-auto">
                {{ __('messages.connect_facebook_account') }}
            </x-facebook-button>
        @endif
    </div>
</section>
