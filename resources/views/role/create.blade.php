{{--
    The first-run form for a new schedule.

    /new/{type} used to render role/edit.blade.php - 9,680 lines, six section groups and about
    twenty-four sub-tabs - to somebody who had signed up ninety seconds earlier. Of the people who
    opened it, 28% never saved anything. The first two interactive things on that page were a greyed
    "AI Generator" (not Enterprise) and an amber "available on the Pro plan" panel, because a fresh
    Role is neither.

    Only three fields are required by RoleCreateRequest - name, timezone, email - and the last two
    are inherited from the user. Everything else here is a hidden input.

    THE HIDDEN INPUTS ARE LOAD-BEARING. RoleController::store() does $role->fill($request->all()),
    so an omitted field is not "use the column default", it is whatever fill() and the code after it
    leave behind:

      - type          roles.type is NOT NULL with no default, and store() takes no $type argument.
                      This input is the ONLY thing carrying the route parameter. Omit it: 500.
      - require_account  column default TRUE, but create() prefills ($type === 'curator'), i.e.
                      false for talent and venue. Omitting it makes every new talent and venue
                      demand an account from guests who want to submit.
      - accept_requests  belt and braces, not load-bearing: omitting it leaves the column default
                      of TRUE, which is what we want. It is explicit because the FULL form gets
                      this wrong - create() never prefills it, so its <x-toggle> renders OFF and
                      still posts its companion hidden 0, and every schedule made through that
                      form started out refusing requests. Stating it here keeps the two forms
                      producing the same row.
      - background / background_colors  create() picks 'image' plus a random gradient; the column
                      defaults are 'gradient' and NULL. And store() has
                      `if (! $request->background_colors) { ... custom_color1.', '.custom_color2 }`,
                      so with all three absent it writes the literal string ", " - which
                      hasConfiguredBackground() reads as set, and the guest page then emits
                      `linear-gradient(150deg, , )`. Every new schedule would render no background.
      - language_code / use_24_hour_time  inherited from the user; the column default is 'en', so a
                      Hebrew organizer's first schedule would silently be English.

    Safe to omit, because the column default and create()'s prefill agree: event_layout,
    announce_new_events, show_subscribe_panel, accent_color, font_family, font_color, header_style.

    tests/Feature/FirstScheduleFormTest.php compares a schedule saved from here against one saved
    from the full form, column by column, so a field dropped from this list fails the build.
--}}
<x-app-layout :theme-variants="true" :title="__('messages.new_schedule') . ' | Event Schedule'">

    <div class="flex flex-col items-center px-4 pt-8 pb-12 sm:px-6 lg:px-8">
        <div class="w-full max-w-2xl rounded-2xl overflow-hidden">
            <x-step-indicator :currentStep="2" />
        </div>

        <div class="w-full max-w-xl mt-8">
            <div class="text-center mb-6">
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
                    {{-- Both fall back, because create() copies them straight off the user and a
                         user can carry neither: roles.language_code is NOT NULL, and the full form
                         never hit this because its <select required> always posted its first
                         option. store() has the same guard for translation_language_code, for the
                         same reason. --}}
                    <input type="hidden" name="timezone" value="{{ $role->timezone ?: config('app.timezone') }}">
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

                    {{-- Shown rather than asked. Both are inherited from the account and are
                         changeable afterwards, so making them fields here would be two more
                         decisions in front of the one that matters. --}}
                    <p class="mt-4 text-xs text-gray-500 dark:text-gray-400">
                        <bdi dir="ltr">{{ $user->email }}</bdi> &middot; {{ $role->timezone }}
                    </p>

                    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                        {{ __('messages.note_all_schedules_are_publicly_listed') }}
                    </p>

                    <div class="mt-6 flex items-center justify-end gap-3">
                        @if (auth()->user()->member()->doesntExist())
                        <a href="{{ route('getting-started') }}" class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:underline">
                            {{ __('messages.choose_different_type') }}
                        </a>
                        @endif
                        <x-brand-button type="submit">
                            {{ __('messages.save') }}
                        </x-brand-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
