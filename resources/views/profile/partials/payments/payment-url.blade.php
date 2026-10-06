    <p class="event-hint">{{ __('messages.payment_url_help') }}</p>

    @if ($user->payment_url)
        <div class="event-picked">
            <div class="settings-picked-main">
                <span class="settings-picked-name settings-mono" dir="ltr">{{ $user->payment_url }}</span>
                <span class="event-status is-on">{{ __('messages.connected') }}</span>
            </div>
            <div class="settings-picked-actions">
                <form method="POST" action="{{ route('profile.unlink_payment_url') }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <button type="submit" class="event-link is-danger" @disabled(is_demo_mode())>{{ __('messages.disconnect') }}</button>
                </form>
            </div>
        </div>
    @else
        <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data">
            @csrf
            @method('patch')

            <div>
                <x-input-label for="payment_url" :value="__('messages.payment_url') . ' *'" />
                <x-text-input id="payment_url" name="payment_url" type="url" class="mt-1 block w-full" dir="ltr" placeholder="https://"
                    :value="old('payment_url', $user->payment_url)" autocomplete="off" required :disabled="is_demo_mode()" />
                <x-input-error class="mt-2" :messages="$errors->get('payment_url')" />
            </div>

            @include('profile.partials.save', ['saveClass' => 'mt-4', 'saveLabel' => __('messages.connect')])
        </form>
    @endif
