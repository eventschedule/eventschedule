{{--
    The event flyer's lightbox: a click on the flyer (the link inside #gp-flyer, marked
    data-flyer-open) shows the full-size original over the page. A small Vue app, replacing the
    Alpine island that used to wrap the flyer.

    The flyer is a real link to the original, so without JavaScript - or before this has booted - a
    click simply opens the image, and a modified or middle click is left to the browser: it is a
    link, so "open in new tab" works.

    Mounted on its own empty element at the end of the page rather than inside #gp-flyer: that card
    has a backdrop-filter, which would make it the containing block of a position:fixed overlay
    inside it. Not a gp- id: those are the documented custom-CSS hooks, and this is plumbing.

    The template is a JS string with no user data in it; the image address and alt text are read
    from the clicked link when it opens and bound with :src and :alt, which Vue sets as attributes
    and never compiles.
--}}
<div id="flyer-lightbox-app"></div>

<script {!! nonce_attr() !!}>window.Vue || document.write('<script src="{{ asset('js/vue.global.prod.js') }}"{!! nonce_attr() !!}><\/script>')</script>
<script {!! nonce_attr() !!}>
(function () {
    function mountFlyerLightbox() {
        const mountEl = document.getElementById('flyer-lightbox-app');

        if (!mountEl || typeof Vue === 'undefined') {
            return;
        }

        const { createApp, ref, nextTick, onMounted, onBeforeUnmount } = Vue;

        createApp({
            setup() {
                const open = ref(false);
                const src = ref('');
                const alt = ref('');
                const closeButton = ref(null);
                let trigger = null;

                const close = () => {
                    open.value = false;
                    document.body.style.overflow = '';

                    if (trigger) {
                        try { trigger.focus(); } catch (e) {}
                    }
                };

                const onClick = (e) => {
                    const link = e.target && e.target.closest ? e.target.closest('[data-flyer-open]') : null;

                    // Anything but a plain primary click is the browser's: a new tab, a new window.
                    if (!link || e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                        return;
                    }

                    e.preventDefault();

                    const img = link.querySelector('img');
                    src.value = link.getAttribute('href') || '';
                    alt.value = img ? (img.getAttribute('alt') || '') : '';
                    trigger = link;
                    open.value = true;
                    document.body.style.overflow = 'hidden';

                    nextTick(() => {
                        if (closeButton.value) {
                            closeButton.value.focus();
                        }
                    });
                };

                const onKeydown = (e) => {
                    if (open.value && e.key === 'Escape') {
                        close();
                    }
                };

                onMounted(() => {
                    document.addEventListener('click', onClick);
                    document.addEventListener('keydown', onKeydown);
                });

                onBeforeUnmount(() => {
                    document.removeEventListener('click', onClick);
                    document.removeEventListener('keydown', onKeydown);
                });

                return { open, src, alt, closeButton, close, closeLabel: @json(__('messages.close')) };
            },
            template: `
<div v-if="open"
    class="fixed inset-0 z-[70] flex items-center justify-center bg-black/90"
    style="font-family: sans-serif"
    role="dialog"
    aria-modal="true"
    :aria-label="alt"
    @click.self="close">
    <button type="button" ref="closeButton" @click="close" :aria-label="closeLabel"
        class="absolute top-3 {{ $role->isRtl() ? 'left-3' : 'right-3' }} text-white/80 hover:text-white text-4xl leading-none z-10 w-10 h-10 flex items-center justify-center">&times;</button>
    <img :src="src" :alt="alt" class="max-w-[96vw] max-h-[90vh] object-contain pointer-events-none">
</div>`
        }).mount(mountEl);
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', mountFlyerLightbox);
    } else {
        mountFlyerLightbox();
    }
})();
</script>
