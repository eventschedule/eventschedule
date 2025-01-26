<x-app-admin-layout>

    <div class="mt-8 flow-root">
        <div class="flex justify-end">
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

    <div class="mt-8 flow-root">
        <div class="overflow-x-auto px-4 sm:px-6 lg:px-8 bg-white">
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
                                <th></th>
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
                                    @if ($sale->payment_method == 'invoiceninja')
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
                                    <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" target="_blank" class="inline-flex items-center rounded-md bg-white px-3 py-2 text-sm font-semibold text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50">
                                        {{ __('messages.view_ticket') }}                                        
                                    </a>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="mt-4">
                {{ $sales->links() }}
            </div>
        </div>
    </div>

</x-app-admin-layout>