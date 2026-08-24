{{--
    The generic credentials tab, rendered for any gateway that declares credentialFields() instead of
    a settingsView() of its own. This is what makes a new gateway a driver class and nothing else: no
    blade, no route, no controller.

    Expects $gateway (PaymentGatewayDriver) and $gatewayKey (its registry key).
--}}
@php
    $fields = $gateway->credentialFields();
    // hasOwnCredentials(), NOT isConfiguredFor(): on an install that supplies its own credentials
    // (a selfhost operator's PAYFAST_* in .env) every owner is "configured" without having entered
    // anything, and this flag drives the write-only secret placeholder, whether a first connect may
    // leave a secret blank, and the Unlink button. Keyed off the broader question, the form would
    // offer to unlink credentials the owner never typed.
    $isConnected = $gateway->hasOwnCredentials($user);
    $platformProvided = $gateway->platformCredentials() !== [];
    // Bullets, not the stored value: a secret is write-only from here on. Leaving the input blank
    // means "keep what is stored", which is how an owner corrects a merchant id without having to
    // re-enter a key they cannot read back.
    $secretPlaceholder = str_repeat('•', 10);
@endphp

@if ($platformProvided)
    {{-- The install supplies this gateway for everyone. Say which account is actually taking the
         money, because the form below stays open either way and an owner has no other way to tell. --}}
    <div class="mt-4">
        <div class="flex items-center">
            <svg class="w-5 h-5 text-green-500 me-2 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
            </svg>
            <span class="text-sm text-gray-600 dark:text-gray-400">
                {{ $isConnected ? __('messages.gateway_own_account_in_use') : __('messages.gateway_provided_by_install') }}
            </span>
        </div>
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
            {{ $isConnected ? __('messages.gateway_own_account_in_use_help') : __('messages.gateway_provided_by_install_help') }}
        </p>
    </div>
@endif

{{-- After the panel, not before it: with an install-supplied account in force, "enter your merchant
     details" is an instruction the reader may not need, and leading with it contradicts the panel. --}}
@if ($gateway->credentialHelp())
    <p class="text-sm text-gray-600 dark:text-gray-400 mt-4 mb-4">
        {{ $gateway->credentialHelp() }}
    </p>
@endif

<form method="post" action="{{ route('payments.connect', ['gateway' => $gatewayKey]) }}" class="mt-4">
    @csrf

    @foreach ($fields as $field)
        <div class="mb-4">
            @if ($field->type === 'toggle')
                <x-toggle
                    :name="$field->name"
                    :id="$gatewayKey . '_' . $field->name"
                    :label="__($field->label)"
                    :help="$field->help ? __($field->help) : null"
                    :checked="(bool) $user->{$field->name}"
                    :disabled="is_demo_mode()" />

            @elseif ($field->type === 'multiselect')
                <x-input-label :value="__($field->label)" />
                @php
                    // Stored as a comma list; empty means "offer everything the gateway offers".
                    $selected = array_filter(explode(',', (string) $user->{$field->name}));
                @endphp
                {{-- Unticking every box posts nothing at all, and saveCredentials() skips a field
                     that is absent - so without this the owner could never clear a restriction they
                     had set, and the help text promising otherwise would be a lie. --}}
                <input type="hidden" name="{{ $field->name }}[]" value="">
                <div class="mt-2 grid grid-cols-2 sm:grid-cols-3 gap-2">
                    @foreach ($field->options as $optionValue => $optionLabel)
                        <label class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                            <input type="checkbox" name="{{ $field->name }}[]" value="{{ $optionValue }}"
                                @checked(in_array($optionValue, $selected, true))
                                @disabled(is_demo_mode())
                                class="rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                            <span>{{ $optionLabel }}</span>
                        </label>
                    @endforeach
                </div>
                @if ($field->help)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-2">{{ __($field->help) }}</p>
                @endif

            @else
                <x-input-label :for="$gatewayKey . '_' . $field->name" :value="__($field->label)" />
                <x-text-input
                    :id="$gatewayKey . '_' . $field->name"
                    :name="$field->name"
                    :type="$field->isSecret() ? 'password' : 'text'"
                    class="mt-1 block w-full"
                    :value="$field->isSecret() ? '' : old($field->name, $user->{$field->name})"
                    :placeholder="$field->isSecret() && $isConnected ? $secretPlaceholder : ''"
                    autocomplete="off"
                    :required="$field->required && ! ($field->isSecret() && $isConnected)"
                    :disabled="is_demo_mode()" />
                @if ($field->help)
                    <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __($field->help) }}</p>
                @endif
            @endif

            <x-input-error class="mt-2" :messages="$errors->get($field->name)" />
        </div>
    @endforeach

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
    </div>
</form>

@if ($isConnected && ! is_demo_mode())
    <div class="text-xs pt-3">
        <form method="POST" action="{{ route('payments.disconnect', ['gateway' => $gatewayKey]) }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
            @csrf
            <button type="submit" class="hover:underline text-gray-600 dark:text-gray-400">{{ __('messages.unlink_account') }}</button>
        </form>
    </div>
@endif
