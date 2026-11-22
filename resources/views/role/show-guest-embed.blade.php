<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts">

    @if (request()->graphic)

        @include('role.partials.calendar-graphic')

    @else

        {{-- The calendar a site embeds, laid on the stage (partials/guest-stage-styles) as the
             schedule page's is: the month lies on the sheet with no panel of its own, and the
             schedule's background shows around the sheet from a tablet up. On a phone the sheet
             runs to the frame's edges. --}}
        <style {!! nonce_attr() !!}>
          .gk-p-embed-wrap { padding: 0; }
          @media (min-width: 40rem) { .gk-p-embed-wrap { padding: 1rem 1rem 0; } }
          {{-- Its own side padding: the stage's centres a 70rem column with calc((100% - 70rem) / 2),
               and here 100% is the frame, not this 64rem sheet, so a wide frame left the list a
               strip in the middle (EmbedEventJourneyTest, 290px of a 1886px frame). --}}
          .gk-stage.gk-p-embed { max-width: 64rem; margin-block: 0 1rem; padding-inline: clamp(1rem, 4vw, 2.5rem); }
          .calendar-panel-border,
          .calendar-panel-border-transparent {
            background: transparent !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            border: none !important;
          }
        </style>

        {{-- data-embed-root: what partials/embed-frame measures to fit the frame to the calendar. --}}
        <div class="gk-p-embed-wrap" data-embed-root>
            <div class="gk-stage gk-p-embed">
                <div class="calendar-panel-border" id="gp-calendar">

                    @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'calendarHeadingTag' => 'h1', 'category' => request('category'), 'schedule' => request('schedule'), 'eventLayout' => $role->activeEventLayout(), 'pastEvents' => $pastEvents ?? collect()])

                </div>
            </div>
        </div>

    @endif

</x-app-guest-layout>
