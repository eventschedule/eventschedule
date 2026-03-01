<x-slot name="head">
  <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
  @if (\App\Utils\TurnstileUtils::isEnabled())
  <script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer {!! nonce_attr() !!}></script>
  @endif
  <script {!! nonce_attr() !!}>
    window.addEventListener('DOMContentLoaded', function() {
        const { createApp, ref } = Vue;

        const app = createApp({
            data() {
                return {
                    createAccount: @json((bool) old('create_account', false)),
                    eventCustomFields: @json($event->custom_fields ?? []),
                    eventCustomValues: @json(old('event_custom_values', [])),
                    eventMultiselectValues: {},
                    name: @json(old('name', auth()->check() ? auth()->user()->name : '')),
                    email: @json(old('email', auth()->check() ? auth()->user()->email : '')),
                    password: @json(old('password', '')),
                    turnstileEnabled: @json(\App\Utils\TurnstileUtils::isEnabled()),
                    turnstileSiteKey: @json(\App\Utils\TurnstileUtils::getSiteKey()),
                    turnstileToken: '',
                    turnstileWidgetId: null,
                    isSubmitting: false,
                    rsvpFull: @json($event->isRsvpFull($date ?? request()->date)),
                };
            },
            created() {
                Object.entries(this.eventCustomFields).forEach(([key, field]) => {
                    if (field.type === 'multiselect') {
                        this.eventMultiselectValues[key] = [];
                    }
                });
            },
            mounted() {
                if (this.turnstileEnabled && this.turnstileSiteKey) {
                    const checkTurnstile = () => {
                        if (typeof turnstile !== 'undefined') {
                            this.turnstileWidgetId = turnstile.render('#turnstile-rsvp-widget', {
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
                }
            },
            computed: {
                canSubmit() {
                    return this.name.trim() !== '' && this.email.trim() !== '' && !this.rsvpFull;
                },
                hasEventCustomFields() {
                    return this.eventCustomFields && Object.keys(this.eventCustomFields).length > 0;
                },
            },
            methods: {
                validateForm(e) {
                    if (!this.canSubmit) {
                        e.preventDefault();
                        return;
                    }
                    if (this.turnstileEnabled && !this.turnstileToken) {
                        e.preventDefault();
                        alert(@json(__('messages.turnstile_verification_failed')));
                        return;
                    }
                    this.isSubmitting = true;
                },
            },
        }).mount('#rsvp-form');
    });
</script>
</x-slot>

<div id="rsvp-form">
    @if ($event->isRsvpFull($date ?? request()->date))
        <div class="text-center py-4">
            <p class="text-lg font-medium text-gray-500 dark:text-gray-400">{{ __('messages.rsvp_full') }}</p>
        </div>
    @else
    <form action="{{ route('event.rsvp', ['subdomain' => $subdomain]) }}" method="post" v-on:submit="validateForm">
        @csrf
        <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">
        <input type="hidden" name="event_date" value="{{ $date }}">
        <input type="hidden" name="subdomain" value="{{ $subdomain }}">

        @if ($event->rsvp_limit)
        @php $remaining = $event->rsvpRemaining($date ?? request()->date); @endphp
        <div class="mb-6 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.spots_remaining', ['count' => $remaining]) }}
        </div>
        @endif

        <div class="mb-6">
            <label for="name" class="text-gray-900 dark:text-gray-100">{{ __('messages.name') . ' *' }}</label>
            <input type="text" name="name" id="name" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                v-model="name" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div class="mb-6">
            <label for="email" class="text-gray-900 dark:text-gray-100">{{ __('messages.email') . ' *' }}</label>
            <input type="email" name="email" id="email" class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                v-model="email" required autocomplete="email" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if (! auth()->check() && config('app.hosted'))
                <div class="mt-6">
                    <div class="flex items-center">
                        <input id="create_account" name="create_account" type="checkbox"
                            v-model="createAccount" value="1"
                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 dark:border-gray-600 rounded">
                        <label for="create_account" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                            {{ __('messages.create_account') }}
                        </label>
                    </div>

                    <div class="mt-6" v-if="createAccount">
                        <label for="password" class="text-gray-900 dark:text-gray-100">{{ __('messages.password') . ' *' }}</label>
                        <div class="relative mt-1" x-data="{ show: false }">
                            <input :type="show ? 'text' : 'password'" name="password" id="password" class="block w-full pe-10 rounded-md border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-700 text-gray-900 dark:text-gray-100 shadow-sm focus:border-[#4E81FA] focus:ring-[#4E81FA]"
                                v-model="password" required autocomplete="new-password" />
                            <button
                                type="button"
                                @click="show = !show"
                                class="absolute inset-y-0 end-0 flex items-center pe-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-300"
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
        @if ($event->isPro())
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
                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 dark:border-gray-600 rounded" />
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
        <div class="mb-6 text-sm text-red-600 dark:text-red-400">
            {{ session('error') }}
        </div>
        @endif

        <button type="submit"
            :disabled="!canSubmit || isSubmitting"
            class="w-full justify-center rounded-xl px-6 py-3 text-lg font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg disabled:opacity-50 disabled:cursor-not-allowed disabled:hover:scale-100"
            style="background-color: #4E81FA; color: white;">
            <span v-if="isSubmitting">...</span>
            <span v-else>{{ __('messages.register') }}</span>
        </button>
    </form>
    @endif
</div>
