<x-app-admin-layout>
    {{-- The country flags of the events card: the sprite /admin/realtime uses. --}}
    <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">

    {{-- What each card counts is App\Services\AdminDashboard's to say. The order is "what's new
         first": the four numbers, then the newest schedules and events, then the breakdowns that
         move slowly. Which cards an install gets is two rules - revenue where there is billing
         (hosted), the hub's federation numbers on the nexus and this install's own sharing
         anywhere else - and a new install gets the numbers and one card. --}}
    @php
        $hosted = (bool) config('app.hosted');
        $signups = $dashboard['signups'];
        $active = $dashboard['active'];
        $revenue = $dashboard['revenue'];
        $events = $dashboard['events'];
        $federation = $dashboard['federation'];
        $schedules = $dashboard['schedules'];
        $recentEvents = $dashboard['recentEvents'];
        $system = $dashboard['system'];

        // "+16%" and the colour it wears. One decimal only when there is one. Marked left-to-right,
        // as every signed figure on the page is: in a right-to-left language the sign otherwise
        // lands after the number.
        $pct = fn ($value) => new \Illuminate\Support\HtmlString('<span dir="ltr">'.($value >= 0 ? '+' : '').rtrim(rtrim(number_format((float) $value, 1), '0'), '.').'%</span>');
        $tone = fn ($value) => $value >= 0 ? 'text-green-700 dark:text-green-400' : 'text-red-600 dark:text-red-400';
        $pair = 'grid grid-cols-1 lg:grid-cols-2 gap-4';
    @endphp

    {{-- data-now: the server's clock, for the "new since your last visit" marks. A row's time is
         the server's, so the visit it is compared with has to be too. --}}
    <div class="space-y-4" id="admin-dashboard" data-now="{{ now()->getTimestamp() }}">
        @include('admin.partials._navigation', ['active' => 'dashboard'])

        {{-- Everything waiting on an admin, from AdminAlertService, as chips beside the live line.
             Only there when there is something to do. --}}
        @if ($adminAlerts->isNotEmpty())
            <x-needs-attention :items="$adminAlerts" layout="chips" :limit="4">
                @include('admin.dashboard._live')
            </x-needs-attention>
        @else
            @include('admin.dashboard._live')
        @endif

        @include('admin.dashboard._headline')

        @if ($dashboard['firstRun'])
            @include('admin.dashboard._first-run')
        @else
            <div class="{{ $pair }}">
                @include('admin.dashboard._recent-schedules')
                @include('admin.dashboard._recent-events')
            </div>

            <div class="{{ $pair }}">
                @include('admin.dashboard._signups')
                @include('admin.dashboard._active-users')
            </div>

            <div class="{{ $pair }}">
                @include('admin.dashboard._sources')
                @include('admin.dashboard._latest-signups')
            </div>

            @if ($hosted)
                <div class="{{ $pair }}">
                    @include('admin.dashboard._revenue')
                    @include('admin.dashboard._upcoming-events')
                </div>
            @else
                @include('admin.dashboard._upcoming-events', ['wide' => true])
            @endif

            @include('admin.dashboard._federation')
        @endif

        @include('admin.dashboard._system')
    </div>

    @include('admin.dashboard._script')
</x-app-admin-layout>
