{{-- theme-variants: see admin/newsletters/templates.blade.php. A brand-new user lands here
     and then on the dashboard, so the two must not disagree about the palette. --}}
<x-app-layout :theme-variants="true" :title="__('messages.get_started') . ' | Event Schedule'">

    {{-- This view renders through the shared shell without going via app-admin, so it carries the
         platform manifest itself. --}}
    <x-slot name="head">
        @include('partials.web-app-manifest', ['platformApp' => true])
    </x-slot>

    <div class="px-4 pt-7 pb-16 sm:px-6 lg:px-8">
        {{-- One column of the chooser's width, every line on its left edge, on the page's own flat
             ground (the 2026-11 design has no glow behind the heading). --}}
        <div class="mx-auto w-full max-w-[57.5rem]">
            {{-- The logo at one end and the step at the other, as on the first schedule's form.
                 Deliberately not a link: the dashboard would forward zero-schedule users straight
                 back here. --}}
            <header class="flex flex-wrap items-center justify-between gap-x-4 gap-y-2">
                <div class="flex">
                    <img class="h-9 w-auto dark:hidden" src="{{ asset(ltrim(config('app.logo_dark'), '/')) }}" alt="{{ config('app.name') }}"/>
                    <img class="h-9 w-auto hidden dark:block" src="{{ asset(ltrim(config('app.logo_light'), '/')) }}" alt="{{ config('app.name') }}"/>
                </div>
                {{-- Step 1 of 2: choosing the type. Not for somebody who turned suggestions off
                     (the setup guide's, and off means all of it), nor in the guest-submit flow,
                     which has its own band below. --}}
                @if (! session('pending_request') && auth()->user()->wantsSuggestions())
                    @include('partials.setup-step-bar', ['current' => 1, 'total' => 2])
                @endif
            </header>

            {{-- The step band, for the guest-submit flow only. --}}
            @if (session('pending_request'))
            <div class="mt-4 rounded-2xl overflow-hidden">
                <x-step-indicator :currentStep="2" />
            </div>
            @endif

            <div class="mt-9 mb-9 flex max-w-2xl flex-col gap-2.5">
                <p class="text-[0.9375rem] font-medium text-[var(--brand-blue)]" v-pre>
                    {{ __('messages.getting_started_welcome_name', ['name' => auth()->user()->firstName()]) }}
                </p>
                <h1 class="text-3xl sm:text-[2.625rem] font-bold leading-[1.1] tracking-tight text-gray-900 dark:text-white" style="text-wrap: balance">
                    {{ __('messages.schedule_type_question_for') }}
                </h1>
                <p class="text-base text-gray-600 dark:text-gray-400">{{ __('messages.schedule_type_pick_lead') }}</p>
            </div>

            @include('partials.schedule-type-cards', ['skipUrl' => route('home', ['skip_onboarding' => 1])])
        </div>
    </div>
</x-app-layout>
