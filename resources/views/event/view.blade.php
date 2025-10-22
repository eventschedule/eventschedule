<x-app-admin-layout>
    @php
        $timezone = auth()->user()->timezone ?? config('app.timezone');
        $startDisplay = $startAt ? $startAt->copy()->locale(app()->getLocale())->translatedFormat('M j, Y • g:i A') : null;
        $endDisplay = $endAt ? $endAt->copy()->locale(app()->getLocale())->translatedFormat('M j, Y • g:i A') : null;
        $talentNames = $talents->map->translatedName()->implode(', ');
        $curatorNames = $curators->map->translatedName()->implode(', ');
        $hasTickets = $event->tickets_enabled && $event->tickets->count() > 0;
        $hasLimitedTickets = $event->hasLimitedTickets();
        $totalTicketCapacity = $hasLimitedTickets ? $event->getTotalTicketQuantity() : null;
        $remainingTicketCapacity = $hasLimitedTickets ? $event->getRemainingTicketQuantity() : null;
        $guestUrl = $event->getGuestUrl(false, null, null, true);
        $cleanGuestUrl = $guestUrl ? \App\Utils\UrlUtils::clean($guestUrl) : null;
        $sales = $sales ?? collect();
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
                            @if (auth()->user()->canEditEvent($event) || auth()->user()->isAdmin())
                                <a href="{{ route('events.clone.confirm', ['hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}"
                                   class="inline-flex items-center rounded-md border border-transparent bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700">
                                    {{ __('messages.clone_event') }}
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
                                <div class="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2">
                                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('messages.total_tickets') }}</p>
                                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            @if ($hasLimitedTickets && ! is_null($totalTicketCapacity))
                                                {{ number_format($totalTicketCapacity) }}
                                            @else
                                                {{ __('messages.unlimited') }}
                                            @endif
                                        </p>
                                    </div>
                                    <div class="rounded-lg bg-gray-50 p-4 dark:bg-gray-900/60">
                                        <p class="text-sm font-medium text-gray-600 dark:text-gray-300">{{ __('messages.available_tickets') }}</p>
                                        <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">
                                            @if ($hasLimitedTickets && ! is_null($remainingTicketCapacity))
                                                {{ number_format($remainingTicketCapacity) }}
                                            @else
                                                {{ __('messages.unlimited') }}
                                            @endif
                                        </p>
                                    </div>
                                </div>
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

                                <div class="mt-8">
                                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                        {{ __('messages.ticket_purchasers') }}
                                    </h3>

                                    @if ($sales->isEmpty())
                                        <p class="mt-3 text-sm text-gray-600 dark:text-gray-300">
                                            {{ __('messages.no_ticket_purchasers') }}
                                        </p>
                                    @else
                                        <div class="mt-4 overflow-x-auto">
                                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                                <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-200">
                                                    <tr>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.name') }}</th>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.email') }}</th>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.tickets') }}</th>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.total') }}</th>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.payment_method') }}</th>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.status') }}</th>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.ticket_usage') }}</th>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.date') }}</th>
                                                        <th scope="col" class="px-4 py-2 text-left font-medium">{{ __('messages.actions') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                                    @foreach ($sales as $sale)
                                                        @php
                                                            $ticketSummary = $sale->saleTickets
                                                                ->map(function ($saleTicket) {
                                                                    $type = $saleTicket->ticket?->type ?: __('messages.ticket');

                                                                    return $type . ' × ' . $saleTicket->quantity;
                                                                })
                                                                ->filter()
                                                                ->implode(', ');

                                                            if (! $ticketSummary) {
                                                                $ticketSummary = __('messages.none');
                                                            }

                                                            $totalAmount = $sale->payment_amount ?? $sale->calculateTotal();
                                                            $formattedAmount = $event->ticket_currency_code
                                                                ? $event->ticket_currency_code . ' ' . number_format($totalAmount, 2)
                                                                : number_format($totalAmount, 2);

                                                            $saleCreatedAt = $sale->created_at
                                                                ? $sale->created_at
                                                                    ->copy()
                                                                    ->timezone($timezone)
                                                                    ->locale(app()->getLocale())
                                                                    ->translatedFormat('M j, Y • g:i A')
                                                                : null;
                                                        @endphp
                                                        <tr class="bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200">
                                                            <td class="px-4 py-2 align-top">
                                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $sale->name }}</div>
                                                            </td>
                                                            <td class="px-4 py-2 align-top">
                                                                <a href="mailto:{{ $sale->email }}" class="text-blue-600 hover:underline dark:text-blue-400">{{ $sale->email }}</a>
                                                            </td>
                                                            <td class="px-4 py-2 align-top">{{ $ticketSummary }}</td>
                                                            <td class="px-4 py-2 align-top">{{ $formattedAmount }}</td>
                                                            <td class="px-4 py-2 align-top">{{ $sale->payment_method ? __('messages.' . $sale->payment_method) : __('messages.none') }}</td>
                                                            <td class="px-4 py-2 align-top">
                                                                @php
                                                                    $statusClasses = match (true) {
                                                                        $sale->status === 'paid' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
                                                                        $sale->status === 'unpaid' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
                                                                        in_array($sale->status, ['cancelled', 'refunded', 'expired']) => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                                                                        default => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                                                                    };
                                                                    $usageStatus = $sale->usage_status;
                                                                    $usageClasses = $usageStatus === 'used'
                                                                        ? 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-200'
                                                                        : 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200';
                                                                @endphp
                                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusClasses }}">
                                                                    {{ __('messages.' . $sale->status) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-2 align-top">
                                                                <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $usageClasses }}">
                                                                    {{ __('messages.ticket_status_' . $usageStatus) }}
                                                                </span>
                                                            </td>
                                                            <td class="px-4 py-2 align-top">{{ $saleCreatedAt ?? __('messages.none') }}</td>
                                                            <td class="px-4 py-2 align-top">
                                                                <div class="flex flex-wrap gap-2">
                                                                    <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}"
                                                                       target="_blank"
                                                                       class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                                                        {{ __('messages.view') }}
                                                                    </a>

                                                                    @if ($sale->status === 'unpaid')
                                                                        <form method="POST" action="{{ route('sales.action', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}">
                                                                            @csrf
                                                                            <input type="hidden" name="action" value="mark_paid">
                                                                            <button type="submit"
                                                                                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                                                                {{ __('messages.mark_paid') }}
                                                                            </button>
                                                                        </form>
                                                                    @endif

                                                                    @if (in_array($sale->status, ['unpaid', 'paid']))
                                                                        <form method="POST" action="{{ route('sales.action', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}">
                                                                            @csrf
                                                                            <input type="hidden" name="action" value="cancel">
                                                                            <button type="submit"
                                                                                class="inline-flex items-center rounded-md border border-gray-300 px-3 py-1 text-xs font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-600 dark:text-gray-200 dark:hover:bg-gray-700">
                                                                                {{ __('messages.cancel') }}
                                                                            </button>
                                                                        </form>
                                                                    @endif

                                                                    @if (! $sale->is_deleted)
                                                                        <form method="POST" action="{{ route('sales.action', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}">
                                                                            @csrf
                                                                            <input type="hidden" name="action" value="delete">
                                                                            <button type="submit"
                                                                                class="inline-flex items-center rounded-md border border-red-300 px-3 py-1 text-xs font-medium text-red-600 hover:bg-red-50 dark:border-red-500 dark:text-red-400 dark:hover:bg-red-900/40">
                                                                                {{ __('messages.delete') }}
                                                                            </button>
                                                                        </form>
                                                                    @endif
                                                                </div>
                                                            </td>
                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    @endif
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
