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
                                    class="py-3.5 ps-4 pe-3 text-start text-sm font-semibold text-gray-900 dark:text-gray-100 sm:ps-6">
                                    {{ __('messages.customer') }}
                                </th>
                                <th scope="col"
                                    class="py-3.5 ps-4 pe-3 text-start text-sm font-semibold text-gray-900 dark:text-gray-100 sm:ps-6">
                                    {{ __('messages.event') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.total') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.transaction_reference') }}
                                </th>
                                <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.status') }}
                                </th>
                                <th scope="col" class="relative py-3.5 ps-3 pe-4 sm:pe-6">
                                    <span class="sr-only">{{ __('messages.actions') }}</span>
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                            @foreach ($sales as $sale)
                            <tr class="bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                                <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
                                    <div class="flex flex-col">
                                        <span class="font-semibold">{{ $sale->name }}</span>
                                        <a href="mailto:{{ $sale->email }}" class="text-blue-600 dark:text-blue-400 hover:text-blue-800 dark:hover:text-blue-300 hover:underline text-xs">{{ $sale->email }}</a>
                                    </div>
                                </td>
                                <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
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
                                        <x-link href="https://app.invoicing.co/#/invoices/{{ $sale->transaction_reference }}/edit" target="_blank">
                                            {{ $sale->transaction_reference }}
                                        </x-link>
                                    @elseif ($sale->payment_method == 'stripe')
                                        <x-link href="https://dashboard.stripe.com/payments/{{ $sale->transaction_reference }}" target="_blank">
                                            {{ $sale->transaction_reference }}
                                        </x-link>
                                    @else
                                        <span class="font-mono text-sm">{{ $sale->transaction_reference }}</span>
                                    @endif
                                </td>
                                <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                    @if($sale->status === 'paid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300">
                                            <svg class="w-3 h-3 me-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.paid') }}
                                        </span>
                                    @elseif($sale->status === 'unpaid')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300">
                                            <svg class="w-3 h-3 me-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.unpaid') }}
                                        </span>
                                    @elseif($sale->status === 'cancelled')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300">
                                            <svg class="w-3 h-3 me-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.cancelled') }}
                                        </span>
                                    @elseif($sale->status === 'refunded')
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                            <svg class="w-3 h-3 me-1" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                            </svg>
                                            {{ __('messages.refunded') }}
                                        </span>
                                    @else
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                            {{ __('messages.' . $sale->status) }}
                                        </span>
                                    @endif
                                </td>
                                <td class="relative whitespace-nowrap py-4 ps-3 pe-4 text-end text-sm font-medium sm:pe-6">
                                    <div class="flex items-center justify-end gap-3">
                                        <div class="relative inline-block text-start">
                                            <button type="button" onclick="onPopUpClick('sale-actions-pop-up-menu-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', event)" class="inline-flex items-center justify-center rounded-md bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700" id="sale-actions-menu-button-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}" aria-expanded="true" aria-haspopup="true">
                                                {{ __('messages.actions') }}
                                                <svg class="-me-1 ms-2 h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div id="sale-actions-pop-up-menu-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}" class="pop-up-menu hidden absolute end-0 z-10 mt-2 w-64 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-gray-600 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="sale-actions-menu-button-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}" tabindex="-1">
                                                <div class="py-2" role="none" onclick="onPopUpClick('sale-actions-pop-up-menu-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', event)">
                                                    <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" target="_blank" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                                        <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z" />
                                                        </svg>
                                                        <div>
                                                            {{ __('messages.view_ticket') }}
                                                        </div>
                                                    </a>
                                                    <button onclick="onPopUpClick('sale-actions-pop-up-menu-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', event); resendEmail('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}')" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors w-full text-start" role="menuitem" tabindex="0">
                                                        <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M20,8L12,13L4,8V6L12,11L20,6M20,4H4C2.89,4 2,4.89 2,6V18A2,2 0 0,0 4,20H20A2,2 0 0,0 22,18V6C22,4.89 21.1,4 20,4Z" />
                                                        </svg>
                                                        <div>
                                                            {{ __('messages.send_email') }}
                                                        </div>
                                                    </button>
                                                    @if($sale->status === 'unpaid')
                                                    <button onclick="onPopUpClick('sale-actions-pop-up-menu-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', event); handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_paid')" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors w-full text-start" role="menuitem" tabindex="0">
                                                        <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M21,7L9,19L3.5,13.5L4.91,12.09L9,16.17L19.59,5.59L21,7Z" />
                                                        </svg>
                                                        <div>
                                                            {{ __('messages.mark_paid') }}
                                                        </div>
                                                    </button>
                                                    @endif
                                                    @if(false && $sale->status === 'paid' && $sale->payment_method != 'cash')
                                                    <button onclick="onPopUpClick('sale-actions-pop-up-menu-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', event); handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'refund')" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors w-full text-start" role="menuitem" tabindex="0">
                                                        <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M12,18A6,6 0 0,1 6,12C6,11 6.25,10.03 6.7,9.2L5.24,7.74C4.46,8.97 4,10.43 4,12A8,8 0 0,0 12,20C13.57,20 15.03,19.54 16.26,18.76L14.8,17.3C13.97,17.75 13,18 12,18M20,12A8,8 0 0,0 12,4C10.43,4 8.97,4.46 7.74,5.24L9.2,6.7C10.03,6.25 11,6 12,6A6,6 0 0,1 18,12C18,13 17.75,13.97 17.3,14.8L18.76,16.26C19.54,15.03 20,13.57 20,12M14.8,17.3L16.26,18.76L18.76,16.26L17.3,14.8L14.8,17.3M9.2,6.7L7.74,5.24L5.24,7.74L6.7,9.2L9.2,6.7Z" />
                                                        </svg>
                                                        <div>
                                                            {{ __('messages.refund_ticket') }}
                                                        </div>
                                                    </button>
                                                    @endif
                                                    @if(in_array($sale->status, ['unpaid', 'paid']))
                                                    <button onclick="onPopUpClick('sale-actions-pop-up-menu-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', event); handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'cancel')" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors w-full text-start" role="menuitem" tabindex="0">
                                                        <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M19,6.41L17.59,5L12,10.59L6.41,5L5,6.41L10.59,12L5,17.59L6.41,19L12,13.41L17.59,19L19,17.59L13.41,12L19,6.41Z" />
                                                        </svg>
                                                        <div>
                                                            {{ __('messages.cancel_ticket') }}
                                                        </div>
                                                    </button>
                                                    @endif
                                                    @if(! $sale->is_deleted)
                                                    <div class="py-2" role="none">
                                                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                                                    </div>
                                                    <button onclick="onPopUpClick('sale-actions-pop-up-menu-{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', event); handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'delete')" class="group flex items-center px-5 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 focus:bg-red-50 dark:focus:bg-red-900/20 focus:text-red-700 dark:focus:text-red-300 focus:outline-none transition-colors w-full text-start" role="menuitem" tabindex="0">
                                                        <svg class="me-3 h-5 w-5 text-red-400 dark:text-red-500 group-hover:text-red-500 dark:group-hover:text-red-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                                            <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                                                        </svg>
                                                        <div>
                                                            {{ __('messages.delete') }}
                                                        </div>
                                                    </button>
                                                    @endif
                                                </div>
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
                    <div class="ms-4">
                        @if($sale->status === 'paid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-100 dark:bg-green-900 text-green-800 dark:text-green-300">
                                <svg class="w-4 h-4 me-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.paid') }}
                            </span>
                        @elseif($sale->status === 'unpaid')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-100 dark:bg-yellow-900 text-yellow-800 dark:text-yellow-300">
                                <svg class="w-4 h-4 me-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.unpaid') }}
                            </span>
                        @elseif($sale->status === 'cancelled')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 dark:bg-red-900 text-red-800 dark:text-red-300">
                                <svg class="w-4 h-4 me-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.cancelled') }}
                            </span>
                        @elseif($sale->status === 'refunded')
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                <svg class="w-4 h-4 me-1.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1zm0 4a1 1 0 011-1h12a1 1 0 110 2H4a1 1 0 01-1-1z" clip-rule="evenodd"></path>
                                </svg>
                                {{ __('messages.refunded') }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-100 dark:bg-gray-700 text-gray-800 dark:text-gray-300">
                                {{ __('messages.' . $sale->status) }}
                            </span>
                        @endif
                    </div>
                </div>

                <!-- Event Info -->
                <div class="bg-gray-50 dark:bg-gray-700 rounded-lg p-3">
                    <div class="text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">{{ __('messages.event') }}</div>
                    <x-link href="{{ $sale->getEventUrl() }}" target="_blank" class="font-medium">{{ $sale->event->name }}</x-link>
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
                                <x-link href="https://app.invoicing.co/#/invoices/{{ $sale->transaction_reference }}/edit" target="_blank" class="break-all">
                                    {{ $sale->transaction_reference }}
                                </x-link>
                            @elseif ($sale->payment_method == 'stripe')
                                <x-link href="https://dashboard.stripe.com/payments/{{ $sale->transaction_reference }}" target="_blank" class="break-all">
                                    {{ $sale->transaction_reference }}
                                </x-link>
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
                            <svg class="w-4 h-4 me-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                               class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150"
                               role="menuitem">
                                {{ __('messages.view_ticket') }}
                            </a>

                            <button @click="open = false; resendEmail('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}')" 
                                    class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150" 
                                    role="menuitem">
                                {{ __('messages.send_email') }}
                            </button>

                            @if($sale->status === 'unpaid')
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'mark_paid')" 
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.mark_paid') }}
                                </button>
                            @endif

                            @if(false && $sale->status === 'paid' && $sale->payment_method != 'cash')
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'refund')" 
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.refund') }}
                                </button>
                            @endif

                            @if(in_array($sale->status, ['unpaid', 'paid']))
                                <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'cancel')" 
                                        class="block px-4 py-2 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150" 
                                        role="menuitem">
                                    {{ __('messages.cancel') }}
                                </button>
                            @endif
                            
                            @if(! $sale->is_deleted)
                            <button @click="open = false; handleAction('{{ \App\Utils\UrlUtils::encodeId($sale->id) }}', 'delete')" 
                                    class="block px-4 py-2 text-sm text-red-700 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 w-full text-start transition-colors duration-150" 
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

