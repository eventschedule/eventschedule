<section>
    @include('profile.partials.heading')
    <p class="form-kit-lead">{{ __('messages.google_settings_description') }}</p>

    @include('profile.partials.notice', ['noticeDemo' => true])

    {{-- Two separate connections with Google, each on its own line: signing in with it, and
         letting schedules sync with its calendar. --}}
    <div class="form-kit-fields space-y-6 {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
        <div>
            <p class="event-group-label">{{ __('messages.google_account') }}</p>
            {{-- The invitation to connect is for somebody who has not. --}}
            @unless (auth()->user()->google_oauth_id)
            <p class="event-hint">{{ __('messages.google_account_description') }}</p>
            @endunless

            @if (auth()->user()->google_oauth_id)
                <div class="event-picked">
                    <div class="settings-picked-main">
                        <span class="event-status is-on">{{ __('messages.google_account_connected') }}</span>
                    </div>
                    @if (auth()->user()->canDisconnectSocialLogin('google'))
                    <div class="settings-picked-actions">
                        <form method="POST" action="{{ route('auth.google.disconnect') }}" class="inline">
                            @csrf
                            <button type="submit" data-confirm="{{ __('messages.settings_confirm_disconnect_google') }}" class="event-link is-danger">{{ __('messages.disconnect') }}</button>
                        </form>
                    </div>
                    @endif
                </div>
                {{-- What the connection is for, now that the invitation above it is gone. --}}
                <p class="event-hint mt-3">{{ __('messages.settings_social_login_connected', ['provider' => 'Google']) }}</p>
                @unless (auth()->user()->canDisconnectSocialLogin('google'))
                    {{-- The only way into the account: no Disconnect is offered, and this says why. --}}
                    @include('profile.partials.notice', ['noticeText' => __('messages.cannot_disconnect_google_no_password'), 'noticeClass' => 'mt-3 mb-0'])
                @endunless
            @else
                <x-google-button :href="is_demo_mode() ? '#' : route('auth.google.connect')" class="sm:w-auto">
                    {{ __('messages.connect_google_account') }}
                </x-google-button>
            @endif
        </div>

        <div>
            <p class="event-group-label">{{ __('messages.google_calendar_integration') }}</p>
            @unless (auth()->user()->google_token)
            <p class="event-hint">{{ __('messages.connect_google_calendar_description') }}</p>
            @endunless

            @if (auth()->user()->google_token)
                <div class="event-picked">
                    <div class="settings-picked-main">
                        <span class="event-status is-on">{{ __('messages.google_calendar_connected') }}</span>
                    </div>
                    <div class="settings-picked-actions">
                        <form method="POST" action="{{ route('google.calendar.disconnect') }}" class="inline">
                            @csrf
                            <button type="submit" id="disconnect-google-calendar" data-confirm="{{ __('messages.settings_confirm_disconnect_calendar') }}" class="event-link is-danger">{{ __('messages.disconnect') }}</button>
                        </form>
                    </div>
                </div>
                {{-- Connected is half of it: nothing syncs until a schedule is told to. --}}
                <p class="event-hint mt-3">{{ __('messages.settings_calendar_sync_per_schedule', ['section' => __('messages.integrations')]) }}</p>
            @else
                <x-google-button :href="is_demo_mode() ? '#' : route('google.calendar.redirect')" class="sm:w-auto">
                    {{ __('messages.connect_google_calendar') }}
                </x-google-button>
            @endif
        </div>
    </div>
</section>
