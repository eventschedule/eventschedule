<x-app-guest-layout :role="$role" :showMobileBackground="true">

@php
  $hasHeaderImage = ($role->header_image && $role->header_image !== 'none') || $role->header_image_url;
@endphp

  @if ($role->profile_image_url && !$hasHeaderImage && $role->language_code == 'en')
  <div class="pt-8"></div>
  @endif

  <main>
    <div>
      <div class="container mx-auto pt-7 pb-20 px-5 max-w-3xl">
        <div class="bg-white dark:bg-gray-800 mb-4 {{ !$hasHeaderImage && $role->profile_image_url ? 'pt-16' : '' }} rounded-lg shadow-md">
          <div
            class="relative before:block before:absolute before:bg-[#00000033] before:-inset-0"
          >
            @if ($role->header_image && $role->header_image !== 'none')
            <picture>
              <source srcset="{{ asset('images/headers') }}/{{ $role->header_image }}.webp" type="image/webp">
              <img
                class="block max-h-72 w-full object-cover rounded-t-2xl"
                src="{{ asset('images/headers') }}/{{ $role->header_image }}.png"
              />
            </picture>
            @elseif ($role->header_image_url)
            <img
              class="block max-h-72 w-full object-cover rounded-t-2xl"
              src="{{ $role->header_image_url }}"
            />
            @endif
          </div>
          <div class="px-4 sm:px-8 pb-4 relative z-10">
            @if ($role->profile_image_url)
            <div class="rounded-lg w-[130px] h-[130px] -mt-[100px] -ms-1 mb-6 bg-white dark:bg-gray-800 flex items-center justify-center">
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
                    <a href="{{ $role->getGuestUrl() }}" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
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
                    <a href="{{ $role->getGuestUrl() }}" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
                        {{ __('messages.view_schedule') }}
                    </a>
                </div>
            @endif
            </div>
          </div>
        </div>

        {{-- Booking Request Form --}}
        <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 sm:p-8">
          <form id="booking-request-form" method="POST" action="{{ route('event.booking_request.store', ['subdomain' => $role->subdomain]) }}">
            @csrf

            {{-- Event Details Section --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.event_details') }}</h3>

            <div class="mb-4">
              <x-input-label for="event_name" :value="__('messages.event_name')" />
              <x-text-input id="event_name" name="event_name" type="text" class="mt-1 block w-full" required />
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
              <div>
                <x-input-label for="event_date" :value="__('messages.date')" />
                <x-text-input id="event_date" name="date" type="date" class="mt-1 block w-full" required />
              </div>
              <div>
                <x-input-label for="event_start_time" :value="__('messages.start_time')" />
                <x-text-input id="event_start_time" name="start_time" type="time" class="mt-1 block w-full" required />
              </div>
            </div>

            <div class="mb-4">
              <x-input-label for="event_duration" :value="__('messages.duration_in_hours')" />
              <x-text-input id="event_duration" name="duration" type="number" step="0.5" min="0.5" class="mt-1 block w-full sm:w-1/2" />
            </div>

            <div class="mb-6">
              <x-input-label for="event_description" :value="__('messages.description')" />
              <textarea id="event_description" name="description" rows="4" class="mt-1 block w-full border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-100 rounded-md shadow-sm focus:border-indigo-500 focus:ring-indigo-500"></textarea>
            </div>

            {{-- Location Section --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.location') }}</h3>

            <div class="mb-4">
              <label class="inline-flex items-center cursor-pointer">
                <input type="checkbox" id="is_online" name="is_online" value="1" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500" onchange="toggleLocationFields()">
                <span class="ms-2 text-sm text-gray-700 dark:text-gray-300">{{ __('messages.online') }}</span>
              </label>
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
                  <input type="checkbox" id="create_account" name="create_account" value="1" class="rounded border-gray-300 dark:border-gray-600 text-indigo-600 shadow-sm focus:ring-indigo-500" {{ $role->require_account ? 'checked disabled' : '' }} onchange="toggleAccountFields()">
                  <span class="ms-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.create_an_account') }}</span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 {{ is_rtl() ? 'me-6' : 'ms-6' }}">{{ __('messages.create_account_benefits') }}</p>

                <div id="account-fields" class="{{ $role->require_account ? '' : 'hidden' }} mt-4">
                  <div class="mb-4">
                    <x-input-label for="account_name" :value="__('messages.name')" />
                    <x-text-input id="account_name" name="name" type="text" class="mt-1 block w-full" />
                  </div>
                  <div class="mb-4">
                    <x-input-label for="account_email" :value="__('messages.email')" />
                    <x-text-input id="account_email" name="email" type="email" class="mt-1 block w-full" />
                  </div>
                  <div>
                    <x-input-label for="account_password" :value="__('messages.password')" />
                    <x-text-input id="account_password" name="password" type="password" class="mt-1 block w-full" />
                  </div>
                </div>
              </div>

              @if ($role->require_account)
                <input type="hidden" name="create_account" value="1">
              @endif
            @endif

            {{-- Submit Button --}}
            <div class="flex items-center justify-end gap-3 mt-8">
              <a href="{{ $role->getGuestUrl() }}" class="px-4 py-3 text-base text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200">
                {{ __('messages.cancel') }}
              </a>
              <button type="submit" id="submit-btn" class="px-4 py-3 text-base bg-indigo-600 text-white rounded-md hover:bg-indigo-700 transition-all duration-200 disabled:opacity-50">
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
    function toggleLocationFields() {
      var isOnline = document.getElementById('is_online').checked;
      var locationFields = document.getElementById('location-fields');
      locationFields.style.display = isOnline ? 'none' : 'block';
    }

    function toggleAccountFields() {
      var createAccount = document.getElementById('create_account').checked;
      var accountFields = document.getElementById('account-fields');
      accountFields.classList.toggle('hidden', !createAccount);
    }

    document.getElementById('booking-request-form').addEventListener('submit', function(e) {
      e.preventDefault();

      var form = this;
      var submitBtn = document.getElementById('submit-btn');
      var messageDiv = document.getElementById('form-message');

      submitBtn.disabled = true;
      submitBtn.textContent = '...';
      messageDiv.classList.add('hidden');

      var formData = new FormData(form);

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
