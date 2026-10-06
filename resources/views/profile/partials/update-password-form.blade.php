<section>
    @include('profile.partials.heading')
    <p class="form-kit-lead">{{ __('messages.ensure_your_account_is_using_a_long_random_password_to_stay_secure') }}</p>

    @include('profile.partials.notice', ['noticeDemo' => true])

    @if(!auth()->user()->hasPassword() && !session('can_set_password'))
        {{-- A social-only user re-authenticates with a provider they are linked to before
             setting a password. The Google button also stays the fallback for an account linked
             to neither (as it always was), and Facebook's appears only while it is configured. --}}
        @php
            $reauthFacebook = facebook_login_enabled() && auth()->user()->facebook_id;
            $reauthGoogle = auth()->user()->google_oauth_id || ! $reauthFacebook;
        @endphp
        <div class="form-kit-fields {{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
            <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
                {{ $reauthGoogle ? __('messages.google_reauth_to_set_password') : __('messages.facebook_reauth_to_set_password') }}
            </p>
            <div class="flex flex-wrap gap-3">
            @if ($reauthGoogle)
                <x-google-button :href="route('auth.google.set_password')" class="sm:w-auto">{{ __('messages.verify_with_google') }}</x-google-button>
            @endif
            @if ($reauthFacebook)
                <x-facebook-button :href="route('auth.facebook.set_password')" class="sm:w-auto">{{ __('messages.verify_with_facebook') }}</x-facebook-button>
            @endif
            </div>
        </div>
    @else
        <form method="post" action="{{ route('password.update') }}" class="form-kit-fields space-y-6">
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
                <x-password-input id="update_password_password" name="password" class="mt-1 block w-full" autocomplete="new-password" :disabled="is_demo_mode()" aria-describedby="update_password_hint" />
                {{-- The rule was only learned by breaking it. The eye in the field shows what was
                     typed, which is why there is no second field to type it into again. --}}
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400" id="update_password_hint">{{ __('messages.password_min_chars') }}</p>
                <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
            </div>

            {{-- Named by what it does, where it said "Save". --}}
            @include('profile.partials.save', ['saveClass' => '', 'saveShown' => session('status') === 'password-updated', 'saveLabel' => auth()->user()->hasPassword() ? __('messages.update_password') : __('messages.set_password')])
        </form>
    @endif
</section>
