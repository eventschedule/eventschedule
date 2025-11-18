<x-app-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                        <x-breadcrumbs
                            :items="[
                                ['label' => __('messages.settings'), 'url' => route('settings.index')],
                                ['label' => __('messages.event_type_settings'), 'current' => true],
                            ]"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        />
                        <p class="text-sm font-medium text-indigo-600">{{ __('messages.settings') }}</p>
                        <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.event_type_settings') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.event_type_settings_description') }}
                        </p>
                    </div>

                    <section>

                        @php
                            $status = session('status');
                            $statusMessages = [
                                'event-type-created' => __('messages.event_type_created'),
                                'event-type-updated' => __('messages.event_type_updated'),
                                'event-type-deleted' => __('messages.event_type_deleted'),
                            ];
                        @endphp

                        @if ($status && isset($statusMessages[$status]))
                            <div class="mt-6 rounded-md border border-green-200 bg-green-50 p-4 text-sm text-green-700 dark:border-green-900 dark:bg-green-950/50 dark:text-green-300">
                                {{ $statusMessages[$status] }}
                            </div>
                        @endif

                        <div class="mt-8 space-y-10">
                            <div>
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.existing_event_types') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.existing_event_types_description') }}
                                </p>

                                <div class="mt-4 space-y-6">
                                    @forelse ($eventTypes as $eventType)
                                        @php
                                            $bag = $errors->getBag('eventType-' . $eventType->id);
                                            $activeForm = old('event_type_form');
                                            $useOld = (string) $activeForm === (string) $eventType->id;
                                            $nameValue = $useOld ? old('name', $eventType->name) : $eventType->name;
                                            $sortValue = $useOld ? old('sort_order', $eventType->sort_order) : $eventType->sort_order;
                                            $translations = $eventType->translations ?? [];
                                        @endphp

                                        <div class="rounded-lg border border-gray-200 bg-white p-6 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                                            <form method="post" action="{{ route('settings.event_types.update', $eventType) }}" class="space-y-6">
                                                @csrf
                                                @method('patch')
                                                <input type="hidden" name="event_type_form" value="{{ $eventType->id }}">

                                                <div class="grid gap-6 sm:grid-cols-2">
                                                    <div class="sm:col-span-2">
                                                        <x-input-label for="event-type-name-{{ $eventType->id }}" :value="__('messages.event_type_name')" />
                                                        <x-text-input id="event-type-name-{{ $eventType->id }}" name="name" type="text" class="mt-1 block w-full"
                                                            value="{{ $nameValue }}" autocomplete="off" />
                                                        <x-input-error class="mt-2" :messages="$bag->get('name')" />
                                                    </div>

                                                    <div>
                                                        <x-input-label for="event-type-sort-order-{{ $eventType->id }}" :value="__('messages.event_type_sort_order')" />
                                                        <x-text-input id="event-type-sort-order-{{ $eventType->id }}" name="sort_order" type="number" min="0"
                                                            class="mt-1 block w-full" value="{{ $sortValue }}" autocomplete="off" />
                                                        <x-input-error class="mt-2" :messages="$bag->get('sort_order')" />
                                                    </div>

                                                    <div class="sm:col-span-2">
                                                        <p class="text-sm text-gray-500 dark:text-gray-400">
                                                            {{ __('messages.event_type_slug_hint', ['slug' => $eventType->slug]) }}
                                                        </p>
                                                    </div>
                                                </div>

                                                @if (!empty($locales))
                                                    <details class="rounded-md border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/60">
                                                        <summary class="cursor-pointer text-sm font-medium text-gray-800 dark:text-gray-200">
                                                            {{ __('messages.event_type_translations') }}
                                                        </summary>
                                                        <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                                            @foreach ($locales as $locale)
                                                                @php
                                                                    $labelKey = 'messages.language_name_' . $locale;
                                                                    $label = __($labelKey);
                                                                    if ($label === $labelKey) {
                                                                        $label = strtoupper($locale);
                                                                    }
                                                                    $translationValue = $useOld
                                                                        ? old('translations.' . $locale, $translations[$locale] ?? '')
                                                                        : ($translations[$locale] ?? '');
                                                                @endphp
                                                                <div>
                                                                    <x-input-label for="event-type-translation-{{ $eventType->id }}-{{ $locale }}" :value="$label" />
                                                                    <x-text-input id="event-type-translation-{{ $eventType->id }}-{{ $locale }}" name="translations[{{ $locale }}]" type="text"
                                                                        class="mt-1 block w-full" value="{{ $translationValue }}" autocomplete="off" />
                                                                    <x-input-error class="mt-2" :messages="$bag->get('translations.' . $locale)" />
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                        <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                                            {{ __('messages.event_type_translation_help') }}
                                                        </p>
                                                    </details>
                                                @endif

                                                <div class="flex flex-wrap items-center justify-between gap-4">
                                                    <div class="flex items-center gap-3">
                                                        <x-primary-button>{{ __('messages.update') }}</x-primary-button>
                                                        @if ($bag->has('delete'))
                                                            <p class="text-sm text-red-600 dark:text-red-400">{{ $bag->first('delete') }}</p>
                                                        @endif
                                                    </div>

                                                    <x-danger-button type="submit" form="delete-event-type-{{ $eventType->id }}">
                                                        {{ __('messages.delete') }}
                                                    </x-danger-button>
                                                </div>
                                            </form>
                                            <form method="post" action="{{ route('settings.event_types.destroy', $eventType) }}" id="delete-event-type-{{ $eventType->id }}">
                                                @csrf
                                                @method('delete')
                                                <input type="hidden" name="event_type_form" value="{{ $eventType->id }}">
                                            </form>
                                        </div>
                                    @empty
                                        <p class="text-sm text-gray-600 dark:text-gray-400">
                                            {{ __('messages.no_event_types_defined') }}
                                        </p>
                                    @endforelse
                                </div>
                            </div>

                            <div class="border-t border-gray-200 pt-8 dark:border-gray-700">
                                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                                    {{ __('messages.add_event_type') }}
                                </h3>
                                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.add_event_type_description') }}
                                </p>

                                @php
                                    $createBag = $errors->getBag('createEventType');
                                @endphp

                                <form method="post" action="{{ route('settings.event_types.store') }}" class="mt-6 space-y-6">
                                    @csrf

                                    <div class="grid gap-6 sm:grid-cols-2">
                                        <div class="sm:col-span-2">
                                            <x-input-label for="new-event-type-name" :value="__('messages.event_type_name')" />
                                            <x-text-input id="new-event-type-name" name="name" type="text" class="mt-1 block w-full"
                                                value="{{ old('name') }}" autocomplete="off" />
                                            <x-input-error class="mt-2" :messages="$createBag->get('name')" />
                                        </div>

                                        <div>
                                            <x-input-label for="new-event-type-sort-order" :value="__('messages.event_type_sort_order')" />
                                            <x-text-input id="new-event-type-sort-order" name="sort_order" type="number" min="0"
                                                class="mt-1 block w-full" value="{{ old('sort_order') }}" autocomplete="off" />
                                            <x-input-error class="mt-2" :messages="$createBag->get('sort_order')" />
                                        </div>
                                    </div>

                                    @if (!empty($locales))
                                        <details class="rounded-md border border-gray-200 bg-gray-50 p-4 dark:border-gray-700 dark:bg-gray-800/60">
                                            <summary class="cursor-pointer text-sm font-medium text-gray-800 dark:text-gray-200">
                                                {{ __('messages.event_type_translations') }}
                                            </summary>
                                            <div class="mt-4 grid gap-4 sm:grid-cols-2">
                                                @foreach ($locales as $locale)
                                                    @php
                                                        $labelKey = 'messages.language_name_' . $locale;
                                                        $label = __($labelKey);
                                                        if ($label === $labelKey) {
                                                            $label = strtoupper($locale);
                                                        }
                                                    @endphp
                                                    <div>
                                                        <x-input-label for="new-event-type-translation-{{ $locale }}" :value="$label" />
                                                        <x-text-input id="new-event-type-translation-{{ $locale }}" name="translations[{{ $locale }}]" type="text"
                                                            class="mt-1 block w-full" value="{{ old('translations.' . $locale) }}" autocomplete="off" />
                                                        <x-input-error class="mt-2" :messages="$createBag->get('translations.' . $locale)" />
                                                    </div>
                                                @endforeach
                                            </div>
                                            <p class="mt-3 text-xs text-gray-500 dark:text-gray-400">
                                                {{ __('messages.event_type_translation_help') }}
                                            </p>
                                        </details>
                                    @endif

                                    <x-primary-button>{{ __('messages.add') }}</x-primary-button>
                                </form>
                            </div>
                        </div>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
