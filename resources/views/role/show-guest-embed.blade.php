<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts">

    <style {!! nonce_attr() !!}>
      .calendar-panel-border {
        background: white !important;
        border-radius: 0.75rem !important;
        box-shadow: 0 1px 2px 0 rgba(0,0,0,0.05) !important;
        border: 1px solid #d1d5db !important;
      }
      .dark .calendar-panel-border {
        background: #1f2937 !important;
        border-color: #374151 !important;
      }
      .calendar-panel-border-transparent {
        background: transparent !important;
        border-radius: 0 !important;
        box-shadow: none !important;
        border: none !important;
      }
    </style>

    <div class="calendar-panel-border px-4 pb-4 max-w-5xl mx-auto pt-2" id="calendar-panel-wrapper">

        @include('role/partials/calendar', ['route' => 'guest', 'tab' => '', 'category' => request('category'), 'schedule' => request('schedule'), 'eventLayout' => $role->event_layout ?? 'calendar', 'pastEvents' => $pastEvents ?? collect()])

    </div>

</x-app-guest-layout>
