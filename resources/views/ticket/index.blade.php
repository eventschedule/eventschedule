<x-app-admin-layout>

    <x-slot name="head">
        {{-- The design's sizes for this page (2026-11-24), over the kit's .tickets-* rules: the next
             ticket larger and lifted (.ap-hero), "Paid" as a bold green word with no dot, and the
             rules between days set in from the card's edges rather than running across it. --}}
        <style {!! nonce_attr() !!}>
            .tickets-next {
              gap: 1.125rem 1.75rem;
              padding: clamp(1.25rem, 3vw, 1.75rem);
            }
            .tickets-next-name {
              margin-top: 0.375rem;
              font-size: clamp(1.5rem, 4vw, 1.875rem);
              line-height: 1.15;
              letter-spacing: -0.025em;
            }
            .tickets-next-meta {
              gap: 0.25rem 0.75rem;
              margin-top: 0.5rem;
              font-size: 0.9375rem;
            }
            .tickets-next-meta .event-link {
              font-weight: 500;
            }
            .tickets-next .tickets-show {
              min-height: 3.25rem;
              padding-inline: 1.5rem;
              border-radius: 0.75rem;
              font-size: 1rem;
            }
            .tickets-next .event-status,
            .tickets-row-meta .event-status {
              font-size: inherit;
              font-weight: 600;
            }
            .tickets-next .event-status::before,
            .tickets-row-meta .event-status::before {
              content: none;
            }
            .tickets-days {
              padding: 0.25rem 1.25rem;
            }
            .tickets-day {
              gap: 0 1.25rem;
              padding: 0.625rem 0;
            }
            @media (min-width: 640px) {
              .tickets-day {
                grid-template-columns: 6rem minmax(0, 1fr);
              }
            }
            .tickets-day-label {
              padding-top: 1.0625rem;
            }
            .tickets-row {
              gap: 0.75rem;
              min-height: 3.5rem;
              padding: 0;
            }
            .tickets-row-time {
              width: 4.125rem;
              font-size: 0.8125rem;
              font-variant-numeric: tabular-nums;
              white-space: nowrap;
            }
            .tickets-row-name {
              letter-spacing: -0.01em;
            }
            .tickets-row-meta {
              gap: 0 0.625rem;
            }
            /* A day's tickets keep to a reading width (the design review of 2026-11): across the
               whole card, View ticket stood a hand's width from the event it opens. */
            .tickets-day-rows {
              max-width: 42rem;
            }
        </style>
    </x-slot>

    {{-- The tickets this person holds, upcoming first. One list for every width: it is a table
         from a tablet up and a stack of rows on a phone (.page-table), where it used to be drawn
         twice, once as a table and once as cards with their own status pills. --}}
    <div class="page-shell">
        <x-page-header
            :title="$past ? __('messages.past_events') : __('messages.your_tickets')"
            :lead="$past ? null : __('messages.your_tickets_lead')"
            :back="$past ? route('tickets') : null"
            :back-label="__('messages.your_tickets')">
            @if (! $past && ! empty($hasPastTickets))
            <x-slot name="actions">
                <a href="{{ route('tickets', ['past' => 1]) }}" class="page-tool">{{ __('messages.show_past_events') }}</a>
            </x-slot>
            @endif
        </x-page-header>

        @php
            // 2026-11 redesign: what is coming, in date order, is the next ticket on its own and the
            // rest under their days. Past tickets, or any other order, stay the sortable table.
            $byDay = ! $past && $sortBy === 'event_date' && strtolower($sortDir ?: 'asc') !== 'desc' && $sales->count() > 0;
        @endphp
        @if ($byDay)
        @php
            $whenOf = function ($sale) {
                $start = $sale->event->getStartDateTime($sale->event_date, true);
                return [
                    'day' => $start->isToday() ? __('messages.today') : $start->translatedFormat('D, M j'),
                    'key' => $start->format('Y-m-d'),
                    'time' => $start->format($sale->event->use24HourTime() ? 'H:i' : 'g:i A'),
                ];
            };
            // The list comes ordered by date only: within a day, by the time it starts.
            $ordered = $sales->sortBy(fn ($sale) => $sale->event->getStartDateTime($sale->event_date)->timestamp)->values();
            $next = $ordered->first();
            $nextWhen = $whenOf($next);
            $rest = $ordered->slice(1);
            $placeOf = fn ($sale) => $sale->event->venue ? $sale->event->venue->getDisplayName(false) : $sale->event->getVenueDisplayName(false);
        @endphp
        <div class="tickets-next ap-card ap-hero rounded-xl">
            <div class="min-w-0">
                <p class="tickets-next-kicker">{{ __('messages.next_up') }} &middot; {{ $nextWhen['day'] }}, <span dir="ltr">{{ $nextWhen['time'] }}</span></p>
                <p class="tickets-next-name" v-pre><bdi>{{ $next->event->name }}</bdi></p>
                <p class="tickets-next-meta">
                    @if ($next->event->venue && $next->event->venue->isClaimed())
                    <a href="{{ $next->event->venue->getGuestUrl() }}" target="_blank" rel="noopener" class="event-link"><bdi>{{ $next->event->venue->getDisplayName(false) }}</bdi></a>
                    @else
                    <bdi>{{ $placeOf($next) }}</bdi>
                    @endif
                    <x-sale-status :status="$next->status" />
                </p>
            </div>
            <x-brand-link :href="route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($next->event_id), 'secret' => $next->secret])" target="_blank" rel="noopener" class="tickets-show gap-2.5 flex-none">
                {{ __('messages.show_ticket') }}
                <span aria-hidden="true">&rarr;</span>
            </x-brand-link>
        </div>

        @if ($rest->isNotEmpty())
        <h2 class="tickets-label">{{ __('messages.dash_coming_up') }} &middot; {{ __('messages.realtime_n_more', ['count' => $rest->count()]) }}</h2>
        <div class="ap-card rounded-xl overflow-hidden">
            <ul class="tickets-days">
                @foreach ($rest->groupBy(fn ($sale) => $whenOf($sale)['key']) as $daySales)
                <li class="tickets-day">
                    <p class="tickets-day-label">{{ $whenOf($daySales->first())['day'] }}</p>
                    <ul class="tickets-day-rows">
                        @foreach ($daySales as $sale)
                        <li class="tickets-row">
                            <span class="tickets-row-time" dir="ltr">{{ $whenOf($sale)['time'] }}</span>
                            <span class="min-w-0 flex-1">
                                <a href="{{ $sale->getEventUrl() }}" target="_blank" rel="noopener" class="tickets-row-name"><bdi>{{ $sale->event->name }}</bdi></a>
                                <span class="tickets-row-meta"><bdi>{{ $placeOf($sale) }}</bdi> <x-sale-status :status="$sale->status" /></span>
                            </span>
                            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" target="_blank" rel="noopener" class="page-tool flex-none">{{ __('messages.view_ticket') }}</a>
                        </li>
                        @endforeach
                    </ul>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
        @elseif ($sales->count() > 0)
        <div class="ap-card rounded-xl overflow-hidden">
            <table class="page-table is-hover">
                <thead>
                    <tr>
                        <x-page-sort column="event_name" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.event') }}</x-page-sort>
                        <th scope="col">{{ __('messages.venue') }}</th>
                        <x-page-sort column="event_date" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.date') }}</x-page-sort>
                        <x-page-sort column="status" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.status') }}</x-page-sort>
                        <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($sales as $sale)
                    <tr>
                        <td class="c-main c-strong">
                            <a href="{{ $sale->getEventUrl() }}" target="_blank" rel="noopener" class="event-link"><bdi>{{ $sale->event->name }}</bdi></a>
                        </td>
                        <td class="c-wrap">
                            @if ($sale->event->venue && $sale->event->venue->isClaimed())
                            <a href="{{ $sale->event->venue->getGuestUrl() }}" target="_blank" rel="noopener" class="event-link"><bdi>{{ $sale->event->venue->getDisplayName(false) }}</bdi></a>
                            @else
                            <bdi>{{ $sale->event->getVenueDisplayName(false) }}</bdi>
                            @endif
                        </td>
                        <td class="c-date">{{ $sale->event->localStartsAt(true, $sale->event_date) }}</td>
                        <td><x-sale-status :status="$sale->status" /></td>
                        <td class="c-actions">
                            <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}" target="_blank" rel="noopener" class="page-tool">{{ __('messages.view_ticket') }}</a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="ap-card rounded-xl">
            <x-page-empty
                :title="$past ? __('messages.no_past_tickets') : __('messages.no_tickets')"
                :text="$past ? null : __('messages.no_tickets_description')"
                icon="M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z" />
        </div>
        @endif
    </div>

<script {!! nonce_attr() !!}>
    document.addEventListener('click', function(e) {
        var header = e.target.closest('[data-sort]');
        if (header) {
            var url = new URL(window.location.href);
            var currentSort = url.searchParams.get('sort_by') || 'event_date';
            var currentDir = url.searchParams.get('sort_dir') || '{{ $past ? "desc" : "asc" }}';
            var sortBy = header.getAttribute('data-sort');
            url.searchParams.set('sort_by', sortBy);
            url.searchParams.set('sort_dir', currentSort === sortBy && currentDir === 'asc' ? 'desc' : 'asc');
            // A new order starts at its first page: page 3 of the old order is no place in the new one.
            url.searchParams.delete('page');
            window.location.href = url.toString();
        }
    });
</script>

</x-app-admin-layout>
