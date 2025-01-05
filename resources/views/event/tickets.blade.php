<x-slot name="head">
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
  <script>
    window.addEventListener('DOMContentLoaded', function() {
        const { createApp, ref } = Vue;

        const app = createApp({
            data() {
                return {
                    tickets: @json($event->tickets),
                };
            },
            created() {
                this.tickets.forEach(ticket => {
                    ticket.selectedQty = this.tickets.length === 1 ? 1 : 0;
                    ticket.available = ticket.quantity - ticket.sold;
                    ticket.maxQty = Math.min(20, ticket.available);
                });
            },
            computed: {
                totalAmount() {
                    return this.tickets.reduce((total, ticket) => {
                        return total + (ticket.price * ticket.selectedQty);
                    }, 0);
                },
                hasSelectedTickets() {
                    return this.tickets.some(ticket => ticket.selectedQty > 0);
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

<form action="{{ route('event.checkout', ['subdomain' => $subdomain]) }}" method="post" v-on:submit="validateForm">
    @csrf
    <input type="hidden" name="event_id" value="{{ $event->id }}">
    
    <div id="ticket-selector">

        <div v-for="(ticket, index) in tickets" :key="ticket.id" class="mb-8">
            <div class="flex items-center justify-between max-w-md">
                <div>
                    <h3 class="text-lg font-medium">@{{ ticket.type }}</h3>
                    <p v-if="ticket.description" class="text-sm text-gray-600">@{{ ticket.description }}</p>
                    <p class="text-sm font-medium">@{{ formatPrice(ticket.price) }}</p>
                </div>
                <div>
                    <select
                        v-model="ticket.selectedQty"
                        class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :name="`tickets[${index}][quantity]`"
                    >
                        <option v-for="n in ticket.maxQty + 1" :key="n" :value="n-1">@{{ n-1 }}</option>
                    </select>
                    <input type="hidden" :value="ticket.id" :name="`tickets[${index}][id]`">
                </div>
            </div>
        </div>

        <hr class="max-w-md"/>
        
        <div class="my-4 text-lg font-bold">
            Total: @{{ formatPrice(totalAmount) }}
        </div>


        <button 
            type="submit" 
            class="mt-4 inline-flex gap-x-1.5 rounded-md bg-white px-6 py-3 text-lg font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 disabled:opacity-50 disabled:cursor-not-allowed"
            :disabled="!hasSelectedTickets"
        >
            {{ strtoupper(__('messages.checkout')) }}
        </button>
        
        <a href="{{ request()->fullUrlWithQuery(['tickets' => false]) }}" class="hover:underline ml-8">
            {{ strtoupper(__('messages.cancel')) }}
        </a>

    </div>

</form>

