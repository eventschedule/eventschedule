{{--
    The body of an audience page written for the vendor map: /for-craft-fairs, /for-flea-markets
    and /for-conferences-and-expos. Each page keeps its own words, examples, questions, colours and
    typefaces, and its own sign-up link (which UseCasesPageTest reads from the page's own file);
    this partial lays them out around the product's real screens.

    $vma keys:
      signup     the page's sign-up address, with its kind (/sign_up?type=venue&vendor_map=...)
      eyebrow    the keyword pill over the headline
      h1         [first line, second line]
      lede       the sentence under the headline
      spot       the word for a spot ("table"), people the word for vendors ("makers")
      shotsNote  a line under the screens: they show a farmers market, the page's kind says its own words
      steps      [[title, body], x3]
      examplesKicker, examplesTitle, examplesLead, examples [[title, body], x6]
      linkTitle, linkLead, linkChecks [..]
      dayCards   [[title, body], x3]
      faqs       [['q' => .., 'a' => ..], ...]
      closing    [heading, lead]
      fonts      [heading family, body family], loaded by the page's headMeta
      light, dark  [bg, bg-2, card, line, ink, muted, accent, button, glow, tint, band-1, band-2]
    Claims are the vendor map's (docs/FEATURES.md "Vendor Map"): drawing, the shoppers' map, the QR
    sign, vendors by hand, moves, Not coming and cancelling a day are free; the vendor link and the
    AI draft from photos are Pro, gated on the map's schedule.
--}}
@php
    $vmaShot = fn (string $name) => asset('images/marketing/vendor-map/'.$name.'.webp');
    $vmaCheck = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="3"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>';
    // The page's colours: --l-* for light and --d-* for dark, mapped onto --vma-* below.
    $vmaStyle = '--vma-head: '.$vma['fonts'][0].'; --vma-body: '.$vma['fonts'][1].';';
    foreach (['l' => $vma['light'], 'd' => $vma['dark']] as $vmaMode => $vmaColours) {
        foreach ($vmaColours as $vmaName => $vmaValue) {
            $vmaStyle .= ' --'.$vmaMode.'-'.$vmaName.': '.$vmaValue.';';
        }
    }
    $vmaArrow = '<svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.2"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>';
@endphp

<style {!! nonce_attr() !!}>
    /* ==============================================================
       A vendor map audience page. The colours and the two typefaces
       are the page's own (set on .vma-page by the page); everything
       else is shared by the three pages that use this partial.

       NO DECORATIVE LINE DRAWINGS: the only pictures are the app's own
       screens. Depth comes from the ground's soft gradient and the
       cards' shade, never from an outline sketch of a stall.

       Contrast: every page sets --vma-ink, --vma-muted and --vma-accent
       for its own grounds, light and dark, at 4.5:1 or better.
       ============================================================== */
    .vma-page { --vma-bg: var(--l-bg); --vma-bg-2: var(--l-bg-2); --vma-card: var(--l-card); --vma-line: var(--l-line); --vma-ink: var(--l-ink); --vma-muted: var(--l-muted); --vma-accent: var(--l-accent); --vma-button: var(--l-button); --vma-glow: var(--l-glow); --vma-tint: var(--l-tint); --vma-band-1: var(--l-band-1); --vma-band-2: var(--l-band-2); }
    .dark .vma-page { --vma-bg: var(--d-bg); --vma-bg-2: var(--d-bg-2); --vma-card: var(--d-card); --vma-line: var(--d-line); --vma-ink: var(--d-ink); --vma-muted: var(--d-muted); --vma-accent: var(--d-accent); --vma-button: var(--d-button); --vma-glow: var(--d-glow); --vma-tint: var(--d-tint); --vma-band-1: var(--d-band-1); --vma-band-2: var(--d-band-2); }
    .vma-page { background: var(--vma-bg); color: var(--vma-ink); font-family: var(--vma-body), ui-sans-serif, system-ui, sans-serif; }
    .vma-page h1, .vma-page h2, .vma-page h3 { font-family: var(--vma-head), ui-serif, Georgia, serif; }
    .vma-wrap { max-width: 72rem; margin-inline: auto; padding-inline: 1rem; }
    @media (min-width: 640px) { .vma-wrap { padding-inline: 1.5rem; } }
    .vma-sec { padding-block: clamp(3.5rem, 8vw, 6rem); }
    .vma-sec.is-alt { background: var(--vma-bg-2); }
    .vma-muted { color: var(--vma-muted); }
    .vma-accent { color: var(--vma-accent); }

    .vma-hero { position: relative; overflow: hidden; padding-block: clamp(4rem, 10vw, 7rem) clamp(3rem, 7vw, 5rem); background: radial-gradient(60rem 26rem at 15% -10%, var(--vma-glow), transparent 70%), var(--vma-bg); }
    .vma-eyebrow { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.35rem 0.85rem; border: 1px solid var(--vma-line); border-radius: 999px; font-family: var(--vma-body), sans-serif; font-size: 0.8rem; font-weight: 700; letter-spacing: 0.04em; color: var(--vma-accent); background: var(--vma-card); }
    .vma-h1 { margin-top: 1.25rem; font-size: clamp(2.4rem, 6vw, 4.4rem); line-height: 1.04; font-weight: 800; letter-spacing: -0.02em; max-width: 18ch; }
    .vma-h1 .vma-h1-eyebrow { display: block; margin-bottom: 1.25rem; }
    .vma-lede { margin-top: 1.5rem; max-width: 40rem; font-size: 1.15rem; line-height: 1.65; color: var(--vma-muted); }
    .vma-actions { display: flex; flex-wrap: wrap; gap: 0.75rem; margin-top: 2rem; }
    .vma-btn { display: inline-flex; align-items: center; gap: 0.5rem; padding: 0.85rem 1.4rem; border-radius: 0.8rem; font-weight: 700; transition: all 0.2s ease; }
    .vma-btn svg { width: 1.1rem; height: 1.1rem; }
    .vma-btn:focus-visible { outline: 2px solid var(--vma-accent); outline-offset: 3px; }
    .vma-btn.is-primary { background: var(--vma-button); color: #ffffff; }
    .vma-btn.is-primary:hover { filter: brightness(1.08); }
    .vma-btn.is-ghost { border: 1px solid var(--vma-line); background: var(--vma-card); color: var(--vma-ink); }
    .vma-btn.is-ghost:hover { border-color: var(--vma-accent); }
    [dir="rtl"] .vma-btn svg { transform: rotate(180deg); }

    .vma-head { max-width: 44rem; }
    .vma-head.is-center { margin-inline: auto; text-align: center; }
    .vma-kicker { font-size: 0.78rem; font-weight: 800; letter-spacing: 0.16em; text-transform: uppercase; color: var(--vma-accent); }
    .vma-h2 { margin-top: 0.75rem; font-size: clamp(1.9rem, 4vw, 2.9rem); line-height: 1.1; font-weight: 800; letter-spacing: -0.015em; }
    .vma-lead { margin-top: 1rem; font-size: 1.08rem; line-height: 1.65; color: var(--vma-muted); }

    .vma-card { border: 1px solid var(--vma-line); border-radius: 1.1rem; background: var(--vma-card); box-shadow: 0 1px 2px rgba(0, 0, 0, 0.04), 0 16px 36px -24px rgba(0, 0, 0, 0.35); }
    .vma-grid-3 { display: grid; gap: 1rem; }
    @media (min-width: 768px) { .vma-grid-3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .vma-grid-2x3 { display: grid; gap: 1rem; }
    @media (min-width: 640px) { .vma-grid-2x3 { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (min-width: 1024px) { .vma-grid-2x3 { grid-template-columns: repeat(3, minmax(0, 1fr)); } }
    .vma-tile { display: flex; flex-direction: column; padding: 1.4rem; }
    .vma-tile b { font-size: 0.8rem; font-weight: 800; letter-spacing: 0.1em; color: var(--vma-accent); }
    .vma-tile h3 { margin-top: 0.4rem; font-size: 1.15rem; font-weight: 700; line-height: 1.3; }
    .vma-tile p { margin-top: 0.5rem; font-size: 0.95rem; line-height: 1.6; color: var(--vma-muted); }

    .vma-split { display: grid; gap: 2.5rem; align-items: center; }
    @media (min-width: 1024px) {
        .vma-split { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 3.5rem; }
        .vma-split.is-wide { grid-template-columns: minmax(0, 2.2fr) minmax(0, 1fr); align-items: start; gap: 2rem; }
    }
    .vma-frame { overflow: hidden; border: 1px solid var(--vma-line); border-radius: 1rem; background: var(--vma-card); box-shadow: 0 24px 50px -30px rgba(0, 0, 0, 0.45); }
    .vma-frame img, .vma-phone img { display: block; width: 100%; height: auto; }
    .vma-frame img.is-night { display: none; }
    .dark .vma-frame img.is-night { display: block; }
    .dark .vma-frame img.is-day { display: none; }
    .vma-phone { overflow: hidden; max-width: 18rem; margin-inline: auto; border: 1px solid var(--vma-line); border-radius: 1.9rem; background: #0b1220; box-shadow: 0 24px 50px -30px rgba(0, 0, 0, 0.5); }
    .vma-cap { margin-top: 0.75rem; text-align: center; font-size: 0.85rem; color: var(--vma-muted); }
    .vma-phones { display: grid; grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 1rem; align-items: start; }
    .vma-phones > :last-child { margin-top: 2.5rem; }

    .vma-checks { display: grid; gap: 0.8rem; margin-top: 1.5rem; }
    .vma-checks li { display: flex; gap: 0.7rem; align-items: flex-start; line-height: 1.55; color: var(--vma-muted); }
    .vma-ok { display: inline-flex; flex: none; align-items: center; justify-content: center; width: 1.35rem; height: 1.35rem; margin-top: 0.12rem; border-radius: 999px; background: var(--vma-tint); color: var(--vma-accent); }
    .vma-ok svg { width: 0.8rem; height: 0.8rem; }

    .vma-pill { display: inline-flex; align-items: center; min-height: 1.45rem; padding: 0 0.6rem; border-radius: 999px; font-size: 0.7rem; font-weight: 800; letter-spacing: 0.08em; text-transform: uppercase; }
    .vma-pill.is-free { background: rgba(16, 185, 129, 0.13); color: #047857; }
    .dark .vma-pill.is-free { background: rgba(16, 185, 129, 0.18); color: #6ee7b7; }
    .vma-pill.is-pro { background: rgba(78, 129, 250, 0.13); color: #1d4ed8; }
    .dark .vma-pill.is-pro { background: rgba(78, 129, 250, 0.2); color: #a5c0ff; }
    .vma-plans { display: grid; gap: 1rem; }
    @media (min-width: 768px) { .vma-plans { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    .vma-plan { display: flex; flex-direction: column; padding: 1.5rem; }
    .vma-plan ul { margin-top: 1rem; }
    .vma-plan .vma-plan-foot { margin-top: auto; padding-top: 1.25rem; font-size: 0.85rem; color: var(--vma-muted); }

    .vma-faq { display: grid; gap: 0.75rem; max-width: 48rem; margin: 2.5rem auto 0; }
    .vma-faq details { padding: 1.1rem 1.3rem; }
    .vma-faq summary { display: flex; align-items: center; justify-content: space-between; gap: 1rem; cursor: pointer; list-style: none; font-weight: 700; }
    .vma-faq summary::-webkit-details-marker { display: none; }
    .vma-faq summary svg { width: 1.2rem; height: 1.2rem; flex: none; transition: transform 0.2s ease; color: var(--vma-accent); }
    .vma-faq details[open] summary svg { transform: rotate(45deg); }
    .vma-faq summary:focus-visible { outline: 2px solid var(--vma-accent); outline-offset: 4px; border-radius: 0.4rem; }
    .vma-faq p { margin-top: 0.75rem; font-size: 0.95rem; line-height: 1.65; color: var(--vma-muted); }

    .vma-band { border-radius: 1.75rem; padding: clamp(2.5rem, 6vw, 4rem) clamp(1.25rem, 5vw, 3.5rem); text-align: center; background: linear-gradient(160deg, var(--vma-band-1), var(--vma-band-2)); color: #ffffff; }
    .vma-band h2 { font-size: clamp(1.9rem, 4vw, 2.8rem); font-weight: 800; line-height: 1.1; }
    .vma-band p { margin: 1rem auto 0; max-width: 36rem; font-size: 1.08rem; line-height: 1.6; color: rgba(255, 255, 255, 0.86); }
    .vma-band .vma-actions { justify-content: center; }
    .vma-band .vma-btn.is-light { background: #ffffff; color: #0f172a; }
    .vma-band .vma-btn.is-outline { border: 1px solid rgba(255, 255, 255, 0.4); color: #ffffff; }
    .vma-band .vma-btn:focus-visible { outline-color: #ffffff; }
</style>

<div class="vma-page" style="{{ $vmaStyle }}">

    {{-- 1. Hero: words only --}}
    <section id="top" class="vma-hero" style="scroll-margin-top: 5rem;">
        <div class="vma-wrap">
            <h1 class="vma-h1">
                <span class="vma-h1-eyebrow"><x-marketing.hero-eyebrow class="vma-eyebrow">{{ $vma['eyebrow'] }}</x-marketing.hero-eyebrow></span>
                {{ $vma['h1'][0] }} <span class="vma-accent">{{ $vma['h1'][1] }}</span>
            </h1>
            <p class="vma-lede">{{ $vma['lede'] }}</p>
            <div class="vma-actions">
                <a href="{{ app_url($vma['signup']) }}" class="vma-btn is-primary">{{ $vma['cta'] }} {!! $vmaArrow !!}</a>
                <a href="{{ marketing_url('/features/vendor-map') }}" class="vma-btn is-ghost">How the vendor map works</a>
            </div>
            <p class="vma-muted mt-5 text-sm">Free to draw and share. The vendor link and the map from photos are part of Pro.</p>
        </div>
    </section>

    {{-- 2. The real screens --}}
    <section id="screens" class="vma-sec is-alt" style="scroll-margin-top: 4rem;">
        <div class="vma-wrap">
            <div class="vma-head is-center">
                <span class="vma-kicker">The map</span>
                <h2 class="vma-h2">{{ $vma['screensTitle'] }}</h2>
                <p class="vma-lead">{{ $vma['screensLead'] }}</p>
            </div>
            <div class="vma-split is-wide mt-10">
                <figure>
                    <div class="vma-frame">
                        <img src="{{ $vmaShot('tab-desktop-light') }}" width="1440" height="1400" loading="lazy" decoding="async" class="is-day"
                             alt="The Vendor map tab: a card with the vendor link and Send on WhatsApp, a card for shoppers, the coming dates, the map, and the requests waiting for an answer">
                        <img src="{{ $vmaShot('tab-desktop-dark') }}" width="1440" height="1400" loading="lazy" decoding="async" class="is-night"
                             alt="The Vendor map tab in dark mode, with the vendor link, the dates, the map and the requests waiting">
                    </div>
                    <figcaption class="vma-cap">Your side: the Vendor map tab.</figcaption>
                </figure>
                <figure>
                    <div class="vma-phone">
                        <img src="{{ $vmaShot('map-phone') }}" width="780" height="1688" loading="lazy" decoding="async"
                             alt="The visitors' map on a phone, with numbered spots in named areas, a search box and the list of who is here that day">
                    </div>
                    <figcaption class="vma-cap">Their side: the map from the QR code.</figcaption>
                </figure>
            </div>
            <p class="vma-muted mt-8 text-center text-sm">{{ $vma['shotsNote'] }}</p>
        </div>
    </section>

    {{-- 3. Three steps --}}
    <section id="how" class="vma-sec" style="scroll-margin-top: 4rem;">
        <div class="vma-wrap">
            <div class="vma-head is-center">
                <span class="vma-kicker">How it works</span>
                <h2 class="vma-h2">{{ $vma['howTitle'] }}</h2>
            </div>
            <ol class="vma-grid-3 mt-10">
                @foreach ($vma['steps'] as $vmaStepIndex => [$vmaStepTitle, $vmaStepBody])
                    <li class="vma-card vma-tile">
                        <b aria-hidden="true">{{ sprintf('%02d', $vmaStepIndex + 1) }}</b>
                        <h3>{{ $vmaStepTitle }}</h3>
                        <p>{{ $vmaStepBody }}</p>
                    </li>
                @endforeach
            </ol>
        </div>
    </section>

    {{-- 4. The page's own examples --}}
    <section id="examples" class="vma-sec is-alt" style="scroll-margin-top: 4rem;">
        <div class="vma-wrap">
            <div class="vma-head is-center">
                <span class="vma-kicker">{{ $vma['examplesKicker'] }}</span>
                <h2 class="vma-h2">{{ $vma['examplesTitle'] }}</h2>
                <p class="vma-lead">{{ $vma['examplesLead'] }}</p>
            </div>
            <div class="vma-grid-2x3 mt-10">
                @foreach ($vma['examples'] as [$vmaExTitle, $vmaExBody])
                    <div class="vma-card vma-tile">
                        <h3>{{ $vmaExTitle }}</h3>
                        <p>{{ $vmaExBody }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- 5. The vendor link --}}
    <section id="vendor-link" class="vma-sec" style="scroll-margin-top: 4rem;">
        <div class="vma-wrap">
            <div class="vma-split">
                <div class="vma-phones">
                    <figure>
                        <div class="vma-phone">
                            <img src="{{ $vmaShot('claim-phone') }}" width="780" height="1688" loading="lazy" decoding="async"
                                 alt="The vendor link on a phone: Is one of these you?, with the spots by number, the names already on them and the free ones">
                        </div>
                    </figure>
                    <figure>
                        <div class="vma-phone">
                            <img src="{{ $vmaShot('claim-map-phone') }}" width="780" height="940" loading="lazy" decoding="async"
                                 alt="The same link showing the map, where each vendor taps their own spot">
                        </div>
                    </figure>
                </div>
                <div>
                    <span class="vma-kicker">The vendor link</span>
                    <h2 class="vma-h2">{{ $vma['linkTitle'] }}</h2>
                    <p class="vma-lead">{{ $vma['linkLead'] }}</p>
                    <ul class="vma-checks">
                        @foreach ($vma['linkChecks'] as $vmaLinkCheck)
                            <li><span class="vma-ok">{!! $vmaCheck !!}</span><span>{{ $vmaLinkCheck }}</span></li>
                        @endforeach
                    </ul>
                    <p class="vma-muted mt-6 flex flex-wrap items-center gap-3 text-sm"><span class="vma-pill is-pro">Pro</span><span>Adding {{ $vma['people'] }} yourself is free on every plan.</span></p>
                </div>
            </div>
        </div>
    </section>

    {{-- 6. On the day, and the sign at the door --}}
    <section id="on-the-day" class="vma-sec is-alt" style="scroll-margin-top: 4rem;">
        <div class="vma-wrap">
            <div class="vma-split">
                <div>
                    <span class="vma-kicker">On the day</span>
                    <h2 class="vma-h2">{{ $vma['dayTitle'] }}</h2>
                    <p class="vma-lead">{{ $vma['dayLead'] }}</p>
                    <div class="mt-6 grid gap-3">
                        @foreach ($vma['dayCards'] as [$vmaDayTitle, $vmaDayBody])
                            <div class="vma-card vma-tile">
                                <h3>{{ $vmaDayTitle }}</h3>
                                <p>{{ $vmaDayBody }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
                <figure>
                    <div class="vma-frame mx-auto max-w-md">
                        <img src="{{ $vmaShot('sign') }}" width="900" height="1270" loading="lazy" decoding="async"
                             alt="The printable entrance sign: the whole map with You are here by the entrance, and a large QR code to scan with a phone camera">
                    </div>
                    <figcaption class="vma-cap">The entrance sign: A4, or a 300 dpi image for the print shop.</figcaption>
                </figure>
            </div>
        </div>
    </section>

    {{-- 7. Plans --}}
    <section id="plans" class="vma-sec" style="scroll-margin-top: 4rem;">
        <div class="vma-wrap">
            <div class="vma-head is-center">
                <span class="vma-kicker">Which plan</span>
                <h2 class="vma-h2">The map is free. <span class="vma-accent">The shortcuts are Pro.</span></h2>
            </div>
            <div class="vma-plans mt-10">
                <div class="vma-card vma-plan">
                    <span class="vma-pill is-free self-start">Free</span>
                    <ul class="vma-checks">
                        @foreach (['Drawing the map: rows, single '.$vma['spot'].'s, areas and landmarks', 'Adding '.$vma['people'].' yourself, for every date or for one', 'Moves, Not coming, and cancelling a day', 'The visitors\' map, the QR entrance sign and the map on your website'] as $vmaFree)
                            <li><span class="vma-ok">{!! $vmaCheck !!}</span><span>{{ $vmaFree }}</span></li>
                        @endforeach
                    </ul>
                    <p class="vma-plan-foot">On every plan, with no limit on {{ $vma['people'] }} or dates.</p>
                </div>
                <div class="vma-card vma-plan">
                    <span class="vma-pill is-pro self-start">Pro</span>
                    <ul class="vma-checks">
                        @foreach (['The vendor link: '.$vma['people'].' claim their own '.$vma['spot'].' and keep it up to date', 'Reviewing requests on the map, and Approve all without conflicts', 'A first draft of the map by AI, from photos or the plan you already print'] as $vmaPro)
                            <li><span class="vma-ok">{!! $vmaCheck !!}</span><span>{{ $vmaPro }}</span></li>
                        @endforeach
                    </ul>
                    <p class="vma-plan-foot">The plan is yours, never theirs. A selfhosted install has all of it.</p>
                </div>
            </div>
        </div>
    </section>

    {{-- 8. Questions --}}
    <section id="faq" class="vma-sec is-alt" style="scroll-margin-top: 4rem;">
        <div class="vma-wrap">
            <div class="vma-head is-center">
                <span class="vma-kicker">Questions</span>
                <h2 class="vma-h2">{{ $vma['faqTitle'] }}</h2>
            </div>
            <div class="vma-faq">
                @foreach ($vma['faqs'] as $vmaFaq)
                    <details class="vma-card" @if ($loop->first) open @endif>
                        <summary><span>{{ $vmaFaq['q'] }}</span><svg aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 6v12m6-6H6" /></svg></summary>
                        <p>{{ $vmaFaq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <x-seo.faq-schema :items="$vma['faqs']" />

    <x-marketing.related-pages />

    {{-- 9. Closing --}}
    <section id="claim" class="vma-sec" style="scroll-margin-top: 4rem;">
        <div class="vma-wrap">
            <div class="vma-band">
                <h2>{{ $vma['closing'][0] }}</h2>
                <p>{{ $vma['closing'][1] }}</p>
                <div class="vma-actions">
                    <a href="{{ app_url($vma['signup']) }}" class="vma-btn is-light">{{ $vma['cta'] }} {!! $vmaArrow !!}</a>
                    <a href="{{ marketing_url('/docs/vendor-maps') }}" class="vma-btn is-outline">Read the guide</a>
                </div>
                <p class="text-sm">No credit card required.</p>
            </div>
        </div>
    </section>
</div>
