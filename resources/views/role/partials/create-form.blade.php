@php
    // The same question HomeController::gettingStarted() asks before it bounces someone away from
    // the onboarding chooser. Kept identical on purpose: a page and the page it links to must not
    // disagree about whether this person is new.
    $isFirstRun = ! is_demo_mode()
        && auth()->user()->member()->doesntExist()
        && auth()->user()->tickets()->count() === 0;

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
@endphp

    <div class="flex flex-col items-center px-4 pt-8 pb-12 sm:px-6 lg:px-8">
        {{-- "Create Account / Create Schedule / Create Event" is noise on somebody's tenth
             schedule, so it is gated - but on the SAME condition HomeController::gettingStarted()
             uses, not a near-miss. That one also bounces on tickets()->count(), so a visitor who
             bought a ticket and later came back to run their own schedule was being shown the
             onboarding bar and a "choose a different type" link that silently redirected them to
             the dashboard. Resolved once, here, and passed down. --}}
        {{-- The band is now the guest-submit flow's alone. Everyone else starting out gets the
             setup guide's ring and line above the heading (partials/setup-guide-eyebrow). --}}
        @if ($isFirstRun && session('pending_request'))
        <div class="w-full max-w-2xl rounded-2xl overflow-hidden">
            <x-step-indicator :currentStep="2" />
        </div>
        @endif

        <div class="w-full max-w-xl mt-8">
            <div class="text-center mb-6">
                @if ($startsGuide)
                <div class="mb-3">
                    @include('partials.setup-guide-eyebrow', ['line' => __('messages.setup_guide_two_steps'), 'initialFrom' => 'name'])
                </div>
                @endif
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white">
                    {{ __('messages.' . $role->type) }}
                </h1>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.' . $role->type . '_tagline') }}
                </p>
            </div>

            <div class="ap-card rounded-xl p-6">
                <form method="post" action="{{ route('role.store') }}" enctype="multipart/form-data" id="edit-form">
                    @csrf

                    {{-- See the block comment at the top of this file before touching these. --}}
                    <input type="hidden" name="type" value="{{ $role->type }}">
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
                        <x-input-label for="name" :value="__('messages.schedule_name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full"
                            :value="old('name', $role->name)" required autofocus autocomplete="organization" />
                        <x-input-error :messages="$errors->get('name')" class="mt-2" />
                    </div>

                    @if ($role->isVenue())
                    <div class="mt-4">
                        <x-input-label for="address1" :value="__('messages.street_address')" />
                        <x-text-input id="address1" name="address1" type="text" class="mt-1 block w-full"
                            :value="old('address1')" autocomplete="off" required />
                        <x-input-error :messages="$errors->get('address1')" class="mt-2" />
                    </div>
                    @endif

                    {{-- The email is shown rather than asked: it is the account's own and
                         changeable afterwards. --}}
                    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                        <bdi dir="ltr">{{ $user->email }}</bdi>
                    </p>

                    {{-- The timezone is shown collapsed but CAN be changed here, because it cannot
                         be trusted: a Google sign-up used to be stored as America/New_York whatever
                         the browser said, and every event on the schedule is anchored to this
                         value. A closed <details> still submits its select, so this works without
                         JavaScript; the script below only opens it when the device disagrees. --}}
                    @php
                        $formTimezone = \App\Utils\TimezoneUtils::canonicalize(old('timezone', $role->timezone))
                            ?? config('app.timezone');
                    @endphp
                    <details id="timezone-details" class="mt-2 group" @if ($errors->has('timezone')) open @endif>
                        <summary class="inline-flex cursor-pointer items-center gap-1 text-xs text-gray-500 dark:text-gray-400 rounded focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                            <span id="timezone-summary" data-template="{{ __('messages.schedule_times_in_timezone', ['timezone' => '__TIMEZONE__']) }}">{{ __('messages.schedule_times_in_timezone', ['timezone' => $formTimezone]) }}</span>
                            <span aria-hidden="true">&middot;</span>
                            <span class="text-[var(--brand-blue)] hover:underline">{{ __('messages.change_timezone') }}</span>
                        </summary>

                        <div class="mt-3">
                            <div id="timezone-mismatch" class="hidden mb-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3"
                                data-template="{{ __('messages.timezone_device_mismatch', ['timezone' => '__TIMEZONE__']) }}">
                                <div class="flex items-start gap-2">
                                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                                    </svg>
                                    <p id="timezone-mismatch-text" class="text-sm text-amber-800 dark:text-amber-200"></p>
                                </div>
                            </div>

                            <x-input-label for="timezone" :value="__('messages.timezone')" />
                            <select name="timezone" id="timezone" required data-searchable
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                <x-timezone-options :selected="$formTimezone" />
                            </select>
                            <x-input-error :messages="$errors->get('timezone')" class="mt-2" />
                        </div>
                    </details>

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

                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('messages.note_all_schedules_are_publicly_listed') }}
                    </p>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        {{-- Always an exit. It used to be gated on being new, so a returning user
                             on the bare shell had no nav, no back and no cancel: the browser button
                             was the only way off this page. --}}
                        @if ($isFirstRun)
                        <a href="{{ route('getting-started') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:underline">
                            {{ __('messages.choose_different_type') }}
                        </a>
                        @else
                        <a href="{{ route('home') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:underline">
                            {{ __('messages.cancel') }}
                        </a>
                        @endif
                        <x-brand-button type="submit">
                            {{ $leadsToEventForm ? __('messages.setup_guide_save_continue') : __('messages.save') }}
                        </x-brand-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
