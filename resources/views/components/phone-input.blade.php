@props(['name' => 'phone', 'value' => '', 'id' => null, 'required' => false, 'disabled' => false, 'country' => 'us'])

@php
    $inputId = $id ?? $name;
@endphp

@once
<link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
<style {!! nonce_attr() !!}>
.dark .iti {
    --iti-dropdown-bg: #1e1e1e;
    --iti-hover-color: #2d2d30;
    --iti-border-color: #2d2d30;
    --iti-dialcode-color: #9ca3af;
    --iti-arrow-color: #d1d5db;
}
.dark .iti__dropdown-content { color: #d1d5db; }
.dark .iti__selected-dial-code { color: #d1d5db; }
.dark .iti__search-input { background: #1e1e1e; color: #d1d5db; border-color: #2d2d30; }
</style>
<script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}" {!! nonce_attr() !!}></script>
@endonce

<input type="hidden" name="{{ $name }}" id="{{ $inputId }}_hidden" value="{{ $value }}">
<input type="tel" id="{{ $inputId }}" value="{{ $value }}"
    {{ $required ? 'required' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
    autocomplete="tel" />

<script {!! nonce_attr() !!}>
(function() {
    var input = document.getElementById('{{ $inputId }}');
    var hidden = document.getElementById('{{ $inputId }}_hidden');
    if (!input || !hidden) return;

    var iti = window.intlTelInput(input, {
        utilsScript: '{{ asset('vendor/intl-tel-input/js/utils.js') }}',
        initialCountry: '{{ strtolower($country) }}',
        separateDialCode: true,
        strictMode: true,
        nationalMode: false,
        autoPlaceholder: 'off',
    });

    // If we have an initial value, set it
    if (hidden.value) {
        iti.setNumber(hidden.value);
    }

    function updateHidden() {
        var number = iti.getNumber();
        hidden.value = number || '';
    }

    input.addEventListener('change', updateHidden);
    input.addEventListener('input', updateHidden);
    input.addEventListener('countrychange', updateHidden);
})();
</script>
