<x-app-admin-layout>

    <x-slot name="head">
        {{-- One venue of a group, as a row to choose: the one being kept says so with its border. --}}
        <style {!! nonce_attr() !!}>
            .merge-target {
              display: flex;
              flex-wrap: wrap;
              align-items: baseline;
              gap: 0.125rem 0.5rem;
              margin: 0 0 1rem;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-2));
            }
            .merge-target .event-chip {
              margin-inline-start: 0;
            }
            /* A class that sets display outranks the hidden attribute. */
            .merge-target [hidden] {
              display: none;
            }
            .merge-options {
              display: grid;
              gap: 0.5rem;
            }
            .merge-option {
              display: flex;
              align-items: flex-start;
              gap: 0.75rem;
              border: 1px solid rgb(var(--ap-border));
              border-radius: 0.625rem;
              padding: 0.75rem;
              cursor: pointer;
              transition: background-color 0.2s, border-color 0.2s;
            }
            .merge-option:hover {
              background: var(--ap-tint-1);
            }
            .merge-option:has(input:checked) {
              border-color: var(--brand-blue);
              background: var(--ap-tint-1);
            }
            .merge-option input {
              margin-top: 0.1875rem;
            }
            .merge-option-name {
              font-weight: 600;
              color: rgb(var(--ap-ink));
              overflow-wrap: anywhere;
            }
            a.merge-option-name:hover {
              text-decoration: underline;
            }
            .merge-option-sub {
              margin-top: 0.125rem;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .merge-summary {
              font-size: 0.875rem;
              color: rgb(var(--ap-ink-3));
            }
        </style>
    </x-slot>

    @php
        // One view, two addresses: a curator schedule's own duplicates (/{subdomain}/merge-venues)
        // and the account's (/following/merge-venues). The way back is named after the page it
        // leads to, the schedule or Following, where it used to be a button that said "Back".
        $mergeSubdomain = request()->route('subdomain');
        $mergeRole = $mergeSubdomain ? \App\Models\Role::subdomain($mergeSubdomain)->first() : null;

        // A count after its name, so no language has to agree a noun with it: a pair of
        // duplicates, which is most groups, read "1 venues, 1 events".
        $eventCountLabel = $eventCountKey === 'merge_venues_future_events_count' ? 'merge_venues_upcoming_events_label' : 'merge_venues_events_label';
    @endphp

    <div class="page-shell">
        <x-page-header
            :title="__('messages.merge_venues_title')"
            :lead="__('messages.' . $introKey)"
            :back="$backUrl"
            :back-label="$mergeRole ? $mergeRole->getDisplayName(false) : __('messages.following')" />

        @if (empty($groups))
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.' . $emptyStateKey)"
                    icon="M9 12.75L11.25 15 15 9.75M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </div>
        @else
            <div class="page-stack">
            @foreach ($groups as $groupIndex => $group)
                @php
                    // Default target preselection: claimed > non-deleted > most future events > lowest id.
                    // Shared with the event form's venue picker so both surfaces keep the same venue.
                    $defaultTarget = \App\Utils\VenueUtils::pickBest($group);
                    $groupHash = $group[0]->ids_hash;
                @endphp

                <div class="ap-card rounded-xl page-card" id="group-{{ $groupHash }}">

                    <p class="merge-target">
                        <span>{{ __('messages.merge_venues_will_merge_into') }}</span>
                        <bdi class="font-semibold text-gray-900 dark:text-gray-100" data-target-name="{{ $groupHash }}">{{ $defaultTarget->getDisplayName(false) }}</bdi>
                        <bdi dir="ltr" class="text-xs text-gray-500 dark:text-gray-400" data-target-subdomain="{{ $groupHash }}">/{{ $defaultTarget->subdomain }}</bdi>
                        <span class="event-chip" data-target-deleted="{{ $groupHash }}" @if (! $defaultTarget->is_deleted) hidden @endif>{{ __('messages.deleted_tag') }}</span>
                        <bdi data-target-city="{{ $groupHash }}" @if (! $defaultTarget->city) hidden @endif>{{ $defaultTarget->city }}</bdi>
                    </p>

                    <form method="POST" action="{{ $mergeUrl }}"
                          class="merge-group-form" data-group-hash="{{ $groupHash }}">
                        @csrf
                        <input type="hidden" name="target_id" value="{{ \App\Utils\UrlUtils::encodeId($defaultTarget->id) }}" data-target-input="{{ $groupHash }}">
                        @foreach ($group as $venue)
                            @if ($venue->id !== $defaultTarget->id)
                                <input type="hidden" name="source_ids[]" value="{{ \App\Utils\UrlUtils::encodeId($venue->id) }}" data-source-input="{{ $groupHash }}-{{ $venue->id }}">
                            @endif
                        @endforeach

                        <div class="merge-options">
                            @foreach ($group as $venue)
                                <label class="merge-option">
                                    <input type="radio" name="target_choice_{{ $groupHash }}" value="{{ \App\Utils\UrlUtils::encodeId($venue->id) }}"
                                           data-group-radio="{{ $groupHash }}"
                                           data-venue-name="{{ $venue->getDisplayName(false) }}"
                                           data-venue-subdomain="{{ $venue->subdomain }}"
                                           data-venue-city="{{ $venue->city }}"
                                           data-venue-deleted="{{ $venue->is_deleted ? '1' : '0' }}"
                                           data-future-events="{{ $venue->future_event_count }}"
                                           class="text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                                           {{ $venue->id === $defaultTarget->id ? 'checked' : '' }}>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-baseline gap-x-2 gap-y-1">
                                            @php
                                                $venueUrl = $venue->getGuestUrl();
                                            @endphp
                                            @if ($venueUrl)
                                                <a href="{{ $venueUrl }}" target="_blank" rel="noopener" class="merge-option-name"><bdi>{{ $venue->name }}</bdi></a>
                                            @else
                                                <span class="merge-option-name"><bdi>{{ $venue->name }}</bdi></span>
                                            @endif
                                            <bdi dir="ltr" class="text-xs text-gray-500 dark:text-gray-400">/{{ $venue->subdomain }}</bdi>
                                            @if ($venue->is_deleted)
                                                <span class="event-chip">{{ __('messages.deleted_tag') }}</span>
                                            @endif
                                        </div>
                                        <div class="merge-option-sub">
                                            @php
                                                $venuePlace = implode(', ', array_filter([$venue->city, $venue->country_code ? strtoupper($venue->country_code) : null]));
                                            @endphp
                                            @if ($venuePlace)<bdi>{{ $venuePlace }}</bdi> &middot; @endif{{ __('messages.' . $eventCountLabel, ['count' => $venue->future_event_count]) }}
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        {{-- What will happen, then the two answers: the one that goes on last. --}}
                        <div class="page-form-actions is-split">
                            <div class="merge-summary" data-merge-summary="{{ $groupHash }}">
                                {{ __('messages.merge_venues_summary_counts', ['venues' => count($group) - 1, 'events' => collect($group)->where('id', '!=', $defaultTarget->id)->sum('future_event_count')]) }}
                            </div>
                            <div class="page-actions">
                                <button type="button" class="dismiss-group-btn ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800"
                                        data-group-hash="{{ $groupHash }}">
                                    {{ __('messages.merge_venues_not_duplicates_button') }}
                                </button>
                                <x-brand-button type="button" class="merge-group-btn" data-group-hash="{{ $groupHash }}">
                                    {{ __('messages.merge_venues_merge_button') }}
                                </x-brand-button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ $dismissUrl }}"
                          class="hidden dismiss-form" data-group-hash="{{ $groupHash }}">
                        @csrf
                        @foreach ($group as $venue)
                            <input type="hidden" name="venue_ids[]" value="{{ \App\Utils\UrlUtils::encodeId($venue->id) }}">
                        @endforeach
                    </form>
                </div>
            @endforeach
            </div>
        @endif

    </div>

    <script {!! nonce_attr() !!}>
    (function() {
        var summaryTemplate = @json(__('messages.merge_venues_summary_counts'));
        var previewSummaryTemplate = @json(__('messages.merge_venues_preview_summary'));
        var reviveSuffixTemplate = @json(__('messages.merge_venues_preview_revive_suffix'));
        var errorMsg = @json(__('messages.an_error_occurred'));
        var dismissConfirmMsg = @json(__('messages.merge_venues_not_duplicates_confirm'));
        var previewUrl = @json($previewUrl);

        // Sync radio selection -> hidden target_id, hidden source_ids, header pieces, and summary line.
        document.querySelectorAll('[data-group-radio]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var hash = radio.getAttribute('data-group-radio');
                var form = document.querySelector('.merge-group-form[data-group-hash="' + hash + '"]');
                if (!form) return;

                var targetInput = form.querySelector('[data-target-input="' + hash + '"]');
                if (targetInput) targetInput.value = radio.value;

                // Rebuild source_ids[] from all non-selected radios in this group, and tally source events.
                form.querySelectorAll('input[name="source_ids[]"]').forEach(function (el) { el.remove(); });
                var totalSourceEvents = 0;
                var sourceCount = 0;
                document.querySelectorAll('[data-group-radio="' + hash + '"]').forEach(function (other) {
                    if (other.value !== radio.value) {
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'source_ids[]';
                        hidden.value = other.value;
                        form.appendChild(hidden);
                        totalSourceEvents += parseInt(other.getAttribute('data-future-events') || '0', 10);
                        sourceCount += 1;
                    }
                });

                // Update header pieces.
                var nameEl = document.querySelector('[data-target-name="' + hash + '"]');
                if (nameEl) nameEl.textContent = radio.getAttribute('data-venue-name') || '';

                var subdomainEl = document.querySelector('[data-target-subdomain="' + hash + '"]');
                if (subdomainEl) subdomainEl.textContent = '/' + (radio.getAttribute('data-venue-subdomain') || '');

                var deletedEl = document.querySelector('[data-target-deleted="' + hash + '"]');
                if (deletedEl) {
                    deletedEl.hidden = radio.getAttribute('data-venue-deleted') !== '1';
                }

                var city = radio.getAttribute('data-venue-city') || '';
                var cityEl = document.querySelector('[data-target-city="' + hash + '"]');
                if (cityEl) {
                    cityEl.textContent = city;
                    cityEl.hidden = ! city;
                }

                // Update summary line.
                var summaryEl = document.querySelector('[data-merge-summary="' + hash + '"]');
                if (summaryEl) {
                    summaryEl.textContent = summaryTemplate
                        .replace(':venues', sourceCount)
                        .replace(':events', totalSourceEvents);
                }
            });
        });

        // Merge button -> aggregate preview -> confirm -> submit.
        document.querySelectorAll('.merge-group-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var hash = btn.getAttribute('data-group-hash');
                var form = document.querySelector('.merge-group-form[data-group-hash="' + hash + '"]');
                if (!form) return;

                var fd = new FormData(form);
                var qs = new URLSearchParams();
                qs.append('target_id', fd.get('target_id') || '');
                (fd.getAll('source_ids[]') || []).forEach(function (id) { qs.append('source_ids[]', id); });

                fetch(previewUrl + '?' + qs.toString(), { headers: { 'Accept': 'application/json' } })
                    .then(function (res) { return res.json().then(function (body) { return { ok: res.ok, body: body }; }); })
                    .then(function (result) {
                        if (!result.ok) {
                            alert(result.body.error || errorMsg);
                            return;
                        }
                        var msg = previewSummaryTemplate
                            .replace(':sources', result.body.source_count)
                            .replace(':events', result.body.total_events)
                            .replace(':overlap', result.body.overlap_events)
                            .replace(/:target/g, result.body.target_name);
                        if (result.body.target_is_deleted) {
                            msg += ' ' + reviveSuffixTemplate.replace(/:target/g, result.body.target_name);
                        }
                        if (confirm(msg)) {
                            form.submit();
                        }
                    })
                    .catch(function () { alert(errorMsg); });
            });
        });

        // "Not duplicates" -> submit the hidden dismiss form.
        // Confirmed, like Merge is: nothing ever deletes a dismissal, so a misclick removes the
        // group from this page permanently and there is no undo anywhere in the app.
        document.querySelectorAll('.dismiss-group-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                if (!confirm(dismissConfirmMsg)) return;

                var hash = btn.getAttribute('data-group-hash');
                var dismissForm = document.querySelector('.dismiss-form[data-group-hash="' + hash + '"]');
                if (dismissForm) dismissForm.submit();
            });
        });
    })();
    </script>

</x-app-admin-layout>
