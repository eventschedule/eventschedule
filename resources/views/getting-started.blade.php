<x-app-layout :title="__('messages.get_started') . ' | Event Schedule'">
    <div class="flex flex-col items-center px-4 pt-8 pb-12 sm:px-6 lg:px-8">
        {{-- Deliberately not a link: the dashboard would forward zero-schedule users straight back here --}}
        <x-application-logo />

        <div class="w-full max-w-2xl mt-2 rounded-2xl overflow-hidden">
            <x-step-indicator :currentStep="2" />
        </div>

        <div class="w-full max-w-6xl mt-8">
            <div class="text-center mb-6">
                <h1 class="text-2xl font-semibold text-gray-900 dark:text-white mb-2">
                    {{ __('messages.getting_started_welcome', ['name' => auth()->user()->firstName()]) }}
                </h1>
                <p class="text-gray-500 dark:text-gray-400">{{ __('messages.create_your_first_schedule') }}</p>
                <p class="mt-1 text-sm text-gray-400 dark:text-gray-500">{{ __('messages.can_create_more_schedules_later') }}</p>
            </div>

            @include('partials.schedule-type-cards')

            <div class="text-center mt-6">
                <a href="{{ route('home', ['skip_onboarding' => 1]) }}"
                   class="text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-200 hover:underline">
                    {{ __('messages.skip_for_now') }}
                </a>
            </div>
        </div>
    </div>
</x-app-layout>
