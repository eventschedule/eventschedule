@if (\App\Utils\TurnstileUtils::isEnabled())
    @once
        <script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer {!! nonce_attr() !!}></script>
    @endonce
    <div class="cf-turnstile mt-4" data-sitekey="{{ \App\Utils\TurnstileUtils::getSiteKey() }}" data-size="flexible"></div>
    <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
@endif
