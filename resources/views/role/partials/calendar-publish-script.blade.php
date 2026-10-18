{{-- The calendar rows of Integrations (role/edit): each provider's "Publish new events
     automatically" shows while its direction reads the calendar, warns while it is on, and tells
     the save bar what Save will change (formkit:says). Choosing another calendar switches it off,
     since it was said for a calendar. The main-calendar panel stands under the select while the
     chosen calendar is the account's main one. Plain DOM script: the form is not a Vue app. --}}
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function () {
    var says = {
        on: @json(__('messages.calendar_says_publishing_on'), JSON_UNESCAPED_UNICODE),
        off: @json(__('messages.calendar_says_publishing_off'), JSON_UNESCAPED_UNICODE),
        changed: @json(__('messages.calendar_says_calendar_changed'), JSON_UNESCAPED_UNICODE),
    };
    var directionNames = { google: 'sync_direction', microsoft: 'microsoft_sync_direction', caldav: 'caldav_sync_direction' };

    function say(provider, text) {
        document.dispatchEvent(new CustomEvent('formkit:says', { detail: { key: 'calendar-publish-' + provider, text: text } }));
    }

    function readsFor(provider) {
        var chosen = Array.prototype.slice.call(document.querySelectorAll('input[name="' + directionNames[provider] + '"]')).filter(function (radio) { return radio.checked; })[0];
        return !! chosen && (chosen.value === 'from' || chosen.value === 'both');
    }

    Object.keys(directionNames).forEach(function (provider) {
        var wrap = document.querySelector('[data-calendar-publish="' + provider + '"]');
        if (! wrap) {
            return;
        }
        var toggle = wrap.querySelector('[data-calendar-publish-toggle]');
        var warning = wrap.querySelector('[data-calendar-publish-warning]');
        var radios = Array.prototype.slice.call(document.querySelectorAll('input[name="' + directionNames[provider] + '"]'));
        var saved = toggle && toggle.getAttribute('data-saved') === '1';

        // The drafts waiting now, in a sentence the server made with their count.
        var waitingSays = wrap.getAttribute('data-waiting-says') || '';

        function reads() {
            return readsFor(provider);
        }

        function paint() {
            wrap.hidden = ! reads();
            if (warning) {
                warning.hidden = ! (toggle && toggle.checked);
            }
            paintMain(provider);
            // Nothing to say about arrivals from a calendar that is not being read.
            if (! reads()) {
                say(provider, '');
                return;
            }
            var on = !! (toggle && toggle.checked);
            var text = on !== saved ? (on ? says.on : says.off) : '';
            if (on && on !== saved && waitingSays) {
                text += ' ' + waitingSays;
            }
            say(provider, text);
        }

        radios.forEach(function (radio) { radio.addEventListener('change', paint); });
        if (toggle) {
            toggle.addEventListener('change', paint);
        }
        paint();

        var select = document.getElementById(provider + '-calendar-select');
        if (select && toggle) {
            select.addEventListener('change', function () {
                // The switch was drawn for the calendar the page opened with: turned on again
                // after choosing another, it is a new word (RoleController::savePublishOnArrival()).
                var rendered = document.querySelector('input[name="' + provider + '_publish_rendered"]');
                if (rendered) {
                    rendered.value = '0';
                }
                if (toggle.checked) {
                    toggle.checked = false;
                    toggle.dispatchEvent(new Event('change', { bubbles: true }));
                    say(provider, says.changed);
                }
            });
        }
    });

    function paintMain(provider) {
        var select = document.getElementById(provider + '-calendar-select');
        var panel = document.querySelector('[data-main-calendar-panel="' + provider + '"]');
        if (! select || ! panel) {
            return;
        }
        var option = select.options[select.selectedIndex];
        // Only while the sync reads the calendar: pushing out to the main calendar is harmless.
        panel.hidden = ! (readsFor(provider) && option && option.dataset.primary === '1');
    }

    ['google', 'microsoft'].forEach(function (provider) {
        var select = document.getElementById(provider + '-calendar-select');
        if (select) {
            select.addEventListener('change', function () { paintMain(provider); });
        }
    });
    window.addEventListener('calendar-list-loaded', function (event) {
        paintMain(event.detail.provider);
    });

    document.addEventListener('click', function (event) {
        var button = event.target.closest ? event.target.closest('[data-calendar-refresh]') : null;
        if (! button) {
            return;
        }
        var provider = button.getAttribute('data-calendar-refresh');
        if (provider === 'google' && typeof loadGoogleCalendars === 'function') {
            loadGoogleCalendars();
        } else if (provider === 'microsoft' && typeof loadMicrosoftCalendars === 'function') {
            loadMicrosoftCalendars();
        }
    });
});
</script>
