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

