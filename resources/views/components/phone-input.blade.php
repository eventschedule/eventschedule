@props(['name' => 'phone', 'value' => '', 'id' => null, 'required' => false, 'disabled' => false])

@php
    $inputId = $id ?? $name;
@endphp

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@24.8.2/build/css/intlTelInput.css">
<style {!! nonce_attr() !!}>
.iti__dropdown-content { background: inherit; }
.dark .iti__dropdown-content { background: rgb(17 24 39); color: rgb(209 213 219); }
.dark .iti__search-input { background: rgb(17 24 39); color: rgb(209 213 219); border-color: rgb(55 65 81); }
.dark .iti__highlight { background-color: rgb(55 65 81); }
</style>

<input type="hidden" name="{{ $name }}" id="{{ $inputId }}_hidden" value="{{ $value }}">
<input type="tel" id="{{ $inputId }}" value="{{ $value }}"
    {{ $required ? 'required' : '' }}
    {{ $disabled ? 'disabled' : '' }}
    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
    autocomplete="tel" />

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@24.8.2/build/js/intlTelInput.min.js" {!! nonce_attr() !!}></script>
<script {!! nonce_attr() !!}>
(function() {
    var input = document.getElementById('{{ $inputId }}');
    var hidden = document.getElementById('{{ $inputId }}_hidden');
    if (!input || !hidden) return;

    var iti = window.intlTelInput(input, {
        utilsScript: 'https://cdn.jsdelivr.net/npm/intl-tel-input@24.8.2/build/js/utils.js',
        initialCountry: 'auto',
        geoIpLookup: function(callback) {
            fetch('https://ipapi.co/json/')
                .then(function(res) { return res.json(); })
                .then(function(data) { callback(data.country_code); })
                .catch(function() { callback('us'); });
        },
        separateDialCode: true,
        strictMode: true,
        nationalMode: false,
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
