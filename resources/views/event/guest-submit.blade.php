{{-- noindex: a form, not content. Every schedule has one, and none is worth a search result. --}}
<x-app-guest-layout :role="$role" :showMobileBackground="true" :page-title="__('messages.submit_event')" :no-index="true">

@php
  $hasHeaderImage = ($role->header_image && ! in_array($role->header_image, \App\Models\Role::HEADER_IMAGE_KEYWORDS, true)) || $role->header_image_url;
  $accentColor = $role->accent_color ?: '#4E81FA';
  $contrastColor = accent_contrast_color($accentColor);
  // The accent as text, on this page's light and dark cards (as appointments/book-type does): a
  // pale accent is unreadable as a link or a ticked pill, so those fall back to ink.
  $accentOnLight = \App\Utils\ColorUtils::readableAccentColor($accentColor, '#ffffff', '#111827');
  $accentOnDark = \App\Utils\ColorUtils::readableAccentColor($accentColor, '#252526', '#ffffff');
  $use24hr = get_use_24_hour_time($role);
  $importFields = $role->import_config['fields'] ?? [];
  $requiredImportFields = $role->import_config['required_fields'] ?? [];
  $aiEnabled = (bool) (config('services.google.gemini_key') || config('services.openai.api_key'));
  $scheduleName = $role->getDisplayName(true);

  // Custom fields the schedule asks on its request form. Rendered by Vue rather than server-side:
  // this page is a Vue mount, so owner-authored labels and options echoed into it would be compiled
  // as a template. Passed as data and interpolated with Vue's own mustaches, they never are.
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
              // The schedule prints this answer on the event's public page once it accepts the
              // event. The person typing it is the one who should know.
              'on_event' => \App\Models\Role::isEventCustomFieldOnEventPage($field),
          ];
          $requestCustomFieldValues[$fieldKey] = ($field['type'] ?? 'string') === 'multiselect'
              ? []
              : (($field['type'] ?? 'string') === 'switch' ? '0' : '');
      }
  }

  // ?lang can arrive as an array (?lang[]=en). is_valid_language_code() answers false for a
  // non-string now, so this narrowing is belt-and-braces rather than the thing standing between
  // the page and a TypeError - but $lang is concatenated below, so keep it a string or null.
  $lang = is_string(request()->query('lang')) ? request()->query('lang') : null;
  $whyAccountUrl = marketing_url('/why-create-account')
      .(is_valid_language_code($lang) ? '?lang='.$lang : '');
  // The language rides along to Google and back; the controller already reads it.
  $googleParams = ['subdomain' => $role->subdomain];
  if (is_valid_language_code($lang)) {
      $googleParams['lang'] = $lang;
  }

  // What happens after Submit, by the one rule that decides it (Role::autoAcceptsEventFrom()).
  // A schedule on this one's approved list is only known once the visitor says who they are, so
  // a guest of that kind reads "reviews" here and "You're live" afterwards. Someone signed in has
  // said who they are: the rule is asked with their own schedule, and "as soon as you submit" is
  // promised only when it holds for every schedule they could post as.
  $viewer = auth()->user();
  $viewerSchedules = $viewer ? $viewer->talents()->get() : collect();
  $landsAtOnce = $viewerSchedules->isNotEmpty()
      ? $viewerSchedules->every(fn ($own) => $role->autoAcceptsEventFrom($viewer, $own))
      : $role->autoAcceptsEventFrom($viewer);
  $whatHappens = __($landsAtOnce ? 'messages.guest_submit_instant' : 'messages.guest_submit_reviewed', ['name' => $scheduleName]);
  // Submitting follows the schedule and shows it the submitter's name and email. Said to
  // everyone it is about to happen to, not only to the people creating an account.
  $alreadyConnected = $viewer ? $viewer->isConnected($role->subdomain) : false;
  $requestTermsText = filled($role->request_terms) ? $role->translatedRequestTerms() : null;

  $categories = [];
  foreach (get_translated_categories($role) as $catId => $catName) {
      $categories[] = ['id' => (string) $catId, 'name' => $catName];
  }

  $in = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm';
  $lb = 'block font-medium text-sm text-gray-700 dark:text-gray-300';
  $star = '<span class="text-red-500" aria-hidden="true">*</span>';

  // Everything the script says, in one array: a multi-line array literal does not compile inside
  // the JSON directive.
  $words = [
      'required' => __('messages.field_required'),
      'valid_email' => __('messages.please_enter_valid_email'),
      'valid_url' => __('messages.invalid_url'),
      'password_min' => __('messages.password_min_chars'),
      'optional' => __('messages.optional'),
      'free' => __('messages.free'),
      'error' => __('messages.error_occurred'),
      'too_many' => __('messages.too_many_attempts'),
      'image_type' => __('messages.image_type_not_supported'),
      'image_size' => __('messages.image_size_warning'),
      'image_error' => __('messages.error_uploading_image'),
      'ready' => __('messages.ready_to_submit', ['name' => $scheduleName]),
      'timezone_mismatch' => __('messages.guest_submit_timezone_mismatch'),
      'show_password' => __('messages.show_password'),
      'hide_password' => __('messages.hide_password'),
      'auto_fill_placeholder' => __('messages.auto_fill_placeholder'),
      'questions' => __('messages.questions_from', ['name' => $scheduleName]),
      'still_needed' => __('messages.still_needed'),
      'check' => __('messages.guest_submit_check'),
      'flyer_not_read' => __('messages.flyer_not_read'),
      'processing' => __('messages.processing'),
      'expired' => __('messages.page_expired_reload'),
      'time_like' => __('messages.enter_time_like'),
  ];
  $labels = [
      'name' => __('messages.event_name'),
      'event_date' => __('messages.date'),
      'event_start_time' => __('messages.start_time'),
      'event_end_time' => __('messages.end_time'),
      'event_url' => __('messages.event_url'),
      'venue_name' => __('messages.venue_name'),
      'short_description' => __('messages.short_description'),
      'description' => __('messages.description'),
      'ticket_price' => __('messages.price'),
      'registration_url' => __('messages.registration_url'),
      'coupon_code' => __('messages.coupon_code'),
      'category_id' => __('messages.category'),
      'group_id' => __('messages.schedule'),
      'account_email' => __('messages.email'),
      'account_name' => __('messages.name'),
      'account_password' => __('messages.password'),
      'terms' => __('messages.terms_of_service'),
  ];
@endphp

<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
@if (\App\Utils\TurnstileUtils::isEnabled())
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer {!! nonce_attr() !!}></script>
@endif

<style {!! nonce_attr() !!}>
@include('partials.request-form-styles')
</style>

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
                  {{ __('messages.submit_your_event') }}
                </h2>
                <h3 class="text-gray-700 dark:text-gray-300">
                  {{ __('messages.submitting_to', ['name' => $scheduleName]) }}
                </h3>
              </div>
              <a href="{{ $role->getGuestUrl() }}" class="gs-quiet hidden sm:block">{{ __('messages.view_schedule') }}</a>
            </div>

            {{-- What pressing Submit leads to, before any of the typing. --}}
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

            @guest
            {{-- Restored from guest-import.blade.php, where it was dropped when this page
                 was split out of it (0820e2550). Frames the account before the visitor
                 spends effort on the form, rather than at the password field at the bottom. --}}
            <p class="text-sm text-gray-600 dark:text-gray-400 mt-2" data-gs-before>
                {!! __('messages.guest_submit_value_strip') !!}
                <x-link href="{{ $whyAccountUrl }}" target="_blank" class="text-sm mt-1 inline-block">{{ __('messages.why_create_account_learn_more') }}</x-link>
            </p>
            @endguest

            {{-- The owner's terms for requests, where the booking form and the import page already
                 show them: this page was the one that did not. Outside the Vue mount, so the
                 owner's text is never compiled as a template. --}}
            @if ($requestTermsText)
            <div class="gs-terms" data-gs-before>
              <h3>{{ __('messages.request_terms') }}</h3>
              <div dir="{{ content_dir($role, showing_translation($role) && filled($role->request_terms_en), $requestTermsText) }}">{!! nl2br(e($requestTermsText)) !!}</div>
            </div>
            @endif
          </div>
        </div>

        {{-- Vue submission app --}}
        <div id="event-submit-app" data-vue-root>

          <form v-show="!submitted && step === 'form'" @submit.prevent="submitEvent" @paste="onFormPaste" novalidate>

            {{-- Draft restored banner --}}
            <div v-if="draftRestored" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg flex items-center gap-3 text-sm text-blue-800 dark:text-blue-200" aria-live="polite">
              <span class="flex-1">{{ __('messages.draft_restored') }}</span>
              <button type="button" @click="startFresh" class="underline hover:no-underline shrink-0">{{ __('messages.start_fresh') }}</button>
              <button type="button" @click="draftRestored = false" aria-label="{{ __('messages.close') }}" class="shrink-0 p-1 text-blue-400 hover:text-blue-600 dark:hover:text-blue-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>

            {{-- 1. The event --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h">{{ __('messages.event_details') }}</h3>

                {{-- The flyer. One image, which is the event's picture and, where an AI key is set,
                     also fills in the fields the visitor has not typed. --}}
                <div class="gs-flyer" :class="{ 'is-drag': flyerDragging, 'has-image': !!flyerPreviewUrl }"
                  @dragenter.prevent="flyerDragEnter" @dragover.prevent @dragleave.prevent="flyerDragLeave" @drop.prevent="flyerDrop">
                  <input type="file" ref="flyerInput" accept="image/jpeg,image/png,image/gif,image/webp" class="hidden" @change="onFlyerSelected">
                  <div class="gs-flyer-art" :class="{ 'is-empty': !flyerPreviewUrl }">
                    <img v-if="flyerPreviewUrl" :src="flyerPreviewUrl" alt="">
                    <svg v-else fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m2.25 15.75 5.159-5.159a2.25 2.25 0 0 1 3.182 0l5.159 5.159m-1.5-1.5 1.409-1.409a2.25 2.25 0 0 1 3.182 0l2.909 2.909M3.75 21h16.5A2.25 2.25 0 0 0 22.5 18.75V5.25A2.25 2.25 0 0 0 20.25 3H3.75A2.25 2.25 0 0 0 1.5 5.25v13.5A2.25 2.25 0 0 0 3.75 21Zm12.75-12.75h.008v.008H16.5V8.25Z" /></svg>
                  </div>
                  <div class="gs-flyer-text" aria-live="polite">
                    <p v-if="flyerBusy" class="gs-flyer-title inline-flex items-center gap-2">
                      <svg class="gs-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                      {{ __('messages.processing') }}
                    </p>
                    <template v-else-if="autoFillDone">
                      {{-- Undo sits with the sentence it undoes; Replace and Remove below are about the picture. --}}
                      <p class="gs-ok">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                        <span>{{ __('messages.auto_fill_applied') }}</span>
                        <button type="button" class="gs-link" @click="undoAutoFill">{{ __('messages.auto_fill_undo') }}</button>
                      </p>
                      {{-- A second flyer that could not be read: the details are still the first one's. --}}
                      <p v-if="flyerNote" class="gs-flyer-help" v-text="flyerNote"></p>
                    </template>
                    <template v-else-if="event.social_image">
                      <p class="gs-ok">
                        <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
                        {{ __('messages.image_attached') }}
                      </p>
                      {{-- The flyer is kept even when it could not be read: say that it was not. --}}
                      <p v-if="flyerNote" class="gs-flyer-help" v-text="flyerNote"></p>
                    </template>
                    <template v-else>
                      @if ($aiEnabled)
                      <p class="gs-flyer-title">{{ __('messages.auto_fill_pitch') }}</p>
                      <p class="gs-flyer-help">{{ __('messages.auto_fill_hint') }}</p>
                      @else
                      <p class="gs-flyer-title">{{ __('messages.flyer') }} <span class="font-normal text-gray-400 dark:text-gray-500">({{ __('messages.optional') }})</span></p>
                      <p class="gs-flyer-help">{{ __('messages.flyer_drop_plain') }}</p>
                      @endif
                    </template>
                    <p v-if="flyerError" class="gs-err" role="alert">@{{ flyerError }}</p>
                    @if ($aiEnabled)
                    {{-- Art. 13: a visitor's text and flyer go to the AI provider. Shown for as long
                         as another image could be added, not only before the first. --}}
                    <p class="gs-flyer-fine">{{ __('messages.ai_processing_notice') }}</p>
                    @endif
                    <div class="gs-flyer-actions">
                      <button type="button" class="gs-btn" :class="{ 'is-lead': !flyerPreviewUrl && !event.social_image }" @click="$refs.flyerInput.click()" :disabled="flyerBusy">
                        <span v-if="event.social_image || flyerPreviewUrl">{{ __('messages.replace_image') }}</span>
                        <span v-else>{{ __('messages.upload_flyer') }}</span>
                      </button>
                      <button v-if="(event.social_image || flyerPreviewUrl) && !flyerBusy" type="button" class="gs-link is-quiet" @click="removeFlyer">{{ __('messages.remove') }}</button>
                      @if ($aiEnabled)
                      <button v-if="!flyerBusy && !autoFillDone" type="button" class="gs-link" @click="showPaste = !showPaste" :aria-expanded="showPaste ? 'true' : 'false'">{{ __('messages.paste_text_instead') }}</button>
                      @endif
                    </div>
                  </div>
                  @if ($aiEnabled)
                  <div v-show="showPaste" class="gs-flyer-paste">
                    <label for="submit_auto_fill_text" class="sr-only">{{ __('messages.paste_text_instead') }}</label>
                    <textarea id="submit_auto_fill_text" v-model="autoFillText" rows="3" :placeholder="words.auto_fill_placeholder" dir="auto"
                      class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"></textarea>
                    <div class="mt-2 flex justify-end">
                      <button type="button" class="gs-btn" @click="runAutoFill(null)" :disabled="flyerBusy || !autoFillText.trim()">{{ __('messages.auto_fill') }}</button>
                    </div>
                  </div>
                  @endif
                </div>

                <div class="mb-4">
                  <label for="submit_event_name" class="{{ $lb }}">{{ __('messages.event_name') }} {!! $star !!}</label>
                  <input id="submit_event_name" type="text" v-model="event.name" autocomplete="off" maxlength="255" aria-required="true" :class="bad('name')" :aria-invalid="msg('name') ? 'true' : 'false'" :aria-describedby="msg('name') ? 'err_name' : null"
                    class="{{ $in }}">
                  <p v-if="msg('name')" id="err_name" class="gs-err">@{{ msg('name') }}</p>
                </div>

                <div class="gs-grid3">
                  <div>
                    <label for="submit_event_date" id="submit_event_date_label" class="{{ $lb }}">{{ __('messages.date') }} {!! $star !!}</label>
                    <input id="submit_event_date" type="text" autocomplete="off" aria-label="{{ __('messages.date') }}" class="{{ $in }}">
                    <p v-if="msg('event_date')" id="err_event_date" class="gs-err">@{{ msg('event_date') }}</p>
                  </div>
                  {{-- Typed or picked, to the minute, as the event form and the booking form take a time.
                       The half-hour list this replaces could not hold 7:15, and rounded a flyer's. --}}
                  <div>
                    <label for="submit_event_time" class="{{ $lb }}">{{ __('messages.start_time') }} {!! $star !!}</label>
                    <div class="relative">
                      <input id="submit_event_time" type="text" v-model="timeText.start" autocomplete="off" dir="ltr" role="combobox" aria-autocomplete="list" aria-required="true"
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
                    <label for="submit_event_end_time" class="{{ $lb }}">{{ __('messages.end_time') }} <span class="font-normal text-gray-400 dark:text-gray-500">({{ __('messages.optional') }})</span></label>
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

                {{-- Only when the visitor's clock really reads differently from the schedule's. Two
                     names for one clock (Toronto and New York, Calcutta and Kolkata) used to warn. --}}
                <div v-if="timezoneMismatch" class="mt-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                  <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                  <p class="text-sm text-amber-700 dark:text-amber-300">@{{ timezoneMismatchMessage }}</p>
                </div>
                @endif
              </div>
            </div>

            {{-- 2. Where --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl mt-4">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h" style="margin-bottom: 0.75rem" id="submit_location_heading">{{ __('messages.location') }}</h3>
                <div class="mb-4">
                  <div class="gs-pills" role="radiogroup" aria-labelledby="submit_location_heading">
                    <label class="gs-pill">
                      <input type="radio" name="submit_location" class="sr-only" :value="false" v-model="event.is_online">
                      <span>{{ __('messages.in_person') }}</span>
                    </label>
                    <label class="gs-pill">
                      <input type="radio" name="submit_location" class="sr-only" :value="true" v-model="event.is_online">
                      <span>{{ __('messages.online') }}</span>
                    </label>
                  </div>
                </div>

                <div v-if="event.is_online">
                  <label for="submit_event_url" class="{{ $lb }}">{{ __('messages.event_url') }} {!! $star !!}</label>
                  <input id="submit_event_url" type="url" inputmode="url" v-model="event.event_url" autocomplete="off" maxlength="500" placeholder="https://" :class="bad('event_url')" :aria-invalid="msg('event_url') ? 'true' : 'false'"
                    class="{{ $in }}">
                  <p v-if="msg('event_url')" id="err_event_url" class="gs-err">@{{ msg('event_url') }}</p>
                </div>

                <div v-else>
                  <div class="mb-4">
                    <label for="submit_venue_name" class="{{ $lb }}">{{ __('messages.venue_name') }} {!! $star !!}</label>
                    <input id="submit_venue_name" type="text" v-model="event.venue_name" autocomplete="off" maxlength="255" :class="bad('venue_name')" :aria-invalid="msg('venue_name') ? 'true' : 'false'"
                      class="{{ $in }}">
                    <p v-if="msg('venue_name')" id="err_venue_name" class="gs-err">@{{ msg('venue_name') }}</p>
                  </div>
                  <div class="mb-4">
                    <label for="submit_venue_address1" class="{{ $lb }}">{{ __('messages.street_address') }}</label>
                    <input id="submit_venue_address1" type="text" v-model="event.venue_address1" autocomplete="off" maxlength="255" class="{{ $in }}">
                  </div>
                  <div class="gs-grid3">
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
                      <input id="submit_venue_postal_code" type="text" v-model="event.venue_postal_code" autocomplete="off" maxlength="255" class="{{ $in }}">
                    </div>
                  </div>
                </div>
              </div>
            </div>

            {{-- 3. The rest, as rows that say what they hold. A row with something the schedule
                 made required is open from the start and stays open. --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl mt-4">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h" style="margin-bottom: 0.5rem">{{ __('messages.additional_details') }}</h3>
                <div class="gs-rows">

                  {{-- Description --}}
                  <button type="button" class="gs-row" :class="{ 'is-fixed': rowFixed('description') }" data-row="description" @click="toggleRow('description')"
                    :aria-expanded="rowFixed('description') ? null : (rowOpen('description') ? 'true' : 'false')" :aria-disabled="rowFixed('description') ? 'true' : null" aria-controls="submit_row_description">
                    <span class="gs-row-title">{{ __('messages.description') }}<span v-if="requiredFields.description" class="text-red-500" aria-hidden="true"> *</span></span>
                    <span class="gs-row-summary" :class="{ 'is-empty': !rows.description }"><bdi v-text="rows.description || (rowFixed('description') ? '' : words.optional)"></bdi></span>
                    <svg v-if="!rowFixed('description')" class="gs-row-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </button>
                  {{-- v-show: EasyMDE is wired to the textarea once, by id. --}}
                  <div id="submit_row_description" v-show="rowOpen('description')" class="gs-row-body">
                    <label for="submit_description" class="sr-only">{{ __('messages.description') }}</label>
                    <div class="rounded-lg" :class="bad('description')">
                      <textarea id="submit_description" class="html-editor block w-full" rows="4"></textarea>
                    </div>
                    <p v-if="msg('description')" class="gs-err">@{{ msg('description') }}</p>
                  </div>

                  {{-- Price and registration --}}
                  <button type="button" class="gs-row" :class="{ 'is-fixed': rowFixed('price') }" data-row="price" @click="toggleRow('price')"
                    :aria-expanded="rowFixed('price') ? null : (rowOpen('price') ? 'true' : 'false')" :aria-disabled="rowFixed('price') ? 'true' : null" aria-controls="submit_row_price">
                    <span class="gs-row-title">{{ __('messages.price_and_registration') }}<span v-if="requiredFields.ticket_price || requiredFields.registration_url || requiredFields.coupon_code" class="text-red-500" aria-hidden="true"> *</span></span>
                    <span class="gs-row-summary" :class="{ 'is-empty': !rows.price }"><bdi v-text="rows.price || (rowFixed('price') ? '' : words.optional)"></bdi></span>
                    <svg v-if="!rowFixed('price')" class="gs-row-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </button>
                  <div id="submit_row_price" v-show="rowOpen('price')" class="gs-row-body">
                    <div class="gs-grid2">
                      <div>
                        <label for="submit_ticket_price" class="{{ $lb }}">{{ __('messages.price') }}<span v-if="requiredFields.ticket_price" class="text-red-500" aria-hidden="true"> *</span></label>
                        <div class="mt-1 flex gap-2">
                          <input id="submit_ticket_price" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0.00" v-model="event.ticket_price" autocomplete="off" :class="bad('ticket_price')"
                            class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                          <select v-model="event.ticket_currency_code" aria-label="{{ __('messages.currency') }}" style="min-width: 5.75rem"
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            <option v-for="c in currencies" :key="c.value" :value="c.value">@{{ c.value }}</option>
                          </select>
                        </div>
                        <p v-if="msg('ticket_price')" class="gs-err">@{{ msg('ticket_price') }}</p>
                      </div>
                      <div>
                        <label for="submit_registration_url" class="{{ $lb }}">{{ __('messages.registration_url') }}<span v-if="requiredFields.registration_url" class="text-red-500" aria-hidden="true"> *</span></label>
                        <input id="submit_registration_url" type="url" inputmode="url" v-model="event.registration_url" autocomplete="off" placeholder="https://" :class="bad('registration_url')"
                          class="{{ $in }}">
                        <p v-if="msg('registration_url')" class="gs-err">@{{ msg('registration_url') }}</p>
                      </div>
                    </div>

                    {{-- Coupon code (curator-configured) --}}
                    <div class="gs-grid2 mt-4" v-if="importFields.coupon_code || requiredFields.coupon_code">
                      <div>
                        <label for="submit_coupon_code" class="{{ $lb }}">{{ __('messages.coupon_code') }}<span v-if="requiredFields.coupon_code" class="text-red-500" aria-hidden="true"> *</span></label>
                        <input id="submit_coupon_code" type="text" maxlength="255" v-model="event.coupon_code" autocomplete="off" :class="bad('coupon_code')" class="{{ $in }}">
                        <p v-if="msg('coupon_code')" class="gs-err">@{{ msg('coupon_code') }}</p>
                      </div>
                      <div>
                        <label for="submit_coupon_discount" class="{{ $lb }}">{{ __('messages.discount') }}</label>
                        <div class="mt-1 flex gap-2">
                          <input id="submit_coupon_discount" type="number" min="0" step="0.01" inputmode="decimal" v-model="event.coupon_discount" autocomplete="off"
                            class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                          <select v-model="event.coupon_discount_type" aria-label="{{ __('messages.discount') }}" style="min-width: 5.75rem"
                            class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            <option value="fixed">@{{ event.ticket_currency_code }}</option>
                            <option value="percentage">%</option>
                          </select>
                        </div>
                      </div>
                    </div>
                  </div>

                  {{-- Listing: what the schedule chose to ask (short description, category, sub-schedule) --}}
                  <template v-if="hasListingRow">
                  <button type="button" class="gs-row" :class="{ 'is-fixed': rowFixed('listing') }" data-row="listing" @click="toggleRow('listing')"
                    :aria-expanded="rowFixed('listing') ? null : (rowOpen('listing') ? 'true' : 'false')" :aria-disabled="rowFixed('listing') ? 'true' : null" aria-controls="submit_row_listing">
                    <span class="gs-row-title">{{ __('messages.guest_submit_listing') }}<span v-if="requiredFields.short_description || requiredFields.category_id || (requiredFields.group_id && groups.length)" class="text-red-500" aria-hidden="true"> *</span></span>
                    <span class="gs-row-summary" :class="{ 'is-empty': !rows.listing }"><bdi v-text="rows.listing || (rowFixed('listing') ? '' : words.optional)"></bdi></span>
                    <svg v-if="!rowFixed('listing')" class="gs-row-chevron" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                  </button>
                  <div id="submit_row_listing" v-show="rowOpen('listing')" class="gs-row-body">
                    <div class="mb-4" v-if="importFields.short_description || requiredFields.short_description">
                      <div class="flex items-baseline justify-between">
                        <label for="submit_short_description" class="{{ $lb }}">{{ __('messages.short_description') }}<span v-if="requiredFields.short_description" class="text-red-500" aria-hidden="true"> *</span></label>
                        <span class="text-xs text-gray-400 dark:text-gray-500" aria-hidden="true">@{{ (event.short_description || '').length }}/200</span>
                      </div>
                      <input id="submit_short_description" type="text" maxlength="200" v-model="event.short_description" autocomplete="off" :class="bad('short_description')" class="{{ $in }}">
                      <p v-if="msg('short_description')" class="gs-err">@{{ msg('short_description') }}</p>
                    </div>
                    <div class="gs-grid2">
                      <div v-if="importFields.category_id || requiredFields.category_id">
                        <label for="submit_category_id" class="{{ $lb }}">{{ __('messages.category') }}<span v-if="requiredFields.category_id" class="text-red-500" aria-hidden="true"> *</span></label>
                        <select id="submit_category_id" v-model="event.category_id" :class="bad('category_id')" class="{{ $in }}">
                          <option value="">{{ __('messages.please_select') }}</option>
                          <option v-for="c in categories" :key="c.id" :value="c.id">@{{ c.name }}</option>
                        </select>
                        <p v-if="msg('category_id')" class="gs-err">@{{ msg('category_id') }}</p>
                      </div>
                      <div v-if="(importFields.group_id || requiredFields.group_id) && groups.length > 0">
                        <label for="submit_group_id" class="{{ $lb }}">{{ __('messages.schedule') }}<span v-if="requiredFields.group_id" class="text-red-500" aria-hidden="true"> *</span></label>
                        <select id="submit_group_id" v-model="event.group_id" :class="bad('group_id')" class="{{ $in }}">
                          <option value="">{{ __('messages.please_select') }}</option>
                          <option v-for="g in groups" :key="g.id" :value="g.id">@{{ g.name }}</option>
                        </select>
                        <p v-if="msg('group_id')" class="gs-err">@{{ msg('group_id') }}</p>
                      </div>
                    </div>
                  </div>
                  </template>

                </div>
              </div>
            </div>

            {{-- 4. The schedule's own questions. Labels and options come through Vue data and are
                 interpolated with Vue's own mustaches, so owner-authored text is never compiled as a
                 template (see the note where $requestCustomFields is built). --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl mt-4" v-if="requestCustomFields.length">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h" v-text="words.questions"></h3>

                <div v-for="field in requestCustomFields" :key="field.key" class="mb-4">
                  <label v-if="field.type !== 'switch'" :id="'submit_custom_field_label_' + field.key" :for="'submit_custom_field_' + field.key" class="{{ $lb }}">
                    @{{ field.label }}<span v-if="field.required" class="text-red-500" aria-hidden="true"> *</span>
                  </label>
                  {{-- Under the question and before its control, not after it: under a list of
                       eight rooms it was nine rows below the question, and under a text box it was
                       read after typing. Who will read an answer is said before it is given. --}}
                  <p v-if="field.on_event" :id="'pub_cf_' + field.key" class="gs-hint" data-answer-on-event>{{ __('messages.request_answer_on_event') }}</p>

                  <input v-if="field.type === 'string'"
                    :id="'submit_custom_field_' + field.key"
                    type="text"
                    v-model="customFieldValues[field.key]"
                    :pattern="field.regex || null"
                    :class="bad('cf_' + field.key)"
                    :aria-describedby="[msg('cf_' + field.key) ? 'err_cf_' + field.key : null, field.on_event ? 'pub_cf_' + field.key : null].filter(Boolean).join(' ') || null"
                    dir="auto"
                    autocomplete="off"
                    class="{{ $in }}">

                  <textarea v-else-if="field.type === 'multiline_string'"
                    :id="'submit_custom_field_' + field.key"
                    rows="3"
                    dir="auto"
                    v-model="customFieldValues[field.key]"
                    :class="bad('cf_' + field.key)"
                    :aria-describedby="[msg('cf_' + field.key) ? 'err_cf_' + field.key : null, field.on_event ? 'pub_cf_' + field.key : null].filter(Boolean).join(' ') || null"
                    class="{{ $in }}"></textarea>

                  {{-- A yes/no question is a box beside its own words, not a box under a heading. --}}
                  <label v-else-if="field.type === 'switch'" class="flex items-start gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                    <input type="checkbox"
                      :id="'submit_custom_field_' + field.key"
                      v-model="customFieldValues[field.key]"
                      true-value="1"
                      false-value="0"
                      class="mt-0.5 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                    <span>@{{ field.label }}<span v-if="field.required" class="text-red-500" aria-hidden="true"> *</span></span>
                  </label>

                  {{-- Flatpickr, as every other date on the site: wired per field once mounted. --}}
                  <input v-else-if="field.type === 'date'"
                    :id="'submit_custom_field_' + field.key"
                    type="text"
                    data-gs-date
                    :data-key="field.key"
                    :data-required="field.required ? '1' : '0'"
                    autocomplete="off"
                    :class="bad('cf_' + field.key)"
                    class="{{ $in }}">

                  <select v-else-if="field.type === 'dropdown'"
                    :id="'submit_custom_field_' + field.key"
                    v-model="customFieldValues[field.key]"
                    :class="bad('cf_' + field.key)"
                    :aria-describedby="[msg('cf_' + field.key) ? 'err_cf_' + field.key : null, field.on_event ? 'pub_cf_' + field.key : null].filter(Boolean).join(' ') || null"
                    class="{{ $in }}">
                    <option value="">{{ __('messages.please_select') }}</option>
                    <option v-for="option in field.options" :key="option" :value="option">@{{ option }}</option>
                  </select>

                  <div v-else-if="field.type === 'multiselect'" :id="'submit_custom_field_' + field.key" class="mt-1 space-y-1 rounded-lg" :class="bad('cf_' + field.key)">
                    <label v-for="option in field.options" :key="option" class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                      <input type="checkbox"
                        :value="option"
                        v-model="customFieldValues[field.key]"
                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                      @{{ option }}
                    </label>
                  </div>

                  <p v-if="msg('cf_' + field.key)" :id="'err_cf_' + field.key" class="gs-err">@{{ msg('cf_' + field.key) }}</p>
                  <p v-else-if="field.regex_hint && (field.type === 'string' || field.type === 'multiline_string')" class="gs-hint">@{{ field.regex_hint }}</p>
                </div>
              </div>
            </div>

            {{-- 5. Who is sending it --}}
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-xl mt-4">
              <div class="max-w-2xl mx-auto">
                <h3 class="gs-h" style="margin-bottom: 0.25rem">{{ __('messages.your_details') }}</h3>
                {{-- Only true for someone who is about to create an account: it contradicts the
                     "creation is disabled" notice below on a closed selfhost, and it told a
                     signed-in visitor to create an account they already have. --}}
                <p v-if="!isAuthed && registrationEnabled && accountMode === 'register'" class="text-sm text-gray-500 dark:text-gray-400 mb-5">{{ __('messages.your_details_help') }}</p>
                <div v-else class="mb-4"></div>

                <x-honeypot vmodel="honeypot" />

                {{-- Posting as (authenticated user with exactly one schedule) --}}
                <p v-if="postingAsName" class="gs-who mb-4">
                  {{ __('messages.posting_as') }} <strong>@{{ postingAsName }}</strong>
                </p>

                {{-- Schedule picker (authenticated user with multiple schedules) --}}
                <div v-if="showTalentPicker" class="mb-4">
                  <label for="post_as" class="{{ $lb }}">{{ __('messages.post_as') }}</label>
                  <select id="post_as" v-model="selectedTalentId" class="{{ $in }}">
                    <option v-for="t in talents" :key="t.id" :value="t.id">@{{ t.name }}</option>
                  </select>
                </div>

                {{-- Account fields (guests only) --}}
                <template v-if="!isAuthed">

                  {{-- Returning user: inline login --}}
                  <template v-if="accountMode === 'login'">
                    <div class="mb-4">
                      <label for="account_email" class="{{ $lb }}">{{ __('messages.email') }} {!! $star !!}</label>
                      <input id="account_email" type="email" v-model="userEmail" @blur="checkEmailExists" :readonly="emailExists === true" :class="bad('account_email')"
                        class="{{ $in }}" autocomplete="email">
                      <p v-if="msg('account_email')" id="err_account_email" class="gs-err">@{{ msg('account_email') }}</p>
                    </div>
                    <div class="mb-3 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg text-sm text-blue-800 dark:text-blue-200" aria-live="polite">
                      {{-- Keyed on the visitor, not the install. With registration closed every
                           email lands in login mode, so gating on registrationEnabled alone told a
                           returning user their account could not be created. When registration is
                           open, accountMode === 'login' already implies emailExists && !emailStub,
                           so this leaves that path unchanged. --}}
                      <span v-if="emailExists && !emailStub">{{ __('messages.email_already_registered_login') }}</span>
                      <span v-else-if="!registrationEnabled">{{ __('messages.account_creation_disabled') }}</span>
                    </div>
                    <div class="mb-2">
                      <label for="login_password" class="{{ $lb }}">{{ __('messages.password') }} {!! $star !!}</label>
                      <div class="relative">
                        <input id="login_password" :type="showPassword ? 'text' : 'password'" v-model="userPassword" autocomplete="current-password" :class="bad('account_password')"
                          class="{{ $in }} pe-10">
                        <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? words.hide_password : words.show_password"
                          class="absolute end-2 top-1/2 -translate-y-1/2 mt-0.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                          <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                          <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                      </div>
                      <p v-if="msg('account_password')" id="err_account_password" class="gs-err">@{{ msg('account_password') }}</p>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                      <a href="{{ route('password.request') }}" target="_blank" class="text-sm text-[var(--brand-blue)] underline hover:no-underline">{{ __('messages.forgot_your_password') }}</a>
                      <button type="button" @click="useAnotherEmail" class="text-sm text-gray-500 dark:text-gray-400 underline hover:no-underline">{{ __('messages.use_another_email') }}</button>
                    </div>
                  </template>

                  {{-- New user: Google first, then email. The emailed code is asked for after
                       Submit, on its own step. --}}
                  <template v-else>
                    @if (config('services.google.client_id') && public_registration_enabled())
                    {{-- Same gate as auth/register.blade.php, so this also disappears wherever
                         closed registration does. Offered first: the honest answer to "why do I
                         need a password" is that with Google you do not. The link goes through
                         event.guest_submit.google, which stores this page as the post-login
                         destination before handing off to Google, so the round trip comes back
                         here where the draft is. --}}
                    <div class="mb-4">
                      <a href="{{ route('event.guest_submit.google', $googleParams) }}" class="w-full inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-sm text-gray-700 dark:text-gray-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[var(--brand-blue)] dark:focus:ring-offset-gray-800 transition-all duration-200">
                        <svg class="w-5 h-5 me-2" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                          <path fill="#4285F4" d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z"/>
                          <path fill="#34A853" d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z"/>
                          <path fill="#FBBC05" d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z"/>
                          <path fill="#EA4335" d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z"/>
                        </svg>
                        {{ __('messages.sign_up_with_google') }}
                      </a>
                      {{-- Flex rule + label, not an absolutely-positioned line behind an opaque
                           label: .ap-card is a gradient, so no flat mask colour can match it. --}}
                      <div class="my-4 flex items-center gap-4">
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                        <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.or') }}</span>
                        <div class="flex-1 h-px bg-gray-300 dark:bg-gray-600"></div>
                      </div>
                    </div>
                    @endif
                    <div class="mb-4">
                      <label for="account_email" class="{{ $lb }}">{{ __('messages.email') }} {!! $star !!}</label>
                      <div class="relative">
                        <input id="account_email" type="email" v-model="userEmail" @blur="checkEmailExists" :class="bad('account_email')" :aria-invalid="msg('account_email') ? 'true' : 'false'"
                          class="{{ $in }}" autocomplete="email">
                        <span v-if="emailChecking" class="absolute end-3 top-3 text-xs text-gray-400 dark:text-gray-500" aria-live="polite">{{ __('messages.checking') }}</span>
                      </div>
                      <p v-if="msg('account_email')" id="err_account_email" class="gs-err">@{{ msg('account_email') }}</p>
                    </div>
                    <div class="mb-4">
                      <label for="account_name" class="{{ $lb }}">{{ __('messages.name') }} {!! $star !!}</label>
                      <input id="account_name" type="text" v-model="userName" autocomplete="name" maxlength="255" :class="bad('account_name')" class="{{ $in }}">
                      <p v-if="msg('account_name')" id="err_account_name" class="gs-err">@{{ msg('account_name') }}</p>
                    </div>
                    <div class="mb-4">
                      <label for="account_password" class="{{ $lb }}">{{ __('messages.password') }} {!! $star !!}</label>
                      <div class="relative">
                        <input id="account_password" :type="showPassword ? 'text' : 'password'" v-model="userPassword" autocomplete="new-password" aria-describedby="account_password_help" :class="bad('account_password')"
                          class="{{ $in }} pe-10">
                        <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? words.hide_password : words.show_password"
                          class="absolute end-2 top-1/2 -translate-y-1/2 mt-0.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                          <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                          <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                      </div>
                      <p v-if="msg('account_password')" id="err_account_password" class="gs-err">@{{ msg('account_password') }}</p>
                      <p id="account_password_help" class="gs-hint" v-show="!msg('account_password')">{{ __('messages.guest_submit_password_help') }}</p>
                    </div>

                  </template>
                </template>

                {{-- The page the event will live on, for anyone about to get one: a new account, or
                     someone signed in who has no page of their own yet. It defaults to their name,
                     so it is one line saying so, and a field only for whoever wants another. --}}
                <div v-if="needsPageName && (pageNameShown || showPageName)" class="mb-4">
                  <p v-if="!showPageName" class="gs-who">
                    {{ __('messages.posting_as') }} <strong v-text="pageNameShown"></strong>
                    <button type="button" class="gs-link" @click="openPageName">{{ __('messages.edit') }}</button>
                  </p>
                  <div v-show="showPageName">
                    <label for="schedule_name" class="{{ $lb }}">{{ __('messages.your_page_name') }} <span class="font-normal text-gray-400 dark:text-gray-500">({{ __('messages.optional') }})</span></label>
                    <input id="schedule_name" type="text" v-model="scheduleName" autocomplete="off" maxlength="255" :placeholder="userName" class="{{ $in }}">
                    <div><p v-pre class="gs-hint">{{ __('messages.your_page_name_help', ['name' => $scheduleName]) }}</p></div>
                  </div>
                </div>

                <template v-if="!isAuthed">
                  <template v-if="accountMode !== 'login'">
                    @if (\App\Utils\TurnstileUtils::isEnabled())
                    <div v-if="turnstileEnabled" class="mt-4">
                      <div id="turnstile-import-widget"></div>
                    </div>
                    @endif

                    <div id="account_terms_row" class="mt-4 flex items-start gap-2 rounded-lg" :class="bad('terms')">
                      <input id="account_terms" type="checkbox" v-model="acceptedTerms"
                        class="mt-1 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                      <label for="account_terms" class="text-sm text-gray-700 dark:text-gray-300">
                        @if (config('app.hosted'))
                          {!! str_replace([':terms', ':privacy'], [
                              '<a href="' . policy_url('terms') . '" target="_blank" class="text-[var(--brand-blue)] hover:underline">' . __('messages.terms_of_service') . '</a>',
                              '<a href="' . policy_url('privacy') . '" target="_blank" class="text-[var(--brand-blue)] hover:underline">' . __('messages.privacy_policy') . '</a>'
                          ], __('messages.i_accept_the_terms_and_privacy')) !!}
                        @else
                          {!! str_replace([':terms'], [
                              '<a href="' . policy_url('terms', '/self-hosting-terms-of-service') . '" target="_blank" class="text-[var(--brand-blue)] hover:underline">' . __('messages.terms_of_service') . '</a>',
                          ], __('messages.i_accept_the_terms')) !!}
                        @endif
                      </label>
                    </div>
                    <p v-if="msg('terms')" id="err_terms" class="gs-err">@{{ msg('terms') }}</p>
                  </template>
                </template>

                {{-- Said to everyone it is about to happen to: a new account, someone signing in,
                     and someone already signed in who does not follow this schedule yet. --}}
                {{-- The v-if is on a wrapper: v-pre switches off every directive on its own element. --}}
                <div v-if="willFollow">
                  <p v-pre class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.guest_submit_consent', ['name' => $scheduleName]) }}</p>
                </div>
              </div>
            </div>

            {{-- The bar. Always on screen; its line is what Submit will do, or what it still needs. --}}
            <div class="gs-bar" id="submit-bar">
              <div class="gs-bar-status" :class="{ 'is-bad': bar.kind === 'bad', 'is-ready': bar.kind === 'ready', 'is-todo': bar.kind === 'todo' }" v-show="bar.kind !== 'quiet'" role="status" aria-live="polite" aria-atomic="true" tabindex="-1" id="submit-error-box">
                {{-- Each name is a way to the field. Three at most, then a count: on a phone a
                     longer list is a bar that covers the field it is pointing at. --}}
                <template v-if="bar.items.length">
                  <span v-text="bar.lead"></span>
                  <template v-for="(p, i) in bar.items" :key="p.key"><button type="button" @click="goTo(p)" v-text="p.label"></button><span v-if="i < bar.items.length - 1 || bar.more" v-text="', '"></span></template>
                  <button v-if="bar.more" type="button" @click="goTo(bar.next)" v-text="'+' + bar.more"></button>
                </template>
                <span v-else v-text="bar.text"></span>
              </div>
              <div class="gs-bar-actions">
                <a href="{{ $role->getGuestUrl() }}" class="gs-bar-cancel" v-show="!saving">{{ __('messages.cancel') }}</a>
                {{-- Always enabled (except while busy): pressing it with something missing says
                     what, here and at the field, which beats a mute disabled button. --}}
                {{-- A page left open past its session cannot send anything: the one thing to press is Reload. What was typed is kept. --}}
                <button v-if="expired" type="button" id="reload-btn" @click="reloadPage" class="gs-bar-go gs-fill">{{ __('messages.reload') }}</button>
                <button v-else type="submit" :disabled="saving || flyerBusy || codeSending" class="gs-bar-go gs-fill">
                  <span v-if="saving || codeSending">{{ __('messages.submitting') }}</span>
                  <span v-else-if="accountMode === 'login' && !isAuthed">{{ __('messages.log_in_and_submit') }}</span>
                  <span v-else>{{ __('messages.submit_event') }}</span>
                </button>
              </div>
            </div>
          </form>

          {{-- The emailed code, after Submit. v-show, so the form above keeps its widgets. --}}
          <div v-show="!submitted && step === 'code'" class="ap-card gs-code-card p-6 sm:p-10 shadow-md rounded-xl">
            <div class="gs-step">
              <h2 id="submit_code_heading" tabindex="-1" class="focus:outline-none">{{ __('messages.check_your_email') }}</h2>
              <p id="submit_code_sent_to" class="mt-2 text-gray-600 dark:text-gray-400" aria-live="polite">
                {{ __('messages.code_sent_to_prefix') }} <span class="font-medium text-gray-900 dark:text-gray-100">@{{ userEmail }}</span>. {{ __('messages.code_sent_to_suffix') }}
              </p>
              <div class="gs-ticket">
                <img v-if="sent.image" :src="sent.image" alt="">
                <div class="min-w-0">
                  <div class="gs-ticket-name" v-text="sent.name"></div>
                  <div class="gs-ticket-sub" v-text="sent.when"></div>
                  <div class="gs-ticket-sub" v-text="sent.where"></div>
                </div>
              </div>
              <label for="verification_code" class="sr-only">{{ __('messages.verification_code') }}</label>
              {{-- Six boxes, ONE input, as on the sign-up page: a phone's "code from Mail" suggestion
                   fills a single one-time-code field, a pasted "Your code is 123456" fills all six,
                   and a screen reader meets one labelled field. No maxlength (the browser would
                   cut a paste before the handler can strip the words around the code); dir="ltr"
                   because a code reads left to right in every language. --}}
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
              {{-- The sixth digit sends the form by itself; the button is for anyone who would rather press one. --}}
              <button type="button" @click="submitCode" :disabled="saving" class="gs-bar-go gs-fill gs-code-go">
                <span v-if="saving">{{ __('messages.submitting') }}</span>
                <span v-else>{{ __('messages.submit_event') }}</span>
              </button>
              <div class="gs-step-links">
                <span v-if="resendCountdown > 0">{{ __('messages.didnt_receive_code') }} {{ __('messages.resend_in_label') }} <span class="tabular-nums">@{{ resendCountdown }}s</span></span>
                <span v-else>{{ __('messages.didnt_receive_code') }} <button type="button" @click="resendCode" :disabled="codeSending" class="gs-link">{{ __('messages.resend_code') }}</button></span>
                <button type="button" @click="backToForm('email')" class="gs-link">{{ __('messages.use_another_email') }}</button>
                <button type="button" @click="backToForm('event')" class="gs-link">{{ __('messages.edit_event') }}</button>
              </div>
            </div>
          </div>

          {{-- Confirmation screen (pending vs live). v-if, not v-show: its bindings dereference
               submissionResult, which is null until a successful submit - eager (v-show) evaluation
               would throw at mount and break the whole app. The form above stays v-show so its
               Flatpickr/EasyMDE widgets survive "Submit another". --}}
          <div v-if="submitted" class="ap-card p-6 sm:p-10 shadow-md rounded-xl">
            <div class="max-w-2xl mx-auto text-center">
              <div class="gs-tick">
                <svg fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path></svg>
              </div>
              <h2 id="submission-success-heading" tabindex="-1" class="text-2xl font-bold text-gray-900 dark:text-gray-100 focus:outline-none">
                <span v-if="submissionResult.status === 'live'">{{ __('messages.youre_live_title') }}</span>
                <span v-else>{{ __('messages.submitted_pending_title') }}</span>
              </h2>
              <p class="mt-3 text-gray-600 dark:text-gray-400">
                <template v-if="submissionResult.status === 'live'">
                  <span class="font-semibold">@{{ submissionResult.event_name }}</span> <span v-pre>{{ __('messages.youre_live_body', ['curator' => $scheduleName]) }}</span>
                </template>
                <template v-else>
                  <span v-pre>{{ __('messages.submitted_pending_body', ['curator' => $scheduleName]) }}</span>
                </template>
              </p>

              {{-- What was sent, so "it worked" is something to look at. --}}
              <div class="gs-ticket" data-gs-sent>
                <img v-if="sent.image" :src="sent.image" alt="">
                <div class="min-w-0">
                  <div class="gs-ticket-name" v-text="sent.name"></div>
                  <div class="gs-ticket-sub" v-text="sent.when"></div>
                  <div class="gs-ticket-sub" v-text="sent.where"></div>
                </div>
              </div>

              <ol v-if="submissionResult.status !== 'live'" class="gs-steps">
                <li class="is-done">{{ __('messages.submitted_step_sent') }}</li>
                <li class="is-now"><span v-pre>{{ __('messages.submitted_step_review', ['name' => $scheduleName]) }}</span></li>
                <li>{{ __('messages.submitted_step_email') }}</li>
              </ol>

              <div class="gs-after mt-6">
                <button type="button" @click="resetForAnother" class="gs-after-again text-sm text-gray-500 dark:text-gray-400 underline hover:no-underline">{{ __('messages.submit_another') }}</button>
                <template v-if="submissionResult.status === 'live'">
                  <a :href="submissionResult.dashboard_url" class="gs-btn gs-after-btn">{{ __('messages.go_to_dashboard') }}</a>
                  <a :href="submissionResult.view_url" class="gs-fill gs-after-btn gs-after-main">{{ __('messages.view_event') }}</a>
                </template>
                <template v-else>
                  <a :href="submissionResult.view_url" class="gs-btn gs-after-btn">{{ __('messages.view_event') }}</a>
                  <a :href="submissionResult.dashboard_url" class="gs-fill gs-after-btn gs-after-main">{{ __('messages.go_to_dashboard') }}</a>
                </template>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>

  @include('partials.request-form-kit')
  @include('event.partials.guest-submit-script')

</x-app-guest-layout>
