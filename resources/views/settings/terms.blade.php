<x-app-admin-layout>
    <div class="py-12">
        <div class="max-w-4xl mx-auto space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                <div class="max-w-3xl">
                    <div class="mb-6 border-b border-gray-100 pb-4 dark:border-gray-800">
                        <x-breadcrumbs
                            :items="[
                                ['label' => __('messages.settings'), 'url' => route('settings.index')],
                                ['label' => __('messages.terms_settings'), 'current' => true],
                            ]"
                            class="text-xs text-gray-500 dark:text-gray-400"
                        />
                        <p class="text-sm font-medium text-indigo-600">{{ __('messages.settings') }}</p>
                        <h1 class="mt-2 text-2xl font-semibold text-gray-900 dark:text-white">
                            {{ __('messages.terms_settings') }}
                        </h1>
                        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
                            {{ __('messages.terms_settings_description') }}
                        </p>
                    </div>

                    <section>

                        <form method="post" action="{{ route('settings.terms.update') }}" class="mt-6 space-y-6">
                            @csrf
                            @method('patch')

                            <div>
                                <x-input-label for="terms_markdown" :value="__('messages.terms_settings_label')" />
                                <textarea id="terms_markdown" name="terms_markdown"
                                          class="html-editor mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">{{ old('terms_markdown', $termsSettings['terms_markdown']) }}</textarea>
                                <p class="mt-2 text-xs text-gray-500 dark:text-gray-500">
                                    {{ __('messages.terms_settings_hint') }}
                                </p>
                                <x-input-error class="mt-2" :messages="$errors->get('terms_markdown')" />
                            </div>

                            <div class="flex items-center gap-4">
                                <x-primary-button>{{ __('messages.save') }}</x-primary-button>

                                @if (session('status') === 'terms-settings-updated')
                                    <p x-data="{ show: true }" x-show="show" x-transition x-init="setTimeout(() => show = false, 2000)"
                                       class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.terms_settings_saved') }}</p>
                                @endif
                            </div>
                        </form>
                    </section>
                </div>
            </div>
        </div>
    </div>
</x-app-admin-layout>
