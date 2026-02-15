<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts">

    @if (request()->graphic)

        @include('role.partials.calendar-graphic')

    @else

        <style {!! nonce_attr() !!}>
          .calendar-panel-border {
            background: white !important;
            border-radius: 0.75rem !important;
            box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05) !important;
            border: 1px solid #d1d5db !important;
          }
          .dark .calendar-panel-border {
            background: #252526 !important;
            border-color: #2d2d30 !important;
          }
          .calendar-panel-border-transparent {
            background: transparent !important;
            border-radius: 0 !important;
            box-shadow: none !important;
            border: none !important;
          }
        </style>

        <div class="pt-4">
            <div class="calendar-panel-border px-4 pb-6 max-w-5xl mx-auto" id="calendar-panel-wrapper">

                @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'category' => request('category'), 'schedule' => request('schedule'), 'eventLayout' => $role->event_layout ?? 'calendar', 'pastEvents' => $pastEvents ?? collect()])

            </div>
        </div>

    @endif

</x-app-guest-layout>
