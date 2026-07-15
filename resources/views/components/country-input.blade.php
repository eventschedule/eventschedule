@props(['name' => 'country_code', 'value' => '', 'id' => null, 'disabled' => false, 'autoInit' => true])

@php
    $inputId = $id ?? $name;
@endphp

@once
<link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
<script src="{{ asset('vendor/intl-tel-input/js/intlTelInput.min.js') }}" {!! nonce_attr() !!}></script>
<script {!! nonce_attr() !!}>
if (!document.getElementById('iti-country-styles')) {
    var s = document.createElement('style');
    s.id = 'iti-country-styles';
    s.textContent = '.dark .iti { --iti-dropdown-bg: #1e1e1e; --iti-hover-color: #2d2d30; --iti-border-color: #2d2d30; --iti-dialcode-color: #9ca3af; --iti-arrow-color: #d1d5db; } .dark .iti__dropdown-content { color: #d1d5db; } .dark .iti__selected-dial-code { color: #d1d5db; } .dark .iti__search-input { background: #1e1e1e; color: #d1d5db; border-color: #2d2d30; } .iti.iti--country-only { box-sizing: border-box; display: flex; align-items: center; width: 100%; padding: 0.625rem 0.75rem; border: 1px solid #d1d5db; border-radius: 0.5rem; background: white; box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.05); font-size: 1rem; line-height: 1.5rem; cursor: pointer; } .dark .iti.iti--country-only { border-color: #2d2d30; background: #1e1e1e; } .iti.iti--country-only:focus-within { border-color: var(--brand-blue); box-shadow: 0 0 0 1px var(--brand-blue); } .iti--country-only input.iti__tel-input, .iti--country-only input[type="tel"] { display: none !important; } .iti--country-only .iti__country-container { position: static; padding: 0; width: 100%; } .iti--country-only .iti__selected-country { width: 100%; height: auto; padding: 0; border: 0; background: none; } .iti--country-only .iti__selected-country-primary { width: 100%; padding: 0; border: none; background: none; box-shadow: none; height: auto; } .iti--country-only .iti__country-name-label { margin-left: 0.5rem; color: #111827; } .dark .iti--country-only .iti__country-name-label { color: #d1d5db; } .iti--country-only .iti__arrow { margin-left: auto; }';
    document.head.appendChild(s);
}

window._countryInputs = window._countryInputs || {};
window._countryInputPending = window._countryInputPending || {};

// Validate a code against intl-tel-input's own alpha-2 (iso2) list before handing it to the
// library. intlTelInput({initialCountry}) and iti.setCountry() throw "No country data for '<code>'"
// on any unknown code - e.g. an ISO alpha-3 value like "isr" stored on a venue. Guarding here keeps
// a bad stored value from breaking the picker (and the surrounding Vue watcher chain).
window._itiValidCountry = function(code) {
    if (!code || typeof code !== 'string') return false;
    var lc = code.trim().toLowerCase();
    try {
        var data = (window.intlTelInput && typeof window.intlTelInput.getCountryData === 'function')
            ? window.intlTelInput.getCountryData() : null;
        if (data && data.length) {
            return data.some(function(c) { return c.iso2 === lc; });
        }
    } catch (e) {}
    // Fallback if the country list isn't available: only a 2-letter code is plausibly valid.
    return /^[a-z]{2}$/.test(lc);
};

window.destroyCountryInput = function(inputId) {
    var entry = window._countryInputs[inputId];
    if (entry && entry.iti) {
        try {
            entry.iti.destroy();
        } catch (e) {}
    }
    delete window._countryInputs[inputId];
    var tel = document.getElementById(inputId + '_tel');
    if (tel) {
        tel._itiInstance = null;
    }
};

window.initCountryInput = function(inputId, initialValue) {
    var telInput = document.getElementById(inputId + '_tel');
    var hidden = document.getElementById(inputId);
    if (!telInput || !hidden) return null;

    // Already initialized on this exact element
    if (telInput._itiInstance) {
        if (initialValue) {
            var inst = window._countryInputs[inputId];
            if (inst) inst.setCountry(initialValue);
        }
        return window._countryInputs[inputId];
    }

    // If telInput is itself nested inside a stale .iti--country-only (e.g. a prior init
    // wrapped it but lost its _itiInstance reference across a Vue v-if remount), unwrap it
    // so intlTelInput below doesn't double-wrap.
    if (telInput.parentNode && telInput.parentNode.classList &&
        telInput.parentNode.classList.contains('iti--country-only')) {
        var staleWrapper = telInput.parentNode;
        var grand = staleWrapper.parentNode;
        if (grand) {
            grand.insertBefore(telInput, staleWrapper);
            staleWrapper.remove();
        }
    }

    // Sweep any other stranded .iti--country-only wrappers in the document whose tel input
    // matches our inputId but isn't the live telInput. Catches orphans regardless of where
    // they ended up after Vue's render lifecycle.
    document.querySelectorAll('.iti--country-only').forEach(function(w) {
        var t = w.querySelector('input[type="tel"]');
        if (t && t.id === inputId + '_tel' && t !== telInput) {
            w.remove();
        }
    });

    // Stale map after Vue removed/reinserted the subtree (new tel node, old Iti in _countryInputs)
    if (window._countryInputs[inputId]) {
        window.destroyCountryInput(inputId);
        telInput = document.getElementById(inputId + '_tel');
        hidden = document.getElementById(inputId);
        if (!telInput || !hidden) return null;
    }

    var iti = intlTelInput(telInput, {
        initialCountry: window._itiValidCountry(initialValue) ? initialValue.trim().toLowerCase() : 'us',
        countrySearch: true,
        containerClass: 'iti--country-only',
        showFlags: true,
        allowDropdown: true,
        fixDropdownWidth: false,
    });
    telInput._itiInstance = iti;
    telInput.style.setProperty('display', 'none', 'important');

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

    updateLabel();

    window._countryInputs[inputId] = {
        iti: iti,
        setCountry: function(code) {
            if (!window._itiValidCountry(code)) {
                return;
            }
            try {
                iti.setCountry(code.trim().toLowerCase());
                updateLabel();
                syncHidden();
            } catch (e) {
                // Defensive: never let an unexpected code break the surrounding Vue watcher chain.
            }
        },
        getSelectedCountryData: function() {
            return iti.getSelectedCountryData();
        }
    };
    return window._countryInputs[inputId];
};

window.getCountryInput = function(id) {
    if (!window._countryInputs[id]) {
        window.initCountryInput(id, window._countryInputPending[id]);
    }
    return window._countryInputs[id] || null;
};

</script>
@endonce

<input type="hidden" name="{{ $name }}" id="{{ $inputId }}" value="{{ $value }}"
    {{ $disabled ? 'disabled' : '' }} />
<input type="tel" id="{{ $inputId }}_tel"
    {{ $disabled ? 'disabled' : '' }}
    autocomplete="off" />

@if ($autoInit)
<script {!! nonce_attr() !!}>
(function() {
    window._countryInputPending['{{ $inputId }}'] = '{{ $value }}' || 'us';
    setTimeout(function() {
        window.initCountryInput('{{ $inputId }}', window._countryInputPending['{{ $inputId }}']);
        var tel = document.getElementById('{{ $inputId }}_tel');
        if (tel) {
            var wrapper = tel.closest('.iti--country-only');
            if (wrapper) {
                var form = wrapper.closest('form');
                var refInput = form && form.querySelector('input[type="text"], input[type="email"]');
                if (refInput && refInput.offsetHeight > 30) {
                    wrapper.style.setProperty('height', refInput.offsetHeight + 'px', 'important');
                }
            }
        }
    }, 0);
})();
</script>
@endif
