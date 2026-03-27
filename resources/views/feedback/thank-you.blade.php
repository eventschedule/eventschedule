<x-app-layout :title="__('messages.feedback_thank_you') . ' | ' . $event->name">

    <x-slot name="meta">
        <meta name="robots" content="noindex, nofollow">
    </x-slot>

    <div class="min-h-screen bg-gray-50 dark:bg-[#1e1e1e] py-8 px-4 sm:px-6 lg:px-8">
        <div class="max-w-lg mx-auto">

            {{-- Schedule branding --}}
            @if ($role->profile_image_url)
            <div class="text-center mb-6">
                <img src="{{ $role->profile_image_url }}" alt="{{ $role->name }}" class="h-12 mx-auto rounded-lg">
            </div>
            @endif

            {{-- Event info card --}}
            <div class="bg-white dark:bg-[#2d2d30] rounded-lg shadow-sm border border-gray-200 dark:border-[#2d2d30] p-6 mb-6">
                <h1 class="text-xl font-semibold text-gray-900 dark:text-white mb-1">{{ $event->name }}</h1>
                <p class="text-sm text-gray-500 dark:text-[#9ca3af]">
                    {{ $event->is_multi_day ? $event->getDateRangeDisplay($sale->event_date) : $event->getStartDateTime($sale->event_date, true)->translatedFormat('F j, Y') }}
                    &middot;
                    {{ $event->getStartEndTime($sale->event_date) }}
                </p>
            </div>

            {{-- Thank you card --}}
            <div class="bg-white dark:bg-[#2d2d30] rounded-lg shadow-sm border border-gray-200 dark:border-[#2d2d30] p-6 text-center">
                <div class="mb-4">
                    <svg class="w-12 h-12 mx-auto text-green-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>

                <h2 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">{{ __('messages.feedback_thank_you') }}</h2>
                <p class="text-sm text-gray-500 dark:text-[#9ca3af] mb-4">{{ __('messages.feedback_thank_you_message') }}</p>

                {{-- Show submitted rating --}}
                <div class="flex justify-center gap-1 mb-4">
                    @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-6 h-6 {{ $i <= $existingFeedback->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 24 24" fill="{{ $i <= $existingFeedback->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    @endfor
                </div>

                @if ($existingFeedback->comment)
                <div class="text-sm text-gray-600 dark:text-[#d1d5db] bg-gray-50 dark:bg-[#1e1e1e] rounded-lg p-3 text-start" dir="auto">
                    {{ $existingFeedback->comment }}
                </div>
                @endif
            </div>
        </div>
    </div>

</x-app-layout>
