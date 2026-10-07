<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* A line of help under a field. */
            .sys-help {
              margin: 0.375rem 0 0;
              font-size: 0.75rem;
              color: rgb(var(--ap-ink-3));
            }
            /* The network card's own state, and the lists it shows before anything is sent. */
            .sys-panel {
              border-radius: 0.75rem;
              padding: 1rem;
              background: var(--ap-tint-1);
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-2));
            }
            .sys-panel-head {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: space-between;
              gap: 0.375rem 1rem;
              margin-bottom: 0.5rem;
            }
            .sys-panel-head strong {
              font-weight: 600;
              color: rgb(var(--ap-ink));
            }
            .sys-panel-head > span:last-child {
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .sys-panel p {
              margin: 0.5rem 0 0;
            }
            .sys-sub {
              margin: 0 0 0.5rem;
              font-size: 0.875rem;
              font-weight: 500;
              color: rgb(var(--ap-ink-2));
            }
            .sys-list {
              display: grid;
              gap: 0.375rem;
              margin: 0;
              padding: 0;
              list-style: none;
              font-size: 0.875rem;
            }
            .sys-list li {
              display: flex;
              flex-wrap: wrap;
              align-items: baseline;
              gap: 0.125rem 0.625rem;
              min-width: 0;
            }
            .sys-list li > :first-child {
              font-weight: 500;
              color: rgb(var(--ap-ink));
              overflow-wrap: anywhere;
            }
            .sys-list li > span + span {
              color: rgb(var(--ap-ink-3));
              overflow-wrap: anywhere;
            }
            .sys-checks {
              display: grid;
              gap: 0.75rem;
              margin: 0.75rem 0 0;
              padding: 0;
              list-style: none;
            }
            .sys-checks label {
              display: flex;
              align-items: flex-start;
              gap: 0.75rem;
              cursor: pointer;
            }
            .sys-checks label > span {
              min-width: 0;
              font-size: 0.875rem;
            }
            .sys-checks label > span > span {
              display: block;
              overflow-wrap: anywhere;
              color: rgb(var(--ap-ink-3));
            }
            .sys-checks label > span > span:first-child {
              font-weight: 500;
              color: rgb(var(--ap-ink));
            }
            .sys-checks label > span > span:last-child {
              font-size: 0.75rem;
            }
            .sys-rule {
              border-top: 1px solid rgb(var(--ap-border));
              padding-top: 1.25rem;
            }
            /* A card whose form is switched off (demo mode) says so with its notice, and shows it. */
            .sys-off {
              opacity: 0.5;
              pointer-events: none;
            }
            /* One Save per card, at its end. The kit's row adds room above for a page that is one
               form; a card needs less. */
            .page-card .page-form-actions {
              margin-top: 1.25rem;
            }
        </style>
    </x-slot>

    {{-- Navigation --}}
    @include('admin.partials._navigation', ['active' => 'settings'])

    {{-- What this installation does for everyone on it. Each card is its own small form with its
         own Save: the cards post to different endpoints, and one card's Save must never carry
         another card's fields. Other pages link to the cards by id (#federation, #monetization,
         #plan-pricing, #currency, #realtime, #accommodation). --}}
    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_settings_lead') }}</p>
    </div>

    @php
        $demo = is_demo_mode();
        $formClass = $demo ? 'sys-off' : '';
        $codeClass = 'mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm font-mono text-sm';
    @endphp

    <div class="page-shell page-stack">

        {{-- Every card on this page flashes `success`, which the layout's toasts do not show -
             the same notice the other admin pages use (admin/legal.blade.php). The network card
             flashes a toast instead, because its save lands on the card, not the top. --}}
        <x-page-flash :keys="['success' => 'success']" />

        <x-page-card beside :title="__('messages.header_footer_code')" :lead="__('messages.header_footer_code_intro')">
            <form method="POST" action="{{ route('admin.settings.update') }}" class="{{ $formClass }}">
                @csrf

                <div class="page-form-fields">
                    <x-page-notice tone="warn">{{ __('messages.header_footer_code_warning') }}</x-page-notice>

                    <div>
                        <x-input-label for="custom_header_code" :value="__('messages.custom_header_code')" />
                        <textarea id="custom_header_code" name="custom_header_code" rows="8" dir="ltr" {{ $demo ? 'disabled' : '' }}
                            class="{{ $codeClass }}"
                            placeholder="<!-- Google Tag Manager -->">{{ old('custom_header_code', $custom_header_code) }}</textarea>
                        <p class="sys-help">{{ __('messages.custom_header_code_help') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('custom_header_code')" />
                    </div>

                    <div>
                        <x-input-label for="custom_footer_code" :value="__('messages.custom_footer_code')" />
                        <textarea id="custom_footer_code" name="custom_footer_code" rows="8" dir="ltr" {{ $demo ? 'disabled' : '' }}
                            class="{{ $codeClass }}">{{ old('custom_footer_code', $custom_footer_code) }}</textarea>
                        <p class="sys-help">{{ __('messages.custom_footer_code_help') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('custom_footer_code')" />
                    </div>

                    @if ($demo)
                    <x-page-notice tone="warn">{{ __('messages.demo_mode_settings_disabled') }}</x-page-notice>
                    @endif
                </div>

                <div class="page-form-actions">
                    <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
                </div>
            </form>
        </x-page-card>

        @if ($federationAvailable)
        {{-- Anchor target for the dashboard adoption prompt's "Open settings" link. --}}
        <x-page-card beside id="federation" class="scroll-mt-24" :title="__('messages.federation_settings_title')" :lead="__('messages.federation_settings_description')">
            <div class="page-form-fields">
                {{-- A failed sync is otherwise completely silent, and a silent sync failure
                     is the most likely long-run failure mode. Mapped to a small set of
                     states rather than echoing the raw response back to the screen.
                     "rejected" has its own wording now that the hourly run reconnects. --}}
                @if ($federationLastError)
                    @php
                        $federationErrorKey = $federationLastError === 'rejected' ? 'rejected_reconnecting' : $federationLastError;
                    @endphp
                    <x-page-notice tone="warn">{{ __('messages.federation_error_'.$federationErrorKey) }}</x-page-notice>
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
                        // The kit's status mark, where a coloured pill used to be.
                        $federationMark = match ($federationStateKey) {
                            'approved' => ['is-on', __('messages.federation_status_approved')],
                            'pending' => ['is-warn', __('messages.federation_status_pending')],
                            'suspended' => ['is-bad', __('messages.federation_status_suspended')],
                            default => ['', __('messages.federation_not_connected')],
                        };
                    @endphp
                    <div class="sys-panel">
                        <div class="sys-panel-head">
                            <span class="flex flex-wrap items-center gap-x-3 gap-y-1">
                                <strong>{{ __('messages.federation_connection') }}</strong>
                                <span class="event-status {{ $federationMark[0] }}">{{ $federationMark[1] }}</span>
                            </span>
                            <span>
                                @if ($federationLastSyncedAt)
                                    {{ __('messages.federation_last_synced', ['time' => \Carbon\Carbon::parse($federationLastSyncedAt)->diffForHumans()]) }}
                                @else
                                    {{ __('messages.federation_never_synced') }}
                                @endif
                            </span>
                        </div>

                        <p>
                            @if ($federationStateKey === 'approved')
                                {{ trans_choice('messages.federation_state_approved', $federationSentTotal, ['count' => number_format($federationSentTotal)]) }}
                            @else
                                {{ __('messages.federation_state_'.$federationStateKey) }}
                            @endif
                        </p>

                        {{-- Only the network can build this link (the id in it is encoded with its
                             key), so it arrives with every sync; FederationService only keeps one
                             that points at the configured network. --}}
                        @if ($federationStateKey === 'approved' && $federationListingsUrl && $federationSentTotal > 0)
                            <p>
                                <x-link href="{{ $federationListingsUrl }}" target="_blank">{{ __('messages.federation_see_listings') }}</x-link>
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <form method="POST" action="{{ route('admin.settings.update') }}" class="{{ $formClass }} {{ $federationLastError || $federationConnected ? 'mt-5' : '' }}">
                @csrf

                {{-- Both cards post to the same endpoint, so each has to be explicit
                     about what it owns. This one marks itself, so the controller saves the
                     federation settings and leaves the header/footer code alone - carrying
                     that code through as hidden inputs would write back whatever this copy
                     of the page held, over a newer save from another tab. --}}
                <input type="hidden" name="federation_settings_submitted" value="1">

                <div class="page-form-fields">
                    <div>
                        <x-toggle
                            id="federation_enabled"
                            name="federation_enabled"
                            :checked="old('federation_enabled', $federationEnabled)"
                            :label="__('messages.federation_enable')"
                            :disabled="$demo" />
                    </div>

                    <div>
                        <x-input-label for="federation_contact_email" :value="__('messages.federation_contact_email')" />
                        <x-text-input id="federation_contact_email" name="federation_contact_email" type="email"
                            class="mt-1 block w-full" :value="old('federation_contact_email', $federationContactEmail)"
                            :disabled="$demo" />
                        <p class="sys-help">{{ __('messages.federation_contact_email_note') }}</p>
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
                        <fieldset>
                            <legend class="sys-sub">{{ __('messages.federation_list_these_title') }}</legend>
                            <p class="sys-help" style="margin-top: 0">{{ __('messages.federation_list_these_help', [
                                'edit' => __('messages.edit_schedule'),
                                'schedule_settings' => __('messages.schedule_settings'),
                                'advanced' => __('messages.advanced'),
                            ]) }}</p>

                            {{-- No max-height: every row is ticked on the enabling save, so every row
                                 has to be in view. --}}
                            <ul class="sys-checks" v-pre>
                                @foreach ($federationMySchedules as $mySchedule)
                                    @php
                                        $myHash = \App\Utils\UrlUtils::encodeId($mySchedule->id);
                                        $myCount = (int) ($federationMyCounts[$mySchedule->id] ?? 0);
                                        $myTicked = $federationResubmitted ? $federationOldTicked->contains($myHash) : ! $federationEnabled;
                                    @endphp
                                    <li>
                                        <label>
                                            <input type="checkbox" name="list_schedules[]" value="{{ $myHash }}" @checked($myTicked) @disabled($demo)
                                                   class="mt-1 rounded border-gray-300 dark:border-gray-600 dark:bg-gray-700 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                            <span>
                                                <span><bdi>{{ $mySchedule->name }}</bdi></span>
                                                {{-- getGuestUrl() is empty until a schedule is verified. --}}
                                                @if ($myGuestUrl = $mySchedule->getGuestUrl())
                                                    <span><bdi dir="ltr">{{ $myGuestUrl }}</bdi></span>
                                                @endif
                                                <span>
                                                    {{ trans_choice('messages.federation_would_share_count', $myCount, ['count' => number_format($myCount)]) }}
                                                    @if (isset($federationMyHeldBack[$mySchedule->id]))
                                                        &middot; {{ __('messages.federation_needs_verification') }}
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
                        <div>
                            <p class="sys-sub">{{ __('messages.federation_preview_schedules_title') }}</p>
                            <ul class="sys-list" v-pre>
                                @foreach ($federationPreviewSchedules as $previewSchedule)
                                    <li>
                                        <span><bdi>{{ $previewSchedule->name }}</bdi></span>
                                        <span><bdi dir="ltr">{{ $previewSchedule->getGuestUrl() }}</bdi></span>
                                    </li>
                                @endforeach
                            </ul>

                            @if ($federationPreviewSchedulesTotal > $federationPreviewSchedules->count())
                                <p class="sys-help">
                                    {{ __('messages.federation_preview_more', ['count' => $federationPreviewSchedulesTotal - $federationPreviewSchedules->count()]) }}
                                </p>
                            @endif
                        </div>
                    @endif

                    {{-- Publishing customers' events to a third-party site sight-unseen is
                         the real anxiety here, so show exactly what would go out - and, per
                         event, whether it has gone, and why not if it cannot. --}}
                    <div>
                        <p class="sys-sub">{{ __('messages.federation_preview_title') }}</p>

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
                            <ul class="sys-list" v-pre id="federation-preview">
                                @foreach ($federationPreview as $previewEvent)
                                    @php
                                        $previewState = $federationPreviewStates[$previewEvent->id] ?? 'next_sync';
                                        $previewTone = match ($previewState) {
                                            'sent' => 'is-on',
                                            'needs_image' => 'is-warn',
                                            'skipped' => 'is-bad',
                                            default => 'is-info',
                                        };
                                    @endphp
                                    <li>
                                        <span><bdi>{{ $previewEvent->name }}</bdi></span>
                                        @if ($previewEvent->starts_at)
                                            <span>{{ \Carbon\Carbon::parse($previewEvent->starts_at)->format('M j, Y') }}</span>
                                        @endif
                                        {{-- Sync state only means something while sharing is on; a missing
                                             image is worth knowing either way. --}}
                                        @if ($federationEnabled || $previewState === 'needs_image')
                                            <span class="event-status {{ $previewTone }}">@lang('messages.federation_pill_'.$previewState)</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>

                            @if ($federationPreviewTotal > $federationPreview->count())
                                <p class="sys-help">
                                    {{ __('messages.federation_preview_more', ['count' => $federationPreviewTotal - $federationPreview->count()]) }}
                                </p>
                            @endif
                        @endif

                        {{-- Both of these hold schedules back on purpose, and both look
                             exactly like a bug unless they are stated - especially on a
                             fresh install, where every schedule is undecided and the
                             preview above is therefore empty. --}}
                        @if ($federationUnverified > 0 || $federationUndecided > 0)
                            <div class="mt-3">
                                @if ($federationUnverified > 0)
                                    <p class="sys-help">{{ trans_choice('messages.federation_unverified_count', $federationUnverified, ['count' => $federationUnverified]) }}</p>
                                @endif

                                @if ($federationUndecided > 0)
                                    <p class="sys-help">{{ trans_choice('messages.federation_undecided_others_count', $federationUndecided, ['count' => $federationUndecided]) }}</p>
                                @endif
                            </div>
                        @endif
                    </div>
                </div>

                <div class="page-form-actions">
                    <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
                </div>
            </form>
        </x-page-card>
        @endif

        @if ($adsAvailable)
        <x-page-card beside id="monetization" class="scroll-mt-24" :title="__('messages.monetization_settings_title')" :lead="__('messages.monetization_settings_description')">
            {{-- This card posts to its own endpoint rather than sharing admin.settings.update.
                 The shared-endpoint cards above each have to carry every other card's values
                 through as hidden inputs, which is quadratic in the number of cards and fails
                 silently (saving one card wipes another). A dedicated action owns only the
                 ads_* keys, so no pass-through is needed and no card can clobber another. --}}
            <form method="POST" action="{{ route('admin.settings.update_ads') }}" class="{{ $formClass }}">
                @csrf

                <div class="page-form-fields">
                    <x-page-notice tone="warn">{{ __('messages.monetization_settings_warning') }}</x-page-notice>

                    <div>
                        <x-toggle
                            id="ads_adsense_enabled"
                            name="ads_adsense_enabled"
                            :checked="old('ads_adsense_enabled', $adsAdsenseEnabled)"
                            :label="__('messages.monetization_adsense_enable')"
                            :help="e(__('messages.monetization_adsense_enable_help'))"
                            :disabled="$demo" />
                    </div>

                    <div>
                        <x-input-label for="ads_adsense_client_id" :value="__('messages.monetization_adsense_client_id')" />
                        <x-text-input id="ads_adsense_client_id" name="ads_adsense_client_id" type="text"
                            class="mt-1 block w-full font-mono text-sm" placeholder="ca-pub-0000000000000000"
                            :value="old('ads_adsense_client_id', $adsAdsenseClientId)"
                            :disabled="$demo" />
                        <p class="sys-help">{{ __('messages.monetization_adsense_client_id_help') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('ads_adsense_client_id')" />
                    </div>

                    <div>
                        <x-input-label for="ads_adsense_slot_id" :value="__('messages.monetization_adsense_slot_id')" />
                        <x-text-input id="ads_adsense_slot_id" name="ads_adsense_slot_id" type="text"
                            class="mt-1 block w-full font-mono text-sm" placeholder="1234567890"
                            :value="old('ads_adsense_slot_id', $adsAdsenseSlotId)"
                            :disabled="$demo" />
                        <p class="sys-help">{{ __('messages.monetization_adsense_slot_id_help') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('ads_adsense_slot_id')" />
                    </div>

                    <div>
                        <x-toggle
                            id="ads_personalized"
                            name="ads_personalized"
                            :checked="old('ads_personalized', $adsPersonalized)"
                            :label="__('messages.monetization_personalized')"
                            :help="e(__('messages.monetization_personalized_help'))"
                            :disabled="$demo" />
                    </div>

                    <div class="sys-rule">
                        <x-toggle
                            id="ads_native_enabled"
                            name="ads_native_enabled"
                            :checked="old('ads_native_enabled', $adsNativeEnabled)"
                            :label="__('messages.monetization_native_enable')"
                            :help="e(__('messages.monetization_native_enable_help'))"
                            :disabled="$demo" />
                    </div>

                    <div>
                        <x-toggle
                            id="ads_native_priority"
                            name="ads_native_priority"
                            :checked="old('ads_native_priority', $adsNativePriority)"
                            :label="__('messages.monetization_native_priority')"
                            :help="e(__('messages.monetization_native_priority_help'))"
                            :disabled="$demo" />
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="ads_native_cpm" :value="__('messages.monetization_native_cpm')" />
                            <x-text-input id="ads_native_cpm" name="ads_native_cpm" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" :value="old('ads_native_cpm', $adsNativeCpm)"
                                :disabled="$demo" />
                            <p class="sys-help">{{ __('messages.monetization_native_cpm_help') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('ads_native_cpm')" />
                        </div>

                        <div>
                            <x-input-label for="ads_native_cpc" :value="__('messages.monetization_native_cpc')" />
                            <x-text-input id="ads_native_cpc" name="ads_native_cpc" type="number" step="0.01" min="0"
                                class="mt-1 block w-full" :value="old('ads_native_cpc', $adsNativeCpc)"
                                :disabled="$demo" />
                            <p class="sys-help">{{ __('messages.monetization_native_cpc_help') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('ads_native_cpc')" />
                        </div>
                    </div>

                    @if ($demo)
                    <x-page-notice tone="warn">{{ __('messages.demo_mode_settings_disabled') }}</x-page-notice>
                    @endif
                </div>

                <div class="page-form-actions">
                    <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
                </div>
            </form>
        </x-page-card>
        @endif

        @if ($planPricingAvailable)
        {{-- Before the currency card, not after: the amounts come first and the currency is what
             they are printed in. Gated on hosted-or-nexus, because a plain selfhost has no
             surface that quotes a plan price - see AdminController::settings(). --}}
        <x-page-card beside id="plan-pricing" class="scroll-mt-24" :title="__('messages.plan_pricing_title')" :lead="__('messages.plan_pricing_description')">
            <form method="POST" action="{{ route('admin.settings.update_plan_pricing') }}" class="{{ $formClass }}">
                @csrf

                <div class="page-form-fields">
                    <div>
                        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                            <div>
                                <x-input-label for="plan_price_pro_monthly" :value="__('messages.plan_pricing_pro_monthly')" />
                                <x-text-input id="plan_price_pro_monthly" name="plan_price_pro_monthly" type="number" step="0.01" min="0.01"
                                    class="mt-1 block w-full"
                                    :disabled="$demo"
                                    :value="old('plan_price_pro_monthly', $planPricingStored['pro_monthly'])"
                                    placeholder="{{ $planPricingEffective['pro_monthly'] }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('plan_price_pro_monthly')" />
                            </div>
                            <div>
                                <x-input-label for="plan_price_pro_yearly" :value="__('messages.plan_pricing_pro_yearly')" />
                                <x-text-input id="plan_price_pro_yearly" name="plan_price_pro_yearly" type="number" step="0.01" min="0.01"
                                    class="mt-1 block w-full"
                                    :disabled="$demo"
                                    :value="old('plan_price_pro_yearly', $planPricingStored['pro_yearly'])"
                                    placeholder="{{ $planPricingEffective['pro_yearly'] }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('plan_price_pro_yearly')" />
                            </div>
                            <div>
                                <x-input-label for="plan_price_enterprise_monthly" :value="__('messages.plan_pricing_enterprise_monthly')" />
                                <x-text-input id="plan_price_enterprise_monthly" name="plan_price_enterprise_monthly" type="number" step="0.01" min="0.01"
                                    class="mt-1 block w-full"
                                    :disabled="$demo"
                                    :value="old('plan_price_enterprise_monthly', $planPricingStored['enterprise_monthly'])"
                                    placeholder="{{ $planPricingEffective['enterprise_monthly'] }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('plan_price_enterprise_monthly')" />
                            </div>
                            <div>
                                <x-input-label for="plan_price_enterprise_yearly" :value="__('messages.plan_pricing_enterprise_yearly')" />
                                <x-text-input id="plan_price_enterprise_yearly" name="plan_price_enterprise_yearly" type="number" step="0.01" min="0.01"
                                    class="mt-1 block w-full"
                                    :disabled="$demo"
                                    :value="old('plan_price_enterprise_yearly', $planPricingStored['enterprise_yearly'])"
                                    placeholder="{{ $planPricingEffective['enterprise_yearly'] }}" />
                                <x-input-error class="mt-2" :messages="$errors->get('plan_price_enterprise_yearly')" />
                            </div>
                        </div>
                        <p class="sys-help">{{ __('messages.plan_pricing_help') }}</p>
                    </div>

                    <x-page-notice tone="warn">{{ __('messages.plan_pricing_display_only') }}</x-page-notice>

                    @if ($demo)
                    <x-page-notice tone="warn">{{ __('messages.demo_mode_settings_disabled') }}</x-page-notice>
                    @endif
                </div>

                <div class="page-form-actions">
                    <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
                </div>
            </form>
        </x-page-card>
        @endif

        {{-- Always rendered. Even a selfhost with no plans to price uses this as the
             default currency for a new event. --}}
        <x-page-card beside id="currency" class="scroll-mt-24" :title="__('messages.platform_currency_title')" :lead="__('messages.platform_currency_description')">
            <form method="POST" action="{{ route('admin.settings.update_currency') }}" class="{{ $formClass }}">
                @csrf

                <div class="page-form-fields">
                    <div>
                        <x-input-label for="platform_currency" :value="__('messages.currency')" />
                        <select id="platform_currency" name="platform_currency" {{ $demo ? 'disabled' : '' }}
                            class="mt-1 block w-full sm:max-w-sm border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] rounded-lg shadow-sm">
                            @foreach ($currencies as $currency)
                            @if ($loop->index == 2)
                            <option disabled>──────────</option>
                            @endif
                            <option value="{{ $currency->value }}" {{ old('platform_currency', $platformCurrency) == $currency->value ? 'selected' : '' }}>
                                {{ $currency->value }} - {{ $currency->label }}
                            </option>
                            @endforeach
                        </select>
                        <p class="sys-help">{{ __('messages.platform_currency_help') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('platform_currency')" />
                    </div>

                    @if (config('app.hosted'))
                    <x-page-notice tone="warn">{{ __('messages.platform_currency_display_only') }}</x-page-notice>
                    @endif

                    @if ($demo)
                    <x-page-notice tone="warn">{{ __('messages.demo_mode_settings_disabled') }}</x-page-notice>
                    @endif
                </div>

                <div class="page-form-actions">
                    <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
                </div>
            </form>
        </x-page-card>

        {{-- Its own form and route (admin.settings.update_realtime): the shared settings endpoint
             overwrites the header and footer code unless the federation marker is posted. --}}
        <x-page-card beside id="realtime" class="scroll-mt-24" :title="__('messages.realtime_settings_title')" :lead="__('messages.realtime_settings_description')">
            <form method="POST" action="{{ route('admin.settings.update_realtime') }}" class="{{ $formClass }}">
                @csrf

                <div class="page-form-fields">
                    <div>
                        <x-toggle
                            id="realtime_enabled"
                            name="realtime_enabled"
                            :checked="old('realtime_enabled', $realtimeEnabled)"
                            :label="e(__('messages.realtime_settings_toggle'))"
                            :help="e(__('messages.realtime_settings_help'))"
                            :disabled="$demo" />
                    </div>

                    {{-- The second switch. Its marker says the switch was on the page: an unchecked box
                         posts nothing, and a request without it leaves the setting alone. --}}
                    <div>
                        <input type="hidden" name="realtime_owner_view_submitted" value="1">
                        <x-toggle
                            id="realtime_owner_view"
                            name="realtime_owner_view"
                            :checked="old('realtime_owner_view', $realtimeOwnerView)"
                            :label="e(__('messages.realtime_owner_view_toggle'))"
                            :help="e(__('messages.realtime_owner_view_help'))"
                            :disabled="$demo" />
                    </div>
                </div>

                <div class="page-form-actions">
                    <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
                </div>
            </form>
        </x-page-card>

        {{-- Deliberately its own card and its own endpoint rather than part of the
             monetization card above. updateAdsSettings() hard-returns unless
             ADS_ENABLED && hosted && ! nexus, which would make this field unreachable in the
             two cases that matter most: an install with ads switched off, and the nexus,
             which is precisely the operator that wants a fallback affiliate ID. The
             monetization heading would also misdescribe it, since that feature is scoped to
             free-tier schedules on multi-tenant hosted installs and this one is none of
             those. --}}
        @if ($stay22Available)
        <x-page-card beside id="accommodation" class="scroll-mt-24" :title="__('messages.stay22_settings_title')" :lead="__('messages.stay22_settings_description')">
            <form method="POST" action="{{ route('admin.settings.update_stay22') }}" class="{{ $formClass }}">
                @csrf

                <div class="page-form-fields">
                    <div>
                        <x-input-label for="stay22_aid" :value="__('messages.stay22_operator_aid')" />
                        <x-text-input id="stay22_aid" name="stay22_aid" type="text"
                            class="mt-1 block w-full sm:max-w-sm font-mono text-sm"
                            :value="old('stay22_aid', $stay22Aid)"
                            :disabled="$demo" />
                        <p class="sys-help">{{ __('messages.stay22_operator_aid_help') }}</p>
                        <x-input-error class="mt-2" :messages="$errors->get('stay22_aid')" />
                    </div>

                    @if ($demo)
                    <x-page-notice tone="warn">{{ __('messages.demo_mode_settings_disabled') }}</x-page-notice>
                    @endif
                </div>

                <div class="page-form-actions">
                    <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
                </div>
            </form>
        </x-page-card>
        @endif
    </div>
</x-app-admin-layout>
