<x-app-admin-layout>

    <div class="flow-root">
        <div class="flex flex-col sm:flex-row sm:justify-between gap-4 mb-6">
            <div class="flex-1">
                <div class="relative">
                    <x-text-input type="text" name="filter" id="filter" placeholder="{{ __('messages.filter') }}"
                        value="{{ request()->filter }}" autocomplete="off"/>
                    <button type="button" id="clear-filter" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400" style="display: none;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>
            <div>
                <form id="bulk-action-form" method="POST" action="{{ route('following.bulk-unfollow') }}">
                    @csrf
                    <input type="hidden" name="subdomains" id="bulk-subdomains" value="">
                    <button type="submit" id="bulk-action-btn" style="display: none;"
                        class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                        {{ __('messages.unfollow') }}
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div id="following-table">
        @include('role.following_table')
    </div>

</x-app-admin-layout>

<script {!! nonce_attr() !!}>
let timeoutId;
let currentSortBy = '{{ $sortBy }}';
let currentSortDir = '{{ $sortDir }}';
const filterInput = document.getElementById('filter');
const clearButton = document.getElementById('clear-filter');
const bulkActionBtn = document.getElementById('bulk-action-btn');
const bulkSubdomainsInput = document.getElementById('bulk-subdomains');
const bulkActionForm = document.getElementById('bulk-action-form');

const unfollowLabel = '{{ __('messages.unfollow') }}';
const deleteLabel = '{{ __('messages.delete') }}';
const confirmMessage = '{{ __('messages.are_you_sure') }}';

// Show clear button if filter has initial value
if (filterInput.value) {
    clearButton.style.display = 'block';
}

// Show/hide clear button based on input content
filterInput.addEventListener('input', function(e) {
    clearTimeout(timeoutId);
    clearButton.style.display = e.target.value ? 'block' : 'none';

    timeoutId = setTimeout(() => {
        updateResults();
    }, 500);
});

// Clear input and trigger search immediately
clearButton.addEventListener('click', function() {
    filterInput.value = '';
    clearButton.style.display = 'none';
    updateResults();
});

// Handle sort header clicks
document.addEventListener('click', function(e) {
    const header = e.target.closest('[data-sort]');
    if (header) {
        const sortBy = header.getAttribute('data-sort');
        if (currentSortBy === sortBy) {
            currentSortDir = currentSortDir === 'asc' ? 'desc' : 'asc';
        } else {
            currentSortBy = sortBy;
            currentSortDir = 'asc';
        }
        updateResults();
    }
});

function updateResults() {
    const filter = filterInput.value;
    const params = new URLSearchParams();
    if (filter) {
        params.append('filter', filter);
    }
    params.append('sort_by', currentSortBy);
    params.append('sort_dir', currentSortDir);

    fetch(`${window.location.pathname}?${params.toString()}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const followingTable = document.getElementById('following-table');
        if (followingTable) {
            followingTable.innerHTML = html;
            setupCheckboxListeners();
            updateBulkActionButton();
        }
    });
}

function setupCheckboxListeners() {
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');

    if (selectAllCheckbox) {
        selectAllCheckbox.addEventListener('change', function() {
            rowCheckboxes.forEach(cb => cb.checked = this.checked);
            updateBulkActionButton();
        });
    }

    rowCheckboxes.forEach(cb => {
        cb.addEventListener('change', function() {
            updateSelectAllState();
            updateBulkActionButton();
        });

        // Make entire row clickable to toggle checkbox
        const row = cb.closest('tr');
        if (row) {
            row.style.cursor = 'pointer';
            row.addEventListener('click', function(e) {
                // Don't toggle if clicking on links, buttons, or the checkbox itself
                if (e.target.closest('a') || e.target.closest('button') || e.target.type === 'checkbox') {
                    return;
                }
                cb.checked = !cb.checked;
                updateSelectAllState();
                updateBulkActionButton();
            });
        }
    });
}

function updateSelectAllState() {
    const selectAllCheckbox = document.getElementById('select-all');
    const rowCheckboxes = document.querySelectorAll('.row-checkbox');
    if (selectAllCheckbox && rowCheckboxes.length > 0) {
        const allChecked = Array.from(rowCheckboxes).every(cb => cb.checked);
        const someChecked = Array.from(rowCheckboxes).some(cb => cb.checked);
        selectAllCheckbox.checked = allChecked;
        selectAllCheckbox.indeterminate = someChecked && !allChecked;
    }
}

function updateBulkActionButton() {
    const checkedBoxes = document.querySelectorAll('.row-checkbox:checked');
    const selectedSubdomains = Array.from(checkedBoxes).map(cb => cb.value);

    let unfollowCount = 0;
    let deleteCount = 0;

    checkedBoxes.forEach(cb => {
        if (cb.dataset.hasEmail === 'true') {
            unfollowCount++;
        } else {
            deleteCount++;
        }
    });

    bulkSubdomainsInput.value = JSON.stringify(selectedSubdomains);

    if (selectedSubdomains.length === 0) {
        bulkActionBtn.style.display = 'none';
    } else {
        bulkActionBtn.style.display = 'inline-flex';
        let label = '';
        if (unfollowCount > 0 && deleteCount > 0) {
            label = `${unfollowLabel} (${unfollowCount}) | ${deleteLabel} (${deleteCount})`;
        } else if (unfollowCount > 0) {
            label = `${unfollowLabel} (${unfollowCount})`;
        } else {
            label = `${deleteLabel} (${deleteCount})`;
        }
        bulkActionBtn.textContent = label;
    }
}

// Confirmation dialog on form submit
bulkActionForm.addEventListener('submit', function(e) {
    if (!confirm(confirmMessage)) {
        e.preventDefault();
    }
});

// Initialize checkbox listeners on page load
document.addEventListener('DOMContentLoaded', function() {
    setupCheckboxListeners();
});
</script>
