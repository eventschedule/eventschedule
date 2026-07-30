@php
    $filter = request('filter', 'upcoming');
    $filterUrl = fn ($f) => route('role.view_admin', [
        'subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings', 'filter' => $f,
    ]);
    $use24 = get_use_24_hour_time($role);

    // Per-row presentation, computed once and shared by the desktop table and the mobile cards.
    $rows = collect($bookings->all())->map(function ($s) use ($use24, $role) {
        $e = $s->event;
        $cancelled = $e->is_cancelled || in_array($s->status, ['cancelled', 'refunded', 'expired']);
        $pending = $e->isAwaitingCreatorApproval();
        $confirmed = ! $cancelled && ! $pending && $s->status === 'paid';

        return [
            'sale' => $s,
            'event' => $e,
            'cancelled' => $cancelled,
            'pending' => $pending,
            // $confirmed above is status-based, so cash bookings are not 'confirmed' here even though
            // bookingState() calls them confirmed - gate row actions on these instead.
            'past' => $e->getStartDateTime()->isPast(),
            // Asked, not re-derived. These rows used to compute their own predicates and drifted from the
            // endpoints in both directions: offering Reschedule on an unpaid card hold or a deactivated
            // type (where the POST bounces straight back with an error), and hiding it on pending rows
            // where it is allowed and the guest page offers it as "Change time".
            'rescheduleBlocked' => \App\Support\AppointmentRescheduleGate::blockedReason($e, $s, $role),
            // Nothing else bumps ical_sequence on a live appointment, so > 0 means "this was moved".
            'moved' => ! $cancelled && (int) $e->ical_sequence > 0,
            'shown' => \App\Utils\AppointmentTimeUtils::render($e, null, $use24),
            'label' => $cancelled ? __('messages.appointments_cancelled')
                : ($pending ? __('messages.appointments_request_sent')
                : ($confirmed ? __('messages.appointments_confirmed_label') : __('messages.appointments_awaiting_payment'))),
            'badge' => $cancelled ? 'bg-gray-100 dark:bg-gray-700 text-gray-600 dark:text-gray-300'
                : ($confirmed ? 'bg-green-100 dark:bg-green-900/30 text-green-700 dark:text-green-400'
                : 'bg-amber-100 dark:bg-amber-900/30 text-amber-700 dark:text-amber-400'),
            'amount' => (float) $s->payment_amount > 0
                ? \App\Utils\MoneyUtils::format((float) $s->payment_amount, $e->ticket_currency_code)
                : __('messages.free'),
            // The guest's notes are stored on the event description behind a prefix; strip it.
            'notes' => $e->description
                ? trim(preg_replace(
                    '/^'.preg_quote(__('messages.appointments_notes_from', ['name' => $s->name]), '/').':\s*/u',
                    '', $e->description))
                : null,
        ];
    });
@endphp

<div class="flex flex-wrap items-center justify-between gap-3">
{{-- Same segmented control as the sub-view switcher, with the pending count where the owner
     actually needs it. --}}
<div class="inline-flex flex-wrap items-center gap-1 rounded-xl bg-gray-100 dark:bg-[#252526] p-1">
    @foreach (['upcoming', 'pending', 'past', 'cancelled'] as $f)
        <a href="{{ $filterUrl($f) }}"
           @if ($filter === $f) aria-current="page" style="box-shadow: inset 0 2px 4px rgba(0, 0, 0, 0.08);" @endif
           class="rounded-lg px-3 py-1.5 text-sm font-medium transition-all duration-200 {{ $filter === $f ? 'bg-white dark:bg-[#1e1e1e] text-gray-900 dark:text-white shadow-sm' : 'text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300' }}">
            {{ __('messages.appointments_filter_'.$f) }}
            @if ($f === 'pending' && $pendingBookingCount > 0)
                <span class="ms-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $pendingBookingCount }}</span>
            @endif
        </a>
    @endforeach
</div>

    <form method="GET" action="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}" class="flex items-center gap-2">
        <input type="hidden" name="view" value="bookings">
        <input type="hidden" name="filter" value="{{ $filter }}">
        <label for="booking-search" class="sr-only">{{ __('messages.search') }}</label>
        <input type="search" id="booking-search" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.name') }} / {{ __('messages.email') }}"
               class="w-44 text-sm px-3 py-1.5 rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
        <x-brand-button type="submit" class="!px-3 !py-1.5 !text-sm">{{ __('messages.search') }}</x-brand-button>
        @if (request('search'))
            <a href="{{ $filterUrl($filter) }}" class="text-sm text-gray-500 dark:text-gray-400 hover:underline">{{ __('messages.clear') }}</a>
        @endif
    </form>
</div>

@if ($bookings->isEmpty())
    <div class="ap-card rounded-xl p-8 text-center text-gray-500 dark:text-gray-400">{{ __('messages.appointments_no_bookings') }}</div>
@else
    {{-- Desktop table --}}
    <div class="ap-card rounded-xl hidden md:block">
        <table class="w-full text-sm text-gray-900 dark:text-gray-100">
            <thead class="bg-gray-50 dark:bg-gray-800 text-left text-gray-500 dark:text-gray-400">
                <tr>
                    <th class="p-3 font-medium">{{ __('messages.date') }}</th>
                    <th class="p-3 font-medium">{{ __('messages.appointment_types') }}</th>
                    <th class="p-3 font-medium">{{ __('messages.name') }}</th>
                    <th class="p-3 font-medium">{{ __('messages.amount') }}</th>
                    <th class="p-3 font-medium">{{ __('messages.status') }}</th>
                    <th class="p-3"></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($rows as $row)
                    @php $s = $row['sale']; $e = $row['event']; @endphp
                    <tr class="border-t border-gray-100 dark:border-gray-700">
                        <td class="p-3 whitespace-nowrap">
                            {{ $row['shown']['date'] }}
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row['shown']['time'] }}</div>
                        </td>
                        <td class="p-3">{{ $e->appointmentType?->name }}</td>
                        <td class="p-3">
                            {{ $s->name }}
                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ $s->email }}</div>
                            @if ($s->phone)
                                <div class="text-xs"><a href="tel:{{ $s->phone }}" class="text-[var(--brand-blue)] hover:underline">{{ $s->phone }}</a></div>
                            @endif
                            @if ($row['notes'])
                                <div class="text-xs text-gray-600 dark:text-gray-400 mt-1"><span class="font-medium">{{ __('messages.notes') }}:</span> {{ $row['notes'] }}</div>
                            @endif
                        </td>
                        <td class="p-3 whitespace-nowrap">{{ $row['amount'] }}</td>
                        <td class="p-3">
                            <span class="inline-block px-2 py-1 rounded-full text-xs {{ $row['badge'] }}">{{ $row['label'] }}</span>
                            @if ($row['moved'])
                                <span class="ms-1 inline-block px-2 py-1 rounded-full text-xs bg-blue-100 dark:bg-blue-900/30 text-[var(--brand-blue)] dark:text-blue-400">{{ __('messages.appointments_moved_badge') }}</span>
                            @endif
                        </td>
                        <td class="p-3 text-end whitespace-nowrap">
                            @include('role.partials.appointment-booking-actions', ['row' => $row])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Mobile cards: every other AP list ships this instead of a horizontally scrolling table. --}}
    <div class="md:hidden space-y-4">
        @foreach ($rows as $row)
            @php $s = $row['sale']; $e = $row['event']; @endphp
            <div class="ap-card rounded-xl p-5">
                <div class="flex items-start justify-between gap-3">
                    <div>
                        <div class="font-semibold text-gray-900 dark:text-gray-100">{{ $row['shown']['date'] }}</div>
                        <div class="text-sm text-gray-500 dark:text-gray-400">{{ $row['shown']['time'] }}</div>
                    </div>
                    <div class="flex flex-col items-end gap-1 flex-shrink-0">
                        <span class="inline-block px-2 py-1 rounded-full text-xs {{ $row['badge'] }}">{{ $row['label'] }}</span>
                        @if ($row['moved'])
                            <span class="inline-block px-2 py-1 rounded-full text-xs bg-blue-100 dark:bg-blue-900/30 text-[var(--brand-blue)] dark:text-blue-400">{{ __('messages.appointments_moved_badge') }}</span>
                        @endif
                    </div>
                </div>

                <div class="mt-3 rounded-lg bg-gray-50 dark:bg-[#252526] p-3 space-y-1 text-sm">
                    <div class="text-gray-900 dark:text-gray-100">{{ $e->appointmentType?->name }}</div>
                    <div class="text-gray-700 dark:text-gray-300">{{ $s->name }}</div>
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $s->email }}</div>
                    @if ($s->phone)
                        <div class="text-xs"><a href="tel:{{ $s->phone }}" class="text-[var(--brand-blue)] hover:underline">{{ $s->phone }}</a></div>
                    @endif
                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $row['amount'] }}</div>
                    @if ($row['notes'])
                        <div class="text-xs text-gray-600 dark:text-gray-400 pt-1"><span class="font-medium">{{ __('messages.notes') }}:</span> {{ $row['notes'] }}</div>
                    @endif
                </div>

                <div class="mt-3 flex flex-wrap items-center gap-2">
                    @include('role.partials.appointment-booking-actions', ['row' => $row])
                </div>
            </div>
        @endforeach
    </div>

    @if ($bookings instanceof \Illuminate\Contracts\Pagination\Paginator && $bookings->hasPages())
        <div class="mt-4">
            {{ $bookings->links() }}
        </div>
    @endif
@endif
