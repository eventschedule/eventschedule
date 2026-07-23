<x-app-guest-layout :role="$role" :showMobileBackground="true">

@php
  $hasHeaderImage = ($role->header_image && ! in_array($role->header_image, ['none', 'logos'], true)) || $role->header_image_url;
  $accentColor = $role->accent_color ?? '#4E81FA';
  $contrastColor = accent_contrast_color($accentColor);
  $use24hr = get_use_24_hour_time($role);
  $importFields = $role->import_config['fields'] ?? [];
  $requiredImportFields = $role->import_config['required_fields'] ?? [];
  $hasRequiredImportFields = (bool) array_filter($requiredImportFields);
@endphp

<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
@if (\App\Utils\TurnstileUtils::isEnabled())
<script src="https://challenges.cloudflare.com/turnstile/v0/api.js?render=explicit" async defer {!! nonce_attr() !!}></script>
@endif

<style {!! nonce_attr() !!}>
    #event-submit-app { visibility: hidden; }
    #event-submit-app.loaded { visibility: visible; }
</style>

  @if ($role->profile_image_url && !$hasHeaderImage && $role->language_code == 'en')
  <div class="pt-8"></div>
  @endif

  <main>
    <div>
      <div class="container mx-auto pt-7 pb-20 px-5 max-w-3xl">
        {{-- Header card --}}
        <div class="ap-card mb-4 {{ !$hasHeaderImage && $role->profile_image_url ? 'pt-16' : '' }} rounded-lg shadow-md">
          <div class="relative before:block before:absolute before:bg-[#00000033] before:-inset-0">
            @if ($role->header_image && ! in_array($role->header_image, ['none', 'logos'], true))
            <picture>
              <source srcset="{{ asset('images/headers') }}/{{ $role->header_image }}.webp" type="image/webp">
              <img class="block max-h-72 w-full object-cover rounded-t-2xl" src="{{ asset('images/headers') }}/{{ $role->header_image }}.png" />
            </picture>
            @elseif ($role->header_image_url)
            <img class="block max-h-72 w-full object-cover rounded-t-2xl" src="{{ $role->header_image_url }}" />
            @endif
          </div>
          <div class="px-4 sm:px-8 pb-4 relative z-10">
            @if ($role->profile_image_url)
            <div class="rounded-lg w-[130px] h-[130px] -mt-[100px] -ms-1 mb-6 bg-white dark:bg-gray-800 flex items-center justify-center">
              <img class="rounded-lg w-[120px] h-[120px] object-cover" src="{{ $role->profile_image_url }}" alt="person" />
            </div>
            @else
            <div style="height: 42px;"></div>
            @endif

            <div class="flex justify-between items-center gap-6 mb-5">
              <div>
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                  {{ __('messages.submit_your_event') }}
                </h2>
                <h3 class="text-gray-700 dark:text-gray-300">
                  {{ __('messages.submitting_to', ['name' => $role->getDisplayName(true)]) }}
                </h3>
              </div>
              <div class="hidden sm:flex items-center gap-3">
                <a href="{{ $role->getGuestUrl() }}" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
                  {{ __('messages.view_schedule') }}
                </a>
              </div>
            </div>
          </div>
        </div>

        {{-- Vue submission app --}}
        <div id="event-submit-app">

          <form v-show="!submitted" @submit.prevent="submitEvent" novalidate>

            {{-- Draft restored banner --}}
            <div v-if="draftRestored" class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg flex items-center gap-3 text-sm text-blue-800 dark:text-blue-200" aria-live="polite">
              <span class="flex-1">{{ __('messages.draft_restored') }}</span>
              <button type="button" @click="startFresh" class="underline hover:no-underline shrink-0">{{ __('messages.start_fresh') }}</button>
              <button type="button" @click="draftRestored = false" aria-label="{{ __('messages.close') }}" class="shrink-0 p-1 text-blue-400 hover:text-blue-600 dark:hover:text-blue-200">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12" /></svg>
              </button>
            </div>

            {{-- Event details --}}
            <div class="ap-card p-4 sm:p-8 shadow-md sm:rounded-xl">
              <div class="max-w-2xl mx-auto">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.event_details') }}</h3>

                @if (config('services.google.gemini_key') || config('services.openai.api_key'))
                {{-- AI auto-fill dropzone: the fast path. Dropping/pasting/selecting a flyer image
                     parses immediately; pasted text parses via the Auto Fill button. Collapses to a
                     success strip once applied. --}}
                <div class="mb-6">
                  <div v-if="autoFillDone" class="p-3 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 rounded-lg flex items-center gap-2 text-sm text-green-800 dark:text-green-300" aria-live="polite">
                    <svg class="h-5 w-5 shrink-0 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                    <span class="flex-1">{{ __('messages.auto_fill_applied') }}</span>
                    <button type="button" @click="reopenAutoFill" class="underline hover:no-underline shrink-0">{{ __('messages.auto_fill_start_over') }}</button>
                  </div>
                  <div v-else class="relative rounded-xl border-2 border-dashed border-gray-300 dark:border-gray-600 bg-gray-50 dark:bg-[#252526] p-4 transition-all duration-200"
                    :style="autoFillDragging ? { borderColor: '{{ $accentColor }}' } : {}"
                    @dragenter.prevent="autoFillDragEnter"
                    @dragover.prevent
                    @dragleave.prevent="autoFillDragLeave"
                    @drop.prevent="autoFillDrop">
                    <div class="flex items-center gap-2 mb-1">
                      <svg class="h-5 w-5 shrink-0 text-[var(--brand-blue)]" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904 9.374 17.5l-.439-1.596a3.375 3.375 0 0 0-2.339-2.34L5 13.126l1.596-.439a3.375 3.375 0 0 0 2.34-2.34L9.374 8.75l.439 1.596a3.375 3.375 0 0 0 2.34 2.34l1.596.438-1.596.439a3.375 3.375 0 0 0-2.34 2.34ZM18.259 8.715 18 9.75l-.259-1.035a2.625 2.625 0 0 0-1.91-1.91L14.796 6.546l1.035-.259a2.625 2.625 0 0 0 1.91-1.91L18 3.34l.259 1.035a2.625 2.625 0 0 0 1.91 1.91l1.035.259-1.035.259a2.625 2.625 0 0 0-1.91 1.91Z" /></svg>
                      <span class="font-medium text-sm text-gray-900 dark:text-gray-100">{{ __('messages.auto_fill_pitch') }}</span>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.auto_fill_hint') }}</p>
                    <textarea v-model="autoFillText" rows="2" :placeholder="autoFillPlaceholder" @paste="onAutoFillPaste" dir="auto"
                      class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"></textarea>
                    <input type="file" ref="autoFillImageInput" accept="image/*" class="hidden" @change="onAutoFillImageSelected">
                    <div class="mt-2 flex flex-wrap items-center gap-2">
                      <button type="button" @click="$refs.autoFillImageInput.click()" :disabled="parsing"
                        class="px-4 py-2 text-sm rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 disabled:opacity-50">
                        {{ __('messages.upload_flyer') }}
                      </button>
                      <span v-if="autoFillImage" class="inline-flex items-center gap-2">
                        <img v-if="autoFillImageUrl" :src="autoFillImageUrl" alt="" class="w-12 h-12 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                        <button type="button" @click="clearAutoFillImage" class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 underline">{{ __('messages.remove') }}</button>
                      </span>
                      <button type="button" v-show="autoFillText.trim() || autoFillImage" @click="runAutoFill" :disabled="parsing"
                        class="ms-auto px-4 py-2 text-sm font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 hover:shadow-md"
                        style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
                        {{ __('messages.auto_fill') }}
                      </button>
                    </div>
                    <p v-if="autoFillError" class="mt-2 text-sm text-red-600 dark:text-red-400" aria-live="polite">@{{ autoFillError }}</p>
                    {{-- Drop overlay (pointer-events-none so it can't steal dragleave from the zone) --}}
                    <div v-show="autoFillDragging" class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/90 dark:bg-gray-900/90 pointer-events-none">
                      <div class="text-center">
                        <svg class="mx-auto h-8 w-8 mb-2" :style="{ color: '{{ $accentColor }}' }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" /></svg>
                        <p class="font-medium" :style="{ color: '{{ $accentColor }}' }">{{ __('messages.drop_files_here') }}</p>
                      </div>
                    </div>
                    {{-- Parsing overlay --}}
                    <div v-show="parsing" class="absolute inset-0 z-10 flex items-center justify-center rounded-xl bg-white/80 dark:bg-gray-900/80">
                      <span class="inline-flex items-center gap-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                        <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        {{ __('messages.processing') }}
                      </span>
                    </div>
                  </div>
                </div>
                @endif

                <div class="mb-4">
                  <label for="submit_event_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.event_name') }} <span class="text-red-500">*</span></label>
                  <input id="submit_event_name" type="text" v-model="event.name" autocomplete="off" :class="errClass('name')"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                  <div>
                    <label for="submit_event_date" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.date') }} <span class="text-red-500">*</span></label>
                    <input id="submit_event_date" type="text" autocomplete="off" aria-label="{{ __('messages.date') }}"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                  </div>
                  <div>
                    <label for="submit_event_time" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.start_time') }} <span class="text-red-500">*</span></label>
                    <select id="submit_event_time" v-model="event.event_start_time" :class="errClass('event_start_time')"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                      <option value="">{{ __('messages.select') }}</option>
                      <option v-for="t in timeOptions" :key="t.value" :value="t.value">@{{ t.label }}</option>
                    </select>
                  </div>
                  <div>
                    <label for="submit_event_end_time" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.end_time') }} <span class="font-normal text-gray-400 dark:text-gray-500">({{ __('messages.optional') }})</span></label>
                    <select id="submit_event_end_time" v-model="event.event_end_time"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                      <option value="">{{ __('messages.select') }}</option>
                      <option v-for="t in timeOptions" :key="t.value" :value="t.value">@{{ t.label }}</option>
                    </select>
                  </div>
                </div>

                @if ($role->timezone)
                <p class="-mt-2 mb-4 text-sm text-gray-500 dark:text-gray-400" v-pre>
                  {{ __('messages.times_are_in_timezone') }} <span class="font-medium">{{ $role->timezone }}</span>.
                </p>

                <div v-if="timezoneMismatch" class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                  <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
                  </svg>
                  <p class="text-sm text-amber-700 dark:text-amber-300">@{{ timezoneMismatchMessage }}</p>
                </div>
                @endif

                {{-- Location --}}
                <div class="mb-4">
                  <div class="flex items-center gap-6">
                    <label class="inline-flex items-center">
                      <input type="radio" :value="false" v-model="event.is_online" class="h-4 w-4 border-gray-300" :style="{ accentColor: '{{ $accentColor }}' }">
                      <span class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.in_person') }}</span>
                    </label>
                    <label class="inline-flex items-center">
                      <input type="radio" :value="true" v-model="event.is_online" class="h-4 w-4 border-gray-300" :style="{ accentColor: '{{ $accentColor }}' }">
                      <span class="ms-2 text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.online') }}</span>
                    </label>
                  </div>
                </div>

                <div v-if="event.is_online" class="mb-4">
                  <label for="submit_event_url" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.event_url') }} <span class="text-red-500">*</span></label>
                  <input id="submit_event_url" type="url" v-model="event.event_url" autocomplete="off" :class="errClass('event_url')"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                </div>

                <div v-else>
                  <div class="mb-4">
                    <label for="submit_venue_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.venue_name') }} <span class="text-red-500">*</span></label>
                    <input id="submit_venue_name" type="text" v-model="event.venue_name" autocomplete="off" :class="errClass('venue_name')"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                  </div>
                  <div class="mb-4">
                    <label for="submit_venue_address1" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.street_address') }}</label>
                    <input id="submit_venue_address1" type="text" v-model="event.venue_address1" autocomplete="off"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                  </div>
                  <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-4">
                    <div>
                      <label for="submit_venue_city" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.city') }}</label>
                      <input id="submit_venue_city" type="text" v-model="event.venue_city" autocomplete="off"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                    </div>
                    <div>
                      <label for="submit_venue_state" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.state_province') }}</label>
                      <input id="submit_venue_state" type="text" v-model="event.venue_state" autocomplete="off"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                    </div>
                    <div>
                      <label for="submit_venue_postal_code" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.postal_code') }}</label>
                      <input id="submit_venue_postal_code" type="text" v-model="event.venue_postal_code" autocomplete="off"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                    </div>
                  </div>
                </div>

                {{-- Additional details (optional): description, image, price, registration --}}
                <div class="mt-2">
                  <button type="button" @click="showMoreDetails = !showMoreDetails" :aria-expanded="showMoreDetails ? 'true' : 'false'" aria-controls="additional-details-panel"
                    class="text-sm text-[var(--brand-blue)] underline hover:no-underline">
                    {{ __('messages.additional_details') }}@if (! $hasRequiredImportFields) ({{ __('messages.optional') }})@endif
                  </button>
                  <div id="additional-details-panel" v-show="showMoreDetails" class="mt-4">

                {{-- Short description (curator-configured) --}}
                <div class="mb-4" v-if="importFields.short_description || requiredFields.short_description">
                  <div class="flex items-baseline justify-between">
                    <label for="submit_short_description" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.short_description') }}<span v-if="requiredFields.short_description" class="text-red-500"> *</span></label>
                    <span class="text-xs text-gray-400 dark:text-gray-500" aria-hidden="true">@{{ (event.short_description || '').length }}/200</span>
                  </div>
                  <input id="submit_short_description" type="text" maxlength="200" v-model="event.short_description" autocomplete="off" :class="errClass('short_description')"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                </div>

                {{-- Description. EasyMDE renders inside the wrapper div, so the error ring goes on
                     the wrapper (the hidden textarea can't show it). --}}
                <div class="mb-4">
                  <label for="submit_description" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.description') }}<span v-if="requiredFields.description" class="text-red-500"> *</span></label>
                  <div class="mt-1 rounded-lg" :class="errClass('description')">
                    <textarea id="submit_description" class="html-editor block w-full" rows="4"></textarea>
                  </div>
                </div>

                {{-- Event image / flyer --}}
                <div class="mb-4">
                  <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.image') }}</label>
                  <input type="file" ref="imageInput" accept="image/*" class="hidden" @change="onImageSelected">
                  <div class="mt-1 flex items-center gap-3">
                    {{-- Thumbnail preview comes from the client-side file (temp uploads are not
                         publicly served); falls back to a text label when no local file exists. --}}
                    <img v-if="event.social_image && eventImageUrl" :src="eventImageUrl" alt="" class="w-16 h-16 object-cover rounded-lg border border-gray-300 dark:border-gray-600">
                    <span v-else-if="event.social_image" class="text-sm text-green-600 dark:text-green-400 inline-flex items-center gap-1">
                      <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                      {{ __('messages.image_attached') }}
                    </span>
                    <button type="button" @click="$refs.imageInput.click()" :disabled="isUploadingImage"
                      class="px-4 py-2 text-sm rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 disabled:opacity-50">
                      <span v-if="isUploadingImage">{{ __('messages.uploading') }}</span>
                      <span v-else-if="event.social_image">{{ __('messages.replace_image') }}</span>
                      <span v-else>{{ __('messages.upload_image') }}</span>
                    </button>
                    <button v-if="event.social_image" type="button" @click="removeEventImage" class="text-sm text-gray-400 hover:text-gray-600 dark:hover:text-gray-200 underline">{{ __('messages.remove') }}</button>
                  </div>
                </div>

                {{-- Price + registration --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-4">
                  <div>
                    <label for="submit_ticket_price" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.price') }}<span v-if="requiredFields.ticket_price" class="text-red-500"> *</span></label>
                    <div class="mt-1 flex gap-2">
                      <input id="submit_ticket_price" type="number" min="0" step="0.01" inputmode="decimal" placeholder="0.00" v-model="event.ticket_price" autocomplete="off" :class="errClass('ticket_price')"
                        class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                      <select v-model="event.ticket_currency_code"
                        class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                        <option v-for="c in currencies" :key="c.value" :value="c.value">@{{ c.value }}</option>
                      </select>
                    </div>
                  </div>
                  <div>
                    <label for="submit_registration_url" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.registration_url') }}<span v-if="requiredFields.registration_url" class="text-red-500"> *</span></label>
                    <input id="submit_registration_url" type="url" v-model="event.registration_url" autocomplete="off" :class="errClass('registration_url')"
                      class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                  </div>
                </div>

                {{-- Coupon code (curator-configured) --}}
                <div class="mb-4" v-if="importFields.coupon_code || requiredFields.coupon_code">
                  <label for="submit_coupon_code" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.coupon_code') }}<span v-if="requiredFields.coupon_code" class="text-red-500"> *</span></label>
                  <input id="submit_coupon_code" type="text" maxlength="255" v-model="event.coupon_code" autocomplete="off" :class="errClass('coupon_code')"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                </div>

                {{-- Category (curator-configured) --}}
                <div class="mb-4" v-if="importFields.category_id || requiredFields.category_id">
                  <label for="submit_category_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.category') }}<span v-if="requiredFields.category_id" class="text-red-500"> *</span></label>
                  <select id="submit_category_id" v-model="event.category_id" :class="errClass('category_id')"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                    <option value="">{{ __('messages.please_select') }}</option>
                    {{-- v-pre: custom category names are curator-controlled, and this select is
                         inside a Vue mount, so an unguarded mustache would be compiled as JS --}}
                    @foreach (get_translated_categories($role) as $catId => $catName)
                    <option v-pre value="{{ $catId }}">{{ $catName }}</option>
                    @endforeach
                  </select>
                </div>

                {{-- Sub-schedule (curator-configured) --}}
                <div class="mb-2" v-if="(importFields.group_id || requiredFields.group_id) && groups.length > 0">
                  <label for="submit_group_id" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.schedule') }}<span v-if="requiredFields.group_id" class="text-red-500"> *</span></label>
                  <select id="submit_group_id" v-model="event.group_id" :class="errClass('group_id')"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                    <option value="">{{ __('messages.please_select') }}</option>
                    <option v-for="g in groups" :key="g.id" :value="g.id">@{{ g.name }}</option>
                  </select>
                </div>

                  </div>
                </div>
              </div>
            </div>

            {{-- Account / identity panel --}}
            <div class="ap-card p-4 sm:p-8 shadow-md sm:rounded-xl mt-4">
              <div class="max-w-2xl mx-auto">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-1">{{ __('messages.your_details') }}</h3>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-5">{{ __('messages.your_details_help') }}</p>

                {{-- Honeypot --}}
                <input type="text" v-model="honeypot" name="website" tabindex="-1" autocomplete="off" class="hidden" aria-hidden="true">

                {{-- Posting as (authenticated user with exactly one schedule) --}}
                <p v-if="postingAsName" class="text-sm text-gray-700 dark:text-gray-300 mb-4">
                  {{ __('messages.posting_as') }} <span class="font-semibold">@{{ postingAsName }}</span>
                </p>

                {{-- Schedule picker (authenticated user with multiple schedules) --}}
                <div v-if="showTalentPicker" class="mb-4">
                  <label for="post_as" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.post_as') }}</label>
                  <select id="post_as" v-model="selectedTalentId" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                    <option v-for="t in talents" :key="t.id" :value="t.id">@{{ t.name }}</option>
                  </select>
                </div>

                {{-- Page name (optional - the server defaults it to the account name) --}}
                <div v-if="needsPageName" class="mb-4">
                  <label for="schedule_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.your_page_name') }} <span class="font-normal text-gray-400 dark:text-gray-500">({{ __('messages.optional') }})</span></label>
                  <input id="schedule_name" type="text" v-model="scheduleName" autocomplete="off" :placeholder="userName"
                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                  <p v-pre class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.your_page_name_help', ['name' => $role->getDisplayName(true)]) }}</p>
                </div>

                {{-- Account fields (guests only) --}}
                <template v-if="!isAuthed">
                  <div class="mb-4">
                    <label for="account_email" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.email') }} <span class="text-red-500">*</span></label>
                    <div class="relative">
                      <input id="account_email" type="email" v-model="userEmail" @blur="checkEmailExists" :readonly="accountMode === 'login' && emailExists" :class="errClass('account_email')"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm" autocomplete="email">
                      <span v-if="emailChecking" class="absolute end-3 top-3 text-xs text-gray-400" aria-live="polite">{{ __('messages.checking') }}</span>
                    </div>
                  </div>

                  {{-- Returning user: inline login --}}
                  <template v-if="accountMode === 'login'">
                    <div class="mb-3 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg text-sm text-blue-800 dark:text-blue-200" aria-live="polite">
                      {{ __('messages.email_already_registered_login') }}
                    </div>
                    <div class="mb-2">
                      <label for="login_password" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.password') }} <span class="text-red-500">*</span></label>
                      <div class="relative">
                        <input id="login_password" :type="showPassword ? 'text' : 'password'" v-model="userPassword" autocomplete="current-password" :class="errClass('account_password')"
                          class="mt-1 block w-full pe-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                        <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? hidePasswordLabel : showPasswordLabel"
                          class="absolute end-2 top-1/2 -translate-y-1/2 mt-0.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                          <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                          <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                      </div>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                      <a href="{{ route('password.request') }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-300 underline hover:no-underline">{{ __('messages.forgot_your_password') }}</a>
                      <button type="button" @click="useAnotherEmail" class="text-sm text-gray-500 dark:text-gray-400 underline hover:no-underline">{{ __('messages.use_another_email') }}</button>
                    </div>
                  </template>

                  {{-- New user: register + 6-digit code --}}
                  <template v-else>
                    <div class="mb-4">
                      <label for="account_name" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.name') }} <span class="text-red-500">*</span></label>
                      <input id="account_name" type="text" v-model="userName" autocomplete="name" :class="errClass('account_name')"
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                    </div>
                    <div class="mb-4">
                      <label for="account_password" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.password') }} <span class="text-red-500">*</span></label>
                      <div class="relative">
                        <input id="account_password" :type="showPassword ? 'text' : 'password'" v-model="userPassword" autocomplete="new-password" :class="errClass('account_password')"
                          class="mt-1 block w-full pe-10 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                        <button type="button" @click="showPassword = !showPassword" :aria-label="showPassword ? hidePasswordLabel : showPasswordLabel"
                          class="absolute end-2 top-1/2 -translate-y-1/2 mt-0.5 p-1 text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                          <svg v-if="showPassword" class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>
                          <svg v-else class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>
                        </button>
                      </div>
                      <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.password_min_chars') }}</p>
                    </div>

                    @if (\App\Utils\TurnstileUtils::isEnabled())
                    <div v-if="turnstileEnabled" class="mt-4">
                      <div id="turnstile-import-widget"></div>
                    </div>
                    @endif

                    @if (config('app.hosted') && ! config('app.is_testing'))
                    <div v-if="!codeSent" class="mt-4">
                      <button id="send_code_button" type="button" @click="sendCode" :disabled="codeSending" :class="errClass('verification_code')"
                        class="inline-flex items-center px-4 py-3 text-base font-semibold rounded-lg bg-blue-600 hover:bg-blue-700 text-white transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed">
                        <span v-if="!codeSending">{{ __('messages.email_me_a_code') }}</span>
                        <span v-else>{{ __('messages.sending') }}</span>
                      </button>
                    </div>
                    <div v-else class="mt-4">
                      <p class="text-sm text-gray-600 dark:text-gray-400 mb-2" aria-live="polite">
                        {{ __('messages.code_sent_to_prefix') }} <span class="font-medium">@{{ userEmail }}</span>. {{ __('messages.code_sent_to_suffix') }}
                      </p>
                      <label for="verification_code" class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.verification_code') }} <span class="text-red-500">*</span></label>
                      <input id="verification_code" type="text" inputmode="numeric" autocomplete="one-time-code" maxlength="6" placeholder="000000" :value="verificationCode" @input="onCodeInput" :class="errClass('verification_code')"
                        class="mt-1 block w-full sm:w-40 tracking-[0.4em] text-center border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-blue-500 focus:ring-blue-500 rounded-lg shadow-sm">
                      <div class="mt-2 text-sm">
                        <span class="text-gray-500 dark:text-gray-400">{{ __('messages.didnt_receive_code') }}</span>
                        <span v-if="resendCountdown > 0" class="text-gray-400 dark:text-gray-500">{{ __('messages.resend_in_label') }} @{{ resendCountdown }}s</span>
                        <button v-else type="button" @click="sendCode" class="text-blue-600 dark:text-blue-300 underline hover:no-underline">{{ __('messages.resend_code') }}</button>
                      </div>
                    </div>
                    @endif

                    <div id="account_terms_row" class="mt-4 flex items-start gap-2 rounded-lg" :class="errClass('terms')">
                      <input id="account_terms" type="checkbox" v-model="acceptedTerms"
                        class="mt-1 h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-600 focus:ring-blue-500">
                      <label for="account_terms" class="text-sm text-gray-700 dark:text-gray-300">
                        @if (config('app.hosted'))
                          {!! str_replace([':terms', ':privacy'], [
                              '<a href="' . marketing_url('/terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">' . __('messages.terms_of_service') . '</a>',
                              '<a href="' . marketing_url('/privacy') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">' . __('messages.privacy_policy') . '</a>'
                          ], __('messages.i_accept_the_terms_and_privacy')) !!}
                        @else
                          {!! str_replace([':terms'], [
                              '<a href="' . marketing_url('/self-hosting-terms-of-service') . '" target="_blank" class="text-blue-600 dark:text-blue-400 hover:underline">' . __('messages.terms_of_service') . '</a>',
                          ], __('messages.i_accept_the_terms')) !!}
                        @endif
                      </label>
                    </div>
                    <p v-pre class="mt-3 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.guest_submit_consent', ['name' => $role->getDisplayName(true)]) }}</p>
                  </template>
                </template>
              </div>
            </div>

            {{-- Error + Cancel/Submit --}}
            <div class="max-w-2xl mx-auto mt-4">
              <div v-if="errorMessage || accountError" id="submit-error-box" class="mb-3 p-3 text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-lg" aria-live="assertive">
                @{{ errorMessage || accountError }}
              </div>
              <div class="flex items-center gap-3">
                <a href="{{ $role->getGuestUrl() }}" class="px-4 py-3 text-base text-gray-700 dark:text-gray-300 bg-gray-200 dark:bg-gray-700 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200">
                  {{ __('messages.cancel') }}
                </a>
                {{-- Always enabled (except while busy): clicking with missing fields highlights
                     them and scrolls to the first one, which beats a mute disabled button. --}}
                <button type="submit" :disabled="saving || isUploadingImage"
                  class="flex-1 inline-flex items-center justify-center px-4 py-3 text-base font-semibold rounded-lg transition-all duration-200 disabled:opacity-50 disabled:cursor-not-allowed hover:shadow-md"
                  style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
                  <span v-if="saving">{{ __('messages.submitting') }}</span>
                  <span v-else-if="accountMode === 'login' && !isAuthed">{{ __('messages.log_in_and_submit') }}</span>
                  <span v-else>{{ __('messages.submit_event') }}</span>
                </button>
              </div>
              <p v-show="triedSubmit && missingFields.length" class="mt-2 text-sm text-red-600 dark:text-red-400 text-center" aria-live="polite">
                {{ __('messages.still_needed') }} @{{ missingFields.join(', ') }}
              </p>
            </div>
          </form>

          {{-- Confirmation screen (pending vs live). v-if, not v-show: its bindings dereference
               submissionResult, which is null until a successful submit - eager (v-show) evaluation
               would throw at mount and break the whole app. The form above stays v-show so its
               Flatpickr/EasyMDE widgets survive "Submit another". --}}
          <div v-if="submitted" class="ap-card p-6 sm:p-10 shadow-md sm:rounded-xl">
            <div class="max-w-2xl mx-auto text-center">
              <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-full bg-green-100 dark:bg-green-900/40">
                <svg class="h-7 w-7 text-green-600 dark:text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                </svg>
              </div>
              <h2 id="submission-success-heading" tabindex="-1" class="text-2xl font-bold text-gray-900 dark:text-gray-100 focus:outline-none">
                <span v-if="submissionResult && submissionResult.status === 'live'">{{ __('messages.youre_live_title') }}</span>
                <span v-else>{{ __('messages.submitted_pending_title') }}</span>
              </h2>
              <p class="mt-3 text-gray-600 dark:text-gray-400">
                <template v-if="submissionResult && submissionResult.status === 'live'">
                  <span class="font-semibold">@{{ submissionResult.event_name }}</span> <span v-pre>{{ __('messages.youre_live_body', ['curator' => $role->getDisplayName(true)]) }}</span>
                </template>
                <template v-else>
                  <span v-pre>{{ __('messages.submitted_pending_body', ['curator' => $role->getDisplayName(true)]) }}</span>
                </template>
              </p>

              <div class="mt-6 flex flex-col sm:flex-row items-center justify-center gap-3">
                <button type="button" @click="resetForAnother" class="text-sm text-gray-500 dark:text-gray-400 underline hover:no-underline order-last sm:order-first">{{ __('messages.submit_another') }}</button>
                <template v-if="submissionResult && submissionResult.status === 'live'">
                  <a :href="submissionResult.dashboard_url" class="px-4 py-3 text-base font-semibold rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200">{{ __('messages.go_to_dashboard') }}</a>
                  <a :href="submissionResult.view_url" class="px-4 py-3 text-base font-semibold rounded-lg text-white transition-all duration-200" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.view_event') }}</a>
                </template>
                <template v-else>
                  <a :href="submissionResult.view_url" class="px-4 py-3 text-base font-semibold rounded-lg bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200">{{ __('messages.view_event') }}</a>
                  <a :href="submissionResult.dashboard_url" class="px-4 py-3 text-base font-semibold rounded-lg text-white transition-all duration-200" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.go_to_dashboard') }}</a>
                </template>
              </div>
            </div>
          </div>

        </div>
      </div>
    </div>
  </main>

  <script {!! nonce_attr() !!}>
    const { createApp } = Vue;

    const app = createApp({
        data() {
            return {
                event: {
                    name: '',
                    name_en: '',
                    event_date: '',
                    event_start_time: '',
                    event_end_time: '',
                    is_online: false,
                    venue_name: '',
                    venue_name_en: '',
                    venue_country_code: @json($role->country_code),
                    venue_address1: '',
                    venue_city: '',
                    venue_state: '',
                    venue_postal_code: '',
                    event_url: '',
                    description: '',
                    social_image: null,
                    ticket_price: '',
                    ticket_currency_code: @json($defaultCurrency),
                    registration_url: '',
                    short_description: '',
                    short_description_en: '',
                    coupon_code: '',
                    category_id: '',
                    group_id: '',
                },
                currencies: @json(collect($currencies)->map(fn($c) => ['value' => $c->value])->values()),
                importFields: @json($importFields),
                // Times typed into this form are read as the curator schedule's local time. Resolved
                // in mounted() so we can warn a guest whose device sits in a different timezone.
                scheduleTimezone: @json($role->timezone),
                deviceTimezone: '',
                timezoneMismatchText: @json(__('messages.guest_submit_timezone_mismatch')),
                requiredFields: @json($requiredImportFields),
                groups: @json(($role->groups ?? collect())->map(fn($g) => ['id' => \App\Utils\UrlUtils::encodeId($g->id), 'name' => $g->translatedName()])->values()),
                categoryIds: @json(array_map('strval', array_keys(get_translated_categories($role)))),
                timeOptions: [],
                datePicker: null,
                // Require-account / identity
                requireAccount: true,
                isAuthed: {{ auth()->check() ? 'true' : 'false' }},
                requiresCode: {{ (config('app.hosted') && ! config('app.is_testing')) ? 'true' : 'false' }},
                turnstileEnabled: {{ \App\Utils\TurnstileUtils::isEnabled() ? 'true' : 'false' }},
                turnstileSiteKey: @json(\App\Utils\TurnstileUtils::getSiteKey()),
                turnstileToken: '',
                turnstileWidgetId: null,
                talents: @json(auth()->check() ? auth()->user()->talents()->get()->map(fn($t) => ['id' => \App\Utils\UrlUtils::encodeId($t->id), 'name' => $t->name])->values() : []),
                accountMode: 'register',
                scheduleName: '',
                userName: @json(auth()->check() ? auth()->user()->name : ''),
                userEmail: '',
                userPassword: '',
                verificationCode: '',
                acceptedTerms: false,
                emailChecking: false,
                emailExists: null,
                emailStub: false,
                codeSent: false,
                codeSending: false,
                resendCountdown: 0,
                resendTimer: null,
                selectedTalentId: '',
                honeypot: '',
                submitted: false,
                submissionResult: null,
                accountError: null,
                errorMessage: null,
                saving: false,
                isUploadingImage: false,
                showPassword: false,
                showPasswordLabel: @json(__('messages.show_password')),
                hidePasswordLabel: @json(__('messages.hide_password')),
                // Inline validation: fields only show error styling after a submit attempt.
                triedSubmit: false,
                pendingSubmit: false,
                // Start open when the curator marked any submission field required - those
                // fields live in this section, so hiding them would strand the submitter.
                showMoreDetails: {{ $hasRequiredImportFields ? 'true' : 'false' }},
                autoFillText: '',
                autoFillImage: null,
                autoFillImageUrl: null,
                autoFillDone: false,
                autoFillDragging: false,
                autoFillDragDepth: 0,
                autoFillError: null,
                autoFillPlaceholder: @json(__('messages.auto_fill_placeholder')),
                parsing: false,
                eventImageUrl: null,
                emailCheckSeq: 0,
                // Draft autosave (localStorage, event fields only - never account data)
                draftKey: 'es_guest_submit_draft_' + @json($role->subdomain),
                draftRestored: false,
                draftTimer: null,
            }
        },

        created() {
            this.timeOptions = this.generateTimeOptions();
            if (this.isAuthed && this.talents.length) {
                this.selectedTalentId = this.talents[0].id;
            }
        },

        mounted() {
            try {
                this.deviceTimezone = Intl.DateTimeFormat().resolvedOptions().timeZone || '';
            } catch (e) { /* very old browsers */ }

            this.$nextTick(() => {
                document.getElementById('event-submit-app').classList.add('loaded');

                // Autofocus the event name on desktop only (avoid popping the mobile keyboard)
                if (window.innerWidth >= 640) {
                    var nameEl = document.getElementById('submit_event_name');
                    if (nameEl) nameEl.focus();
                }

                // Turnstile (register mode is the default)
                this.maybeRenderTurnstile();
            });

            // Flatpickr and EasyMDE live in the deferred Vite bundle, which runs AFTER this
            // parse-time script mounts - their window globals are not defined yet. Initialize
            // them on DOMContentLoaded (deferred modules run first), matching booking-request.
            var self = this;
            var initWidgets = function() {
                // Flatpickr date
                var dateEl = document.getElementById('submit_event_date');
                if (dateEl && window.flatpickr) {
                    var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
                    var localeConfig = fpLocale ? { locale: fpLocale } : {};
                    self.datePicker = window.flatpickr(dateEl, Object.assign({
                        allowInput: true,
                        altInput: true,
                        altFormat: 'M j, Y',
                        dateFormat: 'Y-m-d',
                        minDate: 'today',
                        onChange: function(selectedDates, dateStr) { self.event.event_date = dateStr; },
                    }, localeConfig));
                }

                // EasyMDE description. The global .html-editor handler (app.js, also on
                // DOMContentLoaded but registered after this listener) inits it too if we
                // don't get there first; both paths guard via _easyMDE, and submit reads the
                // instance from the node either way. editor-helpers' IntersectionObserver
                // refreshes the editor when the collapsed section is revealed.
                var descEl = document.getElementById('submit_description');
                if (descEl && !descEl._easyMDE && window.initTinyMDE) {
                    // Sync the editor back into the model on every change so the
                    // required-description check (missingFields) stays reactive.
                    window.initTinyMDE(descEl, function() {
                        if (descEl._easyMDE) self.event.description = descEl._easyMDE.value();
                    });
                }

                // Restore a saved draft only once the widgets exist (the date goes through
                // Flatpickr, the description through EasyMDE).
                self.restoreDraft();
            };
            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', initWidgets);
            } else {
                initWidgets();
            }
        },

        watch: {
            accountMode(mode) {
                if (mode === 'login') {
                    this.turnstileToken = '';
                    this.turnstileWidgetId = null;
                } else {
                    this.$nextTick(() => this.maybeRenderTurnstile());
                }
            },

            // Flatpickr's visible altInput is a DOM clone outside Vue's reach, so the
            // error ring can't be bound with :class - mirror it imperatively.
            dateHasError(hasError) {
                var el = this.datePicker && this.datePicker.altInput;
                if (!el) return;
                el.classList.toggle('ring-1', hasError);
                el.classList.toggle('ring-red-500', hasError);
                el.classList.toggle('border-red-500', hasError);
            },

            event: {
                deep: true,
                handler() { this.queueDraftSave(); },
            },
            showMoreDetails() { this.queueDraftSave(); },
        },

        computed: {
            timezoneMismatch() {
                return !! (this.scheduleTimezone && this.deviceTimezone
                    && this.deviceTimezone !== this.scheduleTimezone);
            },

            timezoneMismatchMessage() {
                return this.timezoneMismatchText
                    .replace(':from', this.deviceTimezone)
                    .replace(':to', this.scheduleTimezone);
            },

            needsPageName() {
                if (this.accountMode === 'login') return false;
                return this.talents.length === 0;
            },

            showTalentPicker() {
                return this.isAuthed && this.talents.length > 1;
            },

            postingAsName() {
                return (this.isAuthed && this.talents.length === 1) ? this.talents[0].name : null;
            },

            // Single source of truth for what still blocks submission: one flag per field.
            // Powers the per-field error styling (errClass), the "Still needed" hint, the
            // scroll-to-first-missing behavior, and canSubmit, so they can never disagree.
            // The page name is NOT required - the server defaults it to the account name.
            missingMap() {
                const e = this.event;
                const m = {
                    name: !e.name || !e.name.trim(),
                    event_date: !e.event_date,
                    event_start_time: !e.event_start_time,
                    event_url: e.is_online && !(e.event_url || '').trim(),
                    venue_name: !e.is_online && !((e.venue_name || '').trim() || (e.venue_address1 || '').trim() || (e.venue_city || '').trim()),
                    // Curator-configured required submission fields (mirrored server-side).
                    short_description: !!this.requiredFields.short_description && !(e.short_description || '').trim(),
                    description: !!this.requiredFields.description && !(e.description || '').trim(),
                    ticket_price: !!this.requiredFields.ticket_price && !e.ticket_price,
                    registration_url: !!this.requiredFields.registration_url && !(e.registration_url || '').trim(),
                    coupon_code: !!this.requiredFields.coupon_code && !(e.coupon_code || '').trim(),
                    category_id: !!this.requiredFields.category_id && !e.category_id,
                    group_id: !!this.requiredFields.group_id && this.groups.length > 0 && !e.group_id,
                    account_email: false,
                    account_name: false,
                    account_password: false,
                    verification_code: false,
                    terms: false,
                };

                if (!this.isAuthed) {
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    m.account_email = !emailRegex.test((this.userEmail || '').trim());

                    if (this.accountMode === 'login') {
                        m.account_password = !this.userPassword;
                    } else {
                        m.account_name = !this.userName.trim();
                        // min:8 mirrors the server rule so a short password fails here,
                        // not after the code round-trip.
                        m.account_password = !this.userPassword || this.userPassword.length < 8;
                        m.verification_code = this.requiresCode && this.verificationCode.trim().length !== 6;
                        m.terms = !this.acceptedTerms;
                    }
                }

                return m;
            },

            // [field key, label, element id] in visual order; single source for the hint
            // text and the scroll-to-first-missing target.
            missingFieldOrder() {
                return [
                    ['name', @json(__('messages.event_name')), 'submit_event_name'],
                    ['event_date', @json(__('messages.date')), 'submit_event_date'],
                    ['event_start_time', @json(__('messages.start_time')), 'submit_event_time'],
                    ['event_url', @json(__('messages.event_url')), 'submit_event_url'],
                    ['venue_name', @json(__('messages.venue_name')), 'submit_venue_name'],
                    ['short_description', @json(__('messages.short_description')), 'submit_short_description'],
                    ['description', @json(__('messages.description')), 'submit_description'],
                    ['ticket_price', @json(__('messages.price')), 'submit_ticket_price'],
                    ['registration_url', @json(__('messages.registration_url')), 'submit_registration_url'],
                    ['coupon_code', @json(__('messages.coupon_code')), 'submit_coupon_code'],
                    ['category_id', @json(__('messages.category')), 'submit_category_id'],
                    ['group_id', @json(__('messages.schedule')), 'submit_group_id'],
                    ['account_email', @json(__('messages.email')), 'account_email'],
                    ['account_name', @json(__('messages.name')), 'account_name'],
                    ['account_password', @json(__('messages.password')), this.accountMode === 'login' ? 'login_password' : 'account_password'],
                    ['verification_code', @json(__('messages.verification_code')), this.codeSent ? 'verification_code' : 'send_code_button'],
                    ['terms', @json(__('messages.terms_of_service')), 'account_terms_row'],
                ];
            },

            missingFields() {
                return this.missingFieldOrder
                    .filter((f) => this.missingMap[f[0]])
                    .map((f) => f[1]);
            },

            dateHasError() {
                return this.triedSubmit && this.missingMap.event_date;
            },

            // End time is optional; when set, it becomes the duration (hours, float) the
            // backend already persists. Crossing midnight rolls to the next day, matching
            // the AP event form.
            computedDurationHours() {
                if (!this.event.event_start_time || !this.event.event_end_time) return null;
                const start = this.event.event_start_time.split(':');
                const end = this.event.event_end_time.split(':');
                let diff = (parseInt(end[0], 10) * 60 + parseInt(end[1], 10)) - (parseInt(start[0], 10) * 60 + parseInt(start[1], 10));
                if (diff < 0) diff += 1440;
                if (diff === 0) return null;
                return Math.round((diff / 60) * 100) / 100;
            },

            canSubmit() {
                return this.missingFields.length === 0 && !this.emailChecking;
            },
        },

        methods: {
            generateTimeOptions() {
                var options = [];
                var use24hr = {{ $use24hr ? 'true' : 'false' }};
                for (var m = 0; m < 1440; m += 30) {
                    var h = Math.floor(m / 60);
                    var min = m % 60;
                    var value = (h < 10 ? '0' : '') + h + ':' + (min < 10 ? '0' : '') + min;
                    var label;
                    if (use24hr) {
                        label = value;
                    } else {
                        var period = h < 12 ? 'AM' : 'PM';
                        var h12 = h % 12 || 12;
                        label = h12 + ':' + (min < 10 ? '0' : '') + min + ' ' + period;
                    }
                    options.push({ value: value, label: label });
                }
                return options;
            },

            // Ring-only error styling (no border-width change) so borderless wrappers like
            // the terms row and the EasyMDE container don't shift layout.
            errClass(key) {
                return (this.triedSubmit && this.missingMap[key]) ? 'ring-1 ring-red-500 border-red-500' : '';
            },

            scrollToFirstMissing() {
                const detailFields = ['short_description', 'description', 'ticket_price', 'registration_url', 'coupon_code', 'category_id', 'group_id'];
                for (const [key, , id] of this.missingFieldOrder) {
                    if (!this.missingMap[key]) continue;
                    if (detailFields.includes(key) && !this.showMoreDetails) {
                        this.showMoreDetails = true;
                    }
                    this.$nextTick(() => {
                        let el = document.getElementById(id);
                        if (!el) return;
                        // The date input is hidden behind Flatpickr's visible altInput clone.
                        if (id === 'submit_event_date' && this.datePicker && this.datePicker.altInput) {
                            el = this.datePicker.altInput;
                        }
                        // The description textarea is hidden behind EasyMDE; scroll its wrapper.
                        if (id === 'submit_description' && el._easyMDE && el.parentElement) {
                            el = el.parentElement;
                        }
                        el.scrollIntoView({ block: 'center', behavior: 'smooth' });
                        if (['INPUT', 'SELECT', 'TEXTAREA', 'BUTTON'].includes(el.tagName)) {
                            try { el.focus({ preventScroll: true }); } catch (e) { /* older browsers */ }
                        }
                    });
                    return;
                }
            },

            async checkEmailExists() {
                const email = (this.userEmail || '').trim();
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (this.isAuthed || !emailRegex.test(email)) {
                    return;
                }
                // Two blur-triggered checks can resolve out of order; only the latest
                // request may update the form (else a slow response for a corrected email
                // flips the mode back to a stale address).
                const seq = ++this.emailCheckSeq;
                this.emailChecking = true;
                this.accountError = null;
                try {
                    const response = await fetch('{{ route("event.check_email", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ email })
                    });
                    if (seq !== this.emailCheckSeq) {
                        return;
                    }
                    if (response.ok) {
                        const data = await response.json();
                        this.emailExists = data.exists;
                        this.emailStub = data.stub;
                        if (data.exists && !data.stub) {
                            this.accountMode = 'login';
                            this.codeSent = false;
                        } else {
                            this.accountMode = 'register';
                        }
                    }
                } catch (e) {
                    // Non-blocking: fall back to register mode on a failed check.
                } finally {
                    if (seq === this.emailCheckSeq) {
                        this.emailChecking = false;
                        // A submit that landed mid-check resumes instead of dead-clicking.
                        if (this.pendingSubmit) {
                            this.pendingSubmit = false;
                            this.submitEvent();
                        }
                    }
                }
            },

            useAnotherEmail() {
                this.accountMode = 'register';
                this.emailExists = null;
                this.codeSent = false;
                this.verificationCode = '';
                this.userPassword = '';
                this.$nextTick(() => {
                    const el = document.getElementById('account_email');
                    if (el) el.focus();
                });
            },

            maybeRenderTurnstile() {
                if (!this.turnstileEnabled || !this.turnstileSiteKey) return;
                if (this.isAuthed || this.accountMode !== 'register') return;
                this.renderImportTurnstile();
            },

            renderImportTurnstile() {
                const el = document.getElementById('turnstile-import-widget');
                if (!el || el.childElementCount > 0) return;
                if (typeof turnstile === 'undefined') {
                    setTimeout(() => this.renderImportTurnstile(), 200);
                    return;
                }
                this.turnstileWidgetId = turnstile.render('#turnstile-import-widget', {
                    sitekey: this.turnstileSiteKey,
                    callback: (token) => { this.turnstileToken = token; },
                    'expired-callback': () => { this.turnstileToken = ''; },
                    'error-callback': () => { this.turnstileToken = ''; },
                });
            },

            resetTurnstile() {
                this.turnstileToken = '';
                if (this.turnstileWidgetId !== null && typeof turnstile !== 'undefined') {
                    turnstile.reset(this.turnstileWidgetId);
                }
            },

            async sendCode() {
                const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                if (!emailRegex.test((this.userEmail || '').trim())) {
                    this.accountError = @json(__('messages.please_enter_valid_email'));
                    return;
                }
                this.codeSending = true;
                this.accountError = null;
                try {
                    const response = await fetch('{{ route("event.guest_send_code", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ email: this.userEmail.trim(), website: this.honeypot, 'cf-turnstile-response': this.turnstileToken })
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || data.success === false) {
                        this.accountError = data.message || @json(__('messages.error'));
                        return;
                    }
                    this.codeSent = true;
                    this.startResendCountdown();
                    this.$nextTick(() => {
                        const el = document.getElementById('verification_code');
                        if (el) el.focus();
                    });
                } catch (e) {
                    this.accountError = @json(__('messages.error'));
                } finally {
                    this.codeSending = false;
                    this.resetTurnstile();
                }
            },

            startResendCountdown() {
                this.resendCountdown = 30;
                if (this.resendTimer) clearInterval(this.resendTimer);
                this.resendTimer = setInterval(() => {
                    this.resendCountdown--;
                    if (this.resendCountdown <= 0) {
                        clearInterval(this.resendTimer);
                        this.resendTimer = null;
                    }
                }, 1000);
            },

            onCodeInput(event) {
                this.verificationCode = (event.target.value || '').replace(/\D/g, '').slice(0, 6);
                // When sanitizing leaves the model unchanged Vue skips the DOM patch, so a
                // rejected character would stay visible; write the clean value back.
                event.target.value = this.verificationCode;
            },

            onImageSelected(e) {
                const file = e.target.files && e.target.files[0];
                if (file) this.uploadImage(file);
            },

            async uploadImage(file) {
                if (!file.type.startsWith('image/')) {
                    this.errorMessage = @json(__('messages.invalid_image_type'));
                    return;
                }
                const maxSize = 2.5 * 1024 * 1024;
                if (file.size > maxSize) {
                    this.errorMessage = @json(__('messages.image_size_warning'));
                    return;
                }
                this.isUploadingImage = true;
                this.errorMessage = null;
                try {
                    const formData = new FormData();
                    formData.append('image', file);
                    const response = await fetch('{{ route("event.guest_upload_image", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: formData
                    });
                    if (!response.ok) throw new Error('Request failed');
                    const data = await response.json();
                    if (data.success && data.filename) {
                        this.event.social_image = data.filename;
                        // Thumbnail from the local file - the temp upload has no public URL.
                        const reader = new FileReader();
                        reader.onload = (ev) => { this.eventImageUrl = ev.target.result; };
                        reader.readAsDataURL(file);
                    } else {
                        throw new Error(data.message || @json(__('messages.error_uploading_image')));
                    }
                } catch (error) {
                    this.errorMessage = error.message || @json(__('messages.error_uploading_image'));
                } finally {
                    this.isUploadingImage = false;
                }
            },

            removeEventImage() {
                this.event.social_image = null;
                this.eventImageUrl = null;
            },

            onAutoFillImageSelected(e) {
                const file = e.target.files && e.target.files[0];
                e.target.value = '';
                if (file) this.setAutoFillImage(file);
            },

            // dragenter/dragleave fire for every child crossed, so track depth instead of
            // a boolean (the classic counter pattern; the overlay is pointer-events-none).
            autoFillDragEnter() {
                this.autoFillDragDepth++;
                this.autoFillDragging = true;
            },

            autoFillDragLeave() {
                this.autoFillDragDepth = Math.max(0, this.autoFillDragDepth - 1);
                if (this.autoFillDragDepth === 0) this.autoFillDragging = false;
            },

            autoFillDrop(e) {
                this.autoFillDragDepth = 0;
                this.autoFillDragging = false;
                const file = e.dataTransfer && e.dataTransfer.files && e.dataTransfer.files[0];
                if (file) {
                    this.setAutoFillImage(file);
                    return;
                }
                const text = e.dataTransfer && e.dataTransfer.getData('text');
                if (text) this.autoFillText = text;
            },

            onAutoFillPaste(e) {
                const items = (e.clipboardData && e.clipboardData.items) || [];
                for (const item of items) {
                    if (item.kind === 'file' && item.type.startsWith('image/')) {
                        const file = item.getAsFile();
                        if (file) {
                            e.preventDefault();
                            this.setAutoFillImage(file);
                            return;
                        }
                    }
                }
                // Plain text: let the default paste land in the textarea (v-model syncs).
            },

            // An image is a complete input on its own, so it parses immediately - no
            // second click. Pasted text still goes through the Auto Fill button.
            setAutoFillImage(file) {
                if (!file.type.startsWith('image/')) {
                    this.autoFillError = @json(__('messages.invalid_image_type'));
                    return;
                }
                const maxSize = 2.5 * 1024 * 1024;
                if (file.size > maxSize) {
                    this.autoFillError = @json(__('messages.image_size_warning'));
                    return;
                }
                this.autoFillError = null;
                this.autoFillImage = file;
                this.autoFillImageUrl = null;
                const reader = new FileReader();
                reader.onload = (ev) => { this.autoFillImageUrl = ev.target.result; };
                reader.readAsDataURL(file);
                if (!this.parsing) this.runAutoFill();
            },

            clearAutoFillImage() {
                this.autoFillImage = null;
                this.autoFillImageUrl = null;
                this.autoFillError = null;
            },

            reopenAutoFill() {
                this.autoFillDone = false;
            },

            async runAutoFill() {
                if (!this.autoFillText.trim() && !this.autoFillImage) return;
                this.parsing = true;
                this.accountError = null;
                this.autoFillError = null;
                try {
                    const formData = new FormData();
                    formData.append('event_details', this.autoFillText);
                    if (this.autoFillImage) {
                        formData.append('details_image', this.autoFillImage);
                    }
                    const response = await fetch('{{ route("event.guest_parse", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' },
                        body: formData
                    });
                    let data = (await response.json().catch(() => null)) || {};
                    // On success the parse endpoint returns the parsed events as a bare array.
                    if (Array.isArray(data)) {
                        data = { parsed: data };
                    }
                    const p = data.parsed && data.parsed[0];
                    if (p) {
                        if (p.event_name) this.event.name = p.event_name;
                        if (p.event_name_en) this.event.name_en = p.event_name_en;

                        // The parser returns one combined 'YYYY-MM-DD HH:MM' stamp; split it
                        // like the AI-import page does (parsed in browser-local time).
                        var parsedDate = '';
                        var parsedMinutes = null;
                        if (p.event_date_time) {
                            var dt = new Date(String(p.event_date_time).replace(' ', 'T'));
                            if (!isNaN(dt.getTime())) {
                                parsedDate = dt.getFullYear() + '-' + ('0' + (dt.getMonth() + 1)).slice(-2) + '-' + ('0' + dt.getDate()).slice(-2);
                                parsedMinutes = dt.getHours() * 60 + dt.getMinutes();
                            }
                        }

                        // Only accept a parsed date the picker will display (minDate: today).
                        // Compare against the browser-LOCAL date - Flatpickr's 'today' is local,
                        // so a UTC comparison would disagree with the visible field near midnight.
                        var now = new Date();
                        var todayLocal = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
                        if (parsedDate && parsedDate >= todayLocal) {
                            this.event.event_date = parsedDate;
                            if (this.datePicker) this.datePicker.setDate(parsedDate, false);
                        }
                        if (parsedMinutes !== null) {
                            // Snap to the select's 30-minute grid so the applied time is one the
                            // dropdown can actually display.
                            var snapped = (Math.round(parsedMinutes / 30) * 30) % 1440;
                            var sh = Math.floor(snapped / 60);
                            var sm = snapped % 60;
                            this.event.event_start_time = (sh < 10 ? '0' : '') + sh + ':' + (sm < 10 ? '0' : '') + sm;
                        }
                        // A parsed duration becomes a visible end time (needs a start to
                        // anchor to; snapped to the same 30-minute grid).
                        if (p.event_duration && this.event.event_start_time) {
                            var stParts = this.event.event_start_time.split(':');
                            var stMin = parseInt(stParts[0], 10) * 60 + parseInt(stParts[1], 10);
                            var enMin = (Math.round((stMin + p.event_duration * 60) / 30) * 30) % 1440;
                            var eh = Math.floor(enMin / 60);
                            var em = enMin % 60;
                            this.event.event_end_time = (eh < 10 ? '0' : '') + eh + ':' + (em < 10 ? '0' : '') + em;
                        }

                        if (p.venue_name) this.event.venue_name = p.venue_name;
                        if (p.venue_name_en) this.event.venue_name_en = p.venue_name_en;
                        if (p.event_address) this.event.venue_address1 = p.event_address;
                        if (p.event_city) this.event.venue_city = p.event_city;
                        if (p.event_state) this.event.venue_state = p.event_state;
                        if (p.event_postal_code) this.event.venue_postal_code = p.event_postal_code;
                        if (p.event_country_code) this.event.venue_country_code = p.event_country_code;
                        if (p.event_details) {
                            this.event.description = p.event_details;
                            var descNode = document.getElementById('submit_description');
                            if (descNode && descNode._easyMDE) descNode._easyMDE.value(p.event_details);
                        }
                        if (p.ticket_price) this.event.ticket_price = p.ticket_price;
                        if (p.ticket_currency_code && this.currencies.some(c => c.value === p.ticket_currency_code)) {
                            this.event.ticket_currency_code = p.ticket_currency_code;
                        }
                        if (p.registration_url) this.event.registration_url = p.registration_url;
                        if (p.short_description) this.event.short_description = p.short_description;
                        if (p.short_description_en) this.event.short_description_en = p.short_description_en;
                        if (p.category_id && this.categoryIds.includes(String(p.category_id))) {
                            this.event.category_id = String(p.category_id);
                        }
                        if (p.group_id && this.groups.some(g => g.id === p.group_id)) {
                            this.event.group_id = p.group_id;
                        }
                        // A parsed flyer image doubles as the event image (the endpoint stores
                        // it and returns the temp filename the save endpoint accepts).
                        if (p.social_image) {
                            this.event.social_image = p.social_image;
                            // The uploaded flyer is the only client-side copy of the image.
                            this.eventImageUrl = this.autoFillImageUrl;
                        }
                        // Reveal the optional section when the parser filled any of its fields
                        if (p.event_details || p.ticket_price || p.registration_url || p.short_description || p.social_image) {
                            this.showMoreDetails = true;
                        }
                        this.autoFillDone = true;
                    } else {
                        // The parse endpoint reports failures (daily limit, parse error) under `error`
                        this.autoFillError = data.error || data.message || @json(__('messages.error'));
                    }
                } catch (e) {
                    this.autoFillError = @json(__('messages.error'));
                } finally {
                    this.parsing = false;
                }
            },

            async submitEvent() {
                if (this.saving || this.isUploadingImage) return;
                if (this.emailChecking) {
                    // Resumed from checkEmailExists' finally block once the check lands.
                    this.pendingSubmit = true;
                    return;
                }

                // Pull the description out of EasyMDE before validating (its change callback
                // usually keeps the model current, but be safe on direct submits).
                var descNode = document.getElementById('submit_description');
                if (descNode && descNode._easyMDE) {
                    this.event.description = descNode._easyMDE.value();
                }

                // The button is always enabled; an incomplete submit highlights what's
                // missing and jumps there instead of silently doing nothing.
                if (!this.canSubmit) {
                    this.triedSubmit = true;
                    this.scrollToFirstMissing();
                    return;
                }

                this.saving = true;
                this.accountError = null;
                this.errorMessage = null;

                var startsAt = null;
                if (this.event.event_date && this.event.event_start_time) {
                    startsAt = this.event.event_date + ' ' + this.event.event_start_time + ':00';
                }

                var body = {
                    name: this.event.name,
                    starts_at: startsAt,
                    duration: this.computedDurationHours,
                    description: this.event.description,
                    social_image: this.event.social_image,
                    registration_url: this.event.registration_url,
                    ticket_price: this.event.ticket_price,
                    ticket_currency_code: this.event.ticket_currency_code,
                    short_description: this.event.short_description,
                    name_en: this.event.name_en || null,
                    short_description_en: this.event.short_description_en || null,
                    coupon_code: this.event.coupon_code,
                    category_id: this.event.category_id || null,
                    curator_group_id: this.event.group_id || null,
                    account_mode: this.accountMode,
                    account_email: this.userEmail,
                    account_password: this.userPassword,
                    selected_talent_id: this.selectedTalentId || null,
                    website: this.honeypot,
                };

                if (this.event.is_online) {
                    body.event_url = this.event.event_url;
                } else {
                    body.venue_name = this.event.venue_name;
                    body.venue_name_en = this.event.venue_name_en || null;
                    body.venue_address1 = this.event.venue_address1;
                    body.venue_city = this.event.venue_city;
                    body.venue_state = this.event.venue_state;
                    body.venue_postal_code = this.event.venue_postal_code;
                    body.venue_country_code = this.event.venue_country_code;
                }

                if (this.accountMode === 'register') {
                    body.account_name = this.userName;
                    body.schedule_name = this.scheduleName;
                    body.verification_code = this.verificationCode;
                    // The new account should get the guest's timezone, not the curator's
                    // (the server falls back to the schedule's timezone without it).
                    try {
                        body.timezone = Intl.DateTimeFormat().resolvedOptions().timeZone || null;
                    } catch (e) { /* very old browsers */ }
                }

                try {
                    const response = await fetch('{{ route("event.guest_import.store", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        },
                        body: JSON.stringify(body),
                    });
                    const data = await response.json().catch(() => ({}));
                    if (!response.ok || !data.success) {
                        let msg = data.message || @json(__('messages.error'));
                        if (data.errors) {
                            const first = Object.values(data.errors)[0];
                            if (Array.isArray(first) && first[0]) msg = first[0];
                        }
                        if (response.status === 429) msg = @json(__('messages.too_many_attempts'));
                        this.accountError = msg;
                        this.$nextTick(() => {
                            var box = document.getElementById('submit-error-box');
                            if (box) box.scrollIntoView({ block: 'nearest', behavior: 'smooth' });
                        });
                        return;
                    }
                    this.submissionResult = data.event;
                    // Adopt the submitter's real schedules from the response so "Submit
                    // another" shows the actual picker/identity (a login-mode client has no
                    // other way to know them).
                    if (Array.isArray(data.talents) && data.talents.length) {
                        this.talents = data.talents;
                        this.selectedTalentId = data.posted_as || data.talents[0].id;
                    }
                    this.submitted = true;
                    this.clearDraftStorage();
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                    this.$nextTick(() => {
                        const h = document.getElementById('submission-success-heading');
                        if (h) h.focus();
                    });
                } catch (e) {
                    this.accountError = @json(__('messages.error'));
                } finally {
                    this.saving = false;
                }
            },

            // Shared by "Submit another" and the draft banner's "Start fresh".
            clearEventFields() {
                this.errorMessage = null;
                this.accountError = null;
                this.triedSubmit = false;
                this.showMoreDetails = {{ $hasRequiredImportFields ? 'true' : 'false' }};
                this.autoFillDone = false;
                this.autoFillText = '';
                this.autoFillImage = null;
                this.autoFillImageUrl = null;
                this.autoFillError = null;
                this.event.name = '';
                this.event.name_en = '';
                this.event.event_date = '';
                this.event.event_start_time = '';
                this.event.event_end_time = '';
                this.event.venue_name = '';
                this.event.venue_name_en = '';
                this.event.venue_address1 = '';
                this.event.venue_city = '';
                this.event.venue_state = '';
                this.event.venue_postal_code = '';
                this.event.event_url = '';
                this.event.description = '';
                this.event.social_image = null;
                this.eventImageUrl = null;
                this.event.ticket_price = '';
                this.event.registration_url = '';
                this.event.short_description = '';
                this.event.short_description_en = '';
                this.event.coupon_code = '';
                this.event.category_id = '';
                this.event.group_id = '';
                if (this.datePicker) this.datePicker.clear();
                var descNode = document.getElementById('submit_description');
                if (descNode && descNode._easyMDE) descNode._easyMDE.value('');
            },

            resetForAnother() {
                this.submitted = false;
                this.submissionResult = null;
                this.clearEventFields();
                this.clearDraftStorage();
                // The submitter is now authenticated; the submit response refreshed `talents`
                // with their real schedules, so the picker/identity line just work. Fabricate a
                // named stub only as a fallback (and never an empty-name one - that would hide
                // both the identity line and the picker for login-mode users).
                this.isAuthed = true;
                if (this.talents.length === 0) {
                    var fallbackName = (this.scheduleName || this.userName || '').trim();
                    if (fallbackName) {
                        this.talents = [{ id: '', name: fallbackName }];
                        this.selectedTalentId = '';
                    }
                }
                window.scrollTo({ top: 0, behavior: 'smooth' });
            },

            // --- Draft autosave -------------------------------------------------------
            // Best-effort recovery for guests who leave mid-form (e.g. switching to their
            // email app for the verification code on mobile). Event fields only.

            queueDraftSave() {
                if (this.submitted) return;
                clearTimeout(this.draftTimer);
                this.draftTimer = setTimeout(() => this.saveDraft(), 500);
            },

            saveDraft() {
                try {
                    const e = this.event;
                    const hasContent = !!((e.name || '').trim() || e.event_date || e.event_start_time
                        || (e.venue_name || '').trim() || (e.venue_address1 || '').trim() || (e.venue_city || '').trim()
                        || (e.event_url || '').trim() || (e.description || '').trim() || (e.short_description || '').trim()
                        || e.ticket_price || (e.registration_url || '').trim() || (e.coupon_code || '').trim());
                    if (!hasContent) {
                        // Clearing the form (Start fresh / Submit another) dissolves the draft.
                        localStorage.removeItem(this.draftKey);
                        return;
                    }
                    const draft = Object.assign({}, e);
                    // The temp upload may be gone by the time they return; never restore it.
                    delete draft.social_image;
                    localStorage.setItem(this.draftKey, JSON.stringify({
                        v: 1,
                        savedAt: Date.now(),
                        event: draft,
                        showMoreDetails: this.showMoreDetails,
                    }));
                } catch (err) {
                    // Storage full or blocked - drafts are best-effort.
                }
            },

            restoreDraft() {
                try {
                    const raw = localStorage.getItem(this.draftKey);
                    if (!raw) return;
                    const draft = JSON.parse(raw);
                    if (!draft || draft.v !== 1 || !draft.event) return;
                    const maxAge = 7 * 24 * 60 * 60 * 1000;
                    if (!draft.savedAt || (Date.now() - draft.savedAt) > maxAge) {
                        localStorage.removeItem(this.draftKey);
                        return;
                    }

                    const e = draft.event;
                    const fields = ['name', 'event_start_time', 'event_end_time', 'is_online', 'venue_name',
                        'venue_address1', 'venue_city', 'venue_state', 'venue_postal_code', 'venue_country_code',
                        'event_url', 'description', 'ticket_price', 'ticket_currency_code', 'registration_url',
                        'short_description', 'coupon_code'];
                    fields.forEach((f) => {
                        if (e[f] !== undefined && e[f] !== null && e[f] !== '') this.event[f] = e[f];
                    });
                    // Selects only accept known values (curator config may have changed).
                    if (e.category_id && this.categoryIds.includes(String(e.category_id))) this.event.category_id = String(e.category_id);
                    if (e.group_id && this.groups.some((g) => g.id === e.group_id)) this.event.group_id = e.group_id;
                    if (e.ticket_currency_code && !this.currencies.some((c) => c.value === e.ticket_currency_code)) {
                        this.event.ticket_currency_code = @json($defaultCurrency);
                    }
                    // Only restore a date the picker will display (minDate: today, local).
                    var now = new Date();
                    var todayLocal = now.getFullYear() + '-' + ('0' + (now.getMonth() + 1)).slice(-2) + '-' + ('0' + now.getDate()).slice(-2);
                    if (e.event_date && e.event_date >= todayLocal) {
                        this.event.event_date = e.event_date;
                        if (this.datePicker) this.datePicker.setDate(e.event_date, false);
                    }
                    if (this.event.description) {
                        var descNode = document.getElementById('submit_description');
                        if (descNode && descNode._easyMDE) descNode._easyMDE.value(this.event.description);
                    }
                    if (draft.showMoreDetails) this.showMoreDetails = true;
                    this.draftRestored = true;
                } catch (err) {
                    // Corrupt or blocked storage - start clean.
                }
            },

            clearDraftStorage() {
                clearTimeout(this.draftTimer);
                try {
                    localStorage.removeItem(this.draftKey);
                } catch (err) { /* storage blocked */ }
            },

            startFresh() {
                this.clearDraftStorage();
                this.draftRestored = false;
                this.clearEventFields();
            },
        },
    });

    window.__submitApp = app.mount('#event-submit-app');
  </script>

</x-app-guest-layout>
