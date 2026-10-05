{{-- theme-variants: see admin/newsletters/templates.blade.php. A brand-new user lands here
     and then on the dashboard, so the two must not disagree about the palette. --}}
<x-app-layout :theme-variants="true" :title="__('messages.get_started') . ' | Event Schedule'">

    {{-- This view renders through the shared shell without going via app-admin, so it carries the
         platform manifest itself. --}}
    <x-slot name="head">
        @include('partials.web-app-manifest', ['platformApp' => true])
    </x-slot>

    <div class="flex flex-col items-center px-4 pt-8 pb-16 sm:px-6 lg:px-8">
        {{-- Deliberately not a link: the dashboard would forward zero-schedule users straight back here --}}
        <x-application-logo />

        {{-- The step band, for the guest-submit flow only. Everyone else gets the setup guide's
             ring in the welcome line below, which adds no height above the cards. --}}
        @if (session('pending_request'))
        <div class="w-full max-w-2xl mt-2 rounded-2xl overflow-hidden">
            <x-step-indicator :currentStep="2" />
        </div>
        @endif

        {{-- isolate: the glow sits at -z-10, behind the heading and cards but still above the
             body's background. It starts below the step band when that is shown, whose own panel
             background would otherwise cut a hard rectangle out of it. Never move it onto
             html/body (see CLAUDE.md on containing blocks). --}}
        <div class="relative isolate w-full max-w-6xl mt-8">
            <div class="getting-started-glow" aria-hidden="true"></div>

            <div class="text-center mb-8">
                {{-- The plain welcome also for somebody who turned suggestions off: the ring and
                     its "two steps" line are the setup guide's, and off means all of it. --}}
                @if (session('pending_request') || ! auth()->user()->wantsSuggestions())
                <p class="text-sm sm:text-base font-semibold text-[var(--brand-blue)]">
                    {{ __('messages.getting_started_welcome', ['name' => auth()->user()->firstName()]) }}
                </p>
                @else
                @include('partials.setup-guide-eyebrow', ['line' => __('messages.setup_guide_welcome_two_steps', ['name' => auth()->user()->firstName()])])
                @endif
                <h1 class="mt-2 text-3xl sm:text-4xl font-bold tracking-tight text-gray-900 dark:text-white">
                    {{ __('messages.schedule_type_question') }}
                </h1>
                <p class="mt-3 text-base text-gray-500 dark:text-gray-400">{{ __('messages.can_create_more_schedules_later') }}</p>
            </div>

            @include('partials.schedule-type-cards')

            <div class="text-center mt-8">
                <a href="{{ route('home', ['skip_onboarding' => 1]) }}"
                   class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:underline">
                    {{ __('messages.skip_for_now') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
