<x-app-guest-layout :role="$role" :showMobileBackground="true">

@php
  $hasHeaderImage = ($role->header_image && $role->header_image !== 'none') || $role->header_image_url;
  $use24hr = get_use_24_hour_time($role);
  $accentColor = $role->accent_color ?? '#4E81FA';
  $contrastColor = accent_contrast_color($accentColor);
@endphp

  <style {!! nonce_attr() !!}>
    .time-dropdown {
      display: none;
      position: absolute;
      z-index: 50;
      width: 100%;
      max-height: 200px;
      overflow-y: auto;
      background: #fff;
      border: 1px solid #d1d5db;
      border-radius: 0.5rem;
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
  </style>

  @if ($role->profile_image_url && !$hasHeaderImage && $role->language_code == 'en')
  <div class="pt-8"></div>
  @endif

  <main>
    <div>
      <div class="container mx-auto pt-7 pb-20 px-5">
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm mb-4 {{ !$hasHeaderImage && $role->profile_image_url ? 'pt-16' : '' }} rounded-2xl max-w-4xl mx-auto">
          <div
            class="relative overflow-hidden rounded-t-xl before:block before:absolute before:bg-[#00000033] before:-inset-0 before:rounded-t-xl"
          >
            @if ($role->header_image && $role->header_image !== 'none')
            <picture>
              <source srcset="{{ asset('images/headers') }}/{{ $role->header_image }}.webp" type="image/webp">
              <img
                class="block max-h-72 w-full object-cover"
                src="{{ asset('images/headers') }}/{{ $role->header_image }}.png"
              />
            </picture>
            @elseif ($role->header_image_url)
            <img
              class="block max-h-72 w-full object-cover"
              src="{{ $role->header_image_url }}"
            />
            @endif
          </div>
          <div class="px-6 sm:px-8 lg:px-16 pb-1 md:pb-4 relative z-10">
            @if ($role->profile_image_url)
            <div class="rounded-lg w-[130px] h-[130px] -mt-[100px] -ms-1 mb-6 bg-white dark:bg-gray-900 flex items-center justify-center">
              <img
                class="rounded-lg w-[120px] h-[120px] object-cover"
                src="{{ $role->profile_image_url }}"
                alt="person"
              />
            </div>
            @else
            <div style="height: 42px;"></div>
            @endif

            <div class="flex justify-between items-center gap-6 mb-5">
            @if (is_rtl())
                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ $role->getGuestUrl() }}" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
                        {{ __('messages.view_schedule') }}
                    </a>
                </div>

                <div class="w-full sm:w-auto text-end">
                    <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                        {{ __('messages.booking_request') }}
                    </h2>
                    <h3 class="text-gray-700 dark:text-gray-300">
                        {{ $role->name }}
                    </h3>
                </div>
            @else
                <div>
                    <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                        {{ __('messages.booking_request') }}
                    </h2>
                    <h3 class="text-gray-700 dark:text-gray-300">
                        {{ $role->getDisplayName(true) }}
                    </h3>
                </div>

                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ $role->getGuestUrl() }}" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
                        {{ __('messages.view_schedule') }}
                    </a>
                </div>
            @endif
            </div>
          </div>
        </div>

        {{-- Booking Request Form --}}
        <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm rounded-2xl p-6 sm:p-8 lg:p-16 pt-6 lg:pt-8 max-w-4xl mx-auto">
          <form id="booking-request-form" method="POST" action="{{ route('event.booking_request.store', ['subdomain' => $role->subdomain]) }}">
            @csrf

            {{-- Event Details Section --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.event_details') }}</h3>

            <div class="mb-4">
              <x-input-label for="event_name" :value="__('messages.event_name')" />
              <x-text-input id="event_name" name="event_name" type="text" class="mt-1 block w-full" />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
              <div>
                <x-input-label for="event_date" :value="__('messages.date')" />
                <input type="text" id="event_date"
                  class="datepicker-date mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                  autocomplete="off" aria-label="{{ __('messages.date') }}" />
                <input type="hidden" name="date" id="hidden_date" />
              </div>
              <div>
                <x-input-label for="event_start_time" :value="__('messages.start_time')" />
                <div class="relative">
                  <input type="text" id="event_start_time"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                    autocomplete="off" aria-label="{{ __('messages.start_time') }}" />
                  <div class="time-dropdown" id="start_time_dropdown"></div>
                </div>
                <input type="hidden" name="start_time" id="hidden_start_time" />
              </div>
            </div>

            <div class="mb-6">
              <x-input-label for="event_description" :value="__('messages.description')" />
              <textarea id="event_description" name="description" rows="4" class="html-editor mt-1 block w-full"></textarea>
            </div>

            {{-- Location Section --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.location') }}</h3>

            <div class="mb-4">
              <fieldset>
                <div class="flex items-center space-x-6">
                  <div class="flex items-center">
                    <input id="in_person" type="checkbox" checked
                      class="h-4 w-4 border-gray-300 rounded"
                      style="accent-color: {{ $accentColor }}">
                    <label for="in_person" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                      {{ __('messages.in_person') }}
                    </label>
                  </div>
                  <div class="flex items-center ps-3">
                    <input id="is_online" name="is_online" value="1" type="checkbox"
                      class="h-4 w-4 border-gray-300 rounded"
                      style="accent-color: {{ $accentColor }}">
                    <label for="is_online" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                      {{ __('messages.online') }}
                    </label>
                  </div>
                </div>
              </fieldset>
            </div>

            <div id="online-url-field" class="mb-4 hidden">
              <x-input-label for="event_url" :value="__('messages.event_url')" />
              <x-text-input id="event_url" name="event_url" type="url" class="mt-1 block w-full" autocomplete="off" />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.event_url_help') }}</p>
            </div>

            <div id="location-fields">
              <div class="mb-4">
                <x-input-label for="venue_name" :value="__('messages.venue_name')" />
                <x-text-input id="venue_name" name="venue_name" type="text" class="mt-1 block w-full" />
              </div>

              <div class="mb-4">
                <x-input-label for="venue_country_code" :value="__('messages.country')" />
                <x-country-input name="venue_country_code" :value="$role->country_code" class="mt-1" />
              </div>

              <div class="mb-4">
                <x-input-label for="venue_address1" :value="__('messages.street_address')" />
                <x-text-input id="venue_address1" name="venue_address1" type="text" class="mt-1 block w-full" />
              </div>

              <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                <div>
                  <x-input-label for="venue_city" :value="__('messages.city')" />
                  <x-text-input id="venue_city" name="venue_city" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                  <x-input-label for="venue_state" :value="__('messages.state_province')" />
                  <x-text-input id="venue_state" name="venue_state" type="text" class="mt-1 block w-full" />
                </div>
                <div>
                  <x-input-label for="venue_postal_code" :value="__('messages.postal_code')" />
                  <x-text-input id="venue_postal_code" name="venue_postal_code" type="text" class="mt-1 block w-full" />
                </div>
              </div>
            </div>

            {{-- Contact Info Section --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 mt-6">{{ __('messages.contact_info') }}</h3>

            @if (auth()->check())
              <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.logged_in_as') }} <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
              </div>
            @else
              <div class="mb-4">
                <x-input-label for="contact_name" :value="__('messages.name')" />
                <x-text-input id="contact_name" name="contact_name" type="text" class="mt-1 block w-full" required />
              </div>

              <div class="mb-4">
                <x-input-label for="contact_email" :value="__('messages.email')" />
                <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" required />
              </div>

              {{-- Create Account Option --}}
              <div class="mb-4 mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                <label class="inline-flex items-center cursor-pointer">
                  <input type="checkbox" id="create_account" name="create_account" value="1" class="rounded border-gray-300 dark:border-gray-600 shadow-sm" style="accent-color: {{ $accentColor }}" {{ $role->require_account ? 'checked disabled' : '' }}>
                  <span class="ms-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.create_an_account') }}</span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 {{ is_rtl() ? 'me-6' : 'ms-6' }}">{{ __('messages.create_account_benefits') }}</p>

                <div id="account-fields" class="{{ $role->require_account ? '' : 'hidden' }} mt-4">
                  <div>
                    <x-input-label for="account_password" :value="__('messages.password')" />
                    <x-text-input id="account_password" name="password" type="password" class="mt-1 block w-full" required />
                  </div>
                  <div class="mt-3">
                    <div class="relative flex items-start">
                      <div class="flex h-6 items-center">
                        <input id="account_terms" name="terms" type="checkbox" required
                          class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm" style="accent-color: {{ $accentColor }}">
                      </div>
                      <div class="ms-3 text-sm leading-6">
                        <label for="account_terms" class="font-medium text-gray-700 dark:text-gray-300">
                          @if (config('app.hosted'))
                            {!! str_replace([':terms', ':privacy'], [
                                '<a href="' . marketing_url('/terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                                '<a href="' . marketing_url('/privacy') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">' . __('messages.privacy_policy') . '</a>'
                            ], __('messages.i_accept_the_terms_and_privacy')) !!}
                          @else
                            {!! str_replace([':terms'], [
                                '<a href="' . marketing_url('/self-hosting-terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                            ], __('messages.i_accept_the_terms')) !!}
                          @endif
                        </label>
                      </div>
                    </div>
                  </div>
                </div>
              </div>

              @if ($role->require_account)
                <input type="hidden" name="create_account" value="1">
              @endif
            @endif

            {{-- Submit Button --}}
            <div class="flex items-center justify-end gap-3 mt-8">
              <a href="{{ $role->getGuestUrl() }}" class="px-4 py-3 text-base text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200">
                {{ __('messages.cancel') }}
              </a>
              <button type="submit" id="submit-btn" class="px-4 py-3 text-base rounded-lg transition-all duration-200 disabled:opacity-50" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
                {{ __('messages.submit') }}
              </button>
            </div>

            {{-- Success/Error Messages --}}
            <div id="form-message" class="hidden mt-4 p-4 rounded-lg text-sm"></div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <script {!! nonce_attr() !!}>
    var use24hr = {{ $use24hr ? 'true' : 'false' }};

    function parseTimeToMinutes(timeStr) {
        if (!timeStr) return null;
        timeStr = timeStr.trim();
        var match24 = timeStr.match(/^(\d{1,2}):(\d{2})$/);
        if (match24 && use24hr) {
            var h = parseInt(match24[1], 10);
            var m = parseInt(match24[2], 10);
            if (h >= 0 && h <= 23 && m >= 0 && m <= 59) return h * 60 + m;
        }
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

    function initPartTimePicker(inputEl, dropdownEl) {
        if (inputEl._timepickerInit) return;
        inputEl._timepickerInit = true;
        var timeOptions = [];
        for (var m = 0; m < 1440; m += 30) {
            timeOptions.push(formatMinutesToTime(m));
        }
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
            items.forEach(function(el) { el.classList.remove('hidden'); });
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
            if (minutes === null) minutes = 540;
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
        inputEl.addEventListener('focus', function() { openDropdown(); });
        inputEl.addEventListener('click', function() {
            if (!dropdownEl.classList.contains('open')) openDropdown();
        });
        inputEl.addEventListener('input', function() {
            filterItems();
            if (dropdownEl.classList.contains('open')) {
                var visible = getVisibleItems();
                if (visible.length > 0) setHighlight(0);
            }
        });
        inputEl.addEventListener('blur', function() { closeDropdown(); });
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
        document.addEventListener('mousedown', function(e) {
            if (!inputEl.contains(e.target) && !dropdownEl.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    function toggleLocationFields() {
      var isInPerson = document.getElementById('in_person').checked;
      var locationFields = document.getElementById('location-fields');
      locationFields.style.display = isInPerson ? 'block' : 'none';
      if (!isInPerson) {
        locationFields.querySelectorAll('input, select').forEach(function(el) { el.value = ''; });
      }
    }

    function toggleOnlineUrl() {
      var isOnline = document.getElementById('is_online').checked;
      var urlField = document.getElementById('online-url-field');
      urlField.classList.toggle('hidden', !isOnline);
      if (!isOnline) {
        document.getElementById('event_url').value = '';
      }
    }

    function toggleAccountFields() {
      var createAccount = document.getElementById('create_account').checked;
      var accountFields = document.getElementById('account-fields');
      accountFields.classList.toggle('hidden', !createAccount);
      document.getElementById('account_password').required = createAccount;
      document.getElementById('account_terms').required = createAccount;
    }

    document.addEventListener('DOMContentLoaded', function() {
      // Bind checkbox handlers
      document.getElementById('in_person').addEventListener('change', toggleLocationFields);
      document.getElementById('is_online').addEventListener('change', toggleOnlineUrl);
      var createAccountEl = document.getElementById('create_account');
      if (createAccountEl) {
        createAccountEl.addEventListener('change', toggleAccountFields);
      }

      // Init Flatpickr date picker
      var dateInput = document.getElementById('event_date');
      var hiddenDate = document.getElementById('hidden_date');
      flatpickr(dateInput, {
        altInput: true,
        altFormat: 'M j, Y',
        dateFormat: 'Y-m-d',
        onChange: function(selectedDates, dateStr) {
          hiddenDate.value = dateStr;
        }
      });

      // Init custom time picker
      var timeInput = document.getElementById('event_start_time');
      var timeDropdown = document.getElementById('start_time_dropdown');
      var hiddenTime = document.getElementById('hidden_start_time');
      initPartTimePicker(timeInput, timeDropdown);

      timeInput.addEventListener('change', function() {
        var minutes = parseTimeToMinutes(timeInput.value);
        if (minutes !== null) {
          var h = Math.floor(minutes / 60);
          var m = minutes % 60;
          hiddenTime.value = (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
        }
      });
    });

    document.getElementById('booking-request-form').addEventListener('submit', function(e) {
      e.preventDefault();

      var form = this;
      var submitBtn = document.getElementById('submit-btn');
      var messageDiv = document.getElementById('form-message');

      submitBtn.disabled = true;
      submitBtn.textContent = '...';
      messageDiv.classList.add('hidden');

      var formData = new FormData(form);

      var descEl = document.getElementById('event_description');
      if (descEl._easyMDE) {
        formData.set('description', descEl._easyMDE.value());
      }

      fetch(form.action, {
        method: 'POST',
        headers: {
          'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
          'Accept': 'application/json',
        },
        body: formData,
      })
      .then(function(response) {
        return response.json().then(function(data) {
          return { ok: response.ok, data: data };
        });
      })
      .then(function(result) {
        if (result.ok && result.data.success) {
          messageDiv.className = 'mt-4 p-4 rounded-lg text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200';
          messageDiv.textContent = result.data.message;
          messageDiv.classList.remove('hidden');

          setTimeout(function() {
            window.location.href = result.data.redirect_url;
          }, 1500);
        } else {
          var errorMessage = result.data.message || result.data.error || '{{ __("messages.error_occurred") }}';

          if (result.data.errors) {
            var errors = Object.values(result.data.errors);
            errorMessage = errors.map(function(e) { return e[0]; }).join('\n');
          }

          messageDiv.className = 'mt-4 p-4 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200';
          messageDiv.textContent = errorMessage;
          messageDiv.classList.remove('hidden');
          submitBtn.disabled = false;
          submitBtn.textContent = '{{ __("messages.submit") }}';
        }
      })
      .catch(function() {
        messageDiv.className = 'mt-4 p-4 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200';
        messageDiv.textContent = '{{ __("messages.error_occurred") }}';
        messageDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = '{{ __("messages.submit") }}';
      });
    });
  </script>

</x-app-guest-layout>
