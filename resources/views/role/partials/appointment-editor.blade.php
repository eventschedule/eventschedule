@php
    $durations = [15, 30, 45, 60, 90, 120];
    $slotIntervals = [5, 10, 15, 20, 30, 60];
    $backUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']);

    // Every field below reads through old() so a validation error re-renders the owner's submission
    // instead of silently reverting to the stored row (or to the defaults for a new type).
    $curDuration = (int) old('duration_minutes', $editing->duration_minutes ?? 30);
    $curInterval = old('slot_interval_minutes', $editing->slot_interval_minutes ?? '');
    $curLocationType = old('location_type', $editing->location_type ?? 'in_person');
    $curPaymentMethod = old('payment_method', $editing->payment_method ?? 'cash');
    $curPrice = old('price', $editing->price ?? 0);
    $curCurrency = old('currency_code', $editing->currency_code
        ?? \App\Utils\MoneyUtils::getCurrencyForCountry($role->country_code));

    // x-toggle posts a hidden "0" alongside the checkbox, so old() yields '0'/'1' on a re-render and
    // the raw default on a first render - filter_var reads both.
    $oldBool = fn ($field, $default) => filter_var(old($field, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $askPhoneOn = $oldBool('ask_phone', $editing->ask_phone ?? false);

    // scroll-mt-32 clears the sticky bar when a card itself is scrolled to - the layout header is
    // 64px and the bar sits on top of it at ~66px, so anything under 128px lands behind them. It does
    // NOT help a focused field inside a card: the browser scrolls the field, not its container.
    // p-4 on a phone and p-6 from sm up, the same step .section-content takes: with seven cards the
    // padding alone was costing a couple of hundred pixels of mobile scroll.
    $sectionClass = 'ap-card rounded-xl p-4 sm:p-6 scroll-mt-32';
    $headingClass = 'text-base font-semibold text-gray-900 dark:text-gray-100 mb-4';
    $selectClass = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm';
    $timeSelectClass = 'px-2 py-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm';

    // One segmented-control language for every small enum on this form: the duration quick-picks, the
    // free/paid switch, the location type and the payment method. Same shell and same pressed look as
    // the tab's sub-view switcher.
    $segShell = 'inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1';
    $segIdle = 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300';
    $segOn = 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm';
    $segItem = 'rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200';
    $segPressed = 'box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);';
    // Radio variant: the input stays a real radio (keyboard, screen reader, form posting all intact)
    // and only the sibling span is painted.
    $segRadio = $segItem.' block cursor-pointer '.$segIdle
        .' peer-checked:bg-white dark:peer-checked:bg-gray-900 peer-checked:text-gray-900 dark:peer-checked:text-white'
        .' peer-checked:shadow-[inset_0_2px_4px_rgba(0,0,0,0.08)]'
        .' peer-focus-visible:ring-2 peer-focus-visible:ring-[var(--brand-blue)]';

    $iconBtn = 'inline-flex h-9 w-9 flex-shrink-0 items-center justify-center rounded-lg text-gray-500 dark:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-700 hover:text-gray-700 dark:hover:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]';
    $rowClass = 'flex flex-wrap items-center gap-2 py-2 border-b border-gray-100 dark:border-gray-700 last:border-b-0';
@endphp

<form method="POST"
      action="{{ $editing ? route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $editing->hashedId()]) : route('appointments.store', ['subdomain' => $role->subdomain]) }}"
      id="appt-editor-form" class="space-y-4 text-gray-900 dark:text-gray-300">
    @csrf
    @if ($editing) @method('PUT') @endif

    {{-- Sticky action bar. This form is seven cards long, so Save was previously only reachable from
         the very bottom. Sits under the layout header (sticky top-0, h-16) and carries the page
         background so content scrolls cleanly beneath it. --}}
    <div class="sticky top-16 z-30 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-3
                bg-gray-50 dark:bg-gray-900 border-b border-gray-200 dark:border-gray-700
                flex items-center gap-3">
        {{-- Hidden below sm: with the heading in, the toggle plus both buttons wrap to a second row
             and the bar ends up taller than the card it replaced. --}}
        <h2 class="hidden sm:block text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editing ? __('messages.edit') : __('messages.appointments_new_type') }}</h2>
        <span class="sr-only sm:hidden">{{ $editing ? __('messages.edit') : __('messages.appointments_new_type') }}</span>

        <div class="ms-auto flex items-center gap-3">
            @if ($editing && $editing->isBookable())
                <a href="{{ route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $editing->slug]) }}"
                   target="_blank" rel="noopener"
                   class="hidden sm:inline-flex text-sm text-[var(--brand-blue)] hover:underline focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">{{ __('messages.preview') }}</a>
            @endif
            <x-toggle name="is_active" :label="__('messages.appointments_active')" :checked="$oldBool('is_active', $editing->is_active ?? true)" />
            {{-- js-cancel-btn is the app-wide handler that sets _skipUnsavedWarning before leaving. --}}
            <x-secondary-link :href="$backUrl" class="js-cancel-btn" data-fallback-url="{{ $backUrl }}">{{ __('messages.cancel') }}</x-secondary-link>
            <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
        </div>
    </div>

    {{-- No bulk error list here: x-app-admin-layout already renders $errors->all() at the top of
         main, so this was a second copy of the same text. Every rule in
         AppointmentTypeController::validated() now has an x-input-error next to its own field
         instead, which matters more once the form is two columns wide. --}}

    {{-- Two columns once there is room for them. The split is at 1400px rather than xl (1280px)
         because the AP pane is viewport minus the 288px sidebar minus 64px of gutters: 928px at
         1280, which would leave 452px columns and squeeze the weekly-hours rows. Two explicit column
         wrappers, not auto-flow, so the single-column order below 1400px stays exactly what it was.
         Section headings mirror the /docs/appointments anchors so the Help button stays aligned. --}}
    <div class="grid gap-4 min-[1400px]:grid-cols-2 min-[1400px]:gap-6 items-start">

    <div class="space-y-4">

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.details') }}</h3>

        <div class="space-y-5">
            <div>
                <x-input-label class="mb-1" for="appt_name" :value="__('messages.name')" />
                <x-text-input type="text" name="name" id="appt_name" required class="block w-full"
                       value="{{ old('name', $editing->name ?? __('messages.appointments_default_type_name')) }}" />
                <x-input-error :messages="$errors->get('name')" class="mt-1" />
            </div>

            <div>
                <x-input-label class="mb-1" for="appt_description" :value="__('messages.description')" />
                <textarea name="description" id="appt_description" rows="3" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">{{ old('description', $editing->description ?? '') }}</textarea>
                <x-input-error :messages="$errors->get('description')" class="mt-1" />
            </div>

            {{-- Quick-pick chips write into the number input, so any duration is reachable rather than
                 only the six presets the old select offered. --}}
            <div>
                <x-input-label class="mb-1" for="duration_minutes" :value="__('messages.appointments_duration')" />
                <div class="flex flex-wrap items-center gap-2">
                    <div class="{{ $segShell }}">
                        @foreach ($durations as $d)
                            <button type="button" data-duration="{{ $d }}"
                                    class="duration-chip {{ $segItem }} focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] {{ $curDuration === $d ? $segOn : $segIdle }}"
                                    @if ($curDuration === $d) style="{{ $segPressed }}" @endif>{{ $d }}</button>
                        @endforeach
                    </div>
                    <x-text-input type="number" name="duration_minutes" id="duration_minutes" min="5" max="1440" required class="block w-24" value="{{ $curDuration }}" />
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.minutes') }}</span>
                </div>
                <x-input-error :messages="$errors->get('duration_minutes')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.appointments_weekly_hours') }}</h3>
        <x-input-error :messages="$errors->get('weekly_windows')" class="mb-3" />
        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.appointments_times_in', ['tz' => $role->timezone ?: config('app.timezone')]) }}</div>
        {{-- Two tabs in this app are about "when am I free"; say which one this is. --}}
        @if ($role->isTalent())
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('messages.appointments_vs_availability') }}</div>
        @else
            <div class="mb-4"></div>
        @endif

        {{-- Weekly hours. Flex with order-last rather than a grid: on a phone the day name and the two
             icon buttons share one line and the time ranges take the full width below, instead of the
             three wrapped lines the old flex row produced. --}}
        <div id="weekly-hours">
            @foreach ($days as $dayNum => $dayLabel)
                <div class="day-row {{ $rowClass }}" data-day="{{ $dayNum }}">
                    <label class="flex w-24 flex-shrink-0 items-center gap-2">
                        <input type="checkbox" class="day-enabled h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" {{ ! empty($windows[$dayNum]) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ $dayLabel }}</span>
                    </label>
                    <div class="ranges order-last w-full space-y-2 sm:order-none sm:w-auto sm:flex-1">
                        @foreach (($windows[$dayNum] ?? []) as $range)
                            <div class="range-row flex items-center gap-1">
                                <select class="range-start {{ $timeSelectClass }} min-w-0 flex-1 sm:flex-none">
                                    @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $range['start'] == $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
                                </select>
                                <span class="text-gray-500 dark:text-gray-400" aria-hidden="true">-</span>
                                <select class="range-end {{ $timeSelectClass }} min-w-0 flex-1 sm:flex-none">
                                    @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $range['end'] == $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
                                </select>
                                <button type="button" class="remove-range {{ $iconBtn }} hover:text-red-600 dark:hover:text-red-400" aria-label="{{ __('messages.delete') }}" title="{{ __('messages.delete') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    {{-- Icons, not two text buttons: repeated seven times the labels were most of the
                         visual weight of the panel. The strings survive as the accessible names.
                         Copy is disabled on a day with no hours in effect: there is nothing to copy,
                         and an enabled control there would erase every other day. --}}
                    <div class="ms-auto flex flex-shrink-0 items-center gap-1 sm:ms-0">
                        <button type="button" class="add-range {{ $iconBtn }} text-[var(--brand-blue)]" aria-label="{{ __('messages.appointments_add_range') }}" title="{{ __('messages.appointments_add_range') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        </button>
                        <button type="button" class="copy-to-days {{ $iconBtn }} disabled:opacity-40 disabled:cursor-not-allowed"
                                aria-label="{{ __('messages.appointments_copy_to_all_days') }}" title="{{ __('messages.appointments_copy_to_all_days') }}"
                                {{ empty($windows[$dayNum]) ? 'disabled' : '' }}>
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <input type="hidden" name="weekly_windows" id="weekly_windows_input">

        {{-- Start times: the slot grid step. Empty means "same as the duration". --}}
        <div class="mt-5">
            <x-input-label class="mb-1" for="slot_interval_minutes" :value="__('messages.appointments_slot_interval')" />
            {{-- Explicit width: left to shrink-wrap, the forms plugin's arrow padding clipped the tail
                 of "Same as the duration". --}}
            <select name="slot_interval_minutes" id="slot_interval_minutes" class="{{ $selectClass }} w-full sm:w-64">
                <option value="">{{ __('messages.appointments_slot_interval_default') }}</option>
                @foreach ($slotIntervals as $iv)
                    <option value="{{ $iv }}" {{ (string) $curInterval === (string) $iv ? 'selected' : '' }}>{{ $iv }} {{ __('messages.minutes') }}</option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('slot_interval_minutes')" class="mt-1" />
        </div>
    </div>

    {{-- Per-date overrides. The slot engine and the controller have always honoured these; there
         was simply no way to enter them. --}}
    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.appointments_date_overrides') }}</h3>
        <x-input-error :messages="$errors->get('date_overrides')" class="mb-3" />
        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('messages.appointments_date_overrides_help') }}</div>
        <div id="date-overrides">
            @foreach ($overrides as $date => $ranges)
                <div class="override-row {{ $rowClass }}">
                    <input type="text" class="override-date appt-override-date w-36 flex-shrink-0 {{ $timeSelectClass }}" value="{{ $date }}" placeholder="{{ __('messages.date') }}">
                    <label class="flex flex-shrink-0 items-center gap-2">
                        <input type="checkbox" class="override-closed h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" {{ empty($ranges) ? 'checked' : '' }}>
                        <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.unavailable') }}</span>
                    </label>
                    <div class="ranges order-last w-full space-y-2 sm:order-none sm:w-auto sm:flex-1 {{ empty($ranges) ? 'hidden' : '' }}">
                        @foreach ($ranges as $range)
                            <div class="range-row flex items-center gap-1">
                                <select class="range-start {{ $timeSelectClass }} min-w-0 flex-1 sm:flex-none">
                                    @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $range['start'] == $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
                                </select>
                                <span class="text-gray-500 dark:text-gray-400" aria-hidden="true">-</span>
                                <select class="range-end {{ $timeSelectClass }} min-w-0 flex-1 sm:flex-none">
                                    @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $range['end'] == $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
                                </select>
                                <button type="button" class="remove-range {{ $iconBtn }} hover:text-red-600 dark:hover:text-red-400" aria-label="{{ __('messages.delete') }}" title="{{ __('messages.delete') }}">
                                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <div class="ms-auto flex flex-shrink-0 items-center gap-1 sm:ms-0">
                        <button type="button" class="add-range {{ $iconBtn }} text-[var(--brand-blue)] {{ empty($ranges) ? 'hidden' : '' }}" aria-label="{{ __('messages.appointments_add_range') }}" title="{{ __('messages.appointments_add_range') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
                        </button>
                        <button type="button" class="remove-override {{ $iconBtn }} hover:text-red-600 dark:hover:text-red-400" aria-label="{{ __('messages.delete') }}" title="{{ __('messages.delete') }}">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                        </button>
                    </div>
                </div>
            @endforeach
        </div>
        <button type="button" id="add-override" class="mt-3 text-sm text-[var(--brand-blue)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">+ {{ __('messages.add_date') }}</button>
        <input type="hidden" name="date_overrides" id="date_overrides_input">
    </div>

    </div>{{-- /column 1 --}}

    <div class="space-y-4">

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.appointments_scheduling_rules') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label class="mb-1" for="buffer_before_minutes" :value="__('messages.appointments_buffer_before')" />
                <x-text-input type="number" name="buffer_before_minutes" id="buffer_before_minutes" min="0" class="block w-full" value="{{ old('buffer_before_minutes', $editing->buffer_before_minutes ?? 0) }}" />
                <x-input-error :messages="$errors->get('buffer_before_minutes')" class="mt-1" />
            </div>
            <div>
                <x-input-label class="mb-1" for="buffer_after_minutes" :value="__('messages.appointments_buffer_after')" />
                <x-text-input type="number" name="buffer_after_minutes" id="buffer_after_minutes" min="0" class="block w-full" value="{{ old('buffer_after_minutes', $editing->buffer_after_minutes ?? 0) }}" />
                <x-input-error :messages="$errors->get('buffer_after_minutes')" class="mt-1" />
            </div>
            <div>
                <x-input-label class="mb-1" for="min_notice_hours" :value="__('messages.appointments_min_notice')" />
                <x-text-input type="number" name="min_notice_hours" id="min_notice_hours" min="0" class="block w-full" value="{{ old('min_notice_hours', $editing->min_notice_hours ?? 0) }}" />
                <x-input-error :messages="$errors->get('min_notice_hours')" class="mt-1" />
            </div>
            <div>
                <x-input-label class="mb-1" for="max_advance_days" :value="__('messages.appointments_booking_window')" />
                <x-text-input type="number" name="max_advance_days" id="max_advance_days" min="1" class="block w-full" value="{{ old('max_advance_days', $editing->max_advance_days ?? 60) }}" />
                <x-input-error :messages="$errors->get('max_advance_days')" class="mt-1" />
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.location') }}</h3>
        {{-- Radio chips rather than a select: same control language as the duration quick-picks, a
             bigger touch target, and all three options readable without opening anything. --}}
        <div class="{{ $segShell }}" id="location-type" role="radiogroup" aria-label="{{ __('messages.location') }}">
            @foreach (['in_person' => __('messages.appointments_in_person'), 'online' => __('messages.online'), 'phone' => __('messages.phone')] as $lt => $ltLabel)
                {{-- checked immediately after value: AppointmentAdminTest asserts on that exact
                     substring for both this group and the payment methods. --}}
                <label>
                    <input type="radio" name="location_type" value="{{ $lt }}" {{ $curLocationType === $lt ? 'checked' : '' }} class="sr-only peer">
                    <span class="{{ $segRadio }}">{{ $ltLabel }}</span>
                </label>
            @endforeach
        </div>
        <x-input-error :messages="$errors->get('location_type')" class="mt-1" />

        {{-- The class lives on the wrapper so the label hides with its field, but the disabled flag
             stays on the input itself: a hidden invalid type="url" value would otherwise block submit
             with an un-focusable constraint-validation error. --}}
        <div class="loc-group loc-in_person mt-4">
            <x-input-label class="mb-1" for="location_address" :value="__('messages.address')" />
            <x-text-input type="text" name="location_address" id="location_address" value="{{ old('location_address', $editing->location_address ?? '') }}" class="block w-full" />
            <x-input-error :messages="$errors->get('location_address')" class="mt-1" />
        </div>
        <div class="loc-group loc-online mt-4">
            <x-input-label class="mb-1" for="location_url" :value="__('messages.url')" />
            <x-text-input type="url" name="location_url" id="location_url" placeholder="https://" value="{{ old('location_url', $editing->location_url ?? '') }}" class="block w-full" />
            <x-input-error :messages="$errors->get('location_url')" class="mt-1" />
        </div>
        <div class="loc-group loc-phone mt-4">
            <x-input-label class="mb-1" for="location_phone" :value="__('messages.phone')" />
            <x-text-input type="text" name="location_phone" id="location_phone" value="{{ old('location_phone', $editing->location_phone ?? '') }}" class="block w-full" />
            <x-input-error :messages="$errors->get('location_phone')" class="mt-1" />
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.price') }}</h3>

        {{-- Free was previously reachable only by typing a zero, and nothing said that a non-zero
             amount is what reveals the currency and the payment methods. --}}
        <div class="{{ $segShell }}" id="price-mode">
            @foreach (['free' => __('messages.free'), 'paid' => __('messages.paid')] as $pmode => $pmodeLabel)
                <button type="button" data-price-mode="{{ $pmode }}"
                        class="price-mode-chip {{ $segItem }} focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">{{ $pmodeLabel }}</button>
            @endforeach
        </div>

        <div id="price-fields" class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <x-input-label class="mb-1" for="price_input" :value="__('messages.amount')" />
                <x-text-input type="number" step="0.01" min="0" name="price" id="price_input" value="{{ $curPrice }}" class="block w-full" />
                <x-input-error :messages="$errors->get('price')" class="mt-1" />
            </div>
            {{-- Free-text was a 3-char box with no validation feedback; this is the same searchable
                 select the event form uses, enhanced by public/js/searchable-select.js. --}}
            <div>
                <x-input-label class="mb-1" for="currency_code" :value="__('messages.currency')" />
                <select name="currency_code" id="currency_code" data-searchable class="{{ $selectClass }} block w-full">
                    <option value=""></option>
                    @foreach ($currencies as $currency)
                        @if ($loop->index == 2)
                            <option disabled>──────────</option>
                        @endif
                        <option value="{{ $currency->value }}" {{ $curCurrency == $currency->value ? 'selected' : '' }}>
                            {{ $currency->value }} - {{ $currency->label }}
                        </option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('currency_code')" class="mt-1" />
            </div>
        </div>

        <div id="payment-methods" class="mt-4">
            <x-input-label class="mb-1" :value="__('messages.payment_method')" />
            <div class="{{ $segShell }}" role="radiogroup" aria-label="{{ __('messages.payment_method') }}">
                @foreach (['cash' => __('messages.cash'), 'stripe' => 'Stripe', 'payment_url' => __('messages.payment_url')] as $pm => $pmLabel)
                    <label>
                        <input type="radio" name="payment_method" value="{{ $pm }}" {{ $curPaymentMethod === $pm ? 'checked' : '' }} class="sr-only peer">
                        <span class="{{ $segRadio }}">{{ $pmLabel }}</span>
                    </label>
                @endforeach
            </div>
            <x-input-error :messages="$errors->get('payment_method')" class="mt-1" />
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.appointments_booking_form') }}</h3>
        <div class="space-y-4">
            <x-toggle name="requires_approval" :label="__('messages.appointments_require_approval')" :checked="$oldBool('requires_approval', $editing->requires_approval ?? false)" />
            <x-toggle name="ask_phone" :label="__('messages.appointments_ask_phone')" :checked="$askPhoneOn" />
            {{-- Nested: "require" only means anything while the field is being asked for at all. The
                 controller normalises the stored value the same way. --}}
            <div id="require-phone-row" class="ms-14 {{ $askPhoneOn ? '' : 'hidden' }}">
                <x-toggle name="require_phone" :label="__('messages.appointments_require_phone')" :checked="$oldBool('require_phone', $editing->require_phone ?? false)" />
            </div>
        </div>
    </div>

    </div>{{-- /column 2 --}}

    </div>{{-- /grid --}}
</form>

<template id="range-template">
    <div class="range-row flex items-center gap-1">
        <select class="range-start {{ $timeSelectClass }} min-w-0 flex-1 sm:flex-none">
            @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $t['value'] === '09:00' ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
        </select>
        <span class="text-gray-500 dark:text-gray-400" aria-hidden="true">-</span>
        <select class="range-end {{ $timeSelectClass }} min-w-0 flex-1 sm:flex-none">
            @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $t['value'] === '17:00' ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
        </select>
        <button type="button" class="remove-range {{ $iconBtn }} hover:text-red-600 dark:hover:text-red-400" aria-label="{{ __('messages.delete') }}" title="{{ __('messages.delete') }}">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
        </button>
    </div>
</template>

<template id="override-template">
    <div class="override-row {{ $rowClass }}">
        <input type="text" class="override-date appt-override-date w-36 flex-shrink-0 {{ $timeSelectClass }}" value="" placeholder="{{ __('messages.date') }}">
        <label class="flex flex-shrink-0 items-center gap-2">
            <input type="checkbox" class="override-closed h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" checked>
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.unavailable') }}</span>
        </label>
        <div class="ranges order-last w-full space-y-2 sm:order-none sm:w-auto sm:flex-1 hidden"></div>
        <div class="ms-auto flex flex-shrink-0 items-center gap-1 sm:ms-0">
            <button type="button" class="add-range {{ $iconBtn }} text-[var(--brand-blue)] hidden" aria-label="{{ __('messages.appointments_add_range') }}" title="{{ __('messages.appointments_add_range') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" /></svg>
            </button>
            <button type="button" class="remove-override {{ $iconBtn }} hover:text-red-600 dark:hover:text-red-400" aria-label="{{ __('messages.delete') }}" title="{{ __('messages.delete') }}">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
            </button>
        </div>
    </div>
</template>

<script {!! nonce_attr() !!}>
    (function () {
        var form = document.getElementById('appt-editor-form');
        if (!form) return;
        var tpl = document.getElementById('range-template');
        var overrideTpl = document.getElementById('override-template');
        var overridesList = document.getElementById('date-overrides');

        // Flatpickr for override dates (native date inputs are not used anywhere in this app).
        function initOverrideDate(el) {
            if (!el || el._fpDone || typeof flatpickr === 'undefined') return;
            el._fpDone = true;
            var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
            flatpickr(el, Object.assign({
                allowInput: true,
                enableTime: false,
                altInput: true,
                altFormat: "M j, Y",
                dateFormat: "Y-m-d",
            }, fpLocale ? { locale: fpLocale } : {}));
        }
        form.querySelectorAll('.appt-override-date').forEach(initOverrideDate);

        // A day can only be copied FROM when it actually has hours in effect. Otherwise the copy is
        // both meaningless and destructive - it would clear every other day - so the control is
        // disabled rather than left to no-op silently.
        function syncCopyButtons() {
            form.querySelectorAll('.day-row').forEach(function (row) {
                var btn = row.querySelector('.copy-to-days');
                if (!btn) return;
                var enabled = row.querySelector('.day-enabled');
                btn.disabled = ! (enabled && enabled.checked) || row.querySelectorAll('.range-row').length === 0;
            });
        }
        syncCopyButtons();

        form.addEventListener('click', function (e) {
            // closest(), not classList on e.target: the row buttons are icons now, so a click lands
            // on the <svg> or its <path> rather than on the button itself.
            var target = e.target.closest('button');
            if (!target || !form.contains(target)) return;

            if (target.classList.contains('add-range')) {
                // Weekly rows and override rows share the range markup.
                var row = target.closest('.day-row, .override-row');
                var ranges = row.querySelector('.ranges');
                ranges.classList.remove('hidden');
                ranges.appendChild(tpl.content.cloneNode(true));
                var enabled = row.querySelector('.day-enabled');
                if (enabled) enabled.checked = true;
                syncCopyButtons();
                return;
            }

            if (target.classList.contains('remove-range')) {
                target.closest('.range-row').remove();
                syncCopyButtons();
                return;
            }

            if (target.classList.contains('remove-override')) {
                target.closest('.override-row').remove();
                return;
            }

            // Copy this day's hours onto every other day - setting a week one dropdown pair at a time
            // is the slowest part of this form. It enables the other days as it goes, rather than
            // skipping the disabled ones, which used to make the button a silent no-op on a fresh
            // type where every other day is still off.
            if (target.classList.contains('copy-to-days')) {
                var source = target.closest('.day-row');
                var sourceRanges = [];
                source.querySelectorAll('.range-row').forEach(function (rr) {
                    sourceRanges.push([rr.querySelector('.range-start').value, rr.querySelector('.range-end').value]);
                });
                // Never propagate an empty day. syncCopyButtons() disables the control in that case,
                // but a keyboard or scripted activation must not be able to wipe the whole week -
                // there is no confirmation and no undo behind this button.
                if (! sourceRanges.length) return;
                form.querySelectorAll('.day-row').forEach(function (row) {
                    if (row === source) return;
                    row.querySelector('.day-enabled').checked = true;
                    var container = row.querySelector('.ranges');
                    container.innerHTML = '';
                    sourceRanges.forEach(function (pair) {
                        container.appendChild(tpl.content.cloneNode(true));
                        var added = container.lastElementChild;
                        added.querySelector('.range-start').value = pair[0];
                        added.querySelector('.range-end').value = pair[1];
                    });
                });
                syncCopyButtons();
                return;
            }

            if (target.id === 'add-override') {
                overridesList.appendChild(overrideTpl.content.cloneNode(true));
                initOverrideDate(overridesList.lastElementChild.querySelector('.appt-override-date'));
            }
        });

        form.addEventListener('change', function (e) {
            // Unchecking a day leaves its ranges in the DOM but takes them out of effect, so it can
            // no longer be a copy source.
            if (e.target.classList.contains('day-enabled')) {
                syncCopyButtons();
                return;
            }

            // "Unavailable" hides the hours for that date; the serializer posts [] for it.
            if (!e.target.classList.contains('override-closed')) return;
            var row = e.target.closest('.override-row');
            var closed = e.target.checked;
            row.querySelector('.ranges').classList.toggle('hidden', closed);
            row.querySelector('.add-range').classList.toggle('hidden', closed);
        });

        // Duration chips drive the number input, and typing keeps the chip state honest.
        var durationInput = document.getElementById('duration_minutes');
        function syncDurationChips() {
            form.querySelectorAll('.duration-chip').forEach(function (chip) {
                var on = chip.dataset.duration === String(parseInt(durationInput.value, 10));
                chip.classList.toggle('bg-white', on);
                chip.classList.toggle('dark:bg-gray-900', on);
                chip.classList.toggle('text-gray-900', on);
                chip.classList.toggle('dark:text-white', on);
                chip.classList.toggle('shadow-sm', on);
                chip.classList.toggle('text-gray-500', !on);
                chip.classList.toggle('dark:text-gray-400', !on);
                chip.style.boxShadow = on ? 'inset 0 2px 4px rgba(0, 0, 0, 0.08)' : '';
            });
        }
        form.addEventListener('click', function (e) {
            var chip = e.target.closest('.duration-chip');
            if (!chip) return;
            durationInput.value = chip.dataset.duration;
            syncDurationChips();
        });
        durationInput.addEventListener('input', syncDurationChips);

        // "Require a phone number" only applies while the field is asked for at all. x-toggle uses
        // the field name as the element id.
        var askPhone = document.getElementById('ask_phone');
        var requirePhoneRow = document.getElementById('require-phone-row');
        if (askPhone && requirePhoneRow) {
            askPhone.addEventListener('change', function () {
                requirePhoneRow.classList.toggle('hidden', !askPhone.checked);
            });
        }

        // Show/hide location fields by type.
        function currentLocType() {
            var checked = form.querySelector('input[name="location_type"]:checked');
            return checked ? checked.value : 'in_person';
        }
        function syncLoc() {
            form.querySelectorAll('.loc-group').forEach(function (g) {
                g.style.display = 'none';
                g.querySelectorAll('input').forEach(function (f) { f.disabled = true; });
            });
            form.querySelectorAll('.loc-' + currentLocType()).forEach(function (g) {
                g.style.display = 'block';
                g.querySelectorAll('input').forEach(function (f) { f.disabled = false; });
            });
        }
        form.querySelectorAll('input[name="location_type"]').forEach(function (r) {
            r.addEventListener('change', syncLoc);
        });
        syncLoc();

        // Free/paid drives the amount, the currency and the payment methods together.
        var priceInput = document.getElementById('price_input');
        var priceFields = document.getElementById('price-fields');
        var pm = document.getElementById('payment-methods');
        var priceMode = parseFloat(priceInput.value) > 0 ? 'paid' : 'free';
        function syncPrice() {
            var paid = priceMode === 'paid';
            priceFields.style.display = paid ? '' : 'none';
            pm.style.display = paid ? '' : 'none';
            form.querySelectorAll('.price-mode-chip').forEach(function (chip) {
                var on = chip.dataset.priceMode === priceMode;
                chip.classList.toggle('bg-white', on);
                chip.classList.toggle('dark:bg-gray-900', on);
                chip.classList.toggle('text-gray-900', on);
                chip.classList.toggle('dark:text-white', on);
                chip.classList.toggle('shadow-sm', on);
                chip.classList.toggle('text-gray-500', !on);
                chip.classList.toggle('dark:text-gray-400', !on);
                chip.style.boxShadow = on ? 'inset 0 2px 4px rgba(0, 0, 0, 0.08)' : '';
            });
        }
        form.addEventListener('click', function (e) {
            var chip = e.target.closest('.price-mode-chip');
            if (!chip) return;
            priceMode = chip.dataset.priceMode;
            // Free has to post a real zero, not just hide the box.
            if (priceMode === 'free') priceInput.value = 0;
            syncPrice();
        });
        syncPrice();

        form.addEventListener('submit', function () {
            var windows = {};
            form.querySelectorAll('.day-row').forEach(function (row) {
                var day = row.dataset.day;
                if (!row.querySelector('.day-enabled').checked) { windows[day] = []; return; }
                var ranges = [];
                row.querySelectorAll('.range-row').forEach(function (rr) {
                    ranges.push({ start: rr.querySelector('.range-start').value, end: rr.querySelector('.range-end').value });
                });
                windows[day] = ranges;
            });
            document.getElementById('weekly_windows_input').value = JSON.stringify(windows);

            var overrides = {};
            form.querySelectorAll('.override-row').forEach(function (row) {
                var date = row.querySelector('.override-date').value;
                if (!date) return;
                if (row.querySelector('.override-closed').checked) { overrides[date] = []; return; }
                var ranges = [];
                row.querySelectorAll('.range-row').forEach(function (rr) {
                    ranges.push({ start: rr.querySelector('.range-start').value, end: rr.querySelector('.range-end').value });
                });
                overrides[date] = ranges;
            });
            // Always posted, so clearing the last override stores {} -> no overrides.
            document.getElementById('date_overrides_input').value = JSON.stringify(overrides);
        });

        // Unsaved-changes guard, same shape as the schedule editor's. A whole week of hours is a lot
        // to lose to a stray click, and Cancel carries js-cancel-btn so it opts out.
        var isDirty = false;
        form.addEventListener('input', function () { isDirty = true; });
        form.addEventListener('change', function () { isDirty = true; });
        form.addEventListener('submit', function (e) {
            if (!e.defaultPrevented) { isDirty = false; }
        });
        window.addEventListener('beforeunload', function (e) {
            if (isDirty && !window._skipUnsavedWarning) { e.preventDefault(); e.returnValue = ''; }
        });
    })();
</script>
