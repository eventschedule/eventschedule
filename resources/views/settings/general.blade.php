<x-app-admin-layout>
    @php
        $generalSettings = $generalSettings ?? [];

        if ($generalSettings instanceof \Illuminate\Support\Collection) {
            $generalSettings = $generalSettings->toArray();
        } elseif (is_object($generalSettings)) {
            $generalSettings = (array) $generalSettings;
        } elseif (! is_array($generalSettings)) {
            $generalSettings = [];
        }

    @endphp

    <div class="py-10">
        <div class="mx-auto max-w-6xl space-y-6">
            <div class="rounded-xl bg-white p-6 shadow-sm ring-1 ring-gray-100 dark:bg-gray-900 dark:ring-gray-800">
                <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                    <x-breadcrumbs
                        :items="[
                            ['label' => __('messages.settings'), 'url' => route('settings.index')],
                            ['label' => __('messages.general_settings'), 'current' => true],
                        ]"
                        class="text-xs text-gray-500 dark:text-gray-400"
                    />
                    <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                        {{ __('messages.general_settings') }}
                    </h1>
                    <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                        {{ __('messages.general_settings_description') }}
                    </p>
                </div>

                <form method="post" action="{{ route('settings.general.update') }}" class="space-y-6">
                    @csrf
                    @method('patch')

                    <div>
                        <x-input-label for="public_url" :value="__('messages.public_url')" />
                        <x-text-input
                            id="public_url"
                            name="public_url"
                            type="url"
                            class="mt-1 block w-full"
                            :value="old('public_url', data_get($generalSettings, 'public_url'))"
                            autocomplete="off"
                        />
                        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.public_url_help') }}
                        </p>
                        <x-input-error class="mt-2" :messages="$errors->get('public_url')" />
                    </div>

                    <div class="flex items-center gap-4">
                        <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                        @if (session('status') === 'general-settings-updated')
                            <p
                                x-data="{ show: true }"
                                x-show="show"
                                x-transition
                                x-init="setTimeout(() => show = false, 2000)"
                                class="text-sm text-gray-600 dark:text-gray-400"
                            >
                                {{ __('messages.general_settings_saved') }}
                            </p>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-admin-layout>
