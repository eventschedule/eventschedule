{{-- Rendered only when facebook_login_enabled(); see profile/edit.blade.php. --}}
<section>
    @include('profile.partials.heading')
    {{-- "Connect your Facebook account ..." is an invitation, for somebody who has not. --}}
    @unless (auth()->user()->facebook_id)
    <p class="form-kit-lead">{{ __('messages.facebook_account_description') }}</p>
    @endunless

    @include('profile.partials.notice', ['noticeDemo' => true])

    <div class="form-kit-fields {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
        @if (auth()->user()->facebook_id)
            <div class="event-picked">
                <div class="settings-picked-main">
                    <span class="event-status is-on">{{ __('messages.facebook_account_connected') }}</span>
                </div>
                @if (auth()->user()->canDisconnectSocialLogin('facebook'))
                <div class="settings-picked-actions">
                    <form method="POST" action="{{ route('auth.facebook.disconnect') }}" class="inline">
                        @csrf
                        <button type="submit" data-confirm="{{ __('messages.confirm_disconnect_facebook') }}" class="event-link is-danger">{{ __('messages.disconnect') }}</button>
                    </form>
                </div>
                @endif
            </div>
            {{-- What the connection is for, now that the invitation above it is gone. --}}
            <p class="event-hint mt-3">{{ __('messages.settings_social_login_connected', ['provider' => 'Facebook']) }}</p>
            @unless (auth()->user()->canDisconnectSocialLogin('facebook'))
                @include('profile.partials.notice', ['noticeText' => __('messages.cannot_disconnect_facebook_no_other_login'), 'noticeClass' => 'mt-3 mb-0'])
            @endunless
        @else
            <x-facebook-button :href="is_demo_mode() ? '#' : route('auth.facebook.connect')" class="sm:w-auto">
                {{ __('messages.connect_facebook_account') }}
            </x-facebook-button>
        @endif
    </div>
</section>
