@props(['name' => 'country_code', 'value' => '', 'id' => null, 'disabled' => false])

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
<script {!! nonce_attr() !!}>
window._countryInputs = window._countryInputs || {};
window.getCountryInput = function(id) {
    return window._countryInputs[id] || null;
};
</script>
@endonce

@once('country-input-styles')
<style {!! nonce_attr() !!}>
.iti.iti--country-only { display: block; width: 100%; }
.iti--country-only input.iti__tel-input,
.iti--country-only input[type="tel"] { display: none !important; }
.iti--country-only .iti__country-container { position: static; padding: 0; }
.iti--country-only .iti__selected-country { width: 100%; height: auto; }
.iti--country-only .iti__selected-country-primary {
    width: 100%;
    padding: 0.5rem 0.75rem;
    border: 1px solid #d1d5db;
    border-radius: 0.375rem;
    background: white;
    box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05);
    height: auto;
    font-size: 1rem;
    line-height: 1.5rem;
}
.dark .iti--country-only .iti__selected-country-primary {
    border-color: #2d2d30;
    background: #1e1e1e;
}
.iti--country-only .iti__country-name-label {
    margin-left: 0.5rem;
    font-size: 1rem;
    line-height: 1.5rem;
    color: #111827;
}
.dark .iti--country-only .iti__country-name-label {
    color: #d1d5db;
}
.iti--country-only .iti__arrow { margin-left: auto; }
</style>
@endonce

<input type="hidden" name="{{ $name }}" id="{{ $inputId }}" value="{{ $value }}"
    {{ $disabled ? 'disabled' : '' }} />
<input type="tel" id="{{ $inputId }}_tel"
    {{ $disabled ? 'disabled' : '' }}
    autocomplete="off" />

<script {!! nonce_attr() !!}>
(function() {
    var telInput = document.getElementById('{{ $inputId }}_tel');
    var hidden = document.getElementById('{{ $inputId }}');
    if (!telInput || !hidden) return;

    var iti = window.intlTelInput(telInput, {
        initialCountry: '{{ $value }}' || 'us',
        countrySearch: true,
        containerClass: 'iti--country-only',
        showFlags: true,
        allowDropdown: true,
        fixDropdownWidth: false,
    });

    // Hide tel input and style wrapper for country-only mode
    telInput.style.setProperty('display', 'none', 'important');
    var wrapper = telInput.closest('.iti');
    if (wrapper) {
        wrapper.style.display = 'block';
        wrapper.style.width = '100%';
        var container = wrapper.querySelector('.iti__country-container');
        if (container) container.style.position = 'static';
        var btn = wrapper.querySelector('.iti__selected-country');
        if (btn) btn.style.width = '100%';
        var primary = wrapper.querySelector('.iti__selected-country-primary');
        if (primary) {
            primary.style.height = 'auto';
            primary.style.padding = '0.5rem 0.75rem';
            primary.style.fontSize = '1rem';
            primary.style.lineHeight = '1.5rem';
            primary.style.boxSizing = 'border-box';
        }
    }

    function updateLabel() {
        var btn = telInput.closest('.iti--country-only').querySelector('.iti__selected-country-primary');
        if (!btn) return;
        var existing = btn.querySelector('.iti__country-name-label');
        if (existing) existing.remove();

        var data = iti.getSelectedCountryData();
        if (data && data.name) {
            var span = document.createElement('span');
            span.className = 'iti__country-name-label';
            span.textContent = data.name;
            btn.insertBefore(span, btn.querySelector('.iti__arrow'));
        }
    }

    function syncHidden() {
        var data = iti.getSelectedCountryData();
        var iso2 = data ? data.iso2 : '';
        hidden.value = iso2;
        hidden.dispatchEvent(new Event('change', { bubbles: true }));
        hidden.dispatchEvent(new Event('input', { bubbles: true }));
    }

    telInput.addEventListener('countrychange', function() {
        updateLabel();
        syncHidden();
    });

    // Initial label
    updateLabel();

    // Register for external access
    window._countryInputs['{{ $inputId }}'] = {
        iti: iti,
        setCountry: function(code) {
            if (code) {
                iti.setCountry(code);
                updateLabel();
                syncHidden();
            }
        },
        getSelectedCountryData: function() {
            return iti.getSelectedCountryData();
        }
    };
})();
</script>
