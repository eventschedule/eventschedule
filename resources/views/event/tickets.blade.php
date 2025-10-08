<x-slot name="head">
  <script src="{{ asset('js/vue.global.prod.js') }}"></script>
  <script {!! nonce_attr() !!}>
    window.addEventListener('DOMContentLoaded', function() {
        const { createApp, ref } = Vue;

        const app = createApp({
            data() {
                return {
                    createAccount: @json(old('create_account', false)),
                    tickets: @json($event->tickets->map(function ($ticket) { 
                        $data = $ticket->toData(request()->date); 
                        $data['selectedQty'] = old('tickets')[$data['id']] ?? 0;
                        return $data;
                    })),
                    name: @json(old('name', auth()->check() ? auth()->user()->name : '')),
                    email: @json(old('email', auth()->check() ? auth()->user()->email : '')),
                    password: '',
                    totalTicketsMode: @json($event->total_tickets_mode ?? 'individual'),
                };
            },
            created() {
                this.tickets.forEach(ticket => {
                    if (! ticket.selectedQty) {
                        ticket.selectedQty = 0;
                    }
                });
            },
            computed: {
                totalAmount() {
                    return this.tickets.reduce((total, ticket) => {
                        return total + (ticket.price * ticket.selectedQty);
                    }, 0);
                },
                hasSelectedTickets() {
                    const hasValidForm = this.tickets.some(ticket => ticket.selectedQty > 0) && 
                        this.name.trim() !== '' &&
                        this.email.trim() !== '';
                    return hasValidForm;
                },
                isCombinedMode() {
                    return this.totalTicketsMode === 'combined';
                },
                totalSelectedTickets() {
                    return this.tickets.reduce((total, ticket) => total + ticket.selectedQty, 0);
                },
                totalAvailableTickets() {
                    if (this.isCombinedMode) {
                        // In combined mode, all tickets have the same quantity
                        return this.tickets[0]?.quantity || 0;
                    }
                    return this.tickets.reduce((total, ticket) => total + ticket.quantity, 0);
                },
                remainingTickets() {
                    if (this.isCombinedMode) {
                        return this.totalAvailableTickets - this.totalSelectedTickets;
                    }
                    return null;
                }
            },
            methods: {
                formatPrice(price) {
                    return new Intl.NumberFormat('{{ app()->getLocale() }}', {
                        style: 'currency',
                        currency: '{{ $event->ticket_currency_code }}'
                    }).format(price);
                },
                validateForm(e) {
                    if (!this.hasSelectedTickets) {
                        e.preventDefault();
                        alert('Please select at least one ticket');
                    }
                },
                getAvailableQuantity(ticket) {
                    if (!this.isCombinedMode) {
                        return ticket.quantity;
                    }
                    
                    // In combined mode, calculate available based on other selections
                    const otherSelected = this.tickets
                        .filter(t => t.id !== ticket.id)
                        .reduce((total, t) => total + t.selectedQty, 0);
                    
                    return Math.max(0, this.totalAvailableTickets - otherSelected);
                },
                updateTicketQuantities() {
                    if (this.isCombinedMode) {
                        this.tickets.forEach(ticket => {
                            const available = this.getAvailableQuantity(ticket);
                            if (ticket.selectedQty > available) {
                                ticket.selectedQty = available;
                            }
                        });
                    }
                }
            },
            watch: {
                tickets: {
                    handler() {
                        this.updateTicketQuantities();
                    },
                    deep: true
                }
            },
        }).mount('#ticket-selector');
    });
</script>
</x-slot>

<div id="ticket-selector">
    <form action="{{ route('event.checkout', ['subdomain' => $subdomain]) }}" method="post" v-on:submit="validateForm">
        @csrf
        <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">
        <input type="hidden" name="event_date" value="{{ $date }}">
        <input type="hidden" name="subdomain" value="{{ $subdomain }}">

        <div class="mb-6">
            <label for="name" class="text-gray-900 dark:text-gray-100">{{ __('messages.name') . ' *' }}</label>
            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" 
                v-model="name" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mb-12">
            <label for="email" class="text-gray-900 dark:text-gray-100">{{ __('messages.email') . ' *' }}</label>
            <input type="email" name="email" id="email" class="mt-1 block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" 
                v-model="email" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (! auth()->check() && config('app.hosted'))
                <div class="mt-6">
                    <div class="flex items-center">
                        <input id="create_account" name="create_account" type="checkbox" 
                            v-model="createAccount" value="1"
                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                        <label for="create_account" class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                            {{ __('messages.create_account') }}
                        </label>
                    </div>

                    <div class="mt-6" v-if="createAccount">
                        <label for="password" class="text-gray-900 dark:text-gray-100">{{ __('messages.password') . ' *' }}</label>
                        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100" 
                            v-model="password" required autocomplete="new-password" />
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>
                </div>
            @endif
        </div>

    
        <div v-for="(ticket, index) in tickets" :key="ticket.id" class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">@{{ ticket.type }}</h3>
                    <p v-if="ticket.description" class="text-sm text-gray-600 dark:text-gray-400">@{{ ticket.description }}</p>
                    <p :class="{'text-lg': tickets.length === 1, 'text-sm': tickets.length > 1}" class="font-medium text-gray-900 dark:text-gray-100">@{{ formatPrice(ticket.price) }}</p>
                </div>
                <div>
                    <p v-if="getAvailableQuantity(ticket) === 0" class="text-lg font-medium text-gray-500 dark:text-gray-400">{{ __('messages.sold_out') }}</p>
                    <p v-else>
                    <select 
                        v-model="ticket.selectedQty"
                        @change="updateTicketQuantities"
                        class="block w-24 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :name="`tickets[${ticket.id}]`" :id="`ticket-${index}`"
                    >
                        <option value="0">0</option>
                        <template v-for="n in getAvailableQuantity(ticket)">
                            <option :value="n" :selected="ticket.selectedQty === n">@{{ n }}</option>
                            </template>
                        </select>
                    </p>
                </div>
            </div>
        </div>

        <hr/>
        
        <div class="my-4 text-lg font-bold text-gray-900 dark:text-gray-100">
            Total: @{{ formatPrice(totalAmount) }}
        </div>

        <div class="flex justify-center items-center py-4 gap-8">
            <button 
                type="submit" 
                class="mt-4 inline-flex gap-x-1.5 rounded-md bg-white dark:bg-gray-700 px-6 py-3 text-lg font-semibold text-gray-500 dark:text-gray-300 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white dark:disabled:hover:bg-gray-700"
                :disabled="!hasSelectedTickets"
            >
                {{ strtoupper(__('messages.checkout')) }}
            </button>
            
            <a href="{{ request()->fullUrlWithQuery(['tickets' => false]) }}" class="hover:underline mt-4 text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200">
                {{ strtoupper(__('messages.cancel')) }}
            </a>
        </div>

        @if ($event->payment_method == 'cash' && $event->payment_instructions_html)
            <div class="mt-8 custom-content">
                {!! \App\Utils\UrlUtils::convertUrlsToLinks($event->payment_instructions_html) !!}
            </div>
        @endif

        @if ($event->expire_unpaid_tickets > 0)
            <div class="mt-8">
                @if ($event->expire_unpaid_tickets == 1)
                    {{ __('messages.payment_must_be_completed_within_hour') }}
                @else
                    {{ __('messages.payment_must_be_completed_within_hours', ['count' => $event->expire_unpaid_tickets]) }}
                @endif
            </div>
        @endif


    </form>
</div>

