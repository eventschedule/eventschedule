<x-app-admin-layout>
    @php
        $exportQuery = request()->except('page');
    @endphp

    <div class="mt-8 flow-root">
        <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
            <div class="flex-1">
                <div class="relative">
                    <x-text-input type="text" name="filter" id="filter" placeholder="{{ __('messages.filter') }}"
                        value="{{ request()->filter }}" autocomplete="off"/>
                    <button type="button" id="clear-filter" class="absolute right-2 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600" style="display: none;">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                        </svg>
                    </button>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full sm:w-auto">
                <div class="flex flex-wrap gap-2">
                    <a href="{{ route('sales.export', array_merge($exportQuery, ['format' => 'csv'])) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5v2.25A1.25 1.25 0 005.25 20h13.5A1.25 1.25 0 0020 18.75V16.5M16 12l-4 4m0 0l-4-4m4 4V3" />
                        </svg>
                        {{ __('messages.export_csv') }}
                    </a>
                    <a href="{{ route('sales.export', array_merge($exportQuery, ['format' => 'xlsx'])) }}"
                        class="inline-flex items-center justify-center gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                        <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5v2.25A1.25 1.25 0 005.25 20h13.5A1.25 1.25 0 0020 18.75V16.5M16 12l-4 4m0 0l-4-4m4 4V3" />
                        </svg>
                        {{ __('messages.export_excel') }}
                    </a>
                </div>

                <a href="{{ route('ticket.scan') }}" class="w-full sm:w-auto">
                    <button type="button"
                        class="w-full sm:w-auto inline-flex items-center justify-center rounded-md shadow-sm bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M4,4H10V10H4V4M20,4V10H14V4H20M14,15H16V13H14V11H16V13H18V11H20V13H18V15H20V18H18V20H16V18H13V20H11V16H14V15M16,15V18H18V15H16M4,20V14H10V20H4M6,6V8H8V6H6M16,6V8H18V6H16M6,16V18H8V16H6M4,11H6V13H4V11M9,11H13V15H11V13H9V11M11,6H13V10H11V6M2,2V6H0V2A2,2 0 0,1 2,0H6V2H2M22,0A2,2 0 0,1 24,2V6H22V2H18V0H22M2,18V22H6V24H2A2,2 0 0,1 0,22V18H2M22,22V18H24V22A2,2 0 0,1 22,24H18V22H22Z" />
                        </svg>
                        <span class="ml-1">{{ __('messages.scan_ticket') }}</span>
                    </button>
                </a>
            </div>
        </div>
    </div>

    <div id="sales-table">
        @include('ticket.sales_table')
    </div>

</x-app-admin-layout>

<script {!! nonce_attr() !!}>
let timeoutId;
let columnFilterTimeoutId;
const filterInput = document.getElementById('filter');
const clearButton = document.getElementById('clear-filter');
let globalFilterValue = filterInput ? filterInput.value : '';
const selectedSales = new Set();
const columnFilterState = {};
let currentPage = Number(new URLSearchParams(window.location.search).get('page') || 1);

const filterKeyToParam = {
    customer: 'filter_customer',
    event: 'filter_event',
    total_min: 'filter_total_min',
    total_max: 'filter_total_max',
    transaction: 'filter_transaction',
    status: 'filter_status',
    usage: 'filter_usage',
};

initializeGlobalFilter();
initializeSalesTableInteractions();

function initializeGlobalFilter() {
    if (!filterInput) {
        return;
    }

    updateGlobalFilterVisibility();

    filterInput.addEventListener('input', (event) => {
        clearTimeout(timeoutId);
        globalFilterValue = event.target.value;
        updateGlobalFilterVisibility();
        timeoutId = setTimeout(() => {
            updateResults({ page: 1 });
        }, 500);
    });

    if (clearButton) {
        clearButton.addEventListener('click', () => {
            filterInput.value = '';
            globalFilterValue = '';
            updateGlobalFilterVisibility();
            updateResults({ page: 1 });
        });
    }
}

function updateGlobalFilterVisibility() {
    if (!clearButton || !filterInput) {
        return;
    }

    clearButton.style.display = filterInput.value ? 'block' : 'none';
}

function initializeSalesTableInteractions() {
    const salesTable = document.getElementById('sales-table');
    if (!salesTable) {
        return;
    }

    const root = salesTable.querySelector('[data-sales-table-root]');
    if (!root) {
        return;
    }

    currentPage = parseInt(root.dataset.currentPage || '1', 10) || 1;

    const columnFilters = root.querySelectorAll('[data-column-filter]');
    columnFilters.forEach((input) => {
        const key = input.dataset.filterKey;
        if (!key || !filterKeyToParam[key]) {
            return;
        }

        columnFilterState[key] = input.value ?? '';
        syncColumnInputs(root, key, columnFilterState[key], input);

        if (input.tagName === 'SELECT') {
            input.addEventListener('change', (event) => {
                columnFilterState[key] = event.target.value;
                syncColumnInputs(root, key, event.target.value, event.target);
                updateResults({ page: 1 });
            });
        } else {
            input.addEventListener('input', (event) => {
                columnFilterState[key] = event.target.value;
                syncColumnInputs(root, key, event.target.value, event.target);
                scheduleColumnUpdate();
            });
        }
    });

    const clearFilterButtons = root.querySelectorAll('[data-clear-filters]');
    clearFilterButtons.forEach((button) => {
        button.addEventListener('click', () => {
            resetColumnFilters(root);
            updateResults({ page: 1 });
        });
    });

    const saleCheckboxes = root.querySelectorAll('.sale-checkbox');
    saleCheckboxes.forEach((checkbox) => {
        checkbox.checked = selectedSales.has(checkbox.value);
        checkbox.addEventListener('change', (event) => {
            if (event.target.checked) {
                selectedSales.add(event.target.value);
            } else {
                selectedSales.delete(event.target.value);
            }
            updateSelectAllState(root);
            updateSelectedCount(root);
        });
    });

    const selectAll = root.querySelector('#select-all-sales');
    if (selectAll) {
        updateSelectAllState(root);
        selectAll.addEventListener('change', (event) => {
            toggleSelectAll(root, event.target.checked);
        });
    }

    updateSelectedCount(root);

    const bulkActionButton = root.querySelector('#apply-bulk-action');
    if (bulkActionButton) {
        bulkActionButton.addEventListener('click', () => {
            const actionSelect = root.querySelector('#bulk-action-select');
            if (!actionSelect) {
                return;
            }

            const action = actionSelect.value;
            if (!action) {
                alert('{{ __('messages.no_action_selected') }}');
                return;
            }

            if (selectedSales.size === 0) {
                alert('{{ __('messages.no_sales_selected') }}');
                return;
            }

            executeAction(action, Array.from(selectedSales));
        });
    }

    const paginationLinks = root.querySelectorAll('.pagination a');
    paginationLinks.forEach((link) => {
        link.addEventListener('click', (event) => {
            event.preventDefault();
            const url = new URL(event.currentTarget.href);
            const page = parseInt(url.searchParams.get('page') || '1', 10);
            updateResults({ page });
        });
    });
}

function scheduleColumnUpdate() {
    clearTimeout(columnFilterTimeoutId);
    columnFilterTimeoutId = setTimeout(() => {
        updateResults({ page: 1 });
    }, 400);
}

function resetColumnFilters(root) {
    Object.keys(filterKeyToParam).forEach((key) => {
        columnFilterState[key] = '';
    });

    root.querySelectorAll('[data-column-filter]').forEach((input) => {
        input.value = '';
    });

    selectedSales.clear();
    updateSelectedCount(root);
    updateSelectAllState(root);
}

function syncColumnInputs(root, key, value, source) {
    root.querySelectorAll(`[data-column-filter][data-filter-key="${key}"]`).forEach((input) => {
        if (input !== source) {
            input.value = value;
        }
    });
}

function updateSelectedCount(root) {
    const counter = root.querySelector('[data-selected-count]');
    if (!counter) {
        return;
    }

    const template = counter.getAttribute('data-selected-count-label') || '{{ __('messages.selected_sales', ['count' => ':count']) }}';
    counter.textContent = template.replace(':count', selectedSales.size);
}

function updateSelectAllState(root) {
    const selectAll = root.querySelector('#select-all-sales');
    if (!selectAll) {
        return;
    }

    const checkboxes = root.querySelectorAll('.sale-checkbox');
    if (checkboxes.length === 0) {
        selectAll.checked = false;
        selectAll.indeterminate = false;
        return;
    }

    let checkedCount = 0;
    checkboxes.forEach((checkbox) => {
        if (selectedSales.has(checkbox.value)) {
            checkbox.checked = true;
            checkedCount += 1;
        } else {
            checkbox.checked = false;
        }
    });

    selectAll.checked = checkedCount === checkboxes.length;
    selectAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
}

function toggleSelectAll(root, shouldSelect) {
    const checkboxes = root.querySelectorAll('.sale-checkbox');
    checkboxes.forEach((checkbox) => {
        checkbox.checked = shouldSelect;
        if (shouldSelect) {
            selectedSales.add(checkbox.value);
        } else {
            selectedSales.delete(checkbox.value);
        }
    });

    updateSelectAllState(root);
    updateSelectedCount(root);
}

function updateResults(options = {}) {
    const salesTable = document.getElementById('sales-table');
    if (!salesTable) {
        return;
    }

    const root = salesTable.querySelector('[data-sales-table-root]');
    if (root) {
        selectedSales.clear();
        updateSelectedCount(root);
        updateSelectAllState(root);
    }

    const params = new URLSearchParams();
    if (globalFilterValue) {
        params.set('filter', globalFilterValue);
    }

    Object.entries(columnFilterState).forEach(([key, value]) => {
        if (value !== undefined && value !== null && value !== '') {
            const paramName = filterKeyToParam[key];
            if (paramName) {
                params.set(paramName, value);
            }
        }
    });

    const page = options.page ? Number(options.page) : 1;
    currentPage = page;
    if (page && page > 1) {
        params.set('page', page);
    }

    const queryString = params.toString();
    const url = queryString ? `${window.location.pathname}?${queryString}` : window.location.pathname;

    fetch(url, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
        .then((response) => response.text())
        .then((html) => {
            salesTable.innerHTML = html;
            initializeSalesTableInteractions();
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('{{ __('messages.an_error_occurred') }}');
        });
}

function executeAction(action, saleIds) {
    if (!Array.isArray(saleIds) || saleIds.length === 0) {
        return;
    }

    if (!confirm('{{ __('messages.are_you_sure') }}')) {
        return;
    }

    fetch(`{{ route('sales.actions') }}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            action: action,
            sale_ids: saleIds,
        })
    })
        .then((response) => response.json())
        .then((data) => {
            if (data.error) {
                alert(data.error);
                return;
            }

            selectedSales.clear();
            updateResults({ page: currentPage });

            const messages = {
                mark_paid: '{{ __('messages.mark_paid_success') }}',
                refund: '{{ __('messages.refund_success') }}',
                cancel: '{{ __('messages.cancel_success') }}',
                delete: '{{ __('messages.delete_success') }}',
            };

            let message = messages[action] || '';
            if (saleIds.length > 1 && message) {
                message += ' {{ __('messages.bulk_action_success') }}';
            }

            if (message) {
                Toastify({
                    text: message,
                    duration: 3000,
                    position: 'center',
                    stopOnFocus: true,
                    style: {
                        background: '#4BB543',
                    }
                }).showToast();
            }
        })
        .catch((error) => {
            console.error('Error:', error);
            alert('{{ __('messages.an_error_occurred') }}');
        });
}

function handleAction(saleId, action) {
    executeAction(action, [saleId]);
}
</script>