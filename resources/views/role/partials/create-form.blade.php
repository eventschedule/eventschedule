@php
    // The same question HomeController::gettingStarted() asks before it bounces someone away from
    // the onboarding chooser. Kept identical on purpose: a page and the page it links to must not
    // disagree about whether this person is new.
    $isFirstRun = ! is_demo_mode()
        && auth()->user()->member()->doesntExist()
        && auth()->user()->tickets()->count() === 0;
@endphp

    <div class="flex flex-col items-center px-4 pt-8 pb-12 sm:px-6 lg:px-8">
        {{-- "Create Account / Create Schedule / Create Event" is noise on somebody's tenth
             schedule, so it is gated - but on the SAME condition HomeController::gettingStarted()
             uses, not a near-miss. That one also bounces on tickets()->count(), so a visitor who
             bought a ticket and later came back to run their own schedule was being shown the
             onboarding bar and a "choose a different type" link that silently redirected them to
             the dashboard. Resolved once, here, and passed down. --}}
        @if ($isFirstRun)
        <div class="w-full max-w-2xl rounded-2xl overflow-hidden">
            <x-step-indicator :currentStep="2" />
        </div>
        @endif

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
                            {{ __('messages.save') }}
                        </x-brand-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
