<section>
    <header>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            {{ __('messages.microsoft_settings') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.microsoft_settings_description') }}
        </p>
    </header>

    @if (is_demo_mode())
    <div class="mt-4 p-3 bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-700 rounded text-yellow-800 dark:text-yellow-200 text-sm">
        {{ __('messages.demo_mode_settings_disabled') }}
    </div>
    @endif

    <div class="mt-6 space-y-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
        {{-- Outlook Calendar Section --}}
        <div>
            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase tracking-wider">
                {{ __('messages.microsoft_calendar_integration') }}
            </h3>
            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.connect_microsoft_calendar_description') }}
            </p>

            <div class="mt-3">
                @if (auth()->user()->microsoft_token)
                    <div class="flex items-center justify-between p-4 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-lg">
                        <div class="flex items-center">
                            <svg class="w-5 h-5 text-green-600 dark:text-green-400 me-2" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                            </svg>
                            <span class="text-sm font-medium text-green-800 dark:text-green-200">
                                {{ __('messages.microsoft_calendar_connected') }}
                            </span>
                        </div>
                        <a href="{{ route('microsoft.calendar.disconnect') }}"
                           id="disconnect-microsoft-calendar"
                           data-confirm="{{ __('messages.are_you_sure') }}"
                           class="text-sm text-red-600 dark:text-red-400 hover:text-red-800 dark:hover:text-red-300">
                            {{ __('messages.disconnect') }}
                        </a>
                    </div>

                @elseif (! config('services.microsoft.client_id'))
                    <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg flex items-start gap-3">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <p class="text-sm text-amber-800 dark:text-amber-200">
                            {{ __('messages.microsoft_calendar_not_configured') }}
                            <x-link href="{{ marketing_url('/docs/selfhost/microsoft-calendar') }}" target="_blank">{{ __('messages.learn_more') }}</x-link>
                        </p>
                    </div>

                @elseif (is_demo_mode())
                    <span class="inline-flex items-center justify-center px-6 py-3 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest cursor-not-allowed">
                        <svg class="w-4 h-4 me-2" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#f25022" d="M1 1h10v10H1z"/>
                            <path fill="#7fba00" d="M12 1h10v10H12z"/>
                            <path fill="#00a4ef" d="M1 12h10v10H1z"/>
                            <path fill="#ffb900" d="M12 12h10v10H12z"/>
                        </svg>
                        <span class="text-white">
                            {{ __('messages.connect_microsoft_calendar') }}
                        </span>
                    </span>
                @else
                    <a href="{{ route('microsoft.calendar.redirect') }}"
                        class="inline-flex items-center justify-center px-6 py-3 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-sm text-white uppercase tracking-widest hover:bg-[var(--brand-button-bg-hover)] focus:bg-[var(--brand-button-bg-hover)] active:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 transition ease-in-out duration-150">
                        <svg class="w-4 h-4 me-2" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg">
                            <path fill="#f25022" d="M1 1h10v10H1z"/>
                            <path fill="#7fba00" d="M12 1h10v10H12z"/>
                            <path fill="#00a4ef" d="M1 12h10v10H1z"/>
                            <path fill="#ffb900" d="M12 12h10v10H12z"/>
                        </svg>
                        <span class="text-white">
                            {{ __('messages.connect_microsoft_calendar') }}
                        </span>
                    </a>
                @endif
            </div>
        </div>
    </div>
</section>
