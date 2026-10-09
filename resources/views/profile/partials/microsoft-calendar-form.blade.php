<section>
    @include('profile.partials.heading')
    {{-- "Connect your Outlook calendar ..." is an invitation, for somebody who has not. --}}
    @unless (auth()->user()->microsoft_token)
    <p class="form-kit-lead">{{ __('messages.microsoft_settings_description') }}</p>
    @endunless

    @include('profile.partials.notice', ['noticeDemo' => true])

    <div class="form-kit-fields {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
        @if (auth()->user()->microsoft_token)
            <div class="event-picked">
                <div class="settings-picked-main">
                    <span class="event-status is-on">{{ __('messages.microsoft_calendar_connected') }}</span>
                </div>
                <div class="settings-picked-actions">
                    <form method="POST" action="{{ route('microsoft.calendar.disconnect') }}" class="inline">
                        @csrf
                        <button type="submit" id="disconnect-microsoft-calendar" data-confirm="{{ __('messages.settings_confirm_disconnect_calendar') }}" class="event-link is-danger">{{ __('messages.disconnect') }}</button>
                    </form>
                </div>
            </div>
            {{-- Connected is half of it: nothing syncs until a schedule is told to. --}}
            <p class="event-hint mt-3">{{ __('messages.settings_calendar_sync_per_schedule', ['section' => __('messages.integrations')]) }}</p>

        @elseif (! config('services.microsoft.client_id'))
            <div class="p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg flex items-start gap-3">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                </svg>
                <p class="text-sm text-amber-800 dark:text-amber-200">
                    {{ __('messages.microsoft_calendar_not_configured') }}
                    <x-link href="{{ marketing_url('/docs/selfhost/microsoft-calendar') }}" target="_blank">{{ __('messages.learn_more') }}</x-link>
                </p>
            </div>

        @else
            {{-- The same outline as the Google and Facebook buttons, with Microsoft's own mark. --}}
            <a href="{{ is_demo_mode() ? '#' : route('microsoft.calendar.redirect') }}"
                class="inline-flex items-center justify-center px-4 py-3 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 rounded-md font-semibold text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800 transition ease-in-out duration-150">
                <svg class="w-5 h-5 me-2" viewBox="0 0 23 23" xmlns="http://www.w3.org/2000/svg" aria-hidden="true">
                    <path fill="#f25022" d="M1 1h10v10H1z"/>
                    <path fill="#7fba00" d="M12 1h10v10H12z"/>
                    <path fill="#00a4ef" d="M1 12h10v10H1z"/>
                    <path fill="#ffb900" d="M12 12h10v10H12z"/>
                </svg>
                {{ __('messages.connect_microsoft_calendar') }}
            </a>
        @endif
    </div>
</section>
