<x-app-admin-layout>
    @php
        // Built here, not inline in the directive: a multi-line array literal inside a Blade
        // directive does not parse. Fully qualified rather than a `use` - this block is inside a
        // component slot, which compiles to a closure body.
        $hash = \App\Utils\UrlUtils::encodeId($event->id);
        $args = ['subdomain' => $subdomain, 'hash' => $hash];
        // Every box office route resolves its own occurrence, and with no date it falls back to
        // the series anchor - so a console opened on one night of a run linked to another night's
        // report. event.edit is deliberately left on $args: it has no occurrence to pin.
        $dateArgs = $args + ['date' => $map->event_date];
        $occurrenceDates = $event->adminOccurrenceDates();

        $boxOfficeProps = [
            'eventId' => $hash,
            'date' => $map->event_date,
            'eventName' => $event->translatedName(),
            'dateLabel' => \Carbon\Carbon::parse($map->event_date)->translatedFormat('l, F j, Y'),
            // Bare $args on purpose: seat-map-store appends `?event_id=..&date=..` to this one
            // unconditionally, so a query string already on it produces a second `?` and a
            // malformed request. The store sends the same date from the `date` prop below.
            'stateUrl' => route('box_office.state', $args, false),
            'blockUrl' => route('box_office.block', $dateArgs, false),
            'unblockUrl' => route('box_office.unblock', $dateArgs, false),
            'releaseUrl' => route('box_office.release_seat', $dateArgs, false),
            'exchangeUrl' => route('box_office.exchange', $dateArgs, false),
            'bookUrl' => route('box_office.book', $dateArgs, false),
            'backUrl' => route('event.edit', $args, false),
            'reportUrl' => route('box_office.report', $dateArgs, false),
            'csrfToken' => csrf_token(),
            'strings' => [
                'back' => __('messages.back'),
                'report' => __('messages.seating_report'),
                'rowPattern' => __('messages.seat_row_label'),
                'seatPattern' => __('messages.seat_number_label'),
                'mapLabel' => __('messages.seating_map_label'),
                'level' => __('messages.seating_level'),
                'zoomIn' => __('messages.seating_zoom_in'),
                'zoomOut' => __('messages.seating_zoom_out'),
                'fit' => __('messages.seating_fit'),
                'mapHint' => __('messages.seating_box_office_hint'),
                'sections' => __('messages.seating_sections'),
                'noSeats' => __('messages.seating_no_seats_left'),
                'lookup' => __('messages.seating_lookup'),
                'lookupPlaceholder' => __('messages.seating_lookup_placeholder'),
                'lookupNothing' => __('messages.seating_lookup_nothing'),
                'lookupMatches' => __('messages.seating_lookup_matches'),
                'count_sold' => __('messages.seating_count_sold'),
                'count_blocked' => __('messages.seating_count_blocked'),
                'count_held' => __('messages.seating_count_held'),
                'arrived' => __('messages.seating_arrived'),
                'sold_label' => __('messages.seating_percent_sold'),
                'countArrived' => __('messages.seating_count_arrived'),
                'count_available' => __('messages.seating_legend_available'),
                'seatSelected' => __('messages.seating_seat_selected'),
                'seatsSelected' => __('messages.seating_seats_selected'),
                'holdReason' => __('messages.seating_hold_reason'),
                'internalNote' => __('messages.seating_internal_note'),
                'internalNoteHelp' => __('messages.seating_internal_note_help'),
                'blockSeats' => __('messages.seating_block_seats'),
                'unblock' => __('messages.seating_unblock'),
                'clear' => __('messages.seating_clear_selection'),
                'exchange' => __('messages.seating_exchange'),
                'exchangeChoose' => __('messages.seating_exchange_choose'),
                'exchangePrompt' => __('messages.seating_exchange_prompt'),
                'releaseSeat' => __('messages.seating_release_seat'),
                'releaseSeats' => __('messages.seating_release_seats'),
                'viewOrder' => __('messages.seating_view_order'),
                'confirmReleaseMany' => __('messages.seating_confirm_release_many'),
                'selectRow' => __('messages.seating_select_row'),
                'multiSelect' => __('messages.seating_multi_select'),
                'confirmRelease' => __('messages.seating_confirm_release'),
                'cancelExchange' => __('messages.seating_cancel_exchange'),
                'releaseHelp' => __('messages.seating_release_help'),
                'actionFailed' => __('messages.error'),
                'loadFailed' => __('messages.seating_load_failed'),
                'kind_house' => __('messages.seating_kind_house'),
                'kind_production' => __('messages.seating_kind_production'),
                'kind_accessibility' => __('messages.seating_kind_accessibility'),
                'kind_box_office' => __('messages.seating_kind_box_office'),
                'bookByPhone' => __('messages.seating_book_by_phone'),
                'bookSeat' => __('messages.seating_book_one_seat'),
                'bookSeats' => __('messages.seating_book_seats'),
                'buyerName' => __('messages.name'),
                'buyerEmail' => __('messages.email'),
                'buyerPhone' => __('messages.phone'),
                'amount' => __('messages.amount'),
                'amountHint' => __('messages.seating_amount_hint'),
                'markPaid' => __('messages.seating_mark_paid'),
                'markUnpaid' => __('messages.seating_mark_unpaid'),
            ],
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            {{-- Outside the Vue mount, not inside it: everything about this screen changes with the
                 date - the map, the counts, every action URL - so it is a navigation, and the mount
                 point must stay empty for seating-box-office.js to own. --}}
            <div class="mb-4 flex justify-end">
                <x-seating-date-picker
                    :action="route('box_office.show', $args)"
                    :dates="$occurrenceDates"
                    :current="$map->event_date" />
            </div>
            <div id="seating-box-office" data-props="{{ json_encode($boxOfficeProps) }}"></div>
        </div>
    </div>

    @vite('resources/js/seating-box-office.js')
</x-app-admin-layout>
