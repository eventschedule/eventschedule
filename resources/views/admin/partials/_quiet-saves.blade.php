{{-- One solid blue button per screen (the 2026-11 design language). A page made of several small
     forms, each with its own Save or Add (the platform settings, the blocklist, a schedule's admin
     page), marks each of those buttons data-quiet-until-change: it is drawn outlined until its own
     form is changed, and turns the brand blue from the first change, so the one blue on the screen
     is the form somebody is filling in. It still submits while quiet: nothing here disables it.

     A change made by a button fires no input event (a row added to a list, a colour picked), so a
     click on any other button inside the form counts too. After a refused save every one starts
     blue, since the page came back with what was typed. Include it once, anywhere on the page. --}}
<style {!! nonce_attr() !!}>
    [data-quiet-until-change]:not(.is-changed) {
        background-image: none !important;
        background-color: transparent !important;
        color: rgb(var(--ap-ink)) !important;
        border-color: rgb(var(--ap-border-strong)) !important;
        box-shadow: none !important;
    }
    [data-quiet-until-change]:not(.is-changed):hover {
        background-color: rgb(var(--ap-surface-hover)) !important;
    }
</style>
<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var refused = @json($errors->any());
        document.querySelectorAll('[data-quiet-until-change]').forEach(function (button) {
            var form = button.form || button.closest('form');
            var wake = function () { button.classList.add('is-changed'); };
            if (refused || ! form) { wake(); return; }
            form.addEventListener('input', wake);
            form.addEventListener('change', wake);
            form.addEventListener('click', function (event) {
                var pressed = event.target.closest('button, [role="button"]');
                if (pressed && pressed !== button && pressed.type !== 'submit') wake();
            });
        });
    });
</script>
