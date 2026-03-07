{{-- Summary card --}}
@if ($feedbackCount > 0)
<div class="bg-white dark:bg-gray-800 rounded-xl shadow ring-1 ring-black ring-opacity-5 p-6 mb-6">
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-6">
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.feedback_average_rating') }}</p>
            <div class="flex items-center gap-2 mt-1">
                <div class="flex gap-0.5">
                    @for ($i = 1; $i <= 5; $i++)
                    <svg class="w-5 h-5 {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 24 24" fill="{{ $i <= round($averageRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                    </svg>
                    @endfor
                </div>
                <span class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $averageRating }}</span>
            </div>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.feedback_responses') }}</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-1">{{ $feedbackCount }}</p>
        </div>
        <div>
            <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.feedback_response_rate') }}</p>
            <p class="text-lg font-semibold text-gray-900 dark:text-gray-100 mt-1">{{ $responseRate }}%</p>
        </div>
    </div>
</div>
@endif

<div class="mt-4 flow-root">
    @if($feedbacks->count() > 0)

    {{-- Export button --}}
    <div class="mb-4 flex justify-end">
        <x-brand-link href="{{ route('sales.export_feedback') }}" class="w-full sm:w-auto">
            <svg class="-ms-0.5 me-2 h-5 w-5" viewBox="0 0 24 24" fill="currentColor">
                <path d="M5,20H19V18H5M19,9H15V3H9V9H5L12,16L19,9Z" />
            </svg>
            {{ __('messages.feedback_export') }}
        </x-brand-link>
    </div>

    {{-- Desktop Table --}}
    <div class="hidden md:block -mx-4 -my-2 overflow-x-auto sm:-mx-6 lg:-mx-8">
        <div class="inline-block min-w-full py-2 align-middle sm:px-6 lg:px-8">
            <div class="overflow-hidden shadow ring-1 ring-black ring-opacity-5 md:rounded-lg">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-800">
                        <tr>
                            <x-sortable-header column="attendee_name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'" class="py-3.5 ps-4 pe-3 sm:ps-6">{{ __('messages.attendee') }}</x-sortable-header>
                            <x-sortable-header column="event_name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.event') }}</x-sortable-header>
                            <x-sortable-header column="event_date" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.date') }}</x-sortable-header>
                            <x-sortable-header column="rating" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.rating') }}</x-sortable-header>
                            <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">
                                {{ __('messages.comment') }}
                            </th>
                            <x-sortable-header column="created_at" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.submitted') }}</x-sortable-header>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                        @foreach ($feedbacks as $feedback)
                        <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                            <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
                                {{ $feedback->sale->name ?? '' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $feedback->event->name ?? '' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $feedback->event_date ?? '' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm">
                                <div class="flex gap-0.5">
                                    @for ($i = 1; $i <= 5; $i++)
                                    <svg class="w-4 h-4 {{ $i <= $feedback->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 24 24" fill="{{ $i <= $feedback->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                                    </svg>
                                    @endfor
                                </div>
                            </td>
                            <td class="px-3 py-4 text-sm text-gray-500 dark:text-gray-400 max-w-xs truncate">
                                {{ $feedback->comment ? \Illuminate\Support\Str::limit($feedback->comment, 80) : '' }}
                            </td>
                            <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                                {{ $feedback->created_at->format('M j, Y') }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- Mobile Card View --}}
    <div class="md:hidden space-y-3 mt-4">
        @foreach ($feedbacks as $feedback)
        <div class="bg-white dark:bg-gray-800 rounded-xl shadow ring-1 ring-black ring-opacity-5 p-4">
            <div class="flex justify-between items-start mb-2">
                <div>
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $feedback->sale->name ?? '' }}</p>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ $feedback->event->name ?? '' }}</p>
                </div>
                <span class="text-xs text-gray-400 dark:text-gray-500">{{ $feedback->created_at->format('M j, Y') }}</span>
            </div>
            <div class="flex gap-0.5 mb-2">
                @for ($i = 1; $i <= 5; $i++)
                <svg class="w-4 h-4 {{ $i <= $feedback->rating ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 24 24" fill="{{ $i <= $feedback->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                @endfor
            </div>
            @if ($feedback->comment)
            <p class="text-sm text-gray-600 dark:text-gray-300" dir="auto">{{ $feedback->comment }}</p>
            @endif
            @if ($feedback->event_date)
            <p class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ $feedback->event_date }}</p>
            @endif
        </div>
        @endforeach
    </div>

    <div class="mt-4">
        {{ $feedbacks->links() }}
    </div>

    @else
    <div class="text-center py-12">
        <svg class="mx-auto h-12 w-12 text-gray-400 dark:text-gray-500" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
        </svg>
        <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.feedback_no_responses') }}</h3>
        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.feedback_enabled_help') }}</p>
    </div>
    @endif
</div>
