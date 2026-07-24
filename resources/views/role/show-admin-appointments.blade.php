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

    $bookings = collect();
    if ($view === 'bookings') {
        $filter = request('filter', 'upcoming');
        $q = \App\Models\Sale::where('subdomain', $role->subdomain)
            ->whereHas('event', fn ($e) => $e->whereNotNull('appointment_type_id'))
            ->with(['event.appointmentType'])
            ->orderByDesc('id');
        $bookings = $q->get()->filter(function ($s) use ($filter) {
            $e = $s->event;
            if (! $e) return false;
            $cancelled = $e->is_cancelled || in_array($s->status, ['cancelled', 'refunded', 'expired']);
            $past = \Carbon\Carbon::createFromFormat('Y-m-d H:i:s', $e->starts_at, 'UTC')->isPast();
            $pending = is_null(optional($e->roles->firstWhere('id', $e->creator_role_id))->pivot->is_accepted ?? optional($e->roles()->where('roles.id', $e->creator_role_id)->first())->pivot->is_accepted);
            return match ($filter) {
                'pending' => $pending && ! $cancelled,
                'past' => $past && ! $cancelled,
                'cancelled' => $cancelled,
                default => ! $past && ! $cancelled,
            };
        })->values();
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
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $view === 'types' ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-gray-300' }}">{{ __('messages.appointment_types') }}</a>
            <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?view=bookings"
               class="px-4 py-2 rounded-lg text-sm font-medium {{ $view === 'bookings' ? 'bg-[var(--brand-button-bg)] text-white' : 'bg-gray-100 dark:bg-[#2d2d30] text-gray-600 dark:text-gray-300' }}">{{ __('messages.bookings') }}</a>
        </div>

        @if ($view === 'bookings')
            @include('role.partials.appointment-bookings', ['bookings' => $bookings])
        @elseif ($showForm && ! $isViewer)
            @include('role.partials.appointment-editor', ['editing' => $editing, 'windows' => $windows, 'timeOptions' => $timeOptions, 'days' => $days])
        @else
            {{-- Share panel --}}
            @if ($types->where('is_active', true)->count() && $role->hasBookableAppointments())
                <div class="ap-card rounded-xl p-4">
                    <div class="text-sm font-medium mb-2">{{ __('messages.appointments_share_link') }}</div>
                    <div class="flex flex-wrap items-center gap-2">
                        <input type="text" readonly value="{{ route('appointments.book', ['subdomain' => $role->subdomain]) }}"
                               class="flex-1 min-w-0 text-sm px-3 py-2 rounded-lg border border-gray-200 dark:border-[#2d2d30] bg-gray-50 dark:bg-[#252526]">
                        <a href="{{ route('appointments.book', ['subdomain' => $role->subdomain]) }}" target="_blank" rel="noopener"
                           class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-[#2d2d30]">{{ __('messages.view') }}</a>
                    </div>
                </div>
            @endif

            {{-- Types list --}}
            @if ($types->isEmpty())
                <div class="ap-card rounded-xl p-8 text-center">
                    <h3 class="text-lg font-semibold">{{ __('messages.appointments_empty_title') }}</h3>
                    <p class="text-gray-500 dark:text-gray-400 mt-1 mb-4">{{ __('messages.appointments_empty_body') }}</p>
                    @if (! $isViewer)
                        <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?new=1"
                           class="inline-block px-4 py-3 text-base rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)]">{{ __('messages.appointments_new_type') }}</a>
                    @endif
                </div>
            @else
                @if (! $isViewer)
                    <div class="flex justify-end">
                        <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?new=1"
                           class="px-4 py-3 text-base rounded-lg text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)]">{{ __('messages.appointments_new_type') }}</a>
                    </div>
                @endif
                <div class="space-y-3">
                    @foreach ($types as $type)
                        <div class="ap-card rounded-xl p-4 flex flex-wrap items-center gap-3">
                            <div class="flex-1 min-w-0">
                                <div class="font-semibold">{{ $type->name }}</div>
                                <div class="text-sm text-gray-500 dark:text-gray-400">
                                    {{ $type->duration_minutes }} {{ __('messages.minutes') }}
                                    &middot; {{ $type->isFree() ? __('messages.free') : strtoupper($type->currency_code).' '.number_format((float) $type->price, 2) }}
                                    @if ($type->requires_approval) &middot; {{ __('messages.appointments_requires_confirmation') }} @endif
                                </div>
                                @if (! $type->isFree() && ! $type->paymentMethodAvailable())
                                    <div class="text-xs text-amber-600 dark:text-amber-400 mt-1">{{ __('messages.appointments_payment_not_set') }}</div>
                                @elseif (! $type->is_active)
                                    <div class="text-xs text-gray-400 mt-1">{{ __('messages.inactive') ?? 'Inactive' }}</div>
                                @endif
                            </div>
                            @if (! $isViewer)
                                <div class="flex items-center gap-2">
                                    <form method="POST" action="{{ route('appointments.toggle', ['subdomain' => $role->subdomain, 'hash' => $type->hashedId()]) }}">
                                        @csrf
                                        <button type="submit" class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-[#2d2d30]">{{ $type->is_active ? __('messages.appointments_deactivate') : __('messages.appointments_activate') }}</button>
                                    </form>
                                    <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}?edit={{ $type->hashedId() }}"
                                       class="px-3 py-2 text-sm rounded-lg border border-gray-200 dark:border-[#2d2d30]">{{ __('messages.edit') }}</a>
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
