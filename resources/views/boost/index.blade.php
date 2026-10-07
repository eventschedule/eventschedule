<x-app-admin-layout>

    <x-slot name="head">
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
        @include('boost.partials.styles')
    </x-slot>

    @php
        // A campaign can only be started where a channel exists to carry it. With neither set up
        // the button used to open a dialog that had an event picker and no way on.
        $boostCanStart = \App\Services\MetaAdsService::isBoostConfigured() || \App\Services\PromotionService::isEnabled();
        // Boost is part of the Pro plan. The schedule the gate speaks about is the one picked,
        // or, with none picked, the first of somebody whose schedules are all on the free plan:
        // they used to get an empty list and a dialog saying they had no upcoming events.
        $boostGateRole = $selectedRole
            ? ($selectedRole->isPro() ? null : $selectedRole)
            : ($roles->isNotEmpty() && ! $roles->contains(fn ($r) => $r->isPro()) ? $roles->first() : null);
    @endphp

    {{-- The campaigns this person started, newest first. One list for every width (.page-table):
         it was a wall of cards, each with its own coloured pill. --}}
    <div class="page-shell">
        <x-page-header :title="__('messages.boost')" :lead="__('messages.boost_lead')">
            <x-slot name="actions">
                @if ($roles->count() > 1)
                <div class="boost-schedule-pick">
                    <label for="role-filter" class="sr-only">{{ __('messages.schedule') }}</label>
                    <select id="role-filter" data-searchable
                        class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-base">
                        <option value="">{{ __('messages.all_schedules') }}</option>
                        @foreach ($roles as $r)
                            <option value="{{ \App\Utils\UrlUtils::encodeId($r->id) }}" {{ $selectedRole && $selectedRole->id == $r->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if ($boostCanStart && ! $boostGateRole)
                {{-- The button is drawn here and again by the Vue island that takes this element
                     over, so the title row does not jump when the script arrives. --}}
                <div id="boost-modal-app">
                    <x-brand-button class="whitespace-nowrap">{{ __('messages.boost_event') }}</x-brand-button>
                </div>
                @endif
            </x-slot>
        </x-page-header>

        <div class="page-stack">
            <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

            @if ($boostGateRole)
            <x-plan-gate tier="pro" :title="__('messages.boost')" :role="$boostGateRole" :subdomain="$boostGateRole->subdomain"
                :canUpgrade="auth()->user()->id == $boostGateRole->user_id">
                {{ __('messages.boost_plan_gate') }}
            </x-plan-gate>
            @elseif (! $boostCanStart)
            <x-page-notice tone="info">{{ __('messages.boost_not_set_up') }}</x-page-notice>
            @endif

            @if ($campaigns->count() > 0)
            <div class="ap-card rounded-xl overflow-hidden">
                <table class="page-table is-hover">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.event') }}</th>
                            <th scope="col">{{ __('messages.status') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.impressions') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.clicks') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.spend') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.promotion_budget') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($campaigns as $campaign)
                            @include('boost.partials.campaign-row', ['campaign' => $campaign])
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($campaigns->hasPages())
            <div class="page-pager">
                {{ $campaigns->withQueryString()->links() }}
            </div>
            @endif
            @elseif (! $boostGateRole)
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.no_boost_campaigns')" :text="__('messages.boost_empty_description')"
                    icon="M15.59 14.37a6 6 0 01-5.84 7.38v-4.8m5.84-2.58a14.98 14.98 0 006.16-12.12A14.98 14.98 0 009.631 8.41m5.96 5.96a14.926 14.926 0 01-5.841 2.58m-.119-8.54a6 6 0 00-7.381 5.84h4.8m2.581-5.84a14.927 14.927 0 00-2.58 5.84m2.699 2.7c-.103.021-.207.041-.311.06a15.09 15.09 0 01-2.448-2.448 14.9 14.9 0 01.06-.312m-2.24 2.39a4.493 4.493 0 00-1.757 4.306 4.493 4.493 0 004.306-1.758M16.5 9a1.5 1.5 0 11-3 0 1.5 1.5 0 013 0z" />
            </div>
            @endif
        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.getElementById('role-filter')?.addEventListener('change', function() {
            const roleId = this.value;
            const url = new URL(window.location.href);
            if (roleId) {
                url.searchParams.set('role_id', roleId);
            } else {
                url.searchParams.delete('role_id');
            }
            // A page of the longer list may not exist in the shorter one.
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    </script>

    @if ($boostCanStart && ! $boostGateRole)
    <script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof Vue === 'undefined') return;
        const { createApp, ref, computed } = Vue;

        const app = createApp({
            setup() {
                const showModal = ref(false);
                const events = ref(@json($eventsData));
                const selectedEventId = ref(null);
                const dropdownOpen = ref(false);

                const selectedEvent = computed(() => {
                    return events.value.find(e => e.id === selectedEventId.value) || null;
                });

                function toggleDropdown() {
                    dropdownOpen.value = !dropdownOpen.value;
                }

                function closeDropdown() {
                    dropdownOpen.value = false;
                }

                function onEventChange(eventId) {
                    selectedEventId.value = eventId;
                    closeDropdown();
                }

                function openModal() {
                    showModal.value = true;
                    selectedEventId.value = null;
                    dropdownOpen.value = false;
                }

                function closeModal() {
                    showModal.value = false;
                    selectedEventId.value = null;
                    dropdownOpen.value = false;
                }

                // channel: 'meta' buys Facebook/Instagram ads, 'network' buys placement on
                // free schedules' pages here. Two destinations, one event picker.
                function boostEvent(channel) {
                    if (!selectedEvent.value) return;
                    const base = channel === 'network'
                        ? @json(route('promotions.create'))
                        : @json(route('boost.create'));
                    const params = new URLSearchParams();
                    params.set('event_id', selectedEvent.value.id);
                    if (selectedEvent.value.role_id) {
                        params.set('role_id', selectedEvent.value.role_id);
                    }
                    window.location.href = base + '?' + params.toString();
                }

                function handleClickOutside(e) {
                    const el = document.getElementById('event-selector-dropdown');
                    if (el && !el.contains(e.target)) {
                        closeDropdown();
                    }
                }

                function handleEscape(e) {
                    if (e.key === 'Escape') {
                        if (dropdownOpen.value) {
                            closeDropdown();
                        } else if (showModal.value) {
                            closeModal();
                        }
                    }
                }

                document.addEventListener('click', handleClickOutside);
                document.addEventListener('keydown', handleEscape);

                return {
                    showModal, events, selectedEventId, selectedEvent, dropdownOpen,
                    toggleDropdown, closeDropdown, onEventChange,
                    openModal, closeModal, boostEvent,
                };
            },
            template: `
<div>
    <x-brand-button @click="openModal" class="whitespace-nowrap">{{ __('messages.boost_event') }}</x-brand-button>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeModal">
        <div class="boost-dialog" role="dialog" aria-modal="true" aria-labelledby="boost-dialog-title" @click.stop>
            <div class="boost-dialog-head">
                <h2 id="boost-dialog-title" class="page-card-title">{{ __('messages.boost_event') }}</h2>
                <button type="button" @click="closeModal" class="boost-dialog-close" aria-label="{{ __('messages.close') }}">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <template v-if="events.length > 0">
                <div class="boost-dialog-body">
                    <x-event-selector />
                </div>
                {{-- Where the campaign runs is the step that goes on, so each channel is a button:
                     the one that leaves this site last. --}}
                <div class="page-form-actions boost-dialog-foot">
                    @if (\App\Services\PromotionService::isEnabled())
                    <button type="button" @click="boostEvent('network')" :disabled="!selectedEvent"
                        class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-50 disabled:cursor-not-allowed">
                        {{ __('messages.promotion_channel_network') }}
                    </button>
                    @endif
                    @if (\App\Services\MetaAdsService::isBoostConfigured())
                    <x-brand-button @click="boostEvent('meta')" ::disabled="!selectedEvent">
                        {{ __('messages.promotion_channel_meta') }}
                    </x-brand-button>
                    @endif
                </div>
            </template>
            <x-page-empty v-else :title="__('messages.no_upcoming_events')" :text="__('messages.boost_no_upcoming_events')"
                icon="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
        </div>
    </div>
</div>
`
        });

        app.mount('#boost-modal-app');
    });
    </script>
    @endif

</x-app-admin-layout>
