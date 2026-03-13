<x-app-admin-layout>

    <x-slot name="head">
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
    </x-slot>

    <div class="flex justify-between items-center mb-6">
            <h2 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ __('messages.segments') }}</h2>
            <a href="{{ route('newsletter.index', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}"
                class="inline-flex items-center justify-center rounded-lg bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                {{ __('messages.back') }}
            </a>
        </div>

        @if (session('status'))
        <div class="mb-4 p-4 bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg text-green-700 dark:text-green-300">
            {{ session('status') }}
        </div>
        @endif

        {{-- Existing Segments --}}
        @if ($segments->count())
        <div class="space-y-4 mb-8">
            @foreach ($segments as $segment)
            <div class="ap-card sm:rounded-xl p-6">
                <div class="flex justify-between items-center">
                    <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 min-w-0">{{ $segment->name }}</h3>
                    <div class="shrink-0 space-x-3">
                        <a href="{{ route('newsletter.segment.edit', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}"
                            class="text-[var(--brand-blue)] hover:text-[var(--brand-blue-dark)] text-sm">{{ __('messages.edit') }}</a>
                        <form method="POST" action="{{ route('newsletter.segment.delete', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($segment->id)]) }}"
                            class="inline js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="text-red-500 hover:text-red-700 text-sm">{{ __('messages.delete') }}</button>
                        </form>
                    </div>
                </div>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">
                    {{ __('messages.type') }}: {{ $segment->type === 'all_followers' ? __('messages.all_followers') : ($segment->type === 'ticket_buyers' ? __('messages.ticket_buyers') : ($segment->type === 'manual' ? __('messages.manual') : ($segment->type === 'waitlist' ? __('messages.waitlist') : __('messages.subschedule')))) }}
                </p>
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.recipients') }}: {{ number_format($segment->recipient_count) }}
                </p>
                @if ($segment->type === 'manual')
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.manual_entries') }}: {{ $segment->segment_users_count }}
                </p>
                @endif
            </div>
            @endforeach
        </div>
        @endif

        {{-- Create New Segment --}}
        <div class="ap-card sm:rounded-xl p-6">
            <h3 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-4">{{ __('messages.create_segment') }}</h3>

            <div id="create-segment-app">
                <form method="POST" action="{{ route('newsletter.segment.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <x-input-label for="segment_name" :value="__('messages.name')" />
                            <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" required />
                        </div>

                        <div>
                            <x-input-label :value="__('messages.type')" />
                            <select name="type" v-model="segmentType" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                <option value="all_followers">{{ __('messages.all_followers') }}</option>
                                <option value="ticket_buyers">{{ __('messages.ticket_buyers') }}</option>
                                <option value="manual">{{ __('messages.manual') }}</option>
                                <option value="waitlist">{{ __('messages.waitlist') }}</option>
                                @if ($groups->count())
                                <option value="group">{{ __('messages.subschedule') }}</option>
                                @endif
                            </select>
                        </div>

                        <div v-if="segmentType === 'ticket_buyers' || segmentType === 'waitlist'">
                            <x-input-label :value="__('messages.event')" />
                            <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">{{ __('messages.optional_filter_by_event') }}</p>
                            <input type="hidden" name="filter_criteria[event_id]" :value="selectedEventId || ''">
                            <x-event-selector />
                        </div>

                        <div v-if="segmentType === 'group'">
                            <x-input-label :value="__('messages.subschedule')" />
                            <select name="filter_criteria[group_id]" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}">{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div v-if="segmentType === 'manual'">
                            <x-input-label :value="__('messages.email_list')" />
                            <textarea name="emails" rows="6"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                placeholder="{{ __('messages.email_list_placeholder') }}"></textarea>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.email_list_help') }}</p>
                        </div>

                        <div class="flex justify-end">
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-sm text-white hover:bg-[var(--brand-button-bg-hover)]">
                                {{ __('messages.create_segment') }}
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            const { createApp, ref, computed } = Vue;

            createApp({
                setup() {
                    const segmentType = ref('all_followers');
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

                    function handleClickOutside(e) {
                        var el = document.getElementById('event-selector-dropdown');
                        if (el && !el.contains(e.target)) {
                            closeDropdown();
                        }
                    }

                    document.addEventListener('click', handleClickOutside);

                    return {
                        segmentType, events, selectedEventId, selectedEvent, dropdownOpen,
                        toggleDropdown, closeDropdown, onEventChange,
                    };
                }
            }).mount('#create-segment-app');
        });

        document.addEventListener('submit', function(e) {
            var form = e.target.closest('.js-confirm-form');
            if (form) {
                if (!confirm(form.getAttribute('data-confirm'))) {
                    e.preventDefault();
                }
            }
        });
    </script>
</x-app-admin-layout>
