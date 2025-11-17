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
                            <div class="relative" x-data="{
                                open: false,
                                positionDropdown() {
                                    if (!this.open) return;
                                    const button = this.$refs.button;
                                    const dropdown = this.$refs.dropdown;
                                    const rect = button.getBoundingClientRect();

                                    dropdown.style.position = 'fixed';
                                    dropdown.style.top = `${rect.bottom + 4}px`;
                                    dropdown.style.left = `${rect.left}px`;
                                    dropdown.style.zIndex = '1000';
                                },
                                submitDelete() {
                                    if (confirm('{{ __('messages.are_you_sure') }}')) {
                                        this.$refs.deleteForm.submit();
                                    }
                                }
                            }">
                                <button x-on:click="open = !open; $nextTick(() => positionDropdown())"
                                        x-ref="button"
                                        type="button"
                                        class="inline-flex items-center rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-900 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors duration-150 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700">
                                    {{ __('messages.select_action') }}
                                    <svg class="ml-2 h-5 w-5" viewBox="0 0 20 20" fill="currentColor">
                                        <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                    </svg>
                                </button>

                                <div x-show="open"
                                     x-ref="dropdown"
                                     x-on:click.away="open = false"
                                     class="w-56 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800"
                                     role="menu"
                                     x-cloak
                                     aria-orientation="vertical">
                                    @if ($guestUrl)
                                        <a href="{{ $guestUrl }}" target="_blank"
                                           x-on:click="open = false"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150 dark:text-gray-200 dark:hover:bg-gray-700"
                                           role="menuitem">
                                            {{ __('messages.view_event') }}
                                        </a>
                                    @endif

                                    @if (auth()->user()->canEditEvent($event) || auth()->user()->isAdmin())
                                        <a href="{{ route('events.clone.confirm', ['hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}"
                                           x-on:click="open = false"
                                           class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150 dark:text-gray-200 dark:hover:bg-gray-700"
                                           role="menuitem">
                                            {{ __('messages.clone_event') }}
                                        </a>
                                    @endif

                                    <a href="{{ route('event.edit_admin', ['hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}"
                                       x-on:click="open = false"
                                       class="block px-4 py-2 text-sm text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150 dark:text-gray-200 dark:hover:bg-gray-700"
                                       role="menuitem">
                                        {{ __('messages.edit') }}
                                    </a>

                                    <button type="button"
                                            x-on:click="open = false; submitDelete()"
                                            class="block w-full px-4 py-2 text-left text-sm text-red-700 hover:bg-gray-100 transition-colors duration-150 dark:text-red-300 dark:hover:bg-gray-700"
                                            role="menuitem">
                                        {{ __('messages.delete') }}
                                    </button>
                                </div>

                                <form x-ref="deleteForm" method="POST" action="{{ route('events.destroy', ['hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="hidden">
                                    @csrf
                                    @method('DELETE')
                                </form>
                            </div>
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
                                        <div class="mt-4 flex flex-wrap items-center justify-end gap-3">
                                            <div class="flex w-full sm:w-auto justify-end">
                                                <x-dropdown align="right" width="48">
                                                    <x-slot name="trigger">
                                                        <button type="button"
                                                            class="inline-flex w-full sm:w-auto items-center justify-between gap-2 rounded-md border border-gray-200 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA] dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-800">
                                                            <span class="inline-flex items-center gap-2">
                                                                <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M4 16.5v2.25A1.25 1.25 0 005.25 20h13.5A1.25 1.25 0 0020 18.75V16.5M16 12l-4 4m0 0l-4-4m4 4V3" />
                                                                </svg>
                                                                {{ __('messages.export') }}
                                                            </span>
                                                            <svg class="h-4 w-4 text-gray-400" viewBox="0 0 20 20" fill="currentColor" aria-hidden="true">
                                                                <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 10.94l3.71-3.71a.75.75 0 111.06 1.06l-4.24 4.25a.75.75 0 01-1.06 0L5.21 8.27a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                            </svg>
                                                        </button>
                                                    </x-slot>

                                                    <x-slot name="content">
                                                        <x-dropdown-link :href="route('events.sales.export', ['hash' => \App\Utils\UrlUtils::encodeId($event->id), 'format' => 'csv'])">
                                                            {{ __('messages.export_csv') }}
                                                        </x-dropdown-link>
                                                        <x-dropdown-link :href="route('events.sales.export', ['hash' => \App\Utils\UrlUtils::encodeId($event->id), 'format' => 'xlsx'])">
                                                            {{ __('messages.export_excel') }}
                                                        </x-dropdown-link>
                                                    </x-slot>
                                                </x-dropdown>
                                            </div>
                                        </div>
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
                                                                    $entryDetails = $sale->saleTickets->flatMap(function ($saleTicket) {
                                                                        $ticketLabel = $saleTicket->ticket->type ?: __('messages.ticket');

                                                                        return $saleTicket->entries->map(function ($entry) use ($ticketLabel) {
                                                                            return [
                                                                                'id' => $entry->id,
                                                                                'label' => $ticketLabel . ' #' . $entry->seat_number,
                                                                                'seat' => (int) $entry->seat_number,
                                                                                'used' => $entry->scanned_at !== null,
                                                                                'used_at' => $entry->scanned_at,
                                                                            ];
                                                                        });
                                                                    })->sortBy('seat')->values();
                                                                    $unusedEntries = $entryDetails->filter(fn ($entry) => ! $entry['used']);
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
                                                                    <div class="relative"
                                                                     x-data="{
                                                                        open: false,
                                                                        positionDropdown() {
                                                                            if (!this.open) return;
                                                                            const button = this.$refs.button;
                                                                            const dropdown = this.$refs.dropdown;
                                                                            const rect = button.getBoundingClientRect();

                                                                            dropdown.style.position = 'fixed';
                                                                            dropdown.style.top = `${rect.bottom + 4}px`;
                                                                            dropdown.style.left = `${rect.left}px`;
                                                                            dropdown.style.zIndex = '1000';
                                                                        }
                                                                    }">
                                                                    <button x-on:click="open = !open; $nextTick(() => positionDropdown())"
                                                                            x-ref="button"
                                                                            type="button"
                                                                            class="inline-flex items-center rounded-md bg-white px-3 py-1.5 text-xs font-medium text-gray-700 shadow-sm ring-1 ring-inset ring-gray-300 hover:bg-gray-50 transition-colors duration-150 dark:bg-gray-900 dark:text-gray-200 dark:hover:bg-gray-700">
                                                                        {{ __('messages.select_action') }}
                                                                        <svg class="ml-2 h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                                                                            <path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" />
                                                                        </svg>
                                                                    </button>

                                                                    <div x-show="open"
                                                                         x-ref="dropdown"
                                                                         x-on:click.away="open = false"
                                                                         class="w-48 origin-top-right rounded-md bg-white py-1 shadow-lg ring-1 ring-black ring-opacity-5 focus:outline-none dark:bg-gray-800"
                                                                         role="menu"
                                                                         x-cloak
                                                                         aria-orientation="vertical">
                                                                        <a href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($sale->event_id), 'secret' => $sale->secret]) }}"
                                                                           target="_blank"
                                                                           x-on:click="open = false"
                                                                           class="block px-4 py-2 text-xs text-gray-700 hover:bg-gray-100 w-full text-left transition-colors duration-150 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                           role="menuitem">
                                                                            {{ __('messages.view') }}
                                                                        </a>

                                                                        @if ($unusedEntries->isNotEmpty())
                                                                            <button type="button"
                                                                                    x-on:click="$dispatch('open-modal', 'mark-ticket-used-{{ $sale->id }}'); open = false"
                                                                                    class="block w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100 transition-colors duration-150 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                                    role="menuitem">
                                                                                {{ __('messages.mark_tickets_as_used') }}
                                                                            </button>
                                                                        @endif

                                                                        @if ($sale->status === 'unpaid')
                                                                            <form method="POST" action="{{ route('sales.action', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}">
                                                                                @csrf
                                                                                <input type="hidden" name="action" value="mark_paid">
                                                                                <button type="submit"
                                                                                        x-on:click="open = false"
                                                                                        class="block w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100 transition-colors duration-150 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                                        role="menuitem">
                                                                                    {{ __('messages.mark_paid') }}
                                                                                </button>
                                                                            </form>
                                                                        @endif

                                                                        @if (in_array($sale->status, ['unpaid', 'paid']))
                                                                            <form method="POST" action="{{ route('sales.action', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}">
                                                                                @csrf
                                                                                <input type="hidden" name="action" value="cancel">
                                                                                <button type="submit"
                                                                                        x-on:click="if (!confirm(@js(__('messages.are_you_sure')))) { $event.preventDefault(); return; } open = false"
                                                                                        class="block w-full px-4 py-2 text-left text-xs text-gray-700 hover:bg-gray-100 transition-colors duration-150 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                                        role="menuitem">
                                                                                    {{ __('messages.cancel') }}
                                                                                </button>
                                                                            </form>
                                                                        @endif

                                                                        @if (! $sale->is_deleted)
                                                                            <form method="POST" action="{{ route('sales.action', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}">
                                                                                @csrf
                                                                                <input type="hidden" name="action" value="delete">
                                                                                <button type="submit"
                                                                                        x-on:click="if (!confirm(@js(__('messages.are_you_sure')))) { $event.preventDefault(); return; } open = false"
                                                                                        class="block w-full px-4 py-2 text-left text-xs text-red-700 hover:bg-gray-100 transition-colors duration-150 dark:text-red-300 dark:hover:bg-gray-700"
                                                                                        role="menuitem">
                                                                                    {{ __('messages.delete') }}
                                                                                </button>
                                                                            </form>
                                                                        @endif
                                                                    </div>
                                                                </div>
                                                            </td>
                                                        </tr>
                                                        @if ($unusedEntries->isNotEmpty())
                                                            <x-modal name="mark-ticket-used-{{ $sale->id }}" focusable>
                                                                <form method="POST" action="{{ route('sales.mark_used', ['sale_id' => \App\Utils\UrlUtils::encodeId($sale->id)]) }}" class="p-6" x-data="{ mode: 'all' }">
                                                                    @csrf
                                                                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                                                        {{ __('messages.mark_tickets_as_used') }}
                                                                    </h2>
                                                                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                                        {{ __('messages.mark_tickets_as_used_description') }}
                                                                    </p>

                                                                    <div class="mt-4 space-y-3">
                                                                        <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200">
                                                                            <input type="radio" name="mode" value="all" class="mt-1" x-model="mode">
                                                                            <span>{{ __('messages.mark_all_tickets_as_used') }}</span>
                                                                        </label>

                                                                        <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200">
                                                                            <input type="radio" name="mode" value="partial" class="mt-1" x-model="mode">
                                                                            <span>{{ __('messages.choose_specific_tickets_to_mark_as_used') }}</span>
                                                                        </label>
                                                                    </div>

                                                                    <div class="mt-4" x-show="mode === 'partial'" x-cloak>
                                                                        <div class="max-h-48 overflow-y-auto divide-y divide-gray-200 dark:divide-gray-700">
                                                                            @foreach ($entryDetails as $entry)
                                                                                <label class="flex items-center justify-between gap-4 py-2 text-sm text-gray-700 dark:text-gray-200">
                                                                                    <div>
                                                                                        <div class="font-medium">{{ $entry['label'] }}</div>
                                                                                        @if ($entry['used'])
                                                                                            <div class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.already_marked_as_used') }}</div>
                                                                                        @endif
                                                                                    </div>
                                                                                    <input type="checkbox" name="entries[]" value="{{ $entry['id'] }}" class="h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                                                                                           @if ($entry['used']) disabled @endif>
                                                                                </label>
                                                                            @endforeach
                                                                        </div>
                                                                        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.used_tickets_cannot_be_selected') }}</p>
                                                                    </div>

                                                                    <div class="mt-6 flex justify-end gap-3">
                                                                        <button type="button"
                                                                                class="inline-flex items-center rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 transition-colors duration-150 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-200 dark:hover:bg-gray-700"
                                                                                x-on:click="$dispatch('close-modal', 'mark-ticket-used-{{ $sale->id }}')">
                                                                            {{ __('messages.cancel') }}
                                                                        </button>
                                                                        <x-primary-button>{{ __('messages.mark_as_used') }}</x-primary-button>
                                                                    </div>
                                                                </form>
                                                            </x-modal>
                                                        @endif
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

                <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                    @php
                        $guestListEnabled = (bool) old('show_guest_list', $event->show_guest_list);
                        $guestListVisibility = old('guest_list_visibility', $event->guest_list_visibility ?? 'paid');
                    @endphp
                    <div class="px-6 py-5 space-y-6">
                        <div>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.public_guest_list') }}</h2>
                            <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">{{ __('messages.public_guest_list_description') }}</p>
                        </div>

                        <form method="POST" action="{{ route('events.guest_list.update', ['hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="space-y-6">
                            @csrf
                            <div class="flex items-start gap-3">
                                <input type="hidden" name="show_guest_list" value="0">
                                <input id="show_guest_list" name="show_guest_list" type="checkbox" value="1" {{ $guestListEnabled ? 'checked' : '' }}
                                    class="mt-1 h-4 w-4 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                                <label for="show_guest_list" class="text-sm text-gray-700 dark:text-gray-200">
                                    <span class="font-medium">{{ __('messages.enable_public_guest_list') }}</span>
                                    <span class="mt-1 block text-gray-500 dark:text-gray-400">{{ __('messages.enable_public_guest_list_help') }}</span>
                                </label>
                            </div>

                            <div class="space-y-3 {{ $guestListEnabled ? '' : 'opacity-50 pointer-events-none' }}">
                                <p class="text-sm font-medium text-gray-900 dark:text-gray-100">{{ __('messages.guest_list_visibility_label') }}</p>
                                <div class="space-y-3">
                                    <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200">
                                        <input type="radio" name="guest_list_visibility" value="paid" class="mt-1 h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $guestListVisibility === 'paid' ? 'checked' : '' }}>
                                        <span>
                                            <span class="font-medium block">{{ __('messages.guest_list_visibility_paid') }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.guest_list_visibility_paid_help') }}</span>
                                        </span>
                                    </label>
                                    <label class="flex items-start gap-3 text-sm text-gray-700 dark:text-gray-200">
                                        <input type="radio" name="guest_list_visibility" value="all" class="mt-1 h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-500" {{ $guestListVisibility === 'all' ? 'checked' : '' }}>
                                        <span>
                                            <span class="font-medium block">{{ __('messages.guest_list_visibility_all') }}</span>
                                            <span class="text-xs text-gray-500 dark:text-gray-400">{{ __('messages.guest_list_visibility_all_help') }}</span>
                                        </span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-end">
                                <x-primary-button>{{ __('messages.save_changes') }}</x-primary-button>
                            </div>
                        </form>
                    </div>
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
