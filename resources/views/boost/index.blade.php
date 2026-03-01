<x-app-admin-layout>

    @if ($eventsData->isNotEmpty())
    <x-slot name="head">
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
    </x-slot>
    @endif

    <div class="max-w-7xl mx-auto">
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ __('messages.boost') }}</h1>

            <div class="flex items-center gap-3">
                @if ($roles->isNotEmpty())
                <div class="min-w-[200px] max-w-xs">
                    <select id="role-filter"
                        class="block w-full rounded-md border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-base">
                        <option value="">{{ __('messages.all_schedules') }}</option>
                        @foreach ($roles as $r)
                            <option value="{{ \App\Utils\UrlUtils::encodeId($r->id) }}" {{ $selectedRole && $selectedRole->id == $r->id ? 'selected' : '' }}>
                                {{ $r->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                @endif

                @if ($eventsData->isNotEmpty())
                <div id="boost-modal-app">
                    <boost-modal-app></boost-modal-app>
                </div>
                @endif
            </div>
        </div>

        @if (session('success'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-md text-green-700 dark:text-green-300">
            {{ session('success') }}
        </div>
        @endif

        @if (session('error'))
        <div class="mb-4 p-4 bg-red-50 dark:bg-red-900/30 border border-red-200 dark:border-red-800 rounded-md text-red-700 dark:text-red-300">
            {{ session('error') }}
        </div>
        @endif

        @if ($campaigns->count() > 0)
        <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
            @foreach ($campaigns as $campaign)
                @include('boost.partials.campaign-card', ['campaign' => $campaign])
            @endforeach
        </div>

        <div class="mt-6">
            {{ $campaigns->withQueryString()->links() }}
        </div>
        @else
        <div class="text-center py-12">
            <svg class="mx-auto h-12 w-12 text-gray-400" viewBox="0 0 24 24" fill="currentColor">
                <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4M19.22 2C19.06 2 18.88 2 18.7 2.04C16.56 2.11 13.5 3.31 10.77 6.04C8.95 7.87 7.57 10.04 6.63 12.46C6.37 13.1 6.55 13.85 7.07 14.33L9.65 16.91C10.13 17.42 10.87 17.61 11.53 17.35C13.95 16.42 16.12 15.04 17.95 13.22C20.67 10.5 21.88 7.44 21.95 5.3C22.04 3.5 20.87 2 19.22 2M14.54 9.46C13.76 8.68 13.76 7.41 14.54 6.63S16.59 5.85 17.37 6.63C18.14 7.41 18.15 8.68 17.37 9.46C16.59 10.24 15.32 10.24 14.54 9.46M8.88 16.53L7.47 15.12L8.88 16.53M6.24 22L8.4 20.46L7.18 19.28L6.24 22M2 18L3.54 15.6L4.72 16.82L2 18Z"/>
            </svg>
            <h3 class="mt-2 text-sm font-semibold text-gray-900 dark:text-white">{{ __('messages.no_boost_campaigns') }}</h3>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.boost_empty_description') }}</p>
        </div>
        @endif
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
            window.location.href = url.toString();
        });
    </script>

    @if ($eventsData->isNotEmpty())
    <script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function() {
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

                function boostEvent() {
                    if (!selectedEvent.value) return;
                    const base = @json(route('boost.create'));
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
    <button @click="openModal" class="inline-flex items-center gap-2 px-4 py-3 bg-[#4E81FA] hover:bg-[#3a6de0] border border-transparent rounded-md font-semibold text-base text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-[#1e1e1e] transition ease-in-out duration-150 hover:scale-105 hover:shadow-lg whitespace-nowrap">
        <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
            <path d="M13.13 22.19L11.5 18.36C13.07 17.78 14.54 17 15.9 16.09L13.13 22.19M5.64 12.5L1.81 10.87L7.91 8.1C7 9.46 6.22 10.93 5.64 12.5M19.22 4C19.5 4 19.75 4 19.96 4.05C20.13 5.44 19.94 8.3 16.66 11.58C14.96 13.29 12.93 14.6 10.65 15.47L8.5 13.37C9.42 11.06 10.73 9.03 12.42 7.34C14.71 5.05 17.11 4.1 18.78 4.04C18.91 4 19.06 4 19.22 4M19.22 2C19.06 2 18.88 2 18.7 2.04C16.56 2.11 13.5 3.31 10.77 6.04C8.95 7.87 7.57 10.04 6.63 12.46C6.37 13.1 6.55 13.85 7.07 14.33L9.65 16.91C10.13 17.42 10.87 17.61 11.53 17.35C13.95 16.42 16.12 15.04 17.95 13.22C20.67 10.5 21.88 7.44 21.95 5.3C22.04 3.5 20.87 2 19.22 2M14.54 9.46C13.76 8.68 13.76 7.41 14.54 6.63S16.59 5.85 17.37 6.63C18.14 7.41 18.15 8.68 17.37 9.46C16.59 10.24 15.32 10.24 14.54 9.46M8.88 16.53L7.47 15.12L8.88 16.53M6.24 22L8.4 20.46L7.18 19.28L6.24 22M2 18L3.54 15.6L4.72 16.82L2 18Z"/>
        </svg>
        {{ __('messages.boost_event') }}
    </button>

    <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50 backdrop-blur-sm" @click.self="closeModal">
        <div class="bg-white dark:bg-[#1e1e1e] rounded-xl shadow-xl max-w-md w-full mx-4" @click.stop>
            <div class="flex items-center justify-between px-5 pt-5 pb-3">
                <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.boost_event') }}</h3>
                <button @click="closeModal" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300 transition-colors">
                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <div class="px-5 pb-3">
                <x-event-selector />
            </div>
            <div class="flex justify-end px-5 pb-5 pt-2">
                <button @click="boostEvent" :disabled="!selectedEvent"
                    class="inline-flex items-center gap-2 px-5 py-2 bg-[#4E81FA] hover:bg-[#3a6de0] border border-transparent rounded-md font-semibold text-sm text-white shadow-sm focus:outline-none focus:ring-2 focus:ring-[#4E81FA] focus:ring-offset-2 dark:focus:ring-offset-[#1e1e1e] transition ease-in-out duration-150 disabled:opacity-50 disabled:cursor-not-allowed">
                    {{ __('messages.boost') }}
                </button>
            </div>
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
