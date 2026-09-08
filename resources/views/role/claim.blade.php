{{--
    Shown to a signed-in visitor whose account does not hold the contact address on the schedule
    they are trying to claim.

    The masked address is the whole content of this page, and it is why the page exists rather than
    a flash message: a claimant needs to know WHICH account to come back with, and the value cannot
    go on the public page it was reached from. It is masked even here - it was typed by one third
    party about another, and the sign-in wall is a lower bar than the mailbox itself.
--}}
<x-app-guest-layout :role="$role" :fonts="$fonts" :no-index="true" :page-title="__('messages.claim_strip_cta')">

<div class="container mx-auto max-w-xl px-0 sm:px-5 pt-8 pb-20 sm:pb-8">
    <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-6 sm:p-8">
        <h1 class="text-xl font-bold text-gray-900 dark:text-gray-100">
            {{ __('messages.claim_wrong_account_heading') }}
        </h1>

        <p class="mt-3 text-sm text-gray-600 dark:text-gray-400">
            <x-user-text>{{ __('messages.claim_wrong_account', ['name' => $role->translatedName(), 'contact' => $maskedContact]) }}</x-user-text>
        </p>

        {{-- No "switch account" button: logout is a POST, so an anchor to it 405s, and a form
             that signs somebody out as a side effect of reading a hint is worse than a sentence. --}}
        <div class="mt-6">
            <a href="{{ $role->getClaimUrl() }}" class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:underline">
                {{ __('messages.back') }}
            </a>
        </div>
    </div>
</div>

</x-app-guest-layout>
