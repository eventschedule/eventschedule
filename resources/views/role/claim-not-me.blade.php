{{--
    "This is not me": the other answer to a page the app created about somebody.

    Deliberately behind sign-in and a POST. It takes a public page down, so it cannot be a link a
    passer-by can aim at a competitor, and whoever asks has to be nameable in the audit log
    afterwards. What happens next is stated plainly here rather than promised vaguely, because the
    two outcomes are genuinely different and the visitor can tell which one applies to them.
--}}
<x-app-guest-layout :role="$role" :fonts="$fonts" :no-index="true" :page-title="__('messages.claim_not_me_heading')">

@include('partials.guest-form-pages-styles')
<div class="gk-stage gk-pg gk-pg-sm">
    <div class="gk-pg-head">
        @include('role.partials.form-page-who', ['whoRole' => $role, 'whoLink' => $role->getClaimUrl()])
        <h1 class="gk-title">
            {{ __('messages.claim_not_me_heading') }}
        </h1>

        <p class="gk-pg-lead">
            <x-user-text dir="{{ content_dir_for_language($role->translatedName(), $role->displayLanguageCode()) }}">{{ __('messages.claim_not_me_body', ['name' => $role->translatedName()]) }}</x-user-text>
        </p>
    </div>

    @auth
    {{-- No honeypot: authenticated forms never get one, because a password manager fills it. --}}
    {{-- Bare route(), NOT app_url(route(..., false)). On hosted this route lives in the tenant
         domain group, so route() already yields the absolute tenant URL, while app_url() would
         point it at https://app.<domain>/not-me - a host the tenant group explicitly excludes
         and where no such route exists. app_url() belongs on the app-domain /{subdomain}/...
         routes, which this is not. Invisible to the suite: routes/web.php gates the hosted
         block on `hosted && ! is_testing`, so only the path copies exist under PHPUnit and
         app_url() degrades to url(). --}}
    <form method="POST" action="{{ route('role.claim.not_me.submit', ['subdomain' => $role->subdomain]) }}"
          class="gk-pg-actions mt-8">
        @csrf
        <a href="{{ $role->getClaimUrl() }}" class="gk-pg-quiet">
            {{ __('messages.back') }}
        </a>
        <button type="submit" class="gk-btn gk-cta-main gk-pg-danger">
            {{ __('messages.claim_not_me_confirm') }}
        </button>
    </form>
    @else
    <p class="gk-pg-fine mt-8">
        {{ __('messages.claim_not_me_sign_in') }}
    </p>
    {{-- Posts to the same action rather than linking straight at sign-up: claimNotMeSubmit()
         parks a pending_claim for a signed-out visitor, which is what brings them back to the
         page afterwards. A bare sign-up link left them on the dashboard with no way back. --}}
    <form method="POST" action="{{ route('role.claim.not_me.submit', ['subdomain' => $role->subdomain]) }}" class="gk-pg-actions mt-4">
        @csrf
        <a href="{{ $role->getClaimUrl() }}" class="gk-pg-quiet">
            {{ __('messages.back') }}
        </a>
        <button type="submit" class="gk-btn gk-btn-primary gk-cta-main">
            {{ __('messages.sign_up') }}
        </button>
    </form>
    @endauth
</div>

</x-app-guest-layout>
