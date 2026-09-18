{{--
    The page every set-password link lands on, for a first password and a genuine reset alike.

    Deliberately says the same thing in both cases. NewPasswordController::create() is a bare view
    with no throttle and no token validation (routes/auth.php), so branching the copy on
    User::isStub() would hand any anonymous caller an unlimited-rate oracle for "does this address
    have a passwordless account". The wording that is specific to a first password belongs in the
    mail, which App\Mail\SetPassword owns.
--}}
<x-auth-layout>
    <div class="text-center">
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('messages.reset_password_heading') }}
        </h2>
        {{-- bdi: an LTR address inside RTL prose reorders around punctuation in ar/he. --}}
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
            {!! __('messages.reset_password_body', [
                'email' => '<bdi dir="ltr" class="font-medium text-gray-900 dark:text-gray-100">'.e($request->email).'</bdi>',
            ]) !!}
        </p>
    </div>

    <form method="POST" action="{{ route('password.store') }}" class="mt-6">
        @csrf
        <x-honeypot />

        <!-- Password Reset Token -->
        <input type="hidden" name="token" value="{{ $request->route('token') }}">

        {{-- Readonly, not editable: the address is bound to the token, so editing it only ever
             produces "We can't find a user with that email address" from the framework's English
             passwords.php. Readonly rather than disabled - the broker needs it submitted.

             Visible rather than hidden, and autocomplete="username", so password managers attach
             the saved credential to an account instead of leaving it orphaned. --}}
        <div>
            <x-input-label for="email" :value="__('messages.email')" />
            <x-text-input id="email" name="email" type="email" readonly
                class="block mt-1 w-full bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300"
                autocomplete="username" :value="old('email', $request->email)" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        {{-- autofocus here rather than on the email above: that field is already filled from the
             link, and on a phone focusing it opens the keyboard over the field they actually need. --}}
        <div class="mt-4">
            <x-input-label for="password" :value="__('messages.password')" />
            <x-password-input id="password" class="block mt-1 w-full" name="password" required autofocus
                minlength="8" autocomplete="new-password" aria-describedby="password_hint" />
            <p id="password_hint" class="mt-1 text-xs text-gray-500 dark:text-gray-400">
                {{ __('messages.subscription_account_password_hint', ['min' => 8]) }}
            </p>
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-6">
            <x-primary-button>
                {{ __('messages.reset_password_button') }}
            </x-primary-button>
        </div>
    </form>
</x-auth-layout>
