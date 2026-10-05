{{--
    Where the setup guide meets a page (App\Utils\SetupGuide decides whether and in which shape).

    $place says who is asking: the admin layout ('layout': the docked card, the pill, the ring),
    the dashboard ('section'), the Schedule tab ('panel') or the import panel ('strip'). A page
    gets at most one shape, so at most one of those includes prints anything.

    THE DASHBOARD'S CARD IS ALSO WHERE THE SUGGESTIONS LIVE. $rows is the dashboard's next steps
    (HomeController::getNextStepItems(), through SetupGuide::withoutCoveredSteps()). With a guide
    they join its card; with none they ARE the card ('steps'), in the guide's look. There is no
    longer a separate "Next steps" panel beside it.

    What is printed here is the STILL version: a plain link to the dashboard's guide, already the
    size and place of the real thing, and for the suggestions the panel they always had, with its
    working forms. The component (resources/js/components/SetupGuide.vue or SetupGuideList.vue,
    loaded lazily by app.js) replaces the host's children when it arrives, so nothing on the page
    moves, and without script, on a stale build or after a failed chunk nothing is missing.

    The hosts carry v-pre: they hold schedule and event names now. The component is compiled and
    discards this markup rather than compiling it, and the attribute keeps that true if anything
    else ever mounts around it.

    Never include this from layouts/app.blade.php: that shell is also the guest portal.
    No named form inputs in here but the still panel's own: FirstScheduleFormTest reads every
    input on the /new pages, where this prints no panel.
--}}
@php
    // The shape first: on most pages it is "none" by route alone, and the guide is never resolved.
    $sgSurface = \App\Utils\SetupGuide::surface();
    $sgState = $sgSurface === 'none' ? null : \App\Utils\SetupGuide::state();
    $sgPlaces = [
        'layout' => ['dock', 'pill', 'ring'],
        'section' => ['section'],
        'panel' => ['panel'],
        'strip' => ['strip'],
    ];
    $sgShow = $sgState && empty($sgState['hidden'])
        && in_array($sgSurface, $sgPlaces[$place ?? 'layout'] ?? [], true);

    // The dashboard's suggestions, as rows. With a guide showing they split into its own
    // schedule (one plain line inside the guide) and the others (a band under it).
    $sgRows = ($place ?? 'layout') === 'section' ? collect($rows ?? []) : collect();
    $sgList = ! $sgShow && $sgRows->isNotEmpty();
    $sgSuggest = null;

    if ($sgShow) {
        $sgPayload = \App\Utils\SetupGuide::payload($sgState, $sgSurface);
        $sgWords = $sgPayload['t'];
        $sgLive = $sgState['live'];
        $sgRemaining = ['', 'one_to_go', 'two_to_go', 'three_to_go'][$sgState['remaining']] ?? 'three_to_go';
        $sgStatus = ! $sgLive
            ? $sgWords['one_step']
            : ($sgState['remaining'] > 0 ? $sgWords[$sgRemaining] : $sgWords['title']);
        $sgSegments = $sgLive ? 3 + count($sgState['steps']) - 1 : 3;
        $sgFilled = $sgLive
            ? 3 + collect($sgState['steps'])->except('event')->filter(fn ($step) => $step['done'] || $step['skipped'])->count()
            : 2;
        $sgHome = $sgState['urls']['dashboard'];

        if ($sgSurface === 'section') {
            $sgSuggest = \App\Utils\SetupGuide::suggestions($sgRows, $sgState, (bool) ($dismissedBefore ?? false));
            $sgPayload['suggestions'] = $sgSuggest;
        }
    } elseif ($sgList) {
        $sgPayload = \App\Utils\SetupGuide::listPayload($sgRows, (bool) ($dismissedBefore ?? false));
        $sgSuggest = $sgPayload['suggestions'];
    }

    // The still panel is handed the rows its own "Dismiss all" will write, and no others:
    // under a showing guide that is the OTHER schedules, because the guide's own schedule is
    // the guide's (HomeController::dismissAllNextSteps()).
    $sgOwnKeys = collect($sgSuggest['own'] ?? [])->pluck('key')->flip();
    $sgPanelRows = $sgRows->reject(fn ($item) => isset($sgOwnKeys[($item['dismiss_schedule'] ?? '').':'.$item['type']]))->values();
    $sgOwnRows = $sgRows->filter(fn ($item) => isset($sgOwnKeys[($item['dismiss_schedule'] ?? '').':'.$item['type']]))->values();

    // THE HEIGHT THE MOUNTED CARD WILL HAVE, reserved so the page under it does not jump when the
    // component takes over. The list is exact: 64px a row plus its heading and foot. The guide
    // is as close as the server can say: its height follows which step is open (measured in
    // English at 390, 1024 and 1440; a longer language wraps and is a little taller), plus
    // 48px for each line about its own schedule and the band's rows. The component drops the
    // reservation as it mounts, so the card is never left taller than what it holds. Kept in
    // step with the styles in SetupGuide.vue and SetupGuideSuggestions.vue.
    $sgRowsBlock = fn (int $count, int $limit, bool $foot) => min($count, $limit) * 64 + ($count > $limit ? 28 : 0) + ($foot ? 30 : 0);
    $sgOthers = $sgPanelRows->count();
    $sgListHeight = 62 + $sgRowsBlock($sgOthers, 8, $sgOthers > 1 || ! empty($dismissedBefore));
    $sgHeights = [0, 0, 0];

    if ($sgShow && $sgSurface === 'section') {
        $sgOwn = $sgOwnRows->count();
        $sgBand = $sgOthers ? 38 + $sgRowsBlock($sgOthers, 3, $sgOthers > 1) : 0;

        if ($sgState['finished']) {
            // A finished guide is as tall as what it says; below 1024px that is not reserved.
            $sgHeights = [0, 244 + $sgOwn * 48 + $sgBand, 244 + $sgOwn * 48 + $sgBand];
        } elseif ($sgState['quiet']) {
            // One list: the guide's line, then every row, its own schedule's included.
            $sgAll = $sgOwn + $sgOthers;
            $sgHeights = array_fill(0, 3, 64 + ($sgAll ? $sgRowsBlock($sgAll, 3, $sgAll > 1) + 12 : 0));
        } else {
            $sgOpen = [
                'event' => [520, 412, 393],
                'events' => [529, 480, 437],
                'share' => [599, 550, 514],
                'tickets' => [533, 465, 465],
            ][$sgState['current'] ?? 'events'] ?? [529, 480, 437];

            $sgHeights = array_map(fn ($height) => $height + $sgOwn * 48 + $sgBand, $sgOpen);
        }
    }
@endphp
@if ($sgShow || $sgList)
@once
@if ($sgList)
{{-- No guide to show: the suggestions are the card. Same id as the guide's section, since it
     takes the same place on the page and the component finds its host by it. --}}
<section id="setup-guide" data-setup-guide="steps" aria-label="{{ __('messages.next_steps') }}" v-pre
    class="scroll-mt-20 print:hidden" style="min-height: {{ $sgListHeight }}px">
    <x-needs-attention :items="$sgRows" :title="__('messages.next_steps')"
        badge-tone="muted"
        :dismiss-route="route('home.next_steps_dismiss')"
        :dismiss-all-route="route('home.next_steps_dismiss_all')" />
</section>
@elseif ($sgSurface === 'section')
<section id="setup-guide" data-setup-guide="section" aria-label="{{ $sgWords['title'] }}" v-pre
    style="--sg-h: {{ $sgHeights[0] }}px; --sg-h-lg: {{ $sgHeights[1] }}px; --sg-h-xl: {{ $sgHeights[2] }}px"
    class="scroll-mt-20 space-y-4 print:hidden min-h-[var(--sg-h)] lg:min-h-[var(--sg-h-lg)] xl:min-h-[var(--sg-h-xl)]">
    {{-- The quiet guide is one 64px line, so its still version is the slimmer of the two. --}}
    <a href="{{ $sgHome }}" class="ap-card flex items-center gap-3 rounded-2xl no-underline {{ $sgState['quiet'] && ! $sgState['finished'] ? 'px-5 py-2.5' : 'p-6' }}">
        @include('partials.setup-guide-ring', ['size' => 40, 'total' => $sgSegments, 'done' => $sgFilled])
        <span class="min-w-0">
            <span class="block text-base font-semibold text-gray-900 dark:text-gray-100">{{ $sgWords['title'] }}</span>
            <span class="block text-sm text-gray-500 dark:text-gray-400">{{ $sgStatus }}</span>
        </span>
    </a>
    {{-- The guide's own schedule: a plain link each, as the mounted guide draws it. --}}
    @foreach ($sgOwnRows as $sgOwnRow)
    <p class="px-6 text-sm"><x-link href="{{ $sgOwnRow['url'] }}">{{ $sgOwnRow['title'] }}</x-link></p>
    @endforeach
    @if ($sgPanelRows->isNotEmpty())
    <x-needs-attention :items="$sgPanelRows" :title="__('messages.next_steps')"
        badge-tone="muted"
        :dismiss-route="route('home.next_steps_dismiss')"
        :dismiss-all-route="route('home.next_steps_dismiss_all')" />
    @endif
</section>
@elseif ($sgSurface === 'panel')
<div class="pb-4 print:hidden">
    <div data-setup-guide="panel" class="min-h-[15rem]">
        <a href="{{ $sgHome }}" class="ap-card flex items-center gap-3 rounded-xl p-6 no-underline">
            @include('partials.setup-guide-ring', ['size' => 40, 'total' => $sgSegments, 'done' => $sgFilled])
            <span class="min-w-0 text-base font-semibold text-gray-900 dark:text-gray-100">{{ $sgWords['your_page'] }}</span>
        </a>
    </div>
</div>
@elseif ($sgSurface === 'strip')
<div data-setup-guide="strip" class="mt-4 min-h-[7.5rem] print:hidden"></div>
@else
<div data-setup-guide="{{ $sgSurface }}" class="print:hidden">
    @if ($sgSurface === 'dock')
    {{-- Where the gutter beside the form is too narrow for the docked card: one line above the
         title. From 1408px the card takes over and this is not shown. --}}
    <a href="{{ $sgHome }}" class="mb-4 inline-flex h-7 items-center gap-2 text-sm font-semibold text-[var(--brand-blue)] no-underline min-[1408px]:hidden">
        @include('partials.setup-guide-ring', ['size' => 20, 'total' => 3, 'done' => 2])
        {{ $sgStatus }}
    </a>
    @elseif (! empty($sgPayload['chip']))
    {{-- Below 1280px on the Schedule tab: a slim chip above the title. --}}
    <a href="{{ $sgHome }}" class="ap-card mb-4 inline-flex h-10 max-w-full items-center gap-2 rounded-xl px-3 text-sm font-semibold text-gray-900 no-underline dark:text-gray-100 xl:hidden">
        @include('partials.setup-guide-ring', ['size' => 24, 'total' => $sgSegments, 'done' => $sgFilled])
        <span class="truncate">{{ $sgStatus }}</span>
    </a>
    @endif
    @if ($sgSurface !== 'dock')
    {{-- 1280px and wider: the corner ring. Positioned by this wrapper, never by an ap-card: that
         class sets position: relative, which beats `fixed`. --}}
    <div class="fixed bottom-4 end-4 z-[45] hidden xl:block">
        <a href="{{ $sgHome }}" aria-label="{{ $sgWords['title'] }}"
            class="ap-card flex h-12 w-12 items-center justify-center rounded-full no-underline">
            @include('partials.setup-guide-ring', ['size' => 36, 'total' => $sgSegments, 'done' => $sgFilled])
        </a>
    </div>
    @endif
</div>
@endif
{{-- One variable in, so the json directive's own splitting on commas cannot bite. Its default flags escape
     < > & and quotes, which is what keeps a schedule named </script> inside the block. --}}
<script type="application/json" data-setup-guide-json>@json($sgPayload)</script>
@endonce
@endif
