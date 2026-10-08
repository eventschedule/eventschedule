@php $isViewer = auth()->user()->isViewer($role->subdomain); @endphp
<x-app-admin-layout>

    <x-slot name="head">
        @if ($tab == 'availability')
        {{-- The grid's look is the month kit's (partials/month-kit-styles, .gk-cal-pick and .day-x).
             The grid makes a day pressable on the condition this script is given on ($pickMarks in
             role/partials/calendar): change one and change the other. --}}
        @if(!$isViewer)
        <script {!! nonce_attr() !!}>
        // Plain script, and listening on the document: the grid sits inside the calendar's Vue
        // mount, which re-creates its elements, and a listener put on a day would be left on the
        // one Vue threw away.
        (function () {
            const availableDays = new Set();
            const unavailableDays = new Set(@json($datesUnavailable));
            const unavailableLabel = @json(__('messages.unavailable'));

            // Marks a day, or clears it. The state is said on the day itself (aria-pressed), which
            // is a button to the keyboard and to a screen reader.
            function toggleDay(dayEl) {
                const day = dayEl.getAttribute('data-date');
                const mark = dayEl.querySelector('.day-x');

                if (unavailableDays.has(day)) {
                    unavailableDays.delete(day);
                    availableDays.add(day);
                    if (mark) mark.remove();
                } else {
                    unavailableDays.add(day);
                    availableDays.delete(day);
                    if (! mark) {
                        const added = document.createElement('div');
                        added.className = 'day-x';
                        added.setAttribute('data-label', unavailableLabel);
                        dayEl.appendChild(added);
                    }
                }
                dayEl.setAttribute('aria-pressed', unavailableDays.has(day) ? 'true' : 'false');

                const saveButton = document.getElementById('saveButton');
                if (saveButton) saveButton.disabled = false;
            }

            // Enter or Space presses the day the focus is on; the arrows move between days.
            document.addEventListener('keydown', function (e) {
                const dayEl = e.target.closest ? e.target.closest('.day-element') : null;
                if (! dayEl || e.target !== dayEl) return;

                if (e.key === 'Enter' || e.key === ' ') {
                    e.preventDefault();
                    toggleDay(dayEl);
                    return;
                }

                const rtl = getComputedStyle(dayEl).direction === 'rtl';
                const step = { ArrowLeft: rtl ? 1 : -1, ArrowRight: rtl ? -1 : 1, ArrowUp: -7, ArrowDown: 7 }[e.key];
                if (! step) return;
                const days = Array.prototype.slice.call(document.querySelectorAll('.day-element'));
                const next = days[days.indexOf(dayEl) + step];
                if (next) {
                    e.preventDefault();
                    next.focus();
                }
            });

            document.addEventListener('click', function (e) {
                if (! e.target.closest) return;

                const dayEl = e.target.closest('.day-element');
                if (dayEl) {
                    toggleDay(dayEl);
                    return;
                }

                if (e.target.closest('#saveButton')) {
                    @if (!$role->isEnterprise() && config('app.hosted'))
                        window.dispatchEvent(new CustomEvent('open-modal', { detail: 'upgrade-availability' }));
                    @else
                        document.getElementById('unavailable_days').value = JSON.stringify(Array.from(unavailableDays));
                        document.getElementById('available_days').value = JSON.stringify(Array.from(availableDays));
                        document.getElementById('availability_form').submit();
                    @endif
                }
            });
        })();
        </script>
        @endif
        @endif

    </x-slot>

    @php
        $viewGuestParams = ['subdomain' => $role->subdomain];
        if ($role->eventLayout() == 'calendar') {
            if (now()->year != $year || now()->month != $month) {
                $viewGuestParams['month'] = $month;
            }
            if (now()->year != $year) {
                $viewGuestParams['year'] = $year;
            }
        }
        $viewGuestUrl = route('role.view_guest', $viewGuestParams);

        // The public address, said the way the schedule form says it (role/edit). getGuestUrl(true)
        // names the custom domain only when it answers: a direct-mode domain that is still pending
        // or has failed does not route to the schedule (ResolveCustomDomain), and this is the
        // address people copy and hand out.
        $scheduleUrl = $role->getGuestUrl(true);
        // Split by hand: parse_url() mangles a non-ASCII host on macOS.
        $scheduleLinkText = $scheduleUrl ? \App\Utils\UrlUtils::clean($scheduleUrl) : '';
        $scheduleLinkSlash = strpos($scheduleLinkText, '/');
        // An address with no path (a subdomain on hosted, a custom domain) is all host, and the kit
        // hides the host on a phone to leave room for the path: there the whole address is the part
        // to keep, or a phone shows "Copy  View" beside nothing.
        $scheduleLinkHost = $scheduleLinkSlash === false ? '' : substr($scheduleLinkText, 0, $scheduleLinkSlash);
        $scheduleLinkPath = $scheduleLinkSlash === false ? $scheduleLinkText : substr($scheduleLinkText, $scheduleLinkSlash);

        // The tabs, once, for the strip. 'count' is how many; 'waiting' marks a count of things
        // that want an answer (requests, bookings to confirm), which is the only loud one.
        $monthParams = (now()->year == $year && now()->month == $month) ? [] : ((now()->year == $year) ? ['month' => $month] : ['year' => $year, 'month' => $month]);
        $isEditorHere = auth()->user()->isEditor($role->subdomain);
        $adminTabs = array_filter([
            'schedule' => ['label' => __('messages.schedule'), 'params' => $tab == 'schedule' ? [] : $monthParams],
            'templates' => $isEditorHere ? ['label' => __('messages.templates')] : null,
            'videos' => $role->isCurator() ? ['label' => __('messages.videos')] : null,
            'availability' => $role->isTalent() ? ['label' => __('messages.availability'), 'params' => $tab == 'availability' ? [] : $monthParams] : null,
            'appointments' => ['label' => __('messages.appointments'), 'count' => $pendingBookingCount, 'waiting' => true],
            'seating' => $role->isVenue() && $isEditorHere ? ['label' => __('messages.seating_plans')] : null,
            'requests' => count($requests) ? ['label' => __('messages.requests'), 'count' => count($requests), 'waiting' => true] : null,
            // Shown once the schedule has a feed, and while the page itself is open (which is how
            // the first one is added). Its count is what waits for somebody: drafts and decisions.
            'feeds' => $isEditorHere && (($feedsCount ?? 0) > 0 || $tab == 'feeds') ? ['label' => __('messages.feeds_tab'), 'count' => $feedsWaiting ?? 0, 'waiting' => true] : null,
            'followers' => (config('app.hosted') || config('app.is_testing') || $subscribersCount) ? ['label' => __('messages.followers'), 'count' => count($followers) + $subscribersCount] : null,
            'team' => ['label' => __('messages.team'), 'count' => count($members) > 1 ? count($members) : 0],
            'plan' => config('app.hosted') ? ['label' => __('messages.plan')] : null,
        ]);
    @endphp

    <div>
        <!--
        <div>
            <nav class="sm:hidden" aria-label="Back">
                <a href="#" class="flex items-center text-sm font-medium text-gray-500 hover:text-gray-700">
                    <svg class="-ml-1 mr-1 h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path fill-rule="evenodd"
                            d="M12.79 5.23a.75.75 0 01-.02 1.06L8.832 10l3.938 3.71a.75.75 0 11-1.04 1.08l-4.5-4.25a.75.75 0 010-1.08l4.5-4.25a.75.75 0 011.06.02z"
                            clip-rule="evenodd" />
                    </svg>
                    {{ __('messages.back') }}
                </a>
            </nav>
            <nav class="hidden sm:flex" aria-label="Breadcrumb">
                <ol role="list" class="flex items-center space-x-4">
                    <li>
                        <div class="flex">
                            <a href="#"
                                class="mr-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ __('messages.' . strtolower($role->type)) }}</a>
                        </div>
                    </li>
                    <li>
                        <div class="flex items-center">
                            <svg class="h-5 w-5 flex-shrink-0 text-gray-400" viewBox="0 0 24 24" fill="currentColor"
                                aria-hidden="true">
                                <path fill-rule="evenodd"
                                    d="M7.21 14.77a.75.75 0 01.02-1.06L11.168 10 7.23 6.29a.75.75 0 111.04-1.08l4.5 4.25a.75.75 0 010 1.08l-4.5 4.25a.75.75 0 01-1.06-.02z"
                                    clip-rule="evenodd" />
                            </svg>
                            <a href="#" aria-current="page"
                                class="mr-4 text-sm font-medium text-gray-500 hover:text-gray-700">{{ $role->name }}</a>
                        </div>
                    </li>
                </ol>
            </nav>
        </div>
        -->
        <div class="flex items-start justify-between">
            @if ($role->profile_image_url)
                <div class="pe-4">
                    <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                </div>
            @endif
            <div class="min-w-0 flex-1">
                {{-- h1, not h2: this is the page title for every admin tab. The Schedule and
                     Availability tabs have no other heading of their own, so with the calendar
                     partial's month title demoted to h2 they would otherwise have no h1 at all. --}}
                <h1 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ $role->name }}</h1>

                @if ($scheduleUrl)
                {{-- What you share. It was nowhere on these pages: only a button that opened it. --}}
                <div class="event-url-strip">
                    <span class="event-url-text" dir="ltr">@if ($scheduleLinkHost !== '')<span class="event-url-host">{{ $scheduleLinkHost }}</span>@endif<span class="event-url-path">{{ $scheduleLinkPath }}</span></span>
                    {{-- data-setup-share: copying the schedule's own address is the setup guide's "share" step. --}}
                    <button type="button" class="event-link" id="copy-schedule-link-btn" data-setup-share="link" data-copy-text="{{ $scheduleUrl }}" data-copied="{{ __('messages.copied') }}">{{ __('messages.copy') }}</button>
                    @if ($role->email_verified_at)
                    <a href="{{ $viewGuestUrl }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view') }}</a>
                    @endif
                </div>
                @endif

                <div class="mt-1 flex flex-col sm:mt-0 sm:flex-row sm:flex-wrap sm:gap-x-6">
                    {{-- Whether this schedule is on the Event Schedule network. Only once the install
                         has joined one, and only for an explicit yes - undecided is the default and
                         says nothing. Links editors straight to the setting (Settings, Advanced). --}}
                    @if (! config('app.is_nexus') && \App\Models\Setting::get('federation_enabled') && $role->federation_enabled === true)
                    <div class="mt-2 flex items-center text-sm text-gray-500 dark:text-gray-400">
                        <svg class="me-1.5 h-5 w-5 flex-shrink-0 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9" />
                        </svg>
                        <div class="mt-1">
                            @if (auth()->user()->isEditor($role->subdomain))
                                <a href="{{ route('role.edit', ['subdomain' => $role->subdomain, 'settings_tab' => 'advanced', 'focus' => 'federation_enabled']) }}#section-settings" class="hover:underline">
                                    @lang('messages.federation_listed_on')
                                </a>
                            @else
                                @lang('messages.federation_listed_on')
                            @endif
                        </div>
                    </div>
                    @endif

                </div>
            </div>

            {{-- Desktop buttons (hidden on mobile) --}}
            <div class="mt-2 hidden md:flex flex-col space-y-2 sm:flex-row sm:space-y-0 sm:gap-x-3 flex-shrink-0 md:ms-4">
                @if (!$isViewer)
                <x-secondary-link href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}" class="w-full sm:w-auto">
                    <svg class="-ms-0.5 me-2 h-6 w-6 text-gray-400 dark:text-gray-500" viewBox="0 0 24 24" fill="currentColor"
                        aria-hidden="true">
                        <path
                            d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" />
                    </svg>
                    {{ __('messages.edit_schedule') }}
                </x-secondary-link>
                @endif
            </div>

            {{-- Actions dropdown (always visible) --}}
            <div class="mt-2 md:ms-3">
                <div class="relative inline-block text-start w-full">
                    <button type="button" data-popup-target="role-actions-pop-up-menu" class="popup-toggle inline-flex w-full justify-center rounded-lg bg-white dark:bg-gray-800 px-4 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm border border-gray-300 dark:border-gray-600 hover:bg-gray-50 dark:hover:bg-gray-700 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800" id="role-actions-menu-button" aria-expanded="false" aria-haspopup="true">
                        {{ __('messages.actions') }}
                        <svg class="-me-1 ms-2 h-6 w-6 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                        </svg>
                    </button>
                    <div id="role-actions-pop-up-menu" class="ap-dropdown pop-up-menu hidden absolute end-0 z-10 mt-2 w-64 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} divide-y divide-gray-100 dark:divide-white/[0.06] rounded-lg ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none" role="menu" aria-orientation="vertical" aria-labelledby="role-actions-menu-button" tabindex="-1">
                        <div class="py-2" role="none" data-popup-target="role-actions-pop-up-menu">
                            {{-- Show edit/view options only when desktop buttons are hidden (mobile) --}}
                            <div class="md:hidden">
                                @if (!$isViewer)
                                <a href="{{ route('role.edit', ['subdomain' => $role->subdomain]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                    <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M2.695 14.763l-1.262 3.154a.5.5 0 00.65.65l3.155-1.262a4 4 0 001.343-.885L17.5 5.5a2.121 2.121 0 00-3-3L3.58 13.42a4 4 0 00-.885 1.343z" />
                                    </svg>
                                    <div>
                                        {{ __('messages.edit_schedule') }}
                                    </div>
                                </a>
                                @endif
                                <a href="{{ $viewGuestUrl }}" target="_blank" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                    <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M14,3V5H17.59L7.76,14.83L9.17,16.24L19,6.41V10H21V3M19,19H5V5H12V3H5C3.89,3 3,3.9 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19V12H19V19Z" />
                                    </svg>
                                    <div>
                                        {{ __('messages.view_schedule') }}
                                    </div>
                                </a>
                            </div>
                            @if (!$isViewer)
                            <a href="{{ route('event.show_import_ai', ['subdomain' => $role->subdomain]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M14,12L10,8V11H2V13H10V16M20,18V6C20,4.89 19.1,4 18,4H6A2,2 0 0,0 4,6V9H6V6H18V18H6V15H4V18A2,2 0 0,0 6,20H18A2,2 0 0,0 20,18Z" />
                                </svg>
                                <div>
                                    {{ __('messages.import_events') }}
                                </div>
                            </a>
                            @if (config('services.google.gemini_key') || config('services.openai.api_key'))
                            @if ($role->isEnterprise())
                            <a href="{{ route('event.scan_agenda', ['subdomain' => $role->subdomain]) }}" class="lg:hidden group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M4,4H7L9,2H15L17,4H20A2,2 0 0,1 22,6V18A2,2 0 0,1 20,20H4A2,2 0 0,1 2,18V6A2,2 0 0,1 4,4M12,7A5,5 0 0,0 7,12A5,5 0 0,0 12,17A5,5 0 0,0 17,12A5,5 0 0,0 12,7M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9Z" />
                                </svg>
                                <div>
                                    {{ __('messages.scan_agenda') }}
                                </div>
                            </a>
                            @elseif (config('app.hosted'))
                            <button type="button" data-modal-open="upgrade-scan-agenda" class="lg:hidden w-full group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M4,4H7L9,2H15L17,4H20A2,2 0 0,1 22,6V18A2,2 0 0,1 20,20H4A2,2 0 0,1 2,18V6A2,2 0 0,1 4,4M12,7A5,5 0 0,0 7,12A5,5 0 0,0 12,17A5,5 0 0,0 17,12A5,5 0 0,0 12,7M12,9A3,3 0 0,1 15,12A3,3 0 0,1 12,15A3,3 0 0,1 9,12A3,3 0 0,1 12,9Z" />
                                </svg>
                                <div>
                                    {{ __('messages.scan_agenda') }}
                                </div>
                            </button>
                            @endif
                            @endif
                            {{-- Owner only, like the endpoint it posts to (GoogleCalendarController::sync()). --}}
                            @if (auth()->id() === $role->user_id && auth()->user()->google_token && $role->hasGoogleCalendarIntegration())
                            <a href="#" id="sync-events-link" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12,18A6,6 0 0,1 6,12C6,11 6.25,10.03 6.7,9.2L5.24,7.74C4.46,8.97 4,10.43 4,12A8,8 0 0,0 12,20C13.57,20 15.03,19.54 16.26,18.76L14.8,17.3C13.97,17.75 13,18 12,18M20,12A8,8 0 0,0 12,4C10.43,4 8.97,4.46 7.74,5.24L9.2,6.7C10.03,6.25 11,6 12,6A6,6 0 0,1 18,12C18,13 17.75,13.97 17.3,14.8L18.76,16.26C19.54,15.03 20,13.57 20,12M14.8,17.3L16.26,18.76L18.76,16.26L17.3,14.8L14.8,17.3M9.2,6.7L7.74,5.24L5.24,7.74L6.7,9.2L9.2,6.7M12,8A4,4 0 0,0 8,12A4,4 0 0,0 12,16A4,4 0 0,0 16,12A4,4 0 0,0 12,8Z" />
                                </svg>
                                <div>
                                    {{ __('messages.sync_events') }}
                                </div>
                            </a>
                            @endif
                            @endif
                            <a href="#" id="events-graphic-link" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19M21,19V5C21,3.89 20.1,3 19,3H5A2,2 0 0,0 3,5V19A2,2 0 0,0 5,21H19A2,2 0 0,0 21,19M8.5,13.5L11,16.5L14.5,12L19,18H5L8.5,13.5Z" />
                                </svg>
                                <div>
                                    {{ __('messages.events_graphic') }}
                                </div>
                            </a>
                            <a href="#" id="embed-schedule-link" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                    <path d="M12.89,3L14.85,3.4L11.11,21L9.15,20.6L12.89,3M19.59,12L16,8.41V5.58L22.42,12L16,18.41V15.58L19.59,12M1.58,12L8,5.58V8.41L4.41,12L8,15.58V18.41L1.58,12Z" />
                                </svg>
                                <div>
                                    {{ __('messages.embed_schedule') }}
                                </div>
                            </a>
                            @if (auth()->user()->isEditor($role->subdomain))
                            <a href="{{ route('role.audit_log', ['subdomain' => $role->subdomain]) }}" class="group flex items-center px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:bg-gray-100 dark:focus:bg-gray-700 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                                    <circle cx="12" cy="12" r="10" /><polyline points="12 6 12 12 16 14" />
                                </svg>
                                <div>
                                    {{ __('messages.audit_log') }}
                                </div>
                            </a>
                            @endif
                            @if ($role->exists && $role->user_id == auth()->user()->id && !is_demo_role($role))
                            <div class="py-2" role="none">
                                <div class="border-t border-gray-100 dark:border-gray-700"></div>
                            </div>
                            @php
                                $outstandingGiftCards = $role->giftCards()
                                    ->where('status', 'active')
                                    ->where('remaining_amount', '>', 0)
                                    ->exists();
                                // Deleting cancels a paid plan immediately (RoleController::delete()),
                                // so say so. The gift-card warning already ends in its own question.
                                $deleteWarnings = $role->hasLiveBilling()
                                    ? [__('messages.delete_schedule_subscription_warning')]
                                    : [];
                                $deleteWarnings[] = $outstandingGiftCards
                                    ? __('messages.delete_schedule_gift_cards_warning')
                                    : __('messages.are_you_sure');
                                $deleteConfirmMessage = implode(' ', $deleteWarnings);
                            @endphp
                            <form method="POST" action="{{ route('role.delete', ['subdomain' => $role->subdomain]) }}" data-confirm="{{ $deleteConfirmMessage }}" class="form-confirm block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full group flex items-center px-5 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-900/20 hover:text-red-700 dark:hover:text-red-300 focus:bg-red-50 dark:focus:bg-red-900/20 focus:text-red-700 dark:focus:text-red-300 focus:outline-none transition-colors" role="menuitem" tabindex="0">
                                    <svg class="me-3 h-5 w-5 text-red-400 dark:text-red-500 group-hover:text-red-500 dark:group-hover:text-red-400" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                        <path d="M19,4H15.5L14.5,3H9.5L8.5,4H5V6H19M6,19A2,2 0 0,0 8,21H16A2,2 0 0,0 18,19V7H6V19Z" />
                                    </svg>
                                    <div>
                                        {{ __('messages.delete_schedule') }}
                                    </div>
                                </button>
                            </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @if (! $role->email_verified_at)
    <div class="pt-5 pb-2">
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
            <div class="flex flex-wrap items-center gap-y-2">
                <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 me-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <span class="text-sm text-amber-800 dark:text-amber-200 font-medium">{{ __('messages.verify_email_address') }}</span>
                <a href="{{ route('role.verification.resend', ['subdomain' => $role->subdomain]) }}"
                        class="ms-auto inline-flex items-center gap-1 rounded-lg bg-yellow-100 dark:bg-yellow-800/40 px-4 py-2 text-base font-semibold text-yellow-800 dark:text-yellow-200 ring-1 ring-inset ring-yellow-300 dark:ring-yellow-700 hover:bg-yellow-200 dark:hover:bg-yellow-800/60 transition-colors duration-150"
                        >
                        {{ __('messages.resend_email') }}
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
    @endif

    {{-- One strip at every width. A tab is never cut off without a sign: the strip fades where it
         runs on and brings the tab you are on into view (the script at the foot of this file). --}}
    <div class="ap-tabs-select md:hidden">
        <label for="admin-tab-select" class="sr-only">{{ __('messages.select_a_tab') }}</label>
        <select id="admin-tab-select" autocomplete="off" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
            @if (! isset($adminTabs[$tab]))
            {{-- A page reached by its address that this schedule's tabs do not list (a viewer on
                 Templates): without this the dropdown claimed to be on Schedule, and choosing
                 Schedule did nothing. --}}
            <option value="" selected disabled>{{ __('messages.select_a_tab') }}</option>
            @endif
            @foreach ($adminTabs as $tabKey => $adminTab)
            <option value="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => $tabKey] + ($adminTab['params'] ?? [])) }}" {{ $tab == $tabKey ? 'selected' : '' }}>
                {{ $adminTab['label'] }}{{ ! empty($adminTab['count']) ? ' ('.number_format($adminTab['count']).')' : '' }}
            </option>
            @endforeach
        </select>
    </div>
    <div class="ap-tabs-wrap hidden md:block" id="admin-tabs-wrap">
        <nav class="ap-tabs" id="admin-tabs" aria-label="{{ __('messages.select_a_tab') }}">
            @foreach ($adminTabs as $tabKey => $adminTab)
            <a href="{{ route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => $tabKey] + ($adminTab['params'] ?? [])) }}"
                class="ap-tab" @if ($tab == $tabKey) aria-current="page" @endif>
                {{ $adminTab['label'] }}
                @if (! empty($adminTab['count']))
                <span class="ap-tab-count {{ ! empty($adminTab['waiting']) ? 'is-waiting' : '' }}">{{ number_format($adminTab['count']) }}</span>
                @endif
            </a>
            @endforeach
        </nav>
    </div>

    {{-- Every tab is the layout's frame. The list tabs kept to a 64rem column here while the
         header above them and the calendar tabs ran the full width. --}}
    <div>
    @if ($tab == 'schedule')
    @include('role.show-admin-schedule')
    @elseif ($tab == 'templates')
    @include('role.show-admin-templates')
    @elseif ($tab == 'availability')
    @include('role.show-admin-availability')
    @elseif ($tab == 'appointments')
    @include('role.show-admin-appointments')
    @elseif ($tab == 'seating')
    @include('role.show-admin-seating')
    @elseif ($tab == 'requests')
    @include('role.show-admin-requests')
    @elseif ($tab == 'feeds')
    @include('role.show-admin-feeds')
    @elseif ($tab == 'followers')
    @include('role.show-admin-followers')
    @elseif ($tab == 'team')
    @include('role.show-admin-team')
    @elseif ($tab == 'videos')
    @include('role.show-admin-videos')
    @elseif ($tab == 'plan')
    @include('role.show-admin-plan')
    @endif
    </div>

<script {!! nonce_attr() !!}>
@if ($tab == 'followers' || $tab == 'team')
document.addEventListener('click', function(e) {
    var header = e.target.closest('[data-sort]');
    if (header) {
        var url = new URL(window.location.href);
        var currentSort = url.searchParams.get('sort_by') || '{{ $tab == "followers" ? "pivot_created_at" : "" }}';
        var currentDir = url.searchParams.get('sort_dir') || '{{ $tab == "team" ? "asc" : "desc" }}';
        var sortBy = header.getAttribute('data-sort');
        url.searchParams.set('sort_by', sortBy);
        url.searchParams.set('sort_dir', currentSort === sortBy && currentDir === 'asc' ? 'desc' : 'asc');
        url.searchParams.delete('page');
        window.location.href = url.toString();
    }
});
@endif

function handleEventsGraphicClick() {
    window.location.href = '{{ route("event.generate_graphic", ["subdomain" => $role->subdomain]) }}';
}

function syncEventsFromDropdown() {
    // Check if user has Google token and role has calendar ID
    @if (!auth()->user()->google_token || !$role->hasGoogleCalendarIntegration())
        alert(@json(__("messages.google_calendar_not_connected")));
        return false;
    @endif

    // Show confirmation dialog
    if (!confirm(@json(__("messages.are_you_sure")))) {
        return false;
    }

    // Use the unified sync endpoint
    const syncDirection = '{{ $role->sync_direction }}' || 'to';
    const requestBody = {
        sync_direction: syncDirection
    };

    const btn = document.getElementById('sync-events-link');
    const originalText = btn ? btn.innerHTML : '';
    if (btn) { btn.disabled = true; }

    fetch('{{ url('/google-calendar/sync/' . $role->subdomain) }}', {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            'Content-Type': 'application/json',
        },
        body: JSON.stringify(requestBody),
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert(@json(__("messages.sync_error")) + ': ' + data.error);
        } else {
            alert(data.message);
        }
    })
    .catch(error => {
        alert(@json(__("messages.sync_error")) + ': ' + error.message);
    })
    .finally(() => {
        if (btn) {
            btn.innerHTML = originalText;
            btn.disabled = false;
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    // The tabs: bring the one you are on into view, and say when there are more to either side.
    var tabsWrap = document.getElementById('admin-tabs-wrap');
    var tabs = document.getElementById('admin-tabs');
    if (tabs && tabsWrap) {
        var paintTabs = function() {
            var start = Math.abs(tabs.scrollLeft);
            tabsWrap.classList.toggle('more-before', start > 4);
            tabsWrap.classList.toggle('more-after', start + tabs.clientWidth < tabs.scrollWidth - 4);
        };
        var current = tabs.querySelector('[aria-current="page"]');
        var showCurrent = function() {
            if (current) {
                tabs.scrollLeft = current.offsetLeft - (tabs.clientWidth - current.offsetWidth) / 2;
            }
            paintTabs();
        };
        showCurrent();
        // Again once the fonts are in: the labels change width and the tab moves.
        window.addEventListener('load', showCurrent);
        tabs.addEventListener('scroll', paintTabs, { passive: true });
        window.addEventListener('resize', paintTabs);
        paintTabs();
    }

    var tabSelect = document.getElementById('admin-tab-select');
    if (tabSelect) {
        var tabHere = tabSelect.value;
        tabSelect.addEventListener('change', function() {
            if (tabSelect.value) {
                window.location.href = tabSelect.value;
            }
        });
        // Back brings the page back with the option that was chosen still showing, and choosing
        // it again would fire nothing.
        window.addEventListener('pageshow', function() {
            tabSelect.value = tabHere;
        });
    }

    // The public address under the name.
    var copyLink = document.getElementById('copy-schedule-link-btn');
    if (copyLink) {
        // Read once: read at each press, a second press inside two seconds took "Copied" for the
        // button's own name and left it saying so for good.
        var copyLabel = copyLink.textContent;
        var copyTimer = null;
        var saidCopied = function() {
            copyLink.textContent = copyLink.getAttribute('data-copied');
            clearTimeout(copyTimer);
            copyTimer = setTimeout(function() { copyLink.textContent = copyLabel; }, 2000);
        };
        // Without the clipboard API (an http:// selfhost install) the whole address is copied
        // from a field made for the moment. Selecting the text on the page would copy it without
        // its scheme, and on a phone without its host.
        var copyByHand = function(text) {
            var field = document.createElement('textarea');
            field.value = text;
            field.setAttribute('readonly', '');
            field.style.position = 'fixed';
            field.style.opacity = '0';
            document.body.appendChild(field);
            field.select();
            var done = false;
            try {
                done = document.execCommand('copy');
            } catch (e) {}
            document.body.removeChild(field);
            if (done) {
                saidCopied();
            }
        };
        copyLink.addEventListener('click', function() {
            var text = copyLink.getAttribute('data-copy-text');
            if (! navigator.clipboard) {
                copyByHand(text);
                return;
            }
            navigator.clipboard.writeText(text).then(saidCopied).catch(function() { copyByHand(text); });
        });
    }

    // Sync events link
    var syncLink = document.getElementById('sync-events-link');
    if (syncLink) {
        syncLink.addEventListener('click', function(e) {
            e.preventDefault();
            syncEventsFromDropdown();
        });
    }

    // Events graphic link
    var graphicLink = document.getElementById('events-graphic-link');
    if (graphicLink) {
        graphicLink.addEventListener('click', function(e) {
            e.preventDefault();
            handleEventsGraphicClick();
        });
    }

    // Embed schedule link
    var embedLink = document.getElementById('embed-schedule-link');
    if (embedLink) {
        embedLink.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            hidePopUp();
            // Always the calendar: the dialog otherwise keeps whichever widget was picked last
            // (the Followers tab opens it on the signup form), and a browser restores a select's
            // value across a soft reload, so "Embed Schedule" could open on the wrong thing.
            openEmbedModal('calendar');
        });
    }

    // The Followers tab's "Embed signup form": the same dialog, on its signup-form widget.
    document.addEventListener('click', function (e) {
        var trigger = e.target.closest('.js-open-subscribe-embed');
        if (! trigger || e.metaKey || e.ctrlKey || e.shiftKey || e.button !== 0) {
            return;
        }
        e.preventDefault();
        e.stopPropagation();
        openEmbedModal('subscribe');
    });
});
</script>

@include('components.embed-modal')

@if(config('app.hosted'))
<x-upgrade-modal name="upgrade-scan-agenda" tier="enterprise" :subdomain="$role->subdomain" :learnMoreUrl="marketing_url('/features/ai')">
    {{ __('messages.upgrade_feature_description_scan_agenda') }}
</x-upgrade-modal>

<x-upgrade-modal name="upgrade-availability" tier="enterprise" :subdomain="$role->subdomain" :learnMoreUrl="marketing_url('/features/availability')">
    {{ __('messages.upgrade_feature_description_availability') }}
</x-upgrade-modal>
@endif

</x-app-admin-layout>