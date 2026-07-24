@php
    use App\Utils\UrlUtils;
    $durations = [15, 30, 45, 60, 90, 120];
    $curDuration = $editing->duration_minutes ?? 30;
    $backUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']);
@endphp

<form method="POST"
      action="{{ $editing ? route('appointments.update', ['subdomain' => $role->subdomain, 'hash' => $editing->hashedId()]) : route('appointments.store', ['subdomain' => $role->subdomain]) }}"
      id="appt-editor-form" class="ap-card rounded-xl p-6 space-y-5 max-w-2xl">
    @csrf
    @if ($editing) @method('PUT') @endif

    <h2 class="text-lg font-semibold">{{ $editing ? __('messages.edit') : __('messages.appointments_new_type') }}</h2>

    @if ($errors->any())
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 rounded-lg p-3 text-sm text-red-700 dark:text-red-300">
            <ul class="list-disc list-inside">@foreach ($errors->all() as $e)<li>{{ $e }}</li>@endforeach</ul>
        </div>
    @endif

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('messages.name') }}</label>
        <input type="text" name="name" required value="{{ old('name', $editing->name ?? __('messages.appointments_default_type_name')) }}"
               class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('messages.description') }}</label>
        <textarea name="description" rows="2" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">{{ old('description', $editing->description ?? '') }}</textarea>
    </div>

    <div>
        <label class="block text-sm font-medium mb-1">{{ __('messages.appointments_duration') }}</label>
        <select name="duration_minutes" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
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
        <label class="block text-sm font-medium mb-2">{{ __('messages.appointments_weekly_hours') }}</label>
        <div class="text-xs text-gray-400 mb-2">{{ __('messages.appointments_times_in', ['tz' => $role->timezone ?: config('app.timezone')]) }}</div>
        <div id="weekly-hours" class="space-y-2">
            @foreach ($days as $dayNum => $dayLabel)
                <div class="day-row flex flex-wrap items-start gap-2 py-1" data-day="{{ $dayNum }}">
                    <label class="flex items-center gap-2 w-32 pt-2">
                        <input type="checkbox" class="day-enabled" {{ ! empty($windows[$dayNum]) ? 'checked' : '' }}>
                        <span class="text-sm">{{ $dayLabel }}</span>
                    </label>
                    <div class="ranges flex-1 space-y-1">
                        @foreach (($windows[$dayNum] ?? []) as $range)
                            <div class="range-row flex items-center gap-1">
                                <select class="range-start px-2 py-1 rounded border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526] text-sm">
                                    @foreach ($timeOptions as $t)<option value="{{ $t }}" {{ $range['start'] == $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
                                </select>
                                <span>-</span>
                                <select class="range-end px-2 py-1 rounded border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526] text-sm">
                                    @foreach ($timeOptions as $t)<option value="{{ $t }}" {{ $range['end'] == $t ? 'selected' : '' }}>{{ $t }}</option>@endforeach
                                </select>
                                <button type="button" class="remove-range text-gray-400 px-2">&times;</button>
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
            <label class="block text-sm font-medium mb-1">{{ __('messages.appointments_buffer_before') }}</label>
            <input type="number" name="buffer_before_minutes" min="0" value="{{ old('buffer_before_minutes', $editing->buffer_before_minutes ?? 0) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.appointments_buffer_after') }}</label>
            <input type="number" name="buffer_after_minutes" min="0" value="{{ old('buffer_after_minutes', $editing->buffer_after_minutes ?? 0) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.appointments_min_notice') }}</label>
            <input type="number" name="min_notice_hours" min="0" value="{{ old('min_notice_hours', $editing->min_notice_hours ?? 0) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
        </div>
        <div>
            <label class="block text-sm font-medium mb-1">{{ __('messages.appointments_booking_window') }}</label>
            <input type="number" name="max_advance_days" min="1" value="{{ old('max_advance_days', $editing->max_advance_days ?? 60) }}" class="w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
        </div>
    </div>

    {{-- Location --}}
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('messages.location') }}</label>
        <select name="location_type" id="location_type" class="px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
            <option value="in_person" {{ ($editing->location_type ?? 'in_person') === 'in_person' ? 'selected' : '' }}>{{ __('messages.appointments_in_person') }}</option>
            <option value="online" {{ ($editing->location_type ?? '') === 'online' ? 'selected' : '' }}>{{ __('messages.online') }}</option>
            <option value="phone" {{ ($editing->location_type ?? '') === 'phone' ? 'selected' : '' }}>{{ __('messages.phone') }}</option>
        </select>
        <input type="text" name="location_address" placeholder="{{ __('messages.location') }}" value="{{ old('location_address', $editing->location_address ?? '') }}" class="loc-field loc-in_person mt-2 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
        <input type="url" name="location_url" placeholder="https://" value="{{ old('location_url', $editing->location_url ?? '') }}" class="loc-field loc-online mt-2 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
        <input type="text" name="location_phone" placeholder="{{ __('messages.phone') }}" value="{{ old('location_phone', $editing->location_phone ?? '') }}" class="loc-field loc-phone mt-2 w-full px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
    </div>

    {{-- Price --}}
    <div>
        <label class="block text-sm font-medium mb-1">{{ __('messages.price') }}</label>
        <div class="flex gap-2">
            <input type="number" step="0.01" min="0" name="price" id="price_input" value="{{ old('price', $editing->price ?? 0) }}" class="w-32 px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526]">
            <input type="text" name="currency_code" maxlength="3" placeholder="USD" value="{{ old('currency_code', $editing->currency_code ?? '') }}" class="w-24 px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526] uppercase" style="text-transform:uppercase">
        </div>
        <div id="payment-methods" class="mt-2 flex gap-3 text-sm">
            @foreach (['cash' => __('messages.cash'), 'stripe' => 'Stripe', 'payment_url' => __('messages.payment_url') ?? 'Payment URL'] as $pm => $pmLabel)
                <label class="flex items-center gap-1"><input type="radio" name="payment_method" value="{{ $pm }}" {{ ($editing->payment_method ?? 'cash') === $pm ? 'checked' : '' }}> {{ $pmLabel }}</label>
            @endforeach
        </div>
    </div>

    {{-- Toggles --}}
    <div class="space-y-2">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="requires_approval" value="1" {{ ($editing->requires_approval ?? false) ? 'checked' : '' }}> {{ __('messages.appointments_require_approval') }}</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="ask_phone" value="1" {{ ($editing->ask_phone ?? false) ? 'checked' : '' }}> {{ __('messages.appointments_ask_phone') }}</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="require_phone" value="1" {{ ($editing->require_phone ?? false) ? 'checked' : '' }}> {{ __('messages.appointments_require_phone') }}</label>
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_active" value="1" {{ ($editing->is_active ?? true) ? 'checked' : '' }}> {{ __('messages.appointments_active') }}</label>
    </div>

    <div class="flex gap-2 pt-2">
        <button type="submit" class="px-4 py-3 text-base rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)]">{{ __('messages.save') }}</button>
        <a href="{{ $backUrl }}" class="px-4 py-3 text-base rounded-lg border border-gray-200 dark:border-[#2d2d30]">{{ __('messages.cancel') }}</a>
    </div>
</form>

<template id="range-template">
    <div class="range-row flex items-center gap-1">
        <select class="range-start px-2 py-1 rounded border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526] text-sm">
            @foreach ($timeOptions as $t)<option value="{{ $t }}" {{ $t === '09:00' ? 'selected' : '' }}>{{ $t }}</option>@endforeach
        </select>
        <span>-</span>
        <select class="range-end px-2 py-1 rounded border border-gray-200 dark:border-[#2d2d30] bg-white dark:bg-[#252526] text-sm">
            @foreach ($timeOptions as $t)<option value="{{ $t }}" {{ $t === '17:00' ? 'selected' : '' }}>{{ $t }}</option>@endforeach
        </select>
        <button type="button" class="remove-range text-gray-400 px-2">&times;</button>
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
            form.querySelectorAll('.loc-field').forEach(function (f) { f.style.display = 'none'; });
            form.querySelectorAll('.loc-' + locType.value).forEach(function (f) { f.style.display = 'block'; });
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
