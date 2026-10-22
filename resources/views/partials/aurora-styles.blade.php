{{-- Aurora, the page background drawn from the schedule's colours (App\Utils\Aurora). One
     stylesheet for the two places that draw it: the guest pages (partials/guest-background) and
     the Style tab's preview (StylePreview.vue), so the owner sees exactly what visitors will.

     The colours arrive as custom properties (Aurora::css(): --aurora-ground, --aurora-c0 to c3,
     --aurora-a, --aurora-grain), each light's place and drift in its own style="" (Aurora::
     lightStyles()), and the drift itself as keyframes with the numbers written out
     (Aurora::keyframes()), so the compositor can run it.

     Every light is a soft round gradient moved by transform alone: no filter, no blend mode,
     nothing the browser has to paint again while it moves. --aurora-u is a hundredth of the
     layer's longer side: the window on a guest page, the preview's frame in the Style tab. --}}
<style {!! nonce_attr() !!}>
    .gp-aurora { position: absolute; top: 0; right: 0; bottom: 0; left: 0; overflow: hidden; background: rgb(var(--aurora-ground)); --aurora-u: 1vmax; }
    @supports (width: 1cqmax) {
        .gp-aurora { container-type: size; --aurora-u: 1cqmax; }
    }
    .gp-aurora-m { position: absolute; top: 0; right: 0; bottom: 0; left: 0; }
    .gp-aurora[data-aurora-mirror="1"] > .gp-aurora-m { transform: scaleX(-1); }
    .gp-aurora-m > i {
        position: absolute;
        display: block;
        left: calc(var(--x) * 1%);
        top: calc(var(--y) * 1%);
        width: calc(var(--w) * var(--aurora-u));
        height: calc(var(--h) * var(--aurora-u));
        margin: calc(var(--h) * var(--aurora-u) * -.5) 0 0 calc(var(--w) * var(--aurora-u) * -.5);
        border-radius: 50%;
        opacity: calc(var(--a) * var(--aurora-a));
        {{-- An eased falloff: a gradient of two stops leaves a ring where it ends. --}}
        background: radial-gradient(closest-side,
            rgb(var(--c)) 0%,
            rgb(var(--c) / .92) 12%,
            rgb(var(--c) / .76) 26%,
            rgb(var(--c) / .54) 42%,
            rgb(var(--c) / .32) 58%,
            rgb(var(--c) / .15) 73%,
            rgb(var(--c) / .05) 87%,
            rgb(var(--c) / 0) 100%);
        animation-timing-function: cubic-bezier(.45, .05, .55, .95);
        animation-iteration-count: infinite;
        animation-direction: alternate;
        animation-fill-mode: both;
    }
    {{-- A layer of its own for a light only while it moves: each is a large texture, and a
         still frame (an embed, a graphic, a visitor who asked for less motion) needs none. --}}
    .gp-aurora[data-aurora-motion="on"] > .gp-aurora-m > i { will-change: transform; }
    {{-- A phone draws the three strongest lights. --}}
    @media (max-width: 767px) {
        .gp-aurora-m > i:nth-child(n+4) { display: none; }
    }
    {{-- Still: the owner's switch, the visitor's system setting, the accessibility widget's two
         modes, an embed and a graphic. One frame, the same whichever reason made it still, chosen
         by the seed. Not the widget's own .01ms duration, which would snap every light to the
         far end of its drift. --}}
    .gp-aurora[data-aurora-motion="off"] > .gp-aurora-m > i,
    html.es-a11y-reduce-motion .gp-aurora-m > i,
    html.es-a11y-high-contrast .gp-aurora-m > i {
        animation: none !important;
        will-change: auto !important;
        transform: translate3d(calc(var(--dx) * var(--t)), calc(var(--dy) * var(--t)), 0) scale(calc(1 + (var(--s) - 1) * var(--t))) rotate(calc(var(--r) * var(--t)));
    }
    @media (prefers-reduced-motion: reduce) {
        .gp-aurora-m > i {
            animation: none !important;
            will-change: auto !important;
            transform: translate3d(calc(var(--dx) * var(--t)), calc(var(--dy) * var(--t)), 0) scale(calc(1 + (var(--s) - 1) * var(--t))) rotate(calc(var(--r) * var(--t)));
        }
    }
    {{-- The grain that keeps a soft gradient from banding: a still tile of noise at its own
         pixel size, laid on with plain opacity (a blend mode would be composited every frame). --}}
    .gp-aurora-grain {
        position: absolute;
        top: 0; right: 0; bottom: 0; left: 0;
        opacity: var(--aurora-grain);
        background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='180' height='180'%3E%3Cfilter id='n'%3E%3CfeTurbulence type='fractalNoise' baseFrequency='.9' numOctaves='3' stitchTiles='stitch'/%3E%3CfeColorMatrix type='saturate' values='0'/%3E%3C/filter%3E%3Crect width='100%25' height='100%25' filter='url(%23n)'/%3E%3C/svg%3E");
        background-size: 180px 180px;
    }
</style>
