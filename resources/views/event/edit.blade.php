<x-app-admin-layout>

@vite([
    'resources/js/countrySelect.min.js',
    'resources/css/countrySelect.min.css',
])

<!-- Step Indicator for Add Event Flow -->
@if(session('pending_request'))
    <div class="my-6">
        <x-step-indicator :compact="true" />
    </div>
@endif

@php
  $use24hr = get_use_24_hour_time($role ?? null);
  $oldStartsAt = old('starts_at', $event->localStartsAt());
  $oldDuration = old('duration', $event->duration);
  $eventDate = '';
  $eventStartTime = '';
  $eventEndTime = '';
  if ($oldStartsAt) {
      $localDt = \Carbon\Carbon::parse($oldStartsAt);
      $eventDate = $localDt->format('Y-m-d');
      $eventStartTime = $use24hr ? $localDt->format('H:i') : $localDt->format('g:i A');
      if ($oldDuration) {
          $endDt = $localDt->copy()->addMinutes(round($oldDuration * 60));
          $eventEndTime = $use24hr ? $endDt->format('H:i') : $endDt->format('g:i A');
      }
  }
@endphp

<x-slot name="head">
  <style {!! nonce_attr() !!}>
    form button {
      min-width: 100px;
      min-height: 40px;
    }
    
    /* Hide all sections except the first one by default */
    .section-content {
      display: none;
    }
    .section-content:first-of-type {
      display: block;
    }

    .section-nav-link.validation-error {
      border-inline-start-color: #dc2626 !important;
    }

    @media (prefers-color-scheme: dark) {
      .section-nav-link.validation-error {
        border-inline-start-color: #ef4444 !important;
      }
    }

    .dark .section-nav-link.validation-error {
      border-inline-start-color: #ef4444 !important;
    }

    /* Mobile accordion styles */
    .mobile-section-header.active .accordion-chevron {
      transform: rotate(180deg);
    }
    .mobile-section-header.active {
      color: #4E81FA;
      border-color: #4E81FA;
    }
    .mobile-section-header.validation-error {
      border-color: #dc2626 !important;
    }
    @media (prefers-color-scheme: dark) {
      .mobile-section-header.validation-error {
        border-color: #ef4444 !important;
      }
    }
    .dark .mobile-section-header.validation-error {
      border-color: #ef4444 !important;
    }

    /* Custom time picker dropdown */
    .time-dropdown {
      display: none;
      position: absolute;
      z-index: 50;
      width: 100%;
      max-height: 200px;
      overflow-y: auto;
      background: #fff;
      border: 1px solid #d1d5db;
      border-radius: 0.375rem;
      box-shadow: 0 4px 6px -1px rgba(0,0,0,.1), 0 2px 4px -2px rgba(0,0,0,.1);
      margin-top: 2px;
    }
    .dark .time-dropdown {
      background: #1e1e1e;
      border-color: #2d2d30;
    }
    .time-dropdown.open {
      display: block;
    }
    .time-dropdown-item {
      padding: 6px 12px;
      cursor: pointer;
      font-size: 0.875rem;
      color: #111827;
    }
    .dark .time-dropdown-item {
      color: #d1d5db;
    }
    .time-dropdown-item:hover,
    .time-dropdown-item.highlighted {
      background: #e5e7eb;
      color: #111827;
    }
    .dark .time-dropdown-item:hover,
    .dark .time-dropdown-item.highlighted {
      background: #2d2d30;
      color: #fff;
    }
    .time-dropdown-item.hidden {
      display: none;
    }

    /* Shake animation for incomplete data warning */
    @keyframes shake {
      0%, 100% { transform: translateX(0); }
      20%, 60% { transform: translateX(-5px); }
      40%, 80% { transform: translateX(5px); }
    }
    .shake {
      animation: shake 0.4s ease-in-out;
    }
  </style>
  <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
  <script {!! nonce_attr() !!}>
    // --- Global time helper functions (used by main event and parts) ---
    var use24hr = {{ $use24hr ? 'true' : 'false' }};

    function parseTimeToMinutes(timeStr) {
        if (!timeStr) return null;
        timeStr = timeStr.trim();

        // Try 24hr format: HH:mm or H:mm
        var match24 = timeStr.match(/^(\d{1,2}):(\d{2})$/);
        if (match24 && use24hr) {
            var h = parseInt(match24[1], 10);
            var m = parseInt(match24[2], 10);
            if (h >= 0 && h <= 23 && m >= 0 && m <= 59) return h * 60 + m;
        }

        // Try 12hr format: h:mm AM/PM or h:mmAM/PM
        var match12 = timeStr.match(/^(\d{1,2}):(\d{2})\s*(AM|PM|am|pm)$/i);
        if (match12) {
            var h = parseInt(match12[1], 10);
            var m = parseInt(match12[2], 10);
            var period = match12[3].toUpperCase();
            if (h >= 1 && h <= 12 && m >= 0 && m <= 59) {
                if (period === 'AM' && h === 12) h = 0;
                else if (period === 'PM' && h !== 12) h += 12;
                return h * 60 + m;
            }
        }

        // Try shorthand: 2pm, 11am
        var matchShort = timeStr.match(/^(\d{1,2})\s*(AM|PM|am|pm)$/i);
        if (matchShort) {
            var h = parseInt(matchShort[1], 10);
            var period = matchShort[2].toUpperCase();
            if (h >= 1 && h <= 12) {
                if (period === 'AM' && h === 12) h = 0;
                else if (period === 'PM' && h !== 12) h += 12;
                return h * 60;
            }
        }

        // Try bare HH:mm in 12hr mode (assume as-is if valid)
        if (!use24hr && match24) {
            var h = parseInt(match24[1], 10);
            var m = parseInt(match24[2], 10);
            if (h >= 0 && h <= 23 && m >= 0 && m <= 59) return h * 60 + m;
        }

        return null;
    }

    function formatMinutesToTime(minutes) {
        var h = Math.floor(minutes / 60) % 24;
        var m = minutes % 60;
        if (use24hr) {
            return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
        } else {
            var period = h < 12 ? 'AM' : 'PM';
            var h12 = h % 12 || 12;
            return h12 + ':' + (m < 10 ? '0' : '') + m + ' ' + period;
        }
    }

    // Simplified time picker for event parts (lazy initialization)
    function initPartTimePicker(inputEl, dropdownEl) {
        if (inputEl._timepickerInit) return; // Already initialized
        inputEl._timepickerInit = true;

        var timeOptions = [];
        for (var m = 0; m < 1440; m += 30) {
            timeOptions.push(formatMinutesToTime(m));
        }

        // Build dropdown items
        timeOptions.forEach(function(label) {
            var div = document.createElement('div');
            div.className = 'time-dropdown-item';
            div.textContent = label;
            div.setAttribute('data-value', label);
            div.addEventListener('mousedown', function(e) {
                e.preventDefault();
                inputEl.value = label;
                closeDropdown();
                inputEl.dispatchEvent(new Event('change', { bubbles: true }));
            });
            dropdownEl.appendChild(div);
        });

        var highlightedIndex = -1;

        function getVisibleItems() {
            return Array.from(dropdownEl.querySelectorAll('.time-dropdown-item:not(.hidden)'));
        }

        function setHighlight(idx) {
            var items = getVisibleItems();
            items.forEach(function(el, i) {
                el.classList.toggle('highlighted', i === idx);
            });
            highlightedIndex = idx;
            if (idx >= 0 && idx < items.length) {
                items[idx].scrollIntoView({ block: 'nearest' });
            }
        }

        function showAllItems() {
            var items = dropdownEl.querySelectorAll('.time-dropdown-item');
            items.forEach(function(el) {
                el.classList.remove('hidden');
            });
            highlightedIndex = -1;
        }

        function openDropdown() {
            showAllItems();
            dropdownEl.classList.add('open');
            scrollToNearest();
        }

        function closeDropdown() {
            dropdownEl.classList.remove('open');
            highlightedIndex = -1;
        }

        function filterItems() {
            var query = inputEl.value.trim().toLowerCase();
            var items = dropdownEl.querySelectorAll('.time-dropdown-item');
            items.forEach(function(el) {
                var val = el.getAttribute('data-value').toLowerCase();
                if (!query || val.indexOf(query) !== -1) {
                    el.classList.remove('hidden');
                } else {
                    el.classList.add('hidden');
                }
            });
            highlightedIndex = -1;
        }

        function scrollToNearest() {
            var minutes = parseTimeToMinutes(inputEl.value);
            if (minutes === null) minutes = 540; // Default to 9am
            var closest = Math.round(minutes / 30) * 30;
            if (closest >= 1440) closest = 0;
            var target = formatMinutesToTime(closest);
            var items = getVisibleItems();
            for (var i = 0; i < items.length; i++) {
                if (items[i].getAttribute('data-value') === target) {
                    items[i].scrollIntoView({ block: 'center' });
                    setHighlight(i);
                    return;
                }
            }
        }

        inputEl.addEventListener('focus', function() {
            openDropdown();
        });

        inputEl.addEventListener('click', function() {
            if (!dropdownEl.classList.contains('open')) {
                openDropdown();
            }
        });

        inputEl.addEventListener('input', function() {
            filterItems();
            if (dropdownEl.classList.contains('open')) {
                var visible = getVisibleItems();
                if (visible.length > 0) {
                    setHighlight(0);
                }
            }
        });

        inputEl.addEventListener('blur', function() {
            closeDropdown();
        });

        inputEl.addEventListener('keydown', function(e) {
            if (!dropdownEl.classList.contains('open')) return;
            var items = getVisibleItems();
            if (e.key === 'ArrowDown') {
                e.preventDefault();
                var next = highlightedIndex + 1;
                if (next >= items.length) next = 0;
                setHighlight(next);
            } else if (e.key === 'ArrowUp') {
                e.preventDefault();
                var prev = highlightedIndex - 1;
                if (prev < 0) prev = items.length - 1;
                setHighlight(prev);
            } else if (e.key === 'Enter') {
                e.preventDefault();
                if (highlightedIndex >= 0 && highlightedIndex < items.length) {
                    inputEl.value = items[highlightedIndex].getAttribute('data-value');
                    closeDropdown();
                    inputEl.dispatchEvent(new Event('change', { bubbles: true }));
                }
            } else if (e.key === 'Escape' || e.key === 'Tab') {
                closeDropdown();
            }
        });

        // Close on click outside
        document.addEventListener('mousedown', function(e) {
            if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
                closeDropdown();
            }
        });

        // Open immediately since this is called on focus
        openDropdown();
    }

    document.addEventListener('DOMContentLoaded', function() {
        var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
        var localeConfig = fpLocale ? { locale: fpLocale } : {};

        var f = flatpickr('.datepicker-date', Object.assign({
            allowInput: true,
            enableTime: false,
            altInput: true,
            altFormat: "M j, Y",
            dateFormat: "Y-m-d",
            onChange: function() {
                updateHiddenFields();
            },
        }, localeConfig));
        // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
        if (f._input) f._input.onkeydown = () => false;

        // Initialize recurring end date picker if it exists on page load
        var endDateInput = document.querySelector('.datepicker-end-date');
        if (endDateInput && endDateInput.value) {
            var endDatePicker = flatpickr(endDateInput, Object.assign({
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
            }, localeConfig));
            if (endDatePicker._input) {
                endDatePicker._input.onkeydown = () => false;
            }
        }

        $("#venue_country").countrySelect({
            defaultCountry: "{{ $selectedVenue && $selectedVenue->country ? $selectedVenue->country : ($role && $role->country_code ? $role->country_code : '') }}",
        });

        // --- Time combobox logic for main event ---

        function normalizeTimeInput(inputEl) {
            var val = inputEl.value;
            var minutes = parseTimeToMinutes(val);
            if (minutes !== null) {
                inputEl.value = formatMinutesToTime(minutes);
            } else if (val.trim() !== '') {
                inputEl.value = '';
            }
        }

        function updateHiddenFields() {
            var dateEl = document.getElementById('event_date');
            var startEl = document.getElementById('start_time');
            var endEl = document.getElementById('end_time');
            var hiddenStartsAt = document.getElementById('starts_at');
            var hiddenDuration = document.getElementById('duration');

            var dateVal = dateEl._flatpickr ? dateEl._flatpickr.selectedDates[0] : null;
            var dateStr = dateVal ? dateEl._flatpickr.formatDate(dateVal, 'Y-m-d') : dateEl.value;
            var startMinutes = parseTimeToMinutes(startEl.value);
            var endMinutes = parseTimeToMinutes(endEl.value);

            if (dateStr && startMinutes !== null) {
                var hh = (Math.floor(startMinutes / 60) < 10 ? '0' : '') + Math.floor(startMinutes / 60);
                var mm = (startMinutes % 60 < 10 ? '0' : '') + (startMinutes % 60);
                hiddenStartsAt.value = dateStr + ' ' + hh + ':' + mm + ':00';
            } else {
                hiddenStartsAt.value = '';
            }

            if (endMinutes !== null && startMinutes !== null) {
                var diff = endMinutes - startMinutes;
                if (diff < 0) diff += 1440; // crosses midnight
                hiddenDuration.value = (diff / 60).toFixed(2).replace(/\.?0+$/, '');
            } else {
                hiddenDuration.value = '';
            }
        }

        // Track duration for auto-adjust
        var lastDurationMinutes = 60;
        var initStart = parseTimeToMinutes(document.getElementById('start_time').value);
        var initEnd = parseTimeToMinutes(document.getElementById('end_time').value);
        if (initStart !== null && initEnd !== null) {
            var diff = initEnd - initStart;
            if (diff < 0) diff += 1440;
            lastDurationMinutes = diff;
        }

        var startTimeEl = document.getElementById('start_time');
        var endTimeEl = document.getElementById('end_time');

        startTimeEl.addEventListener('blur', function() {
            normalizeTimeInput(startTimeEl);
            updateHiddenFields();
        });

        endTimeEl.addEventListener('blur', function() {
            normalizeTimeInput(endTimeEl);
            var startMin = parseTimeToMinutes(startTimeEl.value);
            var endMin = parseTimeToMinutes(endTimeEl.value);
            if (startMin !== null && endMin !== null) {
                var diff = endMin - startMin;
                if (diff < 0) diff += 1440;
                lastDurationMinutes = diff;
            }
            updateHiddenFields();
        });

        startTimeEl.addEventListener('input', function() { updateHiddenFields(); });
        endTimeEl.addEventListener('input', function() { updateHiddenFields(); });

        // Initialize hidden fields on page load
        updateHiddenFields();

        // Custom time picker dropdown
        function initTimePicker(inputEl, dropdownEl, getDefaultMinutes) {
            var timeOptions = [];
            for (var m = 0; m < 1440; m += 30) {
                timeOptions.push(formatMinutesToTime(m));
            }

            // Build dropdown items
            timeOptions.forEach(function(label) {
                var div = document.createElement('div');
                div.className = 'time-dropdown-item';
                div.textContent = label;
                div.setAttribute('data-value', label);
                div.addEventListener('mousedown', function(e) {
                    e.preventDefault();
                    inputEl.value = label;
                    closeDropdown();
                    normalizeTimeInput(inputEl);
                    updateHiddenFields();
                });
                dropdownEl.appendChild(div);
            });

            var highlightedIndex = -1;

            function getVisibleItems() {
                return Array.from(dropdownEl.querySelectorAll('.time-dropdown-item:not(.hidden)'));
            }

            function setHighlight(idx) {
                var items = getVisibleItems();
                items.forEach(function(el, i) {
                    el.classList.toggle('highlighted', i === idx);
                });
                highlightedIndex = idx;
                if (idx >= 0 && idx < items.length) {
                    items[idx].scrollIntoView({ block: 'nearest' });
                }
            }

            function showAllItems() {
                var items = dropdownEl.querySelectorAll('.time-dropdown-item');
                items.forEach(function(el) {
                    el.classList.remove('hidden');
                });
                highlightedIndex = -1;
            }

            function openDropdown() {
                showAllItems();
                dropdownEl.classList.add('open');
                scrollToNearest();
            }

            function closeDropdown() {
                dropdownEl.classList.remove('open');
                highlightedIndex = -1;
            }

            function filterItems() {
                var query = inputEl.value.trim().toLowerCase();
                var items = dropdownEl.querySelectorAll('.time-dropdown-item');
                items.forEach(function(el) {
                    var val = el.getAttribute('data-value').toLowerCase();
                    if (!query || val.indexOf(query) !== -1) {
                        el.classList.remove('hidden');
                    } else {
                        el.classList.add('hidden');
                    }
                });
                highlightedIndex = -1;
            }

            function scrollToNearest() {
                var minutes = parseTimeToMinutes(inputEl.value);
                if (minutes === null) minutes = getDefaultMinutes();
                // Find closest 30-min slot
                var closest = Math.round(minutes / 30) * 30;
                if (closest >= 1440) closest = 0;
                var target = formatMinutesToTime(closest);
                var items = getVisibleItems();
                for (var i = 0; i < items.length; i++) {
                    if (items[i].getAttribute('data-value') === target) {
                        items[i].scrollIntoView({ block: 'center' });
                        setHighlight(i);
                        return;
                    }
                }
            }

            inputEl.addEventListener('focus', function() {
                openDropdown();
            });

            inputEl.addEventListener('click', function() {
                if (!dropdownEl.classList.contains('open')) {
                    openDropdown();
                }
            });

            inputEl.addEventListener('input', function() {
                filterItems();
                if (dropdownEl.classList.contains('open')) {
                    var visible = getVisibleItems();
                    if (visible.length > 0) {
                        setHighlight(0);
                    }
                }
                updateHiddenFields();
            });

            inputEl.addEventListener('blur', function() {
                closeDropdown();
                normalizeTimeInput(inputEl);
                updateHiddenFields();
            });

            inputEl.addEventListener('keydown', function(e) {
                if (!dropdownEl.classList.contains('open')) return;
                var items = getVisibleItems();
                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    var next = highlightedIndex + 1;
                    if (next >= items.length) next = 0;
                    setHighlight(next);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    var prev = highlightedIndex - 1;
                    if (prev < 0) prev = items.length - 1;
                    setHighlight(prev);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (highlightedIndex >= 0 && highlightedIndex < items.length) {
                        inputEl.value = items[highlightedIndex].getAttribute('data-value');
                        closeDropdown();
                        normalizeTimeInput(inputEl);
                        updateHiddenFields();
                    }
                } else if (e.key === 'Escape' || e.key === 'Tab') {
                    closeDropdown();
                }
            });

            // Close on click outside
            document.addEventListener('mousedown', function(e) {
                if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
                    closeDropdown();
                }
            });
        }

        initTimePicker(startTimeEl, document.getElementById('start_time_dropdown'), function() { return 540; });
        initTimePicker(endTimeEl, document.getElementById('end_time_dropdown'), function() {
            var startMinutes = parseTimeToMinutes(startTimeEl.value);
            if (startMinutes === null) return 600;
            return (startMinutes + 60) % 1440;
        });
    });

    function onChangeCountry() {
        var selected = $('#venue_country').countrySelect('getSelectedCountryData');
        $('#venue_country_code').val(selected.iso2);
        app.venueCountryCode = selected.iso2;
    }

    function onChangeDateType() {
        if (typeof window.vueApp === 'undefined' || !window.vueApp.event) {
            return;
        }
        var value = $('input[name="schedule_type"]:checked').val();
        if (value == 'one_time') {
            window.vueApp.isRecurring = false;
        } else {
            window.vueApp.isRecurring = true;
            if (!window.vueApp.event.recurring_frequency) {
                window.vueApp.event.recurring_frequency = 'weekly';
            }
        }
        updateRecurringFieldVisibility();
    }

    function updateRecurringFieldVisibility() {
        if (typeof window.vueApp === 'undefined' || !window.vueApp.event) {
            return;
        }
        var freq = window.vueApp.event.recurring_frequency || 'weekly';
        var showDays = window.vueApp.isRecurring && (freq === 'weekly' || freq === 'every_n_weeks');
        if (showDays) {
            $('#days_of_week_div').show();
        } else {
            $('#days_of_week_div').hide();
        }
    }

    function onValidateClick() {
        $('#address_response').text(@json(__('messages.searching')) + '...').show();
        $('#accept_button').hide();
        var country = $('#venue_country').countrySelect('getSelectedCountryData');
        
        $.post({
            url: '{{ route('validate_address') }}',
            data: {
                _token: '{{ csrf_token() }}',
                address1: $('#venue_address1').val(),
                city: $('#venue_city').val(),
                state: $('#venue_state').val(),
                postal_code: $('#venue_postal_code').val(),                    
                country_code: country ? country.iso2 : '',
            },
            success: function(response) {
                if (response) {
                    var address = response['data']['formatted_address'];
                    $('#address_response').text(address);
                    $('#accept_button').show();
                    $('#address_response').data('validated_address', response['data']);
                } else {
                    $('#address_response').text(@json(__('messages.address_not_found')));
                }
            },
            error: function(xhr, status, error) {
                $('#address_response').text(@json(__('messages.an_error_occurred')) + ': ' + error);
            }
        });
    }

    function viewMap() {
        var address = [
            $('#venue_address1').val(),
            $('#venue_city').val(),
            $('#venue_state').val(),
            $('#venue_postal_code').val(),
            $('#venue_country').countrySelect('getSelectedCountryData').name
        ].filter(Boolean).join(', ');

        if (address) {
            var url = 'https://www.google.com/maps/search/?api=1&query=' + encodeURIComponent(address);
            window.open(url, '_blank');
        } else {
            alert(@json(__('messages.please_enter_address')));
        }
    }

    function acceptAddress(event) {
        event.preventDefault();
        var validatedAddress = $('#address_response').data('validated_address');
        if (validatedAddress) {
            $('#venue_address1').val(validatedAddress['address1']);
            $('#venue_city').val(validatedAddress['city']);
            $('#venue_state').val(validatedAddress['state']);
            $('#venue_postal_code').val(validatedAddress['postal_code']);
                        
            // Hide the address response and accept button after accepting
            $('#address_response').hide();
            $('#accept_button').hide();
        }
    }

    function clearFileInput(inputId) {
        var input = document.getElementById(inputId);
        input.value = '';
        // Directly hide preview instead of relying on change event
        var previewDiv = document.getElementById('image_preview');
        var filenameSpan = document.getElementById('flyer_image_filename');
        var warningElement = document.getElementById('image_size_warning');
        if (previewDiv) previewDiv.style.display = 'none';
        if (filenameSpan) filenameSpan.textContent = '';
        if (warningElement) warningElement.style.display = 'none';
    }

    function previewImage(input) {
        var preview = document.getElementById('preview_img');
        var previewDiv = document.getElementById('image_preview');
        var warningElement = document.getElementById('image_size_warning');
        var filenameSpan = document.getElementById('flyer_image_filename');

        if (input.files && input.files[0]) {
            if (filenameSpan) {
                filenameSpan.textContent = input.files[0].name;
            }
            var reader = new FileReader();

            reader.onload = function(e) {
                preview.src = e.target.result;
                previewDiv.style.display = 'inline-block';
            }

            reader.readAsDataURL(input.files[0]);

            // Check file size
            var fileSize = input.files[0].size / 1024 / 1024; // in MB
            if (fileSize > 2.5) {
                warningElement.textContent = @json(__('messages.image_size_warning'));
                warningElement.style.display = 'block';
            } else {
                warningElement.textContent = '';
                warningElement.style.display = 'none';
            }
        } else {
            if (filenameSpan) {
                filenameSpan.textContent = '';
            }
            preview.src = '#';
            previewDiv.style.display = 'none';
            warningElement.textContent = '';
            warningElement.style.display = 'none';
        }
    }

    function toggleEventSlugEdit() {
        const urlDisplay = document.getElementById('event-url-display');
        const slugEdit = document.getElementById('event-slug-edit');
        const editButton = document.getElementById('edit-slug-btn');
        const cancelButton = document.getElementById('cancel-slug-btn');

        if (urlDisplay.classList.contains('hidden')) {
            urlDisplay.classList.remove('hidden');
            slugEdit.classList.add('hidden');
            editButton.classList.remove('hidden');
            cancelButton.classList.add('hidden');
        } else {
            urlDisplay.classList.add('hidden');
            slugEdit.classList.remove('hidden');
            editButton.classList.add('hidden');
            cancelButton.classList.remove('hidden');
            document.getElementById('event_slug').focus();
        }
    }

    @php
        $eventEditUrl = $event->exists ? $event->getGuestUrl($subdomain, false, true) : '';
        if ($event->exists && $role->direct_registration && $event->registration_url) {
            if (str_contains($eventEditUrl, '?')) {
                $eventEditUrl = str_replace('?', '/?', $eventEditUrl);
            } else {
                $eventEditUrl .= '/';
            }
        }
    @endphp

    function copyEventUrl(button) {
        const url = '{{ $eventEditUrl }}';
        navigator.clipboard.writeText(url).then(() => {
            const originalHTML = button.innerHTML;
            button.innerHTML = `
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            `;
            setTimeout(() => {
                button.innerHTML = originalHTML;
            }, 2000);
        }).catch(() => {});
    }

    </script>

</x-slot>

<div id="app">

  <div class="pb-4 flex items-center justify-between">
    <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
      {{ $title }}
    </h2>

    <div class="hidden lg:flex items-center gap-3">
        {{-- Cancel button --}}
        <a href="{{ route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'schedule']) }}"
           class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
            {{ __('messages.cancel') }}
        </a>

        @if ($event->exists)
        {{-- Actions dropdown --}}
        <div class="relative inline-block text-start">
            <button type="button" class="popup-toggle inline-flex w-full justify-center rounded-md bg-white dark:bg-gray-800 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700" id="event-actions-menu-button" data-popup-target="event-actions-pop-up-menu" aria-expanded="true" aria-haspopup="true">
                {{ __('messages.actions') }}
                <svg class="-me-1 ms-2 h-6 w-6 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
            <div id="event-actions-pop-up-menu" class="pop-up-menu hidden absolute end-0 z-10 mt-2 w-64 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-gray-700 rounded-md bg-white dark:bg-gray-800 shadow-lg ring-1 ring-black ring-opacity-5 dark:ring-gray-600 focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="event-actions-menu-button" tabindex="-1">
                <div class="py-2" role="none" id="event-actions-pop-up-menu-items" data-popup-target="event-actions-pop-up-menu">
                    @if (config('services.meta.app_id'))
                    @if ($event->name && $event->starts_at && \Carbon\Carbon::parse($event->starts_at)->isFuture())
                    @php
                        $activeBoost = $event->boostCampaigns()->whereIn('status', ['active', 'paused'])->first();
                    @endphp
                    @if ($activeBoost)
                    <a href="{{ route('boost.show', ['hash' => $activeBoost->hashedId()]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="me-3 h-5 w-5 text-green-500" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
                        </svg>
                        <div>
                            {{ __('messages.boosted') }} - {{ number_format($activeBoost->reach) }} {{ __('messages.reach') }}
                        </div>
                    </a>
                    @else
                    <a href="{{ route('boost.create', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4Z"/>
                        </svg>
                        <div>
                            {{ __('messages.boost_event') }}
                        </div>
                    </a>
                    @endif
                    @endif
                    @endif
                    <a href="{{ route('event.clone', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                        <svg class="me-3 h-5 w-5 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                            <path d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                        </svg>
                        <div>
                            {{ __('messages.clone_event') }}
                        </div>
                    </a>
                    @if ($event->user_id == $user->id)
                    <div class="py-2" role="none">
                        <div class="border-t border-gray-100 dark:border-gray-700"></div>
                    </div>
                    <form method="POST" action="{{ route('event.delete', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" id="event-delete-form" class="block">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full group flex items-center px-5 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 focus:bg-red-50 dark:focus:bg-red-900/20 focus:text-red-700 dark:focus:text-red-300 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                            <svg class="me-3 h-5 w-5 text-red-400 group-hover:text-red-500 dark:group-hover:text-red-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                            </svg>
                            <div>
                                {{ __('messages.delete') }}
                            </div>
                        </button>
                    </form>
                    @endif
                </div>
            </div>
        </div>
        @endif
    </div>
  </div>

  <form method="POST"
        @submit="validateForm"
        action="{{ $event->exists ? route('event.update', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) : route('event.store', ['subdomain' => $subdomain]) }}"
        enctype="multipart/form-data"
        novalidate
        id="edit-form">

        @csrf

        @if ($event->exists)
        @method('put')
        @endif


        <x-text-input name="venue_name" type="hidden" v-model="venueName" />
        <x-text-input name="venue_email" type="hidden" v-model="venueEmail" />                                                                
        <x-text-input name="venue_address1" type="hidden" v-model="venueAddress1" />                                                                
        <x-text-input name="venue_city" type="hidden" v-model="venueCity" />                                                                
        <x-text-input name="venue_state" type="hidden" v-model="venueState" />                                                                
        <x-text-input name="venue_postal_code" type="hidden" v-model="venuePostalCode" />                                                                
        <x-text-input name="venue_country_code" type="hidden" v-model="venueCountryCode" />
        <x-text-input name="venue_website" type="hidden" v-model="venueWebsite" />

        <div class="py-5">
            <div class="mx-auto lg:grid lg:grid-cols-12 lg:gap-6">
                <!-- Sidebar Navigation (hidden on small screens, visible on lg+) -->
                <div class="hidden lg:block lg:col-span-3">
                    <div class="sticky top-6">
                        <nav class="space-y-1">
                            @if (! $role->isVenue() || $user->isMember($role->subdomain) || $user->canEditEvent($event))
                            <a href="#section-details" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-details">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                                </svg>
                                {{ __('messages.details') }}
                            </a>
                            @endif
                            <a href="#section-venue" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-venue">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-1.5-1.5v18m7.5-18v18" />
                                </svg>
                                {{ __('messages.venue') }}
                            </a>
                            <a href="#section-participants" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-participants">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                                </svg>
                                {{ __('messages.participants') }}
                            </a>
                            @if (! $role->isCurator())
                            <a href="#section-recurring" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-recurring">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                                </svg>
                                {{ __('messages.recurring') }}
                            </a>
                            @endif
                            <a href="#section-agenda" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-agenda">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                {{ __('messages.agenda') }}
                            </a>
                            @php
                                $curatorsForNav = $user->allCurators();
                                $curatorsForNav = $curatorsForNav->filter(function($curator) use ($subdomain) {
                                    return $curator->subdomain !== $subdomain;
                                });
                            @endphp
                            @if ($curatorsForNav->count() > 0)
                            <a href="#section-schedules" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-schedules">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                {{ __('messages.schedules') }}
                            </a>
                            @endif
                            @if ($event->user_id == $user->id)
                            <a href="#section-tickets" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-tickets">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                                {{ __('messages.tickets') }}
                            </a>
                            @endif
                            @if (count($role->getEventCustomFields()) > 0)
                            <a href="#section-custom-fields" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-custom-fields">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                                </svg>
                                {{ __('messages.custom_fields') }}
                            </a>
                            @endif
                            @if ($event->exists && $event->canBeSyncedToGoogleCalendarForSubdomain(request()->subdomain))
                            <a href="#section-google-calendar" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-google-calendar">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                                </svg>
                                {{ __('messages.google_calendar_sync') }}
                            </a>
                            @endif
                            @if ($event->exists)
                            @php $fanContentPendingCount = ($pendingVideos->count() ?? 0) + ($pendingComments->count() ?? 0); @endphp
                            <a href="#section-fan-content" class="section-nav-link flex items-center gap-2 px-3 py-3.5 text-lg font-medium text-gray-700 dark:text-gray-300 rounded-e-md hover:bg-gray-100 dark:hover:bg-gray-700 border-s-4 border-transparent" data-section="section-fan-content">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                                </svg>
                                {{ __('messages.fan_content') }}
                                @if ($fanContentPendingCount > 0)
                                <span class="inline-flex items-center justify-center w-5 h-5 text-xs font-bold text-white bg-red-500 rounded-full">{{ $fanContentPendingCount }}</span>
                                @endif
                            </a>
                            @endif
                        </nav>
                        <!-- Sidebar Save Button -->
                        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
                            <x-primary-button class="w-full justify-center">
                                {{ __('messages.save') }}
                            </x-primary-button>
                            @if (! $event->exists)
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-3 flex items-center justify-center gap-1.5">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-4.247m0 0A8.959 8.959 0 0 1 3 12c0-1.178.227-2.304.638-3.335" />
                                </svg>
                                {{ __('messages.note_all_events_are_publicly_listed') }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Main Content Area -->
                <div class="lg:col-span-9 space-y-6 lg:space-y-0">
                @if (! $role->isVenue() || $user->isMember($role->subdomain) || $user->canEditEvent($event))
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-details">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                        </svg>
                        {{ __('messages.details') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-details" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
                            </svg>
                            {{ __('messages.details') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label for="event_name" :value="__('messages.event_name') . ' *'" />
                            <x-text-input id="event_name" name="name" type="text" class="mt-1 block w-full"
                                :value="old('name', $event->name)"
                                v-model="eventName"
                                autofocus required autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                            @if ($event->exists)
                            <div id="event-url-display" class="text-sm text-gray-500 flex items-center gap-2">
                                <x-link href="{{ $eventEditUrl }}" target="_blank">
                                    {{ \App\Utils\UrlUtils::clean($eventEditUrl) }}
                                </x-link>
                                <button type="button" id="copy-event-url-btn" class="text-gray-500 hover:text-gray-700 dark:hover:text-gray-300" title="{{ __('messages.copy_url') }}">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M19,21H8V7H19M19,5H8A2,2 0 0,0 6,7V21A2,2 0 0,0 8,23H19A2,2 0 0,0 21,21V7A2,2 0 0,0 19,5M16,1H4A2,2 0 0,0 2,3V17H4V3H16V1Z" />
                                    </svg>
                                </button>
                            </div>
                            @if ($role->isCurator() || !($event->venue && $event->venue->isClaimed() && $event->role() && $event->role()->isClaimed()))
                            <div id="event-slug-edit" class="hidden">
                                <x-input-label for="event_slug" :value="__('messages.slug')" />
                                <x-text-input id="event_slug" name="slug" type="text" class="mt-1 block w-full"
                                    :value="old('slug', $event->slug)" />
                                <x-input-error class="mt-2" :messages="$errors->get('slug')" />
                            </div>
                            <x-secondary-button type="button" id="edit-slug-btn" class="mt-3">
                                {{ __('messages.edit') }}
                            </x-secondary-button>
                            <x-secondary-button type="button" id="cancel-slug-btn" class="hidden mt-3">
                                {{ __('messages.cancel') }}
                            </x-secondary-button>
                            @endif
                            @endif
                        </div>

                        @if($effectiveRole->groups && count($effectiveRole->groups))
                        <div class="mb-6">
                            <x-input-label for="current_role_group_id" :value="__('messages.schedule')" />
                            <select id="current_role_group_id" name="current_role_group_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ rtl_class($role, 'rtl', '', true) }}">
                                <option value="">{{ __('messages.please_select') }}</option>
                                @foreach($effectiveRole->groups as $group)
                                    @php
                                        $selectedGroupId = null;
                                        if ($event->exists) {
                                            $selectedGroupId = $event->getGroupIdForSubdomain($effectiveRole->subdomain);
                                            if ($selectedGroupId) {
                                                $selectedGroupId = \App\Utils\UrlUtils::encodeId($selectedGroupId);
                                            }
                                        }
                                    @endphp
                                    <option value="{{ \App\Utils\UrlUtils::encodeId($group->id) }}" {{ old('current_role_group_id', $selectedGroupId) == \App\Utils\UrlUtils::encodeId($group->id) ? 'selected' : '' }}>{{ $group->translatedName() }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('current_role_group_id')" />
                        </div>
                        @endif

                        <div class="mb-6">
                            <x-input-label for="category_id" :value="__('messages.category')" />
                            <select id="category_id" name="category_id" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ rtl_class($role, 'rtl', '', true) }}">
                                <option value="">{{ __('messages.please_select') }}</option>
                                @foreach(get_translated_categories() as $id => $label)
                                    <option value="{{ $id }}" {{ old('category_id', $event->category_id) == $id ? 'selected' : '' }}>{{ $label }}</option>
                                @endforeach
                            </select>
                            <x-input-error class="mt-2" :messages="$errors->get('category_id')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="event_date" :value="__('messages.date_and_time') . '*'" />
                            <div class="flex flex-wrap sm:flex-nowrap items-center gap-2 sm:gap-3 mt-1">
                                <input type="text" id="event_date"
                                    class="datepicker-date flex-1 min-w-[140px] border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                    value="{{ $eventDate }}" autocomplete="off" aria-label="{{ __('messages.date') }}" />
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="relative w-28 sm:w-32">
                                        <input type="text" id="start_time"
                                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                            value="{{ $eventStartTime }}" placeholder="{{ __('messages.start_time') }}"
                                            autocomplete="off" aria-label="{{ __('messages.start_time') }}" />
                                        <div class="time-dropdown" id="start_time_dropdown"></div>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400 text-sm shrink-0">{{ __('messages.to') }}</span>
                                    <div class="relative w-28 sm:w-32">
                                        <input type="text" id="end_time"
                                            class="w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                            value="{{ $eventEndTime }}" placeholder="{{ __('messages.end_time') }}"
                                            autocomplete="off" aria-label="{{ __('messages.end_time') }}" />
                                        <div class="time-dropdown" id="end_time_dropdown"></div>
                                    </div>
                                </div>
                            </div>
                            <input type="hidden" name="starts_at" id="starts_at" value="{{ $oldStartsAt }}" />
                            <input type="hidden" name="duration" id="duration" value="{{ $oldDuration }}" />
                            <x-input-error class="mt-2" :messages="$errors->get('starts_at')" />
                            <x-input-error class="mt-2" :messages="$errors->get('duration')" />
                        </div>
                        
                        <div class="mb-6">
                        <x-input-label :value="__('messages.flyer_image')" />
                        <input id="flyer_image" name="flyer_image" type="file" class="hidden"
                                accept="image/png, image/jpeg" />
                            <div id="flyer_image_choose" style="{{ $event->flyer_image_url ? 'display:none' : '' }}">
                                <div class="mt-1 flex items-center gap-3">
                                    <button type="button" id="flyer-choose-btn"
                                        class="inline-flex items-center px-3 py-1.5 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-700 dark:text-gray-200 text-sm font-medium rounded-md transition-colors border border-gray-300 dark:border-gray-600">
                                        <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                        </svg>
                                        {{ __('messages.choose_file') }}
                                    </button>
                                    <span id="flyer_image_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
                                </div>
                                <x-input-error class="mt-2" :messages="$errors->get('flyer_image')" />
                                <p id="image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
                                    {{ __('messages.image_size_warning') }}
                                </p>
                            </div>

                            <div id="image_preview" class="mt-3 relative inline-block" style="display: none;">
                                <img id="preview_img" src="#" alt="Preview" style="max-height:120px" class="rounded-md border border-gray-200 dark:border-gray-600" />
                                <button type="button" id="clear-flyer-preview-btn" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
                            </div>

                            @if ($event->flyer_image_url)
                            <div id="flyer_image_existing" class="relative inline-block mt-4 pt-1">
                                <img src="{{ $event->flyer_image_url }}" style="max-height:120px" class="rounded-md border border-gray-200 dark:border-gray-600" id="flyer_preview" />
                                <button type="button"
                                    id="delete-flyer-btn"
                                    data-url="{{ route('event.delete_image', ['subdomain' => $subdomain]) }}"
                                    data-hash="{{ \App\Utils\UrlUtils::encodeId($event->id) }}"
                                    data-token="{{ csrf_token() }}"
                                    style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                    class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                                    </svg>
                                </button>
                            </div>
                            @endif
                        </div>

                        <div class="mb-6">
                            <x-input-label for="short_description" :value="__('messages.short_description')" />
                            <x-text-input id="short_description" name="short_description" type="text" class="mt-1 block w-full" :value="old('short_description', $event->short_description)" maxlength="200" autocomplete="off" />
                            <x-input-error class="mt-2" :messages="$errors->get('short_description')" />
                        </div>

                        <div class="mb-6">
                            <x-input-label for="description" :value="__('messages.description')" />
                            <textarea id="description" name="description"
                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                autocomplete="off">{{ old('description', $event->description) }}</textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('description')" />
                        </div>

                    </div>
                </div>
                @endif
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-venue">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-1.5-1.5v18m7.5-18v18" />
                        </svg>
                        {{ __('messages.venue') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-venue" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 21h19.5m-18-18v18m10.5-18v18m6-13.5V21M6.75 6.75h.75m-.75 3h.75m-.75 3h.75m3-6h.75m-.75 3h.75m-.75 3h.75M6.75 21v-3.375c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125V21M3 3h12m-1.5-1.5v18m7.5-18v18" />
                            </svg>
                            {{ __('messages.venue') }}
                        </h2>

                        <div class="mb-6">
                            <fieldset>
                                <div class="flex items-center space-x-6">
                                    <div class="flex items-center">
                                        <input id="in_person" name="event_type" type="checkbox" v-model="isInPerson"
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                            :disabled="roleIsVenue"
                                            @change="onChangeVenueType('in_person')">
                                        <label for="in_person" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.in_person') }}
                                        </label>
                                    </div>
                                    <div class="flex items-center ps-3">
                                        <input id="online" name="event_type" type="checkbox" v-model="isOnline"
                                            class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                            @change="onChangeVenueType('online')">
                                        <label for="online" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                            {{ __('messages.online') }}
                                        </label>
                                    </div>
                                </div>
                            </fieldset>
                        </div>

                        <x-text-input name="venue_id" v-bind:value="selectedVenue.id" type="hidden" />

                        <div v-if="isInPerson">
                            <div v-if="!selectedVenue || showVenueAddressFields" class="mb-6">
                                <div v-if="!selectedVenue">
                                    <fieldset v-if="Object.keys(venues).length > 0">                                
                                        <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                            <div v-if="Object.keys(venues).length > 0" class="flex items-center">
                                                <input id="use_existing_venue" name="venue_type" type="radio" value="use_existing" v-model="venueType"
                                                    class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                                <label for="use_existing_venue"
                                                    class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="create_new_venue" name="venue_type" type="radio" value="create_new" v-model="venueType"
                                                    class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                                <label for="create_new_venue"
                                                    class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.create_new') }}</label>
                                            </div>
                                        </div>
                                    </fieldset>

                                    <div v-if="venueType === 'use_existing'">
                                        <select id="selected_venue"
                                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                                v-model="selectedVenue">
                                                <option value="" disabled selected>{{ __('messages.please_select') }}</option>                                
                                                <option v-for="venue in venues" :key="venue.id" :value="venue">
                                                    @{{ venue.name || venue.address1 }} <template v-if="venue.email">(@{{ venue.email }})</template>
                                                </option>
                                        </select>
                                    </div>
                                </div>

                                <div v-if="showAddressFields()">
                                    <div class="mb-6">
                                        <x-input-label for="venue_name" :value="__('messages.name')" />
                                        <x-text-input id="venue_name" name="venue_name" type="text"
                                            class="mt-1 block w-full" v-model="venueName" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_name')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_email" :value="__('messages.email')" />
                                        <div class="flex mt-1">
                                            <x-text-input id="venue_email" name="venue_email" type="email" class="block w-full"
                                                @blur="searchVenues" v-model="venueEmail" autocomplete="off" />
                                        </div>
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_email')" />
                                        @if (config('app.hosted'))
                                        <div v-if="(venueType === 'create_new' || (!selectedVenue.user_id && venueEmail)) && venueEmail" class="mt-2">
                                            <div class="flex items-center">
                                                <input id="send_email_to_venue" name="send_email_to_venue" type="checkbox" v-model="sendEmailToVenue"
                                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                <label for="send_email_to_venue" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.send_email_to_notify_them') }}
                                                </label>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_website" :value="__('messages.website')" />
                                        <x-text-input id="venue_website" name="venue_website" type="url"
                                            class="mt-1 block w-full" v-model="venueWebsite" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_website')" />
                                    </div>

                                    <div v-if="venueSearchResults.length" class="mb-6">
                                        <div class="space-y-2">
                                            <div v-for="venue in venueSearchResults" :key="venue.id" class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100 truncate">
                                                        <a :href="venue.url" target="_blank" class="hover:underline">@{{ venue.name }}</a>:
                                                        @{{ venue.address1 }}
                                                    </span>
                                                </div>
                                                <x-primary-button @click="selectVenue(venue)" type="button">
                                                    {{ __('messages.select') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_address1" :value="__('messages.street_address')" />
                                        <x-text-input id="venue_address1" name="venue_address1" type="text"
                                            class="mt-1 block w-full" v-model="venueAddress1" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_address1')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_city" :value="__('messages.city')" />
                                        <x-text-input id="venue_city" name="venue_city" type="text" class="mt-1 block w-full"
                                            v-model="venueCity" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_city')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_state" :value="__('messages.state_province')" />
                                        <x-text-input id="venue_state" name="venue_state" type="text" class="mt-1 block w-full"
                                            v-model="venueState" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_state')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_postal_code" :value="__('messages.postal_code')" />
                                        <x-text-input id="venue_postal_code" name="venue_postal_code" type="text"
                                            class="mt-1 block w-full" v-model="venuePostalCode" autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('venue_postal_code')" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="venue_country" :value="__('messages.country')" />
                                        <x-text-input id="venue_country" name="venue_country" type="text" class="mt-1 block w-full"
                                            autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('country')" />
                                        <input type="hidden" id="venue_country_code" name="venue_country_code" 
                                            v-model="venueCountryCode"/>
                                    </div>

                                    <div class="mb-6">
                                        <div class="flex items-center space-x-4">
                                            <x-secondary-button id="view_map_button">{{ __('messages.view_map') }}</x-secondary-button>
                                            @if (config('services.google.backend'))
                                            <x-secondary-button id="validate_button">{{ __('messages.validate_address') }}</x-secondary-button>
                                            <x-secondary-button id="accept_button" class="hidden">{{ __('messages.accept') }}</x-secondary-button>
                                            @endif
                                            <x-primary-button v-if="showVenueAddressFields" type="button" @click="updateSelectedVenue()">{{ __('messages.done') }}</x-primary-button>
                                        </div>
                                    </div>

                                    <div id="address_response" class="mb-6 hidden text-gray-900 dark:text-gray-100"></div>

                                </div>
                            </div>

              
                            <div v-else class="mb-6">
                                <div class="flex justify-between w-full">
                                    <div class="flex items-center">
                                        <span class="text-sm text-gray-900 dark:text-gray-100">
                                            <template v-if="selectedVenue.url">
                                                <a :href="selectedVenue.url" target="_blank" class="hover:underline">@{{ venueName || venueAddress1 }}</a>
                                            </template>
                                            <template v-else>
                                                @{{ venueName || venueAddress1 }}
                                            </template>
                                            <template v-if="venueEmail">
                                                (<a :href="'mailto:' + venueEmail" class="hover:underline">@{{ venueEmail }}</a>)
                                            </template>
                                        </span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <button v-if="!selectedVenue.user_id" @click="editSelectedVenue" type="button" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                            {{ __('messages.edit') }}
                                        </button>
                                        <button v-if="!roleIsVenue" @click="clearSelectedVenue" type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                            {{ __('messages.remove') }}
                                        </button>
                                    </div>
                                </div>
                            </div>                        

                        </div>

                        <div v-if="isOnline">
                            <x-input-label for="event_url" :value="__('messages.event_url')" />
                            <x-text-input id="event_url" name="event_url" type="url" class="mt-1 block w-full"
                                v-model="event.event_url" autocomplete="off" />
                            <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.event_url_help') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('event_url')" />
                        </div>
                        <div v-if="!isOnline">
                            <input type="hidden" name="event_url" value="" />
                        </div>
                    </div>
                </div>

                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-participants">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                        </svg>
                        {{ __('messages.participants') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-participants" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z" />
                            </svg>
                            {{ __('messages.participants') . ($role->isVenue() ? ' - ' . __('messages.optional') : '') }}
                            <span v-if="selectedMembers.length > 1">(@{{ selectedMembers.length }})</span>
                        </h2>

                        <div>
                            <div v-if="selectedMembers && selectedMembers.length > 0" class="mb-2">
                                <div v-for="member in selectedMembers" :key="member.id" class="flex items-center justify-between py-3 border-b border-gray-200 dark:border-gray-700 last:border-b-0">
                                    <input type="hidden" v-bind:name="'members[' + member.id + '][email]'" v-bind:value="member.email" />
                                    <div v-show="editMemberId === member.id" class="w-full">
                                        <div class="mb-6">
                                            <x-input-label :value="__('messages.name') . ' *'" />
                                            <x-text-input v-bind:id="'edit_member_name_' + member.id"
                                                v-bind:name="'members[' + member.id + '][name]'" type="text" class="block w-full"
                                                v-model="selectedMembers.find(m => m.id === member.id).name" v-bind:required="editMemberId === member.id"
                                                @keydown.enter.prevent="editMember()" autocomplete="off" />
                                            <x-input-error class="mt-2" :messages="$errors->get('member_name')" />
                                        </div>

                                        <div class="mb-6">
                                            <x-input-label for="edit_member_email" :value="__('messages.email')" />
                                            <x-text-input v-bind:id="'edit_member_email_' + member.id"
                                                v-bind:name="'members[' + member.id + '][email]'" type="email" class="me-2 block w-full"
                                                v-model="selectedMembers.find(m => m.id === member.id).email" @keydown.enter.prevent="editMember()" autocomplete="off" />
                                            @if (config('app.hosted'))
                                            <div v-if="selectedMembers.find(m => m.id === member.id).email && !member.user_id" class="mt-2">
                                                <div class="flex items-center">
                                                    <input type="checkbox"
                                                        :id="'send_email_to_edit_member_' + member.id"
                                                        :name="'send_email_to_members[' + selectedMembers.find(m => m.id === member.id).email + ']'"
                                                        v-model="sendEmailToMembers[selectedMembers.find(m => m.id === member.id).email]"
                                                        class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                    <label :for="'send_email_to_edit_member_' + member.id" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                        {{ __('messages.send_email_to_notify_them') }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endif
                                        </div>

                                        <div class="mb-6">
                                            <x-input-label for="edit_member_youtube_url" :value="__('messages.youtube_video_url')" />
                                            <x-text-input v-bind:id="'edit_member_youtube_url_' + member.id"
                                                v-bind:name="'members[' + member.id + '][youtube_url]'" type="url" class="me-2 block w-full"
                                                v-model="selectedMembers.find(m => m.id === member.id).youtube_url" @keydown.enter.prevent="editMember()" autocomplete="off" />
                                        </div>

                                        <x-primary-button @click="editMember()" type="button">
                                            {{ __('messages.done') }}
                                        </x-primary-button>

                                    </div>
                                    <div v-show="editMemberId !== member.id" class="flex justify-between w-full">
                                        <div class="flex-1">
                                            <div class="flex items-center">
                                                <span class="text-sm text-gray-900 dark:text-gray-100 truncate">
                                                    <template v-if="member.url">
                                                        <a :href="member.url" target="_blank" class="hover:underline">@{{ member.name }}</a>
                                                    </template>
                                                    <template v-else>
                                                        @{{ member.name }}
                                                    </template>
                                                    <template v-if="member.email">
                                                        (<a :href="'mailto:' + member.email" class="hover:underline">@{{ member.email }}</a>)
                                                    </template>
                                                </span>
                                                <a v-if="member.youtube_url" :href="member.youtube_url" target="_blank" class="ms-2">
                                                    <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                        <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                    </svg>
                                                </a>
                                            </div>
                                            @if (config('app.hosted'))
                                            <div v-if="((member.id && member.id.toString().startsWith('new_')) || (!member.user_id)) && member.email" class="mt-2">
                                                <div class="flex items-center">
                                                    <input type="checkbox" 
                                                        :id="'send_email_to_member_' + member.id" 
                                                        :name="'send_email_to_members[' + member.email + ']'" 
                                                        v-model="sendEmailToMembers[member.email]"
                                                        class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                    <label :for="'send_email_to_member_' + member.id" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                        {{ __('messages.send_email_to_notify_them') }}
                                                    </label>
                                                </div>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="flex items-center gap-3">
                                            <button v-if="!member.user_id" @click="editMember(member)" type="button" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                                {{ __('messages.edit') }}
                                            </button>
                                            <button v-if="!(roleIsTalent && member.id === roleEncodedId)" @click="removeMember(member)" type="button" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                {{ __('messages.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div v-if="!showMemberTypeRadio">
                                <button type="button" @click="showAddMemberForm" class="text-[#4E81FA] hover:text-blue-700 text-sm font-medium flex items-center gap-1">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-4 h-4">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
                                    </svg>
                                    {{ __('messages.add') }}
                                </button>
                            </div>

                            <div v-if="showMemberTypeRadio">
                                <fieldset v-if="filteredMembers.length > 0">
                                    <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                        <div class="flex items-center">
                                            <input id="use_existing_members" name="member_type" type="radio" value="use_existing" v-model="memberType"
                                                class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                            <label for="use_existing_members"
                                                class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                        </div>
                                        <div class="flex items-center">
                                            <input id="create_new_members" name="member_type" type="radio" value="create_new" v-model="memberType"
                                                class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                            <label for="create_new_members"
                                                class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.create_new') }}</label>
                                        </div>
                                    </div>
                                </fieldset>

                                <div v-if="memberType === 'use_existing' && Object.keys(members).length > 0">
                                    <select v-model="selectedMember" @change="addExistingMember" id="selected_member"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="" disabled selected>{{ __('messages.please_select') }}</option>
                                        <option v-for="member in filteredMembers" :key="member.id" :value="member">
                                            @{{ member.name }} <template v-if="member.email">(@{{ member.email }})</template>
                                        </option>
                                    </select>
                                </div>

                                <div v-if="memberType === 'create_new' || filteredMembers.length === 0">
                                    <div class="mb-6">
                                        <x-input-label for="member_name" :value="__('messages.name') . ' *'" />
                                        <x-text-input id="member_name" @keydown.enter.prevent="addMember"
                                            v-model="memberName" type="text" class="mt-1 block w-full" :required="false" autocomplete="off" />
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="member_email" :value="__('messages.email')" />
                                        <div class="flex mt-1">
                                            <x-text-input id="member_email" type="email" class="me-2 block w-full"
                                            @keydown.enter.prevent="addMember" @blur="searchMembers" v-model="memberEmail" autocomplete="off" />
                                        </div>
                                        <x-input-error class="mt-2" :messages="$errors->get('member_email')" />
                                        @if (config('app.hosted'))
                                        <div v-if="memberEmail" class="mt-2">
                                            <div class="flex items-center">
                                                <input id="send_email_to_new_member"
                                                    type="checkbox"
                                                    v-model="sendEmailToNewMember"
                                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                <label for="send_email_to_new_member" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.send_email_to_notify_them') }}
                                                </label>
                                            </div>
                                        </div>
                                        @endif
                                    </div>

                                    <div v-if="memberSearchResults.length" class="mb-6">
                                        <div class="space-y-2">
                                            <div v-for="member in memberSearchResults" :key="member.id" class="flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <span class="text-sm text-gray-900 dark:text-gray-100">
                                                        <a :href="member.url" target="_blank" class="hover:underline">@{{ member.name }}</a>
                                                        <template v-if="member.email">
                                                            (<a :href="'mailto:' + member.email" class="hover:underline">@{{ member.email }}</a>)
                                                        </template>
                                                    </span>
                                                    <a v-if="member.youtube_url" :href="member.youtube_url" target="_blank" class="ms-2">
                                                        <svg class="w-5 h-5 text-red-600" fill="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                                                            <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                        </svg>
                                                    </a>
                                                </div>
                                                <x-primary-button @click="selectMember(member)" type="button">
                                                    {{ __('messages.select') }}
                                                </x-primary-button>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mb-6">
                                        <x-input-label for="member_youtube_url" :value="__('messages.youtube_video_url')" />
                                        <x-text-input id="member_youtube_url" @keydown.enter.prevent="addMember"
                                            v-model="memberYoutubeUrl" type="url" class="me-2 block w-full" autocomplete="off" />
                                    </div>

                                    <div class="flex gap-2">
                                        <x-primary-button id="add-member-btn" type="button" @click="addMember">
                                            {{ __('messages.done') }}
                                        </x-primary-button>
                                        <x-secondary-button v-if="selectedMembers.length > 0" type="button" @click="cancelAddMember">
                                            {{ __('messages.cancel') }}
                                        </x-secondary-button>
                                    </div>

                                </div>

                                <div v-if="memberType === 'use_existing' && filteredMembers.length > 0" class="mt-4">
                                    <x-secondary-button v-if="selectedMembers.length > 0" type="button" @click="cancelAddMember">
                                        {{ __('messages.cancel') }}
                                    </x-secondary-button>
                                </div>

                            </div>
                        </div>
                    </div>
                </div>

                @if (! $role->isCurator())
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-recurring">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                        </svg>
                        {{ __('messages.recurring') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-recurring" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.023 9.348h4.992v-.001M2.985 19.644v-4.992m0 0h4.992m-4.993 0l3.181 3.183a8.25 8.25 0 0013.803-3.7M4.031 9.865a8.25 8.25 0 0113.803-3.7l3.181 3.182m0-4.991v4.99" />
                            </svg>
                            {{ __('messages.recurring') }}
                        </h2>

                        <div class="mb-6 space-y-4 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                            <div class="flex items-center">
                                <input id="one_time" name="schedule_type" type="radio" value="one_time" {{ $event->days_of_week ? '' : 'CHECKED' }}
                                    class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                <label for="one_time"
                                    class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.one_time') }}</label>
                            </div>
                            <div class="flex items-center">
                                <input id="recurring" name="schedule_type" type="radio" value="recurring" {{ $event->days_of_week ? 'CHECKED' : '' }}
                                    class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                <label for="recurring"
                                    class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.recurring') }}</label>
                            </div>
                        </div>

                        <div v-if="isRecurring" class="mb-6">
                            <x-input-label :value="__('messages.frequency')" />
                            <select name="recurring_frequency" v-model="event.recurring_frequency" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm">
                                <option value="daily">{{ __('messages.daily') }}</option>
                                <option value="weekly">{{ __('messages.weekly') }}</option>
                                <option value="every_n_weeks">{{ __('messages.every_n_weeks') }}</option>
                                <option value="monthly_date">{{ __('messages.monthly_same_date') }}</option>
                                <option value="monthly_weekday">{{ __('messages.monthly_same_weekday') }}</option>
                                <option value="yearly">{{ __('messages.yearly') }}</option>
                            </select>
                        </div>

                        <div v-if="isRecurring && event.recurring_frequency === 'every_n_weeks'" class="mb-6">
                            <x-input-label :value="__('messages.repeat_every_n_weeks')" />
                            <x-text-input type="number" name="recurring_interval" class="mt-1 block w-full" min="2" max="52"
                                v-model="event.recurring_interval" />
                        </div>

                        <div id="days_of_week_div" class="mb-6 {{ ! $event || ! $event->days_of_week || !in_array($event->recurring_frequency, ['weekly', 'every_n_weeks', null]) ? 'hidden' : '' }}">
                            <x-input-label :value="__('messages.days_of_week')" />
                            @foreach (['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'] as $index => $day)
                            <label for="days_of_week_{{ $index }}" class="me-3 text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">
                                <input type="checkbox" id="days_of_week_{{ $index }}" name="days_of_week_{{ $index }}" class="h-4 w-4 rounded border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]"
                                    {{ $event && $event->days_of_week && $event->days_of_week[$index] == '1' ? 'checked' : '' }}/> &nbsp;
                                {{ __('messages.' . $day) }}
                            </label>
                            @endforeach
                        </div>

                        <div v-if="isRecurring" id="recurring_end_div" class="mb-6">
                            <x-input-label :value="__('messages.recurring_end')" />
                            <div class="mt-2 space-y-4">
                                <div class="flex items-center">
                                    <input id="recurring_end_never" name="recurring_end_type" type="radio" value="never" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                    <label for="recurring_end_never"
                                        class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.never') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="recurring_end_on_date" name="recurring_end_type" type="radio" value="on_date" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                    <label for="recurring_end_on_date"
                                        class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.on_date') }}</label>
                                </div>
                                <div v-if="event.recurring_end_type === 'on_date'" class="ms-7">
                                    <x-text-input type="text" id="recurring_end_date" name="recurring_end_value" class="datepicker-end-date mt-1 block w-full"
                                        value="{{ old('recurring_end_value', $event->recurring_end_value) }}"
                                        autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('recurring_end_value')" />
                                </div>
                                <div class="flex items-center">
                                    <input id="recurring_end_after_events" name="recurring_end_type" type="radio" value="after_events" v-model="event.recurring_end_type"
                                        class="h-4 w-4 border-gray-300 text-[#4E81FA] focus:ring-[#4E81FA]">
                                    <label for="recurring_end_after_events"
                                        class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100 cursor-pointer">{{ __('messages.after_events') }}</label>
                                </div>
                                <div v-if="event.recurring_end_type === 'after_events'" class="ms-7">
                                    <x-text-input type="number" id="recurring_end_count" name="recurring_end_value" class="mt-1 block w-full"
                                        :value="old('recurring_end_value', $event->recurring_end_value)"
                                        v-model="event.recurring_end_value"
                                        min="1" autocomplete="off" />
                                    <x-input-error class="mt-2" :messages="$errors->get('recurring_end_value')" />
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
                @endif

                <!-- Agenda Section -->
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-agenda">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                        </svg>
                        {{ __('messages.agenda') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-agenda" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl {{ auth()->check() && auth()->user()->isRtl() ? 'rtl' : '' }}">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                            </svg>
                            {{ __('messages.agenda') }}
                        </h2>

                        <!-- Compact mode: drag-drop layout when times AND description are both hidden -->
                        <div v-if="!agendaShowTimes && !agendaShowDescription" @dragover.prevent="onContainerPartDragOver($event)" @drop="onPartDrop()" class="space-y-2">
                            <div v-for="(part, index) in eventParts" :key="part.uid"
                                 draggable="true"
                                 @dragstart="onPartDragStart(index)"
                                 @dragover="onPartDragOver(index, $event)"
                                 @drop="onPartDrop()"
                                 @dragend="onPartDragEnd"
                                 :class="{ 'opacity-50': partDragIndex === index }"
                                 :style="{ marginTop: partDropTargetIndex === index && partDragIndex !== null && partDragIndex !== index ? '2.5rem' : '', transition: 'margin 150ms ease' }"
                                 class="flex items-center gap-2 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg p-3">
                                <!-- Drag handle -->
                                <div class="cursor-grab text-gray-400 dark:text-gray-500">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 16 16">
                                        <circle cx="5.5" cy="3.5" r="1.5"/>
                                        <circle cx="10.5" cy="3.5" r="1.5"/>
                                        <circle cx="5.5" cy="8" r="1.5"/>
                                        <circle cx="10.5" cy="8" r="1.5"/>
                                        <circle cx="5.5" cy="12.5" r="1.5"/>
                                        <circle cx="10.5" cy="12.5" r="1.5"/>
                                    </svg>
                                </div>
                                <!-- Name input -->
                                <input type="text" v-model="part.name" :name="'event_parts[' + index + '][name]'" required
                                       class="flex-1 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" />
                                <!-- Hidden fields -->
                                <input type="hidden" :name="'event_parts[' + index + '][id]'" :value="part.id || ''" />
                                <input type="hidden" :name="'event_parts[' + index + '][start_time]'" :value="part.start_time" />
                                <input type="hidden" :name="'event_parts[' + index + '][end_time]'" :value="part.end_time" />
                                <input type="hidden" :name="'event_parts[' + index + '][description]'" :value="part.description" />
                                <!-- Remove button -->
                                <button type="button" @click="removePart(index)" class="text-red-400 hover:text-red-600 p-1" title="{{ __('messages.remove') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                            </div>
                        </div>

                        <!-- Full mode: card layout with Up/Down buttons when times OR description are shown -->
                        <div v-else class="space-y-4">
                            <template v-for="(part, index) in eventParts" :key="part.uid">
                                <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-4">
                                    <div class="flex items-center justify-between mb-3">
                                        <span class="text-sm font-medium text-gray-500 dark:text-gray-400">#@{{ index + 1 }}</span>
                                        <div class="flex items-center gap-1.5">
                                            <button type="button" @click="movePartUp(index)" :disabled="index === 0" class="inline-flex items-center justify-center gap-1 px-2 py-1 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-30 disabled:cursor-not-allowed">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 15.75l7.5-7.5 7.5 7.5" />
                                                </svg>
                                                {{ __('messages.up') }}
                                            </button>
                                            <button type="button" @click="movePartDown(index)" :disabled="index === eventParts.length - 1" class="inline-flex items-center justify-center gap-1 px-2 py-1 text-xs font-medium rounded-md border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-700 hover:bg-gray-50 dark:hover:bg-gray-600 disabled:opacity-30 disabled:cursor-not-allowed">
                                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-3.5 h-3.5">
                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" />
                                                </svg>
                                                {{ __('messages.down') }}
                                            </button>
                                            <button type="button" @click="removePart(index)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                {{ __('messages.remove') }}
                                            </button>
                                        </div>
                                    </div>
                                    <div class="mb-3">
                                        <x-input-label :value="__('messages.part_name') . ' *'" />
                                        <input type="text" v-model="part.name" :name="'event_parts[' + index + '][name]'" required class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" />
                                    </div>
                                    <div v-if="agendaShowTimes" class="grid grid-cols-2 gap-3 mb-3">
                                        <div class="relative">
                                            <x-input-label :value="__('messages.start_time')" />
                                            <input type="text"
                                                   :value="formatPartTime(part.start_time)"
                                                   @focus="initPartTimePickerOnFocus($event, part.uid, 'start')"
                                                   @change="onPartTimeChange(index, 'start_time', $event)"
                                                   class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" />
                                            <input type="hidden" :name="'event_parts[' + index + '][start_time]'" :value="part.start_time" />
                                            <div class="time-dropdown" :ref="'part_start_dropdown_' + part.uid"></div>
                                        </div>
                                        <div class="relative">
                                            <x-input-label :value="__('messages.end_time')" />
                                            <input type="text"
                                                   :value="formatPartTime(part.end_time)"
                                                   @focus="initPartTimePickerOnFocus($event, part.uid, 'end')"
                                                   @change="onPartTimeChange(index, 'end_time', $event)"
                                                   class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" />
                                            <input type="hidden" :name="'event_parts[' + index + '][end_time]'" :value="part.end_time" />
                                            <div class="time-dropdown" :ref="'part_end_dropdown_' + part.uid"></div>
                                        </div>
                                    </div>
                                    <div v-if="agendaShowDescription">
                                        <x-input-label :value="__('messages.description')" />
                                        <textarea :ref="'partDescription_' + part.uid"
                                                  :name="'event_parts[' + index + '][description]'"
                                                  rows="2"
                                                  class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">@{{ part.description }}</textarea>
                                    </div>
                                    <input type="hidden" :name="'event_parts[' + index + '][id]'" :value="part.id || ''" />
                                </div>
                            </template>
                        </div>

                        <div class="mt-4 flex flex-wrap items-center justify-between gap-2">
                            <button type="button" @click="addPart" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                + {{ __('messages.add_part') }}
                            </button>

                            @if (config('services.google.gemini_key'))
                            <div class="flex flex-wrap gap-2">
                                <x-secondary-button type="button" @click="$refs.partsImageInput.click()" v-bind:disabled="parsingParts">
                                    <svg class="w-4 h-4 ltr:mr-1.5 rtl:ml-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                                    </svg>
                                    <span v-if="parsingParts">{{ __('messages.parsing_image') }}</span>
                                    <span v-else>{{ __('messages.import_from_image') }}</span>
                                </x-secondary-button>
                                <input type="file" ref="partsImageInput" @change="parsePartsFromImage($event)" accept="image/*" class="hidden" />

                                <x-secondary-button type="button" @click="showPartsTextInput = !showPartsTextInput">
                                    {{ __('messages.import_from_text') }}
                                </x-secondary-button>
                            </div>
                            @endif
                        </div>

                        @if (config('services.google.gemini_key'))
                        <div v-if="showPartsTextInput" class="mt-4">
                            <textarea v-model="partsText" rows="4" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" placeholder="{{ __('messages.paste_setlist_or_agenda') }}"></textarea>
                            <div class="mt-2">
                                <x-secondary-button type="button" @click="parsePartsFromText" v-bind:disabled="parsingParts || !partsText">
                                    <span v-if="parsingParts">{{ __('messages.parsing_image') }}</span>
                                    <span v-else>{{ __('messages.import_from_text') }}</span>
                                </x-secondary-button>
                            </div>
                        </div>

                        <div class="mt-4">
                            <x-input-label :value="__('messages.ai_agenda_prompt')" />
                            <textarea v-model="partsAiPrompt" rows="3" maxlength="500" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm" dir="auto"
                                placeholder="{{ __('messages.ai_agenda_prompt_placeholder') }}"></textarea>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.ai_agenda_prompt_help') }}</p>
                            <input type="hidden" name="agenda_ai_prompt" :value="partsAiPrompt" />
                            <input type="hidden" name="save_ai_prompt_default" :value="savePartsAiPromptDefault ? '1' : '0'" />
                        </div>

                        <div class="mt-6 space-y-3">
                            <label class="flex items-center">
                                <input type="checkbox" v-model="agendaShowTimes" class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.show_times') }}</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="agendaShowDescription" class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.show_description') }}</span>
                            </label>
                            <label class="flex items-center">
                                <input type="checkbox" v-model="savePartsAiPromptDefault" class="rounded border-gray-300 dark:border-gray-700 text-[#4E81FA] shadow-sm focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] dark:focus:ring-offset-gray-800" />
                                <span class="ms-2 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.save_as_default') }}</span>
                            </label>
                        </div>
                        <input type="hidden" name="agenda_show_times" :value="agendaShowTimes ? '1' : '0'" />
                        <input type="hidden" name="agenda_show_description" :value="agendaShowDescription ? '1' : '0'" />

                        <!-- Preview Modal -->
                        <div v-if="showPartsPreview" class="mt-4 border border-blue-200 dark:border-blue-800 rounded-lg p-4 bg-blue-50 dark:bg-blue-900/20">
                            <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3">{{ __('messages.preview_parts') }}</h3>
                            <div class="space-y-2 mb-4">
                                <div v-for="(part, index) in parsedPartsPreview" :key="index" class="flex items-center gap-2 text-sm text-gray-700 dark:text-gray-300">
                                    <span class="font-medium text-gray-500">@{{ index + 1 }}.</span>
                                    <span>@{{ part.name }}</span>
                                    <span v-if="part.start_time" class="text-gray-400">(@{{ part.start_time }}<span v-if="part.end_time"> - @{{ part.end_time }}</span>)</span>
                                </div>
                            </div>
                            <div class="flex gap-2">
                                <x-secondary-button type="button" @click="acceptParsedParts" class="!bg-blue-600 !text-white hover:!bg-blue-700">
                                    {{ __('messages.accept_parts') }}
                                </x-secondary-button>
                                <x-secondary-button type="button" @click="showPartsPreview = false; parsedPartsPreview = []">
                                    {{ __('messages.discard') }}
                                </x-secondary-button>
                            </div>
                        </div>
                        @endif
                    </div>
                </div>

                @php
                    $curators = $user->allCurators();
                    $curators = $curators->filter(function($curator) use ($subdomain) {
                        return $curator->subdomain !== $subdomain;
                    });
                @endphp

                @if ($curators->count() > 0)
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-schedules">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        {{ __('messages.schedules') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-schedules" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                            </svg>
                            {{ __('messages.schedules') }}
                        </h2>

                        <div class="mb-6">
                            <x-input-label class="mb-2" for="curators" :value="__(count($curators) > 1 ? 'messages.add_to_schedules' : 'messages.add_to_schedule')" />
                            
                            @foreach($curators as $curator)
                            @php
                                $isClonedCurator = isset($clonedCurators) && $clonedCurators->contains(function($c) use ($curator) { return $c->id == $curator->id; });
                                $isCuratorChecked = (! $event->exists && ($role->subdomain == $curator->subdomain || session('pending_request') == $curator->subdomain)) || $event->curators->contains($curator->id) || $isClonedCurator;
                            @endphp
                            <div class="mb-4">
                                <div class="flex items-center mb-2 h-6">
                                    <input type="checkbox" 
                                           id="curator_{{ $curator->encodeId() }}" 
                                           name="curators[]" 
                                           value="{{ $curator->encodeId() }}"
                                           {{ $isCuratorChecked ? 'checked' : '' }}
                                           class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                           @change="toggleCuratorGroupSelection('{{ $curator->encodeId() }}')">
                                    <label for="curator_{{ $curator->encodeId() }}" class="ms-2 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                        {{ $curator->name }}
                                    </label>
                                    <div class="ms-2 flex-shrink-0">
                                        @if($curator->accept_requests && $curator->request_terms)
                                        <div class="relative group">
                                            <button type="button" class="text-blue-500 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300 focus:outline-none">
                                                <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd" />
                                                </svg>
                                            </button>
                                            <div class="absolute bottom-full start-1/2 transform -translate-x-1/2 mb-2 px-4 py-3 bg-gray-900 dark:bg-gray-700 text-white text-sm rounded-lg opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 w-[28rem] max-w-lg z-10">
                                                <div class="leading-relaxed" 
                                                     dir="{{ is_rtl() ? 'rtl' : 'ltr' }}"
                                                     style="{{ is_rtl() ? 'text-align: right;' : 'text-align: left;' }}">{!! nl2br(e($curator->translatedRequestTerms())) !!}</div>
                                                <div class="absolute top-full start-1/2 transform -translate-x-1/2 w-0 h-0 border-s-4 border-e-4 border-t-4 border-transparent border-t-gray-900 dark:border-t-gray-700"></div>
                                            </div>
                                        </div>
                                        @else
                                        <div class="w-4 h-4"></div>
                                        @endif
                                    </div>
                                </div>
                                
                                @if($curator->groups && count($curator->groups) > 0)
                                <div id="curator_group_{{ $curator->encodeId() }}" class="ms-6 mb-2" style="display: {{ $isCuratorChecked ? 'block' : 'none' }};">
                                    <select id="curator_group_{{ $curator->encodeId() }}" 
                                            name="curator_groups[{{ $curator->encodeId() }}]" 
                                            class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="">{{ __('messages.please_select') }}</option>
                                        @foreach($curator->groups as $group)
                                            @php
                                                $selectedGroupId = null;
                                                if ($event->exists) {
                                                    $selectedGroupId = $event->getGroupIdForSubdomain($curator->subdomain);
                                                    if ($selectedGroupId) {
                                                        $selectedGroupId = \App\Utils\UrlUtils::encodeId($selectedGroupId);
                                                    }
                                                } elseif (isset($clonedCuratorGroups) && isset($clonedCuratorGroups[$curator->encodeId()])) {
                                                    $selectedGroupId = $clonedCuratorGroups[$curator->encodeId()];
                                                }
                                            @endphp
                                            <option value="{{ \App\Utils\UrlUtils::encodeId($group->id) }}" {{ old('curator_groups.' . $curator->encodeId(), $selectedGroupId) == \App\Utils\UrlUtils::encodeId($group->id) ? 'selected' : '' }}>{{ $group->translatedName() }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                @endif

                    @if ($event->user_id == $user->id)
                    <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-tickets">
                        <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                            </svg>
                            {{ __('messages.tickets') }}
                        </span>
                        <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                        </svg>
                    </button>
                    <div id="section-tickets" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                        <div class="max-w-xl">                                                
                            <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
                                </svg>
                                {{ __('messages.tickets') }}
                            </h2>

                            <div class="mb-6">
                                <div class="flex items-center">
                                    <input id="tickets_enabled" name="tickets_enabled" type="checkbox" v-model="event.tickets_enabled" :value="1"
                                        class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                        {{ ! $role->isPro() ? 'disabled' : '' }}>
                                    <input type="hidden" name="tickets_enabled" :value="event.tickets_enabled ? 1 : 0" >
                                    <label for="tickets_enabled" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                        {{ __('messages.enable_tickets') }}
                                        @if (! $role->isPro())
                                        <div class="text-xs pt-1">
                                            <x-link href="{{ route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'plan']) }}" target="_blank">
                                                {{ __('messages.requires_pro_plan') }}
                                            </x-link>
                                        </div>
                                        @endif
                                    </label>
                                </div>
                            </div>

                            @if ($role->isPro() && $user->isMember($subdomain))
                            <div class="flex items-center mt-3" v-show="event.tickets_enabled">
                                <input id="save_default_tickets" name="save_default_tickets" type="checkbox"
                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                <label for="save_default_tickets" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                    {{ __('messages.save_as_default') }}
                                </label>
                            </div>
                            @endif

                            <!-- Registration URL (only visible when tickets are disabled) -->
                            <div class="mb-6" v-show="!event.tickets_enabled">
                                <x-input-label for="registration_url" :value="__('messages.registration_url')" />
                                <x-text-input id="registration_url" name="registration_url" type="url" class="mt-1 block w-full"
                                    v-model="event.registration_url" />
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.registration_url_help') }}</p>
                            </div>

                            <!-- External Event Price (only visible when tickets are disabled) -->
                            <div class="mb-6" v-show="!event.tickets_enabled">
                                <x-input-label :value="__('messages.price')" />
                                <div class="mt-1 flex gap-3">
                                    <select name="ticket_currency_code" v-model="event.ticket_currency_code"
                                        class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        @foreach ($currencies as $currency)
                                        @if ($loop->index == 2)
                                        <option disabled>──────</option>
                                        @endif
                                        <option value="{{ $currency->value }}">{{ $currency->value }}</option>
                                        @endforeach
                                    </select>
                                    <x-text-input type="number" name="ticket_price" step="0.01" min="0"
                                        class="flex-1" v-model="event.ticket_price" />
                                </div>
                                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.external_price_help') }}</p>
                            </div>

                            <div class="mb-6" v-show="!event.tickets_enabled">
                                <x-input-label for="coupon_code" :value="__('messages.coupon_code')" />
                                <x-text-input id="coupon_code" name="coupon_code" type="text" class="mt-1 block w-full"
                                    v-model="event.coupon_code" maxlength="255" />
                            </div>

                            @if ($role->isPro())
                            <div v-show="event.tickets_enabled">

                                <!-- Ticket Section Tabs -->
                                <div class="mt-6 mb-6 border-b border-gray-200 dark:border-gray-700">
                                    <nav class="-mb-px flex space-x-2 sm:space-x-6">
                                        <button type="button" @click="activeTicketTab = 'tickets'"
                                            :class="activeTicketTab === 'tickets' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                            class="flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium">
                                            {{ __('messages.general') }}
                                        </button>
                                        <button type="button" @click="activeTicketTab = 'payment'"
                                            :class="activeTicketTab === 'payment' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                            class="flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium">
                                            {{ __('messages.payment') }}
                                        </button>
                                        <button type="button" @click="activeTicketTab = 'options'"
                                            :class="activeTicketTab === 'options' ? 'border-[#4E81FA] text-[#4E81FA]' : 'border-transparent text-gray-500 dark:text-gray-400 hover:border-gray-300 hover:text-gray-700 dark:hover:text-gray-300'"
                                            class="flex-1 sm:flex-initial text-center whitespace-nowrap border-b-2 pb-3 px-1 text-sm font-medium">
                                            {{ __('messages.options') }}
                                        </button>
                                    </nav>
                                </div>

                                <!-- Payment Tab -->
                                <div v-show="activeTicketTab === 'payment'">
                                @if ($user->canAcceptStripePayments() || $user->invoiceninja_api_key || $user->payment_url)
                                <div class="mb-6">
                                    <x-input-label for="payment_method" :value="__('messages.payment_method')"/>
                                    <select id="payment_method" name="payment_method" v-model="event.payment_method" :required="event.tickets_enabled"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        <option value="cash">@lang('messages.cash')</option>
                                        @if ($user->stripe_completed_at)
                                        <option value="stripe">Stripe - {{ $user->stripe_company_name }}</option>
                                        @elseif ($user->canAcceptStripePayments())
                                        <option value="stripe">Stripe</option>
                                        @endif
                                        @if ($user->invoiceninja_api_key)
                                        <option value="invoiceninja">Invoice Ninja - {{ $user->invoiceninja_company_name }}</option>
                                        @endif
                                        @if ($user->payment_url)
                                        <option value="payment_url">{{ __('messages.payment_url') }} - {{ $user->paymentUrlHost() }}</option>
                                        @endif
                                    </select>
                                    <div class="text-xs pt-1">
                                        <x-link href="{{ route('profile.edit') }}#section-payment-methods" target="_blank">
                                            {{ __('messages.manage_payment_methods') }}
                                        </x-link>
                                    </div>
                                </div>
                                @endif

                                <div class="mb-6">
                                    <x-input-label for="ticket_currency_code" :value="__('messages.currency')"/>
                                    <select id="ticket_currency_code" name="ticket_currency_code" v-model="event.ticket_currency_code" :required="event.tickets_enabled"
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                        @foreach ($currencies as $currency)
                                        @if ($loop->index == 2)
                                        <option disabled>──────────</option>
                                        @endif
                                        <option value="{{ $currency->value }}" {{ $event->ticket_currency_code == $currency->value ? 'selected' : '' }}>
                                            {{ $currency->value }} - {{ $currency->label }}
                                        </option>
                                        @endforeach
                                    </select>
                                    @if (! $user->canAcceptStripePayments() && ! $user->invoiceninja_api_key && ! $user->payment_url)
                                    <div class="text-xs pt-1">
                                        <x-link href="{{ route('profile.edit') }}#section-payment-methods" target="_blank">
                                            {{ __('messages.manage_payment_methods') }}
                                        </x-link>
                                    </div>
                                    @endif
                                </div>

                                <div class="mb-6" v-show="event.payment_method == 'cash'">
                                    <x-input-label for="payment_instructions" :value="__('messages.payment_instructions')" />
                                    <textarea id="payment_instructions" name="payment_instructions" v-model="event.payment_instructions" rows="4"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                </div>
                                </div>

                                <!-- Options Tab -->
                                <div v-show="activeTicketTab === 'options'">
                                <!-- Event-level Custom Fields -->
                                <div class="mb-6">
                                    <x-input-label :value="__('messages.custom_fields') . ' (' . __('messages.per_order') . ')'" class="mb-3" />

                                    <div v-if="eventCustomFields && Object.keys(eventCustomFields).length > 0">
                                        <div v-for="(field, fieldKey) in eventCustomFields" :key="fieldKey" class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-md">
                                            <div class="grid grid-cols-2 gap-2">
                                                <div>
                                                    <x-input-label :value="__('messages.field_name') . ' *'" class="text-xs" />
                                                    <x-text-input type="text" v-model="field.name" class="mt-1 block w-full text-sm" v-bind:required="event.tickets_enabled" v-bind:class="{ 'border-red-500': formSubmitAttempted && !field.name }" />
                                                    <p v-if="formSubmitAttempted && !field.name" class="mt-1 text-xs text-red-600">{{ __('messages.field_name_required') }}</p>
                                                </div>
                                                <div>
                                                    <x-input-label :value="__('messages.field_type')" class="text-xs" />
                                                    <select v-model="field.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                                        <option value="string">{{ __('messages.type_string') }}</option>
                                                        <option value="multiline_string">{{ __('messages.type_multiline_string') }}</option>
                                                        <option value="switch">{{ __('messages.type_switch') }}</option>
                                                        <option value="date">{{ __('messages.type_date') }}</option>
                                                        <option value="dropdown">{{ __('messages.type_dropdown') }}</option>
                                                    </select>
                                                </div>
                                            </div>
                                            @if($role->language_code !== 'en')
                                            <div class="mt-2">
                                                <x-input-label :value="__('messages.english_name')" class="text-xs" />
                                                <x-text-input type="text" v-model="field.name_en" class="mt-1 block w-full text-sm" :placeholder="__('messages.auto_translated_placeholder')" />
                                            </div>
                                            @endif
                                            <div class="mt-2" v-if="field.type === 'dropdown'">
                                                <x-input-label :value="__('messages.field_options')" class="text-xs" />
                                                <x-text-input type="text" v-model="field.options" class="mt-1 block w-full text-sm" :placeholder="__('messages.options_placeholder')" />
                                            </div>
                                            <div class="mt-2 flex items-center justify-between">
                                                <div class="flex items-center">
                                                    <input type="checkbox" v-model="field.required" :id="`event_field_required_${fieldKey}`" class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                    <label :for="`event_field_required_${fieldKey}`" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                                </div>
                                                <button type="button" @click="removeEventCustomField(fieldKey)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <input type="hidden" name="custom_fields" :value="JSON.stringify(eventCustomFields || {})">
                                    <button type="button" @click="addEventCustomField" class="mt-2 text-sm text-[#4E81FA] hover:text-blue-700" v-if="getEventCustomFieldCount() < 8">
                                        + {{ __('messages.add_field') }}
                                    </button>
                                </div>
                                </div>

                                <!-- Tickets Tab -->
                                <div v-show="activeTicketTab === 'tickets'">
                                <div class="mb-6">
                                    <div v-for="(ticket, index) in tickets" :key="index" 
                                        :class="{'mt-4 p-4 border border-gray-300 dark:border-gray-700 rounded-lg': tickets.length > 1, 'mt-4': tickets.length === 1}">
                                        <input type="hidden" v-bind:name="`tickets[${index}][id]`" v-model="ticket.id">
                                        <div class="grid grid-cols-2 gap-4">
                                            <div>
                                                <x-input-label :value="__('messages.price')" />
                                                <x-text-input type="number" step="0.01" v-bind:name="`tickets[${index}][price]`" 
                                                    v-model="ticket.price" class="mt-1 block w-full" placeholder="{{ __('messages.free') }}" />
                                            </div>
                                            <div>
                                                <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.quantity') }} @{{ (! isRecurring && ticket.sold && Object.values(JSON.parse(ticket.sold))[0] > 0) ? (' - ' + Object.values(JSON.parse(ticket.sold))[0] + ' ' +soldLabel) : '' }}</label>
                                                <x-text-input type="number" v-bind:name="`tickets[${index}][quantity]`" 
                                                    v-model="ticket.quantity" class="mt-1 block w-full" placeholder="{{ __('messages.unlimited') }}" />
                                            </div>
                                            <div v-if="tickets.length > 1">
                                                <x-input-label :value="__('messages.type') . ' *'" />
                                                <x-text-input v-bind:name="`tickets[${index}][type]`" v-model="ticket.type"
                                                    class="mt-1 block w-full" v-bind:required="event.tickets_enabled" v-bind:class="{ 'border-red-500': formSubmitAttempted && tickets.length > 1 && !ticket.type }" />
                                                <p v-if="formSubmitAttempted && tickets.length > 1 && !ticket.type" class="mt-1 text-xs text-red-600">{{ __('messages.ticket_type_required') }}</p>
                                            </div>
                                            <div v-if="tickets.length > 1" class="flex items-end gap-3">
                                                <button type="button" @click="addTicketCustomField(index)" class="mt-1 text-sm text-[#4E81FA] hover:text-blue-700" v-if="getTicketCustomFieldCount(index) < 8">
                                                    + {{ __('messages.add_field') }}
                                                </button>
                                                <button type="button" @click="removeTicket(index)" class="mt-1 text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                    {{ __('messages.remove') }}
                                                </button>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <x-input-label :value="__('messages.description')" />
                                            <textarea v-bind:name="`tickets[${index}][description]`" v-model="ticket.description" rows="4"
                                                class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                        </div>

                                        <!-- Ticket-level Custom Fields -->
                                        <div class="mt-4" v-if="ticket.custom_fields && Object.keys(ticket.custom_fields).length > 0">
                                            <x-input-label :value="__('messages.custom_fields') . ' (' . __('messages.per_ticket') . ')'" />
                                            <div v-for="(field, fieldKey) in ticket.custom_fields" :key="fieldKey" class="mt-2 p-3 border border-gray-200 dark:border-gray-600 rounded-md">
                                                <div class="grid grid-cols-2 gap-2">
                                                    <div>
                                                        <x-input-label :value="__('messages.field_name') . ' *'" class="text-xs" />
                                                        <x-text-input type="text" v-model="field.name" class="mt-1 block w-full text-sm" v-bind:required="event.tickets_enabled" v-bind:class="{ 'border-red-500': formSubmitAttempted && !field.name }" />
                                                        <p v-if="formSubmitAttempted && !field.name" class="mt-1 text-xs text-red-600">{{ __('messages.field_name_required') }}</p>
                                                    </div>
                                                    <div>
                                                        <x-input-label :value="__('messages.field_type')" class="text-xs" />
                                                        <select v-model="field.type" class="mt-1 block w-full text-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                                            <option value="string">{{ __('messages.type_string') }}</option>
                                                            <option value="multiline_string">{{ __('messages.type_multiline_string') }}</option>
                                                            <option value="switch">{{ __('messages.type_switch') }}</option>
                                                            <option value="date">{{ __('messages.type_date') }}</option>
                                                            <option value="dropdown">{{ __('messages.type_dropdown') }}</option>
                                                        </select>
                                                    </div>
                                                </div>
                                                @if($role->language_code !== 'en')
                                                <div class="mt-2">
                                                    <x-input-label :value="__('messages.english_name')" class="text-xs" />
                                                    <x-text-input type="text" v-model="field.name_en" class="mt-1 block w-full text-sm" :placeholder="__('messages.auto_translated_placeholder')" />
                                                </div>
                                                @endif
                                                <div class="mt-2" v-if="field.type === 'dropdown'">
                                                    <x-input-label :value="__('messages.field_options')" class="text-xs" />
                                                    <x-text-input type="text" v-model="field.options" class="mt-1 block w-full text-sm" :placeholder="__('messages.options_placeholder')" />
                                                </div>
                                                <div class="mt-2 flex items-center justify-between">
                                                    <div class="flex items-center">
                                                        <input type="checkbox" v-model="field.required" :id="`ticket_${index}_field_required_${fieldKey}`" class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded">
                                                        <label :for="`ticket_${index}_field_required_${fieldKey}`" class="ms-2 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">{{ __('messages.field_required') }}</label>
                                                    </div>
                                                    <button type="button" @click="removeTicketCustomField(index, fieldKey)" class="text-red-600 hover:text-red-800 dark:text-red-400 text-sm">
                                                        {{ __('messages.remove') }}
                                                    </button>
                                                </div>
                                            </div>
                                        </div>
                                        <input type="hidden" v-bind:name="`tickets[${index}][custom_fields]`" :value="JSON.stringify(ticket.custom_fields || {})">
                                    </div>

                                    <!-- Total Tickets Mode Selection -->
                                    <div v-if="hasSameTicketQuantities && tickets.length > 1" class="mt-6 p-4 border rounded-lg bg-gray-50 dark:bg-gray-800">
                                        <div class="space-y-3">
                                            <div class="flex items-center">
                                                <input id="total_tickets_individual" name="total_tickets_mode" type="radio"
                                                    value="individual" v-model="event.total_tickets_mode"
                                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300">
                                                <label for="total_tickets_individual" class="ms-3 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.individual_quantities') }} (@{{ getTotalTicketQuantity }} total)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        {{ __('messages.individual_quantities_help') }}
                                                    </p>
                                                </label>
                                            </div>
                                            <div class="flex items-center">
                                                <input id="total_tickets_combined" name="total_tickets_mode" type="radio"
                                                    value="combined" v-model="event.total_tickets_mode"
                                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300">
                                                <label for="total_tickets_combined" class="ms-3 block text-sm font-medium text-gray-900 dark:text-gray-100">
                                                    {{ __('messages.combined_total') }} (@{{ getCombinedTotalQuantity }} total)
                                                    <p class="text-xs text-gray-600 dark:text-gray-400">
                                                        {{ __('messages.combined_total_help') }}
                                                    </p>
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="flex gap-2 mt-4">
                                        <button type="button" @click="addTicket" class="text-sm text-[#4E81FA] hover:text-blue-700">
                                            + {{ __('messages.add_type') }}
                                        </button>
                                    </div>
                                </div>
                                </div>

                                <!-- Options Tab (continued) -->
                                <div v-show="activeTicketTab === 'options'">
                                <div class="mb-6">
                                    <x-input-label for="ticket_notes" :value="__('messages.ticket_notes')" />
                                    <textarea id="ticket_notes" name="ticket_notes" v-model="event.ticket_notes" rows="4"
                                        class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"></textarea>
                                </div>

                                <div class="mb-6">
                                    <x-input-label for="terms_url" :value="__('messages.terms_url')" />
                                    <x-text-input id="terms_url" name="terms_url" type="url" class="mt-1 block w-full"
                                        :value="old('terms_url', $event->terms_url)"
                                        v-model="event.terms_url" />
                                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                        {{ __('messages.terms_url_help') }}
                                    </p>
                                </div>

                                <div v-if="hasLimitedPaidTickets">
                                    <div class="mb-6">
                                        <div class="flex items-center">
                                            <input id="expire_unpaid_tickets_checkbox" name="expire_unpaid_tickets_checkbox" type="checkbox"
                                                v-model="showExpireUnpaid"
                                                class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                                @change="toggleExpireUnpaid">
                                            <label for="expire_unpaid_tickets_checkbox" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                                                {{ __('messages.expire_unpaid_tickets') }}
                                            </label>
                                        </div>
                                    </div>

                                    <div class="mb-6" v-if="showExpireUnpaid">
                                        <x-input-label for="expire_unpaid_tickets" :value="__('messages.after_number_of_hours')" />
                                        <x-text-input id="expire_unpaid_tickets" name="expire_unpaid_tickets" type="number" class="mt-1 block w-full"
                                            :value="old('expire_unpaid_tickets', $event->expire_unpaid_tickets)"
                                            v-model="event.expire_unpaid_tickets"
                                            autocomplete="off" />
                                        <x-input-error class="mt-2" :messages="$errors->get('expire_unpaid_tickets')" />
                                    </div>
                                    <div v-else>
                                        <input type="hidden" name="expire_unpaid_tickets" value="0"/>
                                    </div>
                                </div>
                                </div>
                            </div>
                            @endif
                        </div>
                    </div>
                @endif

                @if (count($role->getEventCustomFields()) > 0)
                <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-custom-fields">
                    <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                        </svg>
                        {{ __('messages.custom_fields') }}
                    </span>
                    <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                    </svg>
                </button>
                <div id="section-custom-fields" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                    <div class="max-w-xl">
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                            </svg>
                            {{ __('messages.custom_fields') }}
                        </h2>

                        @php
                            $eventCustomFields = $role->getEventCustomFields();
                            $customFieldValues = $event->getCustomFieldValues();
                        @endphp

                        @foreach($eventCustomFields as $fieldKey => $field)
                        <div class="mb-6">
                            <x-input-label for="custom_field_{{ $fieldKey }}" :value="((app()->getLocale() === 'en' && !empty($field['name_en'])) ? $field['name_en'] : $field['name']) . (!empty($field['required']) ? ' *' : '')" />

                            @if(($field['type'] ?? 'string') === 'string')
                            <x-text-input
                                id="custom_field_{{ $fieldKey }}"
                                name="custom_field_values[{{ $fieldKey }}]"
                                type="text"
                                class="mt-1 block w-full"
                                :value="old('custom_field_values.' . $fieldKey, $customFieldValues[$fieldKey] ?? '')"
                                :required="!empty($field['required'])" />
                            @elseif(($field['type'] ?? '') === 'multiline_string')
                            <textarea
                                id="custom_field_{{ $fieldKey }}"
                                name="custom_field_values[{{ $fieldKey }}]"
                                rows="3"
                                dir="auto"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                {{ !empty($field['required']) ? 'required' : '' }}>{{ old('custom_field_values.' . $fieldKey, $customFieldValues[$fieldKey] ?? '') }}</textarea>
                            @elseif(($field['type'] ?? '') === 'switch')
                            <div class="mt-2">
                                <input type="hidden" name="custom_field_values[{{ $fieldKey }}]" value="0" />
                                <input type="checkbox"
                                    id="custom_field_{{ $fieldKey }}"
                                    name="custom_field_values[{{ $fieldKey }}]"
                                    value="1"
                                    class="h-4 w-4 text-[#4E81FA] focus:ring-[#4E81FA] border-gray-300 rounded"
                                    {{ old('custom_field_values.' . $fieldKey, $customFieldValues[$fieldKey] ?? '') ? 'checked' : '' }} />
                            </div>
                            @elseif(($field['type'] ?? '') === 'date')
                            <x-text-input
                                id="custom_field_{{ $fieldKey }}"
                                name="custom_field_values[{{ $fieldKey }}]"
                                type="date"
                                class="mt-1 block w-full"
                                :value="old('custom_field_values.' . $fieldKey, $customFieldValues[$fieldKey] ?? '')"
                                :required="!empty($field['required'])" />
                            @elseif(($field['type'] ?? '') === 'dropdown')
                            <select
                                id="custom_field_{{ $fieldKey }}"
                                name="custom_field_values[{{ $fieldKey }}]"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm"
                                {{ !empty($field['required']) ? 'required' : '' }}>
                                <option value="">{{ __('messages.select') }}...</option>
                                @foreach(explode(',', $field['options'] ?? '') as $option)
                                    @php $option = trim($option); @endphp
                                    @if($option)
                                    <option value="{{ $option }}" {{ old('custom_field_values.' . $fieldKey, $customFieldValues[$fieldKey] ?? '') === $option ? 'selected' : '' }}>
                                        {{ $option }}
                                    </option>
                                    @endif
                                @endforeach
                            </select>
                            @endif

                            <x-input-error class="mt-2" :messages="$errors->get('custom_field_values.' . $fieldKey)" />
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

            @if ($event->exists && $event->canBeSyncedToGoogleCalendarForSubdomain(request()->subdomain))
            <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-google-calendar">
                <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                    </svg>
                    {{ __('messages.google_calendar_sync') }}
                </span>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="section-google-calendar" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                <div class="max-w-xl">                                                
                    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-6 flex items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
                        </svg>
                        {{ __('messages.google_calendar_sync') }}
                    </h2>
                    <p class="text-sm text-gray-600 dark:text-gray-400 mb-6">
                        {{ __('messages.sync_this_event_description') }}
                    </p>
                
                    <div class="flex items-center space-x-4">
                        @if ($event->isSyncedToGoogleCalendarForSubdomain(request()->subdomain))
                            <div class="flex items-center text-green-600 dark:text-green-400">
                                <svg class="w-4 h-4 me-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">{{ __('messages.synced_to_google_calendar') }}</span>
                            </div>
                            <x-secondary-button type="button" id="unsync-event-btn" data-subdomain="{{ $subdomain }}" data-event-id="{{ $event->id }}">
                                {{ __('messages.remove_from_google_calendar') }}
                            </x-secondary-button>
                        @else
                            <div class="flex items-center text-gray-500 dark:text-gray-400">
                                <svg class="w-4 h-4 me-2" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M3 4a1 1 0 011-1h12a1 1 0 011 1v2a1 1 0 01-1 1H4a1 1 0 01-1-1V4zm0 4a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H4a1 1 0 01-1-1V8zm8 0a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1V8zm0 4a1 1 0 011-1h4a1 1 0 011 1v2a1 1 0 01-1 1h-4a1 1 0 01-1-1v-2z" clip-rule="evenodd"></path>
                                </svg>
                                <span class="text-sm">{{ __('messages.not_synced_to_google_calendar') }}</span>
                            </div>
                            <x-primary-button type="button" id="sync-event-btn" data-subdomain="{{ $subdomain }}" data-event-id="{{ $event->id }}">
                                {{ __('messages.sync_to_google_calendar') }}
                            </x-primary-button>
                        @endif
                    </div>
                
                    <div id="sync-status-{{ $event->id }}" class="hidden mt-3">
                        <div class="flex items-center text-blue-600 dark:text-blue-400">
                            <svg class="animate-spin -ms-1 me-3 h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span class="text-sm">{{ __('messages.syncing') }}</span>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            @if ($event->exists)
            <button type="button" class="mobile-section-header lg:hidden w-full flex items-center justify-between px-4 py-3 bg-white dark:bg-gray-800 border border-gray-200 dark:border-gray-700 rounded-lg mb-2 shadow-sm" data-section="section-fan-content">
                <span class="flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 0 0 3.741-.479 3 3 0 0 0-4.682-2.72m.94 3.198.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0 1 12 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 0 1 6 18.719m12 0a5.971 5.971 0 0 0-.941-3.197m0 0A5.995 5.995 0 0 0 12 12.75a5.995 5.995 0 0 0-5.058 2.772m0 0a3 3 0 0 0-4.681 2.72 8.986 8.986 0 0 0 3.74.477m.94-3.197a5.971 5.971 0 0 0-.94 3.197M15 6.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Zm6 3a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Zm-13.5 0a2.25 2.25 0 1 1-4.5 0 2.25 2.25 0 0 1 4.5 0Z" />
                    </svg>
                    {{ __('messages.fan_content') }}
                </span>
                <svg class="w-5 h-5 text-gray-400 transition-transform duration-200 accordion-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                </svg>
            </button>
            <div id="section-fan-content" class="section-content p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg lg:mt-0">
                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                    {{ __('messages.fan_content') }}
                </h2>

                @if ($pendingVideos->count() == 0 && $pendingComments->count() == 0 && $approvedVideos->count() == 0 && $approvedComments->count() == 0)
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.no_fan_content') }}</p>
                @else

                {{-- Pending Section --}}
                @if ($pendingVideos->count() > 0 || $pendingComments->count() > 0)
                <div class="mb-8">
                    <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('messages.pending_approval') }}</h3>
                    <div class="space-y-4">
                        @foreach ($pendingVideos as $video)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                            <div class="flex-1">
                                <div class="rounded overflow-hidden mb-2">
                                    <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($video->youtube_url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $video->eventPart ? $video->eventPart->name : __('messages.general') }}
                                    @if ($video->event_date)
                                    &middot; {{ \Carbon\Carbon::parse($video->event_date)->format('M j, Y') }}
                                    @endif
                                    &middot; {{ __('messages.submitted_by') }} {{ $video->user?->name }}
                                </p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <button type="submit" form="form-approve-video-{{ $video->id }}" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">{{ __('messages.approve') }}</button>
                                <button type="submit" form="form-reject-video-{{ $video->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                            </div>
                        </div>
                        @endforeach

                        @foreach ($pendingComments as $comment)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-yellow-50 dark:bg-yellow-900/20 rounded-lg border border-yellow-200 dark:border-yellow-800">
                            <div class="flex-1">
                                <p class="text-gray-800 dark:text-gray-200">{{ $comment->comment }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ $comment->eventPart ? $comment->eventPart->name : __('messages.general') }}
                                    @if ($comment->event_date)
                                    &middot; {{ \Carbon\Carbon::parse($comment->event_date)->format('M j, Y') }}
                                    @endif
                                    &middot; {{ __('messages.submitted_by') }} {{ $comment->user?->name }}
                                </p>
                            </div>
                            <div class="flex gap-2 shrink-0">
                                <button type="submit" form="form-approve-comment-{{ $comment->id }}" class="px-3 py-1.5 text-sm bg-green-600 text-white rounded hover:bg-green-700">{{ __('messages.approve') }}</button>
                                <button type="submit" form="form-reject-comment-{{ $comment->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                {{-- Approved Section --}}
                @if ($approvedVideos->count() > 0 || $approvedComments->count() > 0)
                <div>
                    <h3 class="text-md font-semibold text-gray-800 dark:text-gray-200 mb-4">{{ __('messages.approved_content') }}</h3>
                    <div class="space-y-4">
                        @foreach ($approvedVideos as $video)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-1">
                                <div class="rounded overflow-hidden mb-2">
                                    <iframe class="w-full" style="aspect-ratio:16/9" src="{{ \App\Utils\UrlUtils::getYouTubeEmbed($video->youtube_url) }}" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" referrerpolicy="strict-origin-when-cross-origin" allowfullscreen loading="lazy"></iframe>
                                </div>
                                <p class="text-sm text-gray-600 dark:text-gray-400">
                                    {{ $video->eventPart ? $video->eventPart->name : __('messages.general') }}
                                    @if ($video->event_date)
                                    &middot; {{ \Carbon\Carbon::parse($video->event_date)->format('M j, Y') }}
                                    @endif
                                    &middot; {{ __('messages.submitted_by') }} {{ $video->user?->name }}
                                </p>
                            </div>
                            <div class="shrink-0">
                                <button type="submit" form="form-reject-video-{{ $video->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                            </div>
                        </div>
                        @endforeach

                        @foreach ($approvedComments as $comment)
                        <div class="flex flex-col sm:flex-row sm:items-center gap-3 p-4 bg-gray-50 dark:bg-gray-700 rounded-lg">
                            <div class="flex-1">
                                <p class="text-gray-800 dark:text-gray-200">{{ $comment->comment }}</p>
                                <p class="text-sm text-gray-600 dark:text-gray-400 mt-1">
                                    {{ $comment->eventPart ? $comment->eventPart->name : __('messages.general') }}
                                    @if ($comment->event_date)
                                    &middot; {{ \Carbon\Carbon::parse($comment->event_date)->format('M j, Y') }}
                                    @endif
                                    &middot; {{ __('messages.submitted_by') }} {{ $comment->user?->name }}
                                </p>
                            </div>
                            <div class="shrink-0">
                                <button type="submit" form="form-reject-comment-{{ $comment->id }}" class="px-3 py-1.5 text-sm bg-red-600 text-white rounded hover:bg-red-700">{{ __('messages.reject') }}</button>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif

                @endif
            </div>
            @endif

                </div> <!-- End of main content area -->
            </div> <!-- End of grid container -->

        <!-- Spacer for mobile fixed buttons -->
        <div class="lg:hidden h-24"></div>

        <!-- Mobile Fixed Save Bar -->
        <div class="lg:hidden fixed bottom-0 inset-x-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-5 py-3 z-40 shadow-lg"
             style="padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
            @if (! $event->exists)
            <p class="text-sm text-gray-500 dark:text-gray-400 mb-3 flex items-center justify-center gap-1.5">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 shrink-0">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 21a9.004 9.004 0 0 0 8.716-6.747M12 21a9.004 9.004 0 0 1-8.716-6.747M12 21c2.485 0 4.5-4.03 4.5-9S14.485 3 12 3m0 18c-2.485 0-4.5-4.03-4.5-9S9.515 3 12 3m0 0a8.997 8.997 0 0 1 7.843 4.582M12 3a8.997 8.997 0 0 0-7.843 4.582m15.686 0A11.953 11.953 0 0 1 12 10.5c-2.998 0-5.74-1.1-7.843-2.918m15.686 0A8.959 8.959 0 0 1 21 12c0 .778-.099 1.533-.284 2.253m0 0A17.919 17.919 0 0 1 12 16.5a17.92 17.92 0 0 1-8.716-4.247m0 0A8.959 8.959 0 0 1 3 12c0-1.178.227-2.304.638-3.335" />
                </svg>
                {{ __('messages.note_all_events_are_publicly_listed') }}
            </p>
            @endif
            <div class="flex gap-3 justify-center max-w-lg mx-auto">
                <x-primary-button class="flex-1 justify-center">
                    {{ __('messages.save') }}
                </x-primary-button>
                <x-cancel-button class="flex-1 justify-center" />
            </div>
        </div>

    </form>

    {{-- External forms for fan content approve/reject buttons (outside main form to avoid nesting) --}}
    @if ($event->exists)
        @foreach ($pendingVideos as $video)
        <form id="form-approve-video-{{ $video->id }}" method="POST" action="{{ route('event.approve_video', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($video->id)]) }}" class="hidden">@csrf</form>
        <form id="form-reject-video-{{ $video->id }}" method="POST" action="{{ route('event.reject_video', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($video->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($pendingComments as $comment)
        <form id="form-approve-comment-{{ $comment->id }}" method="POST" action="{{ route('event.approve_comment', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($comment->id)]) }}" class="hidden">@csrf</form>
        <form id="form-reject-comment-{{ $comment->id }}" method="POST" action="{{ route('event.reject_comment', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($comment->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($approvedVideos as $video)
        <form id="form-reject-video-{{ $video->id }}" method="POST" action="{{ route('event.reject_video', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($video->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
        @foreach ($approvedComments as $comment)
        <form id="form-reject-comment-{{ $comment->id }}" method="POST" action="{{ route('event.reject_comment', ['subdomain' => $subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($comment->id)]) }}" class="hidden">@csrf @method('DELETE')</form>
        @endforeach
    @endif

</div>

<script {!! nonce_attr() !!}>
  const { createApp, ref } = Vue

  app = createApp({
    data() {
      return {
        event: {
          ...@json($event),
          tickets_enabled: {{ $event->tickets_enabled ? 'true' : 'false' }},
          total_tickets_mode: @json($event->total_tickets_mode ?? 'individual'),
          recurring_end_type: @json($event->recurring_end_type ?? 'never'),
          recurring_end_value: @json($event->recurring_end_value ?? null),
          recurring_frequency: @json($event->recurring_frequency ?? 'weekly'),
          recurring_interval: @json($event->recurring_interval ?? 2),
        },
        venues: @json($venues),
        members: @json($members ?? []),
        venueType: "{{ count($venues) > 0 ? 'use_existing' : 'create_new' }}",
        memberType: "{{ 'use_existing' }}",
        venueName: @json($selectedVenue ? $selectedVenue->name : ''),
        venueEmail: @json($selectedVenue ? $selectedVenue->email : ''),
        venueAddress1: @json($selectedVenue ? $selectedVenue->address1 : ''),
        venueCity: @json($selectedVenue ? $selectedVenue->city : ''),
        venueState: @json($selectedVenue ? $selectedVenue->state : ''),
        venuePostalCode: @json($selectedVenue ? $selectedVenue->postal_code : ''),
        venueCountryCode: @json($selectedVenue ? $selectedVenue->country_code : ''),
        venueWebsite: @json($selectedVenue ? $selectedVenue->website : ''),
        venueSearchEmail: "",
        venueSearchResults: [],
        selectedVenue: @json($selectedVenue ? $selectedVenue->toData() : ""),
        roleIsVenue: {{ $role->isVenue() ? 'true' : 'false' }},
        roleIsTalent: {{ $role->isTalent() ? 'true' : 'false' }},
        roleEncodedId: '{{ \App\Utils\UrlUtils::encodeId($role->id) }}',
        selectedMembers: @json($selectedMembers ?? []),
        memberSearchResults: [],
        selectedMember: "",
        editMemberId: "",
        memberEmail: "",
        memberName: "",
        memberYoutubeUrl: "",
        showMemberTypeRadio: @json(empty($selectedMembers)),
        showVenueAddressFields: false,
        isInPerson: false,
        isOnline: false,
        eventName: @json($event->name ?? ''),
        tickets: @json($event->tickets ?? [new Ticket()]).map(ticket => ({
          ...ticket,
          custom_fields: ticket.custom_fields || {},
          /*
          price: new Intl.NumberFormat('{{ app()->getLocale() }}', {
            style: 'currency',
            currency: '{{ $event->ticket_currency_code ?? "USD" }}'
          }).format(ticket.price).toString().replace(/[^\d.,]/g, '')
          */
         price: new Intl.NumberFormat('{{ app()->getLocale() }}', {
            style: 'decimal',
            minimumFractionDigits: 2,
            maximumFractionDigits: 2
          }).format(ticket.price)
        })),
        eventCustomFields: @json($event->custom_fields ?? []),
        showExpireUnpaid: @json($event->expire_unpaid_tickets > 0),
        activeTicketTab: 'tickets',
        formSubmitAttempted: false,
        isDirty: false,
        soldLabel: @json(__('messages.sold_reserved')),
        isRecurring: @json($event->days_of_week ? true : false),
        sendEmailToVenue: false,
        sendEmailToMembers: {},
        sendEmailToNewMember: false,
        eventParts: @json($event->exists ? $event->parts : ($clonedParts ?? [])).map((part, i) => ({
          uid: i,
          id: part.id || '',
          name: part.name || '',
          description: part.description || '',
          start_time: part.start_time || '',
          end_time: part.end_time || '',
        })),
        partUidCounter: @json(count($event->exists ? $event->parts : ($clonedParts ?? []))),
        parsingParts: false,
        parsedPartsPreview: [],
        showPartsPreview: false,
        showPartsTextInput: false,
        partsText: '',
        partsAiPrompt: @json($event->agenda_ai_prompt ?? $role->agenda_ai_prompt ?? ''),
        savePartsAiPromptDefault: false,
        agendaShowTimes: @json($role->agenda_show_times ?? true),
        agendaShowDescription: @json($role->agenda_show_description ?? true),
        partDragIndex: null,
        partDropTargetIndex: null,
        partEditors: {},
      }
    },
    methods: {
      clearSelectedVenue() {
        this.selectedVenue = "";
      },
      editSelectedVenue() {
        this.showVenueAddressFields = true;
        

        this.$nextTick(() => {
            $("#venue_country").countrySelect({
                defaultCountry: this.venueCountryCode,
            });
        });
      },
      updateSelectedVenue() {
        this.showVenueAddressFields = false;
      },
      searchVenues() {
        if (! this.venueEmail) {
          return;
        }

        const emailInput = document.getElementById('venue_email');
        
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        fetch(`{{ url('/search_roles') }}?type=venue&search=${encodeURIComponent(this.venueEmail)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          this.venueSearchResults = data;
        })
        .catch(error => {
          console.error('Error searching venues:', error);
        });
      },
      selectVenue(venue) {
        this.selectedVenue = venue;
        this.venueName = venue.name;
        this.venueEmail = venue.email;
        this.venueAddress1 = venue.address1;
        this.venueCity = venue.city;
        this.venueState = venue.state;
        this.venuePostalCode = venue.postal_code;
        this.venueCountryCode = venue.country_code;
        this.venueWebsite = venue.website;
      },
      setFocusBasedOnVenueType() {
        this.$nextTick(() => {
          if (this.venueType === 'create_new') {
            const venueNameInput = document.getElementById('venue_name');
            if (venueNameInput) {
              venueNameInput.focus();
            }
          }
        });
      },
      showAddressFields() {
        return (this.venueType === 'use_existing' && this.selectedVenue && ! this.selectedVenue.user_id) 
            || this.venueType === 'create_new';
      },
      searchMembers() {
        if (! this.memberEmail) {
          return;
        }

        const emailInput = document.getElementById('member_email');
        
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        fetch(`{{ url('/search_roles') }}?type=member&search=${encodeURIComponent(this.memberEmail)}`, {
          headers: {
            'X-Requested-With': 'XMLHttpRequest',
            'Accept': 'application/json',
          }
        })
        .then(response => response.json())
        .then(data => {
          this.memberSearchResults = data;
        })
        .catch(error => {
          console.error('Error searching members:', error);
        });
      },
      selectMember(member) {
        if (! this.selectedMembers.some(m => m.id === member.id)) {
          this.selectedMembers.push(member);
          // Initialize sendEmailToMembers for selected member if they don't have user_id and have email
          if (!member.user_id && member.email) {
            this.sendEmailToMembers[member.email] = false;
          }
        }        
        this.memberSearchResults = [];
        this.memberEmail = "";
        this.memberName = "";
        this.memberYoutubeUrl = "";
        this.showMemberTypeRadio = false;
      },
      removeMember(member) {
        if (this.roleIsTalent && member.id === this.roleEncodedId) {
          return;
        }
        this.selectedMembers = this.selectedMembers.filter(m => m.id !== member.id);
        // Remove from sendEmailToMembers
        if (member.email && this.sendEmailToMembers[member.email] !== undefined) {
          delete this.sendEmailToMembers[member.email];
        }
        if (this.selectedMembers.length === 0) {
          this.showMemberTypeRadio = true;
        }
      },
      editMember(member) {
        if (member) {
          this.showMemberTypeRadio = false;
          this.editMemberId = member.id;
          this.$nextTick(() => {
            const memberNameInput = document.getElementById(`edit_member_name_${member.id}`);
            if (memberNameInput) {
              memberNameInput.focus();
            }
          });
        } else {
           const memberNameInput = document.getElementById(`edit_member_name_${this.editMemberId}`);
           if (memberNameInput && !memberNameInput.checkValidity()) {
            memberNameInput.reportValidity();
            return;
          }

          const youtubeInput = document.getElementById(`edit_member_youtube_url_${this.editMemberId}`);
          if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
            youtubeInput.reportValidity();
            return;
          }

          this.editMemberId = "";
        }
      },
      addNewMember() {
        const nameInput = document.getElementById('member_name');    
        if (!nameInput.checkValidity()) {
          nameInput.reportValidity();
          return;
        }

        const youtubeInput = document.getElementById('member_youtube_url');
        if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
          youtubeInput.reportValidity();
          return;
        }

        const newMember = {
          id: 'new_' + Date.now(),
          name: this.memberName,
          email: this.memberEmail,
          youtube_url: this.memberYoutubeUrl,
        };

        this.selectedMembers.push(newMember);
        // Initialize sendEmailToMembers for new member using the checkbox value
        if (newMember.email) {
          this.sendEmailToMembers[newMember.email] = this.sendEmailToNewMember;
        }
        this.memberEmail = "";
        this.memberName = "";
        this.memberYoutubeUrl = "";
        this.sendEmailToNewMember = false;
        this.showMemberTypeRadio = false;
      },
      addExistingMember() {
        if (this.selectedMember && !this.selectedMembers.some(m => m.id === this.selectedMember.id)) {
          this.selectedMembers.push(this.selectedMember);
          // Initialize sendEmailToMembers for selected member if they don't have user_id and have email
          if (!this.selectedMember.user_id && this.selectedMember.email) {
            this.sendEmailToMembers[this.selectedMember.email] = false;
          }
          this.$nextTick(() => {
            this.selectedMember = "";
          });
          this.showMemberTypeRadio = false;
        }
      },
      addMember() {
        // Check if name is empty (since we can't use HTML required attribute)
        if (!this.memberName.trim()) {
          const nameInput = document.getElementById('member_name');
          nameInput.focus();
          return;
        }

        const nameInput = document.getElementById('member_name');
        if (!nameInput.checkValidity()) {
          nameInput.reportValidity();
          return;
        }

        const emailInput = document.getElementById('member_email');    
        if (!emailInput.checkValidity()) {
          emailInput.reportValidity();
          return;
        }

        const youtubeInput = document.getElementById('member_youtube_url');
        if (youtubeInput && youtubeInput.value && !youtubeInput.checkValidity()) {
          youtubeInput.reportValidity();
          return;
        }

        const newMember = {
          id: 'new_' + Date.now(),
          name: this.memberName,
          email: this.memberEmail,
          youtube_url: this.memberYoutubeUrl,
        };

        this.selectedMembers.push(newMember);
        // Initialize sendEmailToMembers for new member using the checkbox value
        if (newMember.email) {
          this.sendEmailToMembers[newMember.email] = this.sendEmailToNewMember;
        }
        this.memberSearchResults = [];
        this.memberName = "";
        this.memberEmail = "";
        this.memberYoutubeUrl = "";
        this.sendEmailToNewMember = false;
        this.showMemberTypeRadio = false;
      },
      setFocusBasedOnMemberType() {
        this.$nextTick(() => {
          if (this.memberType === 'create_new') {
            const nameInput = document.getElementById('member_name');
            if (nameInput) {
              nameInput.focus();
            }
          }
        });
      },
      cancelAddMember() {
        this.memberName = "";
        this.memberEmail = "";
        this.memberYoutubeUrl = "";
        this.memberSearchResults = [];
        this.showMemberTypeRadio = false;
      },
      showAddMemberForm() {
        this.showMemberTypeRadio = true;
        this.editMemberId = "";
        if (this.filteredMembers.length === 0) {
          this.memberType = 'create_new';
        }
        this.setFocusBasedOnMemberType();
      },
      clearEventUrl() {
        this.event.event_url = "";
      },
      addPart() {
        const newPart = { uid: this.partUidCounter++, id: '', name: '', description: '', start_time: '', end_time: '' };
        this.eventParts.push(newPart);
        this.initPartEditor(newPart);
      },
      removePart(index) {
        const part = this.eventParts[index];
        this.destroyPartEditor(part);
        this.eventParts.splice(index, 1);
      },
      formatPartTime(time) {
        if (!time) return '';
        var minutes = parseTimeToMinutes(time);
        if (minutes === null) return time;
        return formatMinutesToTime(minutes);
      },
      onPartTimeChange(index, field, event) {
        var minutes = parseTimeToMinutes(event.target.value);
        if (minutes !== null) {
          var h = Math.floor(minutes / 60);
          var m = minutes % 60;
          this.eventParts[index][field] = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
          event.target.value = formatMinutesToTime(minutes);
        } else {
          this.eventParts[index][field] = '';
        }
      },
      initPartTimePickerOnFocus(event, uid, type) {
        var inputEl = event.target;
        var dropdownRef = 'part_' + type + '_dropdown_' + uid;
        var dropdownEl = this.$refs[dropdownRef];
        if (Array.isArray(dropdownEl)) {
          dropdownEl = dropdownEl[0];
        }
        if (dropdownEl && !inputEl._timepickerInit) {
          initPartTimePicker(inputEl, dropdownEl);
        }
      },
      movePartUp(index) {
        if (index > 0) {
          const temp = this.eventParts[index];
          this.eventParts.splice(index, 1);
          this.eventParts.splice(index - 1, 0, temp);
        }
      },
      movePartDown(index) {
        if (index < this.eventParts.length - 1) {
          const temp = this.eventParts[index];
          this.eventParts.splice(index, 1);
          this.eventParts.splice(index + 1, 0, temp);
        }
      },
      onPartDragStart(index) {
        this.partDragIndex = index;
      },
      onPartDragOver(index, event) {
        event.preventDefault();
        if (this.partDragIndex === null) return;
        const rect = event.currentTarget.getBoundingClientRect();
        const midpoint = rect.top + rect.height / 2;
        const target = event.clientY < midpoint ? index : index + 1;
        if (target === this.partDragIndex || target === this.partDragIndex + 1) {
          this.partDropTargetIndex = null;
        } else {
          this.partDropTargetIndex = target;
        }
      },
      onPartDrop() {
        if (this.partDragIndex === null || this.partDropTargetIndex === null) {
          this.partDragIndex = null;
          this.partDropTargetIndex = null;
          return;
        }
        const item = this.eventParts.splice(this.partDragIndex, 1)[0];
        const insertAt = this.partDropTargetIndex > this.partDragIndex ? this.partDropTargetIndex - 1 : this.partDropTargetIndex;
        this.eventParts.splice(insertAt, 0, item);
        this.partDragIndex = null;
        this.partDropTargetIndex = null;
      },
      onPartDragEnd() {
        this.partDragIndex = null;
        this.partDropTargetIndex = null;
      },
      onContainerPartDragOver(event) {
        if (this.partDragIndex === null || this.eventParts.length === 0) return;
        const container = event.currentTarget;
        const firstChild = container.children[0];
        if (firstChild) {
          const rect = firstChild.getBoundingClientRect();
          if (event.clientY < rect.top + rect.height / 2) {
            if (0 !== this.partDragIndex && 0 !== this.partDragIndex + 1) {
              this.partDropTargetIndex = 0;
            } else {
              this.partDropTargetIndex = null;
            }
          }
        }
      },
      initPartEditor(part) {
        if (!this.agendaShowDescription) return;
        this.$nextTick(() => {
          const textarea = this.$refs['partDescription_' + part.uid];
          if (textarea && textarea[0] && !this.partEditors[part.uid] && window.initTinyMDE) {
            this.partEditors[part.uid] = window.initTinyMDE(textarea[0], () => {
              part.description = this.partEditors[part.uid].value();
            });
          }
        });
      },
      destroyPartEditor(part) {
        if (this.partEditors[part.uid]) {
          this.partEditors[part.uid].toTextArea();
          delete this.partEditors[part.uid];
        }
      },
      initAllPartEditors() {
        if (this.agendaShowDescription) {
          this.eventParts.forEach(part => this.initPartEditor(part));
        }
      },
      destroyAllPartEditors() {
        this.eventParts.forEach(part => this.destroyPartEditor(part));
      },
      parsePartsFromImage(event) {
        const file = event.target.files[0];
        if (!file) return;
        this.parsingParts = true;
        const formData = new FormData();
        formData.append('parts_image', file);
        formData.append('ai_prompt', this.partsAiPrompt);
        formData.append('save_ai_prompt_default', this.savePartsAiPromptDefault ? '1' : '0');
        @if($event->exists)
        formData.append('event_id', '{{ \App\Utils\UrlUtils::encodeId($event->id) }}');
        @endif
        formData.append('_token', '{{ csrf_token() }}');
        fetch('{{ url('/' . $subdomain . '/parse-event-parts') }}', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
          this.parsingParts = false;
          event.target.value = '';
          if (data.error) {
            alert(@json(__('messages.error')) + ': ' + data.error);
          } else if (Array.isArray(data) && data.length > 0) {
            this.parsedPartsPreview = data;
            this.showPartsPreview = true;
          }
        })
        .catch(() => {
          this.parsingParts = false;
          event.target.value = '';
          alert(@json(__('messages.error')));
        });
      },
      parsePartsFromText() {
        if (!this.partsText) return;
        this.parsingParts = true;
        const formData = new FormData();
        formData.append('parts_text', this.partsText);
        formData.append('ai_prompt', this.partsAiPrompt);
        formData.append('save_ai_prompt_default', this.savePartsAiPromptDefault ? '1' : '0');
        @if($event->exists)
        formData.append('event_id', '{{ \App\Utils\UrlUtils::encodeId($event->id) }}');
        @endif
        formData.append('_token', '{{ csrf_token() }}');
        fetch('{{ url('/' . $subdomain . '/parse-event-parts') }}', {
          method: 'POST',
          body: formData,
          headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
          this.parsingParts = false;
          if (data.error) {
            alert(@json(__('messages.error')) + ': ' + data.error);
          } else if (Array.isArray(data) && data.length > 0) {
            this.parsedPartsPreview = data;
            this.showPartsPreview = true;
          }
        })
        .catch(() => {
          this.parsingParts = false;
          alert(@json(__('messages.error')));
        });
      },
      acceptParsedParts() {
        this.parsedPartsPreview.forEach(part => {
          this.eventParts.push({
            uid: this.partUidCounter++,
            id: '',
            name: part.name || '',
            description: part.description || '',
            start_time: part.start_time || '',
            end_time: part.end_time || '',
          });
        });
        this.parsedPartsPreview = [];
        this.showPartsPreview = false;
        this.showPartsTextInput = false;
        this.partsText = '';
      },
      onChangeVenueType(type) {
        if (type === 'in_person' && !this.isInPerson && !this.roleIsVenue) {
            this.venueType = '{{ (count($venues) > 0 ? 'use_existing' : 'create_new'); }}';
            this.selectedVenue = '';
            this.venueName = '';
            this.venueEmail = '';
            this.venueAddress1 = '';
            this.venueCity = '';
            this.venueState = '';
            this.venuePostalCode = '';
            this.venueWebsite = '';
        }

        this.savePreferences();
      },
      savePreferences() {
        localStorage.setItem('eventPreferences', JSON.stringify({
          isInPerson: this.isInPerson,
          isOnline: this.isOnline,
          ticketsEnabled: this.event.tickets_enabled
        }));
      },
      loadPreferences() {
        const preferences = JSON.parse(localStorage.getItem('eventPreferences'));
        @if (! $event->exists && $selectedVenue)
        this.isInPerson = true;
        if (preferences) {
          this.isOnline = preferences.isOnline;          
          @if ($role->isPro())
            this.event.tickets_enabled = preferences.ticketsEnabled ?? false;
          @else
            this.event.tickets_enabled = false;
          @endif
        }
        @else
        if (preferences) {
          this.isInPerson = preferences.isInPerson;
          this.isOnline = preferences.isOnline;
          @if ($role->isPro())
            this.event.tickets_enabled = preferences.ticketsEnabled ?? false;
          @else
            this.event.tickets_enabled = false;
          @endif
        }
        @endif
      },
      validateForm(event) {
        this.formSubmitAttempted = true;

        var dateVal = document.getElementById('event_date').value;
        var startVal = document.getElementById('start_time').value;
        if (!dateVal || !startVal) {
          event.preventDefault();
          alert(@json(__('messages.date_and_time_required')));
          return;
        }

        if (! this.isFormValid) {
          event.preventDefault();
          alert(@json(__('messages.please_select_venue_or_participant')));
          return;
        }

        // Check custom fields if tickets are enabled
        if (this.event.tickets_enabled) {
          const hasInvalidEventFields = Object.values(this.eventCustomFields || {}).some(field => !field.name);
          const hasInvalidTicketFields = this.tickets.some(ticket =>
            Object.values(ticket.custom_fields || {}).some(field => !field.name)
          );
          const hasInvalidTicketTypes = this.tickets.length > 1 && this.tickets.some(ticket => !ticket.type);

          if (hasInvalidEventFields || hasInvalidTicketFields) {
            event.preventDefault();
            alert(@json(__('messages.please_fill_in_custom_field_names')));
            return;
          }

          if (hasInvalidTicketTypes) {
            event.preventDefault();
            alert(@json(__('messages.please_fill_in_ticket_types')));
            return;
          }
        }

        this.isDirty = false;
      },
      addTicket() {
        this.tickets.push({
            id: null,
            type: '',
            quantity: null,
            price: null,
            description: '',
            custom_fields: {},
        });
      },
      removeTicket(index) {
        this.tickets.splice(index, 1);
      },
      getNextAvailableEventFieldIndex() {
        const usedIndices = Object.values(this.eventCustomFields || {}).map(f => f.index).filter(i => i);
        for (let i = 1; i <= 8; i++) {
          if (!usedIndices.includes(i)) {
            return i;
          }
        }
        return null;
      },
      addEventCustomField() {
        const fieldCount = Object.keys(this.eventCustomFields || {}).length;
        if (fieldCount >= 8) return;
        const fieldKey = 'field' + Date.now();
        const fieldIndex = this.getNextAvailableEventFieldIndex();
        this.eventCustomFields = {
          ...this.eventCustomFields,
          [fieldKey]: { name: '', name_en: '', type: 'string', required: false, index: fieldIndex }
        };
      },
      removeEventCustomField(fieldKey) {
        const newFields = { ...this.eventCustomFields };
        delete newFields[fieldKey];
        this.eventCustomFields = newFields;
      },
      getEventCustomFieldCount() {
        return Object.keys(this.eventCustomFields || {}).length;
      },
      getNextAvailableTicketFieldIndex(ticket) {
        const usedIndices = Object.values(ticket.custom_fields || {}).map(f => f.index).filter(i => i);
        for (let i = 1; i <= 8; i++) {
          if (!usedIndices.includes(i)) {
            return i;
          }
        }
        return null;
      },
      addTicketCustomField(ticketIndex) {
        const ticket = this.tickets[ticketIndex];
        if (!ticket.custom_fields) {
          ticket.custom_fields = {};
        }
        const fieldCount = Object.keys(ticket.custom_fields).length;
        if (fieldCount >= 8) return;
        const fieldKey = 'field' + Date.now();
        const fieldIndex = this.getNextAvailableTicketFieldIndex(ticket);
        ticket.custom_fields = {
          ...ticket.custom_fields,
          [fieldKey]: { name: '', name_en: '', type: 'string', required: false, index: fieldIndex }
        };
      },
      removeTicketCustomField(ticketIndex, fieldKey) {
        const ticket = this.tickets[ticketIndex];
        const newFields = { ...ticket.custom_fields };
        delete newFields[fieldKey];
        ticket.custom_fields = newFields;
      },
      getTicketCustomFieldCount(ticketIndex) {
        const ticket = this.tickets[ticketIndex];
        return Object.keys(ticket.custom_fields || {}).length;
      },
      toggleExpireUnpaid() {
        if (! this.event.expire_unpaid_tickets) {
          this.event.expire_unpaid_tickets = 24;
        } else {
          this.event.expire_unpaid_tickets = 0;
        }
      },
      toggleCuratorGroupSelection(curatorId) {
        const groupSelection = document.getElementById(`curator_group_${curatorId}`);
        if (groupSelection) {
          const checkbox = document.getElementById(`curator_${curatorId}`);
          if (checkbox && checkbox.checked) {
            groupSelection.style.display = 'block';
          } else {
            groupSelection.style.display = 'none';
          }
        }
      },
      initializeCuratorGroupSelections() {
        // Show group selection for curators that are already checked
        const curatorCheckboxes = document.querySelectorAll('input[name="curators[]"]');
        curatorCheckboxes.forEach(checkbox => {
          if (checkbox.checked) {
            const curatorId = checkbox.value;
            this.toggleCuratorGroupSelection(curatorId);
          }
        });
      },
      initializeRecurringEndDatePicker() {
        // Use multiple nextTick calls to ensure Vue has fully rendered the v-if element
        this.$nextTick(() => {
          this.$nextTick(() => {
            const endDateInput = document.querySelector('.datepicker-end-date');
            // Make sure element exists and is not already initialized
            if (endDateInput && !endDateInput._flatpickr) {
              // Get the value from Vue model or input's value attribute
              const inputValue = this.event.recurring_end_value || endDateInput.value || null;
              
              // Clear the value attribute - flatpickr will set it via defaultDate
              if (inputValue) {
                endDateInput.removeAttribute('value');
              }
              
              var f = flatpickr(endDateInput, {
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
                defaultDate: inputValue,
                onChange: (selectedDates, dateStr, instance) => {
                  // Update Vue model with the actual value (YYYY-MM-DD format)
                  this.event.recurring_end_value = dateStr;
                }
              });
              
              // https://github.com/flatpickr/flatpickr/issues/892#issuecomment-604387030
              if (f._input) {
                f._input.onkeydown = () => false;
              }
            }
          });
        });
      },
    },
    computed: {
      filteredMembers() {
        return this.members.filter(member => !this.selectedMembers.some(selected => selected.id === member.id));
      },
      isFormValid() {        
        var hasSubdomain = this.venueName || this.selectedMembers.length > 0;

        return hasSubdomain;
      },
      hasLimitedPaidTickets() {
        return this.tickets.some(ticket => ticket.price > 0 && ticket.quantity > 0);
      },
      hasSameTicketQuantities() {
        if (this.tickets.length <= 1) {
          return false;
        }
        
        // Check that all tickets have quantities set
        const ticketsWithQuantities = this.tickets.filter(ticket => ticket.quantity > 0);
        if (ticketsWithQuantities.length !== this.tickets.length) {
          return false;
        }
        
        // Check that all quantities are the same
        const quantities = ticketsWithQuantities.map(ticket => ticket.quantity);
        return new Set(quantities).size === 1;
      },
      getSameTicketQuantity() {
        if (!this.hasSameTicketQuantities) {
          return null;
        }
        return this.tickets.find(ticket => ticket.quantity > 0).quantity;
      },
      getTotalTicketQuantity() {
        // Always return the sum of all quantities for display purposes
        return this.tickets.reduce((total, ticket) => total + (ticket.quantity || 0), 0);
      },
      getCombinedTotalQuantity() {
        // For combined mode, the total should be the same as the individual quantity
        // since we're treating it as a single pool of tickets
        if (this.hasSameTicketQuantities) {
          return this.getSameTicketQuantity;
        }
        return this.tickets.reduce((total, ticket) => total + (ticket.quantity || 0), 0);
      },
      hasIncompleteParticipantData() {
        return this.showMemberTypeRadio &&
          this.memberType === 'create_new' &&
          (this.memberName.trim() || this.memberEmail.trim() || this.memberYoutubeUrl.trim());
      },
    },
    watch: {
      venueType() {
        this.venueEmail = "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];

        this.$nextTick(() => {
            $("#venue_country").countrySelect({
                defaultCountry: "{{ $role && $role->country_code ? $role->country_code : '' }}",
            });
        });
      },
      memberType() {
        this.memberSearchResults = [];
        this.memberEmail = "";
        this.memberName = "";
      },
      selectedVenue() {
        this.venueName = this.selectedVenue ? this.selectedVenue.name : "";
        this.venueEmail = this.selectedVenue ? this.selectedVenue.email : "";
        this.venueAddress1 = this.selectedVenue ? this.selectedVenue.address1 : "";
        this.venueCity = this.selectedVenue ? this.selectedVenue.city : "";
        this.venueState = this.selectedVenue ? this.selectedVenue.state : "";
        this.venuePostalCode = this.selectedVenue ? this.selectedVenue.postal_code : "";
        this.venueCountryCode = this.selectedVenue ? this.selectedVenue.country_code : "";
        this.venueWebsite = this.selectedVenue ? this.selectedVenue.website : "";
        this.venueSearchEmail = "";
        this.venueSearchResults = [];
      },
      isInPerson(newValue) {
        this.savePreferences();
      },
      isOnline(newValue) {
        if (!newValue) {
          this.clearEventUrl();
        }
        this.savePreferences();
      },
      selectedMembers: {
        handler(newValue, oldValue) {
          if (!this.eventName && newValue.length === 1) {
            this.eventName = newValue[0].name;
          }
          
          // Clean up sendEmailToMembers for removed members
          // Note: Email changes are handled by the checkbox binding using the current email
          if (oldValue && Array.isArray(oldValue)) {
            oldValue.forEach(oldMember => {
              const newMember = newValue.find(m => m.id === oldMember.id);
              if (!newMember && oldMember.email) {
                // Member was removed - clean up
                if (this.sendEmailToMembers[oldMember.email] !== undefined) {
                  delete this.sendEmailToMembers[oldMember.email];
                }
              }
            });
          }
        },
        deep: true
      },
      'event.tickets_enabled'(newValue) {
        this.savePreferences();
      },
      'event.recurring_frequency'() {
        updateRecurringFieldVisibility();
      },
      'event.recurring_end_type'(newValue, oldValue) {
        // Clear the value when switching between types
        if (oldValue && newValue !== oldValue) {
          this.event.recurring_end_value = null;
        }

        if (newValue === 'on_date') {
          this.initializeRecurringEndDatePicker();
        }
      },
      agendaShowDescription(newVal) {
        if (newVal) {
          this.initAllPartEditors();
        } else {
          this.destroyAllPartEditors();
        }
      },
    },
    mounted() {
      this.showMemberTypeRadio = this.selectedMembers.length === 0;
      this.$nextTick(() => updateRecurringFieldVisibility());

      const isCloned = @json($isCloned ?? false);

      if (this.event.id) {
        // Existing event - use event data
        this.isInPerson = !!this.event.venue || !!this.selectedVenue;
        this.isOnline = !!this.event.event_url;
      } else if (isCloned) {
        // Cloned event - use cloned data, don't load from localStorage
        this.isInPerson = !!this.selectedVenue;
        this.isOnline = !!this.event.event_url;
      } else {
        // New event - load from localStorage
        this.loadPreferences();

        if (!this.isInPerson && !this.isOnline) {
          this.isInPerson = true;
        }
      }

      if (this.event.id) {
        this.eventName = this.event.name;
      } else if (isCloned && this.event.name) {
        // Cloned event - preserve the cloned event name
        this.eventName = this.event.name;
      } else if (this.selectedMembers.length === 1) {
        // New event with single member - use member name
        this.eventName = this.selectedMembers[0].name;
      }

      // Initialize curator group selections
      this.initializeCuratorGroupSelections();

      // Initialize part description editors
      this.$nextTick(() => {
        this.initAllPartEditors();
      });

      // Initialize recurring end date picker if needed
      if (this.event.recurring_end_type === 'on_date') {
        this.initializeRecurringEndDatePicker();
      }
      
      // Initialize sendEmailToMembers for existing selectedMembers
      this.selectedMembers.forEach(member => {
        if (((member.id && member.id.toString().startsWith('new_')) || !member.user_id) && member.email) {
          this.sendEmailToMembers[member.email] = false;
        }
      });

      // Unsaved changes warning
      var dirtyForm = document.querySelector('form[enctype]');
      if (dirtyForm) {
          dirtyForm.addEventListener('input', () => { this.isDirty = true; });
          dirtyForm.addEventListener('change', () => { this.isDirty = true; });
      }
      window.addEventListener('beforeunload', (e) => {
          if (this.isDirty) { e.preventDefault(); e.returnValue = ''; }
      });
      document.addEventListener('click', (e) => {
          if (!this.isDirty) return;
          var link = e.target.closest('a[href]');
          if (!link) return;
          var href = link.getAttribute('href');
          if (!href || href === '#' || href.startsWith('#') || href.startsWith('javascript:')) return;
          if (link.closest('form[enctype]')) return;
          if (!confirm(@json(__("messages.unsaved_changes")))) {
              e.preventDefault();
          } else {
              this.isDirty = false;
          }
      });
    }
  });
  const vueInstance = app.mount('#app');

  // Store reference for section navigation
  window.vueApp = vueInstance;

  // --- Migrated inline event handlers ---

  // Delete event form confirmation (line 828 originally)
  var deleteForm = document.getElementById('event-delete-form');
  if (deleteForm) {
    deleteForm.addEventListener('submit', function(e) {
      if (!confirm(@json(__('messages.are_you_sure')))) {
        e.preventDefault();
      }
    });
  }

  // Copy event URL button (line 1013 originally)
  var copyUrlBtn = document.getElementById('copy-event-url-btn');
  if (copyUrlBtn) {
    copyUrlBtn.addEventListener('click', function() {
      copyEventUrl(this);
    });
  }

  // Edit/Cancel slug buttons
  var editSlugBtn = document.getElementById('edit-slug-btn');
  if (editSlugBtn) {
    editSlugBtn.addEventListener('click', function() {
      toggleEventSlugEdit();
    });
  }
  var cancelSlugBtn = document.getElementById('cancel-slug-btn');
  if (cancelSlugBtn) {
    cancelSlugBtn.addEventListener('click', function() {
      toggleEventSlugEdit();
    });
  }

  // Flyer image file input change (line 1105 originally)
  var flyerImageInput = document.getElementById('flyer_image');
  if (flyerImageInput) {
    flyerImageInput.addEventListener('change', function() {
      previewImage(this);
    });
  }

  // Choose file button for flyer (line 1107 originally)
  var flyerChooseBtn = document.getElementById('flyer-choose-btn');
  if (flyerChooseBtn) {
    flyerChooseBtn.addEventListener('click', function() {
      document.getElementById('flyer_image').click();
    });
  }

  // Clear flyer preview button (line 1123 originally)
  var clearFlyerPreviewBtn = document.getElementById('clear-flyer-preview-btn');
  if (clearFlyerPreviewBtn) {
    clearFlyerPreviewBtn.addEventListener('click', function() {
      clearFileInput('flyer_image');
    });
  }

  // Delete existing flyer button (line 1130 originally)
  var deleteFlyerBtn = document.getElementById('delete-flyer-btn');
  if (deleteFlyerBtn) {
    deleteFlyerBtn.addEventListener('click', function() {
      deleteFlyer(this.dataset.url, this.dataset.hash, this.dataset.token, this.parentElement);
    });
  }

  // Country select change (line 1317 originally)
  var venueCountryInput = document.getElementById('venue_country');
  if (venueCountryInput) {
    venueCountryInput.addEventListener('change', function() {
      onChangeCountry();
    });
  }

  // View map button (line 1325 originally)
  var viewMapButton = document.getElementById('view_map_button');
  if (viewMapButton) {
    viewMapButton.addEventListener('click', function() {
      viewMap();
    });
  }

  // Validate address button (line 1327 originally)
  var validateButton = document.getElementById('validate_button');
  if (validateButton) {
    validateButton.addEventListener('click', function() {
      onValidateClick();
    });
  }

  // Accept address button (line 1328 originally)
  var acceptButton = document.getElementById('accept_button');
  if (acceptButton) {
    acceptButton.addEventListener('click', function(e) {
      acceptAddress(e);
    });
  }

  // Schedule type radio buttons (lines 1635, 1641 originally)
  var oneTimeRadio = document.getElementById('one_time');
  if (oneTimeRadio) {
    oneTimeRadio.addEventListener('change', function() {
      onChangeDateType();
    });
  }
  var recurringRadio = document.getElementById('recurring');
  if (recurringRadio) {
    recurringRadio.addEventListener('change', function() {
      onChangeDateType();
    });
  }

  // Unsync from Google Calendar button (line 2523 originally)
  var unsyncBtn = document.getElementById('unsync-event-btn');
  if (unsyncBtn) {
    unsyncBtn.addEventListener('click', function() {
      unsyncEvent(this.dataset.subdomain, parseInt(this.dataset.eventId));
    });
  }

  // Sync to Google Calendar button (line 2533 originally)
  var syncBtn = document.getElementById('sync-event-btn');
  if (syncBtn) {
    syncBtn.addEventListener('click', function() {
      syncEvent(this.dataset.subdomain, parseInt(this.dataset.eventId));
    });
  }

  // Google Calendar sync functions
  function syncEvent(subdomain, eventId) {
    const statusDiv = document.getElementById(`sync-status-${eventId}`);
    statusDiv.classList.remove('hidden');
    
    fetch(`{{ url('/google-calendar/sync-event') }}/${subdomain}/${eventId}`, {
      method: 'POST',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      statusDiv.classList.add('hidden');
      if (data.error) {
        alert(@json(__('messages.error')) + ': ' + data.error);
      } else {
        location.reload(); // Refresh to show updated sync status
      }
    })
    .catch(error => {
      statusDiv.classList.add('hidden');
      alert(@json(__('messages.error')) + ': ' + error.message);
    });
  }

  function unsyncEvent(subdomain, eventId) {
    if (!confirm(@json(__('messages.confirm_remove_google_calendar')))) {
      return;
    }

    const statusDiv = document.getElementById(`sync-status-${eventId}`);
    statusDiv.classList.remove('hidden');

    fetch(`{{ url('/google-calendar/unsync-event') }}/${subdomain}/${eventId}`, {
      method: 'DELETE',
      headers: {
        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        'Content-Type': 'application/json',
      },
    })
    .then(response => {
      if (!response.ok) {
        throw new Error(`HTTP ${response.status}: ${response.statusText}`);
      }
      return response.json();
    })
    .then(data => {
      statusDiv.classList.add('hidden');
      if (data.error) {
        alert(@json(__('messages.error')) + ': ' + data.error);
      } else {
        location.reload(); // Refresh to show updated sync status
      }
    })
    .catch(error => {
      statusDiv.classList.add('hidden');
      alert(@json(__('messages.error')) + ': ' + error.message);
    });
  }

// Prevent browser from restoring scroll position
if ('scrollRestoration' in history) {
    history.scrollRestoration = 'manual';
}

// Scroll to top immediately (before DOMContentLoaded)
window.scrollTo(0, 0);
document.documentElement.scrollTop = 0;
if (document.body) {
    document.body.scrollTop = 0;
}

// Section navigation functionality
document.addEventListener('DOMContentLoaded', function() {
    const sectionLinks = document.querySelectorAll('.section-nav-link');
    const sections = document.querySelectorAll('.section-content');
    
    // Ensure we're at the top
    window.scrollTo(0, 0);
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
    
    // Save the initial hash before anti-scroll logic strips it
    const initialHash = window.location.hash ? window.location.hash.replace('#', '') : '';

    // Prevent browser from scrolling to hash on page load
    if (window.location.hash) {
        // Temporarily remove hash to prevent auto-scroll
        const hash = window.location.hash;
        history.replaceState(null, null, ' ');
        window.scrollTo(0, 0);
        // Restore hash without scrolling
        setTimeout(function() {
            if (history.pushState) {
                history.replaceState(null, null, hash);
            }
            window.scrollTo(0, 0);
        }, 0);
    }
    
    const mobileHeaders = document.querySelectorAll('.mobile-section-header');

    // Track current section for navigation blocking
    let currentSectionId = null;

    // Function to sync mobile accordion headers
    function syncMobileHeaders(sectionId) {
        mobileHeaders.forEach(header => {
            if (header.getAttribute('data-section') === sectionId) {
                header.classList.add('active');
            } else {
                header.classList.remove('active');
            }
        });
    }

    // Function to show a specific section and hide others
    function showSection(sectionId, preventScroll = false) {
        // Block navigation if leaving participants with incomplete add member form
        if (currentSectionId === 'section-participants' &&
            sectionId !== 'section-participants' &&
            window.vueApp &&
            window.vueApp.showMemberTypeRadio &&
            window.vueApp.hasIncompleteParticipantData) {
            // Shake Done button as warning and block navigation
            const addBtn = document.getElementById('add-member-btn');
            if (addBtn) {
                addBtn.classList.add('shake');
                setTimeout(() => addBtn.classList.remove('shake'), 400);
            }
            return; // Block navigation
        }

        // Auto-cancel add member form if leaving participants with empty form
        if (currentSectionId === 'section-participants' &&
            sectionId !== 'section-participants' &&
            window.vueApp &&
            window.vueApp.showMemberTypeRadio) {
            window.vueApp.cancelAddMember();
        }

        // Track current section
        currentSectionId = sectionId;

        sections.forEach(section => {
            if (section.id === sectionId) {
                section.style.display = 'block';
            } else {
                section.style.display = 'none';
            }
        });

        // Update active link
        sectionLinks.forEach(link => {
            if (link.getAttribute('data-section') === sectionId) {
                link.classList.add('bg-gray-100', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'font-bold', 'border-[#4E81FA]');
                link.classList.remove('text-gray-700', 'dark:text-gray-300', 'font-medium', 'border-transparent');
            } else {
                link.classList.remove('bg-gray-100', 'dark:bg-gray-700', 'text-gray-900', 'dark:text-white', 'font-bold', 'border-[#4E81FA]');
                link.classList.add('text-gray-700', 'dark:text-gray-300', 'font-medium', 'border-transparent');
            }
        });

        // Sync mobile accordion headers
        syncMobileHeaders(sectionId);

        // Update URL hash
        if (history.pushState) {
            history.pushState(null, null, '#' + sectionId);
        } else {
            window.location.hash = sectionId;
        }

        // Prevent scroll if requested
        if (preventScroll) {
            window.scrollTo(0, 0);
        }
    }

    // Handle navigation link clicks
    sectionLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const sectionId = this.getAttribute('data-section');
            showSection(sectionId);
        });
    });

    // Handle mobile accordion header clicks
    mobileHeaders.forEach(header => {
        header.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-section');
            showSection(sectionId);
        });
    });

    // Check if we're on a large screen
    function isLargeScreen() {
        return window.matchMedia('(min-width: 1024px)').matches;
    }

    // Initialize: show section based on hash or first section
    function initializeSections() {
        // Use saved initialHash since anti-scroll logic may have stripped it
        if (initialHash && document.getElementById(initialHash)) {
            showSection(initialHash, true); // Prevent scroll on initial load
        } else {
            // Show first section
            const firstSection = sections[0];
            if (firstSection) {
                showSection(firstSection.id, true); // Prevent scroll on initial load
            }
        }
    }

    // Handle hash changes
    window.addEventListener('hashchange', function() {
        const hash = window.location.hash.replace('#', '');
        if (hash && document.getElementById(hash)) {
            showSection(hash);
        }
    });
    
    // Initialize on page load
    initializeSections();
    
    // Form validation error handling
    const form = document.getElementById('edit-form');
    if (form) {
        // Function to highlight section navigation link
        function highlightSectionError(sectionId) {
            if (!sectionId) return;

            const sectionLink = document.querySelector(`.section-nav-link[data-section="${sectionId}"]`);
            if (sectionLink) {
                sectionLink.classList.add('validation-error');
            }
            const mobileHeader = document.querySelector(`.mobile-section-header[data-section="${sectionId}"]`);
            if (mobileHeader) {
                mobileHeader.classList.add('validation-error');
            }
        }

        // Function to clear section error highlight by section ID
        function clearSectionErrorById(sectionId) {
            if (!sectionId) return;

            const sectionLink = document.querySelector(`.section-nav-link[data-section="${sectionId}"]`);
            if (sectionLink) {
                sectionLink.classList.remove('validation-error');
            }
            const mobileHeader = document.querySelector(`.mobile-section-header[data-section="${sectionId}"]`);
            if (mobileHeader) {
                mobileHeader.classList.remove('validation-error');
            }
        }

        // Highlight sections with server-side validation errors
        document.querySelectorAll('.section-content').forEach(section => {
            const hasErrors = section.querySelectorAll('ul.text-red-600, ul.text-red-400').length > 0;
            if (hasErrors) {
                highlightSectionError(section.id);
            }
        });

        // Handle invalid event on ANY required field (including custom fields)
        form.addEventListener('invalid', function(e) {
            const field = e.target;
            const sectionContent = field.closest('.section-content');
            if (sectionContent) {
                highlightSectionError(sectionContent.id);
            }
        }, true); // Use capture phase since invalid doesn't bubble

        // Form submit handler - check validity before submission
        form.addEventListener('submit', function(e) {
            if (!form.checkValidity()) {
                e.preventDefault();
                e.stopPropagation();

                // Find first invalid field across ALL form elements
                let firstInvalidField = null;
                let firstInvalidSection = null;
                const allFields = form.querySelectorAll('input, select, textarea');

                for (const field of allFields) {
                    if (!field.checkValidity()) {
                        if (!firstInvalidField) {
                            firstInvalidField = field;
                            const sectionContent = field.closest('.section-content');
                            firstInvalidSection = sectionContent ? sectionContent.id : null;
                        }
                    }
                }

                if (firstInvalidField && firstInvalidSection) {
                    showSection(firstInvalidSection);
                    highlightSectionError(firstInvalidSection);

                    setTimeout(() => {
                        firstInvalidField.focus();
                        firstInvalidField.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        firstInvalidField.reportValidity();
                    }, 100);
                }
            }
        });

        // Clear error state when any required field becomes valid
        const allRequiredFields = form.querySelectorAll('[required]');
        allRequiredFields.forEach(field => {
            const handler = function() {
                if (this.checkValidity()) {
                    const sectionContent = this.closest('.section-content');
                    if (sectionContent) {
                        const sectionFields = sectionContent.querySelectorAll('[required]');
                        const allValid = Array.from(sectionFields).every(f => f.checkValidity());
                        if (allValid) {
                            clearSectionErrorById(sectionContent.id);
                        }
                    }
                }
            };
            field.addEventListener('input', handler);
            field.addEventListener('change', handler);
        });
    }
    
    // Scroll to top on page load - use multiple methods to ensure it works
    function scrollToTop() {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
        document.documentElement.scrollTop = 0;
        document.body.scrollTop = 0;
    }
    
    // Scroll immediately
    scrollToTop();
    
    // Scroll after a short delay to override any hash navigation
    setTimeout(scrollToTop, 0);
    setTimeout(scrollToTop, 10);
    setTimeout(scrollToTop, 50);
    
    // Use requestAnimationFrame to ensure it happens after layout
    requestAnimationFrame(function() {
        scrollToTop();
        requestAnimationFrame(scrollToTop);
    });
});

// Also scroll to top when window fully loads (backup)
window.addEventListener('load', function() {
    window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    document.documentElement.scrollTop = 0;
    document.body.scrollTop = 0;
    
    // Additional scroll after load
    setTimeout(function() {
        window.scrollTo({ top: 0, left: 0, behavior: 'instant' });
    }, 0);
});

function deleteFlyer(url, hash, token, element) {
    if (!confirm(@json(__('messages.are_you_sure')))) {
        return;
    }

    fetch(url + '?hash=' + hash + '&image_type=flyer', {
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': token,
            'Accept': 'application/json'
        }
    }).then(response => {
        if (response.ok) {
            element.remove();
            var chooseSection = document.getElementById('flyer_image_choose');
            if (chooseSection) chooseSection.style.display = '';
        } else {
            alert('Failed to delete image');
        }
    });
}
</script>

</x-app-admin-layout>