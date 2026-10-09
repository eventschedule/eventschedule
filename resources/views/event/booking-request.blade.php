{{-- noindex: a form, not content. Every schedule has one, and none is worth a search result. --}}
<x-app-guest-layout :role="$role" :showMobileBackground="true" :page-title="$role->isTalent() && $role->user_id ? __('messages.booking_request') : __('messages.submit_event')" :no-index="true">

@php
  $hasHeaderImage = ($role->header_image && ! in_array($role->header_image, \App\Models\Role::HEADER_IMAGE_KEYWORDS, true)) || $role->header_image_url;
  $accentColor = $role->accent_color ?: '#4E81FA';
  $contrastColor = accent_contrast_color($accentColor);
  $accentOnLight = \App\Utils\ColorUtils::readableAccentColor($accentColor, '#ffffff', '#111827');
  $accentOnDark = \App\Utils\ColorUtils::readableAccentColor($accentColor, '#252526', '#ffffff');
  $use24hr = get_use_24_hour_time($role);
  $scheduleName = $role->getDisplayName(true);
  // A schedule nobody has claimed has no address of its own to hand out, but it has a page.
  $scheduleUrl = $role->getGuestUrl() ?: route('role.view_guest', ['subdomain' => $role->subdomain]);

  // Supplied by EventController::showBookingRequest(); the fallbacks keep the view renderable alone.
  $requiredFields ??= $role->bookingFormRequiredFields();
  $allowOnline ??= $role->bookingFormAllowsOnline();
  $askPhone ??= $role->bookingFormAsksPhone();
  $offerAccount ??= ! auth()->check() && public_registration_enabled();
  $needsCode = config('app.hosted') && ! config('app.is_testing');
  // Nobody owns this schedule, so an anonymous request has no owner to be filed under: there
  // the form's optional account is the only way through, and the page says so before Submit.
  $mustHaveAccount = ! auth()->check() && ! $role->user;
  // ...and where the site is not taking new accounts, a signed-out visitor cannot send at all.
  // Said at the top, with the way in, and not discovered after the form is filled.
  $cannotSend = $mustHaveAccount && ! $offerAccount;

  // The schedule's own questions, as data: this page is a Vue mount, so owner-written labels and
  // options echoed into it would be compiled as a template.
  $requestCustomFields = [];
  $requestCustomFieldValues = [];
  if ($role->isPro()) {
      foreach ($role->getRequestFormCustomFields() as $fieldKey => $field) {
          $requestCustomFields[] = [
              'key' => $fieldKey,
              'label' => $role->customFieldLabel($field, $fieldKey, true),
              'type' => $field['type'] ?? 'string',
              'options' => \App\Models\Role::customFieldOptions($field),
              'required' => ! empty($field['required']),
              'regex' => $field['regex'] ?? '',
              'regex_hint' => $field['regex_hint'] ?? '',
              // The answer is kept off the schedule's public pages. Worth telling the person answering.
              'private' => ! empty($field['private']),
              // And the opposite: the schedule prints this answer on the event's public page
              // once it accepts the request. The person typing it is the one who should know.
              'on_event' => \App\Models\Role::isEventCustomFieldOnEventPage($field),
          ];
          $requestCustomFieldValues[$fieldKey] = ($field['type'] ?? 'string') === 'multiselect'
              ? []
              : (($field['type'] ?? 'string') === 'switch' ? '0' : '');
      }
  }

  // What pressing Send leads to, by the one rule that decides it (Role::autoAcceptsEventFrom()).
  $viewer = auth()->user();
  $landsAtOnce = $role->autoAcceptsEventFrom($viewer);
  // Nobody runs this page, so nobody will read a request or answer one: what is sent here is an
  // event that appears at once. The page says that, and does not call it a booking request.
  $nobodyOwnsIt = ! $role->user_id;
  $asksToBook = $role->isTalent() && ! $nobodyOwnsIt;
  $whatHappens = match (true) {
      $nobodyOwnsIt => __('messages.booking_unclaimed_note', ['name' => $scheduleName]),
      $landsAtOnce => __('messages.guest_submit_instant', ['name' => $scheduleName]),
      $role->isTalent() => __('messages.booking_request_reviewed', ['name' => $scheduleName]),
      default => __('messages.guest_submit_reviewed', ['name' => $scheduleName]),
  };
  $requestTermsText = filled($role->request_terms) ? $role->translatedRequestTerms() : null;
  $sendLabel = $asksToBook ? __('messages.send_request') : __('messages.submit_event');

  // An event that appears at once is a public listing the moment it is sent, so it needs what a
  // listing needs, whatever the owner left optional for requests they read first. The endpoint
  // asks the same.
  if ($landsAtOnce) {
      $requiredFields['event_name'] = true;
      $requiredFields['date_time'] = true;
  }

  $in = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm';
  $lb = 'block font-medium text-sm text-gray-700 dark:text-gray-300';
  $star = '<span class="text-red-500" aria-hidden="true">*</span>';
  $opt = '<span class="font-normal text-gray-500 dark:text-gray-400">('.e(__('messages.optional')).')</span>';

  $words = [
      'required' => __('messages.field_required'),
      'valid_email' => __('messages.please_enter_valid_email'),
      'valid_url' => __('messages.invalid_url'),
      'password_min' => __('messages.password_min_chars'),
      'error' => __('messages.error_occurred'),
      'too_many' => __('messages.too_many_attempts'),
      'ready' => __('messages.ready_to_submit', ['name' => $scheduleName]),
      'timezone_mismatch' => __('messages.guest_submit_timezone_mismatch'),
      'show_password' => __('messages.show_password'),
      'hide_password' => __('messages.hide_password'),
      'questions' => __('messages.questions_from', ['name' => $scheduleName]),
      'still_needed' => __('messages.still_needed'),
      'check' => __('messages.guest_submit_check'),
      'expired' => __('messages.page_expired_reload'),
      'time_like' => __('messages.enter_time_like'),
      'location' => __($allowOnline ? 'messages.booking_location_required' : 'messages.booking_venue_required'),
      'reply_to' => __('messages.booking_step_reply'),
      // Whole sentences as data, with the address dropped in by the script: never built by joining.
      'reply_note' => __('messages.booking_reply_note', ['name' => $scheduleName]),
      'has_account' => __('messages.booking_email_has_account'),
      'email_you' => __('messages.submitted_step_email'),
      'optional' => __('messages.optional'),
  ];
  $labels = [
      'name' => __('messages.event_name'),
      'event_date' => __('messages.date'),
      'event_start_time' => __('messages.start_time'),
      'event_end_time' => __('messages.end_time'),
      'description' => __('messages.description'),
      'location' => __('messages.location'),
      'event_url' => __('messages.event_url'),
      'account_name' => __('messages.name'),
      'account_email' => __('messages.email'),
      'contact_phone' => __('messages.phone'),
      'account_password' => __('messages.password'),
      'terms' => __('messages.terms_of_service'),
      'create_account' => __('messages.create_an_account'),
  ];
@endphp

<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
@if (\App\Utils\TurnstileUtils::isEnabled() && $offerAccount && $needsCode)
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer {!! nonce_attr() !!}></script>
@endif

<style {!! nonce_attr() !!}>
@include('partials.request-form-styles')
    {{-- The country picker is a library's own control: give it the height and edge of the boxes around it. --}}
    #gs-page .iti--country-only { display: block; width: 100%; margin-top: 0.25rem; }
    #gs-page .gs-fixed-place { padding: 0.875rem 1rem; border-radius: 0.5rem; background: rgb(0 0 0 / 0.03); }
    .dark #gs-page .gs-fixed-place { background: rgb(255 255 255 / 0.04); }
    {{-- The phone box is a tel input, which the guest pages' text-box sizing does not name. --}}
    #gs-page #contact_phone { padding: 0.75rem 1rem; font-size: 115%; }
    #gs-page .gs-inline-link { color: var(--brand-blue); text-decoration: underline; }
    #gs-page .gs-account { margin-top: 1.25rem; padding: 1rem; border-radius: 0.75rem; border: 1px solid rgb(var(--ap-border)); }
</style>

{{-- The country picker's own assets, once, outside the Vue mount (a mount re-creates its markup,
     and a script inside one never runs). The picker inside the form is then two plain inputs. --}}
@unless ($role->isVenue())
<div hidden><x-country-input name="_country_picker_assets" id="_country_picker_assets" value="us" :auto-init="false" :disabled="true" /></div>
@endunless

  @if ($role->profile_image_url && !$hasHeaderImage && $role->language_code == 'en')
  <div class="pt-8"></div>
  @endif

  <main>
    <div>
      <div id="gs-page" class="container mx-auto pt-7 pb-20 px-5 max-w-3xl">
        {{-- Header card --}}
        <div class="ap-card mb-4 {{ !$hasHeaderImage && $role->profile_image_url ? 'pt-16' : '' }} rounded-lg shadow-md">
          <div class="relative before:block before:absolute before:bg-[#00000033] before:-inset-0">
            @if ($role->header_image && ! in_array($role->header_image, \App\Models\Role::HEADER_IMAGE_KEYWORDS, true))
            <picture>
              <source srcset="{{ asset('images/headers') }}/{{ $role->header_image }}.webp" type="image/webp">
              <img class="block max-h-72 w-full object-cover rounded-t-2xl" src="{{ asset('images/headers') }}/{{ $role->header_image }}.png" alt="" />
            </picture>
            @elseif ($role->header_image_url)
            <img class="block max-h-72 w-full object-cover rounded-t-2xl" src="{{ $role->header_image_url }}" alt="" />
            @endif
          </div>
          <div class="px-4 sm:px-8 pb-5 relative z-10">
            @if ($role->profile_image_url)
            <div class="rounded-lg w-[130px] h-[130px] -mt-[100px] -ms-1 mb-6 bg-white dark:bg-gray-800 flex items-center justify-center">
              <img class="rounded-lg w-[120px] h-[120px] object-cover" src="{{ $role->profile_image_url }}" alt="" />
            </div>
            @else
            <div style="height: 42px;"></div>
            @endif

            <div class="flex justify-between items-start gap-4">
              <div class="min-w-0">
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:text-2xl sm:tracking-tight">
                  {{ $asksToBook ? __('messages.booking_request') : __('messages.submit_your_event') }}
                </h2>
                <h3 class="text-gray-700 dark:text-gray-300">{{ $scheduleName }}</h3>
              </div>
              @if ($scheduleUrl)
              <a href="{{ $scheduleUrl }}" class="gs-quiet hidden sm:block">{{ __('messages.view_schedule') }}</a>
              @endif
            </div>

            {{-- What pressing Send leads to, before any of the typing. --}}
            <p class="gs-next" data-gs-before>
              <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                @if ($landsAtOnce)
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
                @else
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                @endif
              </svg>
              <span>{{ $whatHappens }}</span>
            </p>

            @if ($cannotSend)
            <div class="mt-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2" data-gs-before>
              <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
              </svg>
              <p class="text-sm text-amber-700 dark:text-amber-300">{{ __('messages.booking_cannot_send') }} <x-link href="{{ app_url(route('login', [], false)) }}">{{ __('messages.log_in') }}</x-link></p>
            </div>
            @endif

            {{-- The owner's terms for requests, read before the form and not after it. Outside the
                 Vue mount, so the owner's text is never compiled as a template. --}}
            @if ($requestTermsText)
            <div class="gs-terms" data-gs-before>
              <h3>{{ __('messages.request_terms') }}</h3>
              <div dir="{{ content_dir($role, showing_translation($role) && filled($role->request_terms_en), $requestTermsText) }}">{!! nl2br(e($requestTermsText)) !!}</div>
            </div>
            @endif
          </div>
        </div>

        <div id="event-submit-app" data-vue-root>
          <form id="booking-request-form" v-show="!submitted && step === 'form'" @submit.prevent="submitEvent" novalidate>

            <div v-if="draftRestored" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg flex items-center gap-3 text-sm text-blue-800 dark:text-blue-200" aria-live="polite">
              <span class="flex-1">{{ __('messages.booking_draft_restored') }}</span>
              <button type="button" @click="startFresh" class="underline hover:no-underline shrink-0">{{ __('messages.start_fresh') }}</button>
              <button type="button" @click="draftRestored = false" aria-label="{{ __('messages.close') }}" class="shrink-0 p-1 text-blue-400 hover:text-blue-600 dark:hover:text-blue-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>

            {{-- 1. The event --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h">{{ __('messages.event_details') }}</h3>

                <div class="mb-4">
                  <label for="submit_event_name" class="{{ $lb }}">{{ __('messages.event_name') }} {!! $requiredFields['event_name'] ? $star : $opt !!}</label>
                  <input id="submit_event_name" type="text" v-model="event.name" autocomplete="off" maxlength="255" aria-required="{{ $requiredFields['event_name'] ? 'true' : 'false' }}" :class="bad('name')" :aria-invalid="msg('name') ? 'true' : 'false'" :aria-describedby="msg('name') ? 'err_name' : null"
                    class="{{ $in }}">
                  <p v-if="msg('name')" id="err_name" class="gs-err">@{{ msg('name') }}</p>
                </div>

                <div class="gs-grid3">
                  <div>
                    <label for="submit_event_date" id="submit_event_date_label" class="{{ $lb }}">{{ __('messages.date') }}
                      {{-- A date and a time are kept together or not at all, so each becomes needed once the other is given. --}}
                      <span v-if="required.date_time || event.event_start_time" class="text-red-500" aria-hidden="true">*</span><span v-else class="font-normal text-gray-500 dark:text-gray-400" v-text="'(' + words.optional + ')'"></span></label>
                    <input id="submit_event_date" type="text" autocomplete="off" aria-label="{{ __('messages.date') }}" data-required="{{ $requiredFields['date_time'] ? '1' : '0' }}" class="{{ $in }}">
                    <p v-if="msg('event_date')" id="err_event_date" class="gs-err">@{{ msg('event_date') }}</p>
                  </div>
                  <div>
                    <label for="submit_event_time" class="{{ $lb }}">{{ __('messages.start_time') }}
                      <span v-if="required.date_time || event.event_date || event.event_end_time" class="text-red-500" aria-hidden="true">*</span><span v-else class="font-normal text-gray-500 dark:text-gray-400" v-text="'(' + words.optional + ')'"></span></label>
                    <div class="relative">
                      <input id="submit_event_time" type="text" v-model="timeText.start" autocomplete="off" dir="ltr" role="combobox" aria-autocomplete="list" aria-required="{{ $requiredFields['date_time'] ? 'true' : 'false' }}"
                        aria-controls="submit_event_time_list" :aria-expanded="timeOpen === 'start' ? 'true' : 'false'" :placeholder="timeExample('start')"
                        :class="bad('event_start_time')" :aria-invalid="msg('event_start_time') ? 'true' : 'false'" :aria-describedby="msg('event_start_time') ? 'err_event_start_time' : null"
                        @focus="openTime('start')" @input="openTime('start')" @blur="commitTime('start')" @keydown="timeKey($event, 'start')"
                        class="{{ $in }}">
                      <ul id="submit_event_time_list" class="gs-times" role="listbox" tabindex="-1" @mousedown.prevent v-show="timeOpen === 'start' && timeChoices.length">
                        <li v-for="(o, i) in timeChoices" :key="o.value" role="option" :aria-selected="i === timeHighlight ? 'true' : 'false'"
                          :class="{ 'is-on': i === timeHighlight }" @mousedown.prevent="pickTime('start', o.value)" v-text="o.label"></li>
                      </ul>
                    </div>
                    <p v-if="msg('event_start_time')" id="err_event_start_time" class="gs-err">@{{ msg('event_start_time') }}</p>
                  </div>
                  <div>
                    <label for="submit_event_end_time" class="{{ $lb }}">{{ __('messages.end_time') }} {!! $opt !!}</label>
                    <div class="relative">
                      <input id="submit_event_end_time" type="text" v-model="timeText.end" autocomplete="off" dir="ltr" role="combobox" aria-autocomplete="list"
                        aria-controls="submit_event_end_time_list" :aria-expanded="timeOpen === 'end' ? 'true' : 'false'" :placeholder="timeExample('end')"
                        :class="bad('event_end_time')" :aria-invalid="msg('event_end_time') ? 'true' : 'false'" :aria-describedby="msg('event_end_time') ? 'err_event_end_time' : null"
                        @focus="openTime('end')" @input="openTime('end')" @blur="commitTime('end')" @keydown="timeKey($event, 'end')"
                        class="{{ $in }}">
                      <ul id="submit_event_end_time_list" class="gs-times" role="listbox" tabindex="-1" @mousedown.prevent v-show="timeOpen === 'end' && timeChoices.length">
                        <li v-for="(o, i) in timeChoices" :key="o.value" role="option" :aria-selected="i === timeHighlight ? 'true' : 'false'"
                          :class="{ 'is-on': i === timeHighlight }" @mousedown.prevent="pickTime('end', o.value)" v-text="o.label"></li>
                      </ul>
                    </div>
                    <p v-if="msg('event_end_time')" id="err_event_end_time" class="gs-err">@{{ msg('event_end_time') }}</p>
                    <p v-if="endsNextDay" class="gs-hint">{{ __('messages.ends_next_day') }}</p>
                  </div>
                </div>

                @if ($role->timezone)
                <p class="gs-hint">
                  {{ __('messages.times_are_in_timezone') }} <bdi class="font-medium" v-text="timezoneLabel"></bdi>.
                </p>
                <div v-if="timezoneMismatch" class="mt-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                  <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                  <p class="text-sm text-amber-700 dark:text-amber-300">@{{ timezoneMismatchMessage }}</p>
                </div>
                @endif

                <div class="mt-4">
                  <label for="submit_description" class="{{ $lb }}">{{ __('messages.description') }} {!! $requiredFields['description'] ? $star : $opt !!}</label>
                  {{-- The error ring goes on this wrapper: EasyMDE hides the textarea itself. --}}
                  <div class="mt-1 rounded-lg" :class="bad('description')">
                    <textarea id="submit_description" rows="4" class="html-editor block w-full" v-pre
                      aria-label="{{ __('messages.description') }}" aria-required="{{ $requiredFields['description'] ? 'true' : 'false' }}"></textarea>
                  </div>
                  <p v-if="msg('description')" id="err_description" class="gs-err">@{{ msg('description') }}</p>
                </div>
              </div>
            </div>

            {{-- 2. Where --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl mt-4">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h" style="margin-bottom: 0.75rem" id="submit_location_heading">{{ __('messages.location') }} @if ($requiredFields['location']) {!! $star !!} @elseif (! $role->isVenue()) <span class="text-sm">{!! $opt !!}</span> @endif</h3>

                @if ($allowOnline)
                {{-- Two boxes, not a choice of one: an event can be in a room and online at once. --}}
                <div class="mb-4">
                  <div class="flex flex-wrap items-center gap-x-6 gap-y-2" role="group" aria-labelledby="submit_location_heading">
                    @unless ($role->isVenue())
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                      <input type="checkbox" id="in_person" v-model="event.in_person" class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                      {{ __('messages.in_person') }}
                    </label>
                    @endunless
                    <label class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300 cursor-pointer">
                      <input type="checkbox" id="is_online" v-model="event.is_online" class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                      {{ $role->isVenue() ? __('messages.booking_also_online') : __('messages.online') }}
                    </label>
                  </div>
                  <p v-if="msg('location')" id="err_location" class="gs-err">@{{ msg('location') }}</p>
                </div>
                @endif

                @if ($allowOnline)
                <div v-show="event.is_online" class="mb-4">
                  <label for="submit_event_url" class="{{ $lb }}">{{ __('messages.event_url') }} {!! $opt !!}</label>
                  <input id="submit_event_url" type="url" inputmode="url" v-model="event.event_url" autocomplete="off" maxlength="500" placeholder="https://" :class="bad('event_url')" :aria-invalid="msg('event_url') ? 'true' : 'false'" :aria-describedby="msg('event_url') ? 'err_event_url' : null"
                    class="{{ $in }}">
                  <p v-if="msg('event_url')" id="err_event_url" class="gs-err">@{{ msg('event_url') }}</p>
                  <p v-else class="gs-hint">{{ __('messages.event_url_help') }}</p>
                </div>
                @endif

                @if ($role->isVenue())
                {{-- A venue schedule is its own location. --}}
                <div class="gs-fixed-place">
                  <div class="text-sm font-medium text-gray-900 dark:text-gray-100" v-pre>{{ $role->name }}</div>
                  @if ($role->fullAddress())
                  <div class="text-sm text-gray-500 dark:text-gray-400 mt-1" v-pre>{{ $role->fullAddress() }}</div>
                  @endif
                </div>
                @else
                {{-- v-show: what was typed here is kept when In-person is switched off and on again. --}}
                <div v-show="event.in_person">
                  @unless ($allowOnline)
                  <p v-if="msg('location')" id="err_location" class="gs-err mb-2">@{{ msg('location') }}</p>
                  @endunless
                  <div class="mb-4">
                    <label for="submit_venue_name" class="{{ $lb }}">{{ __('messages.venue_name') }}</label>
                    <input id="submit_venue_name" type="text" v-model="event.venue_name" autocomplete="off" maxlength="255" :class="bad('location')" :aria-invalid="msg('location') ? 'true' : 'false'" :aria-describedby="msg('location') ? 'err_location' : null" class="{{ $in }}">
                  </div>
                  <div class="mb-4">
                    <label for="submit_venue_address1" class="{{ $lb }}">{{ __('messages.street_address') }}</label>
                    <input id="submit_venue_address1" type="text" v-model="event.venue_address1" autocomplete="off" maxlength="255" class="{{ $in }}">
                  </div>
                  <div class="gs-grid3 mb-4">
                    <div>
                      <label for="submit_venue_city" class="{{ $lb }}">{{ __('messages.city') }}</label>
                      <input id="submit_venue_city" type="text" v-model="event.venue_city" autocomplete="off" maxlength="255" class="{{ $in }}">
                    </div>
                    <div>
                      <label for="submit_venue_state" class="{{ $lb }}">{{ __('messages.state_province') }}</label>
                      <input id="submit_venue_state" type="text" v-model="event.venue_state" autocomplete="off" maxlength="255" class="{{ $in }}">
                    </div>
                    <div>
                      <label for="submit_venue_postal_code" class="{{ $lb }}">{{ __('messages.postal_code') }}</label>
                      <input id="submit_venue_postal_code" type="text" v-model="event.venue_postal_code" autocomplete="off" maxlength="20" class="{{ $in }}">
                    </div>
                  </div>
                  <div>
                    <span id="venue_country_label" class="{{ $lb }}">{{ __('messages.country') }}</span>
                    <x-country-input name="venue_country_code" id="venue_country_code" :value="$role->country_code" :auto-init="false" />
                  </div>
                </div>
                @endif
              </div>
            </div>

            {{-- 3. The schedule's own questions --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl mt-4" v-if="requestCustomFields.length">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h" v-text="words.questions"></h3>

                <div v-for="field in requestCustomFields" :key="field.key" class="mb-4">
                  <label v-if="field.type !== 'switch'" :id="'submit_custom_field_label_' + field.key" :for="'submit_custom_field_' + field.key" class="{{ $lb }}">
                    @{{ field.label }}<span v-if="field.required" class="text-red-500" aria-hidden="true"> *</span><span v-else class="font-normal text-gray-500 dark:text-gray-400" v-text="' (' + words.optional + ')'"></span>
                  </label>
                  {{-- Under the question and before its control, not after it: under a list of
                       eight rooms it was nine rows below the question, and under a text box it was
                       read after typing. Who will read an answer is said before it is given. --}}
                  <p v-if="field.on_event" :id="'pub_cf_' + field.key" class="gs-hint" data-answer-on-event>{{ __('messages.request_answer_on_event') }}</p>
                  <input v-if="field.type === 'string'" :id="'submit_custom_field_' + field.key" type="text" v-model="customFieldValues[field.key]" :pattern="field.regex || null" :class="bad('cf_' + field.key)" :aria-required="field.required ? 'true' : 'false'" :aria-describedby="[msg('cf_' + field.key) ? 'err_cf_' + field.key : null, field.on_event ? 'pub_cf_' + field.key : null].filter(Boolean).join(' ') || null" dir="auto" autocomplete="off" class="{{ $in }}">
                  <textarea v-else-if="field.type === 'multiline_string'" :id="'submit_custom_field_' + field.key" rows="3" dir="auto" v-model="customFieldValues[field.key]" :class="bad('cf_' + field.key)" :aria-required="field.required ? 'true' : 'false'" :aria-describedby="[msg('cf_' + field.key) ? 'err_cf_' + field.key : null, field.on_event ? 'pub_cf_' + field.key : null].filter(Boolean).join(' ') || null" class="{{ $in }}"></textarea>
                  <label v-else-if="field.type === 'switch'" class="flex items-start gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <input type="checkbox" :id="'submit_custom_field_' + field.key" v-model="customFieldValues[field.key]" true-value="1" false-value="0"
                      class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    <span>@{{ field.label }}<span v-if="field.required" class="text-red-500" aria-hidden="true"> *</span></span>
                  </label>
                  <input v-else-if="field.type === 'date'" :id="'submit_custom_field_' + field.key" type="text" data-gs-date :data-key="field.key" :data-required="field.required ? '1' : '0'" autocomplete="off" :class="bad('cf_' + field.key)" class="{{ $in }}">
                  <select v-else-if="field.type === 'dropdown'" :id="'submit_custom_field_' + field.key" v-model="customFieldValues[field.key]" :class="bad('cf_' + field.key)" :aria-required="field.required ? 'true' : 'false'" :aria-describedby="[msg('cf_' + field.key) ? 'err_cf_' + field.key : null, field.on_event ? 'pub_cf_' + field.key : null].filter(Boolean).join(' ') || null" class="{{ $in }}">
                    <option value="">{{ __('messages.please_select') }}</option>
                    <option v-for="option in field.options" :key="option" :value="option">@{{ option }}</option>
                  </select>
                  <div v-else-if="field.type === 'multiselect'" :id="'submit_custom_field_' + field.key" class="mt-1 space-y-1 rounded-lg" :class="bad('cf_' + field.key)">
                    <label v-for="option in field.options" :key="option" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                      <input type="checkbox" :value="option" v-model="customFieldValues[field.key]" class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                      @{{ option }}
                    </label>
                  </div>
                  <p v-if="msg('cf_' + field.key)" :id="'err_cf_' + field.key" class="gs-err">@{{ msg('cf_' + field.key) }}</p>
                  <p v-else-if="field.private" class="gs-hint">{{ __('messages.booking_answer_private') }}</p>
                  <p v-else-if="field.regex_hint && (field.type === 'string' || field.type === 'multiline_string')" class="gs-hint">@{{ field.regex_hint }}</p>
                </div>
              </div>
            </div>

            {{-- 4. Who is asking --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl mt-4">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h">{{ __('messages.your_details') }}</h3>

                @auth
                <p class="gs-who mb-4">{{ __('messages.logged_in_as') }} <strong v-pre>{{ auth()->user()->name }}</strong> <span v-pre>({{ auth()->user()->email }})</span></p>
                @else
                <div class="gs-grid2">
                  <div>
                    <label for="account_name" class="{{ $lb }}">{{ __('messages.name') }} {!! $star !!}</label>
                    <input id="account_name" type="text" v-model="userName" autocomplete="name" maxlength="255" aria-required="true" :class="bad('account_name')" :aria-invalid="msg('account_name') ? 'true' : 'false'" :aria-describedby="msg('account_name') ? 'err_account_name' : null" class="{{ $in }}">
                    <p v-if="msg('account_name')" id="err_account_name" class="gs-err">@{{ msg('account_name') }}</p>
                  </div>
                  <div>
                    <label for="account_email" class="{{ $lb }}">{{ __('messages.email') }} {!! $star !!}</label>
                    <input id="account_email" type="email" v-model="userEmail" autocomplete="email" maxlength="255" aria-required="true" :class="bad('account_email')" :aria-invalid="msg('account_email') ? 'true' : 'false'" :aria-describedby="msg('account_email') ? 'err_account_email' : (emailExists ? 'account_exists_note' : null)" class="{{ $in }}">
                    <p v-if="msg('account_email')" id="err_account_email" class="gs-err">@{{ msg('account_email') }}</p>
                    {{-- The address already has an account, so none can be made for it here. Said under the address, until it changes. --}}
                    <p v-else-if="emailExists" id="account_exists_note" class="gs-hint" v-text="words.has_account"></p>
                  </div>
                </div>
                @endauth

                @if ($askPhone)
                <div class="mt-4">
                  <label for="contact_phone" class="{{ $lb }}">{{ __('messages.phone') }} {!! $requiredFields['phone'] ? $star : $opt !!}</label>
                  <input id="contact_phone" type="tel" v-model="userPhone" autocomplete="tel" maxlength="255" aria-required="{{ $requiredFields['phone'] ? 'true' : 'false' }}" :class="bad('contact_phone')" :aria-invalid="msg('contact_phone') ? 'true' : 'false'" :aria-describedby="msg('contact_phone') ? 'err_contact_phone' : null" class="{{ $in }}">
                  <p v-if="msg('contact_phone')" id="err_contact_phone" class="gs-err">@{{ msg('contact_phone') }}</p>
                </div>
                @endif

                @guest
                @if ($offerAccount)
                <div class="gs-account">
                  @if ($mustHaveAccount)
                  <p class="text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.booking_needs_account') }}</p>
                  @else
                  <label class="flex items-start gap-2 cursor-pointer">
                    <input type="checkbox" id="create_account" v-model="createAccount" class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    <span>
                      <span class="block text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.create_an_account') }}</span>
                      <span class="block text-xs text-gray-500 dark:text-gray-400 mt-0.5" v-pre>{{ __('messages.booking_account_benefits', ['name' => $scheduleName]) }}</span>
                    </span>
                  </label>
                  @endif

                  <div v-show="createAccount" class="mt-4">
                    <label for="account_password" class="{{ $lb }}">{{ __('messages.password') }} {!! $star !!}</label>
                    <div class="relative">
                      <input id="account_password" :type="showPassword ? 'text' : 'password'" v-model="userPassword" autocomplete="new-password" aria-required="true" :class="bad('account_password')" :aria-invalid="msg('account_password') ? 'true' : 'false'" :aria-describedby="msg('account_password') ? 'err_account_password' : null" class="{{ $in }} pe-12">
                      <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? words.hide_password : words.show_password" :aria-pressed="showPassword ? 'true' : 'false'"
                        class="absolute inset-y-0 end-0 mt-1 px-3 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                      </button>
                    </div>
                    <p v-if="msg('account_password')" id="err_account_password" class="gs-err">@{{ msg('account_password') }}</p>
                    {{-- Said before the button is pressed: on the hosted app the next screen is a code, not "sent". --}}
                    <p v-else-if="requiresCode" class="gs-hint">{{ __('messages.booking_code_first') }}</p>

                    <label class="flex items-start gap-2 mt-3 text-sm text-gray-700 dark:text-gray-300">
                      <input id="account_terms" type="checkbox" v-model="acceptedTerms" :aria-invalid="msg('terms') ? 'true' : 'false'" :aria-describedby="msg('terms') ? 'err_terms' : null" class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                      <span v-pre>
                        @if (config('app.hosted'))
                          {!! str_replace([':terms', ':privacy'], [
                              '<a href="' . policy_url('terms') . '" target="_blank" class="gs-inline-link">' . __('messages.terms_of_service') . '</a>',
                              '<a href="' . policy_url('privacy') . '" target="_blank" class="gs-inline-link">' . __('messages.privacy_policy') . '</a>'
                          ], __('messages.i_accept_the_terms_and_privacy')) !!}
                        @else
                          {!! str_replace([':terms'], [
                              '<a href="' . policy_url('terms', '/self-hosting-terms-of-service') . '" target="_blank" class="gs-inline-link">' . __('messages.terms_of_service') . '</a>',
                          ], __('messages.i_accept_the_terms')) !!}
                        @endif
                      </span>
                    </label>
                    <p v-if="msg('terms')" id="err_terms" class="gs-err">@{{ msg('terms') }}</p>

                    <div v-show="turnstileEnabled && requiresCode" class="mt-3"><div id="turnstile-import-widget"></div></div>
                  </div>
                </div>
                @endif
                <p v-if="msg('create_account')" id="err_create_account" class="gs-err">@{{ msg('create_account') }}</p>
                @endguest

                <p class="gs-hint mt-4">
                  {{ $nobodyOwnsIt ? __('messages.booking_contact_unclaimed_note') : __('messages.booking_contact_privacy_note') }}
                  <a href="{{ policy_url('privacy') }}" target="_blank" rel="noopener" class="gs-inline-link">{{ __('messages.privacy_policy') }}</a>
                </p>
                <x-honeypot vmodel="honeypot" />
              </div>
            </div>

            {{-- The bar. Always on screen; its line is what Send will do, or what it still needs. --}}
            <div class="gs-bar" id="submit-bar">
              <div class="gs-bar-status" :class="{ 'is-bad': bar.kind === 'bad', 'is-ready': bar.kind === 'ready', 'is-todo': bar.kind === 'todo' }" v-show="bar.kind !== 'quiet'" role="status" aria-live="polite" aria-atomic="true" tabindex="-1" id="submit-error-box">
                <template v-if="bar.items.length">
                  <span v-text="bar.lead"></span>
                  <template v-for="(p, i) in bar.items" :key="p.key"><button type="button" @click="goTo(p)" v-text="p.label"></button><span v-if="i < bar.items.length - 1 || bar.more" v-text="', '"></span></template>
                  <button v-if="bar.more" type="button" @click="goTo(bar.next)" v-text="'+' + bar.more"></button>
                </template>
                <span v-else v-text="bar.text"></span>
              </div>
              <div class="gs-bar-actions">
                @if ($scheduleUrl)
                <a href="{{ $scheduleUrl }}" class="gs-bar-cancel" v-show="!saving">{{ __('messages.cancel') }}</a>
                @endif
                {{-- A page left open past its session cannot send anything: the one thing to press is Reload. What was typed is kept. --}}
                <button v-if="expired" type="button" id="reload-btn" @click="reloadPage" class="gs-bar-go gs-fill">{{ __('messages.reload') }}</button>
                <button v-else type="submit" id="submit-btn" :disabled="saving || codeSending || cannotSend" class="gs-bar-go gs-fill">
                  <span v-if="saving || codeSending">{{ __('messages.submitting') }}</span>
                  {{-- With an account to make, the next screen is the emailed code, and the button says so. --}}
                  <span v-else-if="needsCode">{{ __('messages.signup_continue') }}</span>
                  <span v-else>{{ $sendLabel }}</span>
                </button>
              </div>
            </div>
          </form>

          {{-- The emailed code, after Send, for someone making an account. --}}
          <div v-show="!submitted && step === 'code'" class="ap-card gs-code-card p-6 sm:p-10 shadow-md rounded-xl">
            <div class="gs-step">
              <h2 id="submit_code_heading" tabindex="-1" class="focus:outline-none">{{ __('messages.check_your_email') }}</h2>
              <p id="submit_code_sent_to" class="mt-2 text-gray-600 dark:text-gray-400" aria-live="polite">
                {{ __('messages.code_sent_to_prefix') }} <span class="font-medium text-gray-900 dark:text-gray-100">@{{ userEmail }}</span>. {{ __('messages.booking_code_not_sent_yet') }}
              </p>
              <div class="gs-ticket">
                <div class="min-w-0">
                  <div class="gs-ticket-name" v-text="sent.name"></div>
                  <div class="gs-ticket-sub" v-text="sent.when"></div>
                  <div class="gs-ticket-sub" v-text="sent.where"></div>
                </div>
              </div>
              <label for="verification_code" class="sr-only">{{ __('messages.verification_code') }}</label>
              <div id="code-boxes" class="relative" dir="ltr" :class="{ 'is-invalid': !!codeError, 'is-busy': saving }">
                <div class="code-slots" aria-hidden="true">
                  <template v-for="i in 6" :key="i">
                    <div data-code-slot class="code-slot bg-white dark:bg-gray-900 border-gray-300 dark:border-gray-700"
                      :class="{ 'is-filled': !!verificationCode.charAt(i - 1), 'is-active': codeFocused && (i - 1) === Math.min(verificationCode.length, 5), 'is-new': (i - 1) >= codeFrom && !!verificationCode.charAt(i - 1) }">
                      <span data-digit :key="i + verificationCode.charAt(i - 1)" v-text="verificationCode.charAt(i - 1)"></span><span data-caret></span>
                    </div>
                    <span v-if="i === 3" class="code-slots-gap"></span>
                  </template>
                </div>
                <input id="verification_code" type="text" class="code-input" inputmode="numeric" autocomplete="one-time-code" pattern="[0-9]{6}"
                  :value="verificationCode" :disabled="saving" aria-describedby="submit_code_sent_to"
                  @input="onCodeInput" @focus="codeFocused = true; keepCodeCaretAtEnd()" @blur="codeFocused = false" @click="keepCodeCaretAtEnd" @keyup="keepCodeCaretAtEnd" @select="keepCodeCaretAtEnd"
                  data-1p-ignore data-lpignore="true" data-bwignore data-form-type="other" data-protonpass-ignore>
              </div>
              <p v-if="codeError" class="gs-err" role="alert">@{{ codeError }}</p>
              <button type="button" @click="submitCode" :disabled="saving" class="gs-bar-go gs-fill gs-code-go">
                <span v-if="saving">{{ __('messages.submitting') }}</span>
                <span v-else>{{ $sendLabel }}</span>
              </button>
              <div class="gs-step-links">
                <span v-if="resendCountdown > 0">{{ __('messages.didnt_receive_code') }} {{ __('messages.resend_in_label') }} <span class="tabular-nums">@{{ resendCountdown }}s</span></span>
                <span v-else>{{ __('messages.didnt_receive_code') }} <button type="button" @click="resendCode" :disabled="codeSending" class="gs-link">{{ __('messages.resend_code') }}</button></span>
                <button type="button" @click="backToForm('email')" class="gs-link">{{ __('messages.use_another_email') }}</button>
                <button type="button" @click="backToForm('event')" class="gs-link">{{ __('messages.booking_edit_request') }}</button>
                <button v-if="!mustHaveAccount" type="button" @click="sendWithoutAccount" class="gs-link">{{ __('messages.booking_send_without_account') }}</button>
              </div>
            </div>
          </div>

          {{-- Sent. v-if: its bindings read submissionResult, which is null until then. --}}
          <div v-if="submitted" class="ap-card p-6 sm:p-10 shadow-md rounded-xl">
            <div class="max-w-2xl mx-auto text-center">
              <div class="gs-tick">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
              </div>
              <h2 id="submission-success-heading" tabindex="-1" class="text-2xl font-bold text-gray-900 dark:text-gray-100 focus:outline-none">
                <span v-if="submissionResult.status === 'live'">{{ __('messages.youre_live_title') }}</span>
                <span v-else>{{ __('messages.booking_sent_title') }}</span>
              </h2>
              <p class="mt-3 text-gray-600 dark:text-gray-400">
                {{-- v-pre switches off every directive on its own element, so the choice is made one level up. --}}
                <template v-if="submissionResult.status === 'live'"><span v-pre>{{ __('messages.booking_live_body', ['name' => $scheduleName]) }}</span></template>
                <template v-else><span v-pre>{{ __('messages.booking_sent_body', ['name' => $scheduleName]) }}</span></template>
              </p>

              <div class="gs-ticket" data-gs-sent>
                <div class="min-w-0">
                  <div class="gs-ticket-name" v-text="sent.name"></div>
                  <div class="gs-ticket-sub" v-text="sent.when"></div>
                  <div class="gs-ticket-sub" v-text="sent.where"></div>
                </div>
              </div>

              <ol v-if="submissionResult.status !== 'live'" class="gs-steps">
                <li class="is-done">{{ __('messages.submitted_step_sent') }}</li>
                <li class="is-now"><span v-pre>{{ __('messages.submitted_step_review', ['name' => $scheduleName]) }}</span></li>
                <li v-text="submissionResult.emails_you ? words.email_you : words.reply_to"></li>
              </ol>
              {{-- The app emails an answer to an account, never to a typed address: someone without one is told who will write, and where to. --}}
              <p v-if="submissionResult.status !== 'live' && !submissionResult.emails_you" class="gs-hint mt-3" v-text="words.reply_note.replace(':email', sent.email)"></p>

              <div class="gs-after mt-6">
                <button type="button" @click="resetForAnother" class="gs-after-again text-sm text-gray-500 dark:text-gray-400 underline hover:no-underline">{{ $asksToBook ? __('messages.booking_send_another') : __('messages.submit_another') }}</button>
                @if ($scheduleUrl)
                <a href="{{ $scheduleUrl }}" class="gs-fill gs-after-btn gs-after-main">{{ __('messages.view_schedule') }}</a>
                @endif
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  @include('partials.request-form-kit')
  @include('event.partials.booking-request-script')

</x-app-guest-layout>
