<div class="mt-8 flow-root">
    @if($sales->count() > 0)
    <!-- Desktop Table View -->
    <div class="hidden md:block -mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <div class="overflow-x-auto" style="overflow-x: auto; scrollbar-width: thin;">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                        <thead class="bg-gray-50 dark:bg-gray-800">
                            <tr>
                                <th scope="col" 
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">
                                    {{ __('messages.customer') }}
                                </th>
                                <th scope="col" 
                                    class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 dark:text-gray-100 sm:pl-6">
                                    {{ __('messages.event') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.total') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.transaction_reference') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.status') }}
                                </th>
                                <th scope="col" class="relative py-3.5 pl-3 pr-4 sm:pr-6">
                                    <span class="sr-only">{{ __('messages.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($sales as $sale)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:pl-6">
                                    <div class="flex flex-col">
                                        <span class="font-semibold">{{ $sale->name }}</span>
                                        <a href="mailto:{{ $sale->email }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline text-xs">{{ $sale->email }}</a>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:pl-6">
                                    <a href="{{ $sale->getEventUrl() }}"
                                        target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">{{ $sale->event->name }}
                                    </a>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    <span class="font-semibold text-gray-900 dark:text-gray-100">{{ number_format($sale->payment_amount, 2, '.', ',') }}</span>
                                    <span class="text-gray-500 dark:text-gray-400">{{ $sale->event->ticket_currency_code }}</span>
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if ($sale->transaction_reference == __('messages.manual_payment'))
                                        <span class="text-gray-600 dark:text-gray-400">{{ __('messages.manual_payment') }}</span>
                                    @elseif ($sale->payment_method == 'invoiceninja')
                                        <a href="https://app.invoicing.co/#/invoices/{{ $sale->transaction_reference }}/edit" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                                            {{ $sale->transaction_reference }}
                                        </a>
                                    @elseif ($sale->payment_method == 'stripe')
                                        <a href="https://dashboard.stripe.com/payments/{{ $sale->transaction_reference }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline">
                                            {{ $sale->transaction_reference }}
                                        </a>
                                    @else
                                        <span class="font-mono text-sm">{{ $sale->transaction_reference }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($sale->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.paid') }}
                                        </span>
                                    @elseif($sale->status === 'unpaid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.unpaid') }}
                                        </span>
                                    @elseif($sale->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.cancelled') }}
                                        </span>
                                    @elseif($sale->status === 'refunded')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                            <svg class="w-3 h-3 mr-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.refunded') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                            {{ __('messages.' . $sale->status) }}
                                        </span>
                                    @endif
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
                                            <button @click="open = !open; $nextTick(() => positionDropdown())" 
                                                    x-ref="button"
                                                    type="button" 
                                                    class="inline-flex items-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                                {{ __('messages.select_action') }}
                                                <svg class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>

                                            <div x-show="open" 
                                                 x-ref="dropdown"
                                                 @click.away="open = false"
                                                 class="w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-gray-700 focus:outline-none" 
                                                 role="menu" 
                                                 x-cloak
                                                 aria-orientation="vertical">
                                                
                                                <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" 
                                                   target="_blank" 
                                                   @click="open = false"
                                                   class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150"
                                                   role="menuitem">
                                                    {{ __('messages.view_ticket') }}
                                                </a>

                                                <button @click="open = false; resendEmail('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}')" 
                                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
                                                        role="menuitem">
                                                    {{ __('messages.send_email') }}
                                                </button>

                                                @if($sale->status === 'unpaid')
                                                    <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_paid')" 
                                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
                                                            role="menuitem">
                                                        {{ __('messages.mark_paid') }}
                                                    </button>
                                                @endif

                                                @if(false && $sale->status === 'paid' && $sale->payment_method != 'cash')
                                                    <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'refund')" 
                                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
                                                            role="menuitem">
                                                        {{ __('messages.refund') }}
                                                    </button>
                                                @endif

                                                @if(in_array($sale->status, ['unpaid', 'paid']))
                                                    <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'cancel')" 
                                                            class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
                                                            role="menuitem">
                                                        {{ __('messages.cancel') }}
                                                    </button>
                                                @endif
                                                
                                                @if(! $sale->is_deleted)
                                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'delete')" 
                                                        class="block px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
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
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-sm border border-gray-200 dark:border-gray-700 p-5 hover:shadow-md transition-shadow duration-200">
            <div class="space-y-4">
                <!-- Header with Status -->
                <div class="flex items-start justify-between">
                    <div class="flex-1">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ $sale->name }}</h3>
                        <a href="mailto:{{ $sale->email }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 text-sm">{{ $sale->email }}</a>
                    </div>
                    <div class="ml-4">
                        @if($sale->status === 'paid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-200">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.paid') }}
                            </span>
                        @elseif($sale->status === 'unpaid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-200">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.unpaid') }}
                            </span>
                        @elseif($sale->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-200">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.cancelled') }}
                            </span>
                        @elseif($sale->status === 'refunded')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                <svg class="w-4 h-4 mr-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.refunded') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-200">
                                {{ __('messages.' . $sale->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Event Info -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.event') }}</div>
                    <a href="{{ $sale->getEventUrl() }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 font-medium">{{ $sale->event->name }}</a>
                </div>

                <!-- Payment Details -->
                <div class="grid grid-cols-2 gap-4">
                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.total') }}</div>
                        <div class="text-lg font-bold text-gray-900 dark:text-gray-100">{{ number_format($sale->payment_amount, 2, '.', ',') }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $sale->event->ticket_currency_code }}</div>
                    </div>

                    <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                        <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.transaction_reference') }}</div>
                        <div class="text-sm text-gray-900 dark:text-gray-100">
                            @if ($sale->transaction_reference == __('messages.manual_payment'))
                                <span class="text-gray-600 dark:text-gray-400">{{ __('messages.manual_payment') }}</span>
                            @elseif ($sale->payment_method == 'invoiceninja')
                                <a href="https://app.invoicing.co/#/invoices/{{ $sale->transaction_reference }}/edit" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 break-all">
                                    {{ $sale->transaction_reference }}
                                </a>
                            @elseif ($sale->payment_method == 'stripe')
                                <a href="https://dashboard.stripe.com/payments/{{ $sale->transaction_reference }}" target="_blank" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 break-all">
                                    {{ $sale->transaction_reference }}
                                </a>
                            @else
                                <span class="font-mono text-sm break-all">{{ $sale->transaction_reference }}</span>
                            @endif
                        </div>
                    </div>                </div>

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
                        <button @click="open = !open; $nextTick(() => positionDropdown())" 
                                x-ref="button"
                                type="button" 
                                class="w-full inline-flex items-center justify-center rounded-lg bg-blue-50 dark:bg-blue-900/30 px-4 py-3 text-sm font-semibold text-blue-700 dark:text-blue-300 shadow-sm ring-1 ring-inset ring-blue-200 dark:ring-blue-800 hover:bg-blue-100 dark:hover:bg-blue-900/50 transition-colors duration-150">
                            <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 5v.01M12 12v.01M12 19v.01M12 6a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2zm0 7a1 1 0 110-2 1 1 0 010 2z"></path>
                            </svg>
                            {{ __('messages.select_action') }}
                        </button>

                        <div x-show="open" 
                             x-ref="dropdown"
                             @click.away="open = false"
                             class="w-48 origin-top-right rounded-md bg-white dark:bg-gray-800 py-1 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-gray-700 focus:outline-none" 
                             role="menu" 
                             x-cloak
                             aria-orientation="vertical">
                            
                            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" 
                               target="_blank" 
                               @click="open = false"
                               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150"
                               role="menuitem">
                                {{ __('messages.view_ticket') }}
                            </a>

                            <button @click="open = false; resendEmail('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}')" 
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
                                    role="menuitem">
                                {{ __('messages.send_email') }}
                            </button>

                            @if($sale->status === 'unpaid')
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_paid')" 
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.mark_paid') }}
                                </button>
                            @endif

                            @if(false && $sale->status === 'paid' && $sale->payment_method != 'cash')
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'refund')" 
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.refund') }}
                                </button>
                            @endif

                            @if(in_array($sale->status, ['unpaid', 'paid']))
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'cancel')" 
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.cancel') }}
                                </button>
                            @endif
                            
                            @if(! $sale->is_deleted)
                            <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'delete')" 
                                    class="block px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-left transition-colors duration-150" 
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
        {{ $sales->links() }}
    </div>
    @else
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
        </svg>
        <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.no_sales') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.no_sales_description') }}</p>
    </div>
    @endif
</div>

