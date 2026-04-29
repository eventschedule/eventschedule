{{-- Pipeline Summary Cards --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
    {{-- Pending --}}
    <div class="ap-card rounded-xl p-6 flex flex-col items-center">
        <div class="flex items-center gap-3 mb-3 self-start">
            <div class="dashboard-icon p-2 rounded-xl bg-amber-50 dark:bg-amber-500/10"
                 style="--icon-glow: rgba(245, 158, 11, 0.15)">
                <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.feedback_pending') }}</p>
        </div>
        <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ $pendingCount }}</p>
        @if ($nextSendAt)
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">
            @if ($nextSendAt->isPast())
                {{ __('messages.feedback_next_send', ['time' => __('messages.feedback_sends_in', ['count' => '< 1'])]) }}
            @elseif ($nextSendAt->diffInHours(now()) < 24)
                {{ __('messages.feedback_next_send', ['time' => __('messages.feedback_sends_in', ['count' => max(1, $nextSendAt->diffInHours(now()))])]) }}
            @else
                {{ __('messages.feedback_next_send', ['time' => $nextSendAt->format('M j, g:i A')]) }}
            @endif
        </p>
        @else
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.feedback_none_pending') }}</p>
        @endif
    </div>

    {{-- Sent --}}
    <div class="ap-card rounded-xl p-6 flex flex-col items-center">
        <div class="flex items-center gap-3 mb-3 self-start">
            <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10"
                 style="--icon-glow: rgba(59, 130, 246, 0.15)">
                <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.feedback_sent') }}</p>
        </div>
        <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ $awaitingCount }}</p>
        <p class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.feedback_awaiting_response') }}</p>
    </div>

    {{-- Responded --}}
    <div class="ap-card rounded-xl p-6 flex flex-col items-center">
        <div class="flex items-center gap-3 mb-3 self-start">
            <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10"
                 style="--icon-glow: rgba(34, 197, 94, 0.15)">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.feedback_responded') }}</p>
        </div>
        <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ $feedbackCount }}</p>
        @if ($averageRating)
        <div class="flex gap-0.5 mt-1">
            @for ($i = 1; $i <= 5; $i++)
            <svg class="w-4 h-4 {{ $i <= round($averageRating) ? 'text-yellow-400' : 'text-gray-300 dark:text-gray-600' }}" viewBox="0 0 24 24" fill="{{ $i <= round($averageRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
            </svg>
            @endfor
            <span class="text-xs text-gray-500 dark:text-gray-400 ms-1">{{ $averageRating }}</span>
        </div>
        @endif
    </div>

    {{-- Response Rate --}}
    <div class="ap-card rounded-xl p-6 flex flex-col items-center">
        <div class="flex items-center gap-3 mb-3 self-start">
            <div class="dashboard-icon p-2 rounded-xl bg-purple-50 dark:bg-purple-500/10"
                 style="--icon-glow: rgba(168, 85, 247, 0.15)">
                <svg class="w-5 h-5 text-purple-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                </svg>
            </div>
            <p class="text-sm font-medium text-gray-500 dark:text-gray-400">{{ __('messages.feedback_response_rate') }}</p>
        </div>
        <p class="dashboard-stat-value text-3xl font-bold text-gray-900 dark:text-white text-center">{{ $responseRate }}%</p>
    </div>
</div>

{{-- Pending Emails Section --}}
@if ($pendingCount > 0)
<div class="mb-6">
    <div class="flex items-center justify-between mb-3">
        <button type="button" onclick="this.closest('div').nextElementSibling.classList.toggle('hidden'); this.querySelector('.js-arrow').classList.toggle('rotate-90')" class="flex items-center gap-2 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
            <svg class="js-arrow w-4 h-4 rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
            </svg>
            <svg class="w-5 h-5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            {{ __('messages.feedback_pending_emails') }}
        </button>
        <div class="relative inline-block text-start">
            <button type="button" data-popup-toggle="feedback-actions-menu" class="inline-flex items-center justify-center rounded-lg bg-white dark:bg-gray-800 px-3 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-700 hover:bg-gray-50 dark:hover:bg-gray-700">
                {{ __('messages.actions') }}
                <svg class="-me-1 ms-2 h-5 w-5 text-gray-400 dark:text-gray-500" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                    <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                </svg>
            </button>
            <div id="feedback-actions-menu" class="ap-dropdown pop-up-menu hidden absolute {{ is_rtl() ? 'start-0' : 'end-0' }} z-10 mt-2 w-56 {{ is_rtl() ? 'origin-top-left' : 'origin-top-right' }} rounded-lg ring-1 ring-black/5 dark:ring-white/[0.06] focus:outline-none" role="menu">
                <div class="py-2" role="none" data-popup-toggle="feedback-actions-menu">
                    @if ($readyToSendCount > 0)
                    <form method="POST" action="{{ route('sales.send_feedback_now') }}">
                        @csrf
                        <button type="submit" onclick="return confirm('{{ __('messages.feedback_send_now_confirm', ['count' => $readyToSendCount]) }}')" class="group flex items-center w-full px-5 py-3 text-sm text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-colors" role="menuitem">
                            <svg class="me-3 h-5 w-5 text-gray-400 dark:text-gray-500 group-hover:text-gray-500 dark:group-hover:text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                            </svg>
                            {{ __('messages.feedback_send_now', ['count' => $readyToSendCount]) }}
                        </button>
                    </form>
                    @endif
                    <form method="POST" action="{{ route('sales.cancel_feedback') }}">
                        @csrf
                        <button type="submit" onclick="return confirm('{{ __('messages.feedback_cancel_confirm', ['count' => $pendingCount]) }}')" class="group flex items-center w-full px-5 py-3 text-sm text-red-600 dark:text-red-400 hover:bg-gray-100 dark:hover:bg-gray-700 focus:outline-none transition-colors" role="menuitem">
                            <svg class="me-3 h-5 w-5 text-red-400 dark:text-red-500 group-hover:text-red-500 dark:group-hover:text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            {{ __('messages.feedback_cancel_all', ['count' => $pendingCount]) }}
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="js-collapsible">
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-hidden shadow ring-1 ring-black/5 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="py-3.5 ps-4 pe-3 text-start text-sm font-semibold text-gray-900 dark:text-gray-100 sm:ps-6">{{ __('messages.event') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.date') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.attendee') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.feedback_estimated_send') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @foreach ($pendingGroups as $group)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150 cursor-pointer" onclick="this.nextElementSibling.classList.toggle('hidden'); this.querySelector('.js-expand-arrow').classList.toggle('rotate-90')">
                        <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400 transition-transform duration-200 js-expand-arrow" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                </svg>
                                {{ $group->event_name }}
                            </div>
                        </td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $group->event_date ?? '' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.feedback_attendees_count', ['count' => $group->count]) }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">
                            @if ($group->estimated_send_at->isPast())
                                <span class="inline-flex items-center gap-1 text-green-600 dark:text-green-400">
                                    <svg class="w-3 h-3 animate-pulse" fill="currentColor" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4"/></svg>
                                    {{ __('messages.feedback_sends_in', ['count' => '< 1']) }}
                                </span>
                            @elseif ($group->estimated_send_at->diffInHours(now()) < 24)
                                {{ __('messages.feedback_sends_in', ['count' => max(1, $group->estimated_send_at->diffInHours(now()))]) }}
                            @else
                                {{ $group->estimated_send_at->format('M j, g:i A') }}
                            @endif
                        </td>
                    </tr>
                    <tr class="hidden">
                        <td colspan="4" class="px-4 py-3 bg-gray-50 dark:bg-[#252526] sm:px-6">
                            <div class="space-y-1">
                                @foreach ($group->sales as $sale)
                                <div class="flex items-center gap-3 text-sm text-gray-600 dark:text-gray-300">
                                    <svg class="w-4 h-4 text-gray-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                    </svg>
                                    <span>{{ $sale->name }}</span>
                                    <span class="text-gray-400 dark:text-gray-500">{{ $sale->email }}</span>
                                </div>
                                @endforeach
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden space-y-3">
            @foreach ($pendingGroups as $group)
            <div class="ap-card rounded-xl shadow ring-1 ring-black/5 p-4">
                <div class="flex justify-between items-start mb-2">
                    <div>
                        <p class="font-medium text-gray-900 dark:text-gray-100">{{ $group->event_name }}</p>
                        @if ($group->event_date)
                        <p class="text-sm text-gray-500 dark:text-gray-400">{{ $group->event_date }}</p>
                        @endif
                    </div>
                    <span class="text-xs font-medium text-amber-600 dark:text-amber-400 bg-amber-50 dark:bg-amber-500/10 px-2 py-1 rounded-full">
                        {{ __('messages.feedback_attendees_count', ['count' => $group->count]) }}
                    </span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    @if ($group->estimated_send_at->isPast())
                        <span class="text-green-600 dark:text-green-400">{{ __('messages.feedback_sends_in', ['count' => '< 1']) }}</span>
                    @elseif ($group->estimated_send_at->diffInHours(now()) < 24)
                        {{ __('messages.feedback_sends_in', ['count' => max(1, $group->estimated_send_at->diffInHours(now()))]) }}
                    @else
                        {{ $group->estimated_send_at->format('M j, g:i A') }}
                    @endif
                </p>
            </div>
            @endforeach
        </div>

        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">
            {{ __('messages.feedback_checked_hourly') }}
            @if ($excludedCount > 0)
                &middot; {{ __('messages.feedback_excluded_note', ['count' => $excludedCount]) }}
            @endif
        </p>
    </div>
</div>
@endif

{{-- Sent Awaiting Response Section --}}
@if ($awaitingCount > 0)
<div class="mb-6">
    <button type="button" onclick="this.parentElement.querySelector('.js-collapsible').classList.toggle('hidden'); this.querySelector('.js-arrow').classList.toggle('rotate-90')" class="flex items-center gap-2 mb-3 text-sm font-semibold text-gray-900 dark:text-gray-100 hover:text-gray-600 dark:hover:text-gray-300 transition-colors duration-200">
        <svg class="js-arrow w-4 h-4 rotate-90 transition-transform duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
        </svg>
        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
        </svg>
        {{ __('messages.feedback_sent_awaiting') }}
    </button>
    <div class="js-collapsible">
        {{-- Desktop Table --}}
        <div class="hidden md:block overflow-hidden shadow ring-1 ring-black/5 rounded-lg">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                <thead class="bg-gray-50 dark:bg-gray-800">
                    <tr>
                        <th scope="col" class="py-3.5 ps-4 pe-3 text-start text-sm font-semibold text-gray-900 dark:text-gray-100 sm:ps-6">{{ __('messages.attendee') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.event') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.date') }}</th>
                        <th scope="col" class="px-3 py-3.5 text-start text-sm font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.feedback_sent') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                    @foreach ($awaitingSales as $sale)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors duration-150">
                        <td class="whitespace-nowrap py-4 ps-4 pe-3 text-sm font-medium text-gray-900 dark:text-gray-100 sm:ps-6">{{ $sale->name }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $sale->event->name ?? '' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $sale->event_date ?? '' }}</td>
                        <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500 dark:text-gray-400">{{ $sale->feedback_sent_at->format('M j, Y g:i A') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Mobile Card View --}}
        <div class="md:hidden space-y-3">
            @foreach ($awaitingSales as $sale)
            <div class="ap-card rounded-xl shadow ring-1 ring-black/5 p-4">
                <div class="flex justify-between items-start mb-1">
                    <p class="font-medium text-gray-900 dark:text-gray-100">{{ $sale->name }}</p>
                    <span class="text-xs text-gray-400 dark:text-gray-500">{{ $sale->feedback_sent_at->format('M j, Y') }}</span>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400">{{ $sale->event->name ?? '' }}</p>
                @if ($sale->event_date)
                <p class="text-xs text-gray-400 dark:text-gray-500 mt-1">{{ $sale->event_date }}</p>
                @endif
            </div>
            @endforeach
        </div>

        @if ($awaitingCount > 50)
        <p class="mt-3 text-xs text-gray-400 dark:text-gray-500">{{ __('messages.feedback_more_results', ['count' => $awaitingCount - 50]) }}</p>
        @endif
    </div>
</div>
@endif

{{-- Submitted Feedback Section --}}
<div>
    <h3 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-3 flex items-center gap-2">
        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        {{ __('messages.feedback_responses') }}
    </h3>

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
                <div class="overflow-hidden shadow ring-1 ring-black/5 md:rounded-lg">
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
            <div class="ap-card rounded-xl shadow ring-1 ring-black/5 p-4">
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
</div>
