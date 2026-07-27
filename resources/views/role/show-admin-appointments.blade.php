@php
    use App\Utils\UrlUtils;

    // Data comes from RoleController::appointmentsTabData(). Only presentation lives here.
    $isGated = config('app.hosted') && ! $role->isPro();
    $view = request('view', 'types');
    $types = $appointmentTypes;
    $editing = $appointmentEditing;
    $showForm = request()->has('new') || $editing;
    $bookings = $appointmentBookings;

    // 15-minute time options for the weekly-hours selects. The stored value is always 24h ('HH:MM');
    // only the label follows the schedule's clock preference.
    $timeOptions = [];
    for ($m = 0; $m < 24 * 60; $m += 15) {
        $value = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        $timeOptions[] = [
            'value' => $value,
            'label' => get_use_24_hour_time($role)
                ? $value
                : \Carbon\Carbon::createFromFormat('H:i', $value)->format('g:i A'),
        ];
    }

    $days = [
        '1' => __('messages.monday'), '2' => __('messages.tuesday'),
        '3' => __('messages.wednesday'), '4' => __('messages.thursday'),
        '5' => __('messages.friday'), '6' => __('messages.saturday'),
        '0' => __('messages.sunday'),
    ];

    $defaultWindows = ['0' => [], '1' => [['start' => '09:00', 'end' => '17:00']], '2' => [['start' => '09:00', 'end' => '17:00']],
        '3' => [['start' => '09:00', 'end' => '17:00']], '4' => [['start' => '09:00', 'end' => '17:00']],
        '5' => [['start' => '09:00', 'end' => '17:00']], '6' => []];
    // A failed submit must not throw away the owner's work: the posted JSON wins over the stored
    // value, so tripping a validation error no longer resets the whole week to Mon-Fri 9-5.
    $postedWindows = old('weekly_windows') ? json_decode(old('weekly_windows'), true) : null;
    $windows = is_array($postedWindows)
        ? $postedWindows
        : ($editing ? ($editing->weekly_windows ?? $defaultWindows) : $defaultWindows);

    // Per-date overrides, same old()-first rule. An empty range list means "closed that day".
    // Past dates are rendered too, so stale entries can still be removed.
    $postedOverrides = old('date_overrides') ? json_decode(old('date_overrides'), true) : null;
    $overrides = is_array($postedOverrides)
        ? $postedOverrides
        : ($editing ? ($editing->date_overrides ?? []) : []);
    ksort($overrides);

@endphp

<div class="space-y-4">
    @if ($isGated)
        <x-upgrade-prompt tier="pro" :subdomain="$role->subdomain" :learnMoreUrl="marketing_url('/features/appointments')">
            {{ __('messages.appointments_pro_description') }}
        </x-upgrade-prompt>
        {{-- The gated state was a bare sentence; a free user could not tell what they would get.
             Rendered outside the component because its slot sits inside a <p>. --}}
        <div class="ap-card rounded-xl p-6">
            <ul class="mx-auto max-w-md space-y-2 text-sm text-gray-600 dark:text-gray-400">
                @foreach ([
                    __('messages.appointments_weekly_hours'),
                    __('messages.appointments_date_overrides'),
                    __('messages.appointments_scheduling_rules'),
                    __('messages.appointments_share_link'),
                ] as $feature)
                    <li class="flex items-start gap-2">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" /></svg>
                        <span>{{ $feature }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    @else

        {{-- Warnings --}}
        @if (! $role->timezone)
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                <div class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.appointments_set_timezone_warning', ['tz' => config('app.timezone')]) }}
                    <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}#section-details" class="underline">{{ __('messages.edit') }}</a>
                </div>
            </div>
        @endif
        @if (config('app.hosted') && ! $role->hasEmailSettings())
            <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                <div class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.appointments_no_email_warning') }}</div>
            </div>
        @endif

        {{-- Sub-view switcher. Same segmented control as the admin federation filter: grouped
             container, inset shadow on the active item, red count badge for things awaiting a
             decision. --}}
        <div class="inline-flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-[#252526] p-1">
            @foreach ([['types', __('messages.appointment_types'), 0], ['bookings', __('messages.bookings'), $pendingBookingCount]] as [$key, $label, $badge])
                <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments'] + ($key === 'bookings' ? ['view' => 'bookings'] : [])) }}"
                   @if ($view === $key) aria-current="page" style="box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);" @endif
                   class="rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 {{ $view === $key ? 'bg-white dark:bg-[#1e1e1e] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                    {{ $label }}
                    @if ($badge > 0)
                        <span class="ms-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $badge }}</span>
                    @endif
                </a>
            @endforeach
        </div>

        @if ($view === 'bookings')
            @include('role.partials.appointment-bookings', ['bookings' => $bookings])
        @elseif ($showForm && ! $isViewer)
            @include('role.partials.appointment-editor', ['editing' => $editing, 'windows' => $windows, 'overrides' => $overrides, 'timeOptions' => $timeOptions, 'days' => $days])
        @else
            {{-- Share panel --}}
            @if ($types->where('is_active', true)->count() && $role->hasBookableAppointments())
                @php $bookUrl = route('appointments.book', ['subdomain' => $role->subdomain]); @endphp
                <div class="ap-card rounded-xl p-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.appointments_share_link') }}</div>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" readonly value="{{ $bookUrl }}"
                               class="flex-1 min-w-0 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                        {{-- Sharing the link is the whole point of the page; it had no copy action. --}}
                        <button type="button" data-appt-copy="{{ $bookUrl }}" title="{{ __('messages.copy_link') }}"
                                class="ap-secondary-btn inline-flex items-center gap-1.5 px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                            <span>{{ __('messages.copy_link') }}</span>
                        </button>
                        <a href="{{ $bookUrl }}" target="_blank" rel="noopener"
                           class="ap-secondary-btn px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">{{ __('messages.preview') }}</a>
                    </div>
                </div>
            @endif

            {{-- Types list --}}
            @if ($types->isEmpty())
                <div class="ap-card rounded-xl p-8 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.appointments_empty_title') }}</h3>
                    <p class="mx-auto max-w-md text-sm text-gray-600 dark:text-gray-400 mt-1 mb-4">{{ __('messages.appointments_empty_body') }}</p>
                    @if (! $isViewer)
                        <x-brand-link href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?new=1">{{ __('messages.appointments_new_type') }}</x-brand-link>
                    @endif
                </div>
            @else
                @if (! $isViewer)
                    <div class="flex justify-end">
                        <x-brand-link href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?new=1">{{ __('messages.appointments_new_type') }}</x-brand-link>
                    </div>
                @endif
                <div class="space-y-3">
                    @foreach ($types as $type)
                        @php
                            $typeUrl = route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]);
                            $bookingCount = $appointmentBookingCounts[$type->id] ?? 0;
                            // isBookable() checks active + payable but never the hours, so a type with an
                            // empty week still lights up the guest "Book a Time" button and then dead-ends
                            // on "No available times".
                            $hasHours = collect($type->weekly_windows ?? [])->flatten(1)->isNotEmpty();
                        @endphp
                        <div class="ap-card rounded-xl p-4 {{ $type->is_active ? '' : 'opacity-60' }}">
                            <div class="flex flex-wrap items-center gap-3">
                                <div class="flex-1 min-w-0">
                                    <div class="flex flex-wrap items-center gap-2">
                                        <span class="font-semibold text-gray-900 dark:text-gray-100">{{ $type->name }}</span>
                                        @if (! $type->is_active)
                                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ __('messages.inactive') }}</span>
                                        @endif
                                    </div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">
                                        {{ $type->duration_minutes }} {{ __('messages.minutes') }}
                                        &middot; {{ $type->isFree() ? __('messages.free') : \App\Utils\MoneyUtils::format((float) $type->price, $type->currency_code) }}
                                        @if ($type->requires_approval) &middot; {{ __('messages.appointments_requires_confirmation') }} @endif
                                        &middot; {{ trans_choice('messages.appointments_booking_count', $bookingCount, ['count' => $bookingCount]) }}
                                    </div>
                                    {{-- Independent checks: a paid type with no payment method can ALSO have
                                         no hours, and the old @elseif hid whichever came second. --}}
                                    @if (! $type->isFree() && ! $type->paymentMethodAvailable())
                                        <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('messages.appointments_payment_not_set') }}</div>
                                    @endif
                                </div>
                                @if (! $isViewer)
                                    <div class="flex flex-wrap items-center gap-2">
                                        <form method="POST" action="{{ route('appointments.toggle', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}">
                                            @csrf
                                            {{-- A standalone boolean, so a switch rather than a button that
                                                 names the opposite state. data-auto-submit is app-wide. --}}
                                            <x-toggle name="is_active" :label="__('messages.active')" :checked="$type->is_active" data-auto-submit />
                                        </form>
                                        @if ($type->isBookable())
                                            <button type="button" data-appt-copy="{{ $typeUrl }}" title="{{ __('messages.copy_link') }}"
                                                    class="ap-secondary-btn inline-flex items-center px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">
                                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                <span class="sr-only">{{ __('messages.copy_link') }}</span>
                                            </button>
                                        @endif
                                        <x-secondary-link href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'edit' => $type->hashedId()]) }}">{{ __('messages.edit') }}</x-secondary-link>
                                        <form method="POST" action="{{ route('appointments.duplicate', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}">
                                            @csrf
                                            <button type="submit" class="ap-secondary-btn px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">{{ __('messages.clone') }}</button>
                                        </form>
                                        <form method="POST" action="{{ route('appointments.destroy', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}"
                                              data-confirm="{{ __('messages.appointments_delete_warning') }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="px-3 py-2 text-sm rounded-lg font-medium text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">{{ __('messages.delete') }}</button>
                                        </form>
                                    </div>
                                @endif
                            </div>

                            @if (! $hasHours)
                                <div class="mt-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                                    <div class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.appointments_no_hours_warning') }}</div>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>

                {{-- Every page in this app rolls its own clipboard handler; there is no global one. --}}
                <script {!! nonce_attr() !!}>
                    document.addEventListener('click', function (e) {
                        var btn = e.target.closest('[data-appt-copy]');
                        if (!btn || !navigator.clipboard) return;
                        navigator.clipboard.writeText(btn.dataset.apptCopy).then(function () {
                            var label = btn.querySelector('span');
                            var icon = btn.querySelector('svg');
                            var originalLabel = label ? label.textContent : null;
                            if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />';
                            if (label && !label.classList.contains('sr-only')) label.textContent = @json(__('messages.copied'));
                            setTimeout(function () {
                                if (icon) icon.innerHTML = '<path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />';
                                if (label && originalLabel !== null) label.textContent = originalLabel;
                            }, 1500);
                        }).catch(function () {});
                    });
                </script>
            @endif
        @endif
    @endif
</div>
