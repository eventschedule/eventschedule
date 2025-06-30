<div class="mt-8 flow-root">
    <!-- Desktop Table View -->
    <div class="hidden md:block overflow-x-auto px-4 sm:px-6 lg:px-8 bg-white">
        <div class="inline-block min-w-full py-2 align-middle">
            <div class="overflow-hidden min-w-full">
                <table class="min-w-full divide-y divide-gray-300">
                    <thead>
                        <tr>
                            <th scope="col" 
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                {{ __('messages.customer') }}
                            </th>
                            <th scope="col" 
                                class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-0">
                                {{ __('messages.event') }}
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
                            </th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @foreach ($sales as $sale)
                        <tr class="bg-white">
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                {{ $sale->name }} • <a href="mailto:{{ $sale->email }}" class="hover:underline">{{ $sale->email }}</a>
                            </td>
                            <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm font-medium text-gray-900 sm:pl-0">
                                <a href="{{ $sale->getEventUrl() }}"
                                    target="_blank" class="hover:underline">{{ $sale->event->name }}
                                </a>
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ number_format($sale->payment_amount, 2, '.', ',') }} {{ $sale->event->ticket_currency_code }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                @if ($sale->transaction_reference == __('messages.manual_payment'))
                                    {{ __('messages.manual_payment') }}
                                @elseif ($sale->payment_method == 'invoiceninja')
                                    <a href="https://app.invoicing.co/#/invoices/{{ $sale->transaction_reference }}/edit" target="_blank" class="hover:underline">
                                        {{ $sale->transaction_reference }}
                                    </a>
                                @elseif ($sale->payment_method == 'stripe')
                                    <a href="https://dashboard.stripe.com/payments/{{ $sale->transaction_reference }}" target="_blank" class="hover:underline">
                                        {{ $sale->transaction_reference }}
                                    </a>
                                @else
                                    {{ $sale->transaction_reference }}
                                @endif
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                {{ __('messages.' . $sale->status) }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                <div class="flex items-center gap-3">
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
                                                class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                            {{ __('messages.select_action') }}
                                            <svg class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                            </svg>
                                        </button>

                                        <div x-show="open" 
                                             x-ref="dropdown"
                                             @click.away="open = false"
                                             class="w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                                             role="menu" 
                                             x-cloak
                                             aria-orientation="vertical">
                                            
                                            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" 
                                               target="_blank" 
                                               @click="open = false"
                                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                                               role="menuitem">
                                                {{ __('messages.view_ticket') }}
                                            </a>

                                            @if($sale->status === 'unpaid')
                                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_paid')" 
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" 
                                                        role="menuitem">
                                                    {{ __('messages.mark_paid') }}
                                                </button>
                                            @endif

                                            @if(false && $sale->status === 'paid' && $sale->payment_method != 'cash')
                                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'refund')" 
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" 
                                                        role="menuitem">
                                                    {{ __('messages.refund') }}
                                                </button>
                                            @endif

                                            @if(in_array($sale->status, ['unpaid', 'paid']))
                                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'cancel')" 
                                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" 
                                                        role="menuitem">
                                                    {{ __('messages.cancel') }}
                                                </button>
                                            @endif
                                            
                                            @if(! $sale->is_deleted)
                                            <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'delete')" 
                                                    class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100 w-full text-left" 
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

    <!-- Mobile List View -->
    <div class="md:hidden space-y-4 px-4">
        @foreach ($sales as $sale)
        <div class="bg-white rounded-lg shadow border border-gray-200 p-4">
            <div class="space-y-3">
                <!-- Customer Info -->
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ $sale->name }}</div>
                    <div class="text-sm text-gray-500">
                        <a href="mailto:{{ $sale->email }}" class="hover:underline">{{ $sale->email }}</a>
                    </div>
                </div>

                <!-- Event Info -->
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ __('messages.event') }}</div>
                    <div class="text-sm text-gray-500">
                        <a href="{{ $sale->getEventUrl() }}" target="_blank" class="hover:underline">{{ $sale->event->name }}</a>
                    </div>
                </div>

                <!-- Total -->
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ __('messages.total') }}</div>
                    <div class="text-sm text-gray-500">
                        {{ number_format($sale->payment_amount, 2, '.', ',') }} {{ $sale->event->ticket_currency_code }}
                    </div>
                </div>

                <!-- Transaction Reference -->
                @if ($sale->transaction_reference)
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ __('messages.transaction_reference') }}</div>
                    <div class="text-sm text-gray-500">
                        @if ($sale->transaction_reference == __('messages.manual_payment'))
                            {{ __('messages.manual_payment') }}
                        @elseif ($sale->payment_method == 'invoiceninja')
                            <a href="https://app.invoicing.co/#/invoices/{{ $sale->transaction_reference }}/edit" target="_blank" class="hover:underline">
                                {{ $sale->transaction_reference }}
                            </a>
                        @elseif ($sale->payment_method == 'stripe')
                            <a href="https://dashboard.stripe.com/payments/{{ $sale->transaction_reference }}" target="_blank" class="hover:underline">
                                {{ $sale->transaction_reference }}
                            </a>
                        @else
                            {{ $sale->transaction_reference }}
                        @endif
                    </div>
                </div>
                @endif

                <!-- Status -->
                <div>
                    <div class="text-sm font-medium text-gray-900">{{ __('messages.status') }}</div>
                    <div class="text-sm text-gray-500">{{ __('messages.' . $sale->status) }}</div>
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
                        <button @click="open = !open; $nextTick(() => positionDropdown())" 
                                x-ref="button"
                                type="button" 
                                class="w-full inline-flex items-center justify-center rounded-md bg-gray-50 px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-100">
                            {{ __('messages.select_action') }}
                            <svg class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                            </svg>
                        </button>

                        <div x-show="open" 
                             x-ref="dropdown"
                             @click.away="open = false"
                             class="w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none" 
                             role="menu" 
                             x-cloak
                             aria-orientation="vertical">
                            
                            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" 
                               target="_blank" 
                               @click="open = false"
                               class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left"
                               role="menuitem">
                                {{ __('messages.view_ticket') }}
                            </a>

                            @if($sale->status === 'unpaid')
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_paid')" 
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" 
                                        role="menuitem">
                                    {{ __('messages.mark_paid') }}
                                </button>
                            @endif

                            @if(false && $sale->status === 'paid' && $sale->payment_method != 'cash')
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'refund')" 
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" 
                                        role="menuitem">
                                    {{ __('messages.refund') }}
                                </button>
                            @endif

                            @if(in_array($sale->status, ['unpaid', 'paid']))
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'cancel')" 
                                        class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left" 
                                        role="menuitem">
                                    {{ __('messages.cancel') }}
                                </button>
                            @endif
                            
                            @if(! $sale->is_deleted)
                            <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'delete')" 
                                    class="block px-4 py-2 text-sm text-red-700 hover:bg-gray-100 w-full text-left" 
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

    <div class="mt-4 px-4">
        {{ $sales->links() }}
    </div>
</div>

