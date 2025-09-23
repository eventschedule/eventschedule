<x-app-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6">
                        <a href="{{ route('settings.index') }}" class="inline-flex items-center gap-2 text-sm font-medium text-[#4E81FA] hover:text-[#365fcc]">
                            <svg class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" />
                            </svg>
                            {{ __('messages.back_to_settings') }}
                        </a>
                    </div>

                    <section>
                        <header>
                            <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
                                {{ __('messages.general_settings') }}
                            </h2>

                            <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                                {{ __('messages.general_settings_description') }}
                            </p>
                        </header>

                        <form method="post" action="{{ route('settings.general.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="public_url" :value="__('messages.public_url')" />
                                <x-text-input id="public_url" name="public_url" type="url" class="mt-1 block w-full"
                                    :value="old('public_url', $generalSettings['public_url'])" autocomplete="off" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.public_url_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('public_url')" />
                            </div>

                            <div>
                                <x-input-label for="update_repository_url" :value="__('messages.update_repository_url')" />
                                <x-text-input id="update_repository_url" name="update_repository_url" type="url"
                                    class="mt-1 block w-full"
                                    :value="old('update_repository_url', $generalSettings['update_repository_url'])"
                                    autocomplete="off" />
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.update_repository_url_help') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('update_repository_url')" />
                            </div>

                            <div>
                                <x-input-label for="terms_markdown" :value="__('messages.terms_settings_label')" />
                                <textarea id="terms_markdown" name="terms_markdown"
                                    class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">{{ old('terms_markdown', $generalSettings['terms_markdown']) }}</textarea>
                                <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
                                    {{ __('messages.terms_settings_description') }}
                                </p>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                    {{ __('messages.terms_settings_hint') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('terms_markdown')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                @if (session('status') === 'general-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                        class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.general_settings_saved') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>

            @if (! config('app.hosted') && ! config('app.testing'))
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-3xl">
                        @include('profile.partials.update-app-form', [
                            'version_installed' => $versionInstalled,
                            'version_available' => $versionAvailable,
                        ])
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-admin-layout>
