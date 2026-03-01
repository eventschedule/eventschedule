<x-app-admin-layout>

    <div class="mb-6 border-b border-gray-200 dark:border-gray-700">
        <nav class="-mb-px flex gap-6">
            <button type="button" id="tab-sales"
                class="sales-tab whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium border-[#4E81FA] text-[#4E81FA]">
                {{ __('messages.sales') }}
            </button>
            <button type="button" id="tab-waitlist"
                class="sales-tab whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300">
                {{ __('messages.waitlist') }}{{ $waitlistCount > 0 ? ' (' . $waitlistCount . ')' : '' }}
            </button>
        </nav>
    </div>

    <div id="sales-panel">
        <div class="flow-root">
            <div class="flex flex-col sm:flex-row sm:justify-between gap-4">
                <div class="flex-1">
                    <div class="relative w-fit">
                        <x-text-input type="text" name="filter" id="filter" placeholder="{{ __('messages.filter') }}"
                            value="{{ request()->filter }}" autocomplete="off"/>
                        <button type="button" id="clear-filter" class="absolute end-2 top-1/2 -translate-y-1/2 text-gray-400 dark:text-gray-500 hover:text-gray-600 dark:hover:text-gray-400" style="display: none;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex flex-wrap gap-2 w-full sm:w-auto">
                    <x-brand-link href="#" id="export-sales" class="w-full sm:w-auto">
                        <svg class="-ms-0.5 me-2 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                            <path d="M5,20H19V18H5M19,9H15V3H9V9H5L12,16L19,9Z" />
                        </svg>
                        {{ __('messages.export') }}
                    </x-brand-link>
                    <x-brand-link href="{{ route('checkin.index') }}" class="w-full sm:w-auto">
                        <svg class="-ms-0.5 me-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                        </svg>
                        {{ __('messages.checkin_dashboard') }}
                    </x-brand-link>
                    <x-brand-link href="{{ route('ticket.scan') }}" class="w-full sm:w-auto">
                        <svg class="-ms-0.5 me-2 h-6 w-6" viewBox="0 0 24 24" fill="currentColor">
                            <path
                                d="M4,4H10V10H4V4M20,4V10H14V4H20M14,15H16V13H14V11H16V13H18V11H20V13H18V15H20V18H18V20H16V18H13V20H11V16H14V15M16,15V18H18V15H16M4,20V14H10V20H4M6,6V8H8V6H6M16,6V8H18V6H16M6,16V18H8V16H6M4,11H6V13H4V11M9,11H13V15H11V13H9V11M11,6H13V10H11V6M2,2V6H0V2A2,2 0 0,1 2,0H6V2H2M22,0A2,2 0 0,1 24,2V6H22V2H18V0H22M2,18V22H6V24H2A2,2 0 0,1 0,22V18H2M22,22V18H24V22A2,2 0 0,1 22,24H18V22H22Z" />
                        </svg>
                        {{ __('messages.scan_ticket') }}
                    </x-brand-link>
                </div>
            </div>
        </div>

        <div id="sales-table">
            @include('ticket.sales_table')
        </div>
    </div>

    <div id="waitlist-panel" style="display: none;">
        <div id="waitlist-table">
            @include('ticket.waitlist_table', ['entries' => $waitlistEntries ?? collect()])
        </div>
    </div>

</x-app-admin-layout>

<script {!! nonce_attr() !!}>
// Tab switching
const activeClass = 'border-[#4E81FA] text-[#4E81FA]';
const inactiveClass = 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 dark:hover:border-gray-600 hover:text-gray-700 dark:hover:text-gray-300';

document.getElementById('tab-sales').addEventListener('click', function() {
    document.getElementById('sales-panel').style.display = '';
    document.getElementById('waitlist-panel').style.display = 'none';
    this.className = 'sales-tab whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium ' + activeClass;
    document.getElementById('tab-waitlist').className = 'sales-tab whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium ' + inactiveClass;
});

document.getElementById('tab-waitlist').addEventListener('click', function() {
    document.getElementById('sales-panel').style.display = 'none';
    document.getElementById('waitlist-panel').style.display = '';
    this.className = 'sales-tab whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium ' + activeClass;
    document.getElementById('tab-sales').className = 'sales-tab whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium ' + inactiveClass;

    // Load waitlist data on first click
    if (!document.getElementById('waitlist-panel').dataset.loaded) {
        loadWaitlist();
        document.getElementById('waitlist-panel').dataset.loaded = '1';
    }
});

function loadWaitlist() {
    fetch('{{ route("waitlist.index") }}', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.text())
    .then(html => {
        document.getElementById('waitlist-table').innerHTML = html;
    });
}

function handleWaitlistRemove(entryId) {
    if (!confirm(@json(__("messages.are_you_sure")))) return;

    fetch(`{{ url('/waitlist/remove') }}/${entryId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            loadWaitlist();
            Toastify({
                text: @json(__("messages.waitlist_removed")),
                duration: 3000,
                position: 'center',
                stopOnFocus: true,
                style: { background: '#4BB543' }
            }).showToast();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(@json(__("messages.an_error_occurred")));
    });
}

let timeoutId;
const filterInput = document.getElementById('filter');
const clearButton = document.getElementById('clear-filter');

// Export sales CSV
document.getElementById('export-sales').addEventListener('click', function(e) {
    e.preventDefault();
    const filter = document.getElementById('filter').value;
    window.location.href = '{{ route("sales.export") }}' + (filter ? '?filter=' + encodeURIComponent(filter) : '');
});

// Show/hide clear button based on input content
filterInput.addEventListener('input', function(e) {
    clearTimeout(timeoutId);
    clearButton.style.display = e.target.value ? 'block' : 'none';
    
    timeoutId = setTimeout(() => {
        updateResults(e.target.value);
    }, 500);
});

// Clear input and trigger search immediately
clearButton.addEventListener('click', function() {
    filterInput.value = '';
    clearButton.style.display = 'none';
    updateResults(''); // Call directly without timeout
});

function updateResults(value) {
    fetch(`${window.location.pathname}?filter=${encodeURIComponent(value)}`, {
        headers: {
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.text())
    .then(html => {
        const salesTable = document.getElementById('sales-table');
        if (salesTable) {
            salesTable.innerHTML = html;
        }
    });
}

function handleAction(saleId, action) {
    if (!confirm(@json(__("messages.are_you_sure")))) {
        return;
    }

    fetch(`{{ url('/sales/action') }}/${saleId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ action: action })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(data.error);
        } else {
            // Refresh the table
            updateResults(document.getElementById('filter').value);

            var message = '';
            if (action === 'mark_paid') {
                message = @json(__("messages.mark_paid_success"));
            } else if (action === 'refund') {
                message = @json(__("messages.refund_success"));
            } else if (action === 'cancel') {
                message = @json(__("messages.cancel_success"));
            } else if (action === 'delete') {
                message = @json(__("messages.delete_success"));
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
        }
    })
    .catch(error => {
        console.error('Error:', error);
        alert(@json(__("messages.an_error_occurred")));
    });
}

function toggleCustomFields(button) {
    var row = button.closest('tr');
    var detailRow = row.nextElementSibling;
    if (detailRow && detailRow.classList.contains('custom-fields-row')) {
        detailRow.classList.toggle('hidden');
        var svg = button.querySelector('svg');
        if (svg) {
            svg.classList.toggle('rotate-90');
        }
    }
}

document.addEventListener('click', function(e) {
    var button = e.target.closest('[data-toggle-custom-fields]');
    if (button) {
        toggleCustomFields(button);
    }
});

$(document).on('click', '[data-popup-toggle]', function(e) {
    var popupId = $(this).attr('data-popup-toggle');
    onPopUpClick(popupId, e);

    var resendId = $(this).attr('data-resend-email');
    if (resendId) {
        resendEmail(resendId);
    }

    var saleAction = $(this).attr('data-sale-action');
    if (saleAction) {
        handleAction($(this).attr('data-sale-id'), saleAction);
    }
});

function resendEmail(saleId) {
    fetch(`{{ url('/sales/resend-email') }}/${saleId}`, {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
            'X-Requested-With': 'XMLHttpRequest'
        }
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            Toastify({
                text: data.error,
                duration: 3000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#FF0000',
                }
            }).showToast();
        } else {
            Toastify({
                text: data.message || @json(__("messages.email_sent_successfully")),
                duration: 3000,
                position: 'center',
                stopOnFocus: true,
                style: {
                    background: '#4BB543',
                }
            }).showToast();
        }
    })
    .catch(error => {
        console.error('Error:', error);
        Toastify({
            text: @json(__("messages.failed_to_send_email")),
            duration: 3000,
            position: 'center',
            stopOnFocus: true,
            style: {
                background: '#FF0000',
            }
        }).showToast();
    });
}

</script>