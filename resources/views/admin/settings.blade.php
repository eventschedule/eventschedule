<x-app-admin-layout>
    <div class="space-y-4">

        {{-- Navigation --}}
        @include('admin.partials._navigation', ['active' => 'settings'])

        {{-- Every card on this page flashes `success`, which the layout's toasts do not show -
             the same banner the other admin pages use (admin/legal.blade.php). The network card
             flashes a toast instead, because its save lands on the card, not the top. --}}
        @if (session('success'))
        <div class="bg-green-50 dark:bg-green-900/30 border border-green-200 dark:border-green-800 rounded-lg p-4">
            <p class="text-sm text-green-800 dark:text-green-200">{{ session('success') }}</p>
        </div>
        @endif

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
                 states rather than echoing the raw response back to the screen.
                 "rejected" has its own wording now that the hourly run reconnects. --}}
            @if ($federationLastError)
                @php
                    $federationErrorKey = $federationLastError === 'rejected' ? 'rejected_reconnecting' : $federationLastError;
                @endphp
                <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>@lang('messages.federation_error_'.$federationErrorKey)</span>
                    </p>
                </div>
            @endif

            {{-- Where the install stands, first, once there is anything to say - before the
                 first connection "Not connected, not synced" is only noise. Echoed back by the
                 network on every call, so there is nothing to poll. --}}
            @if ($federationConnected)
                @php
                    $federationStateKey = match (true) {
                        ! $federationEnabled => 'off',
                        $federationStatus === 'approved' => 'approved',
                        $federationStatus === 'pending' => 'pending',
                        $federationStatus === 'suspended' => 'suspended',
                        default => 'retrying',
                    };
                    $federationPill = match ($federationStateKey) {
                        'approved' => ['bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400', __('messages.federation_status_approved')],
                        'pending' => ['bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400', __('messages.federation_status_pending')],
                        'suspended' => ['bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400', __('messages.federation_status_suspended')],
                        default => ['bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300', __('messages.federation_not_connected')],
                    };
                @endphp
                <div class="mb-6 rounded-lg bg-gray-50 dark:bg-gray-800 p-4">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex flex-wrap items-center gap-2">
                            <p class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.federation_connection')</p>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium {{ $federationPill[0] }}">{{ $federationPill[1] }}</span>
                        </div>
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if ($federationLastSyncedAt)
                                @lang('messages.federation_last_synced', ['time' => \Carbon\Carbon::parse($federationLastSyncedAt)->diffForHumans()])
                            @else
                                @lang('messages.federation_never_synced')
                            @endif
                        </p>
                    </div>

                    <p class="mt-2 text-sm text-gray-600 dark:text-gray-300">
                        @if ($federationStateKey === 'approved')
                            {{ trans_choice('messages.federation_state_approved', $federationSentTotal, ['count' => number_format($federationSentTotal)]) }}
                        @else
                            @lang('messages.federation_state_'.$federationStateKey)
                        @endif
                    </p>

                    {{-- Only the network can build this link (the id in it is encoded with its
                         key), so it arrives with every sync; FederationService only keeps one
                         that points at the configured network. --}}
                    @if ($federationStateKey === 'approved' && $federationListingsUrl && $federationSentTotal > 0)
                        <p class="mt-2 text-sm">
                            <x-link href="{{ $federationListingsUrl }}" target="_blank">@lang('messages.federation_see_listings')</x-link>
                        </p>
                    @endif
                </div>
            @endif

            <form method="POST" action="{{ route('admin.settings.update') }}" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                @csrf

                {{-- Both cards post to the same endpoint, so each has to be explicit
                     about what it owns. This one marks itself, so the controller saves the
                     federation settings and leaves the header/footer code alone - carrying
                     that code through as hidden inputs would write back whatever this copy
                     of the page held, over a newer save from another tab. --}}
                <input type="hidden" name="federation_settings_submitted" value="1">

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
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.federation_contact_email_note')</p>
                    <x-input-error class="mt-2" :messages="$errors->get('federation_contact_email')" />
                </div>

                {{-- The operator's own undecided schedules. Switching sharing on publishes
                     nothing by itself - every schedule starts undecided - so choosing what to
                     share belongs in the same save. Ticked while sharing is OFF, so the enabling
                     save carries them; unticked once it is on, so an unrelated save (a new
                     contact email) never lists anything. Schedules somebody else owns are not
                     here: those owners are asked on their own dashboards. --}}
                @if ($federationMySchedules->isNotEmpty())
                    @php
                        $federationResubmitted = old('federation_settings_submitted') !== null;
                        $federationOldTicked = collect(old('list_schedules', []));
                    @endphp
                    <fieldset class="mb-6">
                        <legend class="text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.federation_list_these_title')</legend>
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('messages.federation_list_these_help', [
                            'edit' => __('messages.edit_schedule'),
                            'schedule_settings' => __('messages.schedule_settings'),
                            'advanced' => __('messages.advanced'),
                        ]) }}</p>

                        {{-- No max-height: every row is ticked on the enabling save, so every row
                             has to be in view. --}}
                        <ul class="mt-3 space-y-3" v-pre>
                            @foreach ($federationMySchedules as $mySchedule)
                                @php
                                    $myHash = \App\Utils\UrlUtils::encodeId($mySchedule->id);
                                    $myCount = (int) ($federationMyCounts[$mySchedule->id] ?? 0);
                                    $myTicked = $federationResubmitted ? $federationOldTicked->contains($myHash) : ! $federationEnabled;
                                @endphp
                                <li>
                                    <label class="flex items-start gap-3 cursor-pointer">
                                        <input type="checkbox" name="list_schedules[]" value="{{ $myHash }}" @checked($myTicked) @disabled(is_demo_mode())
                                               class="mt-1 rounded border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <span class="min-w-0 text-sm">
                                            <span class="block font-medium text-gray-900 dark:text-gray-100 break-words">{{ $mySchedule->name }}</span>
                                            {{-- getGuestUrl() is empty until a schedule is verified. --}}
                                            @if ($myGuestUrl = $mySchedule->getGuestUrl())
                                                <span class="block text-gray-500 dark:text-gray-400 break-all">{{ $myGuestUrl }}</span>
                                            @endif
                                            <span class="block text-xs text-gray-500 dark:text-gray-400">
                                                {{ trans_choice('messages.federation_would_share_count', $myCount, ['count' => number_format($myCount)]) }}
                                                @if (isset($federationMyHeldBack[$mySchedule->id]))
                                                    &middot; @lang('messages.federation_needs_verification')
                                                @endif
                                            </span>
                                        </span>
                                    </label>
                                </li>
                            @endforeach
                        </ul>
                    </fieldset>
                @endif

                {{-- Schedules before events: a listing carries the schedule's name and
                     the address of its public page, and the reviewing administrator at
                     the other end sees both, so the operator should see the same list
                     first. Its own block rather than part of the event preview below,
                     whose unverified/undecided footnotes are caveats on the EVENT list. --}}
                @if ($federationPreviewSchedules->isNotEmpty())
                    <div class="mb-6">
                        <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.federation_preview_schedules_title')</p>
                        <ul class="space-y-1.5" v-pre>
                            @foreach ($federationPreviewSchedules as $previewSchedule)
                                <li class="flex flex-wrap items-baseline gap-x-2 text-sm">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $previewSchedule->name }}</span>
                                    <span class="text-gray-500 dark:text-gray-400 break-all">{{ $previewSchedule->getGuestUrl() }}</span>
                                </li>
                            @endforeach
                        </ul>

                        @if ($federationPreviewSchedulesTotal > $federationPreviewSchedules->count())
                            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                                @lang('messages.federation_preview_more', ['count' => $federationPreviewSchedulesTotal - $federationPreviewSchedules->count()])
                            </p>
                        @endif
                    </div>
                @endif

                {{-- Publishing customers' events to a third-party site sight-unseen is
                     the real anxiety here, so show exactly what would go out - and, per
                     event, whether it has gone, and why not if it cannot. --}}
                <div class="mb-6">
                    <p class="mb-2 text-sm font-medium text-gray-700 dark:text-gray-300">@lang('messages.federation_preview_title')</p>

                    @if ($federationPreview->isEmpty())
                        <p class="text-sm text-gray-500 dark:text-gray-400">
                            @if ($federationMySchedules->isNotEmpty())
                                @lang('messages.federation_preview_empty_tick')
                            @else
                                @lang('messages.federation_preview_empty_rules')
                                <x-link href="{{ rtrim(config('app.nexus_url'), '/') }}/docs/selfhost/federation#listings" target="_blank">@lang('messages.learn_more')</x-link>
                            @endif
                        </p>
                    @else
                        {{-- The id styles nothing: it is a hook, so a test can slice THIS list out
                             of the page. The schedules list above renders identical markup, and one
                             of its rows carries the same schedule name an event row does. --}}
                        <ul class="space-y-1.5" v-pre id="federation-preview">
                            @foreach ($federationPreview as $previewEvent)
                                @php
                                    $previewState = $federationPreviewStates[$previewEvent->id] ?? 'next_sync';
                                    $previewPill = match ($previewState) {
                                        'sent' => 'bg-green-50 text-green-700 dark:bg-green-500/10 dark:text-green-400',
                                        'needs_image' => 'bg-amber-50 text-amber-700 dark:bg-amber-500/10 dark:text-amber-400',
                                        'skipped' => 'bg-red-50 text-red-700 dark:bg-red-500/10 dark:text-red-400',
                                        default => 'bg-blue-50 text-blue-700 dark:bg-blue-500/10 dark:text-blue-400',
                                    };
                                @endphp
                                <li class="flex flex-wrap items-center gap-x-2 gap-y-1 text-sm">
                                    <span class="font-medium text-gray-900 dark:text-gray-100">{{ $previewEvent->name }}</span>
                                    @if ($previewEvent->starts_at)
                                        <span class="text-gray-500 dark:text-gray-400">{{ \Carbon\Carbon::parse($previewEvent->starts_at)->format('M j, Y') }}</span>
                                    @endif
                                    {{-- Sync state only means something while sharing is on; a missing
                                         image is worth knowing either way. --}}
                                    @if ($federationEnabled || $previewState === 'needs_image')
                                        <span class="rounded-full px-2 py-0.5 text-xs font-medium {{ $previewPill }}">@lang('messages.federation_pill_'.$previewState)</span>
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
                                <p>{{ trans_choice('messages.federation_undecided_others_count', $federationUndecided, ['count' => $federationUndecided]) }}</p>
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

        @if ($adsAvailable)
        <div id="monetization" class="ap-card rounded-xl p-6 scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.monetization_settings_title')</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('messages.monetization_settings_description')</p>
            </div>

            <div class="mb-6 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
                <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                    <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                    </svg>
                    <span>@lang('messages.monetization_settings_warning')</span>
                </p>
            </div>

            {{-- This card posts to its own endpoint rather than sharing admin.settings.update.
                 The shared-endpoint cards above each have to carry every other card's values
                 through as hidden inputs, which is quadratic in the number of cards and fails
                 silently (saving one card wipes another). A dedicated action owns only the
                 ads_* keys, so no pass-through is needed and no card can clobber another. --}}
            <form method="POST" action="{{ route('admin.settings.update_ads') }}" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                @csrf

                <div class="mb-6">
                    <x-toggle
                        id="ads_adsense_enabled"
                        name="ads_adsense_enabled"
                        :checked="old('ads_adsense_enabled', $adsAdsenseEnabled)"
                        :label="__('messages.monetization_adsense_enable')"
                        :disabled="is_demo_mode()" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.monetization_adsense_enable_help')</p>
                </div>

                <div class="mb-6">
                    <x-input-label for="ads_adsense_client_id" :value="__('messages.monetization_adsense_client_id')" />
                    <x-text-input id="ads_adsense_client_id" name="ads_adsense_client_id" type="text"
                        class="mt-1 block w-full font-mono text-sm" placeholder="ca-pub-0000000000000000"
                        :value="old('ads_adsense_client_id', $adsAdsenseClientId)"
                        :disabled="is_demo_mode()" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.monetization_adsense_client_id_help')</p>
                    <x-input-error class="mt-2" :messages="$errors->get('ads_adsense_client_id')" />
                </div>

                <div class="mb-6">
                    <x-input-label for="ads_adsense_slot_id" :value="__('messages.monetization_adsense_slot_id')" />
                    <x-text-input id="ads_adsense_slot_id" name="ads_adsense_slot_id" type="text"
                        class="mt-1 block w-full font-mono text-sm" placeholder="1234567890"
                        :value="old('ads_adsense_slot_id', $adsAdsenseSlotId)"
                        :disabled="is_demo_mode()" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.monetization_adsense_slot_id_help')</p>
                    <x-input-error class="mt-2" :messages="$errors->get('ads_adsense_slot_id')" />
                </div>

                <div class="mb-6">
                    <x-toggle
                        id="ads_personalized"
                        name="ads_personalized"
                        :checked="old('ads_personalized', $adsPersonalized)"
                        :label="__('messages.monetization_personalized')"
                        :disabled="is_demo_mode()" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.monetization_personalized_help')</p>
                </div>

                <div class="mb-6 border-t border-gray-200 dark:border-gray-700 pt-6">
                    <x-toggle
                        id="ads_native_enabled"
                        name="ads_native_enabled"
                        :checked="old('ads_native_enabled', $adsNativeEnabled)"
                        :label="__('messages.monetization_native_enable')"
                        :disabled="is_demo_mode()" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.monetization_native_enable_help')</p>
                </div>

                <div class="mb-6">
                    <x-toggle
                        id="ads_native_priority"
                        name="ads_native_priority"
                        :checked="old('ads_native_priority', $adsNativePriority)"
                        :label="__('messages.monetization_native_priority')"
                        :disabled="is_demo_mode()" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.monetization_native_priority_help')</p>
                </div>

                <div class="mb-6 grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <x-input-label for="ads_native_cpm" :value="__('messages.monetization_native_cpm')" />
                        <x-text-input id="ads_native_cpm" name="ads_native_cpm" type="number" step="0.01" min="0"
                            class="mt-1 block w-full" :value="old('ads_native_cpm', $adsNativeCpm)"
                            :disabled="is_demo_mode()" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.monetization_native_cpm_help')</p>
                        <x-input-error class="mt-2" :messages="$errors->get('ads_native_cpm')" />
                    </div>

                    <div>
                        <x-input-label for="ads_native_cpc" :value="__('messages.monetization_native_cpc')" />
                        <x-text-input id="ads_native_cpc" name="ads_native_cpc" type="number" step="0.01" min="0"
                            class="mt-1 block w-full" :value="old('ads_native_cpc', $adsNativeCpc)"
                            :disabled="is_demo_mode()" />
                        <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.monetization_native_cpc_help')</p>
                        <x-input-error class="mt-2" :messages="$errors->get('ads_native_cpc')" />
                    </div>
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
        @endif

        {{-- Deliberately its own card and its own endpoint rather than part of the
             monetization card above. updateAdsSettings() hard-returns unless
             ADS_ENABLED && hosted && ! nexus, which would make this field unreachable in the
             two cases that matter most: an install with ads switched off, and the nexus,
             which is precisely the operator that wants a fallback affiliate ID. The
             monetization heading would also misdescribe it, since that feature is scoped to
             free-tier schedules on multi-tenant hosted installs and this one is none of
             those. --}}
        @if ($planPricingAvailable)
        {{-- Before the currency card, not after: the amounts come first and the currency is what
             they are printed in. Gated on hosted-or-nexus, because a plain selfhost has no
             surface that quotes a plan price - see AdminController::settings(). --}}
        <div id="plan-pricing" class="ap-card rounded-xl p-6 scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.plan_pricing_title')</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('messages.plan_pricing_description')</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update_plan_pricing') }}" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                @csrf

                <div class="mb-2 grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div>
                        <x-input-label for="plan_price_pro_monthly" :value="__('messages.plan_pricing_pro_monthly')" />
                        <x-text-input id="plan_price_pro_monthly" name="plan_price_pro_monthly" type="number" step="0.01" min="0.01"
                            class="mt-1 block w-full"
                            :disabled="is_demo_mode()"
                            :value="old('plan_price_pro_monthly', $planPricingStored['pro_monthly'])"
                            placeholder="{{ $planPricingEffective['pro_monthly'] }}" />
                        <x-input-error class="mt-2" :messages="$errors->get('plan_price_pro_monthly')" />
                    </div>
                    <div>
                        <x-input-label for="plan_price_pro_yearly" :value="__('messages.plan_pricing_pro_yearly')" />
                        <x-text-input id="plan_price_pro_yearly" name="plan_price_pro_yearly" type="number" step="0.01" min="0.01"
                            class="mt-1 block w-full"
                            :disabled="is_demo_mode()"
                            :value="old('plan_price_pro_yearly', $planPricingStored['pro_yearly'])"
                            placeholder="{{ $planPricingEffective['pro_yearly'] }}" />
                        <x-input-error class="mt-2" :messages="$errors->get('plan_price_pro_yearly')" />
                    </div>
                    <div>
                        <x-input-label for="plan_price_enterprise_monthly" :value="__('messages.plan_pricing_enterprise_monthly')" />
                        <x-text-input id="plan_price_enterprise_monthly" name="plan_price_enterprise_monthly" type="number" step="0.01" min="0.01"
                            class="mt-1 block w-full"
                            :disabled="is_demo_mode()"
                            :value="old('plan_price_enterprise_monthly', $planPricingStored['enterprise_monthly'])"
                            placeholder="{{ $planPricingEffective['enterprise_monthly'] }}" />
                        <x-input-error class="mt-2" :messages="$errors->get('plan_price_enterprise_monthly')" />
                    </div>
                    <div>
                        <x-input-label for="plan_price_enterprise_yearly" :value="__('messages.plan_pricing_enterprise_yearly')" />
                        <x-text-input id="plan_price_enterprise_yearly" name="plan_price_enterprise_yearly" type="number" step="0.01" min="0.01"
                            class="mt-1 block w-full"
                            :disabled="is_demo_mode()"
                            :value="old('plan_price_enterprise_yearly', $planPricingStored['enterprise_yearly'])"
                            placeholder="{{ $planPricingEffective['enterprise_yearly'] }}" />
                        <x-input-error class="mt-2" :messages="$errors->get('plan_price_enterprise_yearly')" />
                    </div>
                </div>

                <p class="mb-6 text-xs text-gray-500 dark:text-gray-400">@lang('messages.plan_pricing_help')</p>

                <div class="mb-6 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>@lang('messages.plan_pricing_display_only')</span>
                    </p>
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
        @endif

        {{-- Always rendered. Even a selfhost with no plans to price uses this as the
             default currency for a new event. --}}
        <div id="currency" class="ap-card rounded-xl p-6 scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.platform_currency_title')</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('messages.platform_currency_description')</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update_currency') }}" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                @csrf

                <div class="mb-6">
                    <x-input-label for="platform_currency" :value="__('messages.currency')" />
                    <select id="platform_currency" name="platform_currency" {{ is_demo_mode() ? 'disabled' : '' }}
                        class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                        @foreach ($currencies as $currency)
                        @if ($loop->index == 2)
                        <option disabled>──────────</option>
                        @endif
                        <option value="{{ $currency->value }}" {{ old('platform_currency', $platformCurrency) == $currency->value ? 'selected' : '' }}>
                            {{ $currency->value }} - {{ $currency->label }}
                        </option>
                        @endforeach
                    </select>
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.platform_currency_help')</p>
                    <x-input-error class="mt-2" :messages="$errors->get('platform_currency')" />
                </div>

                @if (config('app.hosted'))
                <div class="mb-6 p-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg">
                    <p class="text-sm text-amber-800 dark:text-amber-200 flex items-start gap-2">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                        <span>@lang('messages.platform_currency_display_only')</span>
                    </p>
                </div>
                @endif

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

        @if ($stay22Available)
        <div id="accommodation" class="ap-card rounded-xl p-6 scroll-mt-24">
            <div class="mb-4">
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">@lang('messages.stay22_settings_title')</h2>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">@lang('messages.stay22_settings_description')</p>
            </div>

            <form method="POST" action="{{ route('admin.settings.update_stay22') }}" class="{{ is_demo_mode() ? 'opacity-50 pointer-events-none' : '' }}">
                @csrf

                <div class="mb-6">
                    <x-input-label for="stay22_aid" :value="__('messages.stay22_operator_aid')" />
                    <x-text-input id="stay22_aid" name="stay22_aid" type="text"
                        class="mt-1 block w-full font-mono text-sm"
                        :value="old('stay22_aid', $stay22Aid)"
                        :disabled="is_demo_mode()" />
                    <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">@lang('messages.stay22_operator_aid_help')</p>
                    <x-input-error class="mt-2" :messages="$errors->get('stay22_aid')" />
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
        @endif
    </div>
</x-app-admin-layout>
