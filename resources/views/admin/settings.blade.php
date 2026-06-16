<x-app-admin-layout>
    <div class="space-y-4">

        {{-- Navigation --}}
        @include('admin.partials._navigation', ['active' => 'settings'])

        <div class="ap-card rounded-xl p-6">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.header_footer_code')</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('messages.header_footer_code_intro')</p>
            </div>

            {{-- Warning --}}
            <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>@lang('messages.header_footer_code_warning')</span>
                </p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                @csrf

                <div class="mb-6">
                    <x-input-label for="custom_header_code" :value="__('messages.custom_header_code')" />
                    <textarea id="custom_header_code" name="custom_header_code" rows="10" {{ is_demo_mode() ? 'disabled' : '' }}
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm font-mono text-sm"
                        placeholder="<!-- Google Tag Manager -->">{{ old('custom_header_code', $custom_header_code) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.custom_header_code_help')</p>
                    <x-input-error class="mt-2" :messages="$errors->get('custom_header_code')" />
                </div>

                <div class="mb-6">
                    <x-input-label for="custom_footer_code" :value="__('messages.custom_footer_code')" />
                    <textarea id="custom_footer_code" name="custom_footer_code" rows="10" {{ is_demo_mode() ? 'disabled' : '' }}
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm font-mono text-sm">{{ old('custom_footer_code', $custom_footer_code) }}</textarea>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.custom_footer_code_help')</p>
                    <x-input-error class="mt-2" :messages="$errors->get('custom_footer_code')" />
                </div>

                @if (is_demo_mode())
                <div class="mb-6 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>@lang('messages.demo_mode_settings_disabled')</span>
                    </p>
                </div>
                @endif

                <div class="flex justify-end">
                    <x-brand-button type="submit">@lang('messages.save')</x-brand-button>
                </div>
            </form>
        </div>
    </div>
</x-app-admin-layout>
