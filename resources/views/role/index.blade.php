<x-app-admin-layout>

    <x-slot name="head">
        {{-- What the list of followed schedules needs beyond the page kit: a narrow column for the
             row's checkbox, and on a phone the checkbox and the name sharing the row's first line
             (the kit gives a list's first cell the whole line). --}}
        <style {!! nonce_attr() !!}>
            .following-list .c-check {
              width: 1%;
              padding-inline-end: 0;
            }
            .following-list .c-check input {
              display: block;
            }
            /* An address stays on one line where there is a table to give it a column, and breaks
               where it must in a phone's row. */
            .following-list .c-contact {
              white-space: nowrap;
            }
            .following-list tbody tr {
              cursor: pointer;
            }
            .following-search {
              position: relative;
            }
            .following-search input {
              width: 100%;
              padding-inline-end: 2.25rem;
            }
            .following-search button {
              position: absolute;
              inset-block: 0;
              inset-inline-end: 0.5rem;
              display: flex;
              align-items: center;
              color: rgb(var(--ap-ink-4));
            }
            .following-search button:hover {
              color: rgb(var(--ap-ink-2));
            }
            /* A class that sets display outranks the hidden attribute. */
            .following-search button[hidden],
            #bulk-action-btn[hidden] {
              display: none;
            }
            /* A phone's row: the tick, the name and the row's menu on one line, and whatever the
               schedule shows of its addresses beneath, under the name. */
            @media (max-width: 639.98px) {
              .following-list .c-check {
                flex: none;
                width: auto;
              }
              .following-list .c-main {
                flex: 1 1 0;
              }
              /* The kit gives a link in a phone's row room for a thumb at its sides, which set a
                 name that is a link in from one that is not, and its second line out from its first. */
              .following-list .c-main a.event-link {
                margin-inline: 0;
                padding-inline: 0;
              }
              .following-list .c-actions {
                order: 2;
                margin-inline-start: 0;
              }
              .following-list .c-contact {
                order: 3;
                flex: 1 1 100%;
                padding-inline-start: 1.875rem;
                white-space: normal;
                overflow-wrap: anywhere;
              }
            }
        </style>
    </x-slot>

    <div class="page-shell">
        <x-page-header :title="__('messages.following')" :lead="__('messages.following_lead')" />

        <div class="page-stack">
            {{-- Somebody who wanted a newsletter has just been handed the admin portal, and the only
                 sentence explaining why used to be a three-second toast. A persistent panel instead,
                 with the way back to the schedule they actually came for.

                 Its own session key rather than the shared 'message': that one is toasted by
                 layouts/app.blade.php for the whole app, and this needs to stay on the page. --}}
            @if (session('subscriber_welcome'))
            <x-page-notice tone="success">
                {{ session('subscriber_welcome') }}
                @if (session('subscriber_welcome_url'))
                <x-slot name="action">
                    <x-link href="{{ session('subscriber_welcome_url') }}">{{ __('messages.back_to_schedule') }}</x-link>
                </x-slot>
                @endif
            </x-page-notice>
            @endif

            @if (! empty($duplicateVenueCount) && $duplicateVenueCount > 0 && ! request()->filter)
            <x-page-notice tone="warn" id="duplicate-venues-notice">
                {{ str_replace(':count', $duplicateVenueCount, __('messages.possible_duplicate_venues_banner')) }}
                <x-slot name="action">
                    <a href="{{ route('following.merge_venues') }}" class="event-link">{{ __('messages.review') }}</a>
                </x-slot>
            </x-page-notice>
            @endif

            <div>
                <div class="page-filters">
                    <div class="page-filter is-grow following-search">
                        <label for="filter" class="sr-only">{{ __('messages.filter') }}</label>
                        <x-text-input type="text" name="filter" id="filter" placeholder="{{ __('messages.filter') }}"
                            value="{{ request()->filter }}" autocomplete="off" />
                        <button type="button" id="clear-filter" aria-label="{{ __('messages.clear_filter') }}" hidden>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                    <form id="bulk-action-form" method="POST" action="{{ route('following.bulk-unfollow') }}" class="is-end">
                        @csrf
                        <input type="hidden" name="subdomains" id="bulk-subdomains" value="">
                        <button type="submit" id="bulk-action-btn" class="page-tool" hidden>
                            {{ __('messages.unfollow') }}
                        </button>
                    </form>
                </div>

                <div id="following-table">
                    @include('role.following_table')
                </div>
            </div>
        </div>
    </div>

<script {!! nonce_attr() !!}>
(function() {
    let timeoutId;
    let currentSortBy = @json($sortBy);
    let currentSortDir = @json($sortDir);
    const filterInput = document.getElementById('filter');
    const clearButton = document.getElementById('clear-filter');
    const bulkActionBtn = document.getElementById('bulk-action-btn');
    const bulkSubdomainsInput = document.getElementById('bulk-subdomains');
    const bulkActionForm = document.getElementById('bulk-action-form');
    const followingTable = document.getElementById('following-table');

    const unfollowLabel = @json(__('messages.unfollow'));
    const deleteLabel = @json(__('messages.delete'));
    const confirmMessage = @json(__('messages.are_you_sure'));
    const copiedLabel = @json(__('messages.copied'));
    const syncError = @json(__('messages.sync_error'));
    const notConnected = @json(__('messages.google_calendar_not_connected'));
    const selectCalendar = @json(__('messages.select_your_calendar'));
    const calendarsUrl = @json(url('/google-calendar/calendars'));
    const memberSyncUrl = @json(url('/google-calendar/member-sync'));

    clearButton.hidden = ! filterInput.value;

    filterInput.addEventListener('input', function(e) {
        clearTimeout(timeoutId);
        clearButton.hidden = ! e.target.value;
        timeoutId = setTimeout(updateResults, 500);
    });

    clearButton.addEventListener('click', function() {
        filterInput.value = '';
        clearButton.hidden = true;
        updateResults();
        filterInput.focus();
    });

    function updateResults() {
        const params = new URLSearchParams();
        if (filterInput.value) {
            params.append('filter', filterInput.value);
        }
        params.append('sort_by', currentSortBy);
        params.append('sort_dir', currentSortDir);

        fetch(`${window.location.pathname}?${params.toString()}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(response => response.text())
        .then(html => {
            followingTable.innerHTML = html;
            updateBulkActionButton();
        })
        .catch(() => {});
    }

    function rowCheckboxes() {
        return Array.from(followingTable.querySelectorAll('.row-checkbox'));
    }

    function updateSelectAllState() {
        const selectAll = document.getElementById('select-all');
        const boxes = rowCheckboxes();
        if (selectAll && boxes.length > 0) {
            const allChecked = boxes.every(cb => cb.checked);
            selectAll.checked = allChecked;
            selectAll.indeterminate = ! allChecked && boxes.some(cb => cb.checked);
        }
    }

    function updateBulkActionButton() {
        const checked = rowCheckboxes().filter(cb => cb.checked);
        // A followed schedule nobody runs (no address of its own) is removed, not just unfollowed.
        const unfollowCount = checked.filter(cb => cb.dataset.hasEmail === 'true').length;
        const deleteCount = checked.length - unfollowCount;

        bulkSubdomainsInput.value = JSON.stringify(checked.map(cb => cb.value));
        bulkActionBtn.hidden = checked.length === 0;

        if (unfollowCount > 0 && deleteCount > 0) {
            bulkActionBtn.textContent = `${unfollowLabel} (${unfollowCount}) | ${deleteLabel} (${deleteCount})`;
        } else if (deleteCount > 0) {
            bulkActionBtn.textContent = `${deleteLabel} (${deleteCount})`;
        } else {
            bulkActionBtn.textContent = `${unfollowLabel} (${unfollowCount})`;
        }
    }

    // The list is replaced whole by the filter box and by sorting, so everything in it is heard
    // on the wrapper that stays: a listener on a row would be gone with the row.
    followingTable.addEventListener('change', function(e) {
        if (e.target.id === 'select-all') {
            rowCheckboxes().forEach(cb => cb.checked = e.target.checked);
        } else if (! e.target.classList.contains('row-checkbox')) {
            return;
        }
        updateSelectAllState();
        updateBulkActionButton();
    });

    followingTable.addEventListener('click', function(e) {
        const header = e.target.closest('[data-sort]');
        if (header) {
            const sortBy = header.getAttribute('data-sort');
            currentSortDir = currentSortBy === sortBy && currentSortDir === 'asc' ? 'desc' : 'asc';
            currentSortBy = sortBy;
            updateResults();
            return;
        }

        // Anywhere on a row ticks it, except on the things in it that do something themselves.
        const row = e.target.closest('tbody tr');
        if (! row || e.target.closest('a, button, input, .pop-up-menu')) {
            return;
        }
        const cb = row.querySelector('.row-checkbox');
        if (cb) {
            cb.checked = ! cb.checked;
            updateSelectAllState();
            updateBulkActionButton();
        }
    });

    bulkActionForm.addEventListener('submit', function(e) {
        if (! confirm(confirmMessage)) {
            e.preventDefault();
        }
    });

    function toast(text) {
        if (typeof Toastify !== 'undefined') {
            Toastify({ text: text, duration: 2000, position: 'center', style: { background: '#4BB543' } }).showToast();
        }
    }

    function postMemberSync(subdomain, calendarId) {
        return fetch(memberSyncUrl + '/' + encodeURIComponent(subdomain), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ google_calendar_id: calendarId }),
        })
        .then(response => response.json())
        .then(data => {
            alert(data.message || data.error);
            if (! data.error) {
                location.reload();
            }
        });
    }

    function syncToCalendar(subdomain) {
        fetch(calendarsUrl)
            .then(response => response.json())
            .then(data => {
                const calendars = (data.calendars && Array.isArray(data.calendars)) ? data.calendars : [];
                if (! calendars.length) {
                    alert(notConnected);
                    return;
                }

                const options = calendars.map((cal, i) => (i + 1) + '. ' + cal.summary).join('\n');
                const choice = prompt(selectCalendar + ':\n\n' + options);
                if (! choice) {
                    return;
                }

                const index = parseInt(choice, 10) - 1;
                if (isNaN(index) || index < 0 || index >= calendars.length) {
                    return;
                }

                return postMemberSync(subdomain, calendars[index].id);
            })
            .catch(() => alert(syncError));
    }

    // A row's menu. Heard in the capture phase: the layout stops a click inside one of its menus
    // from travelling any further up, and the menu closes itself on the same click.
    document.addEventListener('click', function(e) {
        if (! e.target.closest) {
            return;
        }

        const copy = e.target.closest('[data-copy-feed]');
        if (copy) {
            // The menu closes under the pointer, so the old "Copied" written into the button was
            // never seen. Said where it can be.
            navigator.clipboard.writeText(copy.getAttribute('data-copy-feed'))
                .then(() => toast(copiedLabel))
                .catch(() => {});
            return;
        }

        const sync = e.target.closest('[data-calendar-sync]');
        if (sync) {
            syncToCalendar(sync.getAttribute('data-calendar-sync'));
            return;
        }

        const unsync = e.target.closest('[data-calendar-unsync]');
        if (unsync && confirm(confirmMessage)) {
            postMemberSync(unsync.getAttribute('data-calendar-unsync'), '').catch(() => alert(syncError));
        }
    }, true);
})();
</script>

</x-app-admin-layout>
