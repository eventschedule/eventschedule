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
        }
    });
}
</script>
