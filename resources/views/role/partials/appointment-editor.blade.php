@php
    use App\Utils\UrlUtils;
    $durations = [15, 30, 45, 60, 90, 120];
    $curDuration = $editing->duration_minutes ?? 30;
    $backUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']);
@endphp

<form method="POST"
      action="{{ $editing ? route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $editing->hashedId()]) : route('appointments.store', ['subdomain' => $role->subdomain]) }}"
      id="appt-editor-form" class="ap-card rounded-xl p-6 space-y-5 max-w-2xl text-gray-900 dark:text-gray-300">
    @csrf
    @if ($editing) @method('PUT') @endif

    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $editing ? __('messages.edit') : __('messages.appointments_new_type') }}</h2>

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3 text-sm text-red-700 dark:text-red-300">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div>
        <x-input-label class="mb-1" :value="__('messages.name')" />
        <x-text-input type="text" name="name" required class="block w-full"
               value="{{ old('name', $editing->name ?? __('messages.appointments_default_type_name')) }}" />
    </div>

    <div>
        <x-input-label class="mb-1" :value="__('messages.description')" />
        <textarea name="description" rows="2" class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">{{ old('description', $editing->description ?? '') }}</textarea>
    </div>

    <div>
        <x-input-label class="mb-1" :value="__('messages.appointments_duration')" />
        <select name="duration_minutes" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
            @foreach ($durations as $d)
                <option value="{{ $d }}" {{ $curDuration == $d ? 'selected' : '' }}>{{ $d }} {{ __('messages.minutes') }}</option>
            @endforeach
            @if (! in_array($curDuration, $durations))
                <option value="{{ $curDuration }}" selected>{{ $curDuration }} {{ __('messages.minutes') }}</option>
            @endif
        </select>
    </div>

    {{-- Weekly hours --}}
    <div>
        <x-input-label class="mb-2" :value="__('messages.appointments_weekly_hours')" />
        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">{{ __('messages.appointments_times_in', ['tz' => $role->timezone ?: config('app.timezone')]) }}</div>
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
                                <select class="range-start px-2 py-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
                                    @foreach ($timeOptions as $t)<option value="{{ $t }}" {{ $range['start'] == $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
                                </select>
                                <span class="text-gray-500 dark:text-gray-400">-</span>
                                <select class="range-end px-2 py-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
                                    @foreach ($timeOptions as $t)<option value="{{ $t }}" {{ $range['end'] == $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
                                </select>
                                <button type="button" class="remove-range text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 px-2 transition-all duration-200" aria-label="{{ __('messages.delete') }}">&times;</button>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="add-range text-sm text-[var(--brand-blue)] pt-1">+ {{ __('messages.appointments_add_range') }}</button>
                </div>
            @endforeach
        </div>
        <input type="hidden" name="weekly_windows" id="weekly_windows_input">
    </div>

    {{-- Scheduling rules --}}
    <div class="grid grid-cols-2 gap-3">
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

    {{-- Location --}}
    <div>
        <x-input-label class="mb-1" :value="__('messages.location')" />
        <select name="location_type" id="location_type" class="border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
            <option value="in_person" {{ ($editing->location_type ?? 'in_person') === 'in_person' ? 'selected' : '' }}>{{ __('messages.appointments_in_person') }}</option>
            <option value="online" {{ ($editing->location_type ?? '') === 'online' ? 'selected' : '' }}>{{ __('messages.online') }}</option>
            <option value="phone" {{ ($editing->location_type ?? '') === 'phone' ? 'selected' : '' }}>{{ __('messages.phone') }}</option>
        </select>
        <x-text-input type="text" name="location_address" placeholder="{{ __('messages.location') }}" value="{{ old('location_address', $editing->location_address ?? '') }}" class="loc-field loc-in_person mt-2 block w-full" />
        <x-text-input type="url" name="location_url" placeholder="https://" value="{{ old('location_url', $editing->location_url ?? '') }}" class="loc-field loc-online mt-2 block w-full" />
        <x-text-input type="text" name="location_phone" placeholder="{{ __('messages.phone') }}" value="{{ old('location_phone', $editing->location_phone ?? '') }}" class="loc-field loc-phone mt-2 block w-full" />
    </div>

    {{-- Price --}}
    <div>
        <x-input-label class="mb-1" :value="__('messages.price')" />
        <div class="flex gap-2">
            <x-text-input type="number" step="0.01" min="0" name="price" id="price_input" value="{{ old('price', $editing->price ?? 0) }}" class="block w-32" />
            <x-text-input type="text" name="currency_code" maxlength="3" placeholder="USD" value="{{ old('currency_code', $editing->currency_code ?? '') }}" class="block w-24 uppercase" />
        </div>
        <div id="payment-methods" class="mt-2 flex gap-3 text-sm">
            @foreach (['cash' => __('messages.cash'), 'stripe' => 'Stripe', 'payment_url' => __('messages.payment_url') ?? 'Payment URL'] as $pm => $pmLabel)
                <label class="flex items-center gap-1 text-gray-700 dark:text-gray-300"><input type="radio" name="payment_method" value="{{ $pm }}" {{ ($editing->payment_method ?? 'cash') === $pm ? 'checked' : '' }} class="h-4 w-4 border-gray-300 dark:border-gray-600 dark:bg-gray-900 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"> {{ $pmLabel }}</label>
            @endforeach
        </div>
    </div>

    {{-- Toggles --}}
    <div class="space-y-4">
        <x-toggle name="requires_approval" :label="__('messages.appointments_require_approval')" :checked="$editing->requires_approval ?? false" />
        <x-toggle name="ask_phone" :label="__('messages.appointments_ask_phone')" :checked="$editing->ask_phone ?? false" />
        <x-toggle name="require_phone" :label="__('messages.appointments_require_phone')" :checked="$editing->require_phone ?? false" />
        <x-toggle name="is_active" :label="__('messages.appointments_active')" :checked="$editing->is_active ?? true" />
    </div>

    <div class="flex gap-2 pt-2">
        <x-secondary-link :href="$backUrl">{{ __('messages.cancel') }}</x-secondary-link>
        <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
    </div>
</form>

<template id="range-template">
    <div class="range-row flex items-center gap-1">
        <select class="range-start px-2 py-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
            @foreach ($timeOptions as $t)<option value="{{ $t }}" {{ $t === '09:00' ? 'selected' : '' }}>{{ $t }}</option>@endforeach
        </select>
        <span class="text-gray-500 dark:text-gray-400">-</span>
        <select class="range-end px-2 py-1 rounded border-gray-300 dark:border-gray-700 dark:bg-gray-900 text-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-sm">
            @foreach ($timeOptions as $t)<option value="{{ $t }}" {{ $t === '17:00' ? 'selected' : '' }}>{{ $t }}</option>@endforeach
        </select>
        <button type="button" class="remove-range text-gray-500 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 px-2 transition-all duration-200" aria-label="{{ __('messages.delete') }}">&times;</button>
    </div>
</template>

<script {!! nonce_attr() !!}>
    (function () {
        var form = document.getElementById('appt-editor-form');
        if (!form) return;
        var tpl = document.getElementById('range-template');

        form.addEventListener('click', function (e) {
            if (e.target.classList.contains('add-range')) {
                var ranges = e.target.closest('.day-row').querySelector('.ranges');
                ranges.appendChild(tpl.content.cloneNode(true));
                e.target.closest('.day-row').querySelector('.day-enabled').checked = true;
            } else if (e.target.classList.contains('remove-range')) {
                e.target.closest('.range-row').remove();
            }
        });

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

        // Show/hide payment methods by price.
        var priceInput = document.getElementById('price_input');
        var pm = document.getElementById('payment-methods');
        function syncPm() { pm.style.display = (parseFloat(priceInput.value) > 0) ? 'flex' : 'none'; }
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
        });
    })();
</script>
