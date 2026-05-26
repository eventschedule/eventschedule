<x-app-admin-layout>

    <div class="space-y-4 max-w-3xl mx-auto">

        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
            <h2 class="text-xl font-bold text-gray-900 dark:text-gray-100">
                {{ __('messages.merge_venues_title') }}
            </h2>
            <x-secondary-link :href="route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'schedule'])">
                {{ __('messages.back') }}
            </x-secondary-link>
        </div>

        <p class="text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.merge_venues_intro') }}
        </p>

        @if (empty($groups))
            <div class="ap-card rounded-xl p-8 text-center">
                <svg class="w-12 h-12 mx-auto text-green-500 dark:text-green-400 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <p class="text-gray-700 dark:text-gray-300">
                    {{ __('messages.merge_venues_empty_state') }}
                </p>
            </div>
        @else
            @foreach ($groups as $groupIndex => $group)
                @php
                    // Default target preselection: claimed > non-deleted > most future events > lowest id.
                    $sorted = collect($group)->sort(function ($a, $b) {
                        $aClaimed = $a->isClaimed() ? 1 : 0;
                        $bClaimed = $b->isClaimed() ? 1 : 0;
                        if ($aClaimed !== $bClaimed) return $bClaimed - $aClaimed;
                        $aLive = $a->is_deleted ? 0 : 1;
                        $bLive = $b->is_deleted ? 0 : 1;
                        if ($aLive !== $bLive) return $bLive - $aLive;
                        if ($a->future_event_count !== $b->future_event_count) {
                            return $b->future_event_count - $a->future_event_count;
                        }
                        return $a->id - $b->id;
                    })->values();
                    $defaultTarget = $sorted->first();
                    $groupHash = $group[0]->ids_hash;
                @endphp

                <div class="ap-card rounded-xl p-5" id="group-{{ $groupHash }}">

                    <div class="mb-4 text-sm text-gray-700 dark:text-gray-300 flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="font-medium">{{ __('messages.merge_venues_will_merge_into') }}</span>
                        <span class="font-semibold text-gray-900 dark:text-gray-100" data-target-name="{{ $groupHash }}">{{ $defaultTarget->getDisplayName(false) }}</span>
                        <span class="text-xs text-gray-500 dark:text-gray-400" data-target-subdomain="{{ $groupHash }}">/{{ $defaultTarget->subdomain }}</span>
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-[#2d2d30] dark:text-gray-300" data-target-deleted="{{ $groupHash }}" @if(! $defaultTarget->is_deleted) style="display:none" @endif>
                            {{ __('messages.deleted_tag') }}
                        </span>
                        <span data-target-city="{{ $groupHash }}" @if(! $defaultTarget->city) style="display:none" @endif>@if ($defaultTarget->city), {{ $defaultTarget->city }}@endif</span>
                    </div>

                    <form method="POST" action="{{ route('role.merge_venues_group', ['subdomain' => $role->subdomain]) }}"
                          class="merge-group-form" data-group-hash="{{ $groupHash }}">
                        @csrf
                        <input type="hidden" name="target_id" value="{{ $defaultTarget->id }}" data-target-input="{{ $groupHash }}">
                        @foreach ($group as $venue)
                            @if ($venue->id !== $defaultTarget->id)
                                <input type="hidden" name="source_ids[]" value="{{ $venue->id }}" data-source-input="{{ $groupHash }}-{{ $venue->id }}">
                            @endif
                        @endforeach

                        <div class="space-y-2">
                            @foreach ($group as $venue)
                                <label class="flex items-start gap-3 p-3 rounded-lg border border-gray-200 dark:border-[#2d2d30] hover:bg-gray-50 dark:hover:bg-[#2d2d30] cursor-pointer transition-colors">
                                    <input type="radio" name="target_choice_{{ $groupHash }}" value="{{ $venue->id }}"
                                           data-group-radio="{{ $groupHash }}"
                                           data-venue-name="{{ $venue->getDisplayName(false) }}"
                                           data-venue-subdomain="{{ $venue->subdomain }}"
                                           data-venue-city="{{ $venue->city }}"
                                           data-venue-deleted="{{ $venue->is_deleted ? '1' : '0' }}"
                                           data-future-events="{{ $venue->future_event_count }}"
                                           class="mt-1 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"
                                           {{ $venue->id === $defaultTarget->id ? 'checked' : '' }}>
                                    <div class="flex-1 min-w-0">
                                        <div class="flex flex-wrap items-center gap-2">
                                            @php($venueUrl = $venue->getGuestUrl())
                                            @if ($venueUrl)
                                                <a href="{{ $venueUrl }}" target="_blank"
                                                   class="font-medium text-gray-900 dark:text-gray-100 hover:underline">
                                                    {{ $venue->name }}
                                                </a>
                                            @else
                                                <span class="font-medium text-gray-900 dark:text-gray-100">{{ $venue->name }}</span>
                                            @endif
                                            <span class="text-xs text-gray-500 dark:text-gray-400">/{{ $venue->subdomain }}</span>
                                            @if ($venue->is_deleted)
                                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-100 text-gray-700 dark:bg-[#2d2d30] dark:text-gray-300">
                                                    {{ __('messages.deleted_tag') }}
                                                </span>
                                            @endif
                                        </div>
                                        <div class="text-sm text-gray-600 dark:text-gray-400 mt-0.5">
                                            {{ implode(', ', array_filter([$venue->city, $venue->country_code ? strtoupper($venue->country_code) : null])) }}
                                            <span class="ms-2">{{ str_replace(':count', $venue->future_event_count, __('messages.merge_venues_future_events_count')) }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-2 mt-4">
                            <div class="text-sm text-gray-600 dark:text-gray-400" data-merge-summary="{{ $groupHash }}">
                                {{ str_replace([':venues', ':events'], [count($group) - 1, collect($group)->where('id', '!=', $defaultTarget->id)->sum('future_event_count')], __('messages.merge_venues_summary')) }}
                            </div>
                            <div class="flex flex-col-reverse sm:flex-row gap-2">
                                <button type="button" class="dismiss-group-btn px-4 py-3 text-base text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-[#2d2d30] rounded-lg transition-colors"
                                        data-group-hash="{{ $groupHash }}">
                                    {{ __('messages.merge_venues_not_duplicates_button') }}
                                </button>
                                <x-brand-button type="button" class="merge-group-btn" data-group-hash="{{ $groupHash }}">
                                    {{ __('messages.merge_venues_merge_button') }}
                                </x-brand-button>
                            </div>
                        </div>
                    </form>

                    <form method="POST" action="{{ route('role.merge_venues_dismiss', ['subdomain' => $role->subdomain]) }}"
                          class="hidden dismiss-form" data-group-hash="{{ $groupHash }}">
                        @csrf
                        @foreach ($group as $venue)
                            <input type="hidden" name="venue_ids[]" value="{{ $venue->id }}">
                        @endforeach
                    </form>
                </div>
            @endforeach
        @endif

    </div>

    <script {!! nonce_attr() !!}>
    (function() {
        var summaryTemplate = @json(__('messages.merge_venues_summary'));
        var previewSummaryTemplate = @json(__('messages.merge_venues_preview_summary'));
        var reviveSuffixTemplate = @json(__('messages.merge_venues_preview_revive_suffix'));
        var errorMsg = @json(__('messages.an_error_occurred'));
        var previewUrl = @json(route('role.merge_venues_preview', ['subdomain' => $role->subdomain]));

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
                    deletedEl.style.display = radio.getAttribute('data-venue-deleted') === '1' ? '' : 'none';
                }

                var city = radio.getAttribute('data-venue-city') || '';
                var cityEl = document.querySelector('[data-target-city="' + hash + '"]');
                if (cityEl) {
                    cityEl.textContent = city ? ', ' + city : '';
                    cityEl.style.display = city ? '' : 'none';
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
                            .replace(':target', result.body.target_name);
                        if (result.body.target_is_deleted) {
                            msg += ' ' + reviveSuffixTemplate.replace(':target', result.body.target_name);
                        }
                        if (confirm(msg)) {
                            form.submit();
                        }
                    })
                    .catch(function () { alert(errorMsg); });
            });
        });

        // "Not duplicates" -> submit the hidden dismiss form.
        document.querySelectorAll('.dismiss-group-btn').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var hash = btn.getAttribute('data-group-hash');
                var dismissForm = document.querySelector('.dismiss-form[data-group-hash="' + hash + '"]');
                if (dismissForm) dismissForm.submit();
            });
        });
    })();
    </script>

</x-app-admin-layout>
