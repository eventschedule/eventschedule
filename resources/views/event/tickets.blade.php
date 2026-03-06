<x-slot name="head">
  <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
  @if ($event->country_code_phone)
  <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
  <style {!! nonce_attr() !!}>
  .iti { display: block; }
  .iti input.iti__tel-input { padding-top: 0.5rem !important; padding-bottom: 0.5rem !important; font-size: 1rem !important; line-height: 1.5rem !important; height: auto !important; }
  .dark .iti { --iti-dropdown-bg: #1e1e1e; --iti-hover-color: #2d2d30; --iti-border-color: #2d2d30; --iti-dialcode-color: #9ca3af; --iti-arrow-color: #d1d5db; }
  .dark .iti__dropdown-content { color: #d1d5db; }
  .dark .iti__selected-dial-code { color: #d1d5db; }
  .dark .iti__search-input { background: #1e1e1e; color: #d1d5db; border-color: #2d2d30; }
  </style>
  <script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}" {!! nonce_attr() !!}></script>
  @endif
  @if (\App\Utils\TurnstileUtils::isActiveForRequest())
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer {!! nonce_attr() !!}></script>
  @endif
  <script {!! nonce_attr() !!}>
    window.addEventListener('DOMContentLoaded', function() {
        const { createApp, ref } = Vue;

        const app = createApp({
            data() {
                return {
                    createAccount: @json((bool) old('create_account', false)),
                    tickets: @json($event->tickets->filter(fn($t) => !$t->isSalesEnded())->values()->map(function ($ticket) {
                        $data = $ticket->toData(request()->date);
                        $data['selectedQty'] = old('tickets')[$data['id']] ?? 0;
                        $data['custom_fields'] = $ticket->custom_fields ?? [];
                        $data['custom_values'] = (object) [];
                        $data['multiselect_values'] = (object) [];
                        return $data;
                    })),
                    eventCustomFields: @json($event->custom_fields ?? []),
                    eventCustomValues: @json(old('event_custom_values', [])),
                    eventMultiselectValues: {},
                    name: @json(old('name', auth()->check() ? auth()->user()->name : '')),
                    email: @json(old('email', auth()->check() ? auth()->user()->email : '')),
                    phone: @json(old('phone', '')),
                    askPhone: @json((bool) $event->ask_phone),
                    requirePhone: @json((bool) $event->require_phone),
                    countryCodePhone: @json((bool) $event->country_code_phone),
                    password: @json(old('password', '')),
                    totalTicketsMode: @json($event->total_tickets_mode ?? 'individual'),
                    turnstileEnabled: @json(\App\Utils\TurnstileUtils::isActiveForRequest()),
                    turnstileSiteKey: @json(\App\Utils\TurnstileUtils::getSiteKey()),
                    turnstileToken: '',
                    turnstileWidgetId: null,
                    promoCode: '',
                    promoCodeValid: false,
                    promoCodeMessage: '',
                    discountAmount: 0,
                    isValidatingPromo: false,
                    promoCodeExpanded: false,
                    isPaymentLinkMode: @json($event->payment_method === 'invoiceninja' && $event->user->invoiceninja_mode === 'payment_link'),
                    isSubmitting: false,
                    allSoldOut: @json($event->allTicketsSoldOut($date ?? request()->date)),
                    waitlistSubmitting: false,
                    waitlistMessage: '',
                    waitlistSuccess: false,
                    showPassword: false,
                    individualTickets: @json((bool) $event->individual_tickets),
                    guests: [],
                };
            },
            created() {
                // Initialize multiselect arrays for event-level fields
                Object.entries(this.eventCustomFields).forEach(([key, field]) => {
                    if (field.type === 'multiselect') {
                        this.eventMultiselectValues[key] = [];
                    }
                });
                this.tickets.forEach(ticket => {
                    if (! ticket.selectedQty) {
                        ticket.selectedQty = 0;
                    }
                    // Initialize multiselect arrays for ticket-level fields
                    if (ticket.custom_fields) {
                        Object.entries(ticket.custom_fields).forEach(([key, field]) => {
                            if (field.type === 'multiselect') {
                                ticket.multiselect_values[key] = [];
                            }
                        });
                    }
                });
                if (this.tickets.length === 1 && this.tickets[0].quantity > 0) {
                    this.tickets[0].selectedQty = 1;
                }
                this.rebuildGuests();
                const urlParams = new URLSearchParams(window.location.search);
                const promoParam = urlParams.get('promo');
                if (promoParam) {
                    this.promoCode = promoParam;
                    this.promoCodeExpanded = true;
                    this.$nextTick(() => {
                        if (this.tickets.some(t => t.selectedQty > 0)) {
                            this.applyPromoCode();
                        }
                    });
                }
            },
            mounted() {
                if (this.turnstileEnabled && this.turnstileSiteKey) {
                    const renderTurnstile = () => {
                        const checkTurnstile = () => {
                            if (typeof turnstile !== 'undefined') {
                                this.turnstileWidgetId = turnstile.render('#turnstile-checkout-widget', {
                                    sitekey: this.turnstileSiteKey,
                                    size: 'flexible',
                                    callback: (token) => {
                                        this.turnstileToken = token;
                                    },
                                });
                            } else {
                                setTimeout(checkTurnstile, 100);
                            }
                        };
                        checkTurnstile();
                    };
                    const el = document.getElementById('ticket-selector');
                    if (el && el.offsetParent !== null) {
                        renderTurnstile();
                    } else {
                        window.addEventListener('event-form-shown', () => renderTurnstile(), { once: true });
                    }
                }
                @if ($event->country_code_phone)
                const initIntlTel = () => {
                    const input = document.getElementById('phone_input');
                    const hidden = document.getElementById('phone_hidden');
                    if (!input || !hidden) return;
                    const iti = window.intlTelInput(input, {
                        utilsScript: '{{ asset('vendor/intl-tel-input/js/utils.js') }}',
                        initialCountry: '{{ strtolower($role->country_code ?? 'us') }}',
                        separateDialCode: true,
                        strictMode: true,
                        nationalMode: false,
                        autoPlaceholder: 'off',
                    });
                    const vm = this;
                    function updateHidden() {
                        var number = iti.getNumber();
                        hidden.value = number || '';
                        vm.phone = number || '';
                    }
                    input.addEventListener('change', updateHidden);
                    input.addEventListener('input', updateHidden);
                    input.addEventListener('countrychange', updateHidden);
                };
                const phoneEl = document.getElementById('phone_input');
                if (phoneEl && phoneEl.offsetParent !== null) {
                    initIntlTel();
                } else {
                    window.addEventListener('event-form-shown', () => this.$nextTick(() => initIntlTel()), { once: true });
                }
                @endif
            },
            computed: {
                subtotalAmount() {
                    return this.tickets.reduce((total, ticket) => {
                        return total + (ticket.price * ticket.selectedQty);
                    }, 0);
                },
                totalAmount() {
                    return Math.max(0, this.subtotalAmount - this.discountAmount);
                },
                hasSelectedTickets() {
                    if (this.isPaymentLinkMode) {
                        return this.name.trim() !== '' && this.email.trim() !== '';
                    }
                    if (!this.tickets.some(ticket => ticket.selectedQty > 0)) return false;
                    if (this.showGuestForms) {
                        return this.guests.every(g => g.name.trim() !== '' && g.email.trim() !== '' && (!this.requirePhone || g.phone.trim() !== ''));
                    }
                    if (this.requirePhone && this.phone.trim() === '') return false;
                    return this.name.trim() !== '' && this.email.trim() !== '';
                },
                showGuestForms() {
                    return this.individualTickets && this.totalSelectedTickets > 1;
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
                },
                ticketSelectionKey() {
                    return this.tickets.map(t => t.id + ':' + t.selectedQty).join(',');
                },
                isAllSoldOut() {
                    if (this.allSoldOut) return true;
                    return this.tickets.length > 0 && this.tickets.every(t => this.getAvailableQuantity(t) === 0);
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
                        alert(@json(__('messages.please_select_ticket')));
                        return;
                    }
                    if (this.turnstileEnabled && !this.turnstileToken) {
                        e.preventDefault();
                        alert(@json(__('messages.turnstile_verification_failed')));
                        return;
                    }
                    const url = new URL(window.location);
                    url.searchParams.set('tickets', 'true');
                    history.replaceState(null, '', url);
                    this.isSubmitting = true;
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
                },
                onTicketChange() {
                    this.updateTicketQuantities();
                    this.rebuildGuests();
                    if (this.promoCodeValid) {
                        this.applyPromoCode();
                    }
                },
                rebuildGuests() {
                    if (!this.individualTickets) return;
                    const newGuests = [];
                    this.tickets.forEach(ticket => {
                        for (let i = 0; i < ticket.selectedQty; i++) {
                            newGuests.push({ ticketId: ticket.id, ticketType: ticket.type });
                        }
                    });
                    // Preserve existing guest data where possible
                    for (let i = 0; i < newGuests.length; i++) {
                        if (i < this.guests.length) {
                            newGuests[i].name = this.guests[i].name;
                            newGuests[i].email = this.guests[i].email;
                            newGuests[i].phone = this.guests[i].phone;
                        } else {
                            newGuests[i].name = i === 0 ? this.name : '';
                            newGuests[i].email = i === 0 ? this.email : '';
                            newGuests[i].phone = i === 0 ? this.phone : '';
                        }
                    }
                    this.guests = newGuests;
                    // Sync guest[0] back to top-level fields when switching to single form
                    if (this.guests.length > 0) {
                        this.name = this.guests[0].name || this.name;
                        this.email = this.guests[0].email || this.email;
                        this.phone = this.guests[0].phone || this.phone;
                    }
                },
                applyPromoCode() {
                    if (!this.promoCode.trim() || this.isValidatingPromo) return;

                    const ticketData = {};
                    this.tickets.forEach(ticket => {
                        if (ticket.selectedQty > 0) {
                            ticketData[ticket.id] = ticket.selectedQty;
                        }
                    });

                    if (Object.keys(ticketData).length === 0) {
                        this.promoCodeMessage = @json(__('messages.please_select_ticket'));
                        this.promoCodeValid = false;
                        return;
                    }

                    this.isValidatingPromo = true;
                    this.promoCodeMessage = '';

                    fetch(@json(route('promo_code.validate', ['subdomain' => $subdomain])), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            event_id: @json(\App\Utils\UrlUtils::encodeId($event->id)),
                            code: this.promoCode.trim(),
                            tickets: ticketData,
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.isValidatingPromo = false;
                        this.promoCodeValid = data.valid;
                        this.promoCodeMessage = data.message;
                        if (data.valid) {
                            this.discountAmount = parseFloat(data.discount_amount);
                        } else {
                            this.discountAmount = 0;
                        }
                    })
                    .catch(() => {
                        this.isValidatingPromo = false;
                        this.promoCodeMessage = @json(__('messages.error'));
                        this.promoCodeValid = false;
                        this.discountAmount = 0;
                    });
                },
                removePromoCode() {
                    this.promoCodeValid = false;
                    this.promoCodeMessage = '';
                    this.discountAmount = 0;
                },
                hideForm() {
                    window.dispatchEvent(new CustomEvent('hide-event-form'));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                joinWaitlist() {
                    if (!this.name.trim() || !this.email.trim() || this.waitlistSubmitting) return;
                    this.waitlistSubmitting = true;
                    this.waitlistMessage = '';

                    fetch(@json(route('waitlist.join', ['subdomain' => $subdomain])), {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                            'Accept': 'application/json',
                        },
                        body: JSON.stringify({
                            event_id: @json(\App\Utils\UrlUtils::encodeId($event->id)),
                            event_date: @json($date ?? request()->date),
                            name: this.name.trim(),
                            email: this.email.trim(),
                        }),
                    })
                    .then(response => response.json())
                    .then(data => {
                        this.waitlistSubmitting = false;
                        this.waitlistMessage = data.message;
                        this.waitlistSuccess = data.success;
                    })
                    .catch(() => {
                        this.waitlistSubmitting = false;
                        this.waitlistMessage = @json(__('messages.error'));
                        this.waitlistSuccess = false;
                    });
                }
            },
            watch: {
                tickets: {
                    handler() {
                        this.updateTicketQuantities();
                    },
                    deep: true
                },
                ticketSelectionKey() {
                    if (this.promoCodeValid) {
                        this.applyPromoCode();
                    }
                },
                totalSelectedTickets(newVal, oldVal) {
                    if (newVal > 0 && oldVal === 0 && this.promoCode && !this.promoCodeValid && !this.isValidatingPromo) {
                        const urlParams = new URLSearchParams(window.location.search);
                        if (urlParams.get('promo')) {
                            this.applyPromoCode();
                        }
                    }
                }
            },
        }).mount('#ticket-selector');
    });
</script>
</x-slot>

<div id="ticket-selector">
    <form action="{{ route('event.checkout', ['subdomain' => $subdomain]) }}" method="post" v-on:submit="validateForm"
        @if (request()->embed && in_array($event->payment_method, ['stripe', 'invoiceninja', 'payment_url'])) target="_top" @endif>
        @csrf
        <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">
        <input type="hidden" name="event_date" value="{{ $date }}">
        <input type="hidden" name="subdomain" value="{{ $subdomain }}">
        <div class="hidden"><input type="text" name="website" autocomplete="off" tabindex="-1"></div>
        @if (request()->embed)
        <input type="hidden" name="embed" value="true">
        @endif

        @if (session('error'))
        <div class="mb-6 p-3 rounded-lg text-sm bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
            {{ session('error') }}
        </div>
        @endif

        @if ($errors->any() && !$errors->has('name') && !$errors->has('email') && !$errors->has('phone') && !$errors->has('password') && !$errors->has('cf-turnstile-response'))
        <div class="mb-6 p-3 rounded-lg text-sm bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300 border border-red-200 dark:border-red-800">
            {{ __('messages.error') }}
        </div>
        @endif

        <div v-if="!showGuestForms">
        <div class="mb-6">
            <label for="name" class="text-gray-900 dark:text-gray-100">{{ __('messages.name') . ' *' }}</label>
            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                v-model="name" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mb-6">
            <label for="email" class="text-gray-900 dark:text-gray-100">{{ __('messages.email') . ' *' }}</label>
            <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                v-model="email" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (! auth()->check() && config('app.hosted') && ! request()->embed)
                <div class="mt-6">
                    <div class="flex items-center">
                        <input id="create_account" name="create_account" type="checkbox"
                            v-model="createAccount" value="1"
                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                        <label for="create_account" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                            {{ __('messages.create_account') }}
                        </label>
                    </div>

                    <div class="mt-6" v-if="createAccount">
                        <label for="password" class="text-gray-900 dark:text-gray-100">{{ __('messages.password') . ' *' }}</label>
                        <div class="relative mt-1">
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" class="block w-full pe-10 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                                v-model="password" required autocomplete="new-password" />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
                            >
                                <svg v-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <x-input-error class="mt-2" :messages="$errors->get('password')" />
                    </div>
                </div>
            @endif
        </div>

        <div class="mb-6" v-if="askPhone">
            <label for="phone_input" class="block text-gray-900 dark:text-gray-100">{{ __('messages.phone_number') }}<span v-if="requirePhone"> *</span></label>
            @if ($event->country_code_phone)
            <input type="hidden" name="phone" id="phone_hidden" v-model="phone">
            <input type="tel" id="phone_input" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                :required="requirePhone" autocomplete="tel" />
            @else
            <input type="tel" name="phone" id="phone_input" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                v-model="phone" :required="requirePhone" autocomplete="tel" />
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>
        </div>

        <!-- Per-Guest Forms (Individual Tickets) -->
        <div v-if="showGuestForms">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.guest_information') }}</h3>
            <div v-for="(guest, gIndex) in guests" :key="gIndex" class="mb-4 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm border border-gray-200 dark:border-gray-600">
                <h4 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">
                    {{ __('messages.guest_n', ['n' => '']) }}@{{ gIndex + 1 }} (@{{ guest.ticketType }})
                    <span v-if="gIndex === 0" class="text-xs text-gray-500 dark:text-gray-400"> - {{ __('messages.you') }}</span>
                </h4>
                <div class="mb-3">
                    <label :for="'guest_name_' + gIndex" class="text-sm text-gray-900 dark:text-gray-100">{{ __('messages.name') }} *</label>
                    <input type="text" :id="'guest_name_' + gIndex" v-model="guest.name" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm" />
                    <input type="hidden" :name="'guests[' + gIndex + '][name]'" :value="guest.name">
                    <input type="hidden" :name="'guests[' + gIndex + '][ticket_id]'" :value="guest.ticketId">
                </div>
                <div class="mb-3">
                    <label :for="'guest_email_' + gIndex" class="text-sm text-gray-900 dark:text-gray-100">{{ __('messages.email') }} *</label>
                    <input type="email" :id="'guest_email_' + gIndex" v-model="guest.email" required
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm" />
                    <input type="hidden" :name="'guests[' + gIndex + '][email]'" :value="guest.email">
                </div>
                <div class="mb-3" v-if="askPhone">
                    <label :for="'guest_phone_' + gIndex" class="text-sm text-gray-900 dark:text-gray-100">{{ __('messages.phone_number') }}<span v-if="requirePhone"> *</span></label>
                    <input type="tel" :id="'guest_phone_' + gIndex" v-model="guest.phone" :required="requirePhone"
                        class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm" />
                    <input type="hidden" :name="'guests[' + gIndex + '][phone]'" :value="guest.phone">
                </div>

                @if (! auth()->check() && config('app.hosted') && ! request()->embed)
                <div v-if="gIndex === 0" class="mt-3">
                    <div class="flex items-center">
                        <input :id="'create_account_guest'" name="create_account" type="checkbox"
                            v-model="createAccount" value="1"
                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                        <label for="create_account_guest" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                            {{ __('messages.create_account') }}
                        </label>
                    </div>
                    <div class="mt-3" v-if="createAccount">
                        <label for="password_guest" class="text-sm text-gray-900 dark:text-gray-100">{{ __('messages.password') }} *</label>
                        <div class="relative mt-1">
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password_guest" class="block w-full pe-10 text-sm rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                                v-model="password" required autocomplete="new-password" />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                                <svg v-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg v-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                            </button>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <!-- Sync guest 0 to top-level name/email for backend compatibility -->
            <input type="hidden" name="name" :value="guests.length > 0 ? guests[0].name : name">
            <input type="hidden" name="email" :value="guests.length > 0 ? guests[0].email : email">
            <input type="hidden" name="phone" :value="guests.length > 0 ? guests[0].phone : phone">
        </div>

        <!-- Event-level Custom Fields -->
        <div v-if="hasEventCustomFields" class="mb-6">
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
                    dir="auto"
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
                <!-- Multi-select -->
                <div v-else-if="field.type === 'multiselect'" class="mt-1 space-y-1">
                    <input type="hidden" :name="`event_custom_values[${fieldKey}]`" :value="(eventMultiselectValues[fieldKey] || []).join(', ')">
                    <label v-for="option in (field.options || '').split(',')" :key="option.trim()" class="flex items-center gap-2 text-gray-900 dark:text-gray-100">
                        <input type="checkbox" :value="option.trim()"
                            v-model="eventMultiselectValues[fieldKey]"
                            class="h-4 w-4 border-gray-300 rounded"
                            style="accent-color: {{ $accentColor }}" />
                        @{{ option.trim() }}
                    </label>
                </div>
            </div>
        </div>

        <div v-if="!isPaymentLinkMode && !isAllSoldOut" v-for="(ticket, index) in tickets" :key="ticket.id" class="mb-6 bg-white dark:bg-gray-700 rounded-lg p-4 shadow-sm border-s-4" style="border-inline-start-color: {{ $accentColor }}">
            <div class="flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100">@{{ ticket.type }}</h3>
                    <p v-if="ticket.description" class="text-sm text-gray-600 dark:text-gray-400" v-html="ticket.description"></p>
                    <p :class="{'text-lg': tickets.length === 1, 'text-sm': tickets.length > 1}" class="font-medium text-gray-900 dark:text-gray-100"><template v-if="ticket.price == 0">{{ __('messages.free') }}</template><template v-else>@{{ formatPrice(ticket.price) }}</template></p>
                </div>
                <div>
                    <p v-if="getAvailableQuantity(ticket) === 0" class="text-lg font-medium text-gray-500 dark:text-gray-400">{{ __('messages.sold_out') }}</p>
                    <p v-else>
                    <select
                        v-model="ticket.selectedQty"
                        @change="onTicketChange"
                        class="block w-24 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-center font-medium"
                        :name="`tickets[${ticket.id}]`" :id="`ticket-${index}`"
                    >
                        <option :value="0">0</option>
                        <template v-for="n in getAvailableQuantity(ticket)">
                            <option :value="n" :selected="ticket.selectedQty === n">@{{ n }}</option>
                            </template>
                        </select>
                    </p>
                </div>
            </div>

            <!-- Ticket-level Custom Fields (shown when ticket is selected) -->
            <div v-if="ticket.selectedQty > 0 && ticket.custom_fields && Object.keys(ticket.custom_fields).length > 0" class="mt-4 ps-4 border-s-2 border-gray-200 dark:border-gray-600">
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
                        dir="auto"
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
                    <!-- Multi-select -->
                    <div v-else-if="field.type === 'multiselect'" class="mt-1 space-y-1">
                        <input type="hidden" :name="`ticket_custom_values[${ticket.id}][${fieldKey}]`" :value="(ticket.multiselect_values && ticket.multiselect_values[fieldKey] || []).join(', ')">
                        <label v-for="option in (field.options || '').split(',')" :key="option.trim()" class="flex items-center gap-2 text-sm text-gray-900 dark:text-gray-100">
                            <input type="checkbox" :value="option.trim()"
                                v-model="ticket.multiselect_values[fieldKey]"
                                class="h-4 w-4 border-gray-300 rounded"
                            style="accent-color: {{ $accentColor }}" />
                            @{{ option.trim() }}
                        </label>
                    </div>
                </div>
            </div>
        </div>

        <!-- Promo Code -->
        @if($event->hasActivePromoCodes())
        <div v-if="!isPaymentLinkMode && !isAllSoldOut" class="mb-6">
            <button type="button" v-if="!promoCodeExpanded" @click="promoCodeExpanded = true" class="text-sm text-[#4E81FA] hover:text-blue-700 dark:hover:text-blue-300 font-medium">
                {{ __('messages.have_a_promo_code') }}
            </button>
            <div v-if="promoCodeExpanded" class="bg-white dark:bg-gray-700/50 rounded-lg p-4">
                <label class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.promo_code') }}</label>
                <div class="flex gap-2 mt-1">
                    <input type="text" v-model="promoCode" :disabled="promoCodeValid"
                        class="flex-1 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA] text-sm"
                        :class="{'opacity-50': promoCodeValid}"
                        placeholder="{{ __('messages.enter_promo_code') }}" />
                    <button type="button" v-if="!promoCodeValid" @click="applyPromoCode" :disabled="isValidatingPromo || !promoCode.trim()"
                        class="px-4 py-2 bg-[#4E81FA] text-white text-sm font-medium rounded-md hover:bg-blue-600 disabled:opacity-50 disabled:cursor-not-allowed transition-colors">
                        <span v-if="isValidatingPromo">...</span>
                        <span v-else>{{ __('messages.apply') }}</span>
                    </button>
                    <button type="button" v-else @click="removePromoCode"
                        class="px-4 py-2 bg-gray-200 dark:bg-gray-600 text-gray-700 dark:text-gray-300 text-sm font-medium rounded-md hover:bg-gray-300 dark:hover:bg-gray-500 transition-colors">
                        &times;
                    </button>
                </div>
                <p v-if="promoCodeMessage" class="mt-2 text-sm" :class="promoCodeValid ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'">
                    @{{ promoCodeMessage }}
                </p>
            </div>
            <input type="hidden" name="promo_code" :value="promoCodeValid ? promoCode.trim() : ''">
        </div>
        @endif

        <!-- Total -->
        <div v-if="!isPaymentLinkMode && !isAllSoldOut" class="mb-6 bg-white dark:bg-gray-700/50 rounded-lg p-4">
            <div v-if="discountAmount > 0">
                <div class="flex justify-between items-center mb-1">
                    <span class="text-gray-600 dark:text-gray-400 text-sm">@lang('messages.subtotal')</span>
                    <span class="text-sm text-gray-500 dark:text-gray-400 line-through">@{{ formatPrice(subtotalAmount) }}</span>
                </div>
                <div class="flex justify-between items-center mb-1">
                    <span class="text-green-600 dark:text-green-400 text-sm">@lang('messages.discount')</span>
                    <span class="text-sm text-green-600 dark:text-green-400">-@{{ formatPrice(discountAmount) }}</span>
                </div>
            </div>
            <div class="flex justify-between items-center">
                <span class="text-gray-600 dark:text-gray-400">@lang('messages.total')</span>
                <span class="text-xl font-bold text-gray-900 dark:text-gray-100">@{{ formatPrice(totalAmount) }}</span>
            </div>
        </div>

        <!-- Turnstile widget -->
        <div v-if="turnstileEnabled && !isAllSoldOut" class="mt-4">
            <div id="turnstile-checkout-widget"></div>
            <input type="hidden" name="cf-turnstile-response" :value="turnstileToken">
            <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
        </div>

        <div v-if="isAllSoldOut" class="mt-6">
            <div v-if="waitlistMessage" class="mb-4 p-4 rounded-lg text-sm" :class="waitlistSuccess ? 'bg-green-50 dark:bg-green-900/30 text-green-700 dark:text-green-300' : 'bg-red-50 dark:bg-red-900/30 text-red-700 dark:text-red-300'">
                @{{ waitlistMessage }}
            </div>
            <div v-if="!waitlistSuccess" class="flex justify-end items-center pt-2 gap-8">
                @if (! request()->embed)
                <button type="button" @click="hideForm" class="mt-4 px-6 py-3 text-lg font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
                    {{ strtoupper(__('messages.cancel')) }}
                </button>
                @endif
                <button type="button" @click="joinWaitlist"
                    :disabled="!name.trim() || !email.trim() || waitlistSubmitting"
                    class="mt-4 text-lg px-6 inline-flex items-center rounded-md border border-transparent py-3 font-semibold shadow-sm focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 hover:scale-105"
                    style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
                    <span v-if="waitlistSubmitting">{{ strtoupper(__('messages.processing')) }}</span>
                    <span v-else>{{ strtoupper(__('messages.join_waitlist')) }}</span>
                </button>
            </div>
            @if (! request()->embed)
            <div v-else class="flex justify-end pt-2">
                <button type="button" @click="hideForm" class="mt-4 px-6 py-3 text-lg font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
                    {{ strtoupper(__('messages.back')) }}
                </button>
            </div>
            @endif
        </div>

        <div v-if="!isAllSoldOut" class="flex justify-end items-center pt-2 gap-8">
            @if (! request()->embed)
            <button type="button" @click="hideForm" class="mt-4 px-6 py-3 text-lg font-semibold text-gray-600 dark:text-gray-400 hover:text-gray-800 dark:hover:text-gray-200 rounded-md hover:bg-gray-100 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105">
                {{ strtoupper(__('messages.cancel')) }}
            </button>
            @endif

            <button type="submit"
                v-bind:disabled="!hasSelectedTickets || isSubmitting"
                class="mt-4 inline-flex items-center justify-center px-6 py-3 border border-transparent rounded-md font-semibold text-lg shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:bg-gray-400 disabled:cursor-not-allowed disabled:opacity-50 disabled:hover:scale-100 disabled:hover:shadow-sm"
                style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
                <span v-if="isSubmitting">{{ strtoupper(__('messages.processing')) }}</span>
                <span v-else>{{ strtoupper(__('messages.checkout')) }}</span>
            </button>
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

