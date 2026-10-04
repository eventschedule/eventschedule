<script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>

<style {!! nonce_attr() !!}>
    #event-import-app {
        visibility: hidden;
    }
    
    #event-import-app.loaded {
        visibility: visible;
    }

    /* Custom time picker dropdown */
    .time-dropdown-import {
        display: none;
        position: absolute;
        z-index: 50;
        width: 100%;
        max-height: 200px;
        overflow-y: auto;
        background: linear-gradient(to bottom, #ffffff, #fafafa);
        border: 1px solid rgba(0, 0, 0, 0.08);
        border-radius: 0.5rem;
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.08);
        margin-top: 2px;
    }
    .dark .time-dropdown-import {
        background: linear-gradient(to bottom, rgb(var(--ap-border)), rgb(var(--ap-rail-active)));
        border-color: rgba(255, 255, 255, 0.06);
        box-shadow: 0 10px 25px rgba(0, 0, 0, 0.4);
    }
    .time-dropdown-import.open {
        display: block;
    }
    .time-dropdown-item-import {
        padding: 6px 12px;
        cursor: pointer;
        font-size: 0.875rem;
        color: #111827;
        transition: all 0.15s ease;
    }
    .dark .time-dropdown-item-import {
        color: rgb(var(--ap-ink-2));
    }
    .time-dropdown-item-import:hover,
    .time-dropdown-item-import.highlighted {
        background: linear-gradient(to bottom, #f3f4f6, #eef0f3);
        color: #111827;
    }
    .dark .time-dropdown-item-import:hover,
    .dark .time-dropdown-item-import.highlighted {
        background: linear-gradient(to bottom, rgb(var(--ap-border-strong)), #383838);
        color: #fff;
    }
    .time-input-wrapper {
        position: relative;
    }
</style>

@php
    // The guest submission form shares this view. Everything about links is the editor's alone:
    // a signed-out visitor must never be able to make the server fetch an address.
    $isGuestPage = isset($isGuest) && $isGuest;
    $hasAi = (bool) (config('services.google.gemini_key') || config('services.openai.api_key'));
    // An editor on an install with no AI key still gets the box: a calendar link, or a page that
    // publishes its own event data, needs no model.
    $linksOnly = ! $hasAi && ! $isGuestPage;
@endphp
@if (! $hasAi && $isGuestPage)
<div class="ap-card p-4 sm:p-8 shadow-md rounded-lg">
    <div class="max-w-3xl mx-auto">
        <x-gemini-setup-guide />
    </div>
</div>
@else
<form method="post"
    action="{{ isset($isGuest) && $isGuest ? route('event.guest_import.store', ['subdomain' => $role->subdomain]) : route('event.import', ['subdomain' => $role->subdomain]) }}"
    enctype="multipart/form-data"
    id="event-import-app">

    @csrf

    {{-- Guests only: this page is shared with the authenticated event.import flow, whose
         payload must stay unchanged. The submit builds its JSON body by hand, so the value
         is bound into Vue and sent explicitly rather than picked up from the form. --}}
    @if (isset($isGuest) && $isGuest)
    <x-honeypot vmodel="honeypot" />
    @endif

        @php $use24hr = get_use_24_hour_time($role ?? null); @endphp

        <div v-if="!preview || !preview.parsed || preview.parsed.length === 0">
            <div class="ap-card p-4 sm:p-8 shadow-md rounded-lg">
            <div class="max-w-3xl mx-auto">
                <!-- Centered single column layout -->
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl md:max-w-xl lg:max-w-2xl">
                        <!-- Status header for guest users (curators that do NOT require an account) -->
                        @if (isset($isGuest) && $isGuest && ! auth()->check())
                        <div class="mb-4 p-3 bg-blue-50 dark:bg-blue-900/30 border border-blue-200 dark:border-blue-800 rounded-lg">
                            <p class="text-sm text-blue-800 dark:text-blue-200">
                                {!! __('messages.create_account_link', ['link' => '<a href="' . app_url('/sign_up') . '" class="font-medium underline hover:no-underline">' . __('messages.create_an_account') . '</a>']) !!}
                                <span class="text-sm text-gray-400 dark:text-gray-500 mx-1">|</span> <a href="{{ marketing_url('/why-create-account') }}{{ request()->has('lang') && is_valid_language_code(request()->get('lang')) ? '?lang=' . request()->get('lang') : '' }}" target="_blank" class="text-blue-600 dark:text-blue-300 underline hover:no-underline">{{ __('messages.why_create_account_learn_more') }} <svg class="inline-block w-3 h-3 ml-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></a>
                            </p>
                        </div>
                        @endif

                        {{-- Art. 13: what is pasted or dropped here goes to the AI provider. --}}
                        {{-- An editor's notice also covers a pasted link. With no AI on this install
                             nothing here reaches an AI service, so there is nothing to disclose. --}}
                        @if ($isGuestPage)
                        <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.ai_processing_notice') }}</p>
                        @elseif (! $linksOnly)
                        <p class="mb-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.ai_processing_notice_links') }}</p>
                        @endif

                        <!-- Combined textarea and image section -->
                        <div class="mb-1">
                            <div class="relative">
                                <textarea id="event_details" 
                                    ref="eventDetails"
                                    name="event_details" 
                                    rows="7"
                                    v-model="eventDetails"
                                    v-bind:readonly="savedEvent"
                                    @input="handleInputChange"
                                    @paste="handlePaste" 
                                    @keydown="handleKeydown"
                                    @dragenter.prevent="dragEnterDetails"
                                    @dragover.prevent="dragOverDetails"
                                    @dragleave.prevent="dragLeaveDetails"
                                    @drop.prevent="handleDetailsImageDrop"
                                    @dragend="dragEndDetails"
                                    autofocus
                                    :class="['mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm transition-all duration-200', 
                                        isDraggingDetails ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30 ring-2 ring-blue-200 dark:ring-blue-800' : '']"
                                    dir="auto"
                                    placeholder="{{ $isGuestPage ? __('messages.drag_drop_image_or_type_text') : ($linksOnly ? __('messages.import_box_placeholder_links') : __('messages.import_box_placeholder')) }}"></textarea>
                                
                                <!-- Drop message overlay for textarea -->
                                <div v-show="isDraggingDetails" 
                                     @dragenter.prevent="dragEnterDetails"
                                     @dragover.prevent="dragOverDetails"
                                     @dragleave.prevent="dragLeaveDetails"
                                     @drop.prevent="handleDetailsImageDrop"
                                     @dragend="dragEndDetails"
                                     class="absolute inset-0 flex items-center justify-center bg-blue-50 dark:bg-blue-900/30 border-2 border-blue-500 rounded-lg z-10 transition-all duration-200 ease-in-out"
                                     :class="{ 'opacity-100 scale-100': isDraggingDetails, 'opacity-0 scale-95': !isDraggingDetails }">
                                    <div class="text-center">
                                        <svg class="mx-auto h-8 w-8 text-blue-500 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.172 7l-6.586 6.586a2 2 0 102.828 2.828l6.414-6.586a4 4 0 00-5.656-5.656l-6.415 6.585a6 6 0 108.486 8.486L20.5 13" />
                                        </svg>
                                        <p class="text-blue-700 dark:text-blue-300 font-medium">{{ __('messages.drop_files_here') }}</p>
                                    </div>
                                </div>
                                
                                <!-- Image preview overlay -->
                                <div v-if="detailsImage" 
                                     :class="['absolute bottom-3 w-16 h-16 rounded-lg overflow-hidden border-2 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 shadow-sm', 
                                         'start-3']">
                                    <img v-if="detailsImageUrl" 
                                         :src="detailsImageUrl" 
                                         class="object-cover w-full h-full" 
                                         alt="Event details image preview">
                                    <div v-else class="flex items-center justify-center h-full text-gray-400">
                                        <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                            <path d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                    </div>
                                    
                                    <!-- Remove image button -->
                                    <button
                                        @click="removeDetailsImage"
                                        type="button"
                                        style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                        :class="['absolute -top-1 -end-1 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center text-xs transition-all duration-200 hover:scale-105']"
                                        title="{{ __('messages.remove_image') }}">
                                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                    </button>
                                </div>
                                
                                {{-- The two buttons sit together at the box's end. An editor's carry their label:
                                     an icon alone is a guess on a phone, where there is no hover to explain it. --}}
                                <div class="absolute bottom-3 end-5 flex items-center gap-3">
                                @unless ($linksOnly)
                                <!-- Plus icon button for file picker -->
                                <button 
                                    type="button"
                                    @click="openDetailsFileSelector"
                                    :disabled="isLoading || detailsImage"
                                    :class="['inline-flex items-center gap-1.5 p-2 rounded-lg transition-all duration-200 shadow-md {{ $isGuestPage ? '' : 'min-h-[44px]' }}', 
                                        (isLoading || detailsImage)
                                            ? 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed border border-gray-400 dark:border-gray-500'
                                            : '{{ (isset($isGuest) && $isGuest) ? 'bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 hover:from-blue-600 hover:via-blue-700 hover:to-blue-800 text-white cursor-pointer border border-blue-400/30 hover:border-blue-300/50 shadow-lg hover:shadow-xl' : 'bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] text-white cursor-pointer border border-[var(--brand-blue-a30)] shadow-lg hover:shadow-xl' }}']"
                                    title="{{ __('messages.add_image') }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                                    </svg>
                                    @unless ($isGuestPage)
                                    <span class="pe-1 text-sm font-medium">{{ __('messages.add_image') }}</span>
                                    @endunless
                                </button>
                                @endunless

                                <!-- Submit button with up arrow -->
                                <button 
                                    type="button"
                                    @click="handleSubmit"
                                    :disabled="!canSubmit || isLoading"
                                    :class="['inline-flex items-center gap-1.5 p-2 rounded-lg transition-all duration-200 shadow-md {{ $isGuestPage ? '' : 'min-h-[44px]' }}', 
                                        canSubmit && !isLoading
                                            ? '{{ (isset($isGuest) && $isGuest) ? 'bg-gradient-to-br from-blue-500 via-blue-600 to-blue-700 hover:from-blue-600 hover:via-blue-700 hover:to-blue-800 text-white cursor-pointer border border-blue-400/30 hover:border-blue-300/50 shadow-lg hover:shadow-xl' : 'bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)] text-white cursor-pointer border border-[var(--brand-blue-a30)] shadow-lg hover:shadow-xl' }}'
                                            : 'bg-gray-300 dark:bg-gray-600 text-gray-500 dark:text-gray-400 cursor-not-allowed border border-gray-400 dark:border-gray-500']"
                                    title="{{ __('messages.submit') }}">
                                    @unless ($isGuestPage)
                                    {{-- Says what pressing it will do with what is in the box. --}}
                                    <span class="ps-1 text-sm font-semibold" v-text="submitLabel"></span>
                                    @endunless
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18" />
                                    </svg>
                                </button>
                                </div>
                            </div>

                            @unless ($isGuestPage)
                            {{-- The box holds a link and nothing else: say so, and name where it goes. --}}
                            <p v-if="isLink && !errorMessage" v-cloak class="mt-2 inline-flex max-w-full items-center gap-2 rounded-lg bg-blue-50 px-3 py-1.5 text-sm text-blue-800 dark:bg-blue-500/10 dark:text-blue-200">
                                <svg class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 0 1 1.242 7.244l-4.5 4.5a4.5 4.5 0 0 1-6.364-6.364l1.757-1.757m13.35-.622 1.757-1.757a4.5 4.5 0 0 0-6.364-6.364l-4.5 4.5a4.5 4.5 0 0 0 1.242 7.244" />
                                </svg>
                                <span dir="ltr" class="truncate font-medium" v-text="linkLabel"></span>
                                <span class="flex-shrink-0 opacity-80">{{ __('messages.import_link_detected') }}</span>
                            </p>
                            <p v-else-if="!eventDetails.trim() && !detailsImage && !isLoading && !errorMessage" v-cloak class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                {{ $linksOnly ? __('messages.import_box_examples_links') : __('messages.import_box_examples') }}
                            </p>
                            @endunless
                            <x-input-error class="mt-2" :messages="$errors->get('event_details')" />

                            @if ($isGuestPage)
                            <!-- Error message display -->
                            <div v-if="errorMessage" class="mt-4 p-3 text-sm text-red-600 dark:text-red-400 bg-red-100 dark:bg-red-900/30 rounded-lg">
                                @{{ errorMessage }}
                            </div>

                            <div v-if="isLoading" class="mt-4 flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400">
                                <div class="relative">
                                    <div class="w-4 h-4 rounded-full bg-blue-500/30"></div>
                                    <div class="absolute top-0 left-0 w-4 h-4 rounded-full border-2 border-blue-500 border-t-transparent animate-spin"></div>
                                </div>
                                <div class="inline-flex items-center">
                                    <span class="animate-pulse">
                                        {{ __('messages.loading') }}
                                    </span>
                                    <span class="ms-1 inline-flex animate-[ellipsis_1.5s_steps(4,end)_infinite]">...</span>
                                </div>
                            </div>
                            @else
                            {{-- What went wrong and what to do instead, in place and until the next try.
                                 What the person typed stays in the box. --}}
                            <div v-if="errorMessage" v-cloak role="alert" class="mt-4 flex items-start gap-3 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
                                <svg class="h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                </svg>
                                <p class="whitespace-pre-line text-sm text-amber-800 dark:text-amber-200" v-text="errorMessage"></p>
                            </div>

                            {{-- A read can take a minute. Say what is being read, show the shape of what
                                 is coming, and leave a way out. --}}
                            <div v-if="isLoading" v-cloak class="mt-4">
                                <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-400" role="status" aria-live="polite">
                                    <span class="relative flex-shrink-0">
                                        <span class="block h-4 w-4 rounded-full bg-blue-500/30"></span>
                                        <span class="absolute start-0 top-0 block h-4 w-4 rounded-full border-2 border-blue-500 border-t-transparent motion-safe:animate-spin"></span>
                                    </span>
                                    <span class="min-w-0 flex-1 truncate" v-text="readingLabel"></span>
                                    <button type="button" @click="cancelReading" class="flex-shrink-0 rounded-md px-2 py-1 font-medium text-gray-700 hover:bg-gray-100 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] dark:text-gray-300 dark:hover:bg-gray-700">
                                        {{ __('messages.cancel') }}
                                    </button>
                                </div>
                                <p v-if="isSlow" class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.import_reading_slow') }}</p>
                                <div class="mt-4 space-y-3" aria-hidden="true">
                                    @foreach ([70, 55, 80, 60, 45] as $width)
                                    <div class="flex items-center gap-3">
                                        <span class="h-10 w-10 flex-shrink-0 rounded-lg bg-gray-200 motion-safe:animate-pulse dark:bg-gray-700"></span>
                                        <span class="h-4 rounded bg-gray-200 motion-safe:animate-pulse dark:bg-gray-700" style="width: {{ $width }}%"></span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            @if ($linksOnly)
                            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.import_ai_optional') }}</p>
                            @endif
                            @endif
                        </div>
                    </div>
                </div>

            </div>
            </div>

            @unless ($isGuestPage)
            {{-- Where else events can come from. After a failed read it is the way out, so it says so. --}}
            <div class="mt-4">
                <h3 v-if="errorMessage" v-cloak class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">{{ __('messages.import_try_another_way') }}</h3>
                <h3 v-else class="mb-2 text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.import_other_sources') }}</h3>
                @include('event.partials.import-other-sources')
            </div>

            @if ($linksOnly && auth()->user()?->isAdmin())
            {{-- Only whoever runs the install can act on this, and nothing above needs it. --}}
            <div class="mt-4">
                <x-gemini-setup-guide :optional="true" />
            </div>
            @endif
            @endunless
        </div>

        {{-- The block above used to be left open here, one closing tag short, so everything down
             to a stray closing tag after the Save All card sat inside it and vanished with it the
             moment a preview appeared: "Show all fields" and "Save All" were never on screen. The
             terms panel keeps the condition it had by that accident. --}}
        @if(isset($isGuest) && $isGuest && $role->accept_requests && $role->request_terms)
        <!-- Request Terms Panel -->
        <div v-if="!preview || !preview.parsed || preview.parsed.length === 0" class="ap-card p-4 sm:p-8 shadow-md rounded-lg mb-4 mt-4">
            <div class="max-w-3xl mx-auto">
                <div class="flex justify-center">
                    <div class="w-full max-w-2xl md:max-w-xl lg:max-w-2xl">
                        <div class="flex items-start">
                            {{-- v-pre: this panel is inside the #event-import-app Vue mount, which compiles
                                 its HTML as a template. e() does not escape mustaches, so owner-written
                                 terms containing a template expression would run in every visitor's browser. --}}
                            <div v-pre class="text-sm text-gray-700 dark:text-gray-300"
                                 dir="{{ is_rtl() ? 'rtl' : 'ltr' }}"
                                 style="{{ is_rtl() ? 'text-align: right;' : 'text-align: left;' }}">
                                <p>{!! nl2br(e($role->translatedRequestTerms())) !!}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Show All Fields and Save All buttons when events are parsed -->
        <div v-if="preview && preview.parsed && preview.parsed.length > 0 && !listMode" class="ap-card p-4 sm:p-8 shadow-md rounded-lg mb-4">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between">
                @if (auth()->user() && auth()->user()->isAdmin())
                <div class="flex items-center mb-3 sm:mb-0">
                    <input type="checkbox" 
                            id="show_all_fields" 
                            v-model="showAllFields" 
                            @change="saveShowAllFieldsPreference"
                            class="rounded border-gray-300 dark:border-gray-600 {{ (isset($isGuest) && $isGuest) ? 'text-blue-500 focus:border-blue-500 focus:ring-blue-500/50' : 'text-[var(--brand-blue)] focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]/50' }} shadow-sm focus:ring">
                    <label for="show_all_fields" class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                        {{ __('messages.show_all_fields') }}
                    </label>
                </div>
                @else
                <div></div>
                @endif

                <!-- Action buttons - now includes Save All -->
                <div class="flex gap-2 self-end sm:self-auto">
                    <button @click="handleSaveAll" v-if="({{ request()->has('automate') ? 'true' : 'false' }} || preview.parsed.length > 1) && !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}" type="button" class="px-4 py-2 bg-[var(--brand-button-bg)] text-white rounded-lg hover:bg-[var(--brand-button-bg-hover)] transition-all duration-200 hover:scale-105 hover:shadow-md">
                        {{ __('messages.save_all') }}
                    </button>
                </div>
            </div>
        </div>


        <!-- Hidden file input for details image -->
        <input type="file"
                ref="detailsFileInput"
                @change="handleDetailsFileSelect"
                accept="image/*"
                class="hidden">

        @unless ($isGuestPage)
        {{-- A link that read fine and found nothing new. --}}
        <div v-if="preview && preview.parsed && preview.parsed.length === 0 && preview.meta" v-cloak class="ap-card mb-4 rounded-xl p-6" role="status">
            <p class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.import_all_on_schedule') }}</p>
        </div>

        {{-- Two or more events are a list to choose from, whatever they were read from: one row
             each, the full card on request, and one button that says how many it will add. --}}
        <div v-if="listMode" v-cloak class="ap-card mb-4 rounded-xl p-4 sm:p-6">
            <div class="flex flex-wrap items-start justify-between gap-3">
                <div class="min-w-0">
                    <h3 id="import-list-heading" tabindex="-1" class="text-base font-semibold text-gray-900 focus:outline-none dark:text-gray-100" v-text="foundLabel"></h3>
                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        <span v-text="timezoneLabel"></span>
                        &middot;
                        <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}" class="js-leave-import font-medium text-[var(--brand-blue)] hover:underline">{{ __('messages.change_timezone') }}</a>
                    </p>
                </div>
                <div class="flex flex-shrink-0 items-center gap-2">
                    <button type="button" @click="handleClear" :disabled="isAddingAll" class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm font-medium text-gray-700 transition-all duration-200 hover:bg-gray-50 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] disabled:opacity-50 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-300 dark:hover:bg-gray-700">
                        {{ __('messages.import_start_over') }}
                    </button>
                    {{-- Once anything is added there is somewhere to go and see it. --}}
                    <a v-if="savedEvents.some(Boolean) && !isAddingAll" href="{{ route('event.import_done', ['subdomain' => $role->subdomain]) }}"
                       class="rounded-lg bg-[var(--brand-button-bg)] px-3 py-2 text-sm font-semibold text-white transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
                        {{ __('messages.import_see_schedule') }}
                    </a>
                </div>
            </div>

            <ul v-if="listNotes.length" class="mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-400">
                <li v-for="note in listNotes" :key="note" v-text="note"></li>
            </ul>

            <button v-if="preview.meta && preview.meta.can_read_whole_page" type="button" @click="fetchPreview(true)" :disabled="isAddingAll"
                    class="mt-3 text-sm font-medium text-[var(--brand-blue)] hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                {{ __('messages.import_read_whole_page') }}
            </button>

            <label class="mt-4 flex cursor-pointer items-center gap-3 border-t border-gray-100 pt-4 text-sm text-gray-700 dark:border-gray-700/50 dark:text-gray-300">
                <input type="checkbox" ref="selectAll" :checked="allSelected" @change="toggleAll" :disabled="isAddingAll || selectableCount === 0"
                       class="h-5 w-5 rounded border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] dark:border-gray-600 dark:[&:not(:checked)]:bg-gray-900">
                <span class="font-medium">{{ __('messages.eventbrite_select_all') }}</span>
                <span class="text-gray-500 dark:text-gray-400" v-text="selectedLabel"></span>
            </label>
        </div>
        @endunless

        <!-- Events cards - Moved outside the main div -->
        <div v-if="preview && preview.parsed && preview.parsed.length > 0"
             :class="listMode ? 'ap-card overflow-hidden rounded-xl divide-y divide-gray-100 dark:divide-gray-700/50' : 'space-y-6'">
            <template v-for="(event, idx) in preview.parsed" :key="idx">
            @unless ($isGuestPage)
            <div v-if="listMode && isRowVisible(idx)"
                 :class="['flex items-center gap-3 px-4 py-3 transition-colors duration-200',
                        expandedRow === idx ? 'bg-gray-50 dark:bg-[#252526]' : '',
                        rowState(idx) === 'idle' && !rowSelected(idx) ? 'opacity-60' : '']">
                {{-- A checkbox until it is being saved; then what became of it, in a shape as
                     well as a colour. --}}
                <span class="flex h-6 w-6 flex-shrink-0 items-center justify-center">
                    <svg v-if="rowState(idx) === 'saving'" class="h-5 w-5 text-[var(--brand-blue)] motion-safe:animate-spin" fill="none" viewBox="0 0 24 24" aria-hidden="true">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    <svg v-else-if="rowState(idx) === 'saved'" class="h-5 w-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" role="img" aria-label="{{ __('messages.saved') }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <svg v-else-if="rowState(idx) === 'error'" class="h-5 w-5 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" role="img" aria-label="{{ __('messages.error') }}">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 9.75l4.5 4.5m0-4.5l-4.5 4.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <input v-else type="checkbox" :checked="rowSelected(idx)" @change="toggleRow(idx)" :disabled="isAddingAll || !rowComplete(idx)"
                           :aria-label="event.event_name"
                           class="h-5 w-5 rounded border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] dark:border-gray-600 dark:[&:not(:checked)]:bg-gray-900">
                </span>

                <span class="flex h-12 w-12 flex-shrink-0 flex-col items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700" aria-hidden="true">
                    <span class="text-[10px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400" v-text="rowDate(idx).month"></span>
                    <span class="text-base font-bold leading-none text-gray-900 dark:text-gray-100" v-text="rowDate(idx).day"></span>
                </span>

                <button type="button" @click="expandRow(idx)" :aria-expanded="expandedRow === idx ? 'true' : 'false'" title="{{ __('messages.import_show_details') }}"
                        class="group flex min-h-[44px] min-w-0 flex-1 items-center gap-3 rounded-md text-start focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                    <span class="min-w-0 flex-1">
                        <span class="block truncate text-sm font-medium text-gray-900 dark:text-gray-100"><bdi v-text="event.event_name"></bdi></span>
                        <span class="mt-0.5 block truncate text-xs text-gray-500 dark:text-gray-400" v-text="rowMeta(idx)"></span>
                        <span v-if="rowProblem(idx)" class="mt-0.5 block text-xs font-medium text-amber-700 dark:text-amber-400" v-text="rowProblem(idx)"></span>
                    </span>
                    <svg :class="['h-5 w-5 flex-shrink-0 text-gray-400 transition-transform duration-200 dark:text-gray-500', expandedRow === idx ? 'rotate-90' : 'rtl:rotate-180']" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </div>
            @endunless
            <div v-if="!listMode || expandedRow === idx"
                    :class="[listMode ? 'bg-gray-50 p-4 dark:bg-[#252526] sm:p-6' : 'ap-card p-4 sm:p-8 shadow-md sm:rounded-xl mt-4',
                            (!listMode && savedEvents[idx]) ? 'border-2 border-green-500 dark:border-green-600' : '']">
                
                <!-- Card header -->
                @if (auth()->user())
                <div v-if="!listMode && (savedEvents[idx] || saveErrors[idx])" :class="['px-4 py-3 -m-4 sm:-m-8 mt-4 sm:mb-4 flex justify-between items-center rounded-t-lg', 
                                savedEvents[idx] ? 'bg-green-50 dark:bg-green-900/30' : 'bg-red-50 dark:bg-red-900/30']">
                    <h3 class="font-medium text-lg">
                        <span v-if="savedEvents[idx]" class="ms-2 text-sm text-green-600 dark:text-green-400">
                            <svg class="inline-block w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path>
                            </svg>
                            {{ __('messages.saved') }}
                        </span>
                        <span v-else-if="saveErrors[idx]" class="ms-2 text-sm text-red-600 dark:text-red-400">
                            <svg class="inline-block w-4 h-4 me-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                            {{ __('messages.error') }}: @{{ saveErrors[idx] }}
                        </span>
                    </h3>                            
                </div>
                @endif
                
                <!-- Card body -->
                <div class="grid lg:grid-cols-2 gap-4">
                    <!-- Left column: Form fields -->
                    <div class="space-y-4">
                        <!-- Show matching event if found for this specific event -->
                        <div v-if="preview.parsed[idx].event_url" class="p-3 text-sm bg-yellow-100 dark:bg-yellow-900/30 text-yellow-800 dark:text-yellow-200 rounded-lg">
                            <div class="flex items-center justify-between">
                                <div>
                                    {{ __('messages.similar_event_found') }}
                                </div>
                                <div class="flex gap-2">
                                    <!-- View button -->
                                    <a :href="preview.parsed[idx].event_url"
                                        target="_blank"
                                        class="px-4 py-2 {{ (isset($isGuest) && $isGuest) ? 'bg-blue-500 hover:bg-blue-600' : 'bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)]' }} text-white rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md">
                                        {{ __('messages.view') }}
                                    </a>
                                    <!-- Show Select button if event hasn't been added to curator schedule -->
                                    <button v-if="isCurator && !preview.parsed[idx].is_curated" 
                                            @click="handleSelect(idx)" 
                                            type="button" 
                                            :disabled="savingEvents[idx]"
                                            :class="['px-4 py-2 rounded-lg transition-all duration-200',
                                                savingEvents[idx]
                                                    ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                                    : 'bg-green-500 text-white hover:bg-green-600 hover:scale-105']">
                                        <span v-if="savingEvents[idx]" class="inline-flex items-center">
                                            <svg class="animate-spin -ms-1 me-1 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                            </svg>
                                            {{ __('messages.selecting') }}
                                        </span>
                                        <span v-else>{{ __('messages.select') }}</span>
                                    </button>
                                </div>
                            </div>
                        </div>
                        
                        <div>
                            <x-input-label for="name_@{{ idx }}" :value="__('messages.event_name')" />
                            <x-text-input id="name_@{{ idx }}"
                                name="name_@{{ idx }}"
                                type="text"
                                class="mt-1 block w-full"
                                v-model="preview.parsed[idx].event_name"
                                v-bind:readonly="savedEvents[idx]"
                                required />
                        </div>

                        <div>
                            <label class="block font-medium text-sm text-gray-700 dark:text-gray-300">
                                {{ __('messages.date_and_time') }}
                            </label>
                            <div class="flex flex-wrap items-center gap-2 sm:gap-3 mt-1">
                                <input type="text"
                                    :class="'datepicker_date_' + idx + ' flex-1 min-w-[140px] basis-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm'"
                                    v-model="preview.parsed[idx].event_date"
                                    v-bind:readonly="savedEvents[idx]"
                                    autocomplete="off"
                                    aria-label="{{ __('messages.date') }}" />
                                <div class="flex items-center gap-2 sm:gap-3 shrink-0">
                                    <div class="time-input-wrapper">
                                        <input type="text"
                                            class="w-28 sm:w-32 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                            v-model="preview.parsed[idx].event_start_time"
                                            v-bind:readonly="savedEvents[idx]"
                                            @focus="openTimeDropdown(idx, 'start')"
                                            @click="openTimeDropdown(idx, 'start')"
                                            @blur="onStartTimeBlur(idx); closeTimeDropdown(idx, 'start')"
                                            @input="filterTimeOptions(idx, 'start')"
                                            @keydown="handleTimeKeydown($event, idx, 'start')"
                                            placeholder="{{ __('messages.start_time') }}"
                                            autocomplete="off"
                                            aria-label="{{ __('messages.start_time') }}" />
                                        <div :class="['time-dropdown-import', { 'open': activeTimeDropdown === idx + '-start' }]"
                                            :ref="'time_dropdown_' + idx + '_start'">
                                            <div v-for="time in timeOptions" :key="time"
                                                class="time-dropdown-item-import"
                                                :class="{ 'highlighted': highlightedTimeIndex[idx + '-start'] === timeOptions.indexOf(time) }"
                                                v-show="!timeFilter[idx + '-start'] || time.toLowerCase().includes(timeFilter[idx + '-start'].toLowerCase())"
                                                @mousedown.prevent="selectTime(idx, 'start', time)">
                                                @{{ time }}
                                            </div>
                                        </div>
                                    </div>
                                    <span class="text-gray-500 dark:text-gray-400 text-sm shrink-0">{{ __('messages.to') }}</span>
                                    <div class="time-input-wrapper">
                                        <input type="text"
                                            class="w-28 sm:w-32 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                            v-model="preview.parsed[idx].event_end_time"
                                            v-bind:readonly="savedEvents[idx]"
                                            @focus="openTimeDropdown(idx, 'end')"
                                            @click="openTimeDropdown(idx, 'end')"
                                            @blur="onEndTimeBlur(idx); closeTimeDropdown(idx, 'end')"
                                            @input="filterTimeOptions(idx, 'end')"
                                            @keydown="handleTimeKeydown($event, idx, 'end')"
                                            placeholder="{{ __('messages.end_time') }}"
                                            autocomplete="off"
                                            aria-label="{{ __('messages.end_time') }}" />
                                        <div :class="['time-dropdown-import', { 'open': activeTimeDropdown === idx + '-end' }]"
                                            :ref="'time_dropdown_' + idx + '_end'">
                                            <div v-for="time in timeOptions" :key="time"
                                                class="time-dropdown-item-import"
                                                :class="{ 'highlighted': highlightedTimeIndex[idx + '-end'] === timeOptions.indexOf(time) }"
                                                v-show="!timeFilter[idx + '-end'] || time.toLowerCase().includes(timeFilter[idx + '-end'].toLowerCase())"
                                                @mousedown.prevent="selectTime(idx, 'end', time)">
                                                @{{ time }}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Venue display for saved events -->
                        <div v-if="savedEvents[idx]" class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                            <x-input-label :value="__('messages.venue')" class="mb-2" />
                            <div class="text-gray-900 dark:text-gray-100">
                                <!-- Show selected existing venue -->
                                <template v-if="eventSelectedVenues[idx]">
                                    <p class="font-medium">@{{ eventSelectedVenues[idx].name || eventSelectedVenues[idx].address1 }}</p>
                                </template>
                                <!-- Show created venue info -->
                                <template v-else>
                                    <p v-if="preview.parsed[idx].venue_name" class="font-medium">@{{ preview.parsed[idx].venue_name }}</p>
                                    <p v-if="preview.parsed[idx].event_address" class="text-sm text-gray-600 dark:text-gray-400">@{{ preview.parsed[idx].event_address }}</p>
                                    <p v-if="preview.parsed[idx].event_city" class="text-sm text-gray-600 dark:text-gray-400">@{{ preview.parsed[idx].event_city }}</p>
                                </template>
                            </div>
                        </div>

                        <!-- Venue section with border (editable) -->
                        <div v-if="!savedEvents[idx]" class="border border-gray-300 dark:border-gray-600 rounded-lg p-4">
                            <x-input-label :value="__('messages.venue')" class="mb-2" />

                            <!-- Venue selection - show when venues available (auth) OR venue match exists (guest) -->
                            <div v-if="venues.length > 0 || preview.parsed[idx].venue_id">
                                <fieldset>
                                <div class="mt-2 mb-4 space-y-4 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                    <div class="flex items-center">
                                        <input :id="'use_existing_venue_' + idx" :name="'venue_type_' + idx" type="radio" value="use_existing" v-model="eventVenueTypes[idx]" @change="onVenueTypeChange(idx)"
                                            class="h-4 w-4 border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <label :for="'use_existing_venue_' + idx"
                                            class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                    </div>
                                    <div class="flex items-center">
                                        <input :id="'create_new_venue_' + idx" :name="'venue_type_' + idx" type="radio" value="create_new" v-model="eventVenueTypes[idx]" @change="onVenueTypeChange(idx)"
                                            class="h-4 w-4 border-gray-300 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <label :for="'create_new_venue_' + idx"
                                            class="ms-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.create_new') }}</label>
                                    </div>
                                </div>
                            </fieldset>

                            <!-- Venue dropdown - only for authenticated users -->
                            @auth
                            <div v-if="eventVenueTypes[idx] === 'use_existing' && venues.length > 0">
                                <select :id="'selected_venue_' + idx" data-searchable
                                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                        @change="onVenueSelect(idx, $event.target.value ? venues.find(v => v.id === $event.target.value) : null)"
                                        :value="eventSelectedVenues[idx]?.id || ''">
                                    <option value="" disabled selected>{{ __('messages.please_select') }}</option>
                                    <option v-if="venueGroupCount > 1 && memberVenues.length" disabled>── {{ __('messages.member') }} ──</option>
                                    <option v-for="venue in memberVenues" :key="venue.id" :value="venue.id">
                                        @{{ (venue.name || venue.address1) + (venue.city ? ', ' + venue.city : '') }}
                                    </option>
                                    <option v-if="venueGroupCount > 1 && followingVenues.length" disabled>── {{ __('messages.following') }} ──</option>
                                    <option v-for="venue in followingVenues" :key="venue.id" :value="venue.id">
                                        @{{ (venue.name || venue.address1) + (venue.city ? ', ' + venue.city : '') }}
                                    </option>
                                    <option v-if="venueGroupCount > 1 && connectedVenues.length" disabled>── {{ __('messages.from_past_events') }} ──</option>
                                    <option v-for="venue in connectedVenues" :key="venue.id" :value="venue.id">
                                        @{{ (venue.name || venue.address1) + (venue.city ? ', ' + venue.city : '') }}
                                    </option>
                                </select>
                                <p v-if="preview.parsed[idx].matched_venue_name" class="mt-1 text-sm text-green-600 dark:text-green-400">
                                    ✓ {{ __('messages.matched_venue_label') }}: @{{ preview.parsed[idx].matched_venue_name }}
                                </p>
                            </div>
                            @endauth

                            <!-- Matched venue message - for unauthenticated users with matched venue -->
                            @guest
                            <div v-if="eventVenueTypes[idx] === 'use_existing' && preview.parsed[idx].venue_id" class="mt-1">
                                <p class="text-sm text-green-600 dark:text-green-400">
                                    {{ __('messages.matched_venue') }}:
                                    <template v-if="preview.parsed[idx].venue_url">
                                        <a v-bind:href="preview.parsed[idx].venue_url"
                                           target="_blank"
                                           class="underline hover:text-green-800 dark:hover:text-green-300">
                                            @{{ preview.parsed[idx].matched_venue_name || preview.parsed[idx].venue_name }}
                                        </a>
                                    </template>
                                    <template v-else>
                                        @{{ preview.parsed[idx].matched_venue_name || preview.parsed[idx].venue_name }}
                                    </template>
                                </p>
                            </div>
                            @endguest
                            </div>

                            <!-- Venue fields (when "Create New" selected or no venues available) -->
                            <div v-if="shouldShowVenueFields(idx) && !eventSelectedVenues[idx]" class="mt-4">
                                <!-- Venue Name -->
                                <div>
                                    <x-input-label for="venue_name_@{{ idx }}" :value="__('messages.name')" />
                                    <x-text-input id="venue_name_@{{ idx }}"
                                        name="venue_name_@{{ idx }}"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="preview.parsed[idx].venue_name"
                                        v-bind:readonly="savedEvents[idx]"
                                        autocomplete="off" />
                                </div>

                                <!-- Street Address -->
                                <div class="mt-4">
                                    <x-input-label for="venue_address1_@{{ idx }}" :value="__('messages.street_address')" />
                                    <x-text-input id="venue_address1_@{{ idx }}"
                                        name="venue_address1_@{{ idx }}"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="preview.parsed[idx].event_address"
                                        v-bind:readonly="savedEvents[idx]"
                                        autocomplete="off" />
                                </div>

                                <!-- City -->
                                <div class="mt-4">
                                    <x-input-label for="venue_city_@{{ idx }}" :value="__('messages.city')" />
                                    <x-text-input id="venue_city_@{{ idx }}"
                                        name="venue_city_@{{ idx }}"
                                        type="text"
                                        class="mt-1 block w-full"
                                        v-model="preview.parsed[idx].event_city"
                                        v-bind:readonly="savedEvents[idx]"
                                        placeholder="{{ $role->isCurator() ? $role->city : '' }}"
                                        autocomplete="off" />
                                </div>
                            </div>

                            @auth
                            {{-- Rendered once for the whole venue section so it covers a brand-new
                                 venue AND an existing venue nobody has claimed yet. canClaimVenue()
                                 is the only place that rule lives on the client; the payload
                                 re-checks it, and EventRepo::claimVenueOwnership() enforces it. --}}
                            <div v-if="canClaimVenue(idx)" class="mt-4 flex items-center">
                                <input :id="'claim_venue_' + idx" type="checkbox"
                                    v-model="eventClaimVenue[idx]"
                                    v-bind:disabled="savedEvents[idx]"
                                    class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]/50">
                                <label :for="'claim_venue_' + idx" class="ms-2 text-sm text-gray-700 dark:text-gray-300">
                                    {{ __('messages.claim_new_venue_ownership') }}
                                </label>
                            </div>
                            @endauth
                        </div>

                        <!-- Configurable Import Fields -->
                        @php $importFields = $role->import_config['fields'] ?? []; @endphp

                        <!-- Short Description -->
                        <div class="mt-4" v-if="importFields.short_description || showAllFields" v-show="!savedEvents[idx]">
                            <x-input-label for="short_description_@{{ idx }}">{{ __('messages.short_description') }}<span v-if="requiredFields.short_description" class="text-red-500"> *</span></x-input-label>
                            <x-text-input
                                id="short_description_@{{ idx }}"
                                type="text"
                                class="mt-1 block w-full"
                                maxlength="200"
                                v-model="preview.parsed[idx].short_description"
                                v-bind:readonly="savedEvents[idx]"
                                autocomplete="off" />
                        </div>

                        <!-- Description -->
                        <div class="mt-4" v-if="importFields.description || showAllFields" v-show="!savedEvents[idx]">
                            <x-input-label for="description_@{{ idx }}">{{ __('messages.description') }}<span v-if="requiredFields.description" class="text-red-500"> *</span></x-input-label>
                            <textarea
                                :id="'import_description_' + idx"
                                :ref="'descriptionEditor_' + idx"
                                rows="4"
                                dir="auto"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                v-bind:readonly="savedEvents[idx]">@{{ preview.parsed[idx].event_details }}</textarea>
                        </div>

                        <!-- Price -->
                        <div class="mt-4" v-if="importFields.ticket_price || showAllFields" v-show="!savedEvents[idx]">
                            <x-input-label>{{ __('messages.price') }}<span v-if="requiredFields.ticket_price" class="text-red-500"> *</span></x-input-label>
                            <div class="mt-1 flex gap-3">
                                <select v-model="preview.parsed[idx].ticket_currency_code" data-searchable
                                    v-bind:disabled="savedEvents[idx]"
                                    class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                    @foreach ($currencies as $currency)
                                    @if ($loop->index == 2)
                                    <option disabled>──────</option>
                                    @endif
                                    <option value="{{ $currency->value }}">{{ $currency->value }}</option>
                                    @endforeach
                                </select>
                                <x-text-input type="number" step="0.01" min="0"
                                    class="flex-1"
                                    v-model="preview.parsed[idx].ticket_price"
                                    v-bind:readonly="savedEvents[idx]" />
                            </div>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.external_price_help') }}</p>
                        </div>

                        <!-- Coupon Code -->
                        <div class="mt-4" v-if="importFields.coupon_code || showAllFields" v-show="!savedEvents[idx]">
                            <x-input-label for="coupon_code_@{{ idx }}">{{ __('messages.coupon_code') }}<span v-if="requiredFields.coupon_code" class="text-red-500"> *</span></x-input-label>
                            <x-text-input id="coupon_code_@{{ idx }}" type="text" class="mt-1 block w-full"
                                maxlength="255" v-model="preview.parsed[idx].coupon_code"
                                v-bind:readonly="savedEvents[idx]" autocomplete="off" />

                            <x-input-label class="mt-4">{{ __('messages.discount') }}</x-input-label>
                            <div class="mt-1 flex gap-3">
                                <select v-model="preview.parsed[idx].coupon_discount_type"
                                    v-bind:disabled="savedEvents[idx]"
                                    class="w-28 border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                    <option value="fixed">@{{ preview.parsed[idx].ticket_currency_code }}</option>
                                    <option value="percentage">%</option>
                                </select>
                                <x-text-input type="number" step="0.01" min="0"
                                    class="flex-1"
                                    v-model="preview.parsed[idx].coupon_discount"
                                    v-bind:readonly="savedEvents[idx]" />
                            </div>
                        </div>

                        <!-- Registration URL -->
                        <div class="mt-4" v-if="importFields.registration_url || showAllFields" v-show="!savedEvents[idx]">
                            <x-input-label for="registration_url_@{{ idx }}">{{ __('messages.registration_url') }}<span v-if="requiredFields.registration_url" class="text-red-500"> *</span></x-input-label>
                            <x-text-input
                                id="registration_url_@{{ idx }}"
                                type="url"
                                class="mt-1 block w-full"
                                v-model="preview.parsed[idx].registration_url"
                                v-bind:readonly="savedEvents[idx]"
                                autocomplete="off" />
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.registration_url_help') }}</p>
                        </div>

                        <!-- Category -->
                        <div class="mt-4" v-if="importFields.category_id || showAllFields" v-show="!savedEvents[idx]">
                            <x-input-label for="category_id_@{{ idx }}">{{ __('messages.category') }}<span v-if="requiredFields.category_id" class="text-red-500"> *</span></x-input-label>
                            <select
                                id="category_id_@{{ idx }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                v-model="preview.parsed[idx].category_id"
                                v-bind:disabled="savedEvents[idx]">
                                <option value="">{{ __('messages.please_select') }}</option>
                                {{-- v-pre: custom category names are curator-controlled, and this select is
                                     inside a Vue mount, so an unguarded mustache would be compiled as JS --}}
                                @foreach(get_translated_categories($role ?? null) as $catId => $catName)
                                <option v-pre value="{{ $catId }}">{{ $catName }}</option>
                                @endforeach
                            </select>
                        </div>

                        <!-- Sub-schedule -->
                        <div class="mt-4" v-if="(importFields.group_id || showAllFields) && groups.length > 0" v-show="!savedEvents[idx]">
                            <x-input-label for="group_id_@{{ idx }}">{{ __('messages.schedule') }}<span v-if="requiredFields.group_id" class="text-red-500"> *</span></x-input-label>
                            <select
                                id="group_id_@{{ idx }}"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm {{ rtl_class($role, 'rtl', '', true) }}"
                                v-model="preview.parsed[idx].group_id"
                                v-bind:disabled="savedEvents[idx]">
                                <option value="">{{ __('messages.please_select') }}</option>
                                <option v-for="group in groups" :key="group.id" :value="group.id">@{{ group.name }}</option>
                            </select>
                        </div>

                        <!-- Custom Fields Section -->
                        @php
                            // Guests only see the fields the schedule opted to put on its request
                            // form; the admin import page still shows every field.
                            $isGuestImport = isset($isGuest) && $isGuest;
                            $eventCustomFields = $isGuestImport
                                ? $role->getRequestFormCustomFields()
                                : $role->getEventCustomFields();
                        @endphp
                        @if ($role->isPro() && count($eventCustomFields) > 0)
                        <div class="mt-6">
                            @foreach($eventCustomFields as $fieldKey => $field)
                            @php
                                $fieldRegex = $field['regex'] ?? '';
                                $fieldRegexHint = $field['regex_hint'] ?? '';
                            @endphp
                            <div class="mt-4">
                                {{-- v-pre on every element below that renders owner-authored text as
                                     a text node: this view is included by guest-import.blade.php, so
                                     Vue would compile a mustache in a field label, option or hint
                                     and run it in the visitor's browser. --}}
                                <x-input-label v-pre for="custom_field_{{ $fieldKey }}_@{{ idx }}" :value="$role->customFieldLabel($field, $fieldKey, $isGuestImport) . (!empty($field['required']) ? ' *' : '')" />

                                @if(($field['type'] ?? 'string') === 'string')
                                <x-text-input
                                    id="custom_field_{{ $fieldKey }}_@{{ idx }}"
                                    type="text"
                                    class="mt-1 block w-full"
                                    v-model="preview.parsed[idx].custom_field_values.{{ $fieldKey }}"
                                    v-bind:readonly="savedEvents[idx]"
                                    :pattern="$fieldRegex ?: null"
                                    :title="$fieldRegexHint ?: null"
                                    autocomplete="off" />
                                @elseif(($field['type'] ?? '') === 'multiline_string')
                                <textarea
                                    id="custom_field_{{ $fieldKey }}_@{{ idx }}"
                                    rows="2"
                                    dir="auto"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                    {!! $fieldRegexHint ? 'title="' . e($fieldRegexHint) . '"' : '' !!}
                                    v-model="preview.parsed[idx].custom_field_values.{{ $fieldKey }}"
                                    v-bind:readonly="savedEvents[idx]"></textarea>
                                @elseif(($field['type'] ?? '') === 'switch')
                                <div class="mt-2">
                                    <input type="checkbox"
                                        id="custom_field_{{ $fieldKey }}_@{{ idx }}"
                                        v-model="preview.parsed[idx].custom_field_values.{{ $fieldKey }}"
                                        true-value="1"
                                        false-value="0"
                                        class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded"
                                        v-bind:disabled="savedEvents[idx]" />
                                </div>
                                @elseif(($field['type'] ?? '') === 'date')
                                <x-text-input
                                    id="custom_field_{{ $fieldKey }}_@{{ idx }}"
                                    type="date"
                                    class="mt-1 block w-full"
                                    v-model="preview.parsed[idx].custom_field_values.{{ $fieldKey }}"
                                    v-bind:readonly="savedEvents[idx]" />
                                @elseif(($field['type'] ?? '') === 'dropdown')
                                <select
                                    id="custom_field_{{ $fieldKey }}_@{{ idx }}"
                                    class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                    v-model="preview.parsed[idx].custom_field_values.{{ $fieldKey }}"
                                    v-bind:disabled="savedEvents[idx]">
                                    <option value="">{{ __('messages.select') }}...</option>
                                    @foreach(explode(',', $field['options'] ?? '') as $option)
                                        @php $option = trim($option); @endphp
                                        @if($option)
                                        <option v-pre value="{{ $option }}">{{ $option }}</option>
                                        @endif
                                    @endforeach
                                </select>
                                @elseif(($field['type'] ?? '') === 'multiselect')
                                <div class="mt-1 space-y-1">
                                    @foreach(explode(',', $field['options'] ?? '') as $option)
                                        @php $option = trim($option); @endphp
                                        @if($option)
                                        <label class="flex items-center gap-2 text-gray-700 dark:text-gray-300">
                                            <input type="checkbox"
                                                value="{{ $option }}"
                                                :checked="(preview.parsed[idx].custom_field_values.{{ $fieldKey }} || '').split(', ').map(s => s.trim()).includes(@js($option))"
                                                @change="toggleImportMultiselect(idx, '{{ $fieldKey }}', @js($option), $event)"
                                                :disabled="savedEvents[idx]"
                                                class="h-4 w-4 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)] border-gray-300 rounded" />
                                            {{-- v-pre goes on the span, not the label: the label
                                                 wraps an input carrying :checked/@change/:disabled,
                                                 and v-pre would stop those compiling. --}}
                                            <span v-pre>{{ $option }}</span>
                                        </label>
                                        @endif
                                    @endforeach
                                </div>
                                @endif

                                @if ($fieldRegexHint && in_array($field['type'] ?? 'string', ['string', 'multiline_string'], true))
                                <p v-pre class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ $fieldRegexHint }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                        @endif

                        <!-- Account creation checkbox for guest users (curators that do NOT require an account) -->
                        @if (isset($isGuest) && $isGuest && ! auth()->check())
                        <div class="flex items-center">
                            <input type="checkbox" 
                                    id="create_account_@{{ idx }}" 
                                    name="create_account_@{{ idx }}" 
                                    v-model="createAccount"
                                    class="rounded border-gray-300 dark:border-gray-600 text-blue-500 shadow-sm focus:border-blue-500 focus:ring focus:ring-blue-500/50">
                            <label for="create_account_@{{ idx }}" class="ms-2 text-sm font-medium text-gray-700 dark:text-gray-300">
                                {{ __('messages.create_account') }}
                            </label>
                            <span class="text-sm text-gray-400 dark:text-gray-500 mx-1">|</span> <a href="{{ marketing_url('/why-create-account') }}{{ request()->has('lang') && is_valid_language_code(request()->get('lang')) ? '?lang=' . request()->get('lang') : '' }}" target="_blank" class="text-sm text-blue-600 dark:text-blue-300 underline hover:no-underline">{{ __('messages.why_create_account_learn_more') }} <svg class="inline-block w-3 h-3 ml-0.5" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 0 0 3 8.25v10.5A2.25 2.25 0 0 0 5.25 21h10.5A2.25 2.25 0 0 0 18 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg></a>
                        </div>

                        <div v-if="createAccount" class="space-y-4">
                            <div>
                                <x-input-label for="name_@{{ idx }}" :value="__('messages.name')" />
                                <x-text-input id="name_@{{ idx }}" 
                                    name="name_@{{ idx }}" 
                                    type="text" 
                                    class="mt-1 block w-full" 
                                    v-model="userName"
                                    required />
                            </div>
                            
                            <div>
                                <x-input-label for="email_@{{ idx }}" :value="__('messages.email')" />
                                <x-text-input id="email_@{{ idx }}" 
                                    name="email_@{{ idx }}" 
                                    type="email" 
                                    class="mt-1 block w-full" 
                                    v-model="userEmail"
                                    required />
                            </div>
                            
                            <div>
                                <x-input-label for="password_@{{ idx }}" :value="__('messages.password')" />
                                <x-text-input id="password_@{{ idx }}"
                                    name="password_@{{ idx }}"
                                    type="password"
                                    class="mt-1 block w-full"
                                    v-model="userPassword"
                                    required />
                            </div>
                            <div class="relative flex items-start">
                                <div class="flex h-6 items-center">
                                    <input type="checkbox" id="terms_@{{ idx }}" name="terms_@{{ idx }}" required
                                        class="h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-blue-500 focus:ring-blue-500">
                                </div>
                                <div class="ms-3 text-sm leading-6">
                                    <label :for="'terms_' + idx" class="font-medium text-gray-700 dark:text-gray-300">
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
                        </div>
                        @endif

                        <!-- Add buttons at the bottom of the left column -->
                        <div class="mt-12 flex justify-end gap-2">
                            <template v-if="savedEvents[idx]">
                                <button v-if="!savedEventData[idx]?.is_curated && !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}" @click="handleEdit(idx)" type="button" class="px-4 py-2 {{ (isset($isGuest) && $isGuest) ? 'bg-blue-500 hover:bg-blue-600' : 'bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)]' }} text-white rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md">
                                    {{ __('messages.edit') }}
                                </button>
                                <button v-if="{{ auth()->check() ? 'true' : 'false' }}" @click="handleView(idx)" type="button" class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
                                    {{ __('messages.view') }}
                                </button>
                                <button @click="handleClearForNext" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
                                    {{ __('messages.clear') }}
                                </button>
                            </template>
                            <template v-else>
                                <button @click="handleRemoveEvent(idx)" v-if="preview.parsed.length > 1" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
                                    {{ __('messages.remove') }}
                                </button>
                                <button @click="handleClear" type="button" v-if="preview.parsed.length == 1" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-lg hover:bg-gray-300 dark:hover:bg-gray-600 transition-all duration-200 hover:scale-105 hover:shadow-md">
                                    {{ __('messages.clear') }}
                                </button>
                                <button @click="handleSave(idx)" 
                                        type="button" 
                                        :disabled="savingEvents[idx] || !canSaveRow(idx)"
                                        :class="['px-4 py-2 rounded-lg transition-all duration-200',
                                            (savingEvents[idx] || !canSaveRow(idx))
                                                ? 'bg-gray-400 text-gray-200 cursor-not-allowed'
                                                : '{{ (isset($isGuest) && $isGuest) ? 'bg-blue-500 text-white hover:bg-blue-600' : 'bg-[var(--brand-button-bg)] text-white hover:bg-[var(--brand-button-bg-hover)]' }} hover:scale-105']">
                                    <span v-if="savingEvents[idx]" class="inline-flex items-center">
                                        <svg class="animate-spin -ms-1 me-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                        </svg>
                                        {{ __('messages.saving') }}
                                    </span>
                                    <span v-else>{{ __('messages.save') }}</span>
                                </button>
                                <!--
                                <button v-if="isCurator && preview.parsed[idx].event_url && !preview.parsed[idx].is_curated && !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}" 
                                        @click="handleCurate(idx)" 
                                        type="button" 
                                        class="px-4 py-2 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors">
                                    {{ __('messages.curate') }}
                                </button>
                                -->
                            </template>
                        </div>
                        


                        <!-- JSON preview with border matching textarea -->
                        <div v-if="showAllFields && !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}" class="mt-4 border border-gray-300 dark:border-gray-700 rounded-lg shadow-sm overflow-auto bg-gray-50 dark:bg-gray-900">
                            <pre class="p-4 text-xs text-gray-800 dark:text-gray-200">@{{ JSON.stringify(preview.parsed[idx], null, 2) }}</pre>
                        </div>
                        

                    </div>
                    
                    <!-- Right column: Image -->
                    <div class="flex flex-col">
                        <div class="relative h-full flex flex-col">
                            <!-- Image preview -->
                            <div v-if="preview.parsed[idx].social_image" 
                                    class="flex-grow rounded-lg overflow-hidden bg-gray-100 dark:bg-gray-800">
                                <img v-bind:src="getSocialImageUrl(preview.parsed[idx].social_image)" 
                                        class="object-contain w-full h-full" 
                                        alt="Event preview image">
                                
                                <!-- Remove image button -->
                                <button v-if="!isLoading"
                                        @click="removeImage(idx)"
                                        type="button"
                                        v-bind:disabled="savedEvents[idx]"
                                        style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
                                        class="absolute top-2 end-2 bg-red-500 text-white rounded-full hover:bg-red-600 focus:outline-none disabled:opacity-50 disabled:cursor-not-allowed transition-all duration-200 hover:scale-105 flex items-center justify-center">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                    </svg>
                                </button>
                            </div>

                            <!-- Drop zone -->
                            <div v-else-if="!savedEvents[idx]"
                                    @dragover.prevent="dragOver"
                                    @dragleave.prevent="dragLeave"
                                    @drop.prevent="(e) => handleDrop(e, idx)"
                                    @click="() => openFileSelector(idx)"
                                    v-bind:class="['flex-grow flex items-center justify-center rounded-lg border-2 border-dashed cursor-pointer', 
                                            isDragging ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-300 dark:border-gray-600']">
                                <div class="text-center py-10">
                                    <!-- Show loading spinner when uploading -->
                                    <template v-if="isUploadingImage === idx">
                                        <div class="relative mx-auto w-12 h-12">
                                            <div class="w-12 h-12 rounded-full bg-blue-500/30"></div>
                                            <div class="absolute top-0 left-0 w-12 h-12 rounded-full border-4 border-blue-500 border-t-transparent animate-spin"></div>
                                        </div>
                                        <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('messages.uploading') }}...
                                        </p>
                                    </template>
                                    <!-- Default upload icon and text -->
                                    <template v-else>
                                        <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                            <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                        </svg>
                                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                            {{ __('messages.drag_drop_image') }}
                                        </p>

                                    </template>
                                </div>
                            </div>

                            <!-- Hidden file input -->
                            <input type="file" 
                                    v-bind:ref="'fileInput_' + idx"
                                    @change="(e) => handleFileSelect(e, idx)"
                                    accept="image/*"
                                    class="hidden">
                        </div>
                    </div>
                </div>

                <!-- YouTube Videos Section for Talent - Now below the form and image -->
                <div v-if="!savedEvents[idx] && preview.parsed[idx].performers && preview.parsed[idx].performers.length > 0" class="mt-6">
                    <div v-for="(performer, performerIdx) in preview.parsed[idx].performers" :key="performerIdx" class="my-4 bg-gray-50 dark:bg-gray-800 rounded-lg">
                        <!-- Only show YouTube video selection for the first performer when there are multiple performers -->
                        <div v-if="performerIdx === 0 && ! performer.talent_id">

                            <!-- Loading state -->
                            <div v-if="performer.searching" class="flex items-center space-x-2 text-sm text-gray-500 dark:text-gray-400">
                                <div class="animate-spin rounded-full h-3 w-3 border-b-2 border-blue-500"></div>
                                <span>{{ __('messages.searching_youtube') }}</span>
                            </div>

                            <!-- Videos grid - Now in two columns if there's room -->
                            <div v-else-if="performer.videos && performer.videos.length > 0" class="space-y-3">
                                <div class="text-xs text-gray-600 dark:text-gray-400 mb-2">
                                    {{ __('messages.results_for') }} "@{{ performer.name }}"
                                </div>
                                <div class="grid grid-cols-1 md:grid-cols-2 gap-2 -mx-3 p-3">
                                    <div v-for="video in performer.videos.slice(0, 6)" :key="video.id" 
                                            class="border rounded-lg p-2 cursor-pointer hover:border-blue-300 transition-colors relative"
                                            :class="isVideoSelected(idx, performerIdx, video) ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/30' : 'border-gray-200 dark:border-gray-600'"
                                            @click="selectVideo(idx, performerIdx, video)">
                                        <div class="flex items-center space-x-3">
                                            <div class="w-16 h-12 bg-gray-100 dark:bg-gray-700 rounded flex items-center justify-center relative flex-shrink-0">
                                                <img v-if="video.thumbnail" :src="video.thumbnail" :alt="video.title" class="w-full h-full object-cover rounded">
                                                <svg v-else class="h-6 w-6 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                                </svg>
                                            </div>
                                            
                                            <div class="flex-1 min-w-0">
                                                <h6 class="text-xs font-medium text-gray-900 dark:text-gray-100 truncate">@{{ video.title }}</h6>
                                                <p class="text-xs text-gray-500 dark:text-gray-400 truncate">@{{ video.channelTitle }}</p>
                                                <div class="flex items-center space-x-3 text-xs text-gray-500 dark:text-gray-400 mt-1">
                                                    <div class="flex items-center space-x-1">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                                                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span>@{{ formatNumber(video.viewCount) }}</span>
                                                    </div>
                                                    <div class="flex items-center space-x-1">
                                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                            <path fill-rule="evenodd" d="M3.172 5.172a4 4 0 015.656 0L10 6.343l1.172-1.171a4 4 0 115.656 5.656L10 17.657l-6.828-6.829a4 4 0 010-5.656z" clip-rule="evenodd"/>
                                                        </svg>
                                                        <span>@{{ formatNumber(video.likeCount) }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Watch button -->
                                            <a :href="video.url" target="_blank" 
                                                class="inline-flex items-center text-xs text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300 font-medium transition-colors flex-shrink-0"
                                                @click.stop>
                                                <svg class="w-3 h-3 me-1" fill="currentColor" viewBox="0 0 24 24">
                                                    <path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/>
                                                </svg>
                                                Watch
                                            </a>
                                        </div>
                                        
                                        <!-- Selection indicator -->
                                        <div v-if="isVideoSelected(idx, performerIdx, video)" class="absolute top-2 end-2">
                                            <div class="bg-blue-500 text-white rounded-full w-5 h-5 flex items-center justify-center">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Error state -->
                            @if (auth()->user() && auth()->user()->isAdmin())
                            <div v-else-if="performer.error" class="text-xs text-red-600 dark:text-red-400">
                                @{{ performer.error }}
                            </div>
                            @endif

                        </div>
                    </div>
                </div>
            </div>
            </template>
        </div>

        @unless ($isGuestPage)
        {{-- Sticky, not fixed: it stays with the list and leaves the rest of the page alone. --}}
        <div v-if="listMode" v-cloak class="ap-card sticky bottom-0 z-10 mt-4 flex flex-wrap items-center justify-between gap-4 rounded-xl p-4">
            <div class="min-w-0 flex-1" role="status" aria-live="polite">
                <template v-if="isAddingAll">
                    <div class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300" v-text="progressLabel"></div>
                    <div class="h-2.5 w-full rounded-full bg-gray-200 dark:bg-gray-700" role="progressbar" aria-valuemin="0" :aria-valuemax="addProgress.total" :aria-valuenow="addProgress.done">
                        <div class="h-2.5 rounded-full bg-[var(--brand-button-bg)] transition-all duration-300" :style="{ width: (addProgress.total ? Math.round(addProgress.done / addProgress.total * 100) : 0) + '%' }"></div>
                    </div>
                </template>
                <p v-else-if="addSummary" class="text-sm text-gray-700 dark:text-gray-300" v-text="summaryLabel"></p>
                <p v-else class="text-sm text-gray-500 dark:text-gray-400" v-text="selectedLabel"></p>
            </div>
            <button type="button" @click="addSelected" :disabled="isAddingAll || allDone || selectedCount === 0"
                    :class="['inline-flex items-center justify-center gap-2 rounded-lg px-4 py-3 text-base font-semibold shadow-sm transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800',
                        (isAddingAll || allDone || selectedCount === 0)
                            ? 'cursor-not-allowed bg-gray-300 text-gray-500 dark:bg-gray-600 dark:text-gray-400'
                            : 'bg-[var(--brand-button-bg)] text-white hover:bg-[var(--brand-button-bg-hover)] hover:shadow-md']">
                <svg v-if="allDone" class="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
                <span v-text="addLabel"></span>
            </button>
        </div>
        @endunless

</form>

<script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function() {
        // Initialize flatpickr for any existing datepickers on page load
        initializeFlatpickr();
    });

    // Function to initialize flatpickr on date-only datepicker elements
    function initializeFlatpickr() {
        var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
        var localeConfig = fpLocale ? { locale: fpLocale } : {};

        // Select only date inputs (datepicker_date_X class)
        document.querySelectorAll('[class*="datepicker_date_"]').forEach(element => {
            // Destroy existing flatpickr instance if it exists
            if (element._flatpickr) {
                element._flatpickr.destroy();
            }

            // Extract index from class name for Vue model sync
            var classMatch = element.className.match(/datepicker_date_(\d+)/);
            var idx = classMatch ? parseInt(classMatch[1], 10) : null;

            var f = flatpickr(element, Object.assign({
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
                onChange: function(selectedDates, dateStr) {
                    // Sync flatpickr selection back to Vue model
                    if (idx !== null && window.__importApp && window.__importApp.preview && window.__importApp.preview.parsed[idx]) {
                        window.__importApp.preview.parsed[idx].event_date = dateStr;
                    }
                },
            }, localeConfig));

            // Prevent keyboard input as per edit view
            if (f && f._input) {
                f._input.onkeydown = () => false;
            }
        });
    }

    const { createApp } = Vue

    var app = createApp({
        data() {
            return {
                honeypot: '',
                eventDetails: '',
                preview: null,
                isLoading: false,
                isUploadingImage: null,
                isUploadingDetailsImage: false,
                errorMessage: null,
                savedEvents: [],
                savedEventData: [],
                saveErrors: [],
                isDragging: false,
                isDraggingDetails: false,
                dragCounter: 0,
                dragTimeout: null,
                dragStartTime: 0,
                isDragActive: false,
                showAllFields: false,
                importFields: @json($role->import_config['fields'] ?? []),
                requiredFields: @json($role->import_config['required_fields'] ?? []),
                groups: @json(($role->groups ?? collect())->map(fn($g) => ['id' => \App\Utils\UrlUtils::encodeId($g->id), 'name' => $g->translatedName()])),
                descriptionEditors: {},
                isCurator: {{ $role->isCurator() ? 'true' : 'false' }},
                detailsImage: null,
                detailsImageUrl: null,
                currentRequestId: null,
                // Links are an editor's: the guest submission form shares this component.
                isGuestPage: {{ $isGuestPage ? 'true' : 'false' }},
                // No AI key on this install: the box takes links only.
                linksOnly: {{ $linksOnly ? 'true' : 'false' }},
                readingHost: '',
                isSlow: false,
                slowTimer: null,
                // The list a preview of two or more becomes.
                selectedRows: [],
                expandedRow: null,
                isAddingAll: false,
                addProgress: { done: 0, total: 0 },
                addSummary: null,
                stoppedByLimit: false,
                allDone: false,
                savingEvents: [], // Track which events are currently being saved
                createAccount: false, // New data property for guest user account creation
                userName: '',
                userEmail: '',
                userPassword: '',
                venues: @json($venues ?? []),
                eventVenueTypes: [],      // 'use_existing' | 'create_new' per event
                eventSelectedVenues: [],  // selected venue object per event
                eventClaimVenue: [],      // bool per event: claim ownership of a venue nobody owns yet
                // Time picker dropdown
                activeTimeDropdown: null,
                highlightedTimeIndex: {},
                timeFilter: {},
                timeOptions: [],
            }
        },

        mounted() {
            // Component is mounted and ready
            // Show the form now that Vue.js has loaded
            this.$nextTick(() => {
                document.getElementById('event-import-app').classList.add('loaded');
            });
        },

        computed: {
            canSubmit() {
                if (this.linksOnly) {
                    return this.isLink;
                }

                return this.eventDetails.trim() || this.detailsImage;
            },

            // The box holds one web address and nothing else, so it is read as a link rather than
            // as text. Decided here, by what is in the box: the server never looks for an address
            // inside pasted text.
            isLink() {
                if (this.isGuestPage || this.detailsImage) {
                    return false;
                }
                const text = this.eventDetails.trim();
                if (! text || /\s/.test(text)) {
                    return false;
                }

                return /^(https?|webcals?):\/\/\S+\.\S+/i.test(text)
                    // "venue.com/events" without the scheme. The last label has to be letters, or
                    // "8.30pm" would count.
                    || /^(?:[a-z0-9-]+\.)+[a-z]{2,}(?:[\/?#]\S*)?$/i.test(text);
            },

            linkUrl() {
                const text = this.eventDetails.trim();

                return /^[a-z][a-z0-9+.-]*:\/\//i.test(text) ? text : 'https://' + text;
            },

            // Where the link goes, without the scheme: what the person recognises.
            linkLabel() {
                return this.linkUrl.replace(/^[a-z][a-z0-9+.-]*:\/\//i, '').replace(/\/$/, '');
            },

            linkHost() {
                return this.linkLabel.split(/[\/?#]/)[0];
            },

            submitLabel() {
                if (this.isLink) {
                    return @json(__('messages.import_read_link'), JSON_UNESCAPED_UNICODE);
                }
                if (this.detailsImage && ! this.eventDetails.trim()) {
                    return @json(__('messages.import_read_flyer'), JSON_UNESCAPED_UNICODE);
                }

                return this.linksOnly
                    ? @json(__('messages.import_read_link'), JSON_UNESCAPED_UNICODE)
                    : @json(__('messages.import_read_text'), JSON_UNESCAPED_UNICODE);
            },

            readingLabel() {
                return this.readingHost
                    ? @json(__('messages.import_reading_host', ['host' => '__HOST__']), JSON_UNESCAPED_UNICODE).replace('__HOST__', this.readingHost)
                    : @json(__('messages.import_reading'), JSON_UNESCAPED_UNICODE);
            },

            memberVenues() {
                return this.venues.filter(v => v.is_member);
            },

            followingVenues() {
                return this.venues.filter(v => !v.is_member && !v.is_connected);
            },

            connectedVenues() {
                return this.venues.filter(v => !v.is_member && v.is_connected);
            },

            venueGroupCount() {
                return [this.memberVenues, this.followingVenues, this.connectedVenues]
                    .filter(group => group.length > 0).length;
            },

            // Two or more events, for an editor: a list to choose from rather than a stack of forms.
            listMode() {
                return ! this.isGuestPage && !! (this.preview && this.preview.parsed && this.preview.parsed.length > 1);
            },

            // Rows that could be added: complete, and not added already.
            selectableCount() {
                return this.listMode ? this.preview.parsed.filter((event, idx) => ! this.savedEvents[idx] && this.isEventComplete(event)).length : 0;
            },

            selectedCount() {
                return this.listMode ? this.preview.parsed.filter((event, idx) => this.selectedRows[idx] && ! this.savedEvents[idx]).length : 0;
            },

            allSelected() {
                return this.selectableCount > 0 && this.selectedCount === this.selectableCount;
            },

            selectedLabel() {
                return @json(__('messages.import_selected_count', ['selected' => '__S__', 'total' => '__T__']), JSON_UNESCAPED_UNICODE)
                    .replace('__S__', this.selectedCount).replace('__T__', this.preview.parsed.length - this.savedEvents.filter(Boolean).length);
            },

            addLabel() {
                if (this.allDone) {
                    return @json(__('messages.import_added_all', ['count' => '__N__']), JSON_UNESCAPED_UNICODE).replace('__N__', this.savedEvents.filter(Boolean).length);
                }

                // While it works the button keeps the number it was pressed with; the bar beside
                // it is what counts down.
                const count = this.isAddingAll ? this.addProgress.total : this.selectedCount;

                return count === 1
                    ? @json(__('messages.import_add_one'), JSON_UNESCAPED_UNICODE)
                    : @json(__('messages.import_add_many', ['count' => '__N__']), JSON_UNESCAPED_UNICODE).replace('__N__', count);
            },

            progressLabel() {
                return @json(__('messages.import_saving_progress', ['done' => '__D__', 'total' => '__T__']), JSON_UNESCAPED_UNICODE)
                    .replace('__D__', Math.min(this.addProgress.done + 1, this.addProgress.total)).replace('__T__', this.addProgress.total);
            },

            summaryLabel() {
                if (! this.addSummary) {
                    return '';
                }
                let label = this.addSummary.failed
                    ? @json(__('messages.import_added_summary', ['added' => '__A__', 'failed' => '__F__']), JSON_UNESCAPED_UNICODE)
                        .replace('__A__', this.addSummary.added).replace('__F__', this.addSummary.failed)
                    : @json(__('messages.import_added_all', ['count' => '__A__']), JSON_UNESCAPED_UNICODE).replace('__A__', this.addSummary.added);
                if (this.stoppedByLimit) {
                    label += ' ' + @json(__('messages.import_stopped_daily_limit'), JSON_UNESCAPED_UNICODE);
                }

                return label;
            },

            foundLabel() {
                const meta = this.preview.meta;
                const count = meta ? meta.shown : this.preview.parsed.length;

                // The host is isolated (LRI ... PDI): inside a right-to-left sentence it otherwise
                // takes the count that follows it into its own left-to-right run.
                return meta && meta.host
                    ? @json(__('messages.import_found_on_host', ['host' => '__H__', 'count' => '__N__']), JSON_UNESCAPED_UNICODE).replace('__H__', '\u2066' + meta.host + '\u2069').replace('__N__', count)
                    : @json(__('messages.import_found_events', ['count' => '__N__']), JSON_UNESCAPED_UNICODE).replace('__N__', count);
            },

            // The zone the times are in is said out loud: a wrong one is otherwise invisible until
            // every event is an hour or three off.
            timezoneLabel() {
                const zone = (this.preview.meta && this.preview.meta.timezone) || @json($role->captureTimezone());

                return @json(__('messages.import_times_shown_in', ['timezone' => '__Z__']), JSON_UNESCAPED_UNICODE).replace('__Z__', '\u2066' + zone.replace(/_/g, ' ') + '\u2069');
            },

            // What the person should know before choosing: what was left out and why, and that a
            // link is copied once, not followed.
            listNotes() {
                const meta = this.preview.meta;
                if (! meta) {
                    return [];
                }
                const notes = [];
                if (meta.already_on_schedule > 0) {
                    notes.push(@json(__('messages.import_already_on_schedule', ['count' => '__N__']), JSON_UNESCAPED_UNICODE).replace('__N__', meta.already_on_schedule));
                }
                if (meta.found - meta.already_on_schedule > meta.shown) {
                    notes.push(@json(__('messages.import_showing_next', ['shown' => '__S__', 'found' => '__F__']), JSON_UNESCAPED_UNICODE)
                        .replace('__S__', meta.shown).replace('__F__', meta.found - meta.already_on_schedule));
                }
                if (meta.text_truncated) {
                    notes.push(@json(__('messages.import_text_truncated'), JSON_UNESCAPED_UNICODE));
                }
                notes.push(@json(__('messages.import_one_time_copy'), JSON_UNESCAPED_UNICODE));

                return notes;
            },

            canCreateAccount() {
                // Always check event fields regardless of createAccount status
                const eventFieldsValid = this.preview?.parsed?.every(event => this.isEventComplete(event)) || false;
                
                // If createAccount is checked, also validate user fields
                if (this.createAccount) {
                    // Email validation regex
                    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
                    const isEmailValid = emailRegex.test(this.userEmail.trim());
                    
                    return eventFieldsValid && 
                           this.userName.trim() && 
                           this.userEmail.trim() && 
                           isEmailValid &&
                           this.userPassword;
                }
                
                // If createAccount is not checked, only validate event fields
                return eventFieldsValid;
            },
        },

                    created() {
                this.loadShowAllFieldsPreference()
                this.timeOptions = this.generateTimeOptions()

                // Add global drag event listeners
                document.addEventListener('dragend', this.handleGlobalDragEnd)
                document.addEventListener('dragstart', this.handleGlobalDragStart)
                
                // Add mouse move listener to track when we're actually outside the drop zone
                document.addEventListener('mousemove', this.handleMouseMove)
            },
        
        beforeUnmount() {
            // Clean up event listeners when component is destroyed
            document.removeEventListener('dragend', this.handleGlobalDragEnd)
            document.removeEventListener('dragstart', this.handleGlobalDragStart)
            document.removeEventListener('mousemove', this.handleMouseMove)
            
            // Clear any pending timeouts
            if (this.dragTimeout) {
                clearTimeout(this.dragTimeout);
                this.dragTimeout = null;
            }
        },

        updated() {
            this.$nextTick(() => {
                // Call the global function to initialize flatpickr
                initializeFlatpickr();
            });
        },

        watch: {
            showAllFields(newVal) {
                if (newVal && this.preview) {
                    this.$nextTick(() => {
                        this.initDescriptionEditors();
                    });
                }
            },
        },

        methods: {
            toggleImportMultiselect(idx, fieldKey, option, event) {
                const current = (this.preview.parsed[idx].custom_field_values[fieldKey] || '').split(', ').map(s => s.trim()).filter(s => s);
                if (event.target.checked) {
                    if (!current.includes(option)) current.push(option);
                } else {
                    const i = current.indexOf(option);
                    if (i > -1) current.splice(i, 1);
                }
                this.preview.parsed[idx].custom_field_values[fieldKey] = current.join(', ');
            },
            // Time picker dropdown methods
            generateTimeOptions() {
                var options = [];
                var use24hr = {{ $use24hr ? 'true' : 'false' }};
                for (var m = 0; m < 1440; m += 30) {
                    var h = Math.floor(m / 60);
                    var min = m % 60;
                    var label;
                    if (use24hr) {
                        label = (h < 10 ? '0' : '') + h + ':' + (min < 10 ? '0' : '') + min;
                    } else {
                        var period = h < 12 ? 'AM' : 'PM';
                        var h12 = h % 12 || 12;
                        label = h12 + ':' + (min < 10 ? '0' : '') + min + ' ' + period;
                    }
                    options.push(label);
                }
                return options;
            },

            openTimeDropdown(idx, type) {
                if (this.savedEvents[idx]) return;
                var key = idx + '-' + type;
                this.activeTimeDropdown = key;
                this.timeFilter[key] = '';
                var self = this;
                this.$nextTick(() => {
                    // Use setTimeout to ensure DOM is fully laid out after display:block
                    setTimeout(() => {
                        self.scrollToNearestTime(idx, type);
                    }, 0);
                });
            },

            closeTimeDropdown(idx, type) {
                var key = idx + '-' + type;
                // Small delay to allow mousedown to register before closing
                setTimeout(() => {
                    if (this.activeTimeDropdown === key) {
                        this.activeTimeDropdown = null;
                        this.highlightedTimeIndex[key] = -1;
                    }
                }, 150);
            },

            filterTimeOptions(idx, type) {
                var key = idx + '-' + type;
                var value = type === 'start'
                    ? this.preview.parsed[idx].event_start_time
                    : this.preview.parsed[idx].event_end_time;
                this.timeFilter[key] = value || '';
            },

            selectTime(idx, type, time) {
                if (type === 'start') {
                    this.preview.parsed[idx].event_start_time = time;
                } else {
                    this.preview.parsed[idx].event_end_time = time;
                }
                this.activeTimeDropdown = null;
                var key = idx + '-' + type;
                this.timeFilter[key] = '';
            },

            scrollToNearestTime(idx, type) {
                var key = idx + '-' + type;
                var refKey = 'time_dropdown_' + idx + '_' + type;
                var dropdownEl = this.$refs[refKey];
                // Vue 3 dynamic refs inside v-for return arrays
                if (Array.isArray(dropdownEl)) dropdownEl = dropdownEl[0];
                if (!dropdownEl) return;

                var value = type === 'start'
                    ? this.preview.parsed[idx].event_start_time
                    : this.preview.parsed[idx].event_end_time;

                // Normalize the value to match timeOptions format
                var minutes = this.parseTimeToMinutes(value);
                if (minutes !== null) {
                    var normalizedValue = this.formatMinutesToTime(minutes);
                    var exactIndex = this.timeOptions.indexOf(normalizedValue);
                    if (exactIndex !== -1) {
                        var items = dropdownEl.querySelectorAll('.time-dropdown-item-import');
                        if (items[exactIndex]) {
                            items[exactIndex].scrollIntoView({ block: 'center' });
                            this.highlightedTimeIndex[key] = exactIndex;
                        }
                        return;
                    }
                }

                // Fall back to nearest 30-min slot
                if (minutes === null) minutes = type === 'start' ? 540 : 600; // Default 9am or 10am

                var closest = Math.round(minutes / 30) * 30;
                if (closest >= 1440) closest = 0;
                var targetTime = this.formatMinutesToTime(closest);
                var targetIndex = this.timeOptions.indexOf(targetTime);

                if (targetIndex !== -1) {
                    var items = dropdownEl.querySelectorAll('.time-dropdown-item-import');
                    if (items[targetIndex]) {
                        items[targetIndex].scrollIntoView({ block: 'center' });
                        this.highlightedTimeIndex[key] = targetIndex;
                    }
                }
            },

            handleTimeKeydown(e, idx, type) {
                var key = idx + '-' + type;
                if (this.activeTimeDropdown !== key) return;

                var visibleOptions = this.getVisibleTimeOptions(key);
                var currentHighlight = this.highlightedTimeIndex[key] || 0;

                if (e.key === 'ArrowDown') {
                    e.preventDefault();
                    var nextIndex = currentHighlight + 1;
                    if (nextIndex >= visibleOptions.length) nextIndex = 0;
                    this.highlightedTimeIndex[key] = nextIndex;
                    this.scrollHighlightedIntoView(idx, type);
                } else if (e.key === 'ArrowUp') {
                    e.preventDefault();
                    var prevIndex = currentHighlight - 1;
                    if (prevIndex < 0) prevIndex = visibleOptions.length - 1;
                    this.highlightedTimeIndex[key] = prevIndex;
                    this.scrollHighlightedIntoView(idx, type);
                } else if (e.key === 'Enter') {
                    e.preventDefault();
                    if (visibleOptions[currentHighlight]) {
                        this.selectTime(idx, type, visibleOptions[currentHighlight]);
                    }
                } else if (e.key === 'Escape' || e.key === 'Tab') {
                    this.activeTimeDropdown = null;
                }
            },

            getVisibleTimeOptions(key) {
                var filter = (this.timeFilter[key] || '').toLowerCase();
                if (!filter) return this.timeOptions;
                return this.timeOptions.filter(t => t.toLowerCase().includes(filter));
            },

            scrollHighlightedIntoView(idx, type) {
                var refKey = 'time_dropdown_' + idx + '_' + type;
                var dropdownEl = this.$refs[refKey];
                // Vue 3 dynamic refs inside v-for return arrays
                if (Array.isArray(dropdownEl)) dropdownEl = dropdownEl[0];
                if (!dropdownEl) return;

                var key = idx + '-' + type;
                var highlightIdx = this.highlightedTimeIndex[key];
                var items = dropdownEl.querySelectorAll('.time-dropdown-item-import:not([style*="display: none"])');
                if (items[highlightIdx]) {
                    items[highlightIdx].scrollIntoView({ block: 'nearest' });
                }
            },

            parseTimeToMinutes(timeStr) {
                if (!timeStr) return null;
                timeStr = timeStr.trim();
                var use24hr = {{ $use24hr ? 'true' : 'false' }};

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

                // Try bare HH:mm in 12hr mode
                if (!use24hr && match24) {
                    var h = parseInt(match24[1], 10);
                    var m = parseInt(match24[2], 10);
                    if (h >= 0 && h <= 23 && m >= 0 && m <= 59) return h * 60 + m;
                }

                return null;
            },

            formatMinutesToTime(minutes) {
                var use24hr = {{ $use24hr ? 'true' : 'false' }};
                var h = Math.floor(minutes / 60) % 24;
                var m = minutes % 60;
                if (use24hr) {
                    return (h < 10 ? '0' : '') + h + ':' + (m < 10 ? '0' : '') + m;
                } else {
                    var period = h < 12 ? 'AM' : 'PM';
                    var h12 = h % 12 || 12;
                    return h12 + ':' + (m < 10 ? '0' : '') + m + ' ' + period;
                }
            },

            onStartTimeBlur(idx) {
                var event = this.preview.parsed[idx];
                var startMin = this.parseTimeToMinutes(event.event_start_time);
                if (startMin !== null) {
                    event.event_start_time = this.formatMinutesToTime(startMin);
                } else if (event.event_start_time.trim() !== '') {
                    event.event_start_time = '';
                }
            },

            onEndTimeBlur(idx) {
                var event = this.preview.parsed[idx];
                var endMin = this.parseTimeToMinutes(event.event_end_time);
                if (endMin !== null) {
                    event.event_end_time = this.formatMinutesToTime(endMin);
                } else if (event.event_end_time.trim() !== '') {
                    event.event_end_time = '';
                }
            },

            handleKeydown(event) {
                // Handle Enter key for form submission
                if (event.key === 'Enter') {
                    if (event.ctrlKey) {
                        // Ctrl+Enter: submit the form
                        event.preventDefault();
                        this.handleSubmit();
                    } else {
                        // Enter: allow default behavior (new line)
                        return;
                    }
                }
            },

            handleInputChange() {
                // Just update the model, don't auto-submit
                // The submit button will be enabled/disabled based on canSubmit computed property

                // What went wrong was about what the box held. Editing it is the next try.
                if (! this.isGuestPage) {
                    this.errorMessage = null;
                }
            },

            handleSubmit() {
                if (this.canSubmit) {
                    this.fetchPreview();
                }
            },

            playNotificationChime() {
                try {
                    const ctx = new (window.AudioContext || window.webkitAudioContext)();
                    if (ctx.state === 'suspended') ctx.resume();
                    const playTone = (freq, startTime, duration) => {
                        const osc = ctx.createOscillator();
                        const gain = ctx.createGain();
                        osc.connect(gain);
                        gain.connect(ctx.destination);
                        osc.frequency.value = freq;
                        osc.type = 'sine';
                        gain.gain.setValueAtTime(0.3, startTime);
                        gain.gain.exponentialRampToValueAtTime(0.01, startTime + duration);
                        osc.start(startTime);
                        osc.stop(startTime + duration);
                    };
                    playTone(523.25, ctx.currentTime, 0.15);
                    playTone(659.25, ctx.currentTime + 0.15, 0.2);
                    setTimeout(() => ctx.close(), 1000);
                } catch (e) {
                    // Web Audio not supported
                }
            },

            notifyParseComplete() {
                this.playNotificationChime();
            },

            // Whether one event has what the save needs. Asked per row: one incomplete row used to
            // disable Save on every card, with nothing saying why.
            isEventComplete(event) {
                const hasName = event.event_name?.trim();
                const hasDate = event.event_date && event.event_start_time;

                // Any venue field (name, address, or city) is sufficient. A row read from a link
                // needs none: the server does not ask for one, and a feed often has no location.
                const hasVenueInfo = event.venue_name?.trim() ||
                                     event.event_address?.trim() ||
                                     event.event_city?.trim() ||
                                     ({{ $role->isCurator() ? 'true' : 'false' }} && @json($role->city)) ||
                                     !! (this.preview && this.preview.meta);

                // Check required import fields
                if (this.requiredFields.short_description && !event.short_description?.trim()) return false;
                if (this.requiredFields.description && !event.event_details?.trim()) return false;
                if (this.requiredFields.ticket_price && !event.ticket_price) return false;
                if (this.requiredFields.coupon_code && !event.coupon_code?.trim()) return false;
                if (this.requiredFields.registration_url && !event.registration_url?.trim()) return false;
                if (this.requiredFields.category_id && !event.category_id) return false;
                if (this.requiredFields.group_id && !event.group_id) return false;

                return !! (hasName && hasVenueInfo && hasDate);
            },

            // A card's own Save: its own row for an editor, everything (and the account fields)
            // for a guest, who has one event.
            canSaveRow(idx) {
                return this.isGuestPage ? this.canCreateAccount : this.isEventComplete(this.preview.parsed[idx]);
            },

            // The dates of a series that could not be one repeating event arrive as separate
            // rows sharing series.id. The list shows the first and treats them as one.
            rowIndexes(idx) {
                const series = this.preview.parsed[idx] && this.preview.parsed[idx].series;
                if (! series) {
                    return [idx];
                }

                return this.preview.parsed.map((event, i) => i).filter(i => this.preview.parsed[i].series && this.preview.parsed[i].series.id === series.id);
            },

            isRowVisible(idx) {
                const series = this.preview.parsed[idx].series;

                return ! series || series.position === 1;
            },

            rowSelected(idx) {
                return this.rowIndexes(idx).some(i => this.selectedRows[i]);
            },

            rowComplete(idx) {
                return this.rowIndexes(idx).every(i => this.isEventComplete(this.preview.parsed[i]));
            },

            rowState(idx) {
                const indexes = this.rowIndexes(idx);
                if (indexes.some(i => this.savingEvents[i])) return 'saving';
                if (indexes.every(i => this.savedEvents[i])) return 'saved';
                if (indexes.some(i => this.saveErrors[i])) return 'error';

                return 'idle';
            },

            toggleRow(idx) {
                const on = ! this.rowSelected(idx);
                this.rowIndexes(idx).forEach(i => {
                    this.selectedRows[i] = on && ! this.savedEvents[i] && this.isEventComplete(this.preview.parsed[i]);
                });
            },

            toggleAll() {
                const on = ! this.allSelected;
                this.preview.parsed.forEach((event, i) => {
                    this.selectedRows[i] = on && ! this.savedEvents[i] && this.isEventComplete(event);
                });
            },

            // What is ticked when a preview arrives: everything that could be added as it stands
            // and does not look like something the schedule already has.
            resetSelection() {
                this.selectedRows = this.preview.parsed.map(event => this.isEventComplete(event) && ! event.event_url);
                this.expandedRow = null;
                this.addSummary = null;
                this.stoppedByLimit = false;
                this.allDone = false;
            },

            parseLocalDate(value) {
                const parts = /^(\d{4})-(\d{2})-(\d{2})/.exec(value || '');

                return parts ? new Date(parseInt(parts[1]), parseInt(parts[2]) - 1, parseInt(parts[3])) : null;
            },

            // The row's date chip. A repeating event shows when it next happens, not the first
            // date it ever had.
            rowDate(idx) {
                const event = this.preview.parsed[idx];
                const date = this.parseLocalDate(event.recurrence && event.sort_at ? event.sort_at : event.event_date);
                if (! date) {
                    return { month: '', day: '?' };
                }
                const locale = document.documentElement.lang || undefined;

                return { month: date.toLocaleDateString(locale, { month: 'short' }), day: String(date.getDate()) };
            },

            shortDate(value) {
                const date = this.parseLocalDate(value);

                return date ? date.toLocaleDateString(document.documentElement.lang || undefined, { day: 'numeric', month: 'short' }) : '';
            },

            repeatLabel(recurrence) {
                const locale = document.documentElement.lang || undefined;
                // 1 January 2023 was a Sunday, which is day 0.
                const days = (recurrence.days || []).map(day => new Date(2023, 0, 1 + day).toLocaleDateString(locale, { weekday: 'short' })).join(', ');
                let label = '';
                if (recurrence.frequency === 'daily') {
                    label = @json(__('messages.import_repeats_daily'), JSON_UNESCAPED_UNICODE);
                } else if (recurrence.frequency === 'weekly') {
                    label = @json(__('messages.import_repeats_weekly', ['days' => '__D__']), JSON_UNESCAPED_UNICODE).replace('__D__', days);
                } else if (recurrence.frequency === 'every_n_weeks') {
                    label = @json(__('messages.import_repeats_every_n_weeks', ['count' => '__N__', 'days' => '__D__']), JSON_UNESCAPED_UNICODE).replace('__N__', recurrence.interval).replace('__D__', days);
                } else if (recurrence.frequency === 'yearly') {
                    label = @json(__('messages.import_repeats_yearly'), JSON_UNESCAPED_UNICODE);
                } else {
                    label = @json(__('messages.import_repeats_monthly'), JSON_UNESCAPED_UNICODE);
                }
                if (recurrence.until) {
                    label += ' ' + @json(__('messages.import_repeats_until', ['date' => '__U__']), JSON_UNESCAPED_UNICODE).replace('__U__', this.shortDate(recurrence.until));
                }

                return label;
            },

            // The line under the name: when, where, and how it repeats.
            rowMeta(idx) {
                const event = this.preview.parsed[idx];
                const parts = [];

                parts.push(event.is_all_day ? @json(__('messages.import_all_day'), JSON_UNESCAPED_UNICODE) : (event.event_start_time || ''));
                if (event.recurrence) {
                    parts.push(this.repeatLabel(event.recurrence));
                } else if (event.series) {
                    const dates = this.rowIndexes(idx);
                    const last = this.preview.parsed[dates[dates.length - 1]];
                    parts.push(@json(__('messages.import_series_dates', ['count' => '__N__', 'date' => '__D__']), JSON_UNESCAPED_UNICODE)
                        .replace('__N__', dates.length).replace('__D__', this.shortDate(last.event_date)));
                }
                const venue = (this.eventVenueTypes[idx] === 'use_existing' && this.eventSelectedVenues[idx])
                    ? this.eventSelectedVenues[idx].name
                    : event.venue_name;
                if (venue) {
                    parts.push(venue);
                }
                if (event.local_time_zone) {
                    parts.push(@json(__('messages.import_local_time', ['timezone' => '__Z__']), JSON_UNESCAPED_UNICODE).replace('__Z__', String(event.local_time_zone).replace(/_/g, ' ')));
                }

                // Each part keeps its own direction (FSI ... PDI) and the parts keep the page's order.
                return parts.filter(Boolean).map(part => '\u2068' + part + '\u2069').join(' \u00B7 ');
            },

            // Why a row is not ticked or did not save, said on the row.
            rowProblem(idx) {
                const indexes = this.rowIndexes(idx);
                const failed = indexes.find(i => this.saveErrors[i]);
                if (failed !== undefined) {
                    return this.saveErrors[failed];
                }
                if (this.rowState(idx) === 'saved') {
                    return '';
                }
                if (! this.rowComplete(idx)) {
                    return @json(__('messages.import_row_incomplete'), JSON_UNESCAPED_UNICODE);
                }
                if (this.preview.parsed[idx].event_url) {
                    return @json(__('messages.import_already_listed'), JSON_UNESCAPED_UNICODE);
                }

                return '';
            },

            // Open one row's full card in place, and close whichever was open. The card's date
            // pickers and editor exist only while it is open.
            expandRow(idx) {
                this.destroyDescriptionEditors();
                this.expandedRow = this.expandedRow === idx ? null : idx;

                if (this.expandedRow === null) {
                    return;
                }

                this.$nextTick(() => {
                    initializeFlatpickr();
                    this.initDescriptionEditors();
                    const venue = this.eventSelectedVenues[idx];
                    const select = document.getElementById('selected_venue_' + idx);
                    if (venue && select) {
                        select.value = venue.id;
                    }
                    // Looked up now, for the one event on screen, instead of for every row at once.
                    const performer = this.preview.parsed[idx].performers && this.preview.parsed[idx].performers[0];
                    if (performer && ! performer.talent_id && performer.videos === null && ! performer.searching) {
                        this.searchVideos(idx, 0);
                    }
                });
            },

            // Add every ticked row, one after another, saying how far along it is.
            async addSelected() {
                if (this.isAddingAll) {
                    return;
                }
                const queue = this.preview.parsed.map((event, i) => i).filter(i => this.selectedRows[i] && ! this.savedEvents[i]);
                if (! queue.length) {
                    return;
                }

                this.destroyDescriptionEditors();
                this.expandedRow = null;
                this.errorMessage = null;
                this.isAddingAll = true;
                this.addSummary = null;
                this.stoppedByLimit = false;
                this.addProgress = { done: 0, total: queue.length };
                window.onbeforeunload = () => true;

                let added = 0;
                let failed = 0;
                for (const idx of queue) {
                    let result = await this.handleSave(idx, true);
                    // The request limit is sized so a full import stays under it. If it is met
                    // anyway, wait it out once rather than fail every row after it.
                    if (! result.ok && result.status === 429) {
                        await new Promise(resolve => setTimeout(resolve, Math.min(result.retryAfter || 60, 65) * 1000));
                        result = await this.handleSave(idx, true);
                    }
                    if (result.ok) {
                        added++;
                        this.selectedRows[idx] = false;
                    } else {
                        failed++;
                        if (result.limit) {
                            // Today's allowance is used up: every row after this would fail the same way.
                            this.stoppedByLimit = true;
                            break;
                        }
                    }
                    this.addProgress.done++;
                    await new Promise(resolve => setTimeout(resolve, 150));
                }

                window.onbeforeunload = null;
                this.isAddingAll = false;
                this.addSummary = { added, failed };

                // Everything asked for is on the schedule: show that for a beat, then go and see it.
                if (failed === 0 && ! this.stoppedByLimit) {
                    this.allDone = true;
                    setTimeout(() => {
                        window.location.href = @json(route('event.import_done', ['subdomain' => $role->subdomain]));
                    }, 900);
                }
            },

            cancelReading() {
                // The answer, when it comes, is for a request nobody is waiting on any more.
                this.currentRequestId = null;
                this.isLoading = false;
                this.stopSlowTimer();
            },

            stopSlowTimer() {
                clearTimeout(this.slowTimer);
                this.slowTimer = null;
                this.isSlow = false;
            },

            async fetchPreview(wholePage = false) {
                if (!this.eventDetails.trim() && !this.detailsImage) {
                    this.preview = null;
                    return;
                }

                const readingLink = this.isLink;
                this.readingHost = readingLink ? this.linkHost : '';
                this.stopSlowTimer();
                this.slowTimer = setTimeout(() => { this.isSlow = true; }, 5000);

                this.isLoading = true;
                this.preview = null;
                this.errorMessage = null;
                this.savedEvents = [];
                this.savedEventData = [];
                this.saveErrors = [];
                
                // Create a unique request ID to track the latest request
                const requestId = Date.now();
                this.currentRequestId = requestId;
                
                // Don't clear preview immediately - we'll only update it if this is still the latest request
                // when the response comes back
                
                try {
                    const formData = new FormData();
                    @if ($isGuestPage)
                    {{-- The guest form sends text and nothing else: no link field exists for it to send. --}}
                    formData.append('event_details', this.eventDetails);
                    formData.append('website', this.honeypot);
                    @else
                    if (readingLink) {
                        formData.append('source_url', this.linkUrl);
                        if (wholePage) {
                            formData.append('source_mode', 'page');
                        }
                    } else {
                        formData.append('event_details', this.eventDetails);
                    }
                    @endif
                    if (this.detailsImage) {
                        formData.append('details_image', this.detailsImage);
                    }

                    const response = await fetch('{{ isset($isGuest) && $isGuest ? route("event.guest_parse", ["subdomain" => $role->subdomain]) : route("event.parse", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    // If this is no longer the latest request, ignore the response
                    if (this.currentRequestId !== requestId) {
                        return;
                    }

                    // The server's own message first: it is written for the person and already in
                    // their language. Only an answer that carries none gets a general one.
                    let data = null;
                    try {
                        data = await response.json();
                    } catch (e) {
                        data = null;
                    }

                    if (!response.ok) {
                        if (data && data.errors) {
                            throw new Error(Object.values(data.errors).flat().join('\n'));
                        }
                        if (data && data.error) {
                            throw new Error(data.error);
                        }
                        if (response.status === 429) {
                            throw new Error(@json(__('messages.ai_rate_limit'), JSON_UNESCAPED_UNICODE));
                        }
                        if (response.status === 401 || response.status === 403 || response.status === 419) {
                            throw new Error(@json(__('messages.not_authorized'), JSON_UNESCAPED_UNICODE));
                        }
                        throw new Error(@json(__('messages.error_occurred'), JSON_UNESCAPED_UNICODE));
                    }

                    if (data === null) {
                        throw new Error(@json(__('messages.error_occurred'), JSON_UNESCAPED_UNICODE));
                    }

                    // Ensure preview.parsed is always an array            
                    // TODO: remove this, it shouldn't be needed
                    if (data && data.parsed && !Array.isArray(data.parsed)) {
                        data.parsed = [data.parsed];
                    } else if (data && Array.isArray(data)) {
                        data = { parsed: data };
                    }

                    // For guest users, only show the first event if multiple are parsed
                    if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }} && data.parsed && data.parsed.length > 1) {
                        data.parsed = [data.parsed[0]];
                    }

                    // Now that we have valid data and this is still the latest request, update the preview
                    this.preview = data;
                    
                    // Initialize arrays to track saved events and errors
                    if (Array.isArray(this.preview.parsed)) {
                        this.savedEvents = new Array(this.preview.parsed.length).fill(false);
                        this.savedEventData = new Array(this.preview.parsed.length).fill(null);
                        this.saveErrors = new Array(this.preview.parsed.length).fill(false);
                        this.savingEvents = new Array(this.preview.parsed.length).fill(false);

                        // Initialize venue selection arrays
                        this.eventVenueTypes = [];
                        this.eventSelectedVenues = [];
                        this.eventClaimVenue = [];

                        // Split event_date_time into separate date, start time, end time fields
                        this.preview.parsed.forEach((event) => {
                            if (event.event_date_time) {
                                try {
                                    var dt = new Date(event.event_date_time.replace(' ', 'T'));
                                    if (!isNaN(dt.getTime())) {
                                        var y = dt.getFullYear();
                                        var mo = ('0' + (dt.getMonth() + 1)).slice(-2);
                                        var d = ('0' + dt.getDate()).slice(-2);
                                        event.event_date = y + '-' + mo + '-' + d;
                                        var h = dt.getHours();
                                        var m = dt.getMinutes();
                                        event.event_start_time = this.formatMinutesToTime(h * 60 + m);
                                        if (event.event_duration) {
                                            var endMin = h * 60 + m + Math.round(event.event_duration * 60);
                                            event.event_end_time = this.formatMinutesToTime(endMin % 1440);
                                        } else {
                                            event.event_end_time = '';
                                        }
                                    } else {
                                        event.event_date = '';
                                        event.event_start_time = '';
                                        event.event_end_time = '';
                                    }
                                } catch(e) {
                                    event.event_date = '';
                                    event.event_start_time = '';
                                    event.event_end_time = '';
                                }
                                delete event.event_date_time;
                            } else {
                                if (!event.event_date) event.event_date = '';
                                if (!event.event_start_time) event.event_start_time = '';
                                if (!event.event_end_time) event.event_end_time = '';
                            }
                        });

                        this.preview.parsed.forEach((event, idx) => {
                            let matchedUserVenue = null;
                            if (event.venue_id) {
                                // Check if venue is already in the list (connected or previously added)
                                matchedUserVenue = this.venues.find(v => v.id === event.venue_id);
                            }

                            // If venue matched but not in user's venues list, add it
                            if (event.venue_id && !matchedUserVenue && event.matched_venue_name) {
                                const matchedVenue = {
                                    id: event.venue_id,
                                    name: event.matched_venue_name,
                                    subdomain: event.venue_subdomain,
                                    is_claimable: !!event.venue_is_claimable,
                                    _matched: true  // Flag to identify matched venues
                                };
                                this.venues.push(matchedVenue);
                                matchedUserVenue = matchedVenue;
                            }

                            if (matchedUserVenue) {
                                this.eventVenueTypes[idx] = 'use_existing';
                                this.eventSelectedVenues[idx] = matchedUserVenue;
                            } else if (this.venues.length > 0) {
                                // Has connected venues but no match - default to use_existing
                                this.eventVenueTypes[idx] = 'use_existing';
                                this.eventSelectedVenues[idx] = null;
                            } else if (event.venue_id) {
                                // Guest user with venue match (displayed as text, not dropdown)
                                this.eventVenueTypes[idx] = 'use_existing';
                                this.eventSelectedVenues[idx] = null;
                            } else {
                                this.eventVenueTypes[idx] = 'create_new';
                                this.eventSelectedVenues[idx] = null;
                            }
                            this.eventClaimVenue[idx] = false;
                        });

                    // Initialize video properties for performers and automatically search for videos
                    this.preview.parsed.forEach((event, eventIdx) => {
                        // Initialize custom_field_values object if not present
                        if (!event.custom_field_values) {
                            event.custom_field_values = {};
                        }
                        // Ensure all custom fields have at least empty values for Vue reactivity.
                        // Same subset the form renders, so seeded keys and inputs agree.
                        @php
                            // Recomputed rather than reusing $isGuestImport from the markup above:
                            // that block sits inside the preview card, so its scope is not
                            // guaranteed to have run by the time this script renders.
                            $seedCustomFields = (isset($isGuest) && $isGuest)
                                ? $role->getRequestFormCustomFields()
                                : $role->getEventCustomFields();
                        @endphp
                        const roleCustomFields = @json($seedCustomFields);
                        Object.keys(roleCustomFields).forEach(fieldKey => {
                            if (event.custom_field_values[fieldKey] === undefined) {
                                event.custom_field_values[fieldKey] = '';
                                return;
                            }

                            // AI parsing can guess a value that is not one of the allowed options.
                            // Vue renders such a select blank while keeping the value, so leaving it
                            // would fail server validation on a field the guest sees as empty.
                            const fieldType = roleCustomFields[fieldKey].type || '';
                            if (fieldType !== 'dropdown' && fieldType !== 'multiselect') {
                                return;
                            }

                            const allowed = (roleCustomFields[fieldKey].options || '')
                                .split(',').map(s => s.trim()).filter(s => s);
                            const current = event.custom_field_values[fieldKey];

                            if (fieldType === 'dropdown') {
                                if (current && allowed.indexOf(String(current)) === -1) {
                                    event.custom_field_values[fieldKey] = '';
                                }
                            } else {
                                const kept = String(current || '').split(',')
                                    .map(s => s.trim())
                                    .filter(s => s && allowed.indexOf(s) !== -1);
                                event.custom_field_values[fieldKey] = kept.join(', ');
                            }
                        });

                        // Initialize configurable import field defaults
                        if (!event.ticket_currency_code) {
                            event.ticket_currency_code = '{{ $defaultCurrency }}';
                        }
                        if (!event.coupon_discount_type) {
                            event.coupon_discount_type = '{{ \App\Models\Event::DEFAULT_COUPON_DISCOUNT_TYPE }}';
                        }
                        if (!event.group_id) {
                            event.group_id = '';
                        }

                        if (event.performers && Array.isArray(event.performers)) {
                            event.performers.forEach((performer, performerIdx) => {
                                // Initialize video-related properties using Object.assign for reactivity
                                Object.assign(performer, {
                                    videos: null,
                                    selectedVideos: [], // Will contain at most one video
                                    searching: false,
                                    error: null
                                });

                                // Only search for videos for the first performer when there are multiple performers.
                                // In a list it waits until the row is opened: a hundred lookups for
                                // rows nobody may look at is a hundred lookups too many.
                                if (performerIdx === 0 && ! performer.talent_id && ! this.listMode) {
                                    this.$nextTick(() => {
                                        this.searchVideos(eventIdx, performerIdx);
                                    });
                                }
                            });
                        }
                    });
                    }

                    if (Array.isArray(this.preview.parsed)) {
                        this.resetSelection();
                    }

                    // Initialize datepickers after preview is loaded
                    this.$nextTick(() => {
                        if (this.listMode) {
                            // Say what arrived to whoever cannot see it arrive.
                            const heading = document.getElementById('import-list-heading');
                            if (heading) {
                                heading.focus();
                            }
                        }
                        initializeFlatpickr();
                        // Initialize EasyMDE editors for description fields
                        this.initDescriptionEditors();
                        // Re-apply venue selections to searchable-select dropdowns
                        this.eventSelectedVenues.forEach((venue, idx) => {
                            if (venue) {
                                const select = document.getElementById('selected_venue_' + idx);
                                if (select) {
                                    select.value = venue.id;
                                }
                            }
                        });
                    });

                    this.notifyParseComplete();
                } catch (error) {
                    // Only show error if this is still the latest request
                    if (this.currentRequestId === requestId) {
                        console.error('Error fetching preview:', error)
                        this.errorMessage = error.message || @json(__('messages.error_occurred'), JSON_UNESCAPED_UNICODE);
                    }
                } finally {
                    // Only update loading state if this is still the latest request
                    if (this.currentRequestId === requestId) {
                        this.isLoading = false;
                        this.stopSlowTimer();
                    }
                }
            },
            
            handlePaste(event) {
                // A pasted picture (a screenshot, usually) is a flyer.
                const files = (event.clipboardData && event.clipboardData.files) ? Array.from(event.clipboardData.files) : [];
                const image = files.find(file => file.type.startsWith('image/'));
                if (image && ! this.linksOnly) {
                    event.preventDefault();
                    this.uploadDetailsImage(image);
                }
                // Text is left to the browser, which puts it where the cursor is. This used to
                // replace the whole box with the clipboard, and blank it when a picture was pasted.
            },

            shouldShowVenueFields(idx) {
                // Hide fields if "use_existing" is selected (for auth users with venues or guest with matched venue)
                if (this.eventVenueTypes[idx] === 'use_existing') {
                    return false;
                }
                // Show fields if "create_new" selected or no venue selection available
                return true;
            },

            canClaimVenue(idx) {
                // Brand-new venue: unchanged, the user typed it in themselves.
                if (this.eventVenueTypes[idx] !== 'use_existing') {
                    return true;
                }
                // Claiming a venue that already exists is admin-portal only: the public request
                // page can create and sign in an account mid-submission, so a seconds-old
                // account must not be able to take over an ownerless venue. saveEvent()
                // enforces this server-side too.
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return false;
                }
                // Only venues nobody owns yet.
                const venue = this.eventSelectedVenues[idx];
                return !!venue && !!venue.is_claimable;
            },

            onVenueTypeChange(idx) {
                if (this.eventVenueTypes[idx] === 'create_new') {
                    this.eventSelectedVenues[idx] = null;
                }
                // The tick belongs to whichever venue was showing when it was made.
                this.eventClaimVenue[idx] = false;
            },

            onVenueSelect(idx, venue) {
                this.eventSelectedVenues[idx] = venue;
                this.eventClaimVenue[idx] = false;
                if (venue) {
                    this.preview.parsed[idx].venue_id = venue.id;
                    this.preview.parsed[idx].venue_name = venue.name;
                    this.preview.parsed[idx].event_address = venue.address1;
                    this.preview.parsed[idx].event_city = venue.city;
                }
            },

            clearSelectedVenue(idx) {
                this.eventSelectedVenues[idx] = null;
                this.eventVenueTypes[idx] = 'create_new';
                this.eventClaimVenue[idx] = false;
                this.preview.parsed[idx].venue_id = null;
            },

            handleEdit(idx) {
                // For guest users, don't allow editing events
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return;
                }
                
                if (this.savedEvents[idx] && this.savedEventData[idx]) {
                    window.open(this.savedEventData[idx].edit_url, '_blank');
                }
            },

            handleView(idx) {
                if (this.savedEvents[idx] && this.savedEventData[idx]) {
                    if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                        // For guest users, redirect to the view URL
                        window.location.href = this.savedEventData[idx].view_url;
                    } else {
                        // For authenticated users, open in new tab
                        window.open(this.savedEventData[idx].view_url, '_blank');
                    }
                }
            },

            // quiet: called for each row of a bulk add, which reports once at the end instead of
            // raising a toast and a page-level error per row. Answers with what happened.
            async handleSave(idx, quiet = false) {
                const result = { ok: false, status: 0, limit: false, retryAfter: 0 };
                if (! quiet) {
                    this.errorMessage = null;
                }
                // Reset error state for this event
                this.saveErrors[idx] = false;
                // Set saving state for this event
                this.savingEvents[idx] = true;
                
                try {
                    // Get data from the Vue model
                    if (!this.preview?.parsed?.[idx]) {
                        throw new Error('Event data not found');
                    }
                    
                    const parsed = this.preview.parsed[idx];
                    
                    // Build starts_at from split date/time fields
                    var eventDate = parsed.event_date;
                    var startMinutes = this.parseTimeToMinutes(parsed.event_start_time);
                    if (!eventDate || startMinutes === null) {
                        throw new Error('Date and start time are required');
                    }
                    var sh = Math.floor(startMinutes / 60);
                    var sm = startMinutes % 60;
                    var dateValue = eventDate + ' ' + (sh < 10 ? '0' : '') + sh + ':' + (sm < 10 ? '0' : '') + sm + ':00';

                    // Compute duration from start/end times
                    var endMinutes = this.parseTimeToMinutes(parsed.event_end_time);
                    var computedDuration = parsed.event_duration || '';
                    if (endMinutes !== null && startMinutes !== null) {
                        var diff = endMinutes - startMinutes;
                        if (diff < 0) diff += 1440;
                        computedDuration = (diff / 60);
                        // The two clock times say how far into its last day the event ends, not
                        // how many days it runs. A three-day festival kept only its final hours.
                        var wholeDays = Math.floor((parseFloat(parsed.event_duration) || 0) / 24);
                        if (wholeDays > 0) {
                            computedDuration = Math.round((wholeDays * 24 + (diff / 60)) * 1000) / 1000;
                        }
                    }
                    
                    // Prepare members data
                    const members = {};
                    
                    if (parsed.performers && parsed.performers.length > 0) {
                        parsed.performers.forEach((performer, index) => {
                            const memberData = {
                                name: performer.name,
                                name_en: performer.name_en || '',
                                email: performer.email || '',
                                website: performer.website || '',
                                language_code: '{{ $role->language_code }}',
                            };
                            
                            // Add selected videos if any
                            if (performer.selectedVideos && performer.selectedVideos.length > 0) {
                                memberData.youtube_url = performer.selectedVideos[0].url; // Only send one video URL
                            }
                            
                            // Use talent_id if available, otherwise use new_talent_${index}
                            const memberId = performer.talent_id || `new_talent_${index}`;
                            members[memberId] = memberData;
                        });
                    } else if (parsed.talent_id) {
                        members[parsed.talent_id] = {
                            name: parsed.performer_name,
                            name_en: parsed.performer_name_en || '',
                            email: parsed.performer_email || '',
                            youtube_url: parsed.performer_youtube_url || '',
                            language_code: '{{ $role->language_code }}',
                        };
                    }
                    
                    // Determine venue_id based on selection
                    let venueId = null;
                    let venueName = parsed.venue_name;
                    let venueAddress = parsed.event_address || "{{ $role->isCurator() ? $role->city : '' }}";
                    let venueCity = parsed.event_city;

                    if (this.eventVenueTypes[idx] === 'use_existing' && this.eventSelectedVenues[idx]) {
                        // Use selected existing venue
                        venueId = this.eventSelectedVenues[idx].id;
                        venueName = this.eventSelectedVenues[idx].name;
                        venueAddress = this.eventSelectedVenues[idx].address1;
                        venueCity = this.eventSelectedVenues[idx].city;
                    }

                    // Get event name from VueJS model
                    const eventName = parsed.event_name;

                    // Send request to server
                    const response = await fetch('{{ isset($isGuest) && $isGuest ? route("event.guest_import.store", ["subdomain" => $role->subdomain]) : route("event.import", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: JSON.stringify({
                            venue_name: venueName,
                            venue_name_en: parsed.venue_name_en,
                            venue_address1: venueAddress,
                            venue_address1_en: parsed.venue_address1_en,
                            venue_city: venueCity,
                            venue_city_en: parsed.event_city_en,
                            venue_state: parsed.event_state,
                            venue_state_en: parsed.event_state_en,
                            venue_postal_code: parsed.event_postal_code,
                            venue_country_code: parsed.event_country_code || '{{ $role->country_code }}',
                            venue_id: venueId,
                            venue_language_code: '{{ $role->language_code }}',
                            claim_venue_ownership: this.canClaimVenue(idx) && !!this.eventClaimVenue[idx],
                            members: members,
                            name: eventName,
                            name_en: parsed.event_name_en,
                            short_name: parsed.event_short_name,
                            short_name_en: parsed.event_short_name_en,
                            short_description: parsed.short_description,
                            short_description_en: parsed.short_description_en,
                            starts_at: dateValue,
                            duration: computedDuration,
                            description: this.descriptionEditors[idx] ? this.descriptionEditors[idx].value() : parsed.event_details,
                            social_image: parsed.social_image,
                            registration_url: parsed.registration_url,
                            ticket_price: parsed.ticket_price,
                            coupon_code: parsed.coupon_code,
                            coupon_discount: parsed.coupon_discount,
                            coupon_discount_type: parsed.coupon_discount_type,
                            ticket_currency_code: parsed.ticket_currency_code,
                            current_role_group_id: parsed.group_id || null,
                            custom_field_values: parsed.custom_field_values || {},
                            category_id: parsed.category_id,
                            @unless ($isGuestPage)
                            // A repeating entry from a feed or a calendar, as the fields the event
                            // form itself posts for a repeating event (RecurrenceMapper).
                            ...(parsed.recurrence && parsed.recurrence.fields ? parsed.recurrence.fields : {}),
                            // Which import this preview came from. The server reads it to label
                            // the event, and treats anything it cannot verify as a pasted row.
                            import_token: (this.preview.meta && this.preview.meta.import_token) || null,
                            @endunless
                            @if (isset($isGuest) && $isGuest)
                                website: this.honeypot,
                            @endif
                            @if ($role->isCurator() && !isset($isGuest))
                                curators: ['{{ \App\Utils\UrlUtils::encodeId($role->id) }}'],
                            @endif
                            // Optional create-account checkbox for guests (curators that do
                            // not require an account). account_* keys keep these distinct
                            // from the event's own name/email.
                            ...({{ isset($isGuest) && $isGuest ? 'true' : 'false' }} && this.createAccount ? {
                                create_account: true,
                                account_name: this.userName,
                                account_email: this.userEmail,
                                account_password: this.userPassword
                            } : {})
                        })
                    });
                    
                    // Handle response
                    if (!response.ok) {
                        const errorData = await response.json().catch(() => ({}));
                        // The daily creation cap and a refused request answer with {error}.
                        let msg = errorData.message || errorData.error || @json(__('messages.error'));
                        if (errorData.errors) {
                            const first = Object.values(errorData.errors)[0];
                            if (Array.isArray(first) && first[0]) msg = first[0];
                        }
                        if (response.status === 429) {
                            msg = @json(__('messages.too_many_attempts'));
                        }
                        result.status = response.status;
                        result.retryAfter = parseInt(response.headers.get('Retry-After')) || 0;
                        result.limit = errorData.code === 'event_create_limit';
                        throw new Error(msg);
                    }

                    const data = await response.json();
                    result.ok = true;

                    // Store the response data in savedEventData array
                    this.savedEvents[idx] = true;
                    this.savedEventData[idx] = data.event; // Store the event object with view_url and edit_url

                    // Add the venue to venues list if it's new (for future imports in same session)
                    if (data.venue && !this.venues.find(v => v.id === data.venue.id)) {
                        this.venues.push(data.venue);
                    }

                    // For guest users, automatically redirect to view the event
                    if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }} && data.event.view_url) {
                        window.location.href = data.event.view_url;
                    } else if (! quiet) {
                        // Show success message for non-guest users
                        Toastify({
                            text: @json(__("messages.event_created")),
                            duration: 3000,
                            position: 'center',
                            stopOnFocus: true,
                            style: {
                                background: '#4BB543',
                            }
                        }).showToast();
                    }
                    
                } catch (error) {
                    console.error('Error saving event:', error);
                    if (! quiet) {
                        this.errorMessage = error.message;
                    }
                    this.saveErrors[idx] = error.message || @json(__('messages.error_occurred'), JSON_UNESCAPED_UNICODE);
                } finally {
                    // Clear saving state for this event
                    this.savingEvents[idx] = false;
                }

                return result;
            },

            getYouTubeEmbedUrl(url) {
                // Extract video ID from various YouTube URL formats
                const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|shorts\/|live\/|watch\?v=|&v=)([^#&?]*).*/;
                const match = url.match(regExp);
                const videoId = match && match[2].length === 11 ? match[2] : null;
                
                return videoId ? `https://www.youtube-nocookie.com/embed/${videoId}` : '';
            },

            getSocialImageUrl(path) {
                // Extract filename from /tmp/event_XXXXX.jpg path
                const filename = path.split('/').pop();
                return `{{ route('event.tmp_image', ['filename' => '']) }}/${filename}`;
            },

            handleClear() {
                const rows = (this.preview && this.preview.parsed) ? this.preview.parsed : [];
                const unsaved = rows.filter((row, idx) => ! this.savedEvents[idx]).length;
                // One unsaved row is the one being cleared. More than that is work about to be lost.
                if (rows.length > 1 && unsaved > 0 && ! confirm(@json(__('messages.import_clear_unsaved'), JSON_UNESCAPED_UNICODE))) {
                    return;
                }

                this.destroyDescriptionEditors();
                this.eventDetails = '';
                this.detailsImage = null;
                this.detailsImageUrl = null;
                this.preview = null;
                this.savedEvents = [];
                this.savedEventData = [];
                this.savingEvents = [];
                this.saveErrors = [];
                this.selectedRows = [];
                this.expandedRow = null;
                this.addSummary = null;
                this.allDone = false;
                this.errorMessage = null;
                // Clear user creation fields
                this.createAccount = false;
                this.userName = '';
                this.userEmail = '';
                this.userPassword = '';
                this.$nextTick(() => {
                    document.getElementById('event_details').focus();
                });
            },

            handleClearForNext() {
                this.selectedRows = [];
                this.expandedRow = null;
                this.addSummary = null;
                this.allDone = false;
                this.destroyDescriptionEditors();
                // Clear the form state to import another event
                this.preview = null;
                this.eventDetails = '';
                this.detailsImage = null;
                this.detailsImageUrl = null;
                this.savedEvents = [];
                this.savedEventData = [];
                this.saveErrors = [];
                this.savingEvents = [];
                this.eventVenueTypes = [];
                this.eventSelectedVenues = [];
                this.eventClaimVenue = [];
                this.errorMessage = null;

                // Focus on the textarea
                this.$nextTick(() => {
                    document.getElementById('event_details').focus();
                });
            },

            initDescriptionEditors() {
                if (!this.importFields.description && !this.showAllFields) return;
                if (!this.preview || !this.preview.parsed) return;

                this.preview.parsed.forEach((event, idx) => {
                    if (this.descriptionEditors[idx] || this.savedEvents[idx]) return;
                    const el = document.getElementById('import_description_' + idx);
                    if (!el || !window.initTinyMDE) return;
                    const editor = window.initTinyMDE(el, () => {
                        this.preview.parsed[idx].event_details = editor.value();
                    });
                    this.descriptionEditors[idx] = editor;
                });
            },

            destroyDescriptionEditors() {
                Object.keys(this.descriptionEditors).forEach(idx => {
                    if (this.descriptionEditors[idx]) {
                        this.descriptionEditors[idx]._stopEditorObserver?.();
                        this.descriptionEditors[idx].toTextArea();
                    }
                });
                this.descriptionEditors = {};
            },

            dragOver(e) {
                this.isDragging = true
            },

            dragLeave(e) {
                this.isDragging = false
            },

            async handleDrop(e, idx) {
                this.isDragging = false
                const files = e.dataTransfer.files
                if (files.length > 0) {
                    await this.uploadImage(files[0], idx)
                }
            },

            openFileSelector(idx) {
                const fileInput = this.$refs[`fileInput_${idx}`];
                if (fileInput) {
                    if (Array.isArray(fileInput)) {
                        fileInput[0].click();
                    } else {
                        fileInput.click();
                    }
                }
            },

            async handleFileSelect(e, idx) {
                const files = e.target.files
                if (files.length > 0) {
                    await this.uploadImage(files[0], idx)
                }
            },

            async uploadImage(file, idx) {
                if (!file.type.startsWith('image/')) {
                    this.errorMessage = @json(__("messages.invalid_image_type"));
                    return;
                }

                // Check file size (2.5 MB = 2.5 * 1024 * 1024 bytes)
                const maxSize = 2.5 * 1024 * 1024;
                if (file.size > maxSize) {
                    this.errorMessage = @json(__("messages.image_size_warning"));
                    return;
                }

                this.isUploadingImage = idx;
                
                try {
                    // Create a FormData object to send the file
                    const formData = new FormData();
                    formData.append('image', file);
                    @if (isset($isGuest) && $isGuest)
                    formData.append('website', this.honeypot);
                    @endif

                    // Upload the image to get a temporary URL
                    const response = await fetch('{{ isset($isGuest) && $isGuest ? route("event.guest_upload_image", ["subdomain" => $role->subdomain]) : route("event.upload_image", ["subdomain" => $role->subdomain]) }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': '{{ csrf_token() }}'
                        },
                        body: formData
                    });

                    if (!response.ok) throw new Error('Request failed');
                    const data = await response.json();

                    if (data.success && data.filename) {
                        // Update the social_image property for the specific event
                        this.preview.parsed[idx].social_image = data.filename;
                    } else {
                        throw new Error(data.message || @json(__("messages.error_uploading_image")));
                    }
                } catch (error) {
                    console.error('Error uploading image:', error);
                    this.errorMessage = error.message || @json(__("messages.error_uploading_image"));
                } finally {
                    this.isUploadingImage = null;
                }
            },

            removeImage(idx) {
                if (this.preview && this.preview.parsed && this.preview.parsed[idx]) {
                    this.preview.parsed[idx].social_image = null;
                }
            },

            saveShowAllFieldsPreference() {
                localStorage.setItem('event_import_show_all_fields', this.showAllFields)
            },

            loadShowAllFieldsPreference() {
                const savedPreference = localStorage.getItem('event_import_show_all_fields')
                if (savedPreference !== null) {
                    this.showAllFields = savedPreference === 'true'
                }
            },

            handleRemoveEvent(idx) {
                // For guest users, don't allow removing events since they only see one
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return;
                }

                if (confirm(@json(__("messages.confirm_remove_event")))) {
                    // Destroy description editor for this event
                    if (this.descriptionEditors[idx]) {
                        this.descriptionEditors[idx]._stopEditorObserver?.();
                        this.descriptionEditors[idx].toTextArea();
                        delete this.descriptionEditors[idx];
                    }
                    // Re-index remaining editors
                    const newEditors = {};
                    Object.keys(this.descriptionEditors).forEach(key => {
                        const k = parseInt(key);
                        if (k > idx) {
                            newEditors[k - 1] = this.descriptionEditors[k];
                        } else {
                            newEditors[k] = this.descriptionEditors[k];
                        }
                    });
                    this.descriptionEditors = newEditors;

                    // Remove the event from the parsed array
                    this.preview.parsed.splice(idx, 1);
                    // And its entry in every array kept beside it. Four of these were missed,
                    // so the venue chosen for one event slid onto the next after a removal.
                    [this.savedEvents, this.savedEventData, this.savingEvents, this.saveErrors,
                        this.eventVenueTypes, this.eventSelectedVenues, this.eventClaimVenue,
                        this.selectedRows].forEach(list => list.splice(idx, 1));
                    this.expandedRow = null;
                    
                    // If no events left, clear the preview
                    if (this.preview.parsed.length === 0) {
                        this.preview = null;
                    }
                    
                    // Re-initialize datepickers after removing an event
                    this.$nextTick(() => {
                        initializeFlatpickr();
                    });
                    
                    // Show success message
                    Toastify({
                        text: @json(__("messages.event_removed")),
                        duration: 3000,
                        position: 'center',
                        stopOnFocus: true,
                        style: {
                            background: '#4BB543',
                        }
                    }).showToast();
                }
            },

            async handleSelect(idx) {
                // Reset error state for this event
                this.saveErrors[idx] = false;
                // Set saving state for this event
                this.savingEvents[idx] = true;
                
                if (!this.preview?.parsed?.[idx]?.event_url) {
                    return;
                }

                const hash = this.preview.parsed[idx].event_id;

                try {
                    const url = @json(route('event.curate', ['subdomain' => $role->subdomain, 'hash' => '--hash--'])).replace('--hash--', hash);
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) throw new Error('Request failed');
                    const data = await response.json();

                    if (data.success) {
                        this.savedEvents[idx] = true;
                        this.savedEventData[idx] = {
                            view_url: data.event_url || this.preview.parsed[idx].event_url,
                            is_curated: true
                        };

                        // Update the is_curated flag in the preview data
                        this.preview.parsed[idx].is_curated = true;
                        
                        const eventUrl = data.event_url || this.preview.parsed[idx].event_url;
                        
                        // For guest users, redirect immediately without toast
                        if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                            if (eventUrl) {
                                window.location.href = eventUrl;
                            }
                        } else {
                            // For non-guest users, show toast and redirect after delay
                            Toastify({
                                text: @json(__("messages.event_added_to_schedule")),
                                duration: 3000,
                                position: 'center',
                                stopOnFocus: true,
                                style: {
                                    background: '#4BB543',
                                }
                            }).showToast();
                        }
                    } else {
                        throw new Error(data.message || @json(__("messages.error_adding_event")));
                    }
                } catch (error) {
                    console.error('Error selecting event:', error);
                    this.errorMessage = error.message || @json(__("messages.error_adding_event"));
                    // Set error state for this event
                    this.saveErrors[idx] = error.message || @json(__("messages.error_adding_event"));
                } finally {
                    // Clear saving state for this event
                    this.savingEvents[idx] = false;
                }
            },

            async handleCurate(idx) {
                // For guest users, don't allow curating events
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return;
                }
                
                // Reset error state for this event
                this.saveErrors[idx] = false;
                // Set saving state for this event
                this.savingEvents[idx] = true;
                
                if (!this.preview?.parsed?.[idx]?.event_url) {
                    return;
                }

                const hash = this.preview.parsed[idx].event_id;

                try {
                    const url = @json(route('event.curate', ['subdomain' => $role->subdomain, 'hash' => '--hash--'])).replace('--hash--', hash);
                    const response = await fetch(url, {
                        method: 'GET',
                        headers: {
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    if (!response.ok) throw new Error('Request failed');
                    const data = await response.json();

                    if (data.success) {
                        this.savedEvents[idx] = true;
                        this.savedEventData[idx] = {
                            view_url: data.event_url || this.preview.parsed[idx].event_url,
                            is_curated: true
                        };

                        Toastify({
                            text: @json(__("messages.curate_event")),
                            duration: 3000,
                            position: 'center',
                            stopOnFocus: true,
                            style: {
                                background: '#4BB543',
                            }
                        }).showToast();
                    } else {
                        throw new Error(data.message || @json(__("messages.error_curating_event")));
                    }
                } catch (error) {
                    console.error('Error curating event:', error);
                    this.errorMessage = error.message || @json(__("messages.error_curating_event"));
                    // Set error state for this event
                    this.saveErrors[idx] = error.message || @json(__("messages.error_curating_event"));
                } finally {
                    // Clear saving state for this event
                    this.savingEvents[idx] = false;
                }
            },

            async handleSaveAll() {
                // For guest users, don't allow save all since they only see one event
                if ({{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                    return;
                }
                
                // Check if there are any events to save
                if (!this.preview?.parsed || this.preview.parsed.length === 0) {
                    return;
                }
                
                // Prevent multiple clicks by disabling the button
                const saveAllButton = event.target;
                if (saveAllButton) {
                    saveAllButton.disabled = true;
                    saveAllButton.classList.add('opacity-50', 'cursor-not-allowed');
                }
                
                let successCount = 0;
                let errorCount = 0;
                let skippedCount = 0;
                
                // Set saving state for all events that will be processed
                for (let idx = 0; idx < this.preview.parsed.length; idx++) {
                    if (!this.savedEvents[idx] && !this.preview.parsed[idx].event_url) {
                        this.savingEvents[idx] = true;
                    }
                }
                
                // Loop through all events
                for (let idx = 0; idx < this.preview.parsed.length; idx++) {
                    // Skip already saved events
                    if (this.savedEvents[idx]) {
                        skippedCount++;
                        continue;
                    }
                    
                    // Skip events that have a matching one (indicated by event_url)
                    if (this.preview.parsed[idx].event_url) {
                        skippedCount++;
                        continue;
                    }
                    
                    try {
                        // If event has a curate button and is not already curated, curate it
                        if (this.isCurator && 
                            this.preview.parsed[idx].event_url && 
                            !this.preview.parsed[idx].is_curated &&
                            !{{ isset($isGuest) && $isGuest ? 'true' : 'false' }}) {
                            await this.handleCurate(idx);
                        } else {
                            // Otherwise save it normally
                            await this.handleSave(idx);
                        }
                        
                        // Check if the operation was successful
                        if (this.savedEvents[idx]) {
                            successCount++;
                        } else if (this.saveErrors[idx]) {
                            errorCount++;
                        }
                        
                        // Add a small delay between saves to prevent overwhelming the server
                        await new Promise(resolve => setTimeout(resolve, 500));
                    } catch (error) {
                        errorCount++;
                        console.error('Error processing event ' + idx + ':', error);
                    }
                }
                
                // Show appropriate message after all events are processed
                let message = '';
                if (errorCount === 0 && skippedCount === 0) {
                    message = @json(__("messages.all_events_processed"));
                } else {
                    message = @json(__("messages.events_processed_with_errors")).replace('{success}', successCount).replace('{errors}', errorCount);
                    if (skippedCount > 0) {
                        message += ' (' + @json(__("messages.events_skipped")).replace('{skipped}', skippedCount) + ')';
                    }
                }

                Toastify({
                    text: message,
                    duration: 3000,
                    position: 'center',
                    stopOnFocus: true,
                    style: {
                        background: errorCount > 0 && successCount === 0 ? '#FF0000' : 
                                    skippedCount > 0 && successCount === 0 ? '#FF9800' : '#4BB543',
                    }
                }).showToast();
                
                // Re-enable the button after processing is complete
                if (saveAllButton) {
                    saveAllButton.disabled = false;
                    saveAllButton.classList.remove('opacity-50', 'cursor-not-allowed');
                }
                
                // Clear saving state for all events
                for (let idx = 0; idx < this.preview.parsed.length; idx++) {
                    this.savingEvents[idx] = false;
                }
            },



            dragOverDetails(e) {
                e.preventDefault();
                // Don't change state here, just prevent default
            },

            dragEnterDetails(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Only handle drag enter on the main textarea
                if (e.target === this.$refs.eventDetails) {
                    // Clear any existing timeout when re-entering
                    if (this.dragTimeout) {
                        clearTimeout(this.dragTimeout);
                        this.dragTimeout = null;
                    }
                    this.isDraggingDetails = true;
                    this.isDragActive = true;
                }
            },

            dragLeaveDetails(e) {
                e.preventDefault();
                e.stopPropagation();
                
                // Don't immediately hide - let the timeout handle it
                // This prevents flickering when moving over child elements
            },

            dragOverDetails(e) {
                e.preventDefault();
                // Keep the drop zone visible while dragging over
                if (!this.isDraggingDetails) {
                    this.isDraggingDetails = true;
                }
            },

            dragEndDetails(e) {
                // Reset drag state when drag operation ends
                this.resetDragState();
            },

            handleGlobalDragEnd(e) {
                // Reset drag state when any drag operation ends globally
                this.resetDragState();
            },

            resetDragState() {
                this.isDraggingDetails = false;
                this.isDragActive = false;
                this.dragStartTime = 0;
                if (this.dragTimeout) {
                    clearTimeout(this.dragTimeout);
                    this.dragTimeout = null;
                }
            },

            handleGlobalDragStart(e) {
                // Track when a global drag operation starts
                this.isDragActive = true;
            },

            handleMouseMove(e) {
                // Only track mouse movement if we're in a drag operation
                if (!this.isDragActive || !this.isDraggingDetails) {
                    return;
                }

                // Check if mouse is outside the drop zone with some tolerance
                const dropZone = this.$refs.eventDetails;
                if (dropZone) {
                    const rect = dropZone.getBoundingClientRect();
                    const tolerance = 10; // 10px tolerance to prevent edge flickering
                    const isOutside = e.clientX < (rect.left - tolerance) || 
                                    e.clientX > (rect.right + tolerance) || 
                                    e.clientY < (rect.top - tolerance) || 
                                    e.clientY > (rect.bottom + tolerance);
                    
                    if (isOutside) {
                        // Use a longer delay to prevent flickering
                        if (this.dragTimeout) {
                            clearTimeout(this.dragTimeout);
                        }
                        this.dragTimeout = setTimeout(() => {
                            // Double-check that we're still outside before hiding
                            const currentRect = dropZone.getBoundingClientRect();
                            const stillOutside = e.clientX < (currentRect.left - tolerance) || 
                                              e.clientX > (currentRect.right + tolerance) || 
                                              e.clientY < (currentRect.top - tolerance) || 
                                              e.clientY > (currentRect.bottom + tolerance);
                            
                            if (stillOutside) {
                                this.isDraggingDetails = false;
                            }
                        }, 300); // Increased delay for more stability
                    }
                }
            },

            openDetailsFileSelector() {
                this.$refs.detailsFileInput.click();
            },

            async handleDetailsFileSelect(e) {
                const files = e.target.files;
                if (files.length > 0) {
                    await this.uploadDetailsImage(files[0]);
                }
                
                // Reset the file input to allow selecting the same file again
                e.target.value = '';
            },

            async handleDetailsImageDrop(e) {
                this.resetDragState();
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    await this.uploadDetailsImage(files[0]);
                }
            },

            async uploadDetailsImage(file) {
                if (!file.type.startsWith('image/')) {
                    this.errorMessage = @json(__("messages.invalid_image_type"));
                    return;
                }

                // A phone photo is routinely several megabytes. It is made smaller here rather
                // than refused: the server takes 10 MB, and a flyer read needs far less.
                if (file.size > 2.5 * 1024 * 1024) {
                    file = await this.shrinkImage(file);
                }
                if (file.size > 10 * 1024 * 1024) {
                    this.errorMessage = @json(__("messages.import_image_too_large"), JSON_UNESCAPED_UNICODE);
                    return;
                }

                this.isUploadingDetailsImage = true;
                
                try {
                    this.detailsImage = file;
                    
                    // Use FileReader to create a data URL for preview
                    const reader = new FileReader();
                    reader.onload = (e) => {
                        this.detailsImageUrl = e.target.result; // This will be a data URL
                    };
                    reader.readAsDataURL(file);
                    
                    // Clear any previous error messages
                    this.errorMessage = null;
                    
                    // Don't auto-submit - user must click the submit button
                } catch (error) {
                    console.error('Error uploading details image:', error);
                    this.errorMessage = error.message || @json(__("messages.error_uploading_image"));
                    // Reset the image state on error
                    this.detailsImage = null;
                    this.detailsImageUrl = null;
                } finally {
                    this.isUploadingDetailsImage = false;
                }
            },

            // Redraw a picture at no more than 2000px on its longer side, as a JPEG. Returns the
            // original when the browser cannot decode it, and the size check decides from there.
            async shrinkImage(file) {
                try {
                    const bitmap = await createImageBitmap(file);
                    const scale = Math.min(1, 2000 / Math.max(bitmap.width, bitmap.height));
                    const canvas = document.createElement('canvas');
                    canvas.width = Math.round(bitmap.width * scale);
                    canvas.height = Math.round(bitmap.height * scale);
                    canvas.getContext('2d').drawImage(bitmap, 0, 0, canvas.width, canvas.height);
                    const blob = await new Promise(resolve => canvas.toBlob(resolve, 'image/jpeg', 0.85));

                    return blob && blob.size < file.size ? new File([blob], 'flyer.jpg', { type: 'image/jpeg' }) : file;
                } catch (e) {
                    return file;
                }
            },

            removeDetailsImage() {
                // No need to revoke anything with data URLs
                this.detailsImage = null;
                this.detailsImageUrl = null;
                this.errorMessage = null; // Clear any error messages when removing the image
                
                // Reset the file input to allow selecting the same file again
                if (this.$refs.detailsFileInput) {
                    this.$refs.detailsFileInput.value = '';
                }
                
                // Don't auto-submit - user must click the submit button
            },

            getDetailsImageUrl() {
                if (!this.detailsImage) return '';
                
                try {
                    // Create a new URL object each time to avoid caching issues
                    return URL.createObjectURL(this.detailsImage);
                } catch (e) {
                    console.error('Error creating object URL:', e);
                    return '';
                }
            },

            // YouTube video search methods
            formatNumber(num) {
                if (!num) return '0';
                if (num >= 1000000) {
                    return (num / 1000000).toFixed(1) + 'M';
                } else if (num >= 1000) {
                    return (num / 1000).toFixed(1) + 'K';
                }
                return num.toString();
            },

            async searchVideos(eventIdx, performerIdx) {
                const event = this.preview.parsed[eventIdx];
                const performer = event.performers[performerIdx];
                
                if (!performer || !performer.name) return;
                
                // Initialize performer properties if they don't exist
                if (!performer.videos) {
                    Object.assign(performer, { videos: [] });
                }
                if (!performer.selectedVideos) {
                    Object.assign(performer, { selectedVideos: [] }); // Will contain at most one video
                }
                
                performer.searching = true;
                performer.error = null;
                
                try {
                    const endpoint = {{ isset($isGuest) && $isGuest ? 'true' : 'false' }} 
                        ? `{{ route('role.guest_search_youtube', ['subdomain' => $role->subdomain]) }}`
                        : `{{ route('role.search_youtube', ['subdomain' => $role->subdomain]) }}`;
                    
                    
                    const response = await fetch(`${endpoint}?q=${encodeURIComponent(performer.name)}`);
                    if (!response.ok) throw new Error('Request failed');
                    const data = await response.json();
                    
                    if (data.success && data.videos) {
                        performer.videos = data.videos;
                        // Don't auto-select videos - let user choose
                        performer.selectedVideos = []; // Reset to empty array
                    } else {
                        performer.error = data.message || @json(__("messages.no_videos_found"));
                    }
                } catch (error) {
                    performer.error = @json(__("messages.error_searching_videos"));
                    console.error('Error searching videos:', error);
                } finally {
                    performer.searching = false;
                }
            },

            isVideoSelected(eventIdx, performerIdx, video) {
                const event = this.preview.parsed[eventIdx];
                const performer = event.performers[performerIdx];
                return performer && performer.selectedVideos && performer.selectedVideos.length > 0 && performer.selectedVideos[0].id === video.id;
            },

            selectVideo(eventIdx, performerIdx, video) {
                const event = this.preview.parsed[eventIdx];
                const performer = event.performers[performerIdx];

                if (!performer) return;

                if (!performer.selectedVideos) {
                    Object.assign(performer, { selectedVideos: [] });
                }

                const index = performer.selectedVideos.findIndex(v => v.id === video.id);
                if (index > -1) {
                    // Remove video if already selected
                    performer.selectedVideos.splice(index, 1);
                } else {
                    // Replace any existing video with the new one (only allow one)
                    performer.selectedVideos = [video];
                }
            },
        }
    });
    window.__importApp = app.mount('#event-import-app');
</script>
@endif