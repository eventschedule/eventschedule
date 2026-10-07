{{-- The one script of the page kit (partials/admin-page-styles), loaded by layouts/app-admin.

     A list of the kit is a real table that a phone lays out as a stack of rows (display: block
     and flex), and a browser that sees that drops the table's meaning: a screen reader then reads
     a row as loose text. Saying the roles outright keeps it a table. A schedule's Team and
     Followers lists say them in their markup; the sixty lists built after them get them here, as
     do rows a page adds later (a fetched list, a Vue row). Plain DOM script, like the kit's pages.

     The stack also puts the headings out of sight, and with them the only way to sort a list or
     to tick every row. So a list that can be sorted, or has a box in its head, gets a small bar
     above it on a phone: "Sort by" as a dropdown of its sortable columns, a button that turns the
     order round, and "Select all". Each presses the real control in the hidden head, so the
     page's own script does what it always did. A list drawn by Vue is left alone: a node put
     among the ones Vue manages would be in its way. That includes a list Vue has not taken over
     YET: this script is in the head, so it hears DOMContentLoaded before a page whose app starts
     on that same event, and a bar built then became part of that app's template, redrawn by Vue
     without its listeners ("Select all" above the translation suggestions did nothing). So the
     bars wait until every listener of that event has run. --}}
<script {!! nonce_attr() !!}>
(function() {
    var roles = [
        ['table.page-table', 'table'],
        ['table.page-table > thead, table.page-table > tbody, table.page-table > tfoot', 'rowgroup'],
        ['table.page-table > * > tr', 'row'],
        ['table.page-table > * > tr > th[scope="row"]', 'rowheader'],
        ['table.page-table > * > tr > th:not([scope="row"])', 'columnheader'],
        ['table.page-table > * > tr > td', 'cell'],
    ];
    function mark(root) {
        roles.forEach(function(pair) {
            root.querySelectorAll(pair[0]).forEach(function(el) {
                if (! el.hasAttribute('role')) {
                    el.setAttribute('role', pair[1]);
                }
            });
        });
    }
    var words = {
        sortBy: @json(__('messages.sort_by_column')),
        reverse: @json(__('messages.reverse_order')),
        selectAll: @json(__('messages.select_all')),
    };
    // A heading's name without the arrow that says which way it is sorted.
    function nameOf(button) {
        var copy = button.cloneNode(true);
        copy.querySelectorAll('[aria-hidden="true"]').forEach(function(mark) { mark.remove(); });
        return copy.textContent.replace(/[\s\u2191\u2193]+$/, '').trim();
    }
    var settled = false;
    function tools(root) {
        if (! settled) {
            return;
        }
        root.querySelectorAll('table.page-table').forEach(function(table) {
            if (table.dataset.phoneTools || ! table.tHead || table.closest('[data-v-app]')) {
                return;
            }
            var sorts = Array.prototype.slice.call(table.tHead.querySelectorAll('button.page-sort[data-sort]'));
            var all = table.tHead.querySelector('input[type="checkbox"]');
            if (! sorts.length && ! all) {
                return;
            }
            table.dataset.phoneTools = '1';
            var bar = document.createElement('div');
            bar.className = 'page-table-phone';
            if (all) {
                var pick = document.createElement('button');
                pick.type = 'button';
                pick.className = 'event-link';
                pick.textContent = words.selectAll;
                pick.addEventListener('click', function() { all.click(); });
                bar.appendChild(pick);
            }
            if (sorts.length) {
                var select = document.createElement('select');
                select.className = 'rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100';
                select.setAttribute('autocomplete', 'off');
                var sorted = false;
                sorts.forEach(function(button, i) {
                    var option = document.createElement('option');
                    option.value = String(i);
                    option.textContent = words.sortBy.replace(':column', nameOf(button));
                    // On a phone the button is put away with the rest of the head (the kit hides
                    // the head's controls there), so the cell says the column's name itself.
                    if (! button.closest('th').hasAttribute('aria-label')) {
                        button.closest('th').setAttribute('aria-label', nameOf(button));
                    }
                    if (button.closest('th').hasAttribute('aria-sort')) {
                        option.selected = true;
                        sorted = true;
                    }
                    select.appendChild(option);
                });
                if (! sorted) {
                    var none = document.createElement('option');
                    none.value = '';
                    none.disabled = true;
                    none.selected = true;
                    none.textContent = words.sortBy.replace(':column', '').replace(/[\s:]+$/, '');
                    select.insertBefore(none, select.firstChild);
                }
                select.setAttribute('aria-label', words.sortBy.replace(':column', '').replace(/[\s:]+$/, ''));
                bar.appendChild(select);
                var flip = document.createElement('button');
                flip.type = 'button';
                flip.className = 'page-tool';
                flip.title = words.reverse;
                flip.setAttribute('aria-label', words.reverse);
                flip.innerHTML = '<svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 7.5L7.5 3m0 0L12 7.5M7.5 3v13.5m13.5 0L16.5 21m0 0L12 16.5m4.5 4.5V7.5" /></svg>';
                flip.addEventListener('click', function() {
                    if (select.value !== '') {
                        sorts[Number(select.value)].click();
                    }
                });
                // Nothing to turn round until the list is sorted by something: on a list with no
                // order of its own (a schedule's Team) the button was there and did nothing.
                flip.style.display = sorted ? '' : 'none';
                select.addEventListener('change', function() {
                    if (select.value !== '') {
                        flip.style.display = '';
                        sorts[Number(select.value)].click();
                    }
                });
                bar.appendChild(flip);
            }
            table.parentNode.insertBefore(bar, table);
        });
    }
    document.addEventListener('DOMContentLoaded', function() {
        mark(document);
        setTimeout(function() {
            settled = true;
            tools(document);
        }, 0);
        new MutationObserver(function(changes) {
            for (var i = 0; i < changes.length; i++) {
                for (var j = 0; j < changes[i].addedNodes.length; j++) {
                    var node = changes[i].addedNodes[j];
                    if (node.nodeType === 1 && (node.closest('table.page-table') || node.querySelector('table.page-table') || (node.matches && node.matches('table.page-table')))) {
                        mark(document);
                        tools(document);
                        return;
                    }
                }
            }
        }).observe(document.body, { childList: true, subtree: true });
    });
})();
</script>
