<x-app-admin-layout>

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

        @if ($sales->count() > 0)
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
