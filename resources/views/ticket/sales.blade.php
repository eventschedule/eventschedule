<x-app-admin-layout>

    <div class="mt-8 flow-root">
        <div class="flex justify-between">
            <div>
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


            <a href="{{ route('ticket.scan') }}">
                <button type="button"
                    class="inline-flex items-center rounded-md shadow-sm bg-[#4E81FA] px-3 py-2 text-sm font-semibold text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                    <svg class="-ml-0.5 mr-1.5 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                        <path
                            d="M4,4H10V10H4V4M20,4V10H14V4H20M14,15H16V13H14V11H16V13H18V11H20V13H18V15H20V18H18V20H16V18H13V20H11V16H14V15M16,15V18H18V15H16M4,20V14H10V20H4M6,6V8H8V6H6M16,6V8H18V6H16M6,16V18H8V16H6M4,11H6V13H4V11M9,11H13V15H11V13H9V11M11,6H13V10H11V6M2,2V6H0V2A2,2 0 0,1 2,0H6V2H2M22,0A2,2 0 0,1 24,2V6H22V2H18V0H22M2,18V22H6V24H2A2,2 0 0,1 0,22V18H2M22,22V18H24V22A2,2 0 0,1 22,24H18V22H22Z" />
                    </svg>
                    <span class="ml-1">{{ __('messages.scan_ticket') }}</span>
                </button>
            </a>
        </div>
    </div>

    <div id="sales-table">
        @include('ticket.sales_table')
    </div>

</x-app-admin-layout>

<script {!! nonce_attr() !!}>
let timeoutId;
const filterInput = document.getElementById('filter');
const clearButton = document.getElementById('clear-filter');

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
    if (!confirm('{{ __("messages.are_you_sure") }}')) {
        return;
    }

    fetch(`/sales/action/${saleId}`, {
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
                message = '{{ __("messages.mark_paid_success") }}';
            } else if (action === 'refund') {
                message = '{{ __("messages.refund_success") }}';
            } else if (action === 'cancel') {
                message = '{{ __("messages.cancel_success") }}';
            } else if (action === 'delete') {
                message = '{{ __("messages.delete_success") }}';
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
        alert('{{ __("messages.an_error_occurred") }}');
    });
}

</script>