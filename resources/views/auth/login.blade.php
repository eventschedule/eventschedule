<x-auth-layout>
    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    @php
        $lastLoginMethod = \App\Utils\SocialLoginUtils::lastMethod();
    @endphp

    {{-- "Continue with Facebook" matched this account by email. Signing in below links it
         (SocialLoginUtils::consumePendingLink), so this is guidance, not an error. --}}
    @if (session('social_link_pending') === 'facebook')
    <div class="mb-4 flex gap-3 rounded-lg border border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-900/20 p-3" role="status">
        <svg class="w-5 h-5 shrink-0 text-[var(--brand-blue)]" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" /></svg>
        <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.facebook_link_requires_login') }}</p>
    </div>
    @endif

    {{-- Facebook returned no email: a phone-only account, or the permission was unticked. The
         retry asks again with auth_type=rerequest, without which Facebook remembers the "no". --}}
    @if (session('social_email_required') === 'facebook' && facebook_login_enabled())
    <div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3" role="alert">
        <div class="flex gap-3">
            <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" /></svg>
            <div class="text-sm text-gray-700 dark:text-gray-300">
                <p>{{ __('messages.facebook_email_required') }}</p>
                <x-link href="{{ route('auth.facebook', ['rerequest' => 1]) }}" class="mt-1 inline-block">{{ __('messages.try_again') }}</x-link>
            </div>
        </div>
    </div>
    @endif

    <form method="POST" action="{{ route('login') }}" class="w-full">
        @csrf
        <x-honeypot />

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('messages.email')" />
            {{-- is_string, not a bare request('email'): ?email[]=x hands this an ARRAY, which reaches
                 ComponentAttributeBag::__toString()'s trim() and 500s on an unauthenticated page. --}}
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email"
                :value="old('email', is_string(request('email')) ? request('email') : null)" required autofocus autocomplete="username" />

            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('messages.password')" />

            <x-password-input id="password" class="block mt-1 w-full"
                            name="password"
                            required autocomplete="current-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4 hidden">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded dark:bg-gray-900 border-gray-300 dark:border-gray-700 text-[var(--brand-blue)] shadow-sm focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" name="remember" CHECKED>
                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.remember_me') }}</span>
            </label>
        </div>

        <x-turnstile />

        <div class="flex items-center justify-end mt-4">
            <x-primary-button>
                {{ __('messages.log_in') }}
            </x-primary-button>
        </div>
    </form>

    @if (config('services.google.client_id') || facebook_login_enabled())
    <div class="w-full mt-6 mb-4">
        {{-- Flex rule with the label between the halves, not a line behind an opaque label:
             .auth-card is a gradient, so no flat colour can mask it. The two auth pages disagreed
             about which flat colour to use (gray-900 here, gray-800 on sign-up) and neither was
             right. Same fix as event/guest-submit.blade.php:569-571. --}}
        <div class="mb-8 flex items-center gap-4">
            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
            <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
            <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
        </div>

        {{-- Google first: it is the proven path and about half of all sign-ins. --}}
        <div class="space-y-3">
            @if (config('services.google.client_id'))
            <x-google-button :last-used="$lastLoginMethod === 'google'">{{ __('messages.log_in_with_google') }}</x-google-button>
            @endif
            @if (facebook_login_enabled())
            <x-facebook-button :last-used="$lastLoginMethod === 'facebook'">{{ __('messages.log_in_with_facebook') }}</x-facebook-button>
            @endif
        </div>

        {{-- The social buttons collect no checkbox, so the terms are stated beside it and
             pressing it is the consent. Both documents are replaceable by the operator, so
             policy_url() resolves them, never marketing_url(). --}}
        @if (config('app.hosted'))
        <p class="mt-3 text-xs text-center text-gray-500 dark:text-gray-400">
            {!! str_replace([':terms', ':privacy'], [
                '<a href="' . policy_url('terms') . '" target="_blank" class="underline hover:no-underline">' . __('messages.terms_of_service') . '</a>',
                '<a href="' . policy_url('privacy') . '" target="_blank" class="underline hover:no-underline">' . __('messages.privacy_policy') . '</a>'
            ], __('messages.by_continuing_you_accept')) !!}
        </p>
        @endif

        <div class="flex items-center {{ public_registration_enabled() ? 'justify-between' : 'justify-end' }} mt-8">
            @if (public_registration_enabled())
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" href="{{ route('sign_up') }}">
                {{ __('messages.create_new_account') }}
            </a>
            @endif
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                {{ __('messages.reset_password') }}
            </a>
        </div>
    </div>
    @else
        <div class="flex items-center {{ public_registration_enabled() ? 'justify-between' : 'justify-end' }} mt-8">
            @if (public_registration_enabled())
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" href="{{ route('sign_up') }}">
                {{ __('messages.create_new_account') }}
            </a>
            @endif
            <a class="hover:underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800" href="{{ route('password.request') }}">
                {{ __('messages.reset_password') }}
            </a>
        </div>
    @endif
    @include('partials.social-login-guard')
    <script {!! nonce_attr() !!}>
        document.cookie = "browser_timezone=" + Intl.DateTimeFormat().resolvedOptions().timeZone + ";path=/;max-age=3600;SameSite=Lax";
        document.cookie = "browser_language=" + (navigator.language || "en").substring(0, 2) + ";path=/;max-age=3600;SameSite=Lax";
    </script>
</x-auth-layout>
