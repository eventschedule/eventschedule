<x-app-admin-layout>
    @php
        // Fully qualified rather than a `use` statement: this @php block sits inside a component
        // slot, which Blade compiles into a CLOSURE body, and a `use` import there is a PHP syntax
        // error. Top-of-file @php blocks in the @include'd tab partials can import normally.
        // Editing ONE DATE, or the reusable template. Same component either way - that is the
        // whole point of the owner-agnostic structure service - only the endpoints differ.
        $occurrence = $occurrence ?? null;
        $occurrenceEvent = $occurrenceEvent ?? null;
        $isOccurrence = (bool) $occurrence;

        $hash = $isOccurrence
            ? \App\Utils\UrlUtils::encodeId($occurrenceEvent->id)
            : \App\Utils\UrlUtils::encodeId($plan->id);

        $occurrenceQuery = $isOccurrence ? ['date' => $occurrence->event_date] : [];

        // Path-relative routes: the AP is reachable on a custom domain, and an absolute URL built
        // from app.url would be cross-origin there and fail CORS on the designer's fetch calls.
        $props = [
            'planName' => $isOccurrence ? $occurrenceEvent->translatedName() : $plan->name,
            'structureUrl' => $isOccurrence
                ? route('seating.occurrence_structure', ['subdomain' => $subdomain, 'hash' => $hash] + $occurrenceQuery, false)
                : route('seating.structure', ['subdomain' => $subdomain, 'hash' => $hash], false),
            'saveUrl' => $isOccurrence
                ? route('seating.occurrence_save', ['subdomain' => $subdomain, 'hash' => $hash] + $occurrenceQuery, false)
                : route('seating.save_structure', ['subdomain' => $subdomain, 'hash' => $hash], false),
            'backUrl' => $isOccurrence
                ? route('event.edit', ['subdomain' => $subdomain, 'hash' => $hash], false)
                : route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'seating'], false),
            // Renaming is a TEMPLATE operation. On one date it would be meaningless and confusing.
            'nameEditable' => ! $isOccurrence,
            // Set only on the redirect out of seating.store. The tab no longer asks for a name, so
            // the name box here IS the naming step - without focusing it, a toolbar input carrying
            // only an aria-label and a pre-filled value is easy to walk straight past.
            'focusName' => (bool) session('seating_plan_created'),
            'usage' => $usage ?? ['events' => 0, 'sold' => 0],
            // Which story the sold-seat warning tells: a plan used by other events, or one
            // date of one event. Getting this wrong stacked two amber banners that disagreed.
            'isOccurrence' => $isOccurrence,
            'subtitle' => $isOccurrence
                ? \Carbon\Carbon::parse($occurrence->event_date)->translatedFormat('l, F j, Y')
                : '',
            'csrfToken' => csrf_token(),
            // Flat map rather than a nested lang array: check_translations.php walks top-level
            // keys, and a nested blob would hide every missing translation inside it.
            'strings' => [
                'inUse' => __('messages.seating_plan_in_use'),
                'dateHasSold' => __('messages.seating_date_has_sold'),
                'confirmRemoveLevel' => __('messages.seating_confirm_remove_level'),
                'confirmRemoveSection' => __('messages.seating_confirm_remove_section'),
                'confirmRemoveSeats' => __('messages.seating_confirm_remove_seats'),
                'inUseSold' => __('messages.seating_plan_in_use_sold'),
                'back' => __('messages.back'), 'planName' => __('messages.name'),
                'planLabel' => __('messages.seating_plan'),
                'seatCount' => __('messages.seating_seat_count'),
                'rotation' => __('messages.seating_rotation'),
                'rotateLeft' => __('messages.seating_rotate_left'),
                'rotateRight' => __('messages.seating_rotate_right'),
                'tableSelected' => __('messages.seating_table_selected'),
                'removeTable' => __('messages.seating_remove_table'),
                'confirmRemoveTable' => __('messages.seating_confirm_remove_table'),
                'undo' => __('messages.seating_undo'), 'redo' => __('messages.seating_redo'),
                'saved' => __('messages.saved'), 'dismiss' => __('messages.dismiss'),
                'areYouSure' => __('messages.are_you_sure'),
                'planTooLarge' => __('messages.seating_plan_too_large'),
                // The other three server caps. All four already exist and are already translated;
                // only the seat one was ever shown before the save failed.
                'tooManyLevels' => __('messages.seating_too_many_levels'),
                'tooManySections' => __('messages.seating_too_many_sections'),
                'tooManyTables' => __('messages.seating_too_many_tables'),
                'generateNeedsNumbers' => __('messages.seating_generate_needs_numbers'),
                'cannotRemoveSoldHere' => __('messages.seating_cannot_remove_sold_here'),
                'curveHelp' => __('messages.seating_curve_help'),
                'zoomForNumbers' => __('messages.seating_zoom_for_numbers'),
                // Defined and translated in all 12 locales, and referenced by nothing since the
                // Seating tab stopped asking for a name up front. This is where it belongs.
                'planNamePlaceholder' => __('messages.seating_plan_name_placeholder'),
                'emptyLevel' => __('messages.seating_empty_level'),
                'nothingSelected' => __('messages.seating_nothing_selected'),
                'issueWheelchairOutside' => __('messages.seating_issue_wheelchair_outside'),
                // Already translated into all 12 locales in Phase 1; reused verbatim for the
                // seat aria-labels rather than minting English-only duplicates.
                'rowPattern' => __('messages.seat_row_label'),
                'seatPattern' => __('messages.seat_number_label'),
                'zoomIn' => __('messages.seating_zoom_in'), 'zoomOut' => __('messages.seating_zoom_out'),
                'fit' => __('messages.seating_fit'), 'issue' => __('messages.seating_issue'),
                'issues' => __('messages.seating_issues'), 'unsaved' => __('messages.seating_unsaved'),
                'seats' => __('messages.seating_seats'), 'save' => __('messages.save'),
                'saving' => __('messages.saving'), 'startFrom' => __('messages.seating_start_from'),
                'startFromHelp' => __('messages.seating_start_from_help'),
                'blankCanvas' => __('messages.seating_blank_canvas'),
                'levels' => __('messages.seating_levels'), 'add' => __('messages.add'),
                'level' => __('messages.seating_level'), 'levelName' => __('messages.seating_level_name'),
                'removeLevel' => __('messages.seating_remove_level'),
                'sections' => __('messages.seating_sections'), 'section' => __('messages.seating_section'),
                'addSeated' => __('messages.seating_add_seated'), 'addTables' => __('messages.seating_add_tables'),
                'addStanding' => __('messages.seating_add_standing'),
                'standingCapacity' => __('messages.seating_standing_capacity'),
                'canvasHint' => __('messages.seating_canvas_hint'), 'name' => __('messages.name'),
                'band' => __('messages.seating_band'), 'bandHelp' => __('messages.seating_band_help'),
                'colour' => __('messages.seating_colour'),
                'accessibilityOnly' => __('messages.seating_accessibility_only'),
                'capacity' => __('messages.seating_capacity'),
                'addRows' => __('messages.seating_add_rows'), 'rows' => __('messages.seating_rows'),
                'seatsPerRow' => __('messages.seating_seats_per_row'),
                'rowLabels' => __('messages.seating_row_labels'), 'curve' => __('messages.seating_curve'),
                'aisleAfterSeats' => __('messages.seating_aisle_after_seats'),
                'generateRows' => __('messages.seating_generate_rows'),
                'generateRowsHelp' => __('messages.seating_generate_rows_help'),
                'addTablesTitle' => __('messages.seating_add_tables_title'),
                'tables' => __('messages.seating_tables'), 'tablesLabel' => __('messages.seating_tables'),
                'tableLabel' => __('messages.seating_table'),
                'seatsPerTable' => __('messages.seating_seats_per_table'),
                'shape' => __('messages.seating_shape'), 'round' => __('messages.seating_round'),
                'rectangular' => __('messages.seating_rectangular'),
                'booking' => __('messages.seating_booking'), 'bookSeat' => __('messages.seating_book_seat'),
                'bookWhole' => __('messages.seating_book_whole'), 'bookEither' => __('messages.seating_book_either'),
                'numberSeats' => __('messages.seating_number_seats'),
                'generateTables' => __('messages.seating_generate_tables'),
                'removeSection' => __('messages.seating_remove_section'),
                'seatSelected' => __('messages.seating_seat_selected'),
                'seatsSelected' => __('messages.seating_seats_selected'),
                'kind_standard' => __('messages.seating_kind_standard'),
                'kind_wheelchair' => __('messages.seating_kind_wheelchair'),
                'kind_companion' => __('messages.seating_kind_companion'),
                'kind_restricted_view' => __('messages.seating_kind_restricted_view'),
                'toggleAisle' => __('messages.seating_toggle_aisle'),
                'removeSeats' => __('messages.seating_remove_seats'),
                'standing' => __('messages.seating_standing'), 'seating' => __('messages.seating_seating'),
                'cannotRemoveSold' => __('messages.seating_cannot_remove_sold'),
                'soldSeat' => __('messages.seating_count_sold'),
                'loading' => __('messages.loading'),
                'presetTheatre' => __('messages.seating_preset_theatre'),
                'presetTheatreHelp' => __('messages.seating_preset_theatre_help'),
                'presetCabaret' => __('messages.seating_preset_cabaret'),
                'presetCabaretHelp' => __('messages.seating_preset_cabaret_help'),
                'presetRows' => __('messages.seating_preset_rows'),
                'presetRowsHelp' => __('messages.seating_preset_rows_help'),
                'presetMixed' => __('messages.seating_preset_mixed'),
                'presetMixedHelp' => __('messages.seating_preset_mixed_help'),
                'presetStalls' => __('messages.seating_preset_stalls'),
                'presetBalcony' => __('messages.seating_preset_balcony'),
                'presetFloor' => __('messages.seating_preset_floor'),
                'presetTables' => __('messages.seating_tables'),
                'presetSeating' => __('messages.seating_seating'),
                'presetSeated' => __('messages.seating_preset_seated'),
                'presetStanding' => __('messages.seating_standing'),
                'issueUnnamedSection' => __('messages.seating_issue_unnamed_section'),
                'issueNoSeats' => __('messages.seating_issue_no_seats'),
                'issueNoCapacity' => __('messages.seating_issue_no_capacity'),
                'issueNoBand' => __('messages.seating_issue_no_band'),
                'issueDuplicateSeat' => __('messages.seating_issue_duplicate_seat'),
                'loadFailed' => __('messages.seating_load_failed'),
                'saveFailed' => __('messages.seating_save_failed'),
                'staleRevision' => __('messages.seating_stale_revision'),
                'retry' => __('messages.retry'),
            ],
        ];
    @endphp

    <div class="py-6">
        <div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8">
            @if ($isOccurrence)
                {{-- Unmistakable, on purpose. Every support ticket in this area starts with
                     somebody editing the template when they meant one date, or the reverse. --}}
                <div class="mb-4 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                    <div class="flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 shrink-0" fill="none" viewBox="0 0 24 24"
                             stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round"
                                  d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <div class="flex-1">
                            <p class="text-sm font-medium text-amber-800 dark:text-amber-200">
                                {{ __('messages.seating_editing_one_date', ['date' => \Carbon\Carbon::parse($occurrence->event_date)->translatedFormat('l, F j, Y')]) }}
                            </p>
                            <p class="mt-1 text-xs text-amber-700 dark:text-amber-300">
                                {{ __('messages.seating_editing_one_date_help') }}
                            </p>
                        </div>
                        {{-- Names the consequence. revertToTemplate() deliberately does NOT refuse
                             over staff holds - it deletes the map and they go with it - and a
                             generic "are you sure" never said so. The docs had to. --}}
                        <form method="POST" class="form-confirm shrink-0"
                              data-confirm="{{ __('messages.seating_confirm_revert') }}"
                              action="{{ route('seating.occurrence_revert', ['subdomain' => $subdomain, 'hash' => $hash] + $occurrenceQuery) }}">
                            @csrf
                            <button type="submit" class="text-xs font-medium text-amber-800 dark:text-amber-200 hover:underline">
                                {{ __('messages.seating_revert_to_template') }}
                            </button>
                        </form>
                    </div>
                </div>
            @endif

            {{-- data-props, not a Vue attribute binding: @json inside an attribute on a Vue-mounted
                 element kills the mount, and this element IS the mount point. --}}
            <div id="seating-designer" data-props="{{ json_encode($props) }}"></div>
        </div>
    </div>

    @vite('resources/js/seating-designer.js')
</x-app-admin-layout>
