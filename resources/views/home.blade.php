<x-app-admin-layout>
    {{-- /dashboard. What it shows above the calendar is decided by who is looking, and built by
         App\Services\HomeDashboard, one method to a card:

           organizer   the four tiles, their schedules, what is coming up, what just happened
           (fresh)     ... minus the tiles while there is nothing to count: the first event, and
                       one card waiting for the first visitor
           viewer      the schedules they were given a look at, and what is coming up on them
           attendee    their tickets and what the schedules they follow have on
           empty       an invitation to make a schedule

         The page is server-rendered. Three small scripts ride on it: the shared popup menus of
         layouts/app, the Realtime tile's refresh (home/_live-script) and the Customize dialog,
         which is its own Vue island (home/_customize). The calendar below is its own Vue root and
         none of this wraps it.

         space-y-4 rather than an mb-4 on each block: one rhythm, as on admin/dashboard. --}}
    @php
        $state = $dashboard['state'];
        $organizer = $state === 'organizer';
        $fresh = $organizer && ! empty($dashboard['fresh']);
        $period = $dashboard['period'];
        $shown = collect($dashboardConfig['panels'])->where('visible', true)->pluck('id')->flip();
        $several = $organizer && ! empty($dashboard['schedules']);
    @endphp

    <div class="space-y-4">
        {{-- Title and actions. Deliberately the FIRST thing in the template: the blocks below open
             with an h2, so an h1 placed after them would put a section heading ahead of the page's
             own title. --}}
        @include('home._header')

        {{-- The setup guide's home (partials/setup-guide): its steps, the open one, and a picture
             of the person's own page. Under the title, unless something is owed: then the
             "Needs attention" queue keeps first place and the guide follows it, below. --}}
        @if ($pendingActionItems->isEmpty())
            @include('partials.setup-guide', ['place' => 'section', 'rows' => $nextStepItems, 'dismissedBefore' => $nextStepsDismissedBefore])
        @endif

        {{-- Nobody's schedule, nobody's ticket, nobody followed: the invitation to start. --}}
        @if ($state === 'empty' && ! is_demo_mode())
        <div>
            <div class="text-center mb-6">
                <h2 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                    {{ __('messages.getting_started_welcome', ['name' => auth()->user()->firstName()]) }}
                </h2>
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.create_your_first_schedule') }}</p>
            </div>

            @include('partials.schedule-type-cards', ['cardHeading' => 'h3'])
        </div>
        @endif

        @if(!empty($showFederationPrompt))
            {{-- No bottom padding of its own here: space-y-4 already spaces it. --}}
            @include('partials.federation-prompt', ['padded' => false])
        @endif

        {{-- Needs attention: what is owed, across every schedule. A row of chips, each as wide as
             its own words, not a card across the page holding two short lines. In every state:
             a schedule offered to someone who runs none arrives here. With several schedules a
             chip says whose it is. --}}
        @if ($pendingActionItems->isNotEmpty())
            <x-needs-attention :items="$pendingActionItems" layout="chips" :subtitles="$several" />

            {{-- Something is owed: the queue keeps first place and the card follows it. --}}
            @include('partials.setup-guide', ['place' => 'section', 'rows' => $nextStepItems, 'dismissedBefore' => $nextStepsDismissedBefore])
        @endif

        {{-- Below the task list on purpose: listing on the network is a suggestion, and the queue
             above is things that are owed. --}}
        @if (! empty($federationListingSchedules) && $federationListingSchedules->isNotEmpty())
            @include('partials.federation-listing-prompt', ['listingSchedules' => $federationListingSchedules, 'padded' => false])
        @endif

        @if ($organizer)
            @if ($fresh)
                {{-- Nothing to count yet, so no row of four zeros: the event they made, beside the
                     one thing worth waiting for. The two cards stretch to one height, so nothing
                     on the page moves when the first visitor comes. --}}
                {{-- One column where there is no live view (every selfhost by default): a card
                     must not sit beside a hole. --}}
                <div class="grid grid-cols-1 {{ $dashboard['live'] !== null ? 'lg:grid-cols-2' : '' }} gap-4">
                    @include('home._coming-up', ['coming' => $dashboard['coming'], 'numbers' => false, 'stretch' => $dashboard['live'] !== null])
                    @if ($dashboard['live'] !== null)
                        @include('home._first-visitor', ['live' => $dashboard['live']])
                    @endif
                </div>
            @else
                @include('home._tiles')

                @if ($several)
                    @include('home._schedules', ['rows' => $dashboard['schedules'], 'viewer' => false])
                @endif

                {{-- Two to a row when both are on the page; one alone takes the row. --}}
                @php
                    $showComing = $shown->has('upcoming_events') && $dashboard['coming'] !== null;
                    $showActivity = $shown->has('recent_activity') && $dashboard['activity'] !== null;
                @endphp
                @if ($showComing || $showActivity)
                <div class="grid grid-cols-1 {{ $showComing && $showActivity ? 'lg:grid-cols-2' : '' }} gap-4">
                    @if ($showComing)
                        @include('home._coming-up', ['coming' => $dashboard['coming'], 'numbers' => true, 'stretch' => false])
                    @endif
                    @if ($showActivity)
                        @include('home._activity', ['activity' => $dashboard['activity']])
                    @endif
                </div>
                @endif

                {{-- The cards someone switched on in Customize, two to a row. --}}
                @php $extras = collect(['top_events', 'traffic_sources', 'newsletters', 'boosts'])->filter(fn ($id) => $shown->has($id)); @endphp
                @if ($extras->isNotEmpty())
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    @foreach ($extras as $panel)
                        {{-- An odd one out at the end takes the row. --}}
                        <div class="{{ $loop->last && $loop->odd ? 'lg:col-span-2' : '' }}">@include('home.panels.'.$panel)</div>
                    @endforeach
                </div>
                @endif
            @endif
        @elseif ($state === 'viewer')
            @include('home._schedules', ['rows' => $dashboard['schedules'], 'viewer' => true])
            @if ($dashboard['coming'] !== null && $dashboard['coming']['rows'])
                @include('home._coming-up', ['coming' => $dashboard['coming'], 'numbers' => false, 'stretch' => false])
            @endif
        @elseif ($state === 'attendee')
            @include('home._attendee')
        @endif

        {{-- The month calendar: the same component as ever, its own Vue root. Someone who runs
             nothing gets it only when it would have something on it (it also lists events they
             submitted to somebody else's schedule). --}}
        @if ($showCalendar)
        <div id="dashboard-calendar" class="scroll-mt-6">
            @include('role/partials/calendar', ['route' => 'home', 'tab' => ''])
        </div>
        @endif
    </div>

    {{-- Outside the space-y-4 wrapper on purpose: the dialog is `fixed inset-0`, and a margin-top
         on a fixed box with top:0/bottom:0 shifts it down and shortens it. --}}
    @if ($organizer)
        @include('home._customize')
        @if (! empty($dashboard['live']))
            @include('home._live-script')
        @endif
    @endif
</x-app-admin-layout>
