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
                    ticket.selectedQty = 0;
                    ticket.available = ticket.quantity - ticket.sold;
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

        <div v-for="(ticket, index) in tickets" :key="ticket.id" class="mb-4">
            <div class="flex items-center justify-between max-w-md">
                <div>
                    <h3 class="text-lg font-medium">@{{ ticket.type }}</h3>
                    <p v-if="ticket.description" class="text-sm text-gray-600">@{{ ticket.description }}</p>
                    <p class="text-sm font-medium">@{{ formatPrice(ticket.price) }}</p>
                </div>
                <div>
                    <input 
                        type="number" 
                        v-model="ticket.selectedQty"
                        :max="ticket.available"
                        min="0"
                        class="block w-24 rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500"
                        :name="`tickets[${index}][quantity]`"
                    >
                    <input type="hidden" :value="ticket.id" :name="`tickets[${index}][id]`">
                </div>
            </div>
        </div>

        <div v-if="totalAmount > 0" class="mt-4 text-lg font-bold">
            Total: @{{ formatPrice(totalAmount) }}
        </div>

    </div>

    <button 
        type="submit" 
        class="mt-4 inline-flex gap-x-1.5 rounded-md bg-white px-6 py-3 text-lg font-semibold text-gray-500 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50"
        :disabled="!hasSelectedTickets"
    >
        {{ __('messages.checkout') }}
    </button>
</form>

