{{--
    "This is not me": the other answer to a page the app created about somebody.

    Deliberately behind sign-in and a POST. It takes a public page down, so it cannot be a link a
    passer-by can aim at a competitor, and whoever asks has to be nameable in the audit log
    afterwards. What happens next is stated plainly here rather than promised vaguely, because the
    two outcomes are genuinely different and the visitor can tell which one applies to them.
--}}
<x-app-guest-layout :role="$role" :fonts="$fonts" :no-index="true" :page-title="__('messages.claim_not_me_heading')">

<div class="container mx-auto max-w-xl px-0 sm:px-5 pt-8 pb-20 sm:pb-8">
    <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('messages.claim_not_me_heading') }}
        </h1>

        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            <x-user-text>{{ __('messages.claim_not_me_body', ['name' => $role->translatedName()]) }}</x-user-text>
        </p>

        @auth
        {{-- No honeypot: authenticated forms never get one, because a password manager fills it. --}}
        <form method="POST" action="{{ app_url(route('role.claim.not_me.submit', ['subdomain' => $role->subdomain], false)) }}"
              class="mt-6 flex flex-wrap items-center gap-x-5 gap-y-3">
            @csrf
            <a href="{{ $role->getClaimUrl() }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                {{ __('messages.back') }}
            </a>
            <button type="submit"
                class="inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-600 dark:focus:ring-offset-gray-900"
                style="background-color: #dc2626;">
                {{ __('messages.claim_not_me_confirm') }}
            </button>
        </form>
        @else
        <p class="mt-6 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.claim_not_me_sign_in') }}
        </p>
        <div class="mt-4">
            <a href="{{ app_url(route('sign_up', [], false)) }}"
               class="inline-flex items-center justify-center rounded-md px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:opacity-90"
               style="background-color: #2563eb;">
                {{ __('messages.sign_up') }}
            </a>
        </div>
        @endauth
    </div>
</div>

</x-app-guest-layout>
