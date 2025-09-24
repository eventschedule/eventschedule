<x-app-admin-layout>
    @php
        $timezone = auth()->user()->timezone ?? config('app.timezone');
        $startDisplay = $startAt ? $startAt->copy()->locale(app()->getLocale())->translatedFormat('M j, Y • g:i A') : null;
        $endDisplay = $endAt ? $endAt->copy()->locale(app()->getLocale())->translatedFormat('M j, Y • g:i A') : null;
        $talentNames = $talents->map->translatedName()->implode(', ');
        $curatorNames = $curators->map->translatedName()->implode(', ');
        $hasTickets = $event->tickets_enabled && $event->tickets->count() > 0;
        $guestUrl = $event->getGuestUrl();
        $cleanGuestUrl = $guestUrl ? \App\Utils\UrlUtils::clean($guestUrl) : null;
    @endphp

    <div class="py-6">
        <div class="space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-5">
                    <div class="flex flex-col gap-6 md:flex-row md:items-start md:justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-50">
                                {{ $event->translatedName() }}
                            </h1>
                            <div class="mt-3 space-y-1 text-sm text-gray-600 dark:text-gray-300">
                                @if ($startDisplay)
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ __('messages.date') }}:</span>
                                        {{ $startDisplay }}
                                        <span class="text-gray-500">({{ $timezone }})</span>
                                    </div>
                                    @if ($endDisplay)
                                        <div>
                                            <span class="font-medium text-gray-700 dark:text-gray-200">{{ __('messages.to') }}</span>
                                            {{ $endDisplay }}
                                            <span class="text-gray-500">({{ $timezone }})</span>
                                        </div>
                                    @endif
                                @elseif (count($recurringDays))
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ __('messages.schedule') }}:</span>
                                        {{ __('messages.recurring') }}
                                    </div>
                                @else
                                    <div>{{ __('messages.unscheduled') }}</div>
                                @endif

                                @if (count($recurringDays))
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ __('messages.days_of_week') }}:</span>
                                        {{ implode(', ', $recurringDays) }}
                                    </div>
                                @endif

                                @if ($event->duration)
                                    <div>
                                        <span class="font-medium text-gray-700 dark:text-gray-200">{{ __('messages.duration_in_hours') }}:</span>
                                        {{ rtrim(rtrim(number_format($event->duration, 2), '0'), '.') }}h
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="flex flex-wrap items-center gap-3">
                            @if ($guestUrl)
                                <a href="{{ $guestUrl }}" target="_blank"
                                   class="inline-flex items-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700">
                                    {{ __('messages.view_event') }}
                                </a>
                            @endif
                            <a href="{{ route('event.edit_admin', ['hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}"
                               class="inline-flex items-center rounded-md bg-[#4E81FA] px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-[#3A6BE0]">
                                {{ __('messages.edit') }}
                            </a>
                            <form method="POST" action="{{ route('events.destroy', ['hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" onsubmit="return confirm('{{ __('messages.are_you_sure') }}');">
                                @csrf
                                @method('DELETE')
                                <x-danger-button>
                                    {{ __('messages.delete') }}
                                </x-danger-button>
                            </form>
                        </div>
                    </div>

                    <div class="mt-4 flex flex-wrap gap-3 text-sm text-gray-600 dark:text-gray-300">
                        @if ($event->venue)
                            <span class="inline-flex items-center rounded-full bg-blue-50 px-3 py-1 text-blue-700 dark:bg-blue-900/30 dark:text-blue-200">
                                {{ __('messages.in_person') }}
                            </span>
                        @endif
                        @if ($event->event_url)
                            <span class="inline-flex items-center rounded-full bg-indigo-50 px-3 py-1 text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">
                                {{ __('messages.online') }}
                            </span>
                        @endif
                        @if ($talentNames)
                            <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-gray-700 dark:bg-gray-700 dark:text-gray-200">
                                {{ $talentNames }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
                <div class="space-y-6 lg:col-span-2">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="px-6 py-5">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('messages.event_details') }}
                            </h2>
                            <div class="mt-4 text-sm text-gray-700 dark:text-gray-200 custom-content">
                                {!! $event->translatedDescription() ?: '<p class="text-gray-500">' . __('messages.none') . '</p>' !!}
                            </div>

                            <dl class="mt-6 grid grid-cols-1 gap-4 text-sm text-gray-600 dark:text-gray-300 sm:grid-cols-2">
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                                    <dt class="font-medium text-gray-900 dark:text-gray-100">{{ __('messages.category') }}</dt>
                                    <dd class="mt-1">{{ $categoryName ?? __('messages.none') }}</dd>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                                    <dt class="font-medium text-gray-900 dark:text-gray-100">{{ __('messages.url') }}</dt>
                                    <dd class="mt-1">
                                        @if ($guestUrl)
                                            <a href="{{ $guestUrl }}" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">{{ $cleanGuestUrl }}</a>
                                        @else
                                            {{ __('messages.none') }}
                                        @endif
                                    </dd>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                                    <dt class="font-medium text-gray-900 dark:text-gray-100">{{ __('messages.registration_url') }}</dt>
                                    <dd class="mt-1">
                                        @if ($event->registration_url)
                                            <a href="{{ $event->registration_url }}" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">{{ $event->registration_url }}</a>
                                        @else
                                            {{ __('messages.none') }}
                                        @endif
                                    </dd>
                                </div>
                                <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                                    <dt class="font-medium text-gray-900 dark:text-gray-100">{{ __('messages.owner') }}</dt>
                                    <dd class="mt-1">{{ $event->user?->name ?? __('messages.none') }}</dd>
                                </div>
                            </dl>
                        </div>
                    </div>

                    @if ($hasTickets)
                        <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                            <div class="px-6 py-5">
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.tickets') }}</h2>
                                <div class="mt-4 overflow-x-auto">
                                    <table class="min-w-full divide-y divide-gray-200 text-sm">
                                        <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-200">
                                            <tr>
                                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.type') }}</th>
                                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.price') }}</th>
                                                <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.quantity') }}</th>
                                            </tr>
                                        </thead>
                                        <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                            @foreach ($event->tickets as $ticket)
                                                <tr class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                                                    <td class="px-4 py-2">{{ $ticket->type ?: __('messages.none') }}</td>
                                                    <td class="px-4 py-2">
                                                        {{ $ticket->price ? $event->ticket_currency_code . ' ' . number_format($ticket->price, 2) : __('messages.free') }}
                                                    </td>
                                                    <td class="px-4 py-2">{{ $ticket->quantity ?? __('messages.unlimited') }}</td>
                                                </tr>
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="space-y-6">
                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="px-6 py-5">
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.venue') }}</h2>
                            <div class="mt-3 text-sm text-gray-700 dark:text-gray-200 space-y-2">
                                @if ($venue)
                                    <div class="font-medium">{{ $venue->translatedName() }}</div>
                                    @if ($venue->formatted_address)
                                        <div>{{ $venue->formatted_address }}</div>
                                    @else
                                        <div>
                                            {{ $venue->address1 }}<br>
                                            {{ trim($venue->city . ', ' . $venue->state . ' ' . $venue->postal_code) }}
                                        </div>
                                    @endif
                                    @if ($venue->website)
                                        <div>
                                            <a href="{{ $venue->website }}" target="_blank" class="text-blue-600 hover:underline dark:text-blue-400">
                                                {{ \App\Utils\UrlUtils::clean($venue->website) }}
                                            </a>
                                        </div>
                                    @endif
                                @else
                                    <div>{{ __('messages.none') }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                        <div class="px-6 py-5 space-y-4">
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.talent') }}</h2>
                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $talentNames ?: __('messages.none') }}
                                </p>
                            </div>
                            <div>
                                <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.curator') }}</h2>
                                <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">
                                    {{ $curatorNames ?: __('messages.none') }}
                                </p>
                            </div>
                            @if ($event->creatorRole)
                                <div>
                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.schedule') }}</h2>
                                    <p class="mt-2 text-sm text-gray-700 dark:text-gray-200">{{ $event->creatorRole->translatedName() }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
