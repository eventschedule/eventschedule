<x-app-admin-layout>

    <div class="mt-8 flow-root">
        <div class="flex justify-between">
            <div>
                <x-text-input type="text" name="filter" id="filter" placeholder="{{ __('messages.filter') }}" 
                    value="{{ request()->filter }}" />
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

<script>
let timeoutId;

document.getElementById('filter').addEventListener('input', function(e) {
    clearTimeout(timeoutId);
    
    timeoutId = setTimeout(() => {
        fetch(`${window.location.pathname}?filter=${encodeURIComponent(e.target.value)}`, {
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
    }, 500);
});
</script>