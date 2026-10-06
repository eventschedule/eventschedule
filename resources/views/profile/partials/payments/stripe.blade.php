    @if (config('app.hosted'))
        <p class="event-hint">{{ __('messages.stripe_help') }}</p>

        @if ($user->stripe_account_id)
            <div class="event-picked">
                <div class="settings-picked-main">
                    {{-- The company name once Stripe has one; until then the account id is all there is. --}}
                    <span class="settings-picked-name">{{ $user->stripe_company_name ?: $user->stripe_account_id }}</span>
                    @if ($user->stripe_completed_at)
                    <span class="event-status is-on">{{ __('messages.connected') }}</span>
                    @else
                    <span class="event-status is-warn">{{ __('messages.settings_setup_not_finished') }}</span>
                    @endif
                </div>
                <div class="settings-picked-actions">
                    <form method="POST" action="{{ route('stripe.unlink') }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
                        @csrf
                        <button type="submit" class="event-link is-danger" @disabled(is_demo_mode())>{{ __('messages.disconnect') }}</button>
                    </form>
                </div>
            </div>
        @endif

        @if (! $user->stripe_completed_at)
            <div class="mt-4">
                @if (is_demo_mode())
                    <x-brand-button disabled :title="__('messages.saving_disabled_demo_mode')">{{ __('messages.connect_stripe') }}</x-brand-button>
                @else
                    {{-- A link, because it leaves for Stripe: it was a button with a script that
                         set the address. --}}
                    <x-brand-link id="connect-stripe-btn" href="{{ route('stripe.link') }}">{{ __('messages.connect_stripe') }}</x-brand-link>
                @endif
            </div>
        @endif
    @else
        <p class="event-hint">{{ __('messages.stripe_selfhosted_help') }}</p>

        @if (config('services.stripe_platform.secret'))
            <p><span class="event-status is-on">{{ __('messages.stripe_configured') }}</span></p>
            <p class="event-hint">{{ __('messages.stripe_configured_help') }}</p>
        @else
            <p><span class="event-status">{{ __('messages.stripe_not_configured') }}</span></p>
            <p class="event-hint">{{ __('messages.stripe_not_configured_help') }}</p>
        @endif
    @endif
