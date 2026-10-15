@php
    use App\Utils\UrlUtils;

    $filter = request('filter', 'upcoming');
    // The host filter rides along with the other filters, so moving between Upcoming and Past keeps
    // one host's bookings in view (the owner is sent here when a host leaves the team).
    $hostParam = request('host');
    $filterUrl = fn ($f) => route('role.view_admin', array_filter([
        'subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings', 'filter' => $f, 'host' => $hostParam,
    ]));
    $use24 = get_use_24_hour_time($role);

    // Everybody who hosts or hosted a type here, by id. The column and the filter only appear once
    // there is somebody to name.
    $hostNames = $appointmentHosts['names'] ?? [];
    $showHosts = ! empty($hostNames);

    // Per-row presentation, computed once.
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
            // where it is allowed and the guest page offers it as "Change time". Owner mode, as the
            // endpoint behind the link asks it: a type switched off because its host left the team
            // still has bookings to move.
            'rescheduleBlocked' => \App\Support\AppointmentRescheduleGate::blockedReason($e, $s, $role, true, true),
            // Nothing else bumps ical_sequence on a live appointment, so > 0 means "this was moved".
            'moved' => ! $cancelled && (int) $e->ical_sequence > 0,
            'shown' => \App\Utils\AppointmentTimeUtils::render($e, null, $use24),
            'label' => $cancelled ? __('messages.appointments_cancelled')
                : ($pending ? __('messages.appointments_request_sent')
                : ($confirmed ? __('messages.appointments_confirmed_label') : __('messages.appointments_awaiting_payment'))),
            // The kit's status mark (form-kit-styles): green when settled, amber while it waits on
            // somebody, plain grey once it is off.
            'status' => $cancelled ? '' : ($confirmed ? 'is-on' : 'is-warn'),
            'amount' => (float) $s->payment_amount > 0
                ? \App\Utils\MoneyUtils::format((float) $s->payment_amount, $e->ticket_currency_code)
                : __('messages.free'),
            // Made by an agent over the API (a voice agent, most often): a name or an address may
            // have been misheard, so the owner is told where it came from.
            'viaApi' => $s->utm_source === 'api',
            // The guest's notes are stored on the event description behind a prefix; strip it.
            'notes' => $e->description
                ? trim(preg_replace(
                    '/^'.preg_quote(__('messages.appointments_notes_from', ['name' => $s->name]), '/').':\s*/u',
                    '', $e->description))
                : null,
        ];
    });
@endphp

{{-- Which bookings: four filters that are links, the one in force pressed in. --}}
<div class="flex flex-wrap items-center gap-2" role="group" aria-label="{{ __('messages.bookings') }}">
    @foreach (['upcoming', 'pending', 'past', 'cancelled'] as $f)
        <a href="{{ $filterUrl($f) }}" class="page-pill" @if ($filter === $f) aria-current="true" @endif>
            {{ __('messages.appointments_filter_'.$f) }}
            @if ($f === 'pending' && $pendingBookingCount > 0)
                <span class="ms-1 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white bg-red-500 rounded-full">{{ $pendingBookingCount }}</span>
            @endif
        </a>
    @endforeach
</div>

<form method="GET" action="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments']) }}" class="page-filters">
    <input type="hidden" name="view" value="bookings">
    <input type="hidden" name="filter" value="{{ $filter }}">
    @if ($showHosts)
        <div class="page-filter">
            <label for="booking-host">{{ __('messages.appointments_host') }}</label>
            <select id="booking-host" name="host" data-auto-submit>
                <option value="">{{ __('messages.appointments_all_hosts') }}</option>
                @foreach ($hostNames as $hostId => $hostName)
                    <option value="{{ UrlUtils::encodeId($hostId) }}" @selected((int) ($appointmentHosts['filter'] ?? 0) === (int) $hostId)>{{ $hostName ?: __('messages.appointments_host_former') }}</option>
                @endforeach
            </select>
        </div>
    @elseif ($hostParam)
        <input type="hidden" name="host" value="{{ $hostParam }}">
    @endif
    <div class="page-filter is-grow">
        <label for="booking-search">{{ __('messages.search') }}</label>
        <input type="search" id="booking-search" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.name') }} / {{ __('messages.email') }}">
    </div>
    <div class="is-end">
        <x-brand-button type="submit">{{ __('messages.search') }}</x-brand-button>
        @if (request('search') || $hostParam)
            <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'appointments', 'view' => 'bookings', 'filter' => $filter]) }}" class="event-link">{{ __('messages.clear') }}</a>
        @endif
    </div>
</form>

@if ($bookings->isEmpty())
    <div class="ap-card rounded-xl">
        <x-page-empty :title="__('messages.appointments_no_bookings')" />
    </div>
@else
    {{-- One list, a table from a tablet up and a stack of rows on a phone (partials/admin-page-styles).
         It used to be a table plus a second copy of every row as phone cards. --}}
    <div class="ap-card rounded-xl overflow-hidden">
        <table role="table" class="page-table">
            <thead role="rowgroup">
                <tr role="row">
                    <th scope="col" role="columnheader">{{ __('messages.date') }}</th>
                    <th scope="col" role="columnheader">{{ __('messages.appointment_types') }}</th>
                    @if ($showHosts)
                        <th scope="col" role="columnheader">{{ __('messages.appointments_host') }}</th>
                    @endif
                    <th scope="col" role="columnheader">{{ __('messages.name') }}</th>
                    <th scope="col" role="columnheader" class="c-num">{{ __('messages.amount') }}</th>
                    <th scope="col" role="columnheader">{{ __('messages.status') }}</th>
                    <th scope="col" role="columnheader"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                </tr>
            </thead>
            <tbody role="rowgroup">
                @foreach ($rows as $row)
                    @php
                        $s = $row['sale'];
                        $e = $row['event'];
                        $rowHostId = (int) $e->appointment_host_user_id;
                        $rowHost = $rowHostId ? ($hostNames[$rowHostId] ?? null) : null;
                    @endphp
                    <tr role="row">
                        <td role="cell" class="c-date">
                            {{ $row['shown']['date'] }}
                            <span class="c-sub"><bdi dir="ltr">{{ $row['shown']['time'] }}</bdi></span>
                        </td>
                        <td role="cell" data-label="{{ __('messages.appointment_types') }}"><bdi v-pre>{{ $e->appointmentType?->name }}</bdi></td>
                        @if ($showHosts)
                            <td role="cell" data-label="{{ __('messages.appointments_host') }}">@if ($rowHostId)<bdi v-pre>{{ $rowHost ?: __('messages.appointments_host_former') }}</bdi>@endif</td>
                        @endif
                        <td role="cell" class="c-main">
                            <div class="page-person-text">
                                <div class="event-list-name">
                                    <bdi v-pre>{{ $s->name }}</bdi>
                                    @if ($row['viaApi'])
                                        <span class="event-chip" title="{{ __('messages.appointments_booked_via_api_note') }}">{{ __('messages.appointments_booked_via_api') }}</span>
                                    @endif
                                </div>
                                <span class="c-sub"><a href="mailto:{{ $s->email }}" class="hover:underline" dir="ltr" v-pre>{{ $s->email }}</a></span>
                                @if ($s->phone)
                                    <span class="c-sub"><a href="tel:{{ $s->phone }}" class="hover:underline" dir="ltr" v-pre>{{ $s->phone }}</a></span>
                                @endif
                                @if ($row['notes'])
                                    {{-- Capped, so a long note cannot push the actions off the row. --}}
                                    <span class="c-sub max-w-xs"><span class="font-medium">{{ __('messages.notes') }}:</span> <bdi v-pre>{{ $row['notes'] }}</bdi></span>
                                @endif
                            </div>
                        </td>
                        <td role="cell" class="c-num" data-label="{{ __('messages.amount') }}">{{ $row['amount'] }}</td>
                        <td role="cell">
                            <span class="event-status {{ $row['status'] }}">{{ $row['label'] }}</span>
                            @if ($row['moved'])
                                <span class="event-chip">{{ __('messages.appointments_moved_badge') }}</span>
                            @endif
                        </td>
                        <td role="cell" class="c-actions">
                            @include('role.partials.appointment-booking-actions', ['row' => $row])
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    @if ($bookings instanceof \Illuminate\Contracts\Pagination\Paginator && $bookings->hasPages())
        <div class="page-pager">
            {{ $bookings->links() }}
        </div>
    @endif
@endif
