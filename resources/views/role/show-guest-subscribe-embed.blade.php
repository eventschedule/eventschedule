{{--
    The embeddable signup form (?embed=true&form=subscribe, issue #125): the guest page's subscribe
    panel on a page of its own, for a schedule to put on its own website.

    Rendered by RoleSubscriberController::renderEmbed() for the GET, the POST outcome and the
    throttle bail alike, so all three draw the same frame. Expects $role, $subscribeEmbedState,
    $accentColor and $contrastColor.
--}}
<x-app-guest-layout :role="$role" :fonts="array_values(array_filter([$role->font_family]))" :paint-background="false">

    {{-- Transparent, so the card sits on the owner's website rather than on the schedule's own
         background, which cropped into a few hundred pixels only looks broken. After the layout's
         <head> styles, and more specific than its `body` rule, so this wins even over a solid
         background declared !important. Never a filter or transform here (see CLAUDE.md). --}}
    {{-- The card is a stage (partials/guest-stage-styles) with nothing of its own around it: no
         sheet colour, no shadow, no corners, no light over the top, so only the band itself (a
         card raised on it, in the schedule's stage colours) sits on the owner's website. --}}
    <style {!! nonce_attr() !!}>
        html, html body { background: transparent !important; min-height: 0 !important; }
        .gk-stage.gk-p-subembed { max-width: none; margin: 0; padding: .25rem; border-radius: 0; background: transparent; box-shadow: none; }
        .gk-stage.gk-p-subembed::before { content: none; }
        .gk-p-subembed .gk-band { padding: clamp(1.125rem, 4vw, 1.75rem); }
    </style>

    <div id="gp-subscribe-embed" class="gk-stage gk-p-subembed">
        @include('partials.subscribe-panel', [
            'subscribeEmbed' => true,
            'panelClass' => 'gk-card gk-band',
        ])
    </div>

    {{-- Tells the embedding page how tall the form is, so the snippet from the Embed dialog can size
         its iframe to fit: the form, an inline error and the "check your email" state all differ,
         and the fields stack on a narrow site. Only a number crosses, so '*' leaks nothing. The
         snippet keeps a fixed height for sites that strip its listener. --}}
    <script {!! nonce_attr() !!}>
    (function () {
        if (window.parent === window) {
            return;
        }

        var el = document.getElementById('gp-subscribe-embed');
        if (! el) {
            return;
        }

        var last = 0;
        function send() {
            var height = Math.ceil(el.getBoundingClientRect().bottom + window.scrollY);
            if (height === last) {
                return;
            }
            last = height;
            try {
                window.parent.postMessage({ type: 'eventschedule:resize', widget: 'subscribe', height: height }, '*');
            } catch (e) {}
        }

        if (window.ResizeObserver) {
            new ResizeObserver(send).observe(el);
        }
        window.addEventListener('load', send);
        send();
    })();
    </script>

</x-app-guest-layout>
