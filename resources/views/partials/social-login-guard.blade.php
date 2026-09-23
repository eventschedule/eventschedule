{{--
    One tap per sign-in button. The provider round trip is slow on a phone, and a second tap
    starts a second OAuth state that invalidates the first, which comes back as "authentication
    failed". Plain script, not Vue: the auth pages mount no Vue app.

    Delegated on document so it runs AFTER the sign-up page's [data-requires-terms] listener: a
    click that listener cancels (terms unticked) must not leave the button marked busy.
--}}
<script {!! nonce_attr() !!}>
    (function () {
        document.addEventListener('click', function (e) {
            var a = e.target.closest('[data-social-login]');
            if (!a) {
                return;
            }
            if (a.dataset.busy) {
                e.preventDefault();
                return;
            }
            // A cancelled click, or one opening a new tab, leaves this page usable.
            if (e.defaultPrevented || e.button !== 0 || e.metaKey || e.ctrlKey || e.shiftKey || e.altKey) {
                return;
            }
            a.dataset.busy = '1';
            a.setAttribute('aria-disabled', 'true');
            a.classList.add('opacity-60', 'cursor-wait');
        });
        // Back from the provider restores this page from the bfcache with the button still busy.
        window.addEventListener('pageshow', function (e) {
            if (!e.persisted) {
                return;
            }
            document.querySelectorAll('[data-social-login]').forEach(function (a) {
                delete a.dataset.busy;
                a.removeAttribute('aria-disabled');
                a.classList.remove('opacity-60', 'cursor-wait');
            });
        });
    })();
</script>
