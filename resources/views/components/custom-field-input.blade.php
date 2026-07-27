@props([
    'role',
    'field',
    'fieldKey',
    'value' => null,
    'namePrefix' => 'custom_field_values',
    'idPrefix' => 'custom_field',
    // Guest request forms follow the translate toggle for the label; the AP follows the admin's
    // UI locale. See Role::customFieldLabel().
    'forGuest' => false,
])

@php
    $type = $field['type'] ?? 'string';
    $inputName = $namePrefix . '[' . $fieldKey . ']';
    $inputId = $idPrefix . '_' . $fieldKey;
    $errorKey = $namePrefix . '.' . $fieldKey;
    $isRequired = ! empty($field['required']);
    $regex = $field['regex'] ?? '';
    $regexHint = $field['regex_hint'] ?? '';
    $current = old($errorKey, $value ?? '');
@endphp

{{-- v-pre on the wrapper, not on individual strings: the AP event form mounts Vue on #app, so
     anything inside it is compiled as a Vue template, and the field name/options are owner-authored.
     A single wrapper covers the label, option text and hint at once, and is safe because nothing in
     here is a Vue directive (`:value` below is a Blade prop, resolved server-side). --}}
<div class="mb-6" v-pre>
    <x-input-label :for="$inputId">
        {{ $role->customFieldLabel($field, $fieldKey, $forGuest) }}{{ $isRequired ? ' *' : '' }}
        @if (! empty($field['private']))
            <span class="ms-1 inline-flex items-center gap-1 text-xs text-gray-500 dark:text-gray-400" title="{{ __('messages.field_private_help') }}">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
                </svg>
                {{ __('messages.field_private') }}
            </span>
        @endif
    </x-input-label>

    @if ($type === 'string')
    {{-- Null props rather than @if inside the tag: a Blade directive in a component tag stops it
         being parsed as a component, and the attribute bag already drops null/false attributes. --}}
    <x-text-input
        :id="$inputId"
        :name="$inputName"
        type="text"
        class="mt-1 block w-full"
        :value="$current"
        :required="$isRequired ?: null"
        :pattern="$regex ?: null"
        :title="$regexHint ?: null" />
    @elseif ($type === 'multiline_string')
    {{-- No pattern attribute: textarea does not support it, so this type is server-validated only. --}}
    <textarea
        id="{{ $inputId }}"
        name="{{ $inputName }}"
        rows="3"
        dir="auto"
        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
        {!! $regexHint ? 'title="' . e($regexHint) . '"' : '' !!}
        {{ $isRequired ? 'required' : '' }}>{{ $current }}</textarea>
    @elseif ($type === 'switch')
    <div class="mt-2">
        <input type="hidden" name="{{ $inputName }}" value="0" />
        <input type="checkbox"
            id="{{ $inputId }}"
            name="{{ $inputName }}"
            value="1"
            class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded"
            {{ $current ? 'checked' : '' }} />
    </div>
    @elseif ($type === 'date')
    <x-text-input
        :id="$inputId"
        :name="$inputName"
        type="date"
        class="mt-1 block w-full"
        :value="$current"
        :required="$isRequired ?: null" />
    @elseif ($type === 'dropdown')
    <select
        id="{{ $inputId }}"
        name="{{ $inputName }}"
        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
        {{ $isRequired ? 'required' : '' }}>
        <option value="">{{ __('messages.select') }}...</option>
        @foreach (\App\Models\Role::customFieldOptions($field) as $option)
        <option value="{{ $option }}" {{ $current === $option ? 'selected' : '' }}>
            {{ $option }}
        </option>
        @endforeach
    </select>
    @elseif ($type === 'multiselect')
    @php
        $selectedValues = is_array($current) ? $current : array_map('trim', explode(',', (string) $current));
    @endphp
    {{-- No `required` attribute is possible on a checkbox group where any one box satisfies it, so
         a required multiselect is flagged by data-required-group and enforced by the form's own JS
         (see booking-request.blade.php) on top of the server rule. --}}
    <div class="mt-1 space-y-1"
        @if ($isRequired) data-required-group="{{ $errorKey }}" @endif>
        @foreach (\App\Models\Role::customFieldOptions($field) as $option)
        <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
            <input type="checkbox"
                name="{{ $inputName }}[]"
                value="{{ $option }}"
                {{ in_array($option, $selectedValues) ? 'checked' : '' }}
                class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded" />
            {{ $option }}
        </label>
        @endforeach
    </div>
    @endif

    @if ($regexHint && in_array($type, ['string', 'multiline_string'], true))
    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $regexHint }}</p>
    @endif

    {{-- The booking form posts over AJAX and has no $errors bag, so its JS writes the message into
         this anchor by key. In the AP the x-input-error below renders it server-side instead. --}}
    <div class="mt-2 text-sm text-red-600 dark:text-red-400 hidden" data-error-for="{{ $errorKey }}"></div>
    <x-input-error class="mt-2" :messages="$errors->get($errorKey)" />
</div>
