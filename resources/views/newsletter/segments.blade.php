<x-app-admin-layout>

    <x-slot name="head">
        @include('newsletter.partials._styles')
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
    </x-slot>

    {{-- The Segments tab: the segments this schedule has, then the form that adds one. The page
         used to open on the form with the segments above it as loose panels and no word saying
         what they were. --}}
    <div class="page-shell">
        @include('newsletter.partials._section', ['tab' => 'segments'])

        <div class="page-head">
            <p class="page-lead">{{ __('messages.newsletter_segments_lead') }}</p>
        </div>

        <div class="news-block">
            @if ($segments->count())
            <div class="ap-card rounded-xl overflow-hidden">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.name') }}</th>
                            <th scope="col">{{ __('messages.type') }}</th>
                            <th scope="col" class="c-num">{{ __('messages.recipients') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($segments as $segment)
                        @php $segmentParams = ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($segment->id)]; @endphp
                        <tr>
                            <td class="c-main c-strong">
                                <a href="{{ route('newsletter.segment.edit', $segmentParams) }}" class="event-link"><bdi>{{ $segment->name }}</bdi></a>
                            </td>
                            <td>
                                {{ \App\Models\NewsletterSegment::typeLabel($segment->type) }}
                                @if ($segment->type === 'manual')
                                <span class="c-sub">{{ __('messages.manual_entries') }}: {{ number_format($segment->segment_users_count) }}</span>
                                @endif
                            </td>
                            <td class="c-num" data-label="{{ __('messages.recipients') }}">{{ number_format($segment->recipient_count) }}</td>
                            <td class="c-actions">
                                <a href="{{ route('newsletter.segment.edit', $segmentParams) }}" class="event-link">{{ __('messages.edit') }}</a>
                                <form method="POST" action="{{ route('newsletter.segment.delete', $segmentParams) }}" class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="ap-card rounded-xl">
                <x-page-empty
                    :title="__('messages.no_segments')"
                    :text="__('messages.default_all_followers')"
                    icon="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </div>
            @endif
        </div>

        {{-- The form is a Vue island of its own: the type decides which of its fields show, and
             the event picker inside it is a Vue component. --}}
        <x-page-card beside class="news-block" :title="__('messages.create_segment')">
            <div id="create-segment-app">
                <form method="POST" action="{{ route('newsletter.segment.store', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}">
                    @csrf
                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="segment_name" :value="__('messages.name')" />
                            <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" required />
                        </div>

                        <div>
                            <x-input-label for="segment_type" :value="__('messages.type')" />
                            <select id="segment_type" name="type" v-model="segmentType" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                <option value="all_followers">{{ __('messages.all_followers') }}</option>
                                <option value="all_subscribers">{{ __('messages.all_subscribers') }}</option>
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
                            <x-input-label for="segment_group" :value="__('messages.subschedule')" />
                            {{-- A sub-schedule's name is somebody's own text and an option's label
                                 is a text node this mount would compile: v-pre on each. --}}
                            <select id="segment_group" name="filter_criteria[group_id]" class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                                @foreach ($groups as $group)
                                <option value="{{ $group->id }}" v-pre>{{ $group->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div v-if="segmentType === 'manual'">
                            <x-input-label for="segment_emails" :value="__('messages.email_list')" />
                            <textarea id="segment_emails" name="emails" rows="6"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm"
                                placeholder="{{ __('messages.email_list_placeholder') }}"></textarea>
                            <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ __('messages.email_list_help') }}</p>
                        </div>
                    </div>

                    <div class="page-form-actions">
                        <x-brand-button type="submit">{{ __('messages.create_segment') }}</x-brand-button>
                    </div>
                </form>
            </div>
        </x-page-card>
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
    </script>
    @include('newsletter.partials._list-script')
</x-app-admin-layout>
