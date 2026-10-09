{{-- Where the list of a country widget (intl-tel-input: the phone field and the country field)
     opens. The widget draws its list under the field at z-index 2 and has no other way: on a form
     with a save bar the bar was drawn across the middle of the list, and near the bottom of the
     window the list ran out of it. Here the list is drawn over the bar, and it opens above its
     field where it does not fit below and does fit above. Opened above, the search box stays
     against the field, so it is the far end of the list that moves as a search narrows it.

     The rules are added to the head by script, as the country field's own are: both fields are
     also printed inside Vue mounts, and Vue drops a style tag from the markup it compiles. They
     are written one class heavier than the widget's, whose stylesheet is linked in the body, after
     the head. One listener on the document serves every widget on the page, whichever script
     started it. Both fields include this, and a page that has both is sent it once. --}}
@once
<script {!! nonce_attr() !!}>
(function () {
    if (window._itiListPlacement) return;
    window._itiListPlacement = true;

    var style = document.createElement('style');
    style.id = 'iti-list-placement';
    style.textContent = [
        // Over the save bar (30, and 40 where it is fixed) and the top bar (40); under the sidebar and a dialog (50).
        '.iti.iti--inline-dropdown .iti__dropdown-content { z-index: 45; }',
        '.iti.iti--dropup.iti--inline-dropdown .iti__dropdown-content { top: auto; bottom: 100%; margin-top: 0; margin-bottom: 3px; }',
        // Not while it is closed: the widget hides the list with a class of its own.
        '.iti.iti--dropup.iti--inline-dropdown .iti__dropdown-content:not(.iti__hide) { display: flex; flex-direction: column-reverse; }',
    ].join(' ');
    document.head.appendChild(style);

    function wrapperOf(event) {
        return event.target && event.target.closest ? event.target.closest('.iti') : null;
    }

    document.addEventListener('open:countrydropdown', function (event) {
        var wrapper = wrapperOf(event);
        var list = wrapper && wrapper.querySelector('.iti__dropdown-content');
        // On a phone the list is a popup over the whole window, and is not inside the wrapper.
        if (!list) return;

        wrapper.classList.remove('iti--dropup');
        var field = wrapper.getBoundingClientRect();
        var needed = list.offsetHeight + 8;
        if (needed > window.innerHeight - field.bottom && needed <= field.top) {
            wrapper.classList.add('iti--dropup');
        }
    });

    document.addEventListener('close:countrydropdown', function (event) {
        var wrapper = wrapperOf(event);
        if (wrapper) wrapper.classList.remove('iti--dropup');
    });
})();
</script>
@endonce
