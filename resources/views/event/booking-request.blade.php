{{-- noindex: a form, not content. Every schedule has one, and none is worth a search result. --}}
<x-app-guest-layout :role="$role" :showMobileBackground="true" :page-title="__('messages.booking_request')" :no-index="true">

@php
  $hasHeaderImage = ($role->header_image && ! in_array($role->header_image, ['none', 'logos'], true)) || $role->header_image_url;
  $use24hr = get_use_24_hour_time($role);
  $accentColor = $role->accent_color ?? '#4E81FA';
  $contrastColor = accent_contrast_color($accentColor);

  // Supplied by EventController::showBookingRequest(); the fallbacks keep the view renderable alone.
  $requiredFields ??= $role->bookingFormRequiredFields();
  $allowOnline ??= $role->bookingFormAllowsOnline();
  $askPhone ??= $role->bookingFormAsksPhone();
  $offerAccount ??= ! auth()->check() && public_registration_enabled();

  // Built here rather than inline: @json() splits its argument on commas.
  $bookingFormScript = [
    'required' => $requiredFields,
    'allowOnline' => (bool) $allowOnline,
    'isVenue' => $role->isVenue(),
  ];
  $bookingFormText = [
    'required' => __('messages.field_is_required'),
    'dateRequired' => __('messages.date_required'),
    'timeInvalid' => __('messages.booking_time_invalid'),
    'location' => __($allowOnline ? 'messages.booking_location_required' : 'messages.booking_venue_required'),
    'description' => __('messages.description'),
    'error' => __('messages.error_occurred'),
    'submit' => __('messages.submit'),
  ];
  $errorAnchorClass = 'mt-2 text-sm text-red-600 dark:text-red-400 hidden';
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
      background: rgb(var(--ap-bg));
      border-color: rgb(var(--ap-border));
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
      color: rgb(var(--ap-ink-2));
    }
    .time-dropdown-item:hover,
    .time-dropdown-item.highlighted {
      background: #e5e7eb;
      color: #111827;
    }
    .dark .time-dropdown-item:hover,
    .dark .time-dropdown-item.highlighted {
      background: rgb(var(--ap-border));
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
            @if ($role->header_image && ! in_array($role->header_image, ['none', 'logos'], true))
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
                        {{ $role->isTalent() ? __('messages.booking_request') : __('messages.submit_event') }}
                    </h2>
                    <h3 class="text-gray-700 dark:text-gray-300">
                        {{ $role->name }}
                    </h3>
                </div>
            @else
                <div>
                    <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                        {{ $role->isTalent() ? __('messages.booking_request') : __('messages.submit_event') }}
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
          {{-- novalidate: the submit handler below checks the form itself and reports only controls
               the visitor can see. With native validation, one required control inside a hidden
               section blocked every submit without a word (issue #124). --}}
          <form id="booking-request-form" method="POST" action="{{ route('event.booking_request.store', ['subdomain' => $role->subdomain]) }}" novalidate>
            @csrf
            <x-honeypot />

            {{-- Event Details Section --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.event_details') }}</h3>

            {{-- Owner-required default fields are marked with aria-required and checked by
                 defaultFieldErrors(), never with a native required attribute: the date input is
                 swapped for Flatpickr's readonly one and the description is hidden behind EasyMDE. --}}
            <div class="mb-4">
              <x-input-label for="event_name">
                {{ __('messages.event_name') }}
                @if ($requiredFields['event_name'])
                <span aria-hidden="true"> *</span>
                @endif
              </x-input-label>
              <x-text-input id="event_name" name="event_name" type="text" class="mt-1 block w-full"
                aria-describedby="error-event_name"
                :aria-required="$requiredFields['event_name'] ? 'true' : null" />
              <div id="error-event_name" data-error-for="event_name" class="{{ $errorAnchorClass }}"></div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
              <div>
                <x-input-label for="event_date">
                  {{ __('messages.date') }}
                  @if ($requiredFields['date_time'])
                  <span aria-hidden="true"> *</span>
                  @endif
                </x-input-label>
                <input type="text" id="event_date"
                  class="datepicker-date mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                  autocomplete="off" aria-label="{{ __('messages.date') }}" aria-describedby="error-date"
                  @if ($requiredFields['date_time']) aria-required="true" @endif />
                <input type="hidden" name="date" id="hidden_date" />
                <div id="error-date" data-error-for="date" class="{{ $errorAnchorClass }}"></div>
              </div>
              <div>
                <x-input-label for="event_start_time">
                  {{ __('messages.start_time') }}
                  @if ($requiredFields['date_time'])
                  <span aria-hidden="true"> *</span>
                  @endif
                </x-input-label>
                <div class="relative">
                  <input type="text" id="event_start_time"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                    autocomplete="off" aria-label="{{ __('messages.start_time') }}" aria-describedby="error-start_time"
                    @if ($requiredFields['date_time']) aria-required="true" @endif />
                  <div class="time-dropdown" id="start_time_dropdown"></div>
                </div>
                <input type="hidden" name="start_time" id="hidden_start_time" />
                <div id="error-start_time" data-error-for="start_time" class="{{ $errorAnchorClass }}"></div>
              </div>
            </div>

            <div class="mb-6">
              <x-input-label for="event_description">
                {{ __('messages.description') }}
                @if ($requiredFields['description'])
                <span aria-hidden="true"> *</span>
                @endif
              </x-input-label>
              {{-- The error ring goes on this wrapper: EasyMDE hides the textarea itself. --}}
              <div class="mt-1 rounded-lg" data-error-ring="description">
                <textarea id="event_description" name="description" rows="4" class="html-editor block w-full"
                  aria-describedby="error-description"
                  @if ($requiredFields['description']) aria-required="true" @endif></textarea>
              </div>
              <div id="error-description" data-error-for="description" class="{{ $errorAnchorClass }}"></div>
            </div>

            {{-- Custom fields the schedule chose to ask on the request form --}}
            @php $requestCustomFields = $role->isPro() ? $role->getRequestFormCustomFields() : []; @endphp
            @if (count($requestCustomFields))
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.additional_information') }}</h3>

            @foreach ($requestCustomFields as $fieldKey => $field)
            <x-custom-field-input :role="$role" :field="$field" :field-key="$fieldKey" :for-guest="true" />
            @endforeach
            @endif

            {{-- Location Section --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">
              {{ __('messages.location') }}
              @if ($requiredFields['location'])
              <span aria-hidden="true"> *</span>
              @endif
            </h3>

            @if ($allowOnline)
            <div class="mb-4">
              <fieldset @if ($requiredFields['location']) aria-describedby="error-location" @endif>
                <legend class="sr-only">{{ __('messages.location') }}</legend>
                <div class="flex flex-wrap items-center gap-6">
                  @if ($role->isVenue())
                    <input type="hidden" id="in_person" value="1">
                  @else
                    <div class="flex items-center">
                      <input id="in_person" type="checkbox" checked
                        class="h-4 w-4 border-gray-300 rounded"
                        style="accent-color: {{ $accentColor }}">
                      <label for="in_person" class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">
                        {{ __('messages.in_person') }}
                      </label>
                    </div>
                  @endif
                  <div class="flex items-center">
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
            @else
            {{-- The owner switched Online off, so every request is in person. --}}
            <input type="hidden" id="in_person" value="1">
            @endif

            @unless ($role->isVenue())
            <div id="error-location" data-error-for="location" class="mb-4 text-sm text-red-600 dark:text-red-400 hidden"></div>
            @endunless

            @if ($allowOnline)
            <div id="online-url-field" class="mb-4 hidden">
              <x-input-label for="event_url" :value="__('messages.event_url')" />
              <x-text-input id="event_url" name="event_url" type="url" class="mt-1 block w-full" autocomplete="off" aria-describedby="error-event_url" />
              <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.event_url_help') }}</p>
              <div id="error-event_url" data-error-for="event_url" class="{{ $errorAnchorClass }}"></div>
            </div>
            @endif

            <div id="location-fields">
              @if ($role->isVenue())
                {{-- Venue schedule: show venue info as read-only --}}
                <div class="mb-4 p-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ $role->name }}</div>
                  @if ($role->fullAddress())
                    <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $role->fullAddress() }}</div>
                  @endif
                </div>
              @else
                <div class="mb-4">
                  <x-input-label for="venue_name" :value="__('messages.venue_name')" />
                  <x-text-input id="venue_name" name="venue_name" type="text" class="mt-1 block w-full"
                    :aria-describedby="$requiredFields['location'] ? 'error-location' : null" />
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
              @endif
            </div>

            {{-- Contact Info Section --}}
            <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4 mt-6">{{ __('messages.contact_info') }}</h3>

            @if (auth()->check())
              <div class="mb-4 text-sm text-gray-600 dark:text-gray-400">
                {{ __('messages.logged_in_as') }} <strong>{{ auth()->user()->name }}</strong> ({{ auth()->user()->email }})
              </div>
            @else
              <div class="mb-4">
                {{-- Starred like every other required control on this page. These two are always
                     required of a guest, so the marker is unconditional. --}}
                <x-input-label for="contact_name">
                  {{ __('messages.name') }}
                  <span aria-hidden="true"> *</span>
                </x-input-label>
                <x-text-input id="contact_name" name="contact_name" type="text" class="mt-1 block w-full" required
                  aria-describedby="error-contact_name error-account_name" />
                <div id="error-contact_name" data-error-for="contact_name" class="{{ $errorAnchorClass }}"></div>
                <div id="error-account_name" data-error-for="account_name" class="{{ $errorAnchorClass }}"></div>
              </div>

              <div class="mb-4">
                <x-input-label for="contact_email">
                  {{ __('messages.email') }}
                  <span aria-hidden="true"> *</span>
                </x-input-label>
                <x-text-input id="contact_email" name="contact_email" type="email" class="mt-1 block w-full" required
                  aria-describedby="error-contact_email error-account_email" />
                <div id="error-contact_email" data-error-for="contact_email" class="{{ $errorAnchorClass }}"></div>
                {{-- createAndLoginUser() reports a taken address, or an install that does not take new
                     accounts, under account_email. --}}
                <div id="error-account_email" data-error-for="account_email" class="{{ $errorAnchorClass }}"></div>
              </div>

              {{-- Create Account Option. Only where an account can actually be created: a selfhost
                   that has not opened registration refuses it on the server. A schedule that requires
                   an account never gets here - showBookingRequest() sends those guests to sign up. --}}
              @if ($offerAccount)
              <div class="mb-4 mt-6 p-4 bg-gray-50 dark:bg-gray-700/50 rounded-lg border border-gray-200 dark:border-gray-600">
                <label class="inline-flex items-center cursor-pointer">
                  <input type="checkbox" id="create_account" name="create_account" value="1" class="rounded border-gray-300 dark:border-gray-600 shadow-sm" style="accent-color: {{ $accentColor }}">
                  <span class="ms-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.create_an_account') }}</span>
                </label>
                <p class="mt-1 text-xs text-gray-500 dark:text-gray-400 ms-6">{{ __('messages.create_account_benefits') }}</p>

                {{-- Rendered hidden and NOT required. toggleAccountFields() makes the two controls
                     required only while the box is ticked and the section is showing. --}}
                <div id="account-fields" class="hidden mt-4">
                  <div>
                    <x-input-label for="account_password" :value="__('messages.password')" />
                    <x-text-input id="account_password" name="password" type="password" class="mt-1 block w-full"
                      autocomplete="new-password" minlength="8" aria-describedby="error-password" />
                    <div id="error-password" data-error-for="password" class="{{ $errorAnchorClass }}"></div>
                  </div>
                  <div class="mt-3">
                    <div class="relative flex items-start">
                      <div class="flex h-6 items-center">
                        <input id="account_terms" name="terms" type="checkbox" aria-describedby="error-terms"
                          class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 shadow-sm" style="accent-color: {{ $accentColor }}">
                      </div>
                      <div class="ms-3 text-sm leading-6">
                        <label for="account_terms" class="font-medium text-gray-700 dark:text-gray-300">
                          @if (config('app.hosted'))
                            {!! str_replace([':terms', ':privacy'], [
                                '<a href="' . policy_url('terms') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                                '<a href="' . policy_url('privacy') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">' . __('messages.privacy_policy') . '</a>'
                            ], __('messages.i_accept_the_terms_and_privacy')) !!}
                          @else
                            {!! str_replace([':terms'], [
                                '<a href="' . policy_url('terms', '/self-hosting-terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline"> ' . __('messages.terms_of_service') . '</a>',
                            ], __('messages.i_accept_the_terms')) !!}
                          @endif
                        </label>
                      </div>
                    </div>
                    <div id="error-terms" data-error-for="terms" class="{{ $errorAnchorClass }}"></div>
                  </div>
                </div>
              </div>
              @endif

              {{-- The server's answer when the owner switched Require Account on after this page loaded. --}}
              <div id="error-create_account" data-error-for="create_account" class="mb-4 text-sm text-red-600 dark:text-red-400 hidden"></div>
            @endif

            {{-- Asked of a signed-in visitor too, which is why it sits outside the block above: the
                 account supplies a name and an email but never a phone number. aria-required and a
                 starred label rather than a native required attribute, the contract every
                 owner-configurable field on this page follows (see the note above event_name);
                 defaultFieldErrors() has the matching check. --}}
            @if ($askPhone)
            <div class="mb-4">
              <x-input-label for="contact_phone">
                {{ __('messages.phone') }}
                @if ($requiredFields['phone'])
                <span aria-hidden="true"> *</span>
                @endif
              </x-input-label>
              <x-text-input id="contact_phone" name="contact_phone" type="tel" class="mt-1 block w-full"
                autocomplete="tel" maxlength="255" aria-describedby="error-contact_phone"
                :aria-required="$requiredFields['phone'] ? 'true' : null" />
              <div id="error-contact_phone" data-error-for="contact_phone" class="{{ $errorAnchorClass }}"></div>
            </div>
            @endif

            {{-- This form takes a name, an email and possibly a phone from somebody with no account
                 and hands them to the schedule, and said nothing about it. The Follow flow already
                 discloses exactly this (partials/follow-consent-modal.blade.php). Rendered for
                 signed-in visitors too: their account name and email are what gets stored.
                 policy_url(), never marketing_url() - a selfhoster's visitors must not be pointed at
                 our privacy policy. --}}
            <p class="mb-4 text-xs text-gray-500 dark:text-gray-400">
              {{ __('messages.booking_contact_privacy_note') }}
              <a href="{{ policy_url('privacy') }}" target="_blank" rel="noopener"
                 class="text-[var(--brand-blue)] hover:underline">{{ __('messages.privacy_policy') }}</a>
            </p>

            {{-- The owner's terms for requests, shown before the visitor sends one. --}}
            @if (filled($role->request_terms))
            @php $requestTermsText = $role->translatedRequestTerms(); @endphp
            <div class="mt-6 p-4 rounded-lg bg-gray-50 dark:bg-gray-800 border border-gray-200 dark:border-gray-700">
              <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.request_terms') }}</h3>
              <div class="text-sm text-gray-700 dark:text-gray-300 text-start break-words"
                dir="{{ content_dir($role, showing_translation($role) && filled($role->request_terms_en), $requestTermsText) }}">{!! nl2br(e($requestTermsText)) !!}</div>
            </div>
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
            <div id="form-message" class="hidden mt-4 p-4 rounded-lg text-sm" aria-live="polite"></div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <script {!! nonce_attr() !!}>
    var use24hr = {{ $use24hr ? 'true' : 'false' }};
    var bookingForm = @json($bookingFormScript);
    var bookingText = @json($bookingFormText);
    var ERROR_RING = ['ring-1', 'ring-red-500', 'border-red-500', 'dark:ring-red-400', 'dark:border-red-400'];

    function byId(id) {
        return document.getElementById(id);
    }

    function isCheckbox(el) {
        return !!el && el.type === 'checkbox';
    }

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

    // The three section toggles run on load as well as on change: a browser can restore a box's
    // checked state on reload while the section it controls renders hidden. Each one returns quietly
    // when its controls are not on the page - a venue has no In person box, and a signed-in visitor
    // or a closed install has no account section - because a throw here would abort the rest of the
    // DOMContentLoaded handler, date and time pickers included.
    function toggleLocationFields() {
      var inPerson = byId('in_person');
      var locationFields = byId('location-fields');
      if (!isCheckbox(inPerson) || !locationFields) return;

      locationFields.style.display = inPerson.checked ? 'block' : 'none';
      if (!inPerson.checked) {
        // Text inputs only: the country picker keeps its own hidden value in step with its display.
        locationFields.querySelectorAll('input[type="text"]').forEach(function(el) { el.value = ''; });
      }
    }

    function toggleOnlineUrl() {
      var isOnline = byId('is_online');
      var urlField = byId('online-url-field');
      if (!isOnline || !urlField) return;

      urlField.classList.toggle('hidden', !isOnline.checked);
      if (!isOnline.checked && byId('event_url')) {
        byId('event_url').value = '';
      }
    }

    function toggleAccountFields() {
      var createAccount = byId('create_account');
      var accountFields = byId('account-fields');
      if (!createAccount || !accountFields) return;

      accountFields.classList.toggle('hidden', !createAccount.checked);
      ['account_password', 'account_terms'].forEach(function(id) {
        if (byId(id)) byId(id).required = createAccount.checked;
      });
    }

    // Rebuild the posted date and time from what the visitor can see. The hidden inputs are only as
    // fresh as the last change event, and pressing Enter can submit without one. Returns false when
    // the typed time does not parse.
    function syncHiddenDateTime() {
      var picker = byId('event_date')._flatpickr;
      byId('hidden_date').value = picker && picker.selectedDates.length
        ? picker.formatDate(picker.selectedDates[0], 'Y-m-d')
        : '';

      var typed = byId('event_start_time').value.trim();
      var minutes = parseTimeToMinutes(typed);
      byId('hidden_start_time').value = minutes === null
        ? ''
        : ('0' + Math.floor(minutes / 60)).slice(-2) + ':' + ('0' + (minutes % 60)).slice(-2);

      return typed === '' || minutes !== null;
    }

    // Sent as typed; only the emptiness check ignores surrounding whitespace.
    function descriptionValue() {
      var textarea = byId('event_description');
      return textarea._easyMDE ? textarea._easyMDE.value() : textarea.value;
    }

    function locationGiven() {
      var inPerson = byId('in_person');
      var isOnline = byId('is_online');
      var hasVenue = ['venue_name', 'venue_address1', 'venue_city'].some(function(id) {
        return byId(id) && byId(id).value.trim() !== '';
      });

      return ((!isCheckbox(inPerson) || inPerson.checked) && hasVenue)
        || (bookingForm.allowOnline && isCheckbox(isOnline) && isOnline.checked);
    }

    // The owner-required default fields, plus the date/time pairing the server enforces anyway: a
    // date is only saved together with a time.
    function defaultFieldErrors() {
      var required = bookingForm.required;
      var errors = {};
      var timeParses = syncHiddenDateTime();
      var hasDate = byId('hidden_date').value !== '';
      var hasTime = byId('hidden_start_time').value !== '';

      if (required.event_name && byId('event_name').value.trim() === '') {
        errors.event_name = [bookingText.required];
      }

      if (!timeParses) {
        errors.start_time = [bookingText.timeInvalid];
      } else if ((required.date_time || hasDate) && !hasTime) {
        errors.start_time = [bookingText.required];
      }

      if ((required.date_time || hasTime) && !hasDate) {
        errors.date = [bookingText.dateRequired];
      }

      if (required.description && descriptionValue().trim() === '') {
        errors.description = [bookingText.required];
      }

      if (required.location && !bookingForm.isVenue && !locationGiven()) {
        errors.location = [bookingText.location];
      }

      // Element-guarded: a page cached while the owner still asked for a phone can carry
      // required.phone in its payload without the input being on the page.
      if (required.phone && byId('contact_phone') && byId('contact_phone').value.trim() === '') {
        errors.contact_phone = [bookingText.required];
      }

      return errors;
    }

    // A required multiselect cannot carry the `required` attribute (any one box satisfies it), so
    // check the group here rather than letting the server be the only gate - the other request form
    // checks the same rule client-side.
    function requiredGroupErrors(form) {
      var errors = {};
      form.querySelectorAll('[data-required-group]').forEach(function(group) {
        if (!group.querySelector('input[type="checkbox"]:checked')) {
          errors[group.dataset.requiredGroup] = [bookingText.required];
        }
      });

      return errors;
    }

    // The form is novalidate, so the browser's own checks (required contact details, email and URL
    // formats, custom field patterns) run here - but only for controls the visitor can see. Anything
    // hidden is the server's job.
    function firstVisibleInvalidControl(form) {
      return Array.prototype.find.call(form.elements, function(control) {
        return control.willValidate && control.getClientRects().length > 0 && !control.checkValidity();
      }) || null;
    }

    function controlsFor(key) {
      return Array.from(document.querySelectorAll('[aria-describedby]')).filter(function(el) {
        return el.getAttribute('aria-describedby').split(/\s+/).indexOf('error-' + key) !== -1;
      });
    }

    function markInvalid(key, invalid) {
      var ring = document.querySelector('[data-error-ring="' + CSS.escape(key) + '"]');
      controlsFor(key).forEach(function(el) {
        if (invalid) {
          el.setAttribute('aria-invalid', 'true');
        } else {
          el.removeAttribute('aria-invalid');
        }
        if (!ring && el.tagName !== 'FIELDSET') {
          ERROR_RING.forEach(function(cls) { el.classList.toggle(cls, invalid); });
        }
      });
      if (ring) {
        ERROR_RING.forEach(function(cls) { ring.classList.toggle(cls, invalid); });
      }
    }

    // Validation errors come back as JSON (this form posts over fetch), so the per-field messages
    // have to be written into the [data-error-for] anchors by hand.
    function clearFieldErrors() {
      document.querySelectorAll('[data-error-for]').forEach(function(el) {
        el.textContent = '';
        el.classList.add('hidden');
        markInvalid(el.dataset.errorFor, false);
      });
    }

    function showFieldErrors(errors) {
      if (!errors) return;

      Object.keys(errors).forEach(function(key) {
        // Laravel reports a multiselect member as custom_field_values.key.2; the anchor is on the
        // field itself, so drop a trailing numeric segment before looking it up.
        var anchorKey = key.replace(/\.\d+$/, '');
        var el = document.querySelector('[data-error-for="' + CSS.escape(anchorKey) + '"]');
        if (!el) return;

        el.textContent = Array.isArray(errors[key]) ? errors[key][0] : errors[key];
        el.classList.remove('hidden');
        markInvalid(anchorKey, true);
      });
    }

    // Scroll to the first message in page order (the server lists custom fields last although they
    // render above the location) and put the cursor in the field it belongs to.
    function focusFirstError(form) {
      var anchor = form.querySelector('[data-error-for]:not(.hidden)');
      if (!anchor) return false;

      anchor.scrollIntoView({ behavior: 'smooth', block: 'center' });

      var key = anchor.dataset.errorFor;
      var textarea = byId('event_description');
      if (key === 'description' && textarea._easyMDE) {
        textarea._easyMDE.codemirror.focus();
        return true;
      }

      var target = controlsFor(key).filter(function(el) {
        return el.tagName !== 'FIELDSET' && el.getClientRects().length > 0;
      })[0];
      if (!target && key === 'location') {
        target = [byId('venue_name'), byId('in_person'), byId('is_online')].filter(function(el) {
          return el && el.type !== 'hidden' && el.getClientRects().length > 0;
        })[0];
      }
      if (target) {
        target.focus({ preventScroll: true });
      }

      return true;
    }

    // Flatpickr swaps the date input for its own display input (or a native one on mobile), which
    // carries none of the original's label or aria wiring. Hand it over.
    function wireDatePickerAccessibility(picker) {
      var display = picker.mobileInput || picker.altInput;
      var original = byId('event_date');
      if (!display || display === original) return;

      display.id = 'event_date_display';
      ['aria-label', 'aria-describedby', 'aria-required'].forEach(function(name) {
        if (original.hasAttribute(name)) display.setAttribute(name, original.getAttribute(name));
      });
      var label = document.querySelector('label[for="event_date"]');
      if (label) label.setAttribute('for', display.id);
    }

    document.addEventListener('DOMContentLoaded', function() {
      var inPersonEl = byId('in_person');
      if (isCheckbox(inPersonEl)) {
        inPersonEl.addEventListener('change', toggleLocationFields);
      }
      if (byId('is_online')) {
        byId('is_online').addEventListener('change', toggleOnlineUrl);
      }
      if (byId('create_account')) {
        byId('create_account').addEventListener('change', toggleAccountFields);
      }

      // Init Flatpickr date picker
      var picker = flatpickr(byId('event_date'), {
        altInput: true,
        altFormat: 'M j, Y',
        dateFormat: 'Y-m-d',
        onChange: function() {
          syncHiddenDateTime();
        }
      });
      wireDatePickerAccessibility(picker);

      // Init custom time picker
      initPartTimePicker(byId('event_start_time'), byId('start_time_dropdown'));
      byId('event_start_time').addEventListener('change', syncHiddenDateTime);

      // app.js creates the description editor in its own DOMContentLoaded listener, which runs after
      // this one, so its input only exists on the next tick.
      setTimeout(function() {
        var textarea = byId('event_description');
        if (!textarea._easyMDE) return;
        var input = textarea._easyMDE.codemirror.getInputField();
        ['aria-describedby', 'aria-required'].forEach(function(name) {
          if (textarea.hasAttribute(name)) input.setAttribute(name, textarea.getAttribute(name));
        });
        input.setAttribute('aria-label', bookingText.description);
      }, 0);

      toggleLocationFields();
      toggleOnlineUrl();
      toggleAccountFields();
      syncHiddenDateTime();
    });

    document.getElementById('booking-request-form').addEventListener('submit', function(e) {
      e.preventDefault();

      var form = this;
      var submitBtn = byId('submit-btn');
      var messageDiv = byId('form-message');

      messageDiv.classList.add('hidden');
      clearFieldErrors();

      var errors = Object.assign(defaultFieldErrors(), requiredGroupErrors(form));
      var invalidControl = firstVisibleInvalidControl(form);

      if (Object.keys(errors).length || invalidControl) {
        showFieldErrors(errors);
        // The browser's own message for a visible control, otherwise the first of ours.
        if (invalidControl) {
          invalidControl.reportValidity();
        } else {
          focusFirstError(form);
        }
        return;
      }

      submitBtn.disabled = true;
      submitBtn.textContent = '...';

      var formData = new FormData(form);
      formData.set('description', descriptionValue());

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
          var errorMessage = result.data.message || result.data.error || bookingText.error;

          if (result.data.errors) {
            var errorList = Object.values(result.data.errors);
            errorMessage = errorList.map(function(e) { return e[0]; }).join('\n');
          }

          // Also place each message next to its own field, so a failed custom-field pattern points
          // at the input that has to change instead of only landing in the summary below.
          clearFieldErrors();
          showFieldErrors(result.data.errors);

          messageDiv.className = 'mt-4 p-4 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200';
          messageDiv.style.whiteSpace = 'pre-line';
          messageDiv.textContent = errorMessage;
          messageDiv.classList.remove('hidden');
          submitBtn.disabled = false;
          submitBtn.textContent = bookingText.submit;

          focusFirstError(form);
        }
      })
      .catch(function() {
        messageDiv.className = 'mt-4 p-4 rounded-lg text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200';
        messageDiv.textContent = bookingText.error;
        messageDiv.classList.remove('hidden');
        submitBtn.disabled = false;
        submitBtn.textContent = bookingText.submit;
      });
    });
  </script>

</x-app-guest-layout>
