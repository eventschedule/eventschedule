@php
    // The same question HomeController::gettingStarted() asks before it bounces someone away from
    // the onboarding chooser. Kept identical on purpose: a page and the page it links to must not
    // disagree about whether this person is new.
    $isFirstRun = ! is_demo_mode()
        && auth()->user()->member()->doesntExist()
        && ! auth()->user()->holdsTickets();

    // Whether saving this form starts a setup guide: a first OWN schedule, outside the guest-submit
    // flow. The same test RoleController::store() pins the guide on, so the line above the heading
    // and the guide that follows cannot disagree. Wider than $isFirstRun on purpose: a ticket
    // holder or a team member making their first own schedule is starting out too.
    $ownsNoSchedule = auth()->user()->owner()->doesntExist();
    $startsGuide = ! is_demo_mode() && ! session('pending_request') && $ownsNoSchedule
        && auth()->user()?->wantsSuggestions();

    // store() sends a first own schedule, and every guest-submit one, on to the event form. The
    // button says so, because nothing else on this page does.
    $leadsToEventForm = session('pending_request') || $ownsNoSchedule;

    // A plan covers one schedule, and nothing here said so: an owner who made a second schedule
    // for an event at another venue was asked to pay again and could not see why. Said only to
    // an owner who already has a plan, and only where plans are sold.
    $paidSchedules = config('app.hosted') && ! is_demo_mode() && ! $ownsNoSchedule
        ? auth()->user()->createdRoles()->where('is_deleted', false)->wherePro()->orderBy('name')->get(['id', 'name', 'type', 'subdomain'])
        : collect();
    // A talent or curator schedule can take an event at any venue; a venue pins its events to itself.
    $paidMovable = $paidSchedules->first(fn ($schedule) => in_array($schedule->type, ['talent', 'curator'], true));
@endphp

    @php
        // The bare shell (a first schedule) has no navigation, so the page draws its own top bar:
        // the logo, and "Step 2 of 2" at the other end (the 2026-11 design; /getting-started is
        // step 1). Inside the portal the sidebar and its own bar are already there.
        $bareShell = ! ($hasSchedules ?? false);
    @endphp
    <div class="px-4 pt-7 pb-12 sm:px-6 lg:px-8">
        @if ($bareShell)
        <header class="mx-auto flex w-full max-w-[57.5rem] flex-wrap items-center justify-between gap-x-4 gap-y-2">
            {{-- Not a link, as on /getting-started: the dashboard forwards a newcomer straight back. --}}
            <div class="flex">
                <img class="h-9 w-auto dark:hidden" src="{{ asset(ltrim(config('app.logo_dark'), '/')) }}" alt="{{ config('app.name') }}"/>
                <img class="h-9 w-auto hidden dark:block" src="{{ asset(ltrim(config('app.logo_light'), '/')) }}" alt="{{ config('app.name') }}"/>
            </div>
            @if ($startsGuide)
                @include('partials.setup-step-bar', ['current' => 2, 'total' => 2])
            @endif
        </header>
        @endif

        {{-- "Create Account / Create Schedule / Create Event" is noise on somebody's tenth
             schedule, so it is gated - but on the SAME condition HomeController::gettingStarted()
             uses, not a near-miss. That one also bounces on holdsTickets(), so a visitor who
             bought a ticket and later came back to run their own schedule was being shown the
             onboarding bar and a "choose a different type" link that silently redirected them to
             the dashboard. Resolved once, here, and passed down. The band is the guest-submit
             flow's alone; everyone else starting out gets the step bar. --}}
        @if ($isFirstRun && session('pending_request'))
        <div class="mx-auto mt-6 w-full max-w-2xl rounded-2xl overflow-hidden">
            <x-step-indicator :currentStep="2" />
        </div>
        @endif

        <div class="mx-auto w-full max-w-[35rem] {{ $bareShell ? 'mt-9' : 'mt-2' }}">
            <div class="mb-7 flex flex-col gap-2.5">
                {{-- Always a way back, named for where it goes. It used to be gated on being new,
                     so a returning user on the bare shell had no nav, no back and no cancel. --}}
                <a href="{{ $isFirstRun ? route('getting-started') : route('home') }}"
                   class="inline-flex min-h-[2rem] items-center gap-1.5 self-start rounded text-sm font-medium text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                    <svg class="h-4 w-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5L3 12m0 0l7.5-7.5M3 12h18" /></svg>
                    {{ $isFirstRun ? __('messages.choose_different_type') : __('messages.dashboard') }}
                </a>
                @if ($startsGuide && ! $bareShell)
                <div>
                    @include('partials.setup-step-bar', ['current' => 2, 'total' => 2])
                </div>
                @endif
                <h1 class="text-3xl sm:text-[2.625rem] font-bold leading-[1.1] tracking-tight text-gray-900 dark:text-white" style="text-wrap: balance">
                    {{ __('messages.create_schedule_heading_' . $role->type) }}
                </h1>
                <p class="text-base text-gray-600 dark:text-gray-400">
                    {{ __('messages.' . $role->type . '_tagline') }}
                </p>
            </div>

            <div class="ap-card new-schedule-card rounded-[1.25rem] p-6 sm:p-9 ![box-shadow:var(--ap-shadow-lift)]">
                <form method="post" action="{{ route('role.store') }}" enctype="multipart/form-data" id="edit-form">
                    @csrf

                    {{-- See the block comment at the top of this file before touching these. --}}
                    <input type="hidden" name="type" value="{{ $role->type }}">
                    {{-- "Running a market, fair or expo?": the new schedule opens on its vendor map. --}}
                    @php
                        $vendorMapKind = request('vendor_map', old('vendor_map'));
                    @endphp
                    @if (! $role->isTalent() && in_array($vendorMapKind, \App\Services\VendorMaps\VendorMapKinds::KINDS, true))
                    <input type="hidden" name="vendor_map" value="{{ $vendorMapKind }}">
                    @endif
                    <input type="hidden" name="email" value="{{ $user->email }}">
                    {{-- Falls back, because create() copies it straight off the user and a user
                         can carry none: roles.language_code is NOT NULL, and the full form never
                         hit this because its <select required> always posted its first option.
                         store() has the same guard for translation_language_code, for the same
                         reason. The timezone gets the same fallback below, as the select's value. --}}
                    <input type="hidden" name="language_code" value="{{ $role->language_code ?: 'en' }}">
                    <input type="hidden" name="use_24_hour_time" value="{{ $role->use_24_hour_time ? 1 : 0 }}">
                    <input type="hidden" name="require_account" value="{{ $role->require_account ? 1 : 0 }}">
                    <input type="hidden" name="accept_requests" value="1">
                    <input type="hidden" name="background" value="{{ $role->background }}">
                    <input type="hidden" name="background_colors" value="{{ $role->background_colors }}">
                    <input type="hidden" name="background_image" value="{{ $role->background_image }}">
                    <input type="hidden" name="background_rotation" value="{{ $role->background_rotation }}">
                    {{-- The Aurora create() chose: its palette and arrangement, and the palette's
                         accent, which the buttons take so the two match. --}}
                    <input type="hidden" name="accent_color" value="{{ $role->accent_color }}">
                    <input type="hidden" name="aurora_submitted" value="1">
                    <input type="hidden" name="aurora_palette" value="{{ $role->auroraSetting->palette }}">
                    <input type="hidden" name="aurora_seed" value="{{ $role->auroraSetting->seed }}">
                    <input type="hidden" name="aurora_motion" value="1">

                    @if ($errors->any())
                    <div role="alert" class="mb-4 rounded-lg bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 p-3">
                        <div class="flex items-center gap-2">
                            <svg class="w-5 h-5 text-red-600 dark:text-red-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                            </svg>
                            <p class="text-sm text-red-700 dark:text-red-300">{{ $errors->first() }}</p>
                        </div>
                    </div>
                    @endif

                    <div>
                        <x-input-label for="name" :value="__('messages.schedule_name')" class="font-semibold text-gray-900 dark:text-gray-100" />
                        <x-text-input id="name" name="name" type="text" class="mt-2 block w-full min-h-[3.25rem] rounded-xl"
                            :value="old('name', $role->name)" required autofocus autocomplete="organization" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    @if ($role->isVenue())
                    <div class="mt-5">
                        <x-input-label for="address1" :value="__('messages.street_address')" class="font-semibold text-gray-900 dark:text-gray-100" />
                        <x-text-input id="address1" name="address1" type="text" class="mt-2 block w-full min-h-[3.25rem] rounded-xl"
                            :value="old('address1')" autocomplete="off" required />
                        <x-input-error :messages="$errors->get('address1')" class="mt-2" />
                    </div>
                    @endif

                    {{-- What the schedule takes from the account, said once, as a small table. The
                         email is shown rather than asked: it is the account's own and changeable
                         afterwards. --}}
                    @php
                        $formTimezone = \App\Utils\TimezoneUtils::canonicalize(old('timezone', $role->timezone))
                            ?? config('app.timezone');
                    @endphp
                    <div class="mt-7 rounded-xl border border-gray-200 dark:border-white/10 text-sm">
                        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-3.5 py-3">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('messages.email') }}</span>
                            <bdi dir="ltr" class="font-medium text-gray-900 dark:text-gray-100 [overflow-wrap:anywhere]">{{ $user->email }}</bdi>
                        </div>

                        {{-- The timezone is shown collapsed but CAN be changed here, because it cannot
                             be trusted: a Google sign-up used to be stored as America/New_York whatever
                             the browser said, and every event on the schedule is anchored to this
                             value. A closed <details> still submits its select, so this works without
                             JavaScript; the script below only opens it when the device disagrees. --}}
                        <details id="timezone-details" class="group border-t border-gray-200 dark:border-white/10" @if ($errors->has('timezone')) open @endif>
                            <summary class="flex cursor-pointer list-none flex-wrap items-baseline justify-between gap-x-4 gap-y-1 px-3.5 py-3 rounded-b-xl focus:outline-none focus-visible:ring-2 focus-visible:ring-inset focus-visible:ring-[var(--brand-blue)] [&::-webkit-details-marker]:hidden">
                                <span class="text-gray-600 dark:text-gray-400">{{ __('messages.time_zone') }}</span>
                                <span class="flex items-baseline gap-2.5">
                                    <span id="timezone-summary" class="font-medium text-gray-900 dark:text-gray-100" data-template="__TIMEZONE__">{{ $formTimezone }}</span>
                                    <span class="font-medium text-[var(--brand-blue)] hover:underline">{{ __('messages.change_timezone') }}</span>
                                </span>
                            </summary>

                            <div class="px-3.5 pb-3.5">
                                <div id="timezone-mismatch" class="hidden mb-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3"
                                    data-template="{{ __('messages.timezone_device_mismatch', ['timezone' => '__TIMEZONE__']) }}">
                                    <div class="flex items-start gap-2">
                                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                        </svg>
                                        <p id="timezone-mismatch-text" class="text-sm text-amber-800 dark:text-amber-200"></p>
                                    </div>
                                </div>

                                <x-input-label for="timezone" :value="__('messages.time_zone')" class="sr-only" />
                                <select name="timezone" id="timezone" required data-searchable
                                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                    <x-timezone-options :selected="$formTimezone" />
                                </select>
                                <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                            </div>
                        </details>

                        {{-- Every schedule is public; said as a fact of the schedule, not a warning. --}}
                        <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 border-t border-gray-200 dark:border-white/10 px-3.5 py-3"
                             title="{{ __('messages.note_all_schedules_are_publicly_listed') }}">
                            <span class="text-gray-600 dark:text-gray-400">{{ __('messages.visibility') }}</span>
                            <span class="font-medium text-gray-900 dark:text-gray-100">{{ __('messages.public') }}</span>
                        </div>
                    </div>

                    {{-- Runs inline, before searchable-select.js (deferred) enhances the select, so
                         the combobox picks up the device timezone as its initial text. The device
                         check is skipped after a failed save, where the posted value is the user's
                         own choice. --}}
                    <script {!! nonce_attr() !!}>
                    (function () {
                        var select = document.getElementById('timezone');
                        var summary = document.getElementById('timezone-summary');
                        function showSummary(value) {
                            summary.textContent = summary.getAttribute('data-template').replace('__TIMEZONE__', value);
                        }
                        select.addEventListener('change', function () { showSummary(select.value); });
                        @if ($errors->any())
                        return;
                        @endif

                        var aliases = @json(\App\Utils\TimezoneUtils::aliasMap());
                        var device;
                        try {
                            device = Intl.DateTimeFormat().resolvedOptions().timeZone;
                        } catch (e) {
                            return;
                        }
                        if (!device) {
                            return;
                        }
                        device = aliases[device] || device;

                        if (select.value === device) {
                            return;
                        }

                        var option = Array.prototype.find.call(select.options, function (o) { return o.value === device; });
                        if (!option) {
                            return;
                        }
                        select.value = device;
                        showSummary(device);

                        var panel = document.getElementById('timezone-mismatch');
                        document.getElementById('timezone-mismatch-text').textContent =
                            panel.getAttribute('data-template').replace('__TIMEZONE__', device);
                        panel.classList.remove('hidden');
                        document.getElementById('timezone-details').open = true;
                    })();
                    </script>

                    @if ($paidSchedules->isNotEmpty())
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400" data-plan-note v-pre>
                        {{ $paidMovable
                            ? __('messages.new_schedule_plan_note', ['name' => $paidMovable->name])
                            : __('messages.new_schedule_plan_note_venue') }}
                    </p>
                    @endif

                    {{-- The way back is the link above the heading, so the card ends in one button. --}}
                    <button type="submit" class="mt-7 inline-flex min-h-[3.25rem] w-full items-center justify-center gap-2.5 rounded-xl bg-[var(--brand-button-bg)] px-6 text-base font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] hover:shadow-md focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
                        {{ $leadsToEventForm ? __('messages.setup_guide_save_continue') : __('messages.save') }}
                        @if ($leadsToEventForm)
                        <span aria-hidden="true" class="inline-block rtl:rotate-180">&rarr;</span>
                        @endif
                    </button>
                </form>
            </div>
        </div>
    </div>
