<x-slot name="head">
  <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
  @if ($event->country_code_phone)
  <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
  <style {!! nonce_attr() !!}>
  .iti { display: block; }
  .dark .iti { --iti-dropdown-bg: rgb(var(--ap-bg)); --iti-hover-color: rgb(var(--ap-border)); --iti-border-color: rgb(var(--ap-border)); --iti-dialcode-color: rgb(var(--ap-ink-3)); --iti-arrow-color: rgb(var(--ap-ink-2)); }
  .dark .iti__dropdown-content { color: rgb(var(--ap-ink-2)); }
  .dark .iti__selected-dial-code { color: rgb(var(--ap-ink-2)); }
  .dark .iti__search-input { background: rgb(var(--ap-bg)); color: rgb(var(--ap-ink-2)); border-color: rgb(var(--ap-border)); }
  </style>
  <script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}" {!! nonce_attr() !!}></script>
  @endif
  @if (\App\Utils\TurnstileUtils::isActiveForRequest())
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer {!! nonce_attr() !!}></script>
  @endif
  <script {!! nonce_attr() !!}>
    window.addEventListener('DOMContentLoaded', function() {
        if (typeof Vue === 'undefined') return;
        const { createApp, ref } = Vue;

        const app = createApp({
            data() {
                return {
                    hasError: @json(session('error') || $errors->any()),
                    createAccount: @json((bool) old('create_account', false)),
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
                    turnstileEnabled: @json(\App\Utils\TurnstileUtils::isActiveForRequest()),
                    turnstileSiteKey: @json(\App\Utils\TurnstileUtils::getSiteKey()),
                    turnstileToken: '',
                    turnstileWidgetId: null,
                    problem: '',
                    isSubmitting: false,
                    rsvpFull: @json($event->isRsvpFull($date ?? $event->saleEventDateFromStartsAt())),
                    showPassword: false,
                    individualTickets: @json((bool) $event->individual_tickets),
                    rsvpQuantity: 1,
                    guests: [],
                    waitlistSubmitting: false,
                    waitlistMessage: '',
                    waitlistSuccess: false,
                    website: '',
                };
            },
            created() {
                Object.entries(this.eventCustomFields).forEach(([key, field]) => {
                    if (field.type === 'multiselect') {
                        this.eventMultiselectValues[key] = [];
                    }
                });
                if (this.hasError) {
                    this.restoreFormState();
                }
            },
            mounted() {
                if (this.turnstileEnabled && this.turnstileSiteKey) {
                    const renderTurnstile = () => {
                        const checkTurnstile = () => {
                            if (typeof turnstile !== 'undefined') {
                                this.turnstileWidgetId = turnstile.render('#turnstile-rsvp-widget', {
                                    sitekey: this.turnstileSiteKey,
                                    size: 'flexible',
                                    'retry': 'auto',
                                    'refresh-expired': 'auto',
                                    callback: (token) => {
                                        this.turnstileToken = token;
                                        this.problem = '';
                                    },
                                    'error-callback': () => {
                                        this.turnstileToken = '';
                                        if (this.turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
                                            turnstile.reset(this.turnstileWidgetId);
                                        }
                                        return true;
                                    },
                                });
                            } else {
                                setTimeout(checkTurnstile, 100);
                            }
                        };
                        checkTurnstile();
                    };
                    const el = document.getElementById('rsvp-form');
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
                storageKey() {
                    return 'rsvp_form_' + @json(\App\Utils\UrlUtils::encodeId($event->id));
                },
                hasEventCustomFields() {
                    return this.eventCustomFields && Object.keys(this.eventCustomFields).length > 0;
                },
                showGuestForms() {
                    return this.individualTickets && this.rsvpQuantity > 1;
                },
                maxRsvpQuantity() {
                    const remaining = @json($event->rsvp_limit ? $event->rsvpRemaining($date ?? $event->saleEventDateFromStartsAt()) : null);
                    const max = remaining !== null ? Math.min(20, remaining) : 20;
                    return Math.max(1, max);
                },
            },
            watch: {
                rsvpQuantity(val) {
                    this.rebuildGuests();
                },
            },
            methods: {
                hideForm() {
                    window.dispatchEvent(new CustomEvent('hide-event-form'));
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                rebuildGuests() {
                    const qty = parseInt(this.rsvpQuantity) || 1;
                    const newGuests = [];
                    for (let i = 0; i < qty; i++) {
                        if (i < this.guests.length) {
                            newGuests.push({ name: this.guests[i].name, email: this.guests[i].email, phone: this.guests[i].phone });
                        } else {
                            newGuests.push({ name: i === 0 ? this.name : '', email: i === 0 ? this.email : '', phone: i === 0 ? this.phone : '' });
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
                validateForm(e) {
                    if (this.turnstileEnabled && !this.turnstileToken) {
                        e.preventDefault();
                        this.problem = @json(__('messages.turnstile_verification_failed'));
                        this.$nextTick(() => { const el = document.getElementById('rsvp-problem'); if (el) { el.scrollIntoView({ behavior: 'smooth', block: 'center' }); } });
                        return;
                    }
                    this.saveFormState();
                    const url = new URL(window.location);
                    url.searchParams.set('rsvp', 'true');
                    history.replaceState(null, '', url);
                    this.isSubmitting = true;
                },
                saveFormState() {
                    try {
                        const state = {
                            name: this.name,
                            email: this.email,
                            phone: this.phone,
                            rsvpQuantity: this.rsvpQuantity,
                            guests: this.guests.map(g => ({
                                name: g.name,
                                email: g.email,
                                phone: g.phone,
                            })),
                            eventCustomValues: this.eventCustomValues,
                            eventMultiselectValues: this.eventMultiselectValues,
                            createAccount: this.createAccount,
                            {{-- No password: this is written to sessionStorage, where any script on
                                 the page can read it and where it used to outlive a successful
                                 sign-up. It is the one field a person expects to retype. --}}
                        };
                        sessionStorage.setItem(this.storageKey, JSON.stringify(state));
                    } catch (e) {}
                },
                restoreFormState() {
                    try {
                        const raw = sessionStorage.getItem(this.storageKey);
                        if (!raw) return;
                        const state = JSON.parse(raw);
                        sessionStorage.removeItem(this.storageKey);

                        if (state.name) this.name = state.name;
                        if (state.email) this.email = state.email;
                        if (state.phone) this.phone = state.phone;
                        if (state.createAccount !== undefined) this.createAccount = state.createAccount;

                        if (state.eventCustomValues) {
                            this.eventCustomValues = state.eventCustomValues;
                        }
                        if (state.eventMultiselectValues) {
                            this.eventMultiselectValues = state.eventMultiselectValues;
                        }

                        if (state.rsvpQuantity) {
                            this.rsvpQuantity = Math.min(state.rsvpQuantity, this.maxRsvpQuantity);
                        }

                        this.rebuildGuests();

                        if (state.guests && state.guests.length) {
                            state.guests.forEach((saved, i) => {
                                if (i < this.guests.length) {
                                    this.guests[i].name = saved.name || '';
                                    this.guests[i].email = saved.email || '';
                                    this.guests[i].phone = saved.phone || '';
                                }
                            });
                        }

                        if (this.guests.length > 0) {
                            this.name = this.guests[0].name || this.name;
                            this.email = this.guests[0].email || this.email;
                            this.phone = this.guests[0].phone || this.phone;
                        }
                    } catch (e) {}
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
                            event_date: @json($date ?? $event->saleEventDateFromStartsAt()),
                            name: this.name.trim(),
                            email: this.email.trim(),
                            website: this.website,
                            lang: @json(isset($role) ? $role->displayLanguageCode() : null),
                        }),
                    })
                    .then(response => {
                        if (!response.ok) throw new Error('Request failed');
                        return response.json();
                    })
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
                },
            },
        }).mount('#rsvp-form[data-vue-root]');
    });
</script>
</x-slot>

{{-- The form's look on the event page's card (partials/guest-stage-styles): fields in the stage's
     colours, the kit's buttons. Outside the Vue mount, which would drop a style element. --}}
<style {!! nonce_attr() !!}>
    #rsvp-form .gk-rsvp-in { min-height: 3.125rem; border: 1px solid var(--gk-line); border-radius: .75rem; background-color: var(--gk-stage); color: var(--gk-ink); box-shadow: none; }
    #rsvp-form .gk-rsvp-in::placeholder { color: var(--gk-ink-3); }
    #rsvp-form .gk-rsvp-in:focus { outline: none; border-color: var(--es-accent-readable); box-shadow: 0 0 0 3px color-mix(in srgb, var(--es-accent-readable) 24%, transparent); }
    #rsvp-form .gk-rsvp-lb { font-size: .875rem; font-weight: 500; color: var(--gk-ink-2); }
    #rsvp-form .gk-rsvp-ink { color: var(--gk-ink); }
    #rsvp-form .gk-rsvp-say { color: var(--gk-ink-2); }
    #rsvp-form .gk-rsvp-mute { color: var(--gk-ink-3); }
    #rsvp-form .gk-rsvp-guest { padding: 1rem; border: 1px solid var(--gk-line); border-radius: 1rem; background: var(--gk-stage); }
    #rsvp-form .gk-rsvp-guest .gk-rsvp-in { background-color: var(--gk-stage-raised); }
    #rsvp-form input[type="checkbox"] { border-color: color-mix(in srgb, var(--gk-ink) 35%, transparent); background-color: var(--gk-stage); color: var(--es-accent-readable); }
    #rsvp-form input[type="checkbox"]:checked { border-color: transparent; background-color: var(--es-accent-readable); }
    #rsvp-form .gk-rsvp-actions { display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-end; gap: .75rem; padding-top: .5rem; }
    #rsvp-form .gk-rsvp-actions .gk-cta-main { flex: none; }
    #rsvp-form .gk-btn[disabled] { opacity: .5; cursor: not-allowed; }
</style>

<div id="rsvp-form" data-vue-root>
    @if ($event->isRsvpFull($date ?? $event->saleEventDateFromStartsAt()))
        <div>
            <input type="hidden" name="_token" value="{{ csrf_token() }}">
            {{-- The waitlist and the RSVP form are opposite branches of this @if, so the
                 honeypot in the form below never renders here. joinWaitlist() posts by
                 fetch, so this one is bound into Vue rather than submitted with a form. --}}
            <x-honeypot vmodel="website" />
            <div v-if="!waitlistSuccess">
                {{-- Said before the fields: the button that led here reads "Join waitlist", and
                     nothing on the form used to say why there was a waitlist at all. --}}
                <p class="mb-6 gk-note gk-note-warn font-semibold" role="status">{{ __('messages.registration_full') }}</p>
                <div class="mb-6">
                    <label for="waitlist_name" class="gk-rsvp-lb">{{ __('messages.name') . ' *' }}</label>
                    <input type="text" id="waitlist_name" class="mt-1 block w-full gk-rsvp-in"
                        v-model="name" required autocomplete="name" />
                </div>
                <div class="mb-6">
                    <label for="waitlist_email" class="gk-rsvp-lb">{{ __('messages.email') . ' *' }}</label>
                    <input type="email" id="waitlist_email" class="mt-1 block w-full gk-rsvp-in"
                        v-model="email" required autocomplete="email" />
                </div>
            </div>

            <div v-if="waitlistMessage" class="mb-4" :class="waitlistSuccess ? 'gk-note gk-note-ok' : 'gk-note gk-note-bad'">
                @{{ waitlistMessage }}
            </div>
            <div v-if="!waitlistSuccess" class="gk-rsvp-actions">
                @if (! request()->embed)
                <button type="button"
                    @click="hideForm"
                    class="gk-btn gk-cta-alt">
                    {{ __('messages.cancel') }}
                </button>
                @endif
                <button type="button" @click="joinWaitlist"
                    :disabled="!name.trim() || !email.trim() || waitlistSubmitting"
                    class="gk-btn gk-btn-primary gk-cta-main">
                    <span v-if="waitlistSubmitting">{{ __('messages.processing') }}</span>
                    <span v-else>{{ __('messages.join_waitlist') }}</span>
                </button>
            </div>
            @if (! request()->embed)
            <div v-else class="gk-rsvp-actions">
                <button type="button" @click="hideForm" class="gk-btn gk-cta-alt">
                    {{ __('messages.back') }}
                </button>
            </div>
            @endif
        </div>
    @else
    <form action="{{ route('event.rsvp', ['subdomain' => $subdomain]) }}" method="post" v-on:submit="validateForm">
        @csrf
        <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">
        <input type="hidden" name="event_date" value="{{ $date ?? $event->saleEventDateFromStartsAt() }}">
        <input type="hidden" name="subdomain" value="{{ $subdomain }}">
        @if ($promoterVia = \App\Services\PromoterLinks::codeForForm(request(), $event))
        <input type="hidden" name="via" value="{{ $promoterVia }}">
        @endif
        <x-honeypot />
        @if (request()->embed)
        <input type="hidden" name="embed" value="true">
        @endif
        {{-- The language the guest is reading this page in, so their ticket mails follow it
             (Sale::languageFromRequest() takes only this schedule's two languages). --}}
        @isset($role)
        <input type="hidden" name="lang" value="{{ $role->displayLanguageCode() }}">
        @endisset

        @if ($event->rsvp_limit)
        @php $remaining = $event->rsvpRemaining($date ?? $event->saleEventDateFromStartsAt()); @endphp
        <div class="mb-6 text-sm gk-rsvp-mute">
            {{ __('messages.spots_remaining', ['count' => $remaining]) }}
        </div>
        @endif

        @if ($event->individual_tickets)
        <div class="mb-6" v-if="maxRsvpQuantity > 1">
            <label for="rsvp_quantity" class="gk-rsvp-lb">{{ __('messages.number_of_guests') }}</label>
            <select id="rsvp_quantity" name="rsvp_quantity" v-model="rsvpQuantity"
                class="mt-1 block w-32 gk-rsvp-in">
                <template v-for="n in maxRsvpQuantity">
                    <option :value="n">@{{ n }}</option>
                </template>
            </select>
        </div>
        @endif

        <div v-if="!showGuestForms">
        <div class="mb-6">
            <label for="name" class="gk-rsvp-lb">{{ __('messages.name') . ' *' }}</label>
            <input type="text" name="name" id="name" class="mt-1 block w-full gk-rsvp-in"
                v-model="name" required autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mb-6">
            <label for="email" class="gk-rsvp-lb">{{ __('messages.email') . ' *' }}</label>
            <input type="email" name="email" id="email" class="mt-1 block w-full gk-rsvp-in"
                v-model="email" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (! auth()->check() && config('app.hosted') && ! request()->embed)
                <div class="mt-6">
                    <div class="flex items-center">
                        <input id="create_account" name="create_account" type="checkbox"
                            v-model="createAccount" value="1"
                            class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 dark:border-gray-600 rounded">
                        <label for="create_account" class="ms-3 block text-sm font-medium leading-6 gk-rsvp-ink">
                            {{ __('messages.create_account') }}
                        </label>
                    </div>

                    <div class="mt-6" v-if="createAccount">
                        <label for="password" class="gk-rsvp-lb">{{ __('messages.password') . ' *' }}</label>
                        <div class="relative mt-1">
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password" class="block w-full pe-10 gk-rsvp-in"
                                v-model="password" required autocomplete="new-password" />
                            <button
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 end-0 flex items-center pe-3 gk-rsvp-mute"
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

                        <div class="mt-3">
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="terms" name="terms" type="checkbox" required
                                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                </div>
                                <div class="ms-3 text-sm leading-6">
                                    <label for="terms" class="font-medium gk-rsvp-ink">
                                        {!! str_replace([':terms', ':privacy'], [
                                            '<a href="' . policy_url('terms') . '" target="_blank" class="gk-link"> ' . __('messages.terms_of_service') . '</a>',
                                            '<a href="' . policy_url('privacy') . '" target="_blank" class="gk-link">' . __('messages.privacy_policy') . '</a>'
                                        ], __('messages.i_accept_the_terms_and_privacy')) !!}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Audience opt-in. Guest buyers are the platform's largest uncaptured audience: the
                 follower row on checkout only fires when a signed-out visitor also opens an
                 account, so everyone else is captured at zero.

                 UNCHECKED by default. GDPR Art. 4(11) wants an affirmative act, and a pre-ticked
                 box on a shared platform mailer damages deliverability for every other schedule.

                 No v-model: this form submits natively, and a plain named checkbox is submitted
                 by the browser without Vue needing to know about it. --}}
            {{-- Rendered for signed-in buyers too. captureAudienceOptIn()'s own docblock counts
                 "every signed-in buyer" among the uncaptured, and holding an account is not the
                 same as having asked this schedule for anything - most signed-in buyers on the
                 platform have never followed the schedule they are buying from.

                 STILL UNCHECKED. Showing the box to more people is a visibility fix; pre-ticking it
                 would be a consent change, and GDPR Art. 4(11) wants an affirmative act. See the
                 comment above for why a pre-ticked box on the shared platform mailer would also
                 damage deliverability for every other schedule. --}}
            @if (! request()->embed && $event->creatorRole)
                <div class="mt-6 flex items-start">
                    <input id="audience_opt_in" name="audience_opt_in" type="checkbox" value="1"
                        class="mt-1 h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 dark:border-gray-600 rounded">
                    {{-- v-pre: the schedule name is user-controlled and this label sits inside a
                         Vue mount (#rsvp-form / #ticket-selector), so without it a name like
                         "{{constructor.constructor('...')()}}" is compiled as a template and runs.
                         Same guard as the custom-content block further down this file. --}}
                    <label for="audience_opt_in" v-pre class="ms-3 block text-sm gk-rsvp-ink">
                        {{ __('messages.audience_opt_in_label', ['schedule' => $event->creatorRole->name]) }}
                    </label>
                </div>
            @endif
        </div>

        <div class="mb-6" v-if="askPhone">
            <label for="phone_input" class="block gk-rsvp-lb">{{ __('messages.phone_number') }}<span v-if="requirePhone"> *</span></label>
            @if ($event->country_code_phone)
            <input type="hidden" name="phone" id="phone_hidden" v-model="phone">
            <input type="tel" id="phone_input" class="mt-1 block w-full gk-rsvp-in"
                :required="requirePhone" autocomplete="tel" />
            @else
            <input type="tel" name="phone" id="phone_input" class="mt-1 block w-full gk-rsvp-in"
                v-model="phone" :required="requirePhone" autocomplete="tel" />
            @endif
            <x-input-error class="mt-2" :messages="$errors->get('phone')" />
        </div>
        </div>

        <!-- Per-Guest Forms (Individual Tickets for RSVP) -->
        <div v-if="showGuestForms">
            <h3 class="gk-h mb-4">{{ __('messages.guest_information') }}</h3>
            <div v-for="(guest, gIndex) in guests" :key="gIndex" class="mb-4 gk-rsvp-guest">
                <h4 class="text-sm font-semibold gk-rsvp-say mb-3">
                    {{ __('messages.guest_n', ['n' => '']) }}@{{ gIndex + 1 }}
                    <span v-if="gIndex === 0" class="text-xs gk-rsvp-mute"> - {{ __('messages.you') }}</span>
                </h4>
                <div class="mb-3">
                    <label :for="'guest_name_' + gIndex" class="gk-rsvp-lb">{{ __('messages.name') }} *</label>
                    <input type="text" :id="'guest_name_' + gIndex" v-model="guest.name" required
                        class="mt-1 block w-full gk-rsvp-in text-sm" />
                    <input type="hidden" :name="'guests[' + gIndex + '][name]'" :value="guest.name">
                </div>
                <div class="mb-3">
                    <label :for="'guest_email_' + gIndex" class="gk-rsvp-lb">{{ __('messages.email') }} *</label>
                    <input type="email" :id="'guest_email_' + gIndex" v-model="guest.email" required
                        class="mt-1 block w-full gk-rsvp-in text-sm" />
                    <input type="hidden" :name="'guests[' + gIndex + '][email]'" :value="guest.email">
                </div>
                <div class="mb-3" v-if="askPhone">
                    <label :for="'guest_phone_' + gIndex" class="gk-rsvp-lb">{{ __('messages.phone_number') }}<span v-if="requirePhone"> *</span></label>
                    <input type="tel" :id="'guest_phone_' + gIndex" v-model="guest.phone" :required="requirePhone"
                        class="mt-1 block w-full gk-rsvp-in text-sm" />
                    <input type="hidden" :name="'guests[' + gIndex + '][phone]'" :value="guest.phone">
                </div>

                @if (! auth()->check() && config('app.hosted') && ! request()->embed)
                <div v-if="gIndex === 0" class="mt-3">
                    <div class="flex items-center">
                        <input id="create_account_rsvp_guest" name="create_account" type="checkbox"
                            v-model="createAccount" value="1"
                            class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded">
                        <label for="create_account_rsvp_guest" class="ms-3 block text-sm font-medium leading-6 gk-rsvp-ink">
                            {{ __('messages.create_account') }}
                        </label>
                    </div>
                    <div class="mt-3" v-if="createAccount">
                        <label for="password_rsvp_guest" class="gk-rsvp-lb">{{ __('messages.password') }} *</label>
                        <div class="relative mt-1">
                            <input :type="showPassword ? 'text' : 'password'" name="password" id="password_rsvp_guest" class="block w-full pe-10 text-sm gk-rsvp-in"
                                v-model="password" required autocomplete="new-password" />
                            <button type="button" @click="showPassword = !showPassword" class="absolute inset-y-0 end-0 flex items-center pe-3 gk-rsvp-mute">
                                <svg v-show="!showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" /></svg>
                                <svg v-show="showPassword" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" style="display: none;"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.542-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.542 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" /></svg>
                            </button>
                        </div>

                        <div class="mt-3">
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input id="terms_rsvp_guest" name="terms" type="checkbox" required
                                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                </div>
                                <div class="ms-3 text-sm leading-6">
                                    <label for="terms_rsvp_guest" class="font-medium gk-rsvp-ink">
                                        {!! str_replace([':terms', ':privacy'], [
                                            '<a href="' . policy_url('terms') . '" target="_blank" class="gk-link"> ' . __('messages.terms_of_service') . '</a>',
                                            '<a href="' . policy_url('privacy') . '" target="_blank" class="gk-link">' . __('messages.privacy_policy') . '</a>'
                                        ], __('messages.i_accept_the_terms_and_privacy')) !!}
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
            <input type="hidden" name="name" :value="guests.length > 0 ? guests[0].name : name">
            <input type="hidden" name="email" :value="guests.length > 0 ? guests[0].email : email">
            <input type="hidden" name="phone" :value="guests.length > 0 ? guests[0].phone : phone">
        </div>

        <!-- Event-level Custom Fields -->
        @if ($event->isPro())
        <div v-if="hasEventCustomFields" class="mb-6">
            <div v-for="(field, fieldKey) in eventCustomFields" :key="fieldKey" class="mb-4">
                <label :for="`event_custom_${fieldKey}`" class="gk-rsvp-lb">
                    @{{ field.name }}@{{ field.required ? ' *' : '' }}
                </label>
                <!-- Text input -->
                <input v-if="field.type === 'string'" type="text"
                    :name="`event_custom_values[${fieldKey}]`"
                    :id="`event_custom_${fieldKey}`"
                    v-model="eventCustomValues[fieldKey]"
                    :required="field.required"
                    class="mt-1 block w-full gk-rsvp-in" />
                <!-- Multiline text -->
                <textarea v-else-if="field.type === 'multiline_string'"
                    :name="`event_custom_values[${fieldKey}]`"
                    :id="`event_custom_${fieldKey}`"
                    v-model="eventCustomValues[fieldKey]"
                    :required="field.required"
                    rows="3"
                    dir="auto"
                    class="mt-1 block w-full gk-rsvp-in"></textarea>
                <!-- Yes/No switch -->
                <div v-else-if="field.type === 'switch'" class="mt-1">
                    <select :name="`event_custom_values[${fieldKey}]`"
                        :id="`event_custom_${fieldKey}`"
                        v-model="eventCustomValues[fieldKey]"
                        :required="field.required"
                        class="block w-full gk-rsvp-in">
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
                    class="mt-1 block w-full gk-rsvp-in" />
                <!-- Dropdown -->
                <select v-else-if="field.type === 'dropdown'"
                    :name="`event_custom_values[${fieldKey}]`"
                    :id="`event_custom_${fieldKey}`"
                    v-model="eventCustomValues[fieldKey]"
                    :required="field.required"
                    class="mt-1 block w-full gk-rsvp-in">
                    <option value="">{{ __('messages.please_select') }}</option>
                    <option v-for="option in (field.options || '').split(',')" :key="option.trim()" :value="option.trim()">@{{ option.trim() }}</option>
                </select>
                <!-- Multi-select -->
                <div v-else-if="field.type === 'multiselect'" class="mt-1 space-y-1">
                    <input type="hidden" :name="`event_custom_values[${fieldKey}]`" :value="(eventMultiselectValues[fieldKey] || []).join(', ')">
                    <label v-for="option in (field.options || '').split(',')" :key="option.trim()" class="flex items-center gap-2 gk-rsvp-ink">
                        <input type="checkbox" :value="option.trim()"
                            v-model="eventMultiselectValues[fieldKey]"
                            class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 dark:border-gray-600 rounded" />
                        @{{ option.trim() }}
                    </label>
                </div>
            </div>
        </div>
        @endif

        <!-- Turnstile CAPTCHA -->
        <div v-if="turnstileEnabled" class="mb-6">
            <div id="turnstile-rsvp-widget"></div>
            <input type="hidden" name="cf-turnstile-response" :value="turnstileToken">
            <x-input-error :messages="$errors->get('cf-turnstile-response')" class="mt-2" />
        </div>

        @if (session('error'))
        {{-- v-pre: this is inside the form's Vue mount, and a refusal can name an event. --}}
        <div v-pre class="mb-6 gk-note gk-note-bad">
            {{ session('error') }}
        </div>
        @endif

        {{-- Said in the page, where a browser alert box used to be. The cloak rule is this
             file's own: no other reaches this form, and without it the box was on screen,
             reading its own template, until Vue started. --}}
        <style {!! nonce_attr() !!}>#rsvp-form [v-cloak] { display: none; }</style>
        <p v-if="problem" v-cloak id="rsvp-problem" role="alert"
            class="mb-4 gk-note gk-note-warn font-medium">@{{ problem }}</p>
        <div class="gk-rsvp-actions">
            @if (! request()->embed)
            <button type="button"
                @click="hideForm"
                class="gk-btn gk-cta-alt">
                {{ __('messages.cancel') }}
            </button>
            @endif
            <button type="submit"
                :disabled="isSubmitting"
                class="gk-btn gk-btn-primary gk-cta-main">
                <span v-if="isSubmitting">...</span>
                <span v-else>{{ __('messages.submit') }}</span>
            </button>
        </div>

        @include('partials.guest-privacy-note', ['privacyNoteRole' => $event->creatorRole ?? $role ?? null])
    </form>
    @endif
</div>
