{{--
    Shown to a signed-in visitor whose account does not hold the contact address on the schedule
    they are trying to claim.

    The masked address is the whole content of this page, and it is why the page exists rather than
    a flash message: a claimant needs to know WHICH account to come back with, and the value cannot
    go on the public page it was reached from. It is masked even here - it was typed by one third
    party about another, and the sign-in wall is a lower bar than the mailbox itself.
--}}
<x-app-guest-layout :role="$role" :fonts="$fonts" :no-index="true" :page-title="__('messages.claim_strip_cta')">

@include('partials.guest-form-pages-styles')
<div class="gk-stage gk-pg gk-pg-sm">
    <div class="gk-pg-head">
        @include('role.partials.form-page-who', ['whoRole' => $role, 'whoLink' => $role->getClaimUrl()])
        <h1 class="gk-title">
            {{ __('messages.claim_wrong_account_heading') }}
        </h1>

        <p class="gk-pg-lead">
            <x-user-text dir="{{ content_dir_for_language($role->translatedName(), $role->displayLanguageCode()) }}">{{ $holdsContact
                ? __('messages.claim_confirm_body', ['name' => $role->translatedName()])
                : __('messages.claim_wrong_account', ['name' => $role->translatedName(), 'contact' => $maskedContact]) }}</x-user-text>
        </p>
    </div>

    @if ($holdsContact)
    {{-- A POST, because this is where ownership actually moves. No honeypot: the form is
         authenticated, and the rule exempts those. Forward action last. --}}
    <form method="POST" action="{{ route('role.claim.confirm', ['subdomain' => $role->subdomain]) }}"
          class="gk-pg-actions mt-8">
        @csrf
        <a href="{{ $role->getClaimUrl() }}" class="gk-pg-quiet">
            {{ __('messages.back') }}
        </a>
        <button type="submit" class="gk-btn gk-btn-primary gk-cta-main">
            {{ __('messages.claim_strip_cta') }}
        </button>
    </form>
    @else
    {{-- No "switch account" button: logout is a POST, so an anchor to it 405s, and a form
         that signs somebody out as a side effect of reading a hint is worse than a sentence. --}}
    <div class="mt-8">
        <a href="{{ $role->getClaimUrl() }}" class="gk-btn gk-cta-alt">
            {{ __('messages.back') }}
        </a>
    </div>
    @endif
</div>

</x-app-guest-layout>
