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

        @if ($federationAvailable)
        {{-- Anchor target for the dashboard adoption prompt's "Open settings" link. --}}
        <div id="federation" class="ap-card rounded-xl p-6 scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.federation_settings_title')</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('messages.federation_settings_description')</p>
            </div>

            {{-- A failed sync is otherwise completely silent, and a silent sync failure
                 is the most likely long-run failure mode. Mapped to a small set of
                 states rather than echoing the raw response back to the screen. --}}
            @if ($federationLastError)
                <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>@lang('messages.federation_error_'.$federationLastError)</span>
                    </p>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                @csrf

                {{-- Both cards post to the same endpoint, so each has to be explicit
                     about what it owns. This form carries the other card's values
                     through, and marks itself so the controller knows the federation
                     settings were actually submitted rather than merely absent. --}}
                <input type="hidden" name="federation_settings_submitted" value="1">
                <input type="hidden" name="custom_header_code" value="{{ $custom_header_code }}">
                <input type="hidden" name="custom_footer_code" value="{{ $custom_footer_code }}">

                <div class="mb-6">
                    <x-toggle
                        id="federation_enabled"
                        name="federation_enabled"
                        :checked="old('federation_enabled', $federationEnabled)"
                        :label="__('messages.federation_enable')"
                        :disabled="is_demo_mode()" />
                </div>

                <div class="mb-6">
                    <x-input-label for="federation_contact_email" :value="__('messages.federation_contact_email')" />
                    <x-text-input id="federation_contact_email" name="federation_contact_email" type="email"
                        class="mt-1 block w-full" :value="old('federation_contact_email', $federationContactEmail)"
                        :disabled="is_demo_mode()" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.federation_contact_email_help')</p>
                    <x-input-error class="mt-2" :messages="$errors->get('federation_contact_email')" />
                </div>

                {{-- Connection state, echoed back by the network on every call so there
                     is nothing to poll. --}}
                <div class="mb-6 rounded-lg bg-gray-50 dark:bg-[#252526] p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.federation_connection')</p>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                @if ($federationStatus === 'approved')
                                    @lang('messages.federation_status_approved')
                                @elseif ($federationStatus === 'pending')
                                    @lang('messages.federation_status_pending')
                                @elseif ($federationStatus === 'suspended')
                                    @lang('messages.federation_status_suspended')
                                @else
                                    @lang('messages.federation_not_connected')
                                @endif
                            </p>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if ($federationLastSyncedAt)
                                @lang('messages.federation_last_synced', ['time' => \Carbon\Carbon::parse($federationLastSyncedAt)->diffForHumans()])
                            @else
                                @lang('messages.federation_never_synced')
                            @endif
                        </p>
                    </div>
                </div>

                {{-- Publishing customers' events to a third-party site sight-unseen is
                     the real anxiety here, so show exactly what would go out. --}}
                <div class="mb-6">
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.federation_preview_title')</p>

                    @if ($federationPreview->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">@lang('messages.federation_preview_empty')</p>
                    @else
                        <ul class="space-y-1.5">
                            @foreach ($federationPreview as $previewEvent)
                                <li class="flex flex-wrap items-baseline gap-x-2 text-sm">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $previewEvent->name }}</span>
                                    @if ($previewEvent->starts_at)
                                        <span class="text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($previewEvent->starts_at)->format('M j, Y') }}</span>
                                    @endif
                                </li>
                            @endforeach
                        </ul>

                        @if ($federationPreviewTotal > $federationPreview->count())
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                @lang('messages.federation_preview_more', ['count' => $federationPreviewTotal - $federationPreview->count()])
                            </p>
                        @endif
                    @endif

                    {{-- Both of these hold schedules back on purpose, and both look
                         exactly like a bug unless they are stated - especially on a
                         fresh install, where every schedule is undecided and the
                         preview above is therefore empty. --}}
                    @if ($federationUnverified > 0 || $federationUndecided > 0)
                        <div class="mt-3 space-y-1 text-xs text-gray-500 dark:text-gray-400">
                            @if ($federationUnverified > 0)
                                <p>{{ trans_choice('messages.federation_unverified_count', $federationUnverified, ['count' => $federationUnverified]) }}</p>
                            @endif

                            @if ($federationUndecided > 0)
                                <p>{{ trans_choice('messages.federation_undecided_count', $federationUndecided, ['count' => $federationUndecided]) }}</p>
                            @endif
                        </div>
                    @endif
                </div>

                <div class="flex justify-end">
                    <x-brand-button type="submit">@lang('messages.save')</x-brand-button>
                </div>
            </form>
        </div>
        @endif
    </div>
</x-app-admin-layout>
