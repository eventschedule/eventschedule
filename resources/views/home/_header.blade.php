{{-- The dashboard's title and what can be done from it.

     "Add event" is the primary button, at the end of the group, because adding an event is what an
     organizer comes here to do. With several schedules it opens a list of the ones an event can be
     added to. A second schedule is a rare thing to want, so "New schedule" is a secondary button;
     so is "Customize". "View page" shows with one schedule, where there is one page to mean.

     A phone gets one row: a "more" button holding View page and the rare two, then Add event.
     View page is in the menu there because a third button did not fit beside "Veranstaltung
     hinzufügen" or "Добавить событие": both wrapped to two lines.

     The menus are the shared .popup-toggle[data-popup-target] menus of layouts/app, which also
     places them: from the toggle's end edge, and back inside the screen when that would put them
     past it (the "more" button sits at the START of its row). Deliberately
     NOT role="menu"/role="menuitem": that pattern obliges arrow-key navigation, and the shared
     script only handles Escape, so every item carrying tabindex="-1" would be unreachable by
     keyboard. As plain lists of links, Tab reaches every one. --}}
@php
    // <x-secondary-link>'s own classes, for the two controls that are buttons and cannot be one.
    $secondaryAction = 'ap-secondary-btn inline-flex items-center justify-center gap-1.5 px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800';
    $menu = 'ap-dropdown pop-up-menu hidden absolute z-10 mt-2 w-64 max-w-[calc(100vw-2rem)] divide-y divide-gray-100 dark:divide-white/[0.06] rounded-md ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none';
    $menuItem = 'group flex items-center gap-3 px-4 py-2 text-sm text-gray-700 dark:text-gray-200 hover:bg-gray-50 dark:hover:bg-black/10';
    $caret = '<svg class="h-4 w-4 shrink-0" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>';
    $gear = 'M10.343 3.94c.09-.542.56-.94 1.11-.94h1.093c.55 0 1.02.398 1.11.94l.149.894c.07.424.384.764.78.93.398.164.855.142 1.205-.108l.737-.527a1.125 1.125 0 011.45.12l.773.774c.39.389.44 1.002.12 1.45l-.527.737c-.25.35-.272.806-.107 1.204.165.397.505.71.93.78l.893.15c.543.09.94.56.94 1.109v1.094c0 .55-.397 1.02-.94 1.11l-.893.149c-.425.07-.765.383-.93.78-.165.398-.143.854.107 1.204l.527.738c.32.447.269 1.06-.12 1.45l-.774.773a1.125 1.125 0 01-1.449.12l-.738-.527c-.35-.25-.806-.272-1.203-.107-.397.165-.71.505-.781.929l-.149.894c-.09.542-.56.94-1.11.94h-1.094c-.55 0-1.019-.398-1.11-.94l-.148-.894c-.071-.424-.384-.764-.781-.93-.398-.164-.854-.142-1.204.108l-.738.527c-.447.32-1.06.269-1.45-.12l-.773-.774a1.125 1.125 0 01-.12-1.45l.527-.737c.25-.35.273-.806.108-1.204-.165-.397-.505-.71-.93-.78l-.894-.15c-.542-.09-.94-.56-.94-1.109v-1.094c0-.55.398-1.02.94-1.11l.894-.149c.424-.07.765-.383.93-.78.165-.398.143-.854-.107-1.204l-.527-.738a1.125 1.125 0 01.12-1.45l.773-.773a1.125 1.125 0 011.45-.12l.737.527c.35.25.807.272 1.204.107.397-.165.71-.505.78-.929l.15-.894z M15 12a3 3 0 11-6 0 3 3 0 016 0z';

    $canNewSchedule = ! is_demo_mode() && $canCreateSchedule;
    $targets = $organizer ? $dashboard['addTargets'] : [];
    $page = $organizer ? $dashboard['page'] : null;
    $kinds = [
        'talent' => ['label' => __('messages.talent'), 'help' => __('messages.new_schedule_tooltip'), 'icon' => 'M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z'],
        'venue' => ['label' => __('messages.venue'), 'help' => __('messages.new_venue_tooltip'), 'icon' => 'M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z'],
        'curator' => ['label' => __('messages.curator'), 'help' => __('messages.new_curator_tooltip'), 'icon' => 'M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z'],
    ];
    $hasActions = $page || $organizer || $canNewSchedule || $targets;
@endphp

<div class="flex flex-wrap justify-between items-center gap-3">
    <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">{{ __('messages.dashboard') }}</h1>

    @if ($hasActions)
    {{-- basis-full on a phone: the actions take their own row under the title, with Add event
         pushed to its end. From sm they sit beside the title, at the end of its row. --}}
    <div class="flex items-center gap-3 basis-full sm:basis-auto sm:ms-auto">
        @if ($page)
            <x-secondary-link :href="$page" target="_blank" rel="noopener" class="gap-1.5 !hidden sm:!inline-flex">
                {{ __('messages.dash_view_page') }}
                <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
            </x-secondary-link>
        @endif

        {{-- From sm: Customize (its word from xl, where there is room for it) and New schedule. --}}
        @if ($organizer)
            <button type="button" data-dashboard-customize class="{{ $secondaryAction }} !hidden sm:!inline-flex !px-3 xl:!px-4" aria-label="{{ __('messages.dash_customize_title') }}" title="{{ __('messages.dash_customize_title') }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $gear }}" /></svg>
                <span class="hidden xl:inline">{{ __('messages.customize_dashboard') }}</span>
            </button>
        @endif

        @if ($canNewSchedule)
            <div class="relative hidden sm:inline-block text-start">
                <button type="button" class="popup-toggle {{ $secondaryAction }}" data-popup-target="dashboard-new-schedule-menu" aria-expanded="false">
                    {{ __('messages.new_schedule') }}
                    <span class="text-gray-400">{!! $caret !!}</span>
                </button>
                <div id="dashboard-new-schedule-menu" class="{{ $menu }} end-0 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }}">
                    <div class="py-1" data-popup-target="dashboard-new-schedule-menu">
                        @foreach ($kinds as $type => $kind)
                            <a href="{{ route('new', ['type' => $type]) }}" class="{{ $menuItem }}">
                                <svg class="h-5 w-5 shrink-0 text-gray-400 group-hover:text-gray-500 dark:group-hover:text-gray-300" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $kind['icon'] }}" /></svg>
                                <div>
                                    {{ $kind['label'] }}
                                    <div class="text-xs text-gray-500 dark:text-gray-400">{{ $kind['help'] }}</div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- A phone: View page and the two rare actions behind one button. --}}
        @if ($page || $organizer || $canNewSchedule)
            <div class="relative sm:hidden text-start">
                <button type="button" class="popup-toggle {{ $secondaryAction }} !px-3" data-popup-target="dashboard-more-menu" aria-expanded="false" aria-label="{{ __('messages.dash_more') }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM12.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0zM18.75 12a.75.75 0 11-1.5 0 .75.75 0 011.5 0z" /></svg>
                </button>
                <div id="dashboard-more-menu" class="{{ $menu }}">
                    @if ($page)
                        <div class="py-1">
                            <a href="{{ $page }}" target="_blank" rel="noopener" class="{{ $menuItem }}">
                                <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" /></svg>
                                {{ __('messages.dash_view_page') }}
                            </a>
                        </div>
                    @endif
                    @if ($organizer)
                        <div class="py-1" data-popup-target="dashboard-more-menu">
                            <button type="button" data-dashboard-customize class="{{ $menuItem }} w-full text-start">
                                <svg class="h-5 w-5 shrink-0 text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $gear }}" /></svg>
                                {{ __('messages.dash_customize_title') }}
                            </button>
                        </div>
                    @endif
                    @if ($canNewSchedule)
                        <div class="py-1">
                            <p class="px-4 pt-1 pb-1 text-[11px] font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">{{ __('messages.new_schedule') }}</p>
                            @foreach ($kinds as $type => $kind)
                                <a href="{{ route('new', ['type' => $type]) }}" class="{{ $menuItem }}">
                                    <svg class="h-5 w-5 shrink-0 text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="{{ $kind['icon'] }}" /></svg>
                                    {{ $kind['label'] }}
                                </a>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        @endif

        {{-- Add event. One schedule: straight to its form. Several: choose which. --}}
        @if (count($targets) === 1)
            <x-brand-link :href="$targets[0]['add']" class="gap-1.5 ms-auto sm:ms-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                {{ __('messages.add_event') }}
            </x-brand-link>
        @elseif (count($targets) > 1)
            <div class="relative inline-block text-start ms-auto sm:ms-0">
                <x-brand-button class="popup-toggle gap-1.5" data-popup-target="dashboard-add-event-menu" aria-expanded="false">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
                    {{ __('messages.add_event') }}
                    <span class="text-white/80">{!! $caret !!}</span>
                </x-brand-button>
                <div id="dashboard-add-event-menu" class="{{ $menu }} end-0 max-h-80 overflow-y-auto {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }}">
                    <div class="py-1" data-popup-target="dashboard-add-event-menu">
                        @foreach ($targets as $target)
                            <a href="{{ $target['add'] }}" class="{{ $menuItem }}">
                                @include('home._thumb', ['image' => $target['image'], 'name' => $target['name'], 'thumbSmall' => true])
                                <span class="min-w-0">
                                    <span class="block truncate">{{ $target['name'] }}</span>
                                    <span class="block text-xs text-gray-500 dark:text-gray-400">{{ __('messages.'.$target['type']) }}</span>
                                </span>
                            </a>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    </div>
    @endif
</div>
