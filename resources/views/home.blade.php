<x-app-admin-layout>
    @php
        $schedules = isset($schedules) ? $schedules : collect();
        $venues = isset($venues) ? $venues : collect();
        $curators = isset($curators) ? $curators : collect();
        $calendarEvents = isset($calendarEvents) ? $calendarEvents : collect();
    @endphp
    @php
        $creationRoles = collect([$schedules, $venues, $curators])->flatten()->unique('id')->sortBy('name');
        $creationRoleOptions = $creationRoles->map(function ($role) {
            return [
                'id' => $role->encodeId(),
                'name' => $role->name,
                'type' => __('messages.' . strtolower($role->type)),
                'route' => route('event.create', ['subdomain' => $role->subdomain]),
            ];
        })->values();
        $venueOptions = $venues->map(function ($role) {
            return [
                'id' => $role->encodeId(),
                'name' => $role->name,
            ];
        })->values();
        $talentOptions = $schedules->map(function ($role) {
            return [
                'id' => $role->encodeId(),
                'name' => $role->name,
            ];
        })->values();
        $curatorOptions = $curators->map(function ($role) {
            return [
                'id' => $role->encodeId(),
                'name' => $role->name,
            ];
        })->values();

    @endphp

    <div class="py-5">

        <!-- Get Started Panel -->
        @if($schedules->isEmpty() && $venues->isEmpty() && $curators->isEmpty() && auth()->user()->tickets()->count() === 0)
        <div class="mb-8">
            <!-- Header Panel -->
            <div class="relative bg-gradient-to-br from-slate-50 via-blue-50 to-indigo-100 border border-blue-100/50 rounded-3xl p-10 mb-8 overflow-hidden">
                <div class="absolute inset-0 bg-gradient-radial from-transparent via-blue-50/30 to-indigo-100/40 rounded-3xl"></div>
                <div class="relative text-center">
                    <h2 class="text-4xl font-bold text-gray-900 mb-4 tracking-tight">Welcome {{ auth()->user()->firstName() }}, let's get started...</h2>
                    <p class="text-xl text-gray-600 max-w-3xl mx-auto leading-relaxed font-medium">{{ __('messages.create_your_first_schedule') }}</p>
                </div>
            </div>
            
            <!-- Schedule Types Grid -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 max-w-6xl mx-auto">
                <!-- Talent Card -->
                <div class="group relative bg-gradient-to-br from-purple-50 to-indigo-50 rounded-lg p-6 border border-purple-100 hover:border-purple-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-indigo-500/5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-purple-500 to-indigo-600 rounded-lg mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">{{ __('messages.talent') }}</h3>
                        <p class="text-gray-600 text-center mb-6 leading-relaxed">{{ __('messages.new_schedule_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'talent']) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-purple-500 to-indigo-600 text-white font-medium rounded-xl hover:from-purple-600 hover:to-indigo-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Venue Card -->
                <div class="group relative bg-gradient-to-br from-emerald-50 to-teal-50 rounded-lg p-6 border border-emerald-100 hover:border-emerald-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/5 to-teal-500/5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-lg mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,11.5A2.5,2.5 0 0,1 9.5,9A2.5,2.5 0 0,1 12,6.5A2.5,2.5 0 0,1 14.5,9A2.5,2.5 0 0,1 12,11.5M12,2A7,7 0 0,0 5,9C5,14.25 12,22 12,22C12,22 19,14.25 19,9A7,7 0 0,0 12,2Z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">{{ __('messages.venue') }}</h3>
                        <p class="text-gray-600 text-center mb-6 leading-relaxed">{{ __('messages.new_venue_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'venue']) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-emerald-500 to-teal-600 text-white font-medium rounded-xl hover:from-emerald-600 hover:to-teal-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Curator Card -->
                <div class="group relative bg-gradient-to-br from-amber-50 to-orange-50 rounded-lg p-6 border border-amber-100 hover:border-amber-200 transition-all duration-300 hover:shadow-lg hover:-translate-y-1">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-500/5 to-orange-500/5 rounded-lg opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <div class="relative">
                        <div class="flex items-center justify-center w-16 h-16 bg-gradient-to-br from-amber-500 to-orange-600 rounded-lg mb-4 mx-auto group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M12,19.2C9.5,19.2 7.29,17.92 6,16C6.03,14 10,12.9 12,12.9C14,12.9 17.97,14 18,16C16.71,17.92 14.5,19.2 12,19.2M12,5A3,3 0 0,1 15,8A3,3 0 0,1 12,11A3,3 0 0,1 9,8A3,3 0 0,1 12,5M12,2A10,10 0 0,0 2,12A10,10 0 0,0 12,22A10,10 0 0,0 22,12C22,6.47 17.5,2 12,2Z"/>
                            </svg>
                        </div>
                        <h3 class="text-xl font-semibold text-gray-900 mb-2 text-center">{{ __('messages.curator') }}</h3>
                        <p class="text-gray-600 text-center mb-6 leading-relaxed">{{ __('messages.new_curator_tooltip') }}</p>
                        <div class="text-center">
                            <a href="{{ route('new', ['type' => 'curator']) }}" class="inline-flex items-center justify-center px-6 py-3 bg-gradient-to-r from-amber-500 to-orange-600 text-white font-medium rounded-xl hover:from-amber-600 hover:to-orange-700 transition-all duration-300 transform hover:scale-105 shadow-lg hover:shadow-xl">
                                <svg class="w-5 h-5 mr-2" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M19,13H13V19H11V13H5V11H11V5H13V11H19V13Z"/>
                                </svg>
                                {{ __('messages.create_schedule') }}
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <div class="mb-8 space-y-6">
            <div class="bg-white dark:bg-gray-800 shadow-sm sm:rounded-lg">
                <div class="px-6 py-5 flex flex-col gap-4 md:flex-row md:items-center md:justify-between">
                    <div>
                        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.events') }}</h2>
                    </div>
                    @if ($creationRoles->isNotEmpty())
                        <x-primary-button type="button" @click="$dispatch('open-modal', 'create-event')">
                            {{ __('messages.add_event') }}
                        </x-primary-button>
                    @endif
                </div>

                <div class="border-t border-gray-200 dark:border-gray-700">
                    @if (count($events))
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 text-sm">
                                <thead class="bg-gray-50 dark:bg-gray-900/60 text-gray-700 dark:text-gray-200">
                                    <tr>
                                        <th scope="col" class="px-6 py-3 text-left font-medium">{{ __('messages.event_details') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left font-medium">{{ __('messages.venue') }}</th>
                                        <th scope="col" class="px-6 py-3 text-left font-medium">{{ \Illuminate\Support\Str::plural(__('messages.talent')) }}</th>
                                        <th scope="col" class="px-6 py-3 text-left font-medium">{{ \Illuminate\Support\Str::plural(__('messages.curator')) }}</th>
                                        <th scope="col" class="px-6 py-3 text-right font-medium">{{ __('messages.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200 dark:divide-gray-700 bg-white dark:bg-gray-800">
                                    @foreach ($events as $event)
                                        @php
                                            $startAt = $event->starts_at ? $event->getStartDateTime(null, true) : null;
                                            $dateDisplay = $startAt ? $startAt->locale(app()->getLocale())->translatedFormat('M j, Y • g:i A') : __('messages.unscheduled');
                                            $talentList = $event->roles->filter(fn($role) => $role->isTalent())->map->translatedName()->implode(', ');
                                            $curatorList = $event->roles->filter(fn($role) => $role->isCurator())->map->translatedName()->implode(', ');
                                            $hashedId = \App\Utils\UrlUtils::encodeId($event->id);
                                            $canEdit = auth()->user()->canEditEvent($event);
                                        @endphp
                                        <tr class="text-gray-700 dark:text-gray-200">
                                            <td class="px-6 py-4 align-top">
                                                <div class="font-medium text-gray-900 dark:text-gray-100">{{ $event->translatedName() }}</div>
                                                <div class="mt-1 text-sm text-gray-600 dark:text-gray-300">
                                                    {{ $dateDisplay }}
                                                    @if ($event->days_of_week)
                                                        <span class="ml-2 inline-flex items-center rounded-full bg-indigo-50 px-2 py-0.5 text-xs font-medium text-indigo-700 dark:bg-indigo-900/30 dark:text-indigo-200">{{ __('messages.recurring') }}</span>
                                                    @endif
                                                </div>
                                                @if ($event->tickets_enabled && $event->tickets->isNotEmpty())
                                                    @php
                                                        $hasLimitedTickets = $event->hasLimitedTickets();
                                                        $totalTicketCount = $hasLimitedTickets ? $event->getTotalTicketQuantity() : null;
                                                        $remainingTicketCount = $event->getRemainingTicketQuantity();
                                                        $remainingTicketValue = $hasLimitedTickets ? max($remainingTicketCount ?? 0, 0) : null;
                                                        $totalTicketLabel = $hasLimitedTickets ? number_format($totalTicketCount) : __('messages.unlimited');
                                                        $remainingTicketLabel = $hasLimitedTickets ? number_format($remainingTicketValue) : __('messages.unlimited');
                                                        $remainingBadgeClasses = 'inline-flex items-center gap-2 rounded-full bg-emerald-50 px-3 py-1 text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-200';

                                                        if ($hasLimitedTickets) {
                                                            if ($remainingTicketValue === 0) {
                                                                $remainingBadgeClasses = 'inline-flex items-center gap-2 rounded-full bg-red-50 px-3 py-1 text-red-700 dark:bg-red-900/40 dark:text-red-200';
                                                            } elseif ($remainingTicketValue <= 5) {
                                                                $remainingBadgeClasses = 'inline-flex items-center gap-2 rounded-full bg-amber-50 px-3 py-1 text-amber-700 dark:bg-amber-900/40 dark:text-amber-200';
                                                            }
                                                        }
                                                    @endphp
                                                    <div class="mt-3 flex flex-wrap items-center gap-2 text-xs font-semibold">
                                                        <span class="inline-flex items-center gap-2 rounded-full bg-blue-50 px-3 py-1 text-blue-700 dark:bg-blue-900/40 dark:text-blue-200">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 5.25h16.5a.75.75 0 01.75.75v3a2.25 2.25 0 010 4.5v3a.75.75 0 01-.75.75H3.75a.75.75 0 01-.75-.75v-3a2.25 2.25 0 010-4.5v-3a.75.75 0 01.75-.75z" />
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5.25v13.5m6-13.5v13.5" />
                                                            </svg>
                                                            <span class="tracking-wide">{{ __('messages.total') }} {{ __('messages.tickets') }}: <span class="text-sm font-bold">{{ $totalTicketLabel }}</span></span>
                                                        </span>
                                                        <span class="{{ $remainingBadgeClasses }}">
                                                            <svg class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                                                                <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                                                            </svg>
                                                            <span class="tracking-wide">{{ __('messages.remaining') }} {{ __('messages.tickets') }}: <span class="text-sm font-bold">{{ $remainingTicketLabel }}</span></span>
                                                        </span>
                                                    </div>
                                                @endif
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="text-sm text-gray-700 dark:text-gray-200">
                                                    {{ $event->getVenueDisplayName() ?: __('messages.none') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="text-sm text-gray-700 dark:text-gray-200">
                                                    {{ $talentList ?: __('messages.none') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="text-sm text-gray-700 dark:text-gray-200">
                                                    {{ $curatorList ?: __('messages.none') }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 align-top">
                                                <div class="flex items-center justify-end space-x-3">
                                                    @php
                                                        $eventGuestUrl = rescue(
                                                            fn () => $event->getGuestUrl(false, null, null, true),
                                                            null,
                                                            false
                                                        );
                                                    @endphp

                                                    @if ($canEdit)
                                                        <a href="{{ route('events.view', ['hash' => $hashedId]) }}" target="_blank" rel="noopener noreferrer"
                                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">{{ __('messages.view_event') }}</a>
                                                    @elseif ($eventGuestUrl)
                                                        <a href="{{ $eventGuestUrl }}" target="_blank" rel="noopener noreferrer"
                                                           class="text-blue-600 hover:text-blue-800 dark:text-blue-400 dark:hover:text-blue-300">{{ __('messages.view_event') }}</a>
                                                    @endif
                                                    @if ($canEdit)
                                                        <a href="{{ route('event.edit_admin', ['hash' => $hashedId]) }}"
                                                           class="inline-flex items-center rounded-md bg-[#4E81FA] px-3 py-1.5 text-sm font-medium text-white shadow-sm hover:bg-[#3A6BE0] focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-[#4E81FA]">
                                                            {{ __('messages.edit') }}
                                                        </a>
                                                        <form method="POST" action="{{ route('events.destroy', ['hash' => $hashedId]) }}" onsubmit="return confirm('{{ __('messages.are_you_sure') }}');">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-800 dark:text-red-400 dark:hover:text-red-300">{{ __('messages.delete') }}</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if (method_exists($events, 'hasPages') && $events->hasPages())
                            <div class="px-6 py-4 border-t border-gray-200 dark:border-gray-700">
                                {{ $events->links() }}
                            </div>
                        @endif
                    @else
                        <div class="px-6 py-10 text-center text-sm text-gray-500 dark:text-gray-400">
                            {{ __('messages.no_events_found') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        @include('role/partials/calendar', ['route' => 'home', 'tab' => '', 'events' => $calendarEvents])

        @if ($creationRoles->isNotEmpty())
            <x-modal name="create-event">
                <div class="px-6 py-5">
                    <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">{{ __('messages.add_event') }}</h2>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-300">{{ __('messages.add_to_schedule') }}</p>

                    <form x-data="eventCreateModal({
                            roles: @js($creationRoleOptions),
                            venues: @js($venueOptions),
                            talents: @js($talentOptions),
                            curators: @js($curatorOptions),
                        })"
                        class="mt-5 space-y-5"
                        @submit.prevent="submit">

                        <div>
                            <x-input-label for="event-create-role" :value="__('messages.schedule')" />
                            <select id="event-create-role"
                                    x-model="selectedRole"
                                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                                <option value="">{{ __('messages.please_select') }}</option>
                                <template x-for="role in roles" :key="role.id">
                                    <option :value="role.id" x-text="`${role.name} • ${role.type}`"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="event-create-venue" :value="__('messages.venue') . ' (' . __('messages.optional') . ')'" />
                            <select id="event-create-venue"
                                    x-model="selectedVenue"
                                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                                <option value="">{{ __('messages.please_select') }}</option>
                                <template x-for="venue in venues" :key="venue.id">
                                    <option :value="venue.id" x-text="venue.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="event-create-talent" :value="__('messages.talent') . ' (' . __('messages.optional') . ')'" />
                            <select id="event-create-talent"
                                    x-model="selectedTalents"
                                    multiple
                                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                                <template x-for="talent in talents" :key="talent.id">
                                    <option :value="talent.id" x-text="talent.name"></option>
                                </template>
                            </select>
                        </div>

                        <div>
                            <x-input-label for="event-create-curator" :value="__('messages.curator') . ' (' . __('messages.optional') . ')'" />
                            <select id="event-create-curator"
                                    x-model="selectedCurators"
                                    multiple
                                    class="mt-2 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-100 focus:border-[#4E81FA] focus:ring-[#4E81FA]">
                                <template x-for="curator in curators" :key="curator.id">
                                    <option :value="curator.id" x-text="curator.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="flex justify-end space-x-3">
                            <x-secondary-button type="button" @click="$dispatch('close-modal', 'create-event')">{{ __('messages.cancel') }}</x-secondary-button>
                            <x-primary-button type="submit" x-bind:disabled="! canSubmit">
                                {{ __('messages.next') }}
                            </x-primary-button>
                        </div>
                    </form>
                </div>
            </x-modal>
        @endif

    </div>
</x-app-admin-layout>

<script {!! nonce_attr() !!}>
    function eventCreateModal(config) {
        return {
            roles: config.roles || [],
            venues: config.venues || [],
            talents: config.talents || [],
            curators: config.curators || [],
            selectedRole: '',
            selectedVenue: '',
            selectedTalents: [],
            selectedCurators: [],
            get canSubmit() {
                return this.selectedRole !== '';
            },
            submit() {
                if (!this.canSubmit) {
                    return;
                }

                const role = this.roles.find((item) => item.id === this.selectedRole);

                if (!role) {
                    return;
                }

                let target = role.route;

                try {
                    const url = new URL(target, window.location.origin);

                    if (this.selectedVenue) {
                        url.searchParams.set('venue', this.selectedVenue);
                    }

                    const uniqueTalents = Array.from(new Set(this.selectedTalents));
                    uniqueTalents
                        .filter((id) => id && id !== this.selectedRole)
                        .forEach((id) => url.searchParams.append('talents[]', id));

                    const uniqueCurators = Array.from(new Set(this.selectedCurators));
                    uniqueCurators
                        .filter((id) => id)
                        .forEach((id) => url.searchParams.append('curators[]', id));

                    window.location.href = url.toString();
                } catch (error) {
                    window.location.href = target;
                }
            }
        };
    }
</script>
