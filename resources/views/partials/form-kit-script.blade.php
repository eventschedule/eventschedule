{{-- The script behind partials/form-kit-styles: rows that open in place, the line under a tab's
     name, the dot on a tab with unsaved changes, and opening whatever holds a field that needs
     attention. Plain DOM script on purpose: the pages that use it are server-rendered and wired by
     element id, so nothing here may re-create their markup (a Vue mount over them would, and would
     compile their text as a template). The page says which function shows a section
     (FormKit.showSection) and registers a function per summary. --}}
<script {!! nonce_attr() !!}>
(function () {
    'use strict';

    var kit = window.FormKit = { showSection: null };
    var summaries = {};
    var dirty = {};
    var armed = false;
    var ERRORS = 'ul.text-red-600, ul.text-red-400, [data-field-error]:not(:empty)';

    function emit(name, detail) {
        document.dispatchEvent(new CustomEvent('formkit:' + name, { detail: detail || {} }));
    }

    // Rows. One of a list is open at a time, and pressing the open one closes it.
    function paneOf(button) {
        return document.getElementById(button.getAttribute('aria-controls'));
    }

    function rowsOf(group) {
        return Array.prototype.slice.call(document.querySelectorAll('button[data-row-group="' + group + '"]'));
    }

    function setRow(button, open) {
        button.setAttribute('aria-expanded', open ? 'true' : 'false');
        var pane = paneOf(button);
        if (pane) {
            pane.hidden = ! open;
        }
    }

    kit.openRow = function (group, tab) {
        var opened = null;
        rowsOf(group).forEach(function (button) {
            var match = button.getAttribute('data-tab') === tab;
            if (match) {
                opened = button;
            }
            setRow(button, match);
        });
        if (opened) {
            emit('row', { group: group, tab: tab, open: true, pane: paneOf(opened) });
        }

        return opened;
    };

    kit.closeRows = function (group) {
        rowsOf(group).forEach(function (button) {
            setRow(button, false);
        });
        emit('row', { group: group, tab: null, open: false, pane: null });
    };

    kit.openTab = function (group) {
        var open = rowsOf(group).filter(function (button) {
            return button.getAttribute('aria-expanded') === 'true';
        })[0];

        return open ? open.getAttribute('data-tab') : '';
    };

    document.addEventListener('click', function (event) {
        var button = event.target.closest ? event.target.closest('button[data-row-group]') : null;
        if (! button || button.disabled) {
            return;
        }
        var group = button.getAttribute('data-row-group');
        if (button.getAttribute('aria-expanded') === 'true') {
            kit.closeRows(group);
        } else {
            kit.openRow(group, button.getAttribute('data-tab'));
        }
    });

    // Anything between an element and its tab that is folded away with the hidden attribute (what a
    // switch shows once it is on, a form behind its link) is shown. A row's own pane is left to
    // its button, which reveal() presses.
    function unfold(element) {
        var section = element.closest('.section-content');
        var node = element.parentElement;
        while (node && node !== section) {
            if (node.hidden && ! node.classList.contains('event-subrow-body')) {
                node.hidden = false;
            }
            node = node.parentElement;
        }
    }

    // Show the tab, and open the row, that holds an element.
    kit.reveal = function (element) {
        if (! element || ! element.closest) {
            return false;
        }
        var section = element.closest('.section-content');
        if (section && kit.showSection) {
            kit.showSection(section.id);
        }
        unfold(element);
        var pane = element.closest('.event-subrow-body');
        while (pane) {
            var button = pane.id ? document.querySelector('button[data-row-group][aria-controls="' + pane.id + '"]') : null;
            if (button && button.getAttribute('aria-expanded') !== 'true') {
                kit.openRow(button.getAttribute('data-row-group'), button.getAttribute('data-tab'));
            }
            pane = pane.parentElement ? pane.parentElement.closest('.event-subrow-body') : null;
        }

        return !! section;
    };

    // A link to a tab (by its id) or to anything inside one (a row's pane, a field).
    kit.openFromHash = function (hash) {
        var id = (hash || '').replace(/^#/, '');
        var element = id ? document.getElementById(id) : null;
        if (! element) {
            return false;
        }
        if (element.classList.contains('section-content')) {
            if (kit.showSection) {
                kit.showSection(element.id);
            }

            return true;
        }
        if (element.classList.contains('event-subrow-body')) {
            var button = document.querySelector('button[data-row-group][aria-controls="' + element.id + '"]');
            var section = element.closest('.section-content');
            if (section && kit.showSection) {
                kit.showSection(section.id);
            }
            if (button) {
                kit.openRow(button.getAttribute('data-row-group'), button.getAttribute('data-tab'));
            }

            return !! section;
        }

        return kit.reveal(element);
    };

    // The tabs a refused save left a message in, in page order; the first is opened.
    kit.errorSections = function () {
        return Array.prototype.slice.call(document.querySelectorAll('.section-content')).filter(function (section) {
            return section.querySelector(ERRORS);
        }).map(function (section) {
            return section.id;
        });
    };

    kit.routeErrors = function () {
        var messages = document.querySelectorAll('.section-content ' + ERRORS.split(', ').join(', .section-content '));
        // Every message is brought into view of its tab, not only the first: one left inside a
        // folded block said "Check: Gift Cards" and showed nothing there.
        Array.prototype.forEach.call(messages, unfold);
        if (messages.length) {
            kit.reveal(messages[0]);
        }

        return kit.errorSections();
    };

    // The line under a tab's name (keyed by the tab's id) and beside a row's ("group:tab").
    kit.summary = function (key, read) {
        summaries[key] = read;
    };

    kit.refresh = function () {
        Object.keys(summaries).forEach(function (key) {
            var value;
            try {
                value = summaries[key]();
            } catch (error) {
                value = '';
            }
            if (value === null || value === undefined) {
                value = '';
            }
            if (typeof value !== 'object') {
                value = { text: String(value) };
            }
            document.querySelectorAll('[data-summary="' + key + '"]').forEach(function (slot) {
                var text = slot.querySelector('bdi') || slot;
                text.textContent = value.text || '';
                slot.classList.toggle('is-empty', !! value.empty);
                slot.classList.toggle('is-warn', !! value.warn);
                // A row that has a chip (form-row's `chip`): what is chosen, as a swatch before
                // the words. `chip` is a CSS background; none hides it.
                var chip = slot.querySelector('.event-row-chip');
                if (chip) {
                    chip.hidden = ! value.chip;
                    chip.style.background = value.chip || '';
                }
            });
        });
    };

    // Unsaved changes, by tab. Nothing counts until the page has finished setting itself up.
    function paintDirty() {
        document.querySelectorAll('[data-dirty-dot]').forEach(function (dot) {
            dot.hidden = ! dirty[dot.getAttribute('data-dirty-dot')];
        });
    }

    kit.arm = function () {
        armed = true;
    };

    // For a browser test to wait on, where a fixed pause would be a guess about a slow machine.
    kit.isArmed = function () {
        return armed;
    };

    kit.markDirty = function (sectionId) {
        if (! armed || ! sectionId) {
            return;
        }
        if (! dirty[sectionId]) {
            dirty[sectionId] = true;
            paintDirty();
        }
        emit('dirty', { sections: kit.dirtySections() });
    };

    kit.clearDirty = function (sectionId) {
        if (sectionId) {
            delete dirty[sectionId];
        } else {
            dirty = {};
        }
        paintDirty();
        emit('dirty', { sections: kit.dirtySections() });
    };

    kit.dirtySections = function () {
        return Object.keys(dirty);
    };

    // Typing or choosing inside a tab marks it and brings its summary up to date. A change made by
    // a button (a row removed, a tile chosen) fires no input event: that code calls markDirty().
    kit.track = function (root, options) {
        options = options || {};
        ['input', 'change'].forEach(function (type) {
            root.addEventListener(type, function (event) {
                var target = event.target;
                if (! target || ! target.closest) {
                    return;
                }
                if (! (options.ignore && target.matches(options.ignore))) {
                    var section = target.closest('.section-content');
                    if (section) {
                        kit.markDirty(section.id);
                    }
                }
                kit.refresh();
            });
        });
    };

    // Small readers for the summaries.
    kit.value = function (selector) {
        var element = document.querySelector(selector);

        return element ? String(element.value || '').trim() : '';
    };

    kit.chosen = function (selector) {
        var select = document.querySelector(selector);
        var option = select && select.selectedOptions ? select.selectedOptions[0] : null;

        return option ? option.textContent.trim() : '';
    };

    kit.on = function (name) {
        var input = document.querySelector('input[type="checkbox"][name="' + name + '"]');

        return !! (input && input.checked);
    };

    kit.radio = function (name) {
        var input = document.querySelector('input[type="radio"][name="' + name + '"]:checked');

        return input ? input.value : '';
    };

    kit.join = function (parts) {
        return parts.filter(function (part) {
            return part !== '' && part !== null && part !== undefined && part !== false;
        }).join(' · ');
    };
})();
</script>
