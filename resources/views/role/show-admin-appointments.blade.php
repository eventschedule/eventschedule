@php
    use App\Utils\UrlUtils;

    // Data comes from RoleController::appointmentsTabData(). Only presentation lives here.
    //
    // Appointments are on every plan. The free plan carries one appointment type, so this tab shows
    // an allowance rather than a paywall: the editor always renders, and only the "add another"
    // action is gated. A schedule that lapsed from Pro keeps every type it created; the extras stop
    // being bookable but are never deleted.
    $typeLimit = $role->appointmentTypeLimit();
    $atTypeLimit = ! $role->canCreateAppointmentType();
    $view = request('view', 'types');
    $types = $appointmentTypes;
    $editing = $appointmentEditing;
    $showForm = request()->has('new') || $editing;
    $bookings = $appointmentBookings;

    // The editor owns the whole pane while it is open: it carries its own sticky title/save bar, and
    // "Bookings" is not a place you can be while editing a type.
    $editorOpen = $showForm && ! $isViewer;

    // 15-minute time options for the weekly-hours selects. The stored value is always 24h ('HH:MM');
    // only the label follows the schedule's clock preference.
    $use24 = get_use_24_hour_time($role);
    $timeLabel = fn ($value) => $use24 ? $value : \Carbon\Carbon::createFromFormat('H:i', $value)->format('g:i A');

    $timeOptions = [];
    for ($m = 0; $m < 24 * 60; $m += 15) {
        $value = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
        $timeOptions[] = ['value' => $value, 'label' => $timeLabel($value)];
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

    // Which days a type is open, and the outer bounds of its week, for the at-a-glance column on each
    // row. Answers "is this bookable at all, and when?" without opening the editor - a question the
    // row used to leave entirely to a warning banner.
    $weekSummary = function ($type) use ($days) {
        $stored = $type->weekly_windows ?? [];
        $open = [];
        $starts = [];
        $ends = [];
        foreach ($days as $num => $label) {
            $ranges = $stored[$num] ?? [];
            $ranges = is_array($ranges) ? $ranges : [];
            $open[$num] = count($ranges) > 0;
            foreach ($ranges as $r) {
                if (! empty($r['start'])) { $starts[] = $r['start']; }
                if (! empty($r['end'])) { $ends[] = $r['end']; }
            }
        }
        sort($starts);
        sort($ends);

        return [
            'open' => $open,
            'start' => $starts[0] ?? null,
            'end' => $ends ? $ends[count($ends) - 1] : null,
        ];
    };

    $locationLabel = fn ($type) => match ($type->location_type) {
        'online' => __('messages.online'),
        'phone' => __('messages.phone'),
        default => __('messages.appointments_in_person'),
    };

    $chipClass = 'inline-flex items-center rounded-full bg-gray-100 dark:bg-gray-700 px-2 py-0.5 text-xs text-gray-600 dark:text-gray-300';
    $menuItemClass = 'flex w-full items-center gap-2 px-4 py-2.5 text-start text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors';
    $warnClass = 'flex items-start gap-2 rounded-lg border border-amber-200 dark:border-amber-700 bg-amber-50 dark:bg-amber-900/20 p-2 text-xs text-amber-800 dark:text-amber-200';
    $secondaryBtnClass = 'ap-secondary-btn inline-flex items-center justify-center rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]';
@endphp

<div class="space-y-4">

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

        {{-- One header row instead of three stacked ones. The sub-view switcher is the same segmented
             control as the admin federation filter (grouped container, inset shadow on the active
             item, red count badge for things awaiting a decision); the allowance meter now sits
             beside the button it gates rather than below the list. --}}
        @if (! $editorOpen)
            <div class="flex flex-wrap items-center gap-3">
                <div class="inline-flex items-center gap-1 rounded-xl bg-gray-100 dark:bg-gray-800 p-1">
                    @foreach ([['types', __('messages.appointment_types'), 0], ['bookings', __('messages.bookings'), $pendingBookingCount]] as [$key, $label, $badge])
                        <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments'] + ($key === 'bookings' ? ['view' => 'bookings'] : [])) }}"
                           @if ($view === $key) aria-current="page" style="box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);" @endif
                           class="rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 {{ $view === $key ? 'bg-white dark:bg-gray-900 text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
                            {{ $label }}
                            @if ($badge > 0)
                                <span class="ms-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $badge }}</span>
                            @endif
                        </a>
                    @endforeach
                </div>

                @if ($view !== 'bookings' && $types->isNotEmpty() && ! $isViewer)
                    <div class="ms-auto flex items-center gap-4">
                        @if ($typeLimit)
                            {{-- Quiet and factual, and only where there is an allowance to report. No
                                 noteText at this width: it wraps badly, and the same sentence already
                                 appears in the empty state and in the upgrade modal. --}}
                            <x-usage-meter class="hidden sm:block w-44"
                                variant="inline"
                                :label="__('messages.appointment_type_usage')"
                                :used="$role->appointmentTypeCount()"
                                :limit="$typeLimit"
                                :usedText="__('messages.appointment_types_used', ['used' => $role->appointmentTypeCount(), 'limit' => $typeLimit])" />
                        @endif
                        @if ($atTypeLimit)
                            {{-- Never hidden and never allowed to navigate to a form the server would
                                 refuse: it opens the allowance modal instead, so the click explains
                                 itself rather than reading as broken. --}}
                            <button type="button" data-modal-open="upgrade-appointment-types"
                                class="inline-flex items-center gap-2 px-4 py-3 text-base font-semibold rounded-lg bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-600 text-gray-900 dark:text-gray-100 shadow-sm transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                                {{ __('messages.appointments_new_type') }}
                                <x-lock-badge tier="pro" />
                            </button>
                        @else
                            <x-brand-link href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?new=1">{{ __('messages.appointments_new_type') }}</x-brand-link>
                        @endif
                    </div>
                @endif
            </div>
        @endif

        @if ($view === 'bookings')
            @include('role.partials.appointment-bookings', ['bookings' => $bookings])
        @elseif ($editorOpen)
            @include('role.partials.appointment-editor', ['editing' => $editing, 'windows' => $windows, 'overrides' => $overrides, 'timeOptions' => $timeOptions, 'days' => $days])
        @else
            {{-- Share toolbar. Sharing the link is the whole point of the page, so it stays visible -
                 but as one row rather than a titled panel of its own. --}}
            @if ($types->where('is_active', true)->count() && $role->hasBookableAppointments())
                @php $bookUrl = route('appointments.book', ['subdomain' => $role->subdomain]); @endphp
                <div class="ap-card rounded-xl p-3 flex flex-wrap items-center gap-2">
                    <span class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.appointments_share_link') }}</span>
                    <label for="appt-book-url" class="sr-only">{{ __('messages.appointments_share_link') }}</label>
                    <input type="text" id="appt-book-url" readonly value="{{ $bookUrl }}"
                           class="flex-1 min-w-0 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                    <button type="button" data-appt-copy="{{ $bookUrl }}" title="{{ __('messages.copy_link') }}"
                            class="{{ $secondaryBtnClass }} gap-1.5 px-3 py-2 text-sm">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                        <span>{{ __('messages.copy_link') }}</span>
                    </button>
                    <a href="{{ $bookUrl }}" target="_blank" rel="noopener"
                       class="{{ $secondaryBtnClass }} px-3 py-2 text-sm">{{ __('messages.preview') }}</a>
                </div>
            @endif

            {{-- Types list --}}
            @if ($types->isEmpty())
                <div class="ap-card rounded-xl p-8 text-center">
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.appointments_empty_title') }}</h3>
                    <p class="mx-auto max-w-md text-sm text-gray-600 dark:text-gray-400 mt-1 mb-4">{{ __('messages.appointments_empty_body') }}</p>
                    @if ($typeLimit)
                        {{-- Encourage, do not gate: with no types yet the allowance is unspent. --}}
                        <p class="mx-auto max-w-md text-sm text-gray-500 dark:text-gray-400 -mt-2 mb-4">{{ __('messages.appointment_type_included_note') }}</p>
                    @endif
                    @if (! $isViewer)
                        <x-brand-link href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?new=1">{{ __('messages.appointments_new_type') }}</x-brand-link>
                    @endif
                </div>
            @else
                @if ($typeLimit)
                    @php $bookableType = $role->bookableAppointmentTypes()->first(); @endphp
                    {{-- Counted with appointmentTypeCount(), the same ACTIVE-only predicate the
                         allowance itself uses - not $types, which is every non-deleted type and so
                         includes ones the owner paused. With one active type and one paused, this
                         used to announce that the plan was clamping something and that "the others
                         become bookable again when you upgrade", when nothing was clamped and only
                         un-pausing would bring the other one back. --}}
                    @if ($role->appointmentTypeCount() > $typeLimit && $bookableType)
                        {{-- A lapsed Pro schedule keeps its extra types; say which one is live so the
                             owner is never guessing why a booking link 404s. --}}
                        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3 flex items-start gap-2">
                            <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" /></svg>
                            <div class="text-sm text-amber-800 dark:text-amber-200">
                                <x-user-text>{{ __('messages.appointment_types_clamped', ['limit' => $typeLimit, 'name' => $bookableType->name]) }}</x-user-text>
                            </div>
                        </div>
                    @endif
                @endif

                <div class="space-y-3">
                    @foreach ($types as $type)
                        @php
                            $typeUrl = route('appointments.book_type', ['subdomain' => $role->subdomain, 'typeSlug' => $type->slug]);
                            $bookingCount = $appointmentBookingCounts[$type->id] ?? 0;
                            // isBookable() checks active + payable but never the hours, so a type with an
                            // empty week still lights up the guest "Book a Time" button and then dead-ends
                            // on "No available times".
                            $week = $weekSummary($type);
                            $hasHours = collect($week['open'])->contains(true);
                            $openDayNames = collect($days)->filter(fn ($l, $n) => $week['open'][$n])->values()->implode(', ');
                        @endphp
                        {{-- Three zones once there is room: identity, availability at a glance, actions;
                             below lg they stack. No opacity dimming for an inactive type - it pushed the
                             muted text under the contrast floor and faded the very pill that explains
                             the state. A muted name plus that pill carries it instead. --}}
                        {{-- items-start, not items-center: a row carrying two warning panels is much
                             taller than the other two zones, and centring left the name floating in
                             the middle of an otherwise empty column. --}}
                        <div class="ap-card rounded-xl p-4 flex flex-col gap-3
                                    lg:grid lg:grid-cols-[minmax(0,1.6fr)_minmax(0,1fr)_auto] lg:items-start lg:gap-4">

                            {{-- Identity --}}
                            <div class="min-w-0">
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-semibold {{ $type->is_active ? 'text-gray-900 dark:text-gray-100' : 'text-gray-500 dark:text-gray-400' }}">{{ $type->name }}</span>
                                    @if (! $type->is_active)
                                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300">{{ __('messages.inactive') }}</span>
                                    @endif
                                </div>
                                <div class="mt-1.5 flex flex-wrap items-center gap-1.5">
                                    <span class="{{ $chipClass }}">{{ $type->duration_minutes }} {{ __('messages.minutes') }}</span>
                                    <span class="{{ $chipClass }}">{{ $type->isFree() ? __('messages.free') : \App\Utils\MoneyUtils::format((float) $type->price, $type->currency_code) }}</span>
                                    @if ($type->requires_approval)
                                        <span class="{{ $chipClass }}">{{ __('messages.appointments_requires_confirmation') }}</span>
                                    @endif
                                    <span class="{{ $chipClass }}">{{ trans_choice('messages.appointments_booking_count', $bookingCount, ['count' => $bookingCount]) }}</span>
                                </div>

                                {{-- Warnings live with the identity, not in the at-a-glance column: that
                                     column is hidden on a phone, and an always-rendered wrapper holding
                                     only hidden children is still a flex child, so it was spending the
                                     parent's gap twice on every row that had nothing to say.
                                     Independent checks: a paid type with no payment method can ALSO
                                     have no hours, and an @elseif hid whichever came second. --}}
                                @if (! $hasHours)
                                    <div class="{{ $warnClass }} mt-2">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                                        <span>{{ __('messages.appointments_no_hours_warning') }}</span>
                                    </div>
                                @endif
                                @if (! $type->isFree() && ! $type->paymentMethodAvailable())
                                    <div class="{{ $warnClass }} mt-2">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M5 19h14a2 2 0 001.84-2.75L13.74 4a2 2 0 00-3.5 0L3.16 16.25A2 2 0 005 19z" /></svg>
                                        <span>{{ __('messages.appointments_payment_not_set') }}</span>
                                    </div>
                                @endif
                            </div>

                            {{-- At a glance. display:none rather than empty on a phone, so it costs no
                                 gap there: the strip plus its hours line adds ~40px to every row, which
                                 measured LONGER than the list it replaced. It earns the space from sm
                                 up, where it is filling a column that was otherwise empty. --}}
                            <div class="hidden sm:block min-w-0 space-y-2">
                                {{-- Open and closed differ by fill and weight, not by colour alone, and
                                     both pairs clear 4.5:1 - a tinted letter on a tinted chip does not
                                     at this size. --brand-blue-a20 is the pre-mixed alpha the app keeps
                                     for exactly this; an opacity modifier on an arbitrary var() cannot
                                     be resolved by Tailwind. --}}
                                <div class="flex items-center gap-1" aria-hidden="true">
                                    @foreach ($days as $dayNum => $dayLabel)
                                        <span title="{{ $dayLabel }}"
                                              class="inline-flex h-6 w-6 items-center justify-center rounded-md text-[11px] {{ $week['open'][$dayNum] ? 'bg-[var(--brand-blue-a20)] font-semibold text-gray-900 dark:text-white' : 'border border-gray-200 dark:border-gray-700 text-gray-600 dark:text-gray-400' }}">{{ mb_substr($dayLabel, 0, 1) }}</span>
                                    @endforeach
                                </div>
                                {{-- The strip is colour-only, so the open days are also spelled out for
                                     anything that is not looking at it. --}}
                                <span class="sr-only">{{ $hasHours ? $openDayNames : __('messages.appointments_no_hours_warning') }}</span>
                                <div class="text-xs text-gray-500 dark:text-gray-400">
                                    @if ($week['start'] && $week['end'])
                                        {{ $timeLabel($week['start']) }} - {{ $timeLabel($week['end']) }}
                                        <span aria-hidden="true">&middot;</span>
                                    @endif
                                    {{ $locationLabel($type) }}
                                </div>
                            </div>

                            {{-- Actions. Rendered even for a viewer so the three-column template cannot
                                 collapse to two children and stretch the middle one - but hidden below
                                 lg in that case, where an empty cluster would only cost a gap. --}}
                            <div class="{{ $isViewer ? 'hidden lg:flex' : 'flex' }} flex-wrap items-center justify-end gap-2">
                                @if (! $isViewer)
                                    <form method="POST" action="{{ route('appointments.toggle', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}">
                                        @csrf
                                        {{-- A standalone boolean, so a switch rather than a button that
                                             names the opposite state. data-auto-submit is app-wide. The
                                             id is per-type: several of these render on one page. --}}
                                        <x-toggle name="is_active" :label="__('messages.active')" :checked="$type->is_active" :id="'active-'.$type->hashedId()" data-auto-submit />
                                    </form>
                                    <x-secondary-link href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'edit' => $type->hashedId()]) }}">{{ __('messages.edit') }}</x-secondary-link>

                                    {{-- Five controls in a row wrapped onto three lines on a phone; the
                                         rest now live behind a menu. <details> rather than x-dropdown:
                                         that component is Alpine, which this codebase is migrating away
                                         from, and appointment-add-to-calendar set the precedent. --}}
                                    <details class="appt-menu relative">
                                        {{-- px-3 py-3 with a 24px icon is 50px tall, the same as the
                                             px-4 py-3 text-base Edit link beside it. --}}
                                        <summary class="{{ $secondaryBtnClass }} list-none cursor-pointer px-3 py-3"
                                                 aria-label="{{ __('messages.actions') }}" title="{{ __('messages.actions') }}">
                                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 20 20" aria-hidden="true"><path d="M6 10a2 2 0 11-4 0 2 2 0 014 0zM12 10a2 2 0 11-4 0 2 2 0 014 0zM18 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                                        </summary>
                                        <div class="absolute z-20 mt-2 end-0 w-52 overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700 bg-white dark:bg-gray-800 shadow-lg">
                                            @if ($type->isBookable())
                                                <a href="{{ $typeUrl }}" target="_blank" rel="noopener" class="{{ $menuItemClass }}">
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                                                    {{ __('messages.preview') }}
                                                </a>
                                                <button type="button" data-appt-copy="{{ $typeUrl }}" class="{{ $menuItemClass }}">
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                                    <span>{{ __('messages.copy_link') }}</span>
                                                </button>
                                            @endif
                                            <form method="POST" action="{{ route('appointments.duplicate', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}">
                                                @csrf
                                                <button type="submit" class="{{ $menuItemClass }}">
                                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h6a2 2 0 002-2v-2" /></svg>
                                                    {{ __('messages.clone') }}
                                                </button>
                                            </form>
                                            <form method="POST" action="{{ route('appointments.destroy', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}"
                                                  data-confirm="{{ __('messages.appointments_delete_warning') }}">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="{{ $menuItemClass }} text-red-600 dark:text-red-400">
                                                    <svg class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" /></svg>
                                                    {{ __('messages.delete') }}
                                                </button>
                                            </form>
                                        </div>
                                    </details>
                                @endif
                            </div>
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
                                // Close after the feedback has been seen, not before it.
                                var menu = btn.closest('details.appt-menu');
                                if (menu) menu.open = false;
                            }, 1500);
                        }).catch(function () {});
                    });

                    // <details> closes on neither Escape nor an outside click by itself, which for a
                    // row-action menu means a stray open panel follows you around the page.
                    function closeApptMenus(except) {
                        document.querySelectorAll('details.appt-menu[open]').forEach(function (d) {
                            if (d !== except) d.open = false;
                        });
                    }
                    document.addEventListener('click', function (e) {
                        closeApptMenus(e.target.closest('details.appt-menu'));
                    });
                    document.addEventListener('keydown', function (e) {
                        if (e.key !== 'Escape') return;
                        var open = document.querySelector('details.appt-menu[open]');
                        if (!open) return;
                        closeApptMenus();
                        var summary = open.querySelector('summary');
                        if (summary) summary.focus();
                    });
                </script>
            @endif
        @endif

        {{-- The free-plan allowance modal, opened by the gated "add type" buttons above. --}}
        @if ($atTypeLimit && ! $isViewer)
            <x-upgrade-modal name="upgrade-appointment-types" tier="pro" :subdomain="$role->subdomain"
                :learnMoreUrl="marketing_url('/features/appointments')"
                :title="__('messages.appointment_type_limit_title')"
                :bullets="[
                    __('messages.appointment_type_pro_bullet_unlimited'),
                    __('messages.appointments_date_overrides'),
                    __('messages.appointments_scheduling_rules'),
                    __('messages.appointments_share_link'),
                ]">
                {{ __('messages.appointment_type_limit_body') }}
            </x-upgrade-modal>
        @endif
</div>
