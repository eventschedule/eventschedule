{{--
    The last panel of a page in the house style (partials/hp-kit): dark in both modes, the claim
    box and one button. A name typed in the box rides into sign-up (initClaim() in
    marketing-home.js finds the box by .es-claim and its button as the first link beside it).

    Props:
      lead         the sentence under the heading
      foot         the line under the box
      label        the button's words
      placeholder  the box's placeholder
    The slot is the heading.
--}}
@props([
    'lead' => null,
    'foot' => 'No credit card required. Free forever.',
    'label' => 'Start for free',
    'placeholder' => 'your-name',
])
@php
    // What follows the name in the box: this install's own domain. A white-label install is not
    // eventschedule.com, and the box must not promise an address on ours.
    $hpClaimHost = _base_domain();
    $hpClaimSuffix = '.' . (str_contains($hpClaimHost, '.') && ! filter_var($hpClaimHost, FILTER_VALIDATE_IP) ? $hpClaimHost : 'eventschedule.com');
@endphp
<section id="claim" {{ $attributes->class(['hp-sec']) }}>
    <div class="hp-wrap">
        <div class="hp-finale" data-reveal="panel" data-hp-confetti>
            <h2 class="hp-h2">{{ $slot }}</h2>
            @if ($lead)
                <p class="hp-lead">{{ $lead }}</p>
            @endif

            <div class="hp-claimrow">
                <label for="es-claim-input" class="sr-only">Your schedule name</label>
                <div dir="ltr" class="es-claim hp-claim">
                    <input id="es-claim-input" type="text" placeholder="{{ $placeholder }}" autocomplete="off" autocapitalize="none" spellcheck="false" maxlength="30">
                    <span class="hp-claim-suffix">{{ $hpClaimSuffix }}</span>
                </div>
                <a href="{{ app_url('/sign_up') }}" class="hp-btn hp-btn-primary">
                    {{ $label }}
                    <svg fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>

            @if ($foot)
                <p class="hp-finale-foot">{{ $foot }}</p>
            @endif
            {{ $after ?? '' }}
        </div>
    </div>
</section>

@once
    {{-- Confetti from the two lower corners, once, when the panel is seen. Drawn on a canvas of
         this page's own: the shared [data-confetti] handler asks the library for a worker, which
         the content security policy refuses without a word. --}}
    <script {!! nonce_attr() !!} src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" defer></script>
    <script {!! nonce_attr() !!}>
        (function () {
            document.addEventListener('DOMContentLoaded', function () {
                var finale = document.querySelector('[data-hp-confetti]');
                if (!finale || !('IntersectionObserver' in window) || window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
                    return;
                }
                var seen = new IntersectionObserver(function (entries) {
                    entries.forEach(function (entry) {
                        if (!entry.isIntersecting || !window.confetti || !window.confetti.create) {
                            return;
                        }
                        seen.disconnect();
                        var fire = window.confetti.create(null, { resize: true });
                        var colors = ['#4E81FA', '#0EA5E9', '#22D3EE', '#ffffff'];
                        fire({ particleCount: 80, angle: 60, spread: 60, startVelocity: 55, origin: { x: 0.06, y: 0.95 }, colors: colors, disableForReducedMotion: true });
                        fire({ particleCount: 80, angle: 120, spread: 60, startVelocity: 55, origin: { x: 0.94, y: 0.95 }, colors: colors, disableForReducedMotion: true });
                    });
                }, { threshold: 0.5 });
                seen.observe(finale);
            });
        })();
    </script>
@endonce
