<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts">

    <div class="px-4 pb-4 max-w-5xl mx-auto">

        @include('role/partials/calendar', ['route' => 'guest', 'tab' => ''])

    </div>

</x-app-guest-layout>
