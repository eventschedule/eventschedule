<div class="mt-8 flow-root" data-sales-table-root data-current-page="{{ $sales->currentPage() }}" data-total-pages="{{ $sales->lastPage() }}">
    @if($sales->count() > 0)
    @php
        $filterCustomer = request('filter_customer', '');
        $filterEvent = request('filter_event', '');
        $filterTotalMin = request('filter_total_min', '');
        $filterTotalMax = request('filter_total_max', '');
        $filterTransaction = request('filter_transaction', '');
        $filterStatus = request('filter_status', '');
        $filterUsage = request('filter_usage', '');
    @endphp

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-4" data-bulk-action-bar>
        <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 p-4">
            <div class="flex items-center gap-3 flex-wrap">
                <label class="inline-flex items-center gap-2 text-sm text-gray-700">
                    <input type="checkbox" id="select-all-sales" class="h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                    <span>{{ __('messages.select_all') }}</span>
                </label>
                <span class="text-sm text-gray-500" data-selected-count data-selected-count-label="{{ __('messages.selected_sales', ['count' => ':count']) }}">{{ __('messages.selected_sales', ['count' => 0]) }}</span>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-2 w-full md:w-auto">
                <select id="bulk-action-select" class="w-full sm:w-56 rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500">
                    <option value="">{{ __('messages.select_action') }}</option>
                    <option value="mark_paid">{{ __('messages.mark_paid') }}</option>
                    <option value="mark_unpaid">{{ __('messages.mark_unpaid') }}</option>
                    <option value="mark_used">{{ __('messages.mark_tickets_as_used') }}</option>
                    <option value="mark_unused">{{ __('messages.mark_tickets_as_unused') }}</option>
                    <option value="cancel">{{ __('messages.cancel') }}</option>
                    <option value="refund">{{ __('messages.refund') }}</option>
                    <option value="delete">{{ __('messages.delete') }}</option>
                </select>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <button id="apply-bulk-action" type="button" class="inline-flex justify-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600 w-full sm:w-auto">
                        {{ __('messages.apply') }}
                    </button>
                    <button type="button" class="text-sm text-indigo-600 hover:text-indigo-500" data-clear-filters>
                        {{ __('messages.clear_filters') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-lg shadow-sm mb-4 md:hidden" data-mobile-filters>
        <div class="p-4 space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('messages.customer') }}</label>
                <input type="text" name="filter_customer" value="{{ $filterCustomer }}" placeholder="{{ __('messages.customer') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="customer">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('messages.event') }}</label>
                <input type="text" name="filter_event" value="{{ $filterEvent }}" placeholder="{{ __('messages.event') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="event">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('messages.total') }}</label>
                <div class="mt-1 grid grid-cols-2 gap-2">
                    <input type="number" step="0.01" name="filter_total_min" value="{{ $filterTotalMin }}" placeholder="{{ __('messages.min') ?? 'Min' }}" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="total_min">
                    <input type="number" step="0.01" name="filter_total_max" value="{{ $filterTotalMax }}" placeholder="{{ __('messages.max') ?? 'Max' }}" class="rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="total_max">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">{{ __('messages.transaction_reference') }}</label>
                <input type="text" name="filter_transaction" value="{{ $filterTransaction }}" placeholder="{{ __('messages.transaction_reference') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="transaction">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.status') }}</label>
                    <select name="filter_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="status">
                        <option value="">{{ __('messages.all_statuses') }}</option>
                        <option value="paid" @selected($filterStatus === 'paid')>{{ __('messages.paid') }}</option>
                        <option value="unpaid" @selected($filterStatus === 'unpaid')>{{ __('messages.unpaid') }}</option>
                        <option value="cancelled" @selected($filterStatus === 'cancelled')>{{ __('messages.cancelled') }}</option>
                        <option value="refunded" @selected($filterStatus === 'refunded')>{{ __('messages.refunded') }}</option>
                        <option value="expired" @selected($filterStatus === 'expired')>{{ __('messages.expired') }}</option>
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">{{ __('messages.ticket_usage') }}</label>
                    <select name="filter_usage" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="usage">
                        <option value="">{{ __('messages.all_usage_states') }}</option>
                        <option value="unused" @selected($filterUsage === 'unused')>{{ __('messages.ticket_status_unused') }}</option>
                        <option value="used" @selected($filterUsage === 'used')>{{ __('messages.ticket_status_used') }}</option>
                    </select>
                </div>
            </div>
            <button type="button" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500" data-clear-filters>{{ __('messages.clear_filters') }}</button>
        </div>
    </div>

    <!-- Desktop Table View -->
    <div class="hidden md:block -mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <div class="overflow-x-auto" style="overflow-x: auto; scrollbar-width: thin;">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6 w-12">
                                    <span class="sr-only">{{ __('messages.select') }}</span>
                                </th>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('messages.customer') }}
                                </th>
                                <th scope="col"
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">
                                    {{ __('messages.event') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.tickets') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.total') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.transaction_reference') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.status') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">
                                    {{ __('messages.ticket_usage') }}
                                </th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">{{ __('messages.actions') }}</span>
                                </th>
                            </tr>
                            <tr class="bg-white">
                                <th class="py-2 pl-4 pr-3 sm:pl-6"></th>
                                <th class="py-2 pl-4 pr-3 sm:pl-6">
                                    <input type="text" name="filter_customer" value="{{ $filterCustomer }}" placeholder="{{ __('messages.customer') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="customer">
                                </th>
                                <th class="py-2 pl-4 pr-3 sm:pl-6">
                                    <input type="text" name="filter_event" value="{{ $filterEvent }}" placeholder="{{ __('messages.event') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="event">
                                </th>
                                <th class="px-3 py-2"></th>
                                <th class="px-3 py-2">
                                    <div class="flex items-center gap-2">
                                        <input type="number" step="0.01" name="filter_total_min" value="{{ $filterTotalMin }}" placeholder="{{ __('messages.min') ?? 'Min' }}" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="total_min">
                                        <input type="number" step="0.01" name="filter_total_max" value="{{ $filterTotalMax }}" placeholder="{{ __('messages.max') ?? 'Max' }}" class="w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="total_max">
                                    </div>
                                </th>
                                <th class="px-3 py-2">
                                    <input type="text" name="filter_transaction" value="{{ $filterTransaction }}" placeholder="{{ __('messages.transaction_reference') }}" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="transaction">
                                </th>
                                <th class="px-3 py-2">
                                    <select name="filter_status" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="status">
                                        <option value="">{{ __('messages.all_statuses') }}</option>
                                        <option value="paid" @selected($filterStatus === 'paid')>{{ __('messages.paid') }}</option>
                                        <option value="unpaid" @selected($filterStatus === 'unpaid')>{{ __('messages.unpaid') }}</option>
                                        <option value="cancelled" @selected($filterStatus === 'cancelled')>{{ __('messages.cancelled') }}</option>
                                        <option value="refunded" @selected($filterStatus === 'refunded')>{{ __('messages.refunded') }}</option>
                                        <option value="expired" @selected($filterStatus === 'expired')>{{ __('messages.expired') }}</option>
                                    </select>
                                </th>
                                <th class="px-3 py-2">
                                    <select name="filter_usage" class="mt-1 block w-full rounded-md border-gray-300 text-sm focus:border-indigo-500 focus:ring-indigo-500" data-column-filter data-filter-key="usage">
                                        <option value="">{{ __('messages.all_usage_states') }}</option>
                                        <option value="unused" @selected($filterUsage === 'unused')>{{ __('messages.ticket_status_unused') }}</option>
                                        <option value="used" @selected($filterUsage === 'used')>{{ __('messages.ticket_status_used') }}</option>
                                    </select>
                                </th>
                                <th class="py-2 pl-3 pr-4 text-right text-sm sm:pr-6">
                                    <button type="button" class="text-sm font-semibold text-indigo-600 hover:text-indigo-500" data-clear-filters>{{ __('messages.clear_filters') }}</button>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 bg-white">
                            @foreach ($sales as $sale)
                            <tr class="bg-white hover:bg-gray-50 transition-colors duration-150" data-sale-id="{{ \App\Utils\UrlUtils::encodeId($sale->id) }}">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-900 sm:pl-6">
                                    <input type="checkbox" value="{{ \App\Utils\UrlUtils::encodeId($sale->id) }}" class="sale-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    <div class="flex flex-col">
                                        <span class="font-semibold">{{ $sale->name }}</span>
                                        <a href="mailto:{{ $sale->email }}" class="text-blue-600 hover:text-blue-800 hover:underline text-xs">{{ $sale->email }}</a>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-6">
                                    <a href="{{ $sale->getEventUrl() }}"
                                        target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">{{ $sale->event->name }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <div class="flex flex-col gap-1">
                                        @foreach ($sale->saleTickets as $saleTicket)
                                            @php
                                                $ticketUsageStatus = $saleTicket->usage_status;
                                                $ticketUsageClasses = $ticketUsageStatus === 'used'
                                                    ? 'bg-orange-100 text-orange-800'
                                                    : 'bg-green-100 text-green-800';
                                            @endphp
                                            <div class="flex items-center gap-2">
                                                <span class="text-gray-900">{{ $saleTicket->ticket->type ?: __('messages.ticket') }} x {{ $saleTicket->quantity }}</span>
                                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticketUsageClasses }}">
                                                    {{ __('messages.ticket_status_' . $ticketUsageStatus) }}
                                                </span>
                                            </div>
                                        @endforeach
                                    </div>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    <span class="font-semibold text-gray-900">{{ number_format($sale->payment_amount, 2, '.', ',') }}</span>
                                    <span class="text-gray-500">{{ $sale->event->ticket_currency_code }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @if ($sale->transaction_reference == __('messages.manual_payment'))
                                        <span class="text-gray-600">{{ __('messages.manual_payment') }}</span>
                                    @elseif ($sale->payment_method == 'invoiceninja')
                                        <a href="https://app.invoicing.co/#/invoices/{{ $sale->transaction_reference }}/edit" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $sale->transaction_reference }}
                                        </a>
                                    @elseif ($sale->payment_method == 'stripe')
                                        <a href="https://dashboard.stripe.com/payments/{{ $sale->transaction_reference }}" target="_blank" class="text-blue-600 hover:text-blue-800 hover:underline">
                                            {{ $sale->transaction_reference }}
                                        </a>
                                    @else
                                        <span class="font-mono text-sm">{{ $sale->transaction_reference }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @if($sale->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.paid') }}
                                        </span>
                                    @elseif($sale->status === 'unpaid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.unpaid') }}
                                        </span>
                                    @elseif($sale->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.cancelled') }}
                                        </span>
                                    @elseif($sale->status === 'refunded')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.refunded') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            {{ __('messages.' . $sale->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                    @php
                                        $usageStatus = $sale->usage_status;
                                        $usageClasses = $usageStatus === 'used'
                                            ? 'bg-orange-100 text-orange-800'
                                            : 'bg-green-100 text-green-800';
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $usageClasses }}">
                                        {{ __('messages.ticket_status_' . $usageStatus) }}
                                    </span>
                                </td>
                                <td class="relative whitespace-nowrap py-4 pl-3 pr-4 text-right text-sm font-medium sm:pr-6">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="relative" x-data="{ 
                                            open: false,
                                            positionDropdown() {
                                                if (!this.open) return;
                                                const button = this.$refs.button;
                                                const dropdown = this.$refs.dropdown;
                                                const rect = button.getBoundingClientRect();
                                                
                                                dropdown.style.position = 'fixed';
                                                dropdown.style.top = `${rect.bottom + 4}px`;
                                                dropdown.style.left = `${rect.left}px`;
                                                dropdown.style.zIndex = '1000';
                                            }
                                        }">
                                            <button x-on:click="open = !open; $nextTick(() => positionDropdown())"
                                                    x-ref="button"
                                                    type="button" 
                                                    class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors duration-150">
                                                {{ __('messages.select_action') }}
                                                <svg class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <div x-show="open" 
                                                 x-ref="dropdown"
                                                   x-on:click.away="open = false"
                                                 class="w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                                                 role="menu" 
                                                 x-cloak
                                                 aria-orientation="vertical">
                                                
                                                <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" 
                                                   target="_blank" 
                                                     x-on:click="open = false"
                                                   class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150"
                                                   role="menuitem">
                                                    {{ __('messages.view_ticket') }}
                                                </a>

                                                @if($sale->status === 'unpaid')
                                                    <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_paid')"
                                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                                            role="menuitem">
                                                        {{ __('messages.mark_paid') }}
                                                    </button>
                                                @elseif($sale->status === 'paid')
                                                    <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_unpaid')"
                                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                                            role="menuitem">
                                                        {{ __('messages.mark_unpaid') }}
                                                    </button>
                                                @endif

                                                @if($sale->status === 'paid' && $sale->payment_method != 'cash')
                                                    <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'refund')"
                                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                                            role="menuitem">
                                                        {{ __('messages.refund') }}
                                                    </button>
                                                @endif

                                                <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_used')"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                                        role="menuitem">
                                                    {{ __('messages.mark_tickets_as_used') }}
                                                </button>

                                                <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_unused')"
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                                        role="menuitem">
                                                    {{ __('messages.mark_tickets_as_unused') }}
                                                </button>

                                                @if(in_array($sale->status, ['unpaid', 'paid']))
                                                      <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'cancel')"
                                                            class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                                            role="menuitem">
                                                        {{ __('messages.cancel') }}
                                                    </button>
                                                @endif
                                                
                                                @if(! $sale->is_deleted)
                                                <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'delete')"
                                                        class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                                        role="menuitem">
                                                        {{ __('messages.delete') }}
                                                    </button>
                                                @endif
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile List View -->
    <div class="md:hidden space-y-4">
        @foreach ($sales as $sale)
        <div class="bg-white rounded-lg shadow-sm border border-gray-200 p-5 hover:shadow-md transition-shadow duration-200" data-sale-id="{{ \App\Utils\UrlUtils::encodeId($sale->id) }}">
            <div class="space-y-4">
                <!-- Header with Status -->
                <div class="flex items-start justify-between gap-3">
                    <div class="flex items-start gap-3 flex-1">
                        <div class="pt-1">
                            <input type="checkbox" value="{{ \App\Utils\UrlUtils::encodeId($sale->id) }}" class="sale-checkbox h-4 w-4 text-indigo-600 border-gray-300 rounded focus:ring-indigo-500">
                        </div>
                        <div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-1">{{ $sale->name }}</h3>
                            <a href="mailto:{{ $sale->email }}" class="text-blue-600 hover:text-blue-800 text-sm">{{ $sale->email }}</a>
                        </div>
                    </div>
                    <div class="ml-4">
                        @if($sale->status === 'paid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 text-green-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.paid') }}
                            </span>
                        @elseif($sale->status === 'unpaid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 text-yellow-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.unpaid') }}
                            </span>
                        @elseif($sale->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.cancelled') }}
                            </span>
                        @elseif($sale->status === 'refunded')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.refunded') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 text-gray-800">
                                {{ __('messages.' . $sale->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Event Info -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-sm font-medium text-gray-700 mb-1">{{ __('messages.event') }}</div>
                    <a href="{{ $sale->getEventUrl() }}" target="_blank" class="text-blue-600 hover:text-blue-800 font-medium">{{ $sale->event->name }}</a>
                </div>

                <!-- Payment Details -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">{{ __('messages.total') }}</div>
                        <div class="text-lg font-bold text-gray-900">{{ number_format($sale->payment_amount, 2, '.', ',') }}</div>
                        <div class="text-sm text-gray-500">{{ $sale->event->ticket_currency_code }}</div>
                    </div>

                    <div class="bg-gray-50 rounded-lg p-3">
                        <div class="text-sm font-medium text-gray-700 mb-1">{{ __('messages.transaction_reference') }}</div>
                        <div class="text-sm text-gray-900">
                            @if ($sale->transaction_reference == __('messages.manual_payment'))
                                <span class="text-gray-600">{{ __('messages.manual_payment') }}</span>
                            @elseif ($sale->payment_method == 'invoiceninja')
                                <a href="https://app.invoicing.co/#/invoices/{{ $sale->transaction_reference }}/edit" target="_blank" class="text-blue-600 hover:text-blue-800 break-all">
                                    {{ $sale->transaction_reference }}
                                </a>
                            @elseif ($sale->payment_method == 'stripe')
                                <a href="https://dashboard.stripe.com/payments/{{ $sale->transaction_reference }}" target="_blank" class="text-blue-600 hover:text-blue-800 break-all">
                                    {{ $sale->transaction_reference }}
                                </a>
                            @else
                                <span class="font-mono text-sm break-all">{{ $sale->transaction_reference }}</span>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Ticket Usage -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-sm font-medium text-gray-700 mb-1">{{ __('messages.ticket_usage') }}</div>
                    @php
                        $usageStatus = $sale->usage_status;
                        $usageClasses = $usageStatus === 'used'
                            ? 'bg-orange-100 text-orange-800'
                            : 'bg-green-100 text-green-800';
                    @endphp
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ $usageClasses }}">
                        {{ __('messages.ticket_status_' . $usageStatus) }}
                    </span>
                </div>

                <!-- Tickets -->
                <div class="bg-gray-50 rounded-lg p-3">
                    <div class="text-sm font-medium text-gray-700 mb-2">{{ __('messages.tickets') }}</div>
                    <div class="space-y-2">
                        @foreach ($sale->saleTickets as $saleTicket)
                            @php
                                $ticketUsageStatus = $saleTicket->usage_status;
                                $ticketUsageClasses = $ticketUsageStatus === 'used'
                                    ? 'bg-orange-100 text-orange-800'
                                    : 'bg-green-100 text-green-800';
                            @endphp
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-900">{{ $saleTicket->ticket->type ?: __('messages.ticket') }} x {{ $saleTicket->quantity }}</span>
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $ticketUsageClasses }}">
                                    {{ __('messages.ticket_status_' . $ticketUsageStatus) }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>

                <!-- Actions -->
                <div class="pt-2">
                    <div class="relative" x-data="{ 
                        open: false,
                        positionDropdown() {
                            if (!this.open) return;
                            const button = this.$refs.button;
                            const dropdown = this.$refs.dropdown;
                            const rect = button.getBoundingClientRect();
                            
                            dropdown.style.position = 'fixed';
                            dropdown.style.top = `${rect.bottom + 4}px`;
                            dropdown.style.left = `${rect.left}px`;
                            dropdown.style.zIndex = '1000';
                        }
                    }">
                        <button x-on:click="open = !open; $nextTick(() => positionDropdown())"
                                x-ref="button"
                                type="button" 
                                class="w-full inline-flex items-center justify-center rounded-lg bg-blue-50 px-4 py-3 text-sm font-semibold text-blue-700 shadow-sm ring-1 ring-inset ring-blue-200 hover:bg-blue-100 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                            {{ __('messages.select_action') }}
                        </button>

                        <div x-show="open" 
                             x-ref="dropdown"
                             x-on:click.away="open = false"
                             class="w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                             role="menu" 
                             x-cloak
                             aria-orientation="vertical">
                            
                            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" 
                               target="_blank" 
                               x-on:click="open = false"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150"
                               role="menuitem">
                                {{ __('messages.view_ticket') }}
                            </a>

                            @if($sale->status === 'unpaid')
                                <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_paid')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.mark_paid') }}
                                </button>
                            @elseif($sale->status === 'paid')
                                <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_unpaid')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.mark_unpaid') }}
                                </button>
                            @endif

                            @if($sale->status === 'paid' && $sale->payment_method != 'cash')
                                <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'refund')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.refund') }}
                                </button>
                            @endif

                            <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_used')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                    role="menuitem">
                                {{ __('messages.mark_tickets_as_used') }}
                            </button>

                            <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_unused')"
                                    class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                    role="menuitem">
                                {{ __('messages.mark_tickets_as_unused') }}
                            </button>

                            @if(in_array($sale->status, ['unpaid', 'paid']))
                                <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'cancel')"
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.cancel') }}
                                </button>
                            @endif
                            
                            @if(! $sale->is_deleted)
                            <button x-on:click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'delete')"
                                    class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100 w-full text-left transition-colors duration-150" 
                                    role="menuitem">
                                {{ __('messages.delete') }}
                            </button>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="mt-6 px-4">
        {{ $sales->appends(request()->query())->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900">{{ __('messages.no_sales') }}</h3>
        <p class="mt-1 text-sm text-gray-500">{{ __('messages.no_sales_description') }}</p>
    </div>
    @endif
</div>

