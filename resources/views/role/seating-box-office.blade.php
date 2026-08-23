<x-app-admin-layout>
    @php
        // Built here, not inline in the directive: a multi-line array literal inside a Blade
        // directive does not parse. Fully qualified rather than a `use` - this block is inside a
        // component slot, which compiles to a closure body.
        $hash = \App\Utils\UrlUtils::encodeId($event->id);
        $args = ['subdomain' => $subdomain, 'hash' => $hash];

        $boxOfficeProps = [
            'eventId' => $hash,
            'date' => $map->event_date,
            'eventName' => $event->translatedName(),
            'dateLabel' => \Carbon\Carbon::parse($map->event_date)->translatedFormat('l, F j, Y'),
            'stateUrl' => route('box_office.state', $args, false),
            'blockUrl' => route('box_office.block', $args, false),
            'unblockUrl' => route('box_office.unblock', $args, false),
            'releaseUrl' => route('box_office.release_seat', $args, false),
            'exchangeUrl' => route('box_office.exchange', $args, false),
            'bookUrl' => route('box_office.book', $args, false),
            'backUrl' => route('event.edit', $args, false),
            'reportUrl' => route('box_office.report', $args, false),
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
                'count_sold' => __('messages.seating_count_sold'),
                'count_blocked' => __('messages.seating_count_blocked'),
                'count_held' => __('messages.seating_count_held'),
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
            <div id="seating-box-office" data-props="{{ json_encode($boxOfficeProps) }}"></div>
        </div>
    </div>

    @vite('resources/js/seating-box-office.js')
</x-app-admin-layout>
