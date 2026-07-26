@php
    use App\Utils\UrlUtils;

    $isGated = config('app.hosted') && ! $role->isPro();
    $view = request('view', 'types');
    $editHash = request('edit');
    $editing = $editHash ? $role->appointmentTypes()->where('is_deleted', false)->find(UrlUtils::decodeId($editHash)) : null;
    $showForm = request()->has('new') || $editing;
    $types = $role->appointmentTypes()->where('is_deleted', false)->orderBy('name')->get();

    // 15-minute time options for the weekly-hours selects.
    $timeOptions = [];
    for ($m = 0; $m < 24 * 60; $m += 15) {
        $timeOptions[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
    }

    $days = [
        '1' => __('messages.monday') ?? 'Monday', '2' => __('messages.tuesday') ?? 'Tuesday',
        '3' => __('messages.wednesday') ?? 'Wednesday', '4' => __('messages.thursday') ?? 'Thursday',
        '5' => __('messages.friday') ?? 'Friday', '6' => __('messages.saturday') ?? 'Saturday',
        '0' => __('messages.sunday') ?? 'Sunday',
    ];

    $defaultWindows = ['0' => [], '1' => [['start' => '09:00', 'end' => '17:00']], '2' => [['start' => '09:00', 'end' => '17:00']],
        '3' => [['start' => '09:00', 'end' => '17:00']], '4' => [['start' => '09:00', 'end' => '17:00']],
        '5' => [['start' => '09:00', 'end' => '17:00']], '6' => []];
    $windows = $editing ? ($editing->weekly_windows ?? $defaultWindows) : $defaultWindows;

    // Whether the booking is still awaiting approval: safe when the creator pivot row is missing
    // (detached/drifted data must not crash the tab). Shared with the bookings partial.
    $bookingIsPending = function ($event) {
        $pivot = $event->roles->firstWhere('id', $event->creator_role_id)?->pivot;

        return $pivot && is_null($pivot->is_accepted);
    };

    $bookings = collect();
    if ($view === 'bookings' && ! $isGated) {
        $filter = request('filter', 'upcoming');
        $terminal = ['cancelled', 'refunded', 'expired'];
        // starts_at is stored as a UTC 'Y-m-d H:i:s' string, so a lexicographic compare against a
        // UTC-formatted "now" is a correct past/upcoming split.
        $nowUtc = now('UTC')->format('Y-m-d H:i:s');

        // Every predicate runs in SQL. Filtering a capped result set in PHP instead would silently
        // hide older bookings once a schedule passes the cap - Past and Cancelled grow forever.
        $bookingQuery = \App\Models\Sale::query()
            ->select('sales.*')
            ->join('events', 'events.id', '=', 'sales.event_id')
            ->where('sales.subdomain', $role->subdomain)
            ->where('sales.is_deleted', false)
            ->whereNotNull('events.appointment_type_id')
            ->with(['event.appointmentType', 'event.roles']);

        $excludeCancelled = fn ($q) => $q
            ->whereNotIn('sales.status', $terminal)
            ->where('events.is_cancelled', false);

        if ($filter === 'cancelled') {
            $bookingQuery->where(fn ($q) => $q
                ->whereIn('sales.status', $terminal)
                ->orWhere('events.is_cancelled', true));
        } elseif ($filter === 'pending') {
            $excludeCancelled($bookingQuery);
            // Awaiting approval: the creator schedule's pivot row is still NULL.
            $bookingQuery->whereExists(fn ($q) => $q
                ->selectRaw('1')
                ->from('event_role')
                ->whereColumn('event_role.event_id', 'sales.event_id')
                ->whereColumn('event_role.role_id', 'events.creator_role_id')
                ->whereNull('event_role.is_accepted'));
        } elseif ($filter === 'past') {
            $excludeCancelled($bookingQuery);
            $bookingQuery->where('events.starts_at', '<', $nowUtc);
        } else {
            $excludeCancelled($bookingQuery);
            $bookingQuery->where('events.starts_at', '>=', $nowUtc);
        }

        // Soonest first for upcoming (what the owner acts on next), newest first otherwise -
        // the same ordering the previous in-PHP sort produced.
        $filter === 'upcoming'
            ? $bookingQuery->orderBy('events.starts_at')
            : $bookingQuery->orderByDesc('sales.id');

        $bookings = $bookingQuery->paginate(50)->withQueryString();
    }
@endphp

<div class="space-y-4">
    @if ($isGated)
        <x-upgrade-prompt tier="pro" :subdomain="$role->subdomain" :learnMoreUrl="marketing_url('/features/appointments')">
            {{ __('messages.appointments_pro_description') }}
        </x-upgrade-prompt>
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

        {{-- Sub-view pills --}}
        <div class="flex gap-2">
            <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $view === 'types' ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">{{ __('messages.appointment_types') }}</a>
            <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?view=bookings"
               class="px-4 py-2 rounded-lg text-sm font-medium transition-all duration-200 {{ $view === 'bookings' ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300' }}">{{ __('messages.bookings') }}</a>
        </div>

        @if ($view === 'bookings')
            @include('role.partials.appointment-bookings', ['bookings' => $bookings])
        @elseif ($showForm && ! $isViewer)
            @include('role.partials.appointment-editor', ['editing' => $editing, 'windows' => $windows, 'timeOptions' => $timeOptions, 'days' => $days])
        @else
            {{-- Share panel --}}
            @if ($types->where('is_active', true)->count() && $role->hasBookableAppointments())
                <div class="ap-card rounded-xl p-4">
                    <div class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-2">{{ __('messages.appointments_share_link') }}</div>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" readonly value="{{ route('appointments.book', ['subdomain' => $role->subdomain]) }}"
                               class="flex-1 min-w-0 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-700 dark:text-gray-300">
                        <a href="{{ route('appointments.book', ['subdomain' => $role->subdomain]) }}" target="_blank" rel="noopener"
                           class="ap-secondary-btn px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">{{ __('messages.view') }}</a>
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
                        <div class="ap-card rounded-xl p-4 flex flex-wrap items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $type->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $type->duration_minutes }} {{ __('messages.minutes') }}
                                    &middot; {{ $type->isFree() ? __('messages.free') : strtoupper($type->currency_code).' '.number_format((float) $type->price, 2) }}
                                    @if ($type->requires_approval) &middot; {{ __('messages.appointments_requires_confirmation') }} @endif
                                </div>
                                @if (! $type->isFree() && ! $type->paymentMethodAvailable())
                                    <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('messages.appointments_payment_not_set') }}</div>
                                @elseif (! $type->is_active)
                                    <div class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.inactive') ?? 'Inactive' }}</div>
                                @endif
                            </div>
                            @if (! $isViewer)
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('appointments.toggle', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}">
                                        @csrf
                                        <button type="submit" class="ap-secondary-btn px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">{{ $type->is_active ? __('messages.appointments_deactivate') : __('messages.appointments_activate') }}</button>
                                    </form>
                                    <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?edit={{ $type->hashedId() }}"
                                       class="ap-secondary-btn px-3 py-2 text-sm rounded-lg border border-gray-300 dark:border-gray-600 text-gray-700 dark:text-gray-300 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]">{{ __('messages.edit') }}</a>
                                    <form method="POST" action="{{ route('appointments.destroy', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}" class="form-confirm" data-confirm="{{ __('messages.are_you_sure') }}">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-3 py-2 text-sm rounded-lg text-red-600 dark:text-red-400">{{ __('messages.delete') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @endif
        @endif
    @endif
</div>
