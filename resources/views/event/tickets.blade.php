<x-slot name="head">
  <script src="{{ asset('js/vue.global.prod.js') }}"></script>
  @if (\App\Utils\TurnstileUtils::isEnabled())
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer></script>
  @endif
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
                        $data['custom_fields'] = $ticket->custom_fields ?? [];
                        $data['custom_values'] = (object) [];
                        return $data;
                    })),
                    eventCustomFields: @json($event->custom_fields ?? []),
                    eventCustomValues: @json(old('event_custom_values', [])),
                    name: @json(old('name', auth()->check() ? auth()->user()->name : '')),
                    email: @json(old('email', auth()->check() ? auth()->user()->email : '')),
                    password: '',
                    totalTicketsMode: @json($event->total_tickets_mode ?? 'individual'),
                    turnstileEnabled: @json(\App\Utils\TurnstileUtils::isEnabled()),
                    turnstileSiteKey: @json(\App\Utils\TurnstileUtils::getSiteKey()),
                    turnstileToken: '',
                    turnstileWidgetId: null,
                };
            },
            created() {
                this.tickets.forEach(ticket => {
                    if (! ticket.selectedQty) {
                        ticket.selectedQty = 0;
                    }
                });
            },
            mounted() {
                if (this.turnstileEnabled && this.turnstileSiteKey) {
                    const checkTurnstile = () => {
                        if (typeof turnstile !== 'undefined') {
                            this.turnstileWidgetId = turnstile.render('#turnstile-checkout-widget', {
                                sitekey: this.turnstileSiteKey,
                                callback: (token) => {
                                    this.turnstileToken = token;
                                },
                            });
                        } else {
                            setTimeout(checkTurnstile, 100);
                        }
                    };
                    checkTurnstile();
                }
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
                hasEventCustomFields() {
                    return this.eventCustomFields && Object.keys(this.eventCustomFields).length > 0;
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
                        return;
                    }
                    if (this.turnstileEnabled && !this.turnstileToken) {
                        e.preventDefault();
                        alert('{{ __('messages.turnstile_verification_failed') }}');
                        return;
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
            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                v-model="name" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mb-12">
            <label for="email" class="text-gray-900 dark:text-gray-100">{{ __('messages.email') . ' *' }}</label>
            <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
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
                        <div class="relative mt-1" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="password" id="password" class="block w-full pr-10 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                                v-model="password" required autocomplete="new-password" />
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 right-0 flex items-center pr-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg x-show="!show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg x-show="show" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>
                </div>
            @endif
        </div>

        <!-- Event-level Custom Fields -->
        <div v-if="hasEventCustomFields" class="mb-12">
            <div v-for="(field, fieldKey) in eventCustomFields" :key="fieldKey" class="mb-4">
                <label :for="`event_custom_${fieldKey}`" class="text-gray-900 dark:text-gray-100">
                    @{{ field.name }}@{{ field.required ? ' *' : '' }}
                </label>
                <!-- Text input -->
                <input v-if="field.type === 'string'" type="text"
                    :name="`event_custom_values[${fieldKey}]`"
                    :id="`event_custom_${fieldKey}`"
                    v-model="eventCustomValues[fieldKey]"
                    :required="field.required"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]" />
                <!-- Multiline text -->
                <textarea v-else-if="field.type === 'multiline_string'"
                    :name="`event_custom_values[${fieldKey}]`"
                    :id="`event_custom_${fieldKey}`"
                    v-model="eventCustomValues[fieldKey]"
                    :required="field.required"
                    rows="3"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"></textarea>
                <!-- Yes/No switch -->
                <div v-else-if="field.type === 'switch'" class="mt-1">
                    <select :name="`event_custom_values[${fieldKey}]`"
                        :id="`event_custom_${fieldKey}`"
                        v-model="eventCustomValues[fieldKey]"
                        :required="field.required"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                        <option value="">{{ __('messages.please_select') }}</option>
                        <option value="Yes">{{ __('messages.yes') }}</option>
                        <option value="No">{{ __('messages.no') }}</option>
                    </select>
                </div>
                <!-- Date picker -->
                <input v-else-if="field.type === 'date'" type="date"
                    :name="`event_custom_values[${fieldKey}]`"
                    :id="`event_custom_${fieldKey}`"
                    v-model="eventCustomValues[fieldKey]"
                    :required="field.required"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]" />
                <!-- Dropdown -->
                <select v-else-if="field.type === 'dropdown'"
                    :name="`event_custom_values[${fieldKey}]`"
                    :id="`event_custom_${fieldKey}`"
                    v-model="eventCustomValues[fieldKey]"
                    :required="field.required"
                    class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                    <option value="">{{ __('messages.please_select') }}</option>
                    <option v-for="option in (field.options || '').split(',')" :key="option.trim()" :value="option.trim()">@{{ option.trim() }}</option>
                </select>
            </div>
        </div>

        <div v-for="(ticket, index) in tickets" :key="ticket.id" class="mb-4 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm border-l-4 border-[#4E81FA]">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">@{{ ticket.type }}</h3>
                    <p v-if="ticket.description" class="text-sm text-gray-600 dark:text-gray-400" v-html="ticket.description"></p>
                    <p :class="{'text-lg': tickets.length === 1, 'text-sm': tickets.length > 1}" class="font-medium text-gray-900 dark:text-gray-100">@{{ formatPrice(ticket.price) }}</p>
                </div>
                <div>
                    <p v-if="getAvailableQuantity(ticket) === 0" class="text-lg font-medium text-gray-500 dark:text-gray-400">{{ __('messages.sold_out') }}</p>
                    <p v-else>
                    <select
                        v-model="ticket.selectedQty"
                        @change="updateTicketQuantities"
                        class="block w-24 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-center font-medium"
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

            <!-- Ticket-level Custom Fields (shown when ticket is selected) -->
            <div v-if="ticket.selectedQty > 0 && ticket.custom_fields && Object.keys(ticket.custom_fields).length > 0" class="mt-4 pl-4 border-l-2 border-gray-200 dark:border-gray-600">
                <div v-for="(field, fieldKey) in ticket.custom_fields" :key="fieldKey" class="mb-3">
                    <label :for="`ticket_custom_${ticket.id}_${fieldKey}`" class="text-sm text-gray-900 dark:text-gray-100">
                        @{{ field.name }}@{{ field.required ? ' *' : '' }}
                    </label>
                    <!-- Text input -->
                    <input v-if="field.type === 'string'" type="text"
                        :name="`ticket_custom_values[${ticket.id}][${fieldKey}]`"
                        :id="`ticket_custom_${ticket.id}_${fieldKey}`"
                        v-model="ticket.custom_values[fieldKey]"
                        :required="field.required"
                        class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]" />
                    <!-- Multiline text -->
                    <textarea v-else-if="field.type === 'multiline_string'"
                        :name="`ticket_custom_values[${ticket.id}][${fieldKey}]`"
                        :id="`ticket_custom_${ticket.id}_${fieldKey}`"
                        v-model="ticket.custom_values[fieldKey]"
                        :required="field.required"
                        rows="2"
                        class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"></textarea>
                    <!-- Yes/No switch -->
                    <div v-else-if="field.type === 'switch'" class="mt-1">
                        <select :name="`ticket_custom_values[${ticket.id}][${fieldKey}]`"
                            :id="`ticket_custom_${ticket.id}_${fieldKey}`"
                            v-model="ticket.custom_values[fieldKey]"
                            :required="field.required"
                            class="block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                            <option value="">{{ __('messages.please_select') }}</option>
                            <option value="Yes">{{ __('messages.yes') }}</option>
                            <option value="No">{{ __('messages.no') }}</option>
                        </select>
                    </div>
                    <!-- Date picker -->
                    <input v-else-if="field.type === 'date'" type="date"
                        :name="`ticket_custom_values[${ticket.id}][${fieldKey}]`"
                        :id="`ticket_custom_${ticket.id}_${fieldKey}`"
                        v-model="ticket.custom_values[fieldKey]"
                        :required="field.required"
                        class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]" />
                    <!-- Dropdown -->
                    <select v-else-if="field.type === 'dropdown'"
                        :name="`ticket_custom_values[${ticket.id}][${fieldKey}]`"
                        :id="`ticket_custom_${ticket.id}_${fieldKey}`"
                        v-model="ticket.custom_values[fieldKey]"
                        :required="field.required"
                        class="mt-1 block w-full text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                        <option value="">{{ __('messages.please_select') }}</option>
                        <option v-for="option in (field.options || '').split(',')" :key="option.trim()" :value="option.trim()">@{{ option.trim() }}</option>
                    </select>
                </div>
            </div>
        </div>

        <div class="mt-6 bg-gray-50 dark:bg-gray-700/50 rounded-lg p-4">
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">Total</span>
                <span class="text-xl font-bold text-gray-900 dark:text-gray-100">@{{ formatPrice(totalAmount) }}</span>
            </div>
        </div>

        <!-- Turnstile widget -->
        <div v-if="turnstileEnabled" class="mt-4">
            <div id="turnstile-checkout-widget"></div>
            <input type="hidden" name="cf-turnstile-response" :value="turnstileToken">
            <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
        </div>

        <div class="flex justify-center items-center py-4 gap-8">
            <x-brand-button
                type="submit"
                class="mt-4 text-lg px-6"
                x-bind:disabled="!hasSelectedTickets"
            >
                {{ strtoupper(__('messages.checkout')) }}
            </x-brand-button>
            
            <a href="{{ request()->fullUrlWithQuery(['tickets' => false]) }}" class="mt-4 px-6 py-3 text-lg font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-colors">
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

