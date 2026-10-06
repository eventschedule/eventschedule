    @if (session('invoiceninja_error'))
        <div class="mb-4 flex items-start gap-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 flex-shrink-0 mt-0.5 text-red-600 dark:text-red-400" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m9-.75a9 9 0 11-18 0 9 9 0 0118 0zm-9 3.75h.008v.008H12v-.008z" />
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

    <p class="event-hint">{{ __('messages.invoiceninja_help') }}</p>

    @if ($user->invoiceninja_api_key)
        <div class="event-picked">
            <div class="settings-picked-main">
                <span class="settings-picked-name">{{ $user->invoiceninja_company_name ?: 'Invoice Ninja' }}</span>
                <span class="event-status is-on">{{ __('messages.connected') }}</span>
            </div>
            <div class="settings-picked-actions">
                <button type="button" id="invoiceninja-change-btn" class="event-link" data-reveal="invoiceninja-change-form" data-reveal-focus="#invoiceninja_change_api_url" aria-expanded="false">{{ __('messages.edit') }}</button>
                <form method="POST" action="{{ route('invoiceninja.unlink') }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <button type="submit" class="event-link is-danger" @disabled(is_demo_mode())>{{ __('messages.disconnect') }}</button>
                </form>
            </div>
        </div>

        {{-- "Edit" shows this over the connected state. A blank token keeps the stored one. --}}
        <form method="post" action="{{ route('profile.update_payments') }}" id="invoiceninja-change-form" class="event-add-box" hidden>
            @csrf
            @method('patch')

            <div>
                <x-input-label for="invoiceninja_change_api_key" :value="__('messages.api_token')" />
                {{-- A token is a secret: masked like a password, with the same reveal toggle. It
                     was a plain text field. new-password keeps a browser from filling in the
                     account's own password here. --}}
                <x-password-input id="invoiceninja_change_api_key" name="invoiceninja_api_key" class="mt-1 block w-full"
                    value="" autocomplete="new-password" placeholder="{{ str_repeat('•', 10) }}" :disabled="is_demo_mode()" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.invoiceninja_api_token_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_key')" />
            </div>

            <div class="pt-4">
                <x-input-label for="invoiceninja_change_api_url" :value="__('messages.api_url')" />
                <x-text-input id="invoiceninja_change_api_url" name="invoiceninja_api_url" type="url" class="mt-1 block w-full"
                    :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" placeholder="https://invoices.example.com" :disabled="is_demo_mode()" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.invoiceninja_api_url_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_url')" />
            </div>

            @include('profile.partials.save', ['saveClass' => 'mt-4'])
        </form>

        {{-- How a sale reaches Invoice Ninja. Plain radios that post their own name: this was an
             Alpine island copying its state into a hidden input. --}}
        <form method="POST" action="{{ route('profile.update_invoiceninja_mode') }}" class="mt-6">
            @csrf
            @method('patch')

            <p class="event-group-label">{{ __('messages.invoiceninja_mode') }}</p>

            <div class="space-y-3">
                @foreach (['invoice' => ['invoiceninja_mode_invoice', 'invoiceninja_mode_invoice_desc'], 'payment_link' => ['invoiceninja_mode_payment_link', 'invoiceninja_mode_payment_link_desc']] as $modeValue => [$modeLabel, $modeHelp])
                <label class="flex items-start gap-2 cursor-pointer">
                    <input type="radio" name="invoiceninja_mode" value="{{ $modeValue }}"
                        class="mt-0.5 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                        @checked(old('invoiceninja_mode', $user->invoiceninja_mode ?? 'invoice') === $modeValue)
                        @disabled(is_demo_mode())>
                    <span>
                        <span class="block text-sm text-gray-700 dark:text-gray-300">{{ __('messages.'.$modeLabel) }}</span>
                        <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5">{{ __('messages.'.$modeHelp) }}</span>
                    </span>
                </label>
                @endforeach
            </div>

            @if(Route::has('marketing.docs.tickets'))
            <p class="mt-2 ms-6 text-xs text-gray-500 dark:text-gray-400">
                <x-link href="{{ route('marketing.docs.tickets') }}#invoiceninja-modes" target="_blank">{{ __('messages.learn_more') }}</x-link>
            </p>
            @endif

            @include('profile.partials.save', ['saveClass' => 'mt-4', 'saveShown' => session('status') === 'payments-updated'])
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

            <div>
                <x-input-label for="invoiceninja_api_key" :value="__('messages.api_token') . ' *'" />
                <x-password-input id="invoiceninja_api_key" name="invoiceninja_api_key" class="mt-1 block w-full"
                    autocomplete="new-password" required :disabled="is_demo_mode()" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.invoiceninja_api_token_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_key')" />
            </div>

            <div class="pt-4">
                <x-input-label for="invoiceninja_api_url" :value="__('messages.api_url')" />
                <x-text-input id="invoiceninja_api_url" name="invoiceninja_api_url" type="url" class="mt-1 block w-full"
                    :value="old('invoiceninja_api_url', $user->invoiceninja_api_url)" placeholder="https://invoices.example.com" :disabled="is_demo_mode()" />
                <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.invoiceninja_api_url_help') }}</p>
                <x-input-error class="mt-2" :messages="$errors->get('invoiceninja_api_url')" />
            </div>

            @include('profile.partials.save', ['saveClass' => 'mt-4', 'saveLabel' => __('messages.connect')])
        </form>
    @endif
