    @if (session('invoiceninja_error'))
        <div class="mb-4 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600 dark:text-red-400">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            <div class="min-w-0">
                <p class="text-sm font-medium text-red-800 dark:text-red-200">{{ __('messages.error_invoiceninja_connection') }}</p>
                <p class="mt-1 text-sm text-red-700 dark:text-red-300">{{ __('messages.'.session('invoiceninja_reason', 'invoiceninja_error_generic')) }}</p>
                <p class="mt-2 text-xs font-mono break-words text-red-700 dark:text-red-300" v-pre>{{ session('invoiceninja_error') }}</p>
                <p class="mt-2 text-xs">
                    <x-link href="{{ marketing_url('/docs/account-settings#invoice-ninja') }}" target="_blank">{{ __('messages.learn_more') }}</x-link>
                </p>
            </div>
        </div>
    @endif

    <p class="text-sm text-gray-600 dark:text-gray-400 mb-4">
        {{ __('messages.invoiceninja_help') }}
    </p>

    @if ($user->invoiceninja_api_key)
        <div class="mt-4">
            <x-text-input type="text" class="mt-1 block w-full" :value="$user->invoiceninja_company_name" readonly/>
            <div class="text-xs pt-1 flex items-center gap-3">
                <button type="button" id="invoiceninja-change-btn" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.edit') }}</button>
                <form method="POST" action="{{ route('invoiceninja.unlink') }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <button type="submit" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</button>
                </form>
            </div>
        </div>

        <form method="post" action="{{ route('profile.update_payments') }}" id="invoiceninja-change-form" class="mt-4 hidden">
            @csrf
            @method('patch')

            <div class="pt-4">
                <x-input-label for="invoiceninja_change_api_key" :value="__('messages.api_token')" />
                <x-text-input id="invoiceninja_change_api_key" name="invoiceninja_api_key" type="text" class="mt-1 block w-full"
                    value="" autocomplete="off" placeholder="{{ str_repeat('•', 10) }}" :disabled="is_demo_mode()" />
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_api_token_help') }}</p>
            </div>

            <div class="pt-4">
                <x-input-label for="invoiceninja_change_api_url" :value="__('messages.api_url')" />
                <x-text-input id="invoiceninja_change_api_url" name="invoiceninja_api_url" type="url" class="mt-1 block w-full"
                    :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" placeholder="https://invoices.example.com" :disabled="is_demo_mode()" />
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_api_url_help') }}</p>
            </div>

            <div class="flex items-center gap-4 pt-8">
                @if (is_demo_mode())
                    <button type="button"
                        data-alert="{{ __('messages.saving_disabled_demo_mode') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                        {{ __('messages.save') }}
                    </button>
                @else
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>
                @endif
            </div>
        </form>

        <form method="POST" action="{{ route('profile.update_invoiceninja_mode') }}" class="mt-6"
            x-data="{ type: '{{ $user->invoiceninja_mode ?? 'invoice' }}' }">
            @csrf
            @method('patch')

            <input type="hidden" name="invoiceninja_mode" :value="type">

            <x-input-label :value="__('messages.invoiceninja_mode')" />

            <div class="mt-2 space-y-2">
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="radio" value="invoice" x-model="type"
                        class="mt-0.5 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                        {{ is_demo_mode() ? 'disabled' : '' }}>
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.invoiceninja_mode_invoice') }}</span>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_mode_invoice_desc') }}</p>
                    </div>
                </label>
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="radio" value="payment_link" x-model="type"
                        class="mt-0.5 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                        {{ is_demo_mode() ? 'disabled' : '' }}>
                    <div>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.invoiceninja_mode_payment_link') }}</span>
                        <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_mode_payment_link_desc') }}</p>
                    </div>
                </label>
            </div>

            @if(Route::has('marketing.docs.tickets'))
            <p class="mt-2 ms-6 text-xs text-gray-500 dark:text-gray-500">
                <x-link href="{{ route('marketing.docs.tickets') }}#invoiceninja-modes" target="_blank">{{ __('messages.learn_more') }}</x-link>
            </p>
            @endif

            <div class="flex items-center gap-4 pt-4">
                @if (is_demo_mode())
                    <button type="button"
                        data-alert="{{ __('messages.saving_disabled_demo_mode') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                        {{ __('messages.save') }}
                    </button>
                @else
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>
                @endif

                @if (session('status') === 'payments-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.saved') }}</p>
                @endif
            </div>
        </form>
    @else
        <p class="text-sm text-gray-600 dark:text-gray-400">
            <x-link href="https://invoiceninja.com/partner-perks/event-schedule-perk/" target="_blank">
                {{ __('messages.invoiceninja_offer') }}
            </x-link>
        </p>

        <form method="post" action="{{ route('profile.update_payments') }}" enctype="multipart/form-data" class="mt-4" id="invoiceninja-connect-form">
            @csrf
            @method('patch')

            <div class="pt-4">
                <x-input-label for="invoiceninja_api_key" :value="__('messages.api_token') . ' *'" />
                <x-text-input id="invoiceninja_api_key" name="invoiceninja_api_key" type="text" class="mt-1 block w-full"
                    :value="old('invoiceninja_api_key', $user->invoiceninja_api_key)" autocomplete="off" required :disabled="is_demo_mode()" />
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_api_token_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_key')" />
            </div>

            <div class="pt-4">
                <x-input-label for="invoiceninja_api_url" :value="__('messages.api_url')" />
                <x-text-input id="invoiceninja_api_url" name="invoiceninja_api_url" type="url" class="mt-1 block w-full"
                    :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" placeholder="https://invoices.example.com" :disabled="is_demo_mode()" />
                <p class="text-xs text-gray-500 dark:text-gray-500 mt-0.5">{{ __('messages.invoiceninja_api_url_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_url')" />
            </div>

            <div class="flex items-center gap-4 pt-8">
                @if (is_demo_mode())
                    <button type="button"
                        data-alert="{{ __('messages.saving_disabled_demo_mode') }}"
                        class="inline-flex items-center px-4 py-2 bg-gray-400 dark:bg-gray-600 border border-transparent rounded-lg font-semibold text-xs text-white uppercase tracking-widest cursor-not-allowed">
                        {{ __('messages.save') }}
                    </button>
                @else
                    <x-primary-button>{{ __('messages.save') }}</x-primary-button>
                @endif

                @if (session('status') === 'payments-updated')
                <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.saved') }}</p>
                @endif
            </div>
        </form>
    @endif
