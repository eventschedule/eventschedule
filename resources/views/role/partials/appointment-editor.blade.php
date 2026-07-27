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
    $curCurrency = old('currency_code', $editing->currency_code
        ?? \App\Utils\MoneyUtils::getCurrencyForCountry($role->country_code));

    // x-toggle posts a hidden "0" alongside the checkbox, so old() yields '0'/'1' on a re-render and
    // the raw default on a first render - filter_var reads both.
    $oldBool = fn ($field, $default) => filter_var(old($field, $default ? '1' : '0'), FILTER_VALIDATE_BOOLEAN);
    $askPhoneOn = $oldBool('ask_phone', $editing->ask_phone ?? false);

    $sectionClass = 'ap-card rounded-xl p-6';
    $headingClass = 'text-base font-semibold text-gray-900 dark:text-gray-100 mb-4';
    $selectClass = 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm';
    $timeSelectClass = 'px-2 py-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm';
@endphp

<form method="POST"
      action="{{ $editing ? route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $editing->hashedId()]) : route('appointments.store', ['subdomain' => $role->subdomain]) }}"
      id="appt-editor-form" class="space-y-4 max-w-2xl text-gray-900 dark:text-gray-300">
    @csrf
    @if ($editing) @method('PUT') @endif

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editing ? __('messages.edit') : __('messages.appointments_new_type') }}</h2>

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3 text-sm text-red-700 dark:text-red-300">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    {{-- Sections mirror the /docs/appointments anchors so the Help button and the docs stay aligned. --}}
    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.details') }}</h3>

        <div class="space-y-5">
            <div>
                <x-input-label class="mb-1" :value="__('messages.name')" />
                <x-text-input type="text" name="name" required class="block w-full"
                       value="{{ old('name', $editing->name ?? __('messages.appointments_default_type_name')) }}" />
            </div>

            <div>
                <x-input-label class="mb-1" :value="__('messages.description')" />
                <textarea name="description" rows="3" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">{{ old('description', $editing->description ?? '') }}</textarea>
            </div>

            {{-- Quick-pick chips write into the number input, so any duration is reachable rather than
                 only the six presets the old select offered. --}}
            <div>
                <x-input-label class="mb-1" for="duration_minutes" :value="__('messages.appointments_duration')" />
                <div class="flex flex-wrap items-center gap-2">
                    <div class="inline-flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-[#252526] p-1">
                        @foreach ($durations as $d)
                            <button type="button" data-duration="{{ $d }}"
                                    class="duration-chip rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] {{ $curDuration === $d ? 'bg-white dark:bg-[#1e1e1e] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}"
                                    @if ($curDuration === $d) style="box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);" @endif>{{ $d }}</button>
                        @endforeach
                    </div>
                    <x-text-input type="number" name="duration_minutes" id="duration_minutes" min="5" max="1440" required class="block w-24" value="{{ $curDuration }}" />
                    <span class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.minutes') }}</span>
                </div>
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.availability') }}</h3>
        <div class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ __('messages.appointments_times_in', ['tz' => $role->timezone ?: config('app.timezone')]) }}</div>
        {{-- Two tabs in this app are about "when am I free"; say which one this is. --}}
        @if ($role->isTalent())
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-4">{{ __('messages.appointments_vs_availability') }}</div>
        @else
            <div class="mb-4"></div>
        @endif

        {{-- Weekly hours --}}
        <div>
            <x-input-label class="mb-2" :value="__('messages.appointments_weekly_hours')" />
            <div id="weekly-hours" class="space-y-2">
                @foreach ($days as $dayNum => $dayLabel)
                    <div class="day-row flex flex-wrap items-start gap-2 py-1" data-day="{{ $dayNum }}">
                        <label class="flex items-center gap-2 w-32 pt-2">
                            <input type="checkbox" class="day-enabled h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" {{ ! empty($windows[$dayNum]) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ $dayLabel }}</span>
                        </label>
                        <div class="ranges flex-1 space-y-1">
                            @foreach (($windows[$dayNum] ?? []) as $range)
                                <div class="range-row flex items-center gap-1">
                                    <select class="range-start {{ $timeSelectClass }}">
                                        @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $range['start'] == $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
                                    </select>
                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                    <select class="range-end {{ $timeSelectClass }}">
                                        @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $range['end'] == $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
                                    </select>
                                    <button type="button" class="remove-range text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 px-2 transition-all duration-200" aria-label="{{ __('messages.delete') }}">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" class="add-range text-sm text-[var(--brand-blue)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">+ {{ __('messages.appointments_add_range') }}</button>
                            <button type="button" class="copy-to-days text-xs text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">{{ __('messages.appointments_copy_to_all_days') }}</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <input type="hidden" name="weekly_windows" id="weekly_windows_input">
        </div>

        {{-- Start times: the slot grid step. Empty means "same as the duration". --}}
        <div class="mt-5">
            <x-input-label class="mb-1" for="slot_interval_minutes" :value="__('messages.appointments_slot_interval')" />
            <select name="slot_interval_minutes" id="slot_interval_minutes" class="{{ $selectClass }}">
                <option value="">{{ __('messages.appointments_slot_interval_default') }}</option>
                @foreach ($slotIntervals as $iv)
                    <option value="{{ $iv }}" {{ (string) $curInterval === (string) $iv ? 'selected' : '' }}>{{ $iv }} {{ __('messages.minutes') }}</option>
                @endforeach
            </select>
        </div>

        {{-- Per-date overrides. The slot engine and the controller have always honoured these; there
             was simply no way to enter them. --}}
        <div class="mt-5">
            <x-input-label class="mb-1" :value="__('messages.appointments_date_overrides')" />
            <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('messages.appointments_date_overrides_help') }}</div>
            <div id="date-overrides" class="space-y-2">
                @foreach ($overrides as $date => $ranges)
                    <div class="override-row flex flex-wrap items-start gap-2 py-1">
                        <input type="text" class="override-date appt-override-date w-36 {{ $timeSelectClass }}" value="{{ $date }}" placeholder="{{ __('messages.date') }}">
                        <label class="flex items-center gap-2 pt-1">
                            <input type="checkbox" class="override-closed h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" {{ empty($ranges) ? 'checked' : '' }}>
                            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.unavailable') }}</span>
                        </label>
                        <div class="ranges flex-1 space-y-1 {{ empty($ranges) ? 'hidden' : '' }}">
                            @foreach ($ranges as $range)
                                <div class="range-row flex items-center gap-1">
                                    <select class="range-start {{ $timeSelectClass }}">
                                        @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $range['start'] == $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
                                    </select>
                                    <span class="text-gray-500 dark:text-gray-400">-</span>
                                    <select class="range-end {{ $timeSelectClass }}">
                                        @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $range['end'] == $t['value'] ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
                                    </select>
                                    <button type="button" class="remove-range text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 px-2 transition-all duration-200" aria-label="{{ __('messages.delete') }}">&times;</button>
                                </div>
                            @endforeach
                        </div>
                        <div class="flex items-center gap-2 pt-1">
                            <button type="button" class="add-range text-sm text-[var(--brand-blue)] {{ empty($ranges) ? 'hidden' : '' }} focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">+ {{ __('messages.appointments_add_range') }}</button>
                            <button type="button" class="remove-override text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 px-2 transition-all duration-200" aria-label="{{ __('messages.delete') }}">&times;</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" id="add-override" class="mt-2 text-sm text-[var(--brand-blue)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">+ {{ __('messages.add_date') }}</button>
            <input type="hidden" name="date_overrides" id="date_overrides_input">
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.appointments_scheduling_rules') }}</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
                <x-input-label class="mb-1" :value="__('messages.appointments_buffer_before')" />
                <x-text-input type="number" name="buffer_before_minutes" min="0" class="block w-full" value="{{ old('buffer_before_minutes', $editing->buffer_before_minutes ?? 0) }}" />
            </div>
            <div>
                <x-input-label class="mb-1" :value="__('messages.appointments_buffer_after')" />
                <x-text-input type="number" name="buffer_after_minutes" min="0" class="block w-full" value="{{ old('buffer_after_minutes', $editing->buffer_after_minutes ?? 0) }}" />
            </div>
            <div>
                <x-input-label class="mb-1" :value="__('messages.appointments_min_notice')" />
                <x-text-input type="number" name="min_notice_hours" min="0" class="block w-full" value="{{ old('min_notice_hours', $editing->min_notice_hours ?? 0) }}" />
            </div>
            <div>
                <x-input-label class="mb-1" :value="__('messages.appointments_booking_window')" />
                <x-text-input type="number" name="max_advance_days" min="1" class="block w-full" value="{{ old('max_advance_days', $editing->max_advance_days ?? 60) }}" />
            </div>
        </div>
    </div>

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.location') }}</h3>
        <select name="location_type" id="location_type" class="{{ $selectClass }}">
            <option value="in_person" {{ $curLocationType === 'in_person' ? 'selected' : '' }}>{{ __('messages.appointments_in_person') }}</option>
            <option value="online" {{ $curLocationType === 'online' ? 'selected' : '' }}>{{ __('messages.online') }}</option>
            <option value="phone" {{ $curLocationType === 'phone' ? 'selected' : '' }}>{{ __('messages.phone') }}</option>
        </select>
        <x-text-input type="text" name="location_address" placeholder="{{ __('messages.location') }}" value="{{ old('location_address', $editing->location_address ?? '') }}" class="loc-field loc-in_person mt-2 block w-full" />
        <x-text-input type="url" name="location_url" placeholder="https://" value="{{ old('location_url', $editing->location_url ?? '') }}" class="loc-field loc-online mt-2 block w-full" />
        <x-text-input type="text" name="location_phone" placeholder="{{ __('messages.phone') }}" value="{{ old('location_phone', $editing->location_phone ?? '') }}" class="loc-field loc-phone mt-2 block w-full" />
    </div>

    <div class="{{ $sectionClass }}">
        <h3 class="{{ $headingClass }}">{{ __('messages.price') }}</h3>
        <div class="flex flex-wrap items-end gap-3">
            <div>
                <x-input-label class="mb-1" for="price_input" :value="__('messages.amount')" />
                <x-text-input type="number" step="0.01" min="0" name="price" id="price_input" value="{{ old('price', $editing->price ?? 0) }}" class="block w-32" />
            </div>
            {{-- Free-text was a 3-char box with no validation feedback; this is the same searchable
                 select the event form uses, enhanced by public/js/searchable-select.js. --}}
            <div id="currency-wrapper">
                <x-input-label class="mb-1" for="currency_code" :value="__('messages.currency')" />
                <select name="currency_code" id="currency_code" data-searchable class="{{ $selectClass }}">
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
            </div>
        </div>
        <div id="payment-methods" class="mt-3 flex flex-wrap gap-3 text-sm">
            @foreach (['cash' => __('messages.cash'), 'stripe' => 'Stripe', 'payment_url' => __('messages.payment_url')] as $pm => $pmLabel)
                <label class="flex items-center gap-1 text-gray-700 dark:text-gray-300"><input type="radio" name="payment_method" value="{{ $pm }}" {{ $curPaymentMethod === $pm ? 'checked' : '' }} class="h-4 w-4 border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"> {{ $pmLabel }}</label>
            @endforeach
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

    <div class="{{ $sectionClass }} flex flex-wrap items-center justify-between gap-4">
        <x-toggle name="is_active" :label="__('messages.appointments_active')" :checked="$oldBool('is_active', $editing->is_active ?? true)" />
        <div class="flex gap-2">
            <x-secondary-link :href="$backUrl">{{ __('messages.cancel') }}</x-secondary-link>
            <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
        </div>
    </div>
</form>

<template id="range-template">
    <div class="range-row flex items-center gap-1">
        <select class="range-start {{ $timeSelectClass }}">
            @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $t['value'] === '09:00' ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
        </select>
        <span class="text-gray-500 dark:text-gray-400">-</span>
        <select class="range-end {{ $timeSelectClass }}">
            @foreach ($timeOptions as $t)<option value="{{ $t['value'] }}" {{ $t['value'] === '17:00' ? 'selected' : '' }}>{{ $t['label'] }}</option>@endforeach
        </select>
        <button type="button" class="remove-range text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 px-2 transition-all duration-200" aria-label="{{ __('messages.delete') }}">&times;</button>
    </div>
</template>

<template id="override-template">
    <div class="override-row flex flex-wrap items-start gap-2 py-1">
        <input type="text" class="override-date appt-override-date w-36 {{ $timeSelectClass }}" value="" placeholder="{{ __('messages.date') }}">
        <label class="flex items-center gap-2 pt-1">
            <input type="checkbox" class="override-closed h-4 w-4 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" checked>
            <span class="text-sm text-gray-700 dark:text-gray-300">{{ __('messages.unavailable') }}</span>
        </label>
        <div class="ranges flex-1 space-y-1 hidden"></div>
        <div class="flex items-center gap-2 pt-1">
            <button type="button" class="add-range text-sm text-[var(--brand-blue)] hidden focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] rounded">+ {{ __('messages.appointments_add_range') }}</button>
            <button type="button" class="remove-override text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 px-2 transition-all duration-200" aria-label="{{ __('messages.delete') }}">&times;</button>
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

        form.addEventListener('click', function (e) {
            var target = e.target;

            if (target.classList.contains('add-range')) {
                // Weekly rows and override rows share the range markup.
                var row = target.closest('.day-row, .override-row');
                var ranges = row.querySelector('.ranges');
                ranges.classList.remove('hidden');
                ranges.appendChild(tpl.content.cloneNode(true));
                var enabled = row.querySelector('.day-enabled');
                if (enabled) enabled.checked = true;
                return;
            }

            if (target.classList.contains('remove-range')) {
                target.closest('.range-row').remove();
                return;
            }

            if (target.classList.contains('remove-override')) {
                target.closest('.override-row').remove();
                return;
            }

            // Copy this day's hours onto every other enabled day - setting a week one dropdown pair
            // at a time is the slowest part of this form.
            if (target.classList.contains('copy-to-days')) {
                var source = target.closest('.day-row');
                var sourceRanges = [];
                source.querySelectorAll('.range-row').forEach(function (rr) {
                    sourceRanges.push([rr.querySelector('.range-start').value, rr.querySelector('.range-end').value]);
                });
                form.querySelectorAll('.day-row').forEach(function (row) {
                    if (row === source || !row.querySelector('.day-enabled').checked) return;
                    var container = row.querySelector('.ranges');
                    container.innerHTML = '';
                    sourceRanges.forEach(function (pair) {
                        container.appendChild(tpl.content.cloneNode(true));
                        var added = container.lastElementChild;
                        added.querySelector('.range-start').value = pair[0];
                        added.querySelector('.range-end').value = pair[1];
                    });
                });
                return;
            }

            if (target.id === 'add-override') {
                overridesList.appendChild(overrideTpl.content.cloneNode(true));
                initOverrideDate(overridesList.lastElementChild.querySelector('.appt-override-date'));
            }
        });

        // "Unavailable" hides the hours for that date; the serializer posts [] for it.
        form.addEventListener('change', function (e) {
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
                chip.classList.toggle('dark:bg-[#1e1e1e]', on);
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
        var locType = document.getElementById('location_type');
        function syncLoc() {
            // Hidden fields are also disabled: a hidden invalid type="url" value would otherwise
            // block submit with an un-focusable constraint-validation error.
            form.querySelectorAll('.loc-field').forEach(function (f) { f.style.display = 'none'; f.disabled = true; });
            form.querySelectorAll('.loc-' + locType.value).forEach(function (f) { f.style.display = 'block'; f.disabled = false; });
        }
        locType.addEventListener('change', syncLoc);
        syncLoc();

        // Show/hide payment methods + currency by price.
        var priceInput = document.getElementById('price_input');
        var pm = document.getElementById('payment-methods');
        var currencyWrapper = document.getElementById('currency-wrapper');
        function syncPm() {
            var paid = parseFloat(priceInput.value) > 0;
            pm.style.display = paid ? 'flex' : 'none';
            currencyWrapper.style.display = paid ? 'block' : 'none';
        }
        priceInput.addEventListener('input', syncPm);
        syncPm();

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
    })();
</script>
