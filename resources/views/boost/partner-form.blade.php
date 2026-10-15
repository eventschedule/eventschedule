<x-app-admin-layout>

    <x-slot name="head">
        @include('boost.partials.styles')
    </x-slot>

    @php
        // A boost on a schedule a boost partner covers is a request to that partner: no fee, no
        // card, no daily budget. The partner approves it and arranges payment with the Business
        // itself; nothing goes to Facebook until both are done. On edit, each field says what
        // changing it does to an ad that is already running, and Save says it too.
        $bpEditing = $campaign !== null;
        $bpPageName = $partner->displayName();
        // Help opens the part of the guide on requests: /boost/create looks the same for every boost.
        \App\Utils\HelpUtils::useDoc(\App\Utils\HelpUtils::BOOST_PARTNER_REQUESTS);
        // Dates are the schedule's: a picked end date is the end of that day where it happens.
        $bpZone = ($bpEditing ? $campaign->role?->timezone : $role->timezone) ?: config('app.timezone');
        $bpField = 'block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
        $bpLive = $bpEditing && in_array($campaign->status, \App\Models\BoostCampaign::PARTNER_LIVE_STATUSES, true) && $campaign->meta_campaign_id;
        $bpApproved = $bpEditing && $campaign->isApprovedCurrent();
        $bpPaid = $bpEditing && $campaign->isPaidCurrent();
        $bpResend = $bpEditing && in_array($campaign->status, ['declined', 'rejected'], true);

        if ($bpEditing) {
            $bpValues = [
                'headline' => $version?->headline,
                'primary_text' => $version?->primary_text,
                'description' => $version?->description,
                'call_to_action' => $version?->call_to_action ?: 'LEARN_MORE',
                'budget' => (float) $campaign->user_budget,
                'scheduled_start' => $campaign->scheduled_start,
                'scheduled_end' => $campaign->scheduled_end,
                'age_min' => $campaign->targeting['age_min'] ?? 18,
                'age_max' => $campaign->targeting['age_max'] ?? 65,
                'image_url' => $version?->imageUrl(),
                'link' => $version?->destination_url,
            ];
        } else {
            $bpValues = [
                'headline' => $defaults['headline'] ?? '',
                'primary_text' => $defaults['primary_text'] ?? '',
                'description' => $defaults['description'] ?? '',
                'call_to_action' => $defaults['call_to_action'] ?? 'LEARN_MORE',
                'budget' => min(max((float) ($defaults['budget'] ?? $minBudget), $minBudget), $maxBudget),
                'scheduled_start' => $defaults['scheduled_start'] ?? now(),
                'scheduled_end' => $defaults['scheduled_end'] ?? now()->addDays(7),
                'age_min' => $defaults['targeting']['age_min'] ?? 18,
                'age_max' => $defaults['targeting']['age_max'] ?? 65,
                'image_url' => $defaults['image_url'] ?? $event->getImageUrl(),
                'link' => $defaults['destination_url'] ?? null,
            ];
        }
        $bpLinkHost = $bpValues['link'] ? parse_url($bpValues['link'], PHP_URL_HOST) : null;
        $bpDate = fn ($value) => $value instanceof \DateTimeInterface ? \Carbon\Carbon::instance($value)->tz($bpZone)->format('Y-m-d') : (string) $value;
        // What is stored now, which the Save label is measured against: after a refused save the
        // fields hold what was typed (old()), and a running ad must not be paused unasked.
        $bpStored = fn (string $field) => $bpEditing ? (string) ($bpValues[$field] ?? '') : '';

        // What changing a field does, said under it while it matters.
        $bpCreativeHint = $bpLive ? __('messages.boost_partner_hint_pauses') : ($bpApproved ? __('messages.boost_partner_hint_needs_approval', ['page' => $bpPageName]) : null);
        $bpBudgetHint = $bpLive ? __('messages.boost_partner_hint_pauses_and_pay') : ($bpPaid ? __('messages.boost_partner_hint_needs_payment') : null);
        $bpAtOnceHint = $bpLive ? __('messages.boost_partner_hint_at_once') : null;

        $bpLabels = [
            'save' => __('messages.save'),
            'send' => __('messages.boost_partner_save_and_send'),
            'pause_send' => __('messages.boost_partner_pause_and_send'),
            'pause_save' => __('messages.boost_partner_pause_and_save'),
        ];
        $bpSubmitLabel = ! $bpEditing ? __('messages.boost_partner_send_for_approval') : ($bpResend ? $bpLabels['send'] : $bpLabels['save']);
        $bpCtas = [
            'LEARN_MORE' => __('messages.cta_learn_more'),
            'GET_TICKETS' => __('messages.cta_get_tickets'),
            'SIGN_UP' => __('messages.cta_sign_up'),
            'BOOK_TRAVEL' => __('messages.cta_book_now'),
        ];
        $bpCancel = $bpEditing ? route('boost.show', ['hash' => $campaign->hashedId()]) : route('boost.index');
    @endphp

    <div class="page-shell">
        <x-page-header
            :title="$bpEditing ? __('messages.boost_partner_edit_title') : __('messages.boost_event')"
            :lead="$event->translatedName()"
            :back="$bpCancel" :back-label="$bpEditing ? $event->translatedName() : __('messages.boost')" />

        <x-page-flash :keys="['error' => 'error']" class="mb-4" />

        @if (! $bpEditing)
        <x-page-notice tone="info" :title="__('messages.boost_partner_steps_title', ['page' => $bpPageName])" class="mb-4">
            <ol class="bp-steps">
                <li>{{ __('messages.boost_partner_step_send') }}</li>
                <li>{{ __('messages.boost_partner_step_approve', ['page' => $bpPageName]) }}</li>
                <li>{{ __('messages.boost_partner_step_runs', ['page' => $bpPageName]) }}</li>
            </ol>
        </x-page-notice>
        @elseif ($campaign->status === 'declined' && filled($campaign->approval_note))
        <x-page-notice tone="warn" :title="__('messages.boost_partner_asked_for_changes', ['page' => $bpPageName])" class="mb-4">
            <p class="bp-text-block"><bdi>{{ $campaign->approval_note }}</bdi></p>
        </x-page-notice>
        @elseif ($campaign->status === 'rejected')
        <x-page-notice tone="error" :title="__('messages.boost_partner_status_rejected')" class="mb-4">
            <p class="bp-text-block"><bdi>{{ $campaign->meta_rejection_reason ?: __('messages.boost_partner_rejected_default_reason') }}</bdi></p>
        </x-page-notice>
        @endif

        <div class="boost-cols">
        <div class="boost-cols-main">
        <form id="bp-form" method="POST" enctype="multipart/form-data"
            action="{{ $bpEditing ? route('boost.update', ['hash' => $campaign->hashedId()]) : route('boost.store') }}"
            data-editing="{{ $bpEditing ? '1' : '0' }}" data-live="{{ $bpLive ? '1' : '0' }}" data-resend="{{ $bpResend ? '1' : '0' }}"
            data-label-save="{{ $bpLabels['save'] }}" data-label-send="{{ $bpLabels['send'] }}"
            data-label-pause-send="{{ $bpLabels['pause_send'] }}" data-label-pause-save="{{ $bpLabels['pause_save'] }}"
            data-confirm-pause="{{ __('messages.boost_partner_pause_confirm', ['page' => $bpPageName]) }}">
            @csrf
            @unless ($bpEditing)
            <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">
            <input type="hidden" name="role_id" value="{{ \App\Utils\UrlUtils::encodeId($role->id) }}">
            @endunless

            {{-- What the ad says and shows. --}}
            <x-page-card :title="__('messages.boost_partner_the_ad')">
                <div class="boost-fields">
                    <div>
                        <x-input-label for="bp-headline" :value="__('messages.headline').' ('.__('messages.max_chars', ['count' => 40]).')'" />
                        <input type="text" name="headline" id="bp-headline" value="{{ old('headline', $bpValues['headline']) }}" maxlength="40" dir="auto"
                            data-bp-creative data-bp-initial="{{ $bpStored('headline') }}" data-bp-preview="headline" class="mt-1 {{ $bpField }}">
                        @include('boost.partials.partner-hint', ['bpHintText' => $bpCreativeHint, 'bpHintWarn' => $bpLive])
                        <x-input-error :messages="$errors->get('headline')" class="mt-2" />
                    </div>

                    <div>
                        <x-input-label for="bp-primary-text" :value="__('messages.primary_text').' ('.__('messages.max_chars', ['count' => 125]).')'" />
                        <textarea name="primary_text" id="bp-primary-text" maxlength="125" rows="3" dir="auto"
                            data-bp-creative data-bp-initial="{{ $bpStored('primary_text') }}" data-bp-preview="text" class="mt-1 {{ $bpField }}">{{ old('primary_text', $bpValues['primary_text']) }}</textarea>
                        @include('boost.partials.partner-hint', ['bpHintText' => $bpCreativeHint, 'bpHintWarn' => $bpLive])
                        <x-input-error :messages="$errors->get('primary_text')" class="mt-2" />
                    </div>

                    <div class="boost-fields-2 is-words">
                        <div>
                            <x-input-label for="bp-description" :value="__('messages.description').' ('.__('messages.max_chars', ['count' => 30]).')'" />
                            <input type="text" name="description" id="bp-description" value="{{ old('description', $bpValues['description']) }}" maxlength="30" dir="auto"
                                data-bp-creative data-bp-initial="{{ $bpStored('description') }}" data-bp-preview="description" class="mt-1 {{ $bpField }}">
                            @include('boost.partials.partner-hint', ['bpHintText' => $bpCreativeHint, 'bpHintWarn' => $bpLive])
                            <x-input-error :messages="$errors->get('description')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="bp-call-to-action" :value="__('messages.call_to_action')" />
                            <select name="call_to_action" id="bp-call-to-action" data-bp-creative data-bp-initial="{{ $bpStored('call_to_action') }}" data-bp-preview="cta" class="mt-1 {{ $bpField }}">
                                @foreach ($bpCtas as $bpCtaValue => $bpCtaLabel)
                                <option value="{{ $bpCtaValue }}" @selected(old('call_to_action', $bpValues['call_to_action']) === $bpCtaValue)>{{ $bpCtaLabel }}</option>
                                @endforeach
                            </select>
                            @include('boost.partials.partner-hint', ['bpHintText' => $bpCreativeHint, 'bpHintWarn' => $bpLive])
                            <x-input-error :messages="$errors->get('call_to_action')" class="mt-2" />
                        </div>
                    </div>

                    <div>
                        <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.boost_partner_picture') }}</span>
                        <div class="bp-picture mt-1">
                            @if ($bpValues['image_url'])
                            <img src="{{ $bpValues['image_url'] }}" alt="">
                            @else
                            <img src="" alt="" hidden>
                            @endif
                            <div>
                                <label for="bp-image" class="bp-hint" style="margin-top: 0">{{ $bpEditing ? __('messages.boost_partner_picture_replace') : __('messages.boost_partner_picture_event') }}</label>
                                <input type="file" name="image" id="bp-image" accept="image/jpeg,image/png,image/webp" data-bp-creative
                                    class="mt-1 block w-full text-sm text-gray-700 dark:text-gray-300">
                                @if ($bpEditing && $event->getImageUrl())
                                <div class="mt-3">
                                    <x-toggle name="use_event_image" id="bp-use-event-image" :label="__('messages.boost_partner_use_event_picture')" :checked="(bool) old('use_event_image')" data-bp-creative data-bp-initial="0" />
                                </div>
                                @endif
                                @include('boost.partials.partner-hint', ['bpHintText' => $bpCreativeHint, 'bpHintWarn' => $bpLive])
                                <x-input-error :messages="$errors->get('image')" class="mt-2" />
                            </div>
                        </div>
                    </div>
                </div>
            </x-page-card>

            {{-- What it costs and when it runs. A lifetime budget only, in the ad account's
                 currency. --}}
            <x-page-card :title="__('messages.budget_and_duration')" class="is-after-preview"
                :lead="__('messages.boost_partner_budget_lead', ['min' => \App\Utils\MoneyUtils::format($minBudget, $currency), 'max' => \App\Utils\MoneyUtils::format($maxBudget, $currency)])">
                <div class="boost-fields">
                    <div>
                        <x-input-label for="bp-budget" :value="__('messages.ad_budget').' ('.$currency.')'" />
                        <input type="number" name="budget" id="bp-budget" value="{{ old('budget', $bpValues['budget']) }}"
                            min="{{ $minBudget }}" max="{{ $maxBudget }}" step="0.01" inputmode="decimal" dir="ltr" required
                            data-bp-budget data-bp-initial="{{ $bpStored('budget') }}" class="mt-1 {{ $bpField }}">
                        @include('boost.partials.partner-hint', ['bpHintText' => $bpBudgetHint, 'bpHintWarn' => $bpLive])
                        <x-input-error :messages="$errors->get('budget')" class="mt-2" />
                    </div>

                    <div class="boost-fields-2">
                        @unless ($bpEditing)
                        <div>
                            <x-input-label for="bp-scheduled-start" :value="__('messages.start_date')" />
                            <input type="text" name="scheduled_start" id="bp-scheduled-start" value="{{ old('scheduled_start', $bpDate($bpValues['scheduled_start'])) }}"
                                autocomplete="off" class="mt-1 boost-date {{ $bpField }}">
                            <x-input-error :messages="$errors->get('scheduled_start')" class="mt-2" />
                        </div>
                        @endunless
                        <div>
                            <x-input-label for="bp-scheduled-end" :value="__('messages.end_date')" />
                            <input type="text" name="scheduled_end" id="bp-scheduled-end" value="{{ old('scheduled_end', $bpDate($bpValues['scheduled_end'])) }}"
                                data-bp-initial="{{ $bpDate($bpValues['scheduled_end']) }}" autocomplete="off" class="mt-1 boost-date {{ $bpField }}">
                            @if ($bpAtOnceHint)<p class="bp-hint">{{ $bpAtOnceHint }}</p>@endif
                            <x-input-error :messages="$errors->get('scheduled_end')" class="mt-2" />
                        </div>
                    </div>
                </div>
            </x-page-card>

            {{-- Who sees it. --}}
            <x-page-card :title="__('messages.boost_partner_audience')" class="is-after-preview">
                <div class="boost-fields">
                    @if (filled($audience))
                    <div class="boost-fixed">
                        <strong>{{ __('messages.location') }}</strong>
                        <bdi>{{ $audience }}</bdi>
                    </div>
                    @endif
                    <div class="boost-fields-2">
                        <div>
                            <x-input-label for="bp-age-min" :value="__('messages.age_min')" />
                            <input type="number" name="age_min" id="bp-age-min" value="{{ old('age_min', $bpValues['age_min']) }}" min="18" max="65" dir="ltr"
                                class="mt-1 {{ $bpField }}">
                            <x-input-error :messages="$errors->get('age_min')" class="mt-2" />
                        </div>
                        <div>
                            <x-input-label for="bp-age-max" :value="__('messages.age_max')" />
                            <input type="number" name="age_max" id="bp-age-max" value="{{ old('age_max', $bpValues['age_max']) }}" min="18" max="65" dir="ltr"
                                class="mt-1 {{ $bpField }}">
                            <x-input-error :messages="$errors->get('age_max')" class="mt-2" />
                        </div>
                    </div>
                    @if ($bpAtOnceHint)<p class="bp-hint" style="margin-top: 0">{{ $bpAtOnceHint }}</p>@endif
                </div>
            </x-page-card>

            {{-- Cancel, then the button that goes on. --}}
            <div class="page-form-actions is-after-preview" style="margin-top: 0">
                <x-secondary-link href="{{ $bpCancel }}">{{ __('messages.cancel') }}</x-secondary-link>
                <x-brand-button type="submit" id="bp-submit"><span id="bp-submit-label">{{ $bpSubmitLabel }}</span></x-brand-button>
            </div>
        </form>
        </div>

        {{-- The ad as it will look, under the partner's Page: beside the form from a laptop up,
             after the ad's own fields on a phone. --}}
        <aside class="boost-cols-aside">
            <x-page-card :title="__('messages.ad_preview')">
                @include('boost.partials.ad-preview-mockup', [
                    'headline' => old('headline', $bpValues['headline']),
                    'primaryText' => old('primary_text', $bpValues['primary_text']),
                    'imageUrl' => $bpValues['image_url'],
                    'cta' => old('call_to_action', $bpValues['call_to_action']),
                    'pageName' => $bpPageName,
                    'linkHost' => $bpLinkHost,
                    'description' => old('description', $bpValues['description']),
                ])
            </x-page-card>
        </aside>
        </div>
    </div>

    {{-- The dates use the portal's date picker, sending the same Y-m-d. --}}
    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof flatpickr === 'undefined') {
                return;
            }
            var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
            var localeConfig = fpLocale ? { locale: fpLocale } : {};
            var pickers = {};
            document.querySelectorAll('#bp-form .boost-date').forEach(function (el) {
                pickers[el.name] = flatpickr(el, Object.assign({
                    altInput: true,
                    altFormat: "M j, Y",
                    dateFormat: "Y-m-d",
                }, localeConfig));
            });
            // A start moves the earliest end with it: the server refuses an end before the start.
            if (pickers.scheduled_start && pickers.scheduled_end) {
                pickers.scheduled_start.config.onChange.push(function (dates, value) {
                    if (value) {
                        pickers.scheduled_end.set('minDate', value);
                    }
                });
            }
        });
    </script>

    {{-- The preview follows what is typed, and on edit Save says what it will do: send the ad back
         for approval, and pause it first while it runs (which asks before it goes). --}}
    <script {!! nonce_attr() !!}>
        (function () {
            var form = document.getElementById('bp-form');
            if (! form) {
                return;
            }

            function previewSet(selector, text) {
                document.querySelectorAll(selector).forEach(function (el) {
                    el.textContent = text;
                });
            }
            form.addEventListener('input', function (event) {
                var target = event.target.getAttribute ? event.target.getAttribute('data-bp-preview') : null;
                if (target === 'headline') {
                    previewSet('[data-ad-headline]', event.target.value);
                } else if (target === 'text') {
                    previewSet('[data-ad-text]', event.target.value);
                } else if (target === 'description') {
                    previewSet('[data-ad-description]', event.target.value);
                    document.querySelectorAll('[data-ad-description-line]').forEach(function (line) {
                        line.hidden = event.target.value === '';
                    });
                }
            });
            form.addEventListener('change', function (event) {
                var target = event.target.getAttribute ? event.target.getAttribute('data-bp-preview') : null;
                if (target === 'cta' && event.target.selectedOptions && event.target.selectedOptions[0]) {
                    previewSet('[data-ad-cta]', event.target.selectedOptions[0].textContent.trim());
                }
                if (event.target.id === 'bp-image' && event.target.files && event.target.files[0] && window.URL) {
                    var url = URL.createObjectURL(event.target.files[0]);
                    document.querySelectorAll('[data-ad-image], .bp-picture > img').forEach(function (img) {
                        img.src = url;
                        img.hidden = false;
                    });
                    // style, not the hidden attribute: Tailwind's .flex outranks [hidden].
                    document.querySelectorAll('[data-ad-image-placeholder]').forEach(function (el) {
                        el.style.display = 'none';
                    });
                }
            });

            // An end date nobody touched is not sent: a date without its time would read as moved.
            // Not on a submit that was called off (the layout asks first, in the capture phase).
            form.addEventListener('submit', function (event) {
                if (event.defaultPrevented) {
                    return;
                }
                var end = document.getElementById('bp-scheduled-end');
                if (form.dataset.editing === '1' && end && end.value === end.getAttribute('data-bp-initial')) {
                    end.removeAttribute('name');
                }
            });

            if (form.dataset.editing !== '1') {
                return;
            }

            // Measured against what is STORED (data-bp-initial), not against what the page opened
            // with: after a refused save the fields hold what was typed, and a change that pauses a
            // running ad must still say so and ask.
            var label = document.getElementById('bp-submit-label');
            var creative = Array.prototype.slice.call(form.querySelectorAll('[data-bp-creative]'));
            var budget = form.querySelector('[data-bp-budget]');
            var same = function (text) {
                return String(text || '').replace(/\r\n?/g, '\n');
            };
            var initial = creative.map(function (el) {
                if (el.type === 'file') {
                    return '';
                }
                if (el.type === 'checkbox') {
                    return el.getAttribute('data-bp-initial') === '1';
                }
                return same(el.hasAttribute('data-bp-initial') ? el.getAttribute('data-bp-initial') : el.value);
            });
            var initialBudget = budget ? parseFloat(budget.getAttribute('data-bp-initial') || budget.value || '0') : 0;

            function creativeChanged() {
                return creative.some(function (el, i) {
                    if (el.type === 'file') {
                        return el.files && el.files.length > 0;
                    }
                    return el.type === 'checkbox' ? el.checked !== initial[i] : same(el.value) !== initial[i];
                });
            }

            function sync() {
                var live = form.dataset.live === '1';
                var approval = form.dataset.resend === '1' || creativeChanged();
                var payment = budget && parseFloat(budget.value || '0') !== initialBudget;
                var text = form.dataset.labelSave;
                var ask = false;
                if (approval && live) {
                    text = form.dataset.labelPauseSend;
                    ask = true;
                } else if (approval) {
                    text = form.dataset.labelSend;
                } else if (payment && live) {
                    text = form.dataset.labelPauseSave;
                    ask = true;
                }
                label.textContent = text;
                // The layout's own data-confirm handler asks at submit.
                if (ask) {
                    form.setAttribute('data-confirm', form.dataset.confirmPause);
                } else {
                    form.removeAttribute('data-confirm');
                }
            }

            form.addEventListener('input', sync);
            form.addEventListener('change', sync);
            sync();
        })();
    </script>

</x-app-admin-layout>
