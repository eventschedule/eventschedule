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

{{-- What is in force, on one line, before anything asks for details: the owner's own account
     (with the way to unlink it), or the one the install supplies for everyone. On a hosted install
     there was no sign of being connected at all beyond a row of bullets in the secret fields. --}}
@if ($isConnected)
    <div class="event-picked">
        <div class="settings-picked-main">
            <span class="settings-picked-name">{{ $gateway->label($user) }}</span>
            <span class="event-status is-on">{{ $platformProvided ? __('messages.gateway_own_account_in_use') : __('messages.connected') }}</span>
        </div>
        @if (! is_demo_mode())
        <div class="settings-picked-actions">
            <form method="POST" action="{{ route('payments.disconnect', ['gateway' => $gatewayKey]) }}" class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
                @csrf
                <button type="submit" class="event-link is-danger">{{ __('messages.disconnect') }}</button>
            </form>
        </div>
        @endif
    </div>
    @if ($platformProvided)
    <p class="event-hint">{{ __('messages.gateway_own_account_in_use_help') }}</p>
    @endif
@elseif ($platformProvided)
    <p><span class="event-status is-on">{{ __('messages.gateway_provided_by_install') }}</span></p>
    <p class="event-hint">{{ __('messages.gateway_provided_by_install_help') }}</p>
@endif

{{-- After the line above, not before it: with an install-supplied account in force, "enter your
     merchant details" is an instruction the reader may not need, and leading with it contradicts
     that line. --}}
@if ($gateway->credentialHelp())
    <p class="event-hint {{ ($isConnected || $platformProvided) ? 'mt-4' : '' }}">{{ $gateway->credentialHelp() }}</p>
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
                <div class="mt-2 event-check-grid">
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

    {{-- Named by what it does: "Connect" until there is something to save over. --}}
    @include('profile.partials.save', ['saveClass' => 'mt-2', 'saveLabel' => $isConnected ? __('messages.save') : __('messages.connect')])
</form>
