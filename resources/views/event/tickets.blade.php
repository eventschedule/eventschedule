<x-slot name="head">
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
  <script>
    window.addEventListener('DOMContentLoaded', function() {
        const { createApp, ref } = Vue;

        const app = createApp({
            data() {
                return {
                    createAccount: false,
                    tickets: @json($event->tickets->map(function ($ticket) { 
                        return $ticket->toData(request()->date); 
                    })),
                    name: '{{ old('name', auth()->check() ? auth()->user()->name : '') }}',
                    email: '{{ old('email', auth()->check() ? auth()->user()->email : '') }}',
                    password: ''
                };
            },
            created() {
                this.tickets.forEach(ticket => {
                    ticket.selectedQty = 0;
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
                }
            }
        }).mount('#ticket-selector');
    });
</script>
</x-slot>

<div id="ticket-selector">
    <form action="{{ route('event.checkout', ['subdomain' => $subdomain]) }}" method="post" v-on:submit="validateForm">
        @csrf
        <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">
        <input type="hidden" name="event_date" value="{{ $date }}">

        <div class="mb-6">
            <label for="name" class="text-gray-900">{{ __('messages.name') . ' *' }}</label>
            <input type="text" name="name" id="name" class="mt-1 block w-full border-gray-300 bg-white text-gray-900" 
                v-model="name" required autofocus autocomplete="name" />
        </div>

        <div class="mb-12">
            <label for="email" class="text-gray-900">{{ __('messages.email') . ' *' }}</label>
            <input type="email" name="email" id="email" class="mt-1 block w-full border-gray-300 bg-white text-gray-900" 
                v-model="email" required autocomplete="email" />

            @if (! auth()->check())
                <div class="mt-6">
                    <div class="flex items-center">
                        <input id="create_account" name="create_account" type="checkbox" 
                            v-model="createAccount" value="1"
                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                        <label for="create_account" class="ml-3 block text-sm font-medium leading-6 text-gray-900">
                            {{ __('messages.create_account') }}
                        </label>
                    </div>

                    <div class="mt-6" v-if="createAccount">
                        <label for="password" class="text-gray-900">{{ __('messages.password') . ' *' }}</label>
                        <input type="password" name="password" id="password" class="mt-1 block w-full border-gray-300 bg-white text-gray-900" 
                            v-model="password" required autocomplete="new-password" />
                    </div>
                </div>
            @endif
        </div>

    

        <div v-for="(ticket, index) in tickets" :key="ticket.id" class="mb-8">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium">@{{ ticket.type }}</h3>
                    <p v-if="ticket.description" class="text-sm text-gray-600">@{{ ticket.description }}</p>
                    <p :class="{'text-lg': tickets.length === 1, 'text-sm': tickets.length > 1}" class="font-medium">@{{ formatPrice(ticket.price) }}</p>
                </div>
                <div>
                    <p v-if="ticket.quantity === 0" class="text-lg font-medium text-gray-500">{{ __('messages.sold_out') }}</p>
                    <p v-else>
                    <select 
                        v-model="ticket.selectedQty"
                        class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :name="`tickets[${index}][quantity]`"
                    >
                        <option value="0">0</option>
                        <template v-for="n in ticket.quantity">
                            <option :value="n">@{{ n }}</option>
                            </template>
                        </select>
                    </p>
                    <input type="hidden" :value="ticket.id" :name="`tickets[${index}][id]`">
                </div>
            </div>
        </div>

        <hr/>
        
        <div class="my-4 text-lg font-bold">
            Total: @{{ formatPrice(totalAmount) }}
        </div>

        <div class="flex justify-center items-center py-4 gap-8">
            <button 
                type="submit" 
                class="mt-4 inline-flex gap-x-1.5 rounded-md bg-white px-6 py-3 text-lg font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:bg-white"
                :disabled="!hasSelectedTickets"
            >
                {{ strtoupper(__('messages.checkout')) }}
            </button>
            
            <a href="{{ request()->fullUrlWithQuery(['tickets' => false]) }}" class="hover:underline mt-4">
                {{ strtoupper(__('messages.cancel')) }}
            </a>
        </div>

        @if ($event->payment_method == 'cash' && $event->payment_instructions)
            <div class="mt-8 text-lg font-bold">
                {{ $event->payment_instructions }}
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

