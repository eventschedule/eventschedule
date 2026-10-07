<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'usage'])

    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_usage_lead') }}</p>
        <div class="page-actions">
            @include('admin.partials._date-range-filter', ['range' => $range])
        </div>
    </div>

    <div class="page-shell page-stack">
        {{-- A provider past its daily limit today --}}
        @if (count($anomalies) > 0)
        <x-page-notice tone="error" :title="__('messages.usage_anomalies_detected')">
            <ul class="mt-1 list-disc ps-5 space-y-0.5">
                @foreach ($anomalies as $anomaly)
                <li>{{ __('messages.usage_anomaly_line', ['category' => $anomaly['category'], 'today' => number_format($anomaly['today']), 'limit' => number_format($anomaly['limit'])]) }}</li>
                @endforeach
            </ul>
        </x-page-notice>
        @endif

        {{-- One figure a provider: what the period came to, then today against the limit. They
             were seven separate boxes; red is for a provider that is over its limit today. --}}
        <div class="ap-card rounded-xl page-stats is-auto insight-strip">
            @foreach ($categorySummaries as $key => $cat)
            @php $over = $cat['limit'] && $cat['today_total'] > $cat['limit']; @endphp
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($cat['period_total']) }}</div>
                <div class="page-stat-label">{{ $cat['label'] }}</div>
                <div class="page-stat-sub {{ $over ? 'usage-over' : '' }}">
                    @lang('messages.today'): <span dir="ltr">{{ number_format($cat['today_total']).($cat['limit'] ? ' / '.number_format($cat['limit']) : '') }}</span>
                    @if ($over)
                    <span class="sr-only">@lang('messages.over_limit')</span>
                    @endif
                </div>
                <div class="page-stat-sub">@lang('messages.avg_per_day', ['avg' => $cat['daily_avg']])</div>
            </div>
            @endforeach
        </div>

        <x-page-card flush :title="__('messages.operation_breakdown')">
            @if (count($operationBreakdown) > 0)
            <table class="page-table">
                <thead>
                    <tr>
                        <th scope="col">@lang('messages.operation')</th>
                        <th scope="col" class="c-num">@lang('messages.today')</th>
                        <th scope="col" class="c-num">@lang('messages.period_total')</th>
                        <th scope="col" class="c-num">@lang('messages.daily_avg')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($operationBreakdown as $row)
                    <tr>
                        <td class="c-main c-mono" dir="ltr">{{ $row['operation'] }}</td>
                        <td class="c-num" data-label="{{ __('messages.today') }}">{{ number_format($row['today']) }}</td>
                        <td class="c-num c-strong" data-label="{{ __('messages.period_total') }}">{{ number_format($row['period_total']) }}</td>
                        <td class="c-num c-quiet" data-label="{{ __('messages.daily_avg') }}">{{ $row['daily_avg'] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <x-page-empty compact :title="__('messages.no_usage_data')" />
            @endif
        </x-page-card>

        <x-page-card flush :title="__('messages.top_schedules_by_usage')">
            @if ($topRolesData->count() > 0)
            <div class="page-scroll">
                <table class="page-table is-wide is-hover">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.schedule')</th>
                            <th scope="col" class="c-num">@lang('messages.total')</th>
                            @foreach ($categories as $key => $cat)
                            <th scope="col" class="c-num">{{ $cat['label'] }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($topRolesData as $roleData)
                        <tr>
                            <td class="c-main c-strong">
                                <a href="{{ route('role.view_guest', ['subdomain' => $roleData['subdomain']]) }}" class="event-link" target="_blank" rel="noopener" dir="ltr">{{ $roleData['subdomain'] }}</a>
                            </td>
                            <td class="c-num c-strong" data-label="{{ __('messages.total') }}">{{ number_format($roleData['total']) }}</td>
                            {{-- A provider the schedule never touched is left empty, so the columns
                                 that do hold a number can be found. --}}
                            @foreach ($categories as $key => $cat)
                            <td class="c-num c-quiet" data-label="{{ $cat['label'] }}">{{ ($roleData['categories'][$key] ?? 0) > 0 ? number_format($roleData['categories'][$key]) : '' }}</td>
                            @endforeach
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-page-empty compact :title="__('messages.no_schedule_usage_data')" />
            @endif
        </x-page-card>

        {{-- Who sends the most newsletters, and on whose mail server. A schedule sending in
             volume through the platform's own is the one this list exists to find. --}}
        <x-page-card flush :title="__('messages.top_newsletter_senders')">
            @if ($topNewsletterData->count() > 0)
            <table class="page-table is-hover">
                <thead>
                    <tr>
                        <th scope="col">@lang('messages.schedule')</th>
                        <th scope="col" class="c-num">@lang('messages.emails_sent')</th>
                        <th scope="col">SMTP</th>
                        {{-- Hosted only: off it actualPlanTier() is enterprise for every schedule. --}}
                        @if (config('app.hosted'))
                        <th scope="col" data-col="plan">@lang('messages.plan')</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach ($topNewsletterData as $nlData)
                    <tr>
                        <td class="c-main c-strong">
                            <a href="{{ route('role.view_guest', ['subdomain' => $nlData['subdomain']]) }}" class="event-link" target="_blank" rel="noopener" dir="ltr">{{ $nlData['subdomain'] }}</a>
                        </td>
                        <td class="c-num c-strong" data-label="{{ __('messages.emails_sent') }}">{{ number_format($nlData['total']) }}</td>
                        <td>
                            @if ($nlData['has_smtp'])
                            <span class="event-status is-on">@lang('messages.custom')</span>
                            @else
                            <span class="event-status {{ $nlData['total'] > 50 ? 'is-bad' : '' }}">@lang('messages.platform')</span>
                            @endif
                        </td>
                        @if (config('app.hosted'))
                        <td><span class="event-chip">{{ __('messages.'.(in_array($nlData['plan_tier'], ['pro', 'enterprise'], true) ? $nlData['plan_tier'] : 'free')) }}</span></td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <x-page-empty compact :title="__('messages.no_newsletter_usage_data')" />
            @endif
        </x-page-card>

        <x-page-card flush :title="__('messages.translation_backlog')" :lead="__('messages.translation_backlog_description')">
            <table class="page-table">
                <thead>
                    <tr>
                        <th scope="col">@lang('messages.content')</th>
                        <th scope="col" class="c-num">@lang('messages.pending')</th>
                        <th scope="col" class="c-num">@lang('messages.translation_never_attempted')</th>
                        <th scope="col" class="c-num">@lang('messages.translation_longest_waiting')</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($translationBacklog as $pass)
                    <tr>
                        <td class="c-main c-strong">{{ $pass['label'] }}</td>
                        <td class="c-num {{ $pass['pending'] > 0 ? 'c-strong' : 'c-quiet' }}" data-label="{{ __('messages.pending') }}">{{ number_format($pass['pending']) }}</td>
                        {{-- Amber: rows the run has never reached, which is the sign of a cron
                             that is not keeping up. --}}
                        <td class="c-num {{ $pass['never_attempted'] > 0 ? 'usage-waiting' : 'c-quiet' }}" data-label="{{ __('messages.translation_never_attempted') }}">{{ number_format($pass['never_attempted']) }}</td>
                        <td class="c-num c-quiet" data-label="{{ __('messages.translation_longest_waiting') }}">{{ $pass['oldest'] ? \Carbon\Carbon::parse($pass['oldest'])->diffForHumans() : __('messages.never') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>

            {{-- Kept out of the Pending column on purpose. These are schedules the roles pass has
                 to open to be sure, because their translations live under an `_en` sub-key inside
                 a JSON column and SQL cannot see whether it is filled. Counted as pending they
                 held the figure permanently above zero on any install with custom fields, labels,
                 categories or sponsor logos, which reads as a stuck cron rather than as a caveat.
                 The run parks them with no AI call and no pause. --}}
            @php $usageRecheck = \App\Services\WorkBacklog::translationRecheck($translationBacklog); @endphp
            @if ($usageRecheck > 0)
            <x-slot name="foot">
                {{ trans_choice('messages.translation_recheck_note', $usageRecheck, ['count' => number_format($usageRecheck)]) }}
            </x-slot>
            @endif
        </x-page-card>

        {{-- Records the translation run keeps failing on. The four kinds were four copies of one
             row; they are gathered into one list here and drawn once. --}}
        @php
            $never = __('messages.never');
            $stuckRows = [];

            foreach ($stuckRoles as $record) {
                $missing = [];
                if (!empty($record->name) && is_null($record->name_en)) $missing[] = 'name_en';
                if (!empty($record->description) && is_null($record->description_en)) $missing[] = 'description_en';
                if (!empty($record->address1) && is_null($record->address1_en)) $missing[] = 'address1_en';
                if (!empty($record->city) && is_null($record->city_en)) $missing[] = 'city_en';
                if (!empty($record->state) && is_null($record->state_en)) $missing[] = 'state_en';
                if (!empty($record->request_terms) && is_null($record->request_terms_en)) $missing[] = 'request_terms_en';
                $stuckRows[] = [
                    'kind' => __('messages.schedule'), 'type' => 'role', 'record' => $record,
                    'name' => $record->name ?: $record->subdomain, 'subdomain' => $record->subdomain, 'note' => '#'.$record->id,
                    'language' => $record->language_code, 'missing' => $missing,
                    'preview' => $record->name ?: $record->description,
                ];
            }
            foreach ($stuckEvents as $record) {
                $missing = [];
                if (!empty($record->name) && is_null($record->name_en)) $missing[] = 'name_en';
                if (!empty($record->description) && is_null($record->description_en)) $missing[] = 'description_en';
                $stuckRows[] = [
                    'kind' => __('messages.event'), 'type' => 'event', 'record' => $record,
                    'name' => \Illuminate\Support\Str::limit($record->name, 40), 'subdomain' => null, 'note' => '#'.$record->id,
                    'language' => $record->venue?->language_code, 'missing' => $missing,
                    'preview' => $record->name ?: $record->description,
                ];
            }
            foreach ($stuckEventParts as $record) {
                $missing = [];
                if (!empty($record->name) && is_null($record->name_en)) $missing[] = 'name_en';
                if (!empty($record->description) && is_null($record->description_en)) $missing[] = 'description_en';
                $stuckRows[] = [
                    'kind' => __('messages.agenda'), 'type' => 'event_part', 'record' => $record,
                    'name' => \Illuminate\Support\Str::limit($record->name, 40), 'subdomain' => null,
                    'note' => '#'.$record->id.' ('.mb_strtolower(__('messages.event')).' #'.$record->event_id.')',
                    'language' => $record->event?->venue?->language_code, 'missing' => $missing,
                    'preview' => $record->name ?: $record->description,
                ];
            }
            foreach ($stuckEventRoles as $record) {
                $missing = [];
                if ($record->event && !empty($record->event->name) && is_null($record->name_translated)) $missing[] = 'name_translated';
                if ($record->event && !empty($record->event->description) && is_null($record->description_translated)) $missing[] = 'description_translated';
                $stuckRows[] = [
                    'kind' => __('messages.curator').' / '.__('messages.event'), 'type' => 'event_role', 'record' => $record,
                    'name' => $record->event?->name ? \Illuminate\Support\Str::limit($record->event->name, 25) : __('messages.event').' #'.$record->event_id,
                    'subdomain' => $record->role?->subdomain,
                    'note' => ($record->role?->subdomain ? '' : '@ #'.$record->role_id.' ').'#'.$record->id,
                    'language' => $record->role?->language_code, 'missing' => $missing,
                    'preview' => $record->event?->name,
                ];
            }
        @endphp
        <x-page-card flush :title="__('messages.stuck_translation_records')" :lead="__('messages.stuck_translation_description', ['threshold' => $stuckThreshold])">
            @if (count($stuckRows) > 0)
            <div class="page-scroll">
                <table class="page-table is-wide">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.name')</th>
                            <th scope="col">@lang('messages.type')</th>
                            <th scope="col">@lang('messages.language')</th>
                            <th scope="col">@lang('messages.missing_fields')</th>
                            <th scope="col" class="c-num">@lang('messages.attempts')</th>
                            <th scope="col">@lang('messages.last_attempt')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($stuckRows as $row)
                        <tr>
                            <td class="c-main" title="{{ $row['preview'] }}">
                                <span class="c-strong"><bdi>{{ $row['name'] }}</bdi></span>
                                @if ($row['subdomain'])
                                <a href="{{ route('role.view_guest', ['subdomain' => $row['subdomain']]) }}" class="event-link" target="_blank" rel="noopener" dir="ltr">{{ '@'.$row['subdomain'] }}</a>
                                @endif
                                <span class="c-quiet" dir="ltr">{{ $row['note'] }}</span>
                            </td>
                            <td class="c-quiet">{{ $row['kind'] }}</td>
                            <td><span class="event-chip" dir="ltr">{{ strtoupper($row['language'] ?? 'N/A') }}</span></td>
                            <td class="c-mono c-quiet c-wrap" dir="ltr">{{ implode(', ', $row['missing']) }}</td>
                            {{-- Red once it has failed twice as often as it takes to be listed. --}}
                            <td class="c-num {{ $row['record']->translation_attempts >= $stuckThreshold * 2 ? 'usage-over' : 'usage-waiting' }}" data-label="{{ __('messages.attempts') }}">{{ $row['record']->translation_attempts }}</td>
                            <td class="c-date">{{ $row['record']->last_translated_at ? $row['record']->last_translated_at->diffForHumans() : $never }}</td>
                            <td class="c-actions">
                                <button type="button" class="js-retry-translation event-link" data-type="{{ $row['type'] }}" data-id="{{ $row['record']->id }}">@lang('messages.retry')</button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <x-page-empty compact :title="__('messages.no_stuck_translations')" />
            @endif
        </x-page-card>
    </div>

    <x-slot name="head">
        @include('admin.partials._insight-styles')
        <style {!! nonce_attr() !!}>
            /* The two colours this page uses, each for one thing: over a limit (or failing again
               and again), and still waiting. */
            .usage-over,
            .page-table .usage-over {
              font-weight: 600;
              color: #b91c1c;
            }
            .dark .usage-over,
            .dark .page-table .usage-over {
              color: #f87171;
            }
            .page-table .usage-waiting {
              font-weight: 600;
              color: #b45309;
            }
            .dark .page-table .usage-waiting {
              color: #fbbf24;
            }
        </style>
    </x-slot>

    <script {!! nonce_attr() !!}>
        document.addEventListener('click', function(e) {
            var button = e.target.closest('.js-retry-translation');
            if (!button) return;
            retryTranslation(button.getAttribute('data-type'), parseInt(button.getAttribute('data-id')), button);
        });

        function retryTranslation(type, id, button) {
            const originalText = button.textContent;
            // What the button says when the retry did not go through, then what it said before.
            const failed = function() {
                button.textContent = @json(__('messages.error'));
                button.classList.add('is-danger');
                setTimeout(() => {
                    button.textContent = originalText;
                    button.classList.remove('is-danger');
                    button.disabled = false;
                }, 2000);
            };
            button.textContent = '...';
            button.disabled = true;

            fetch('{{ route("admin.translation.retry") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    // Accept, not just Content-Type: Content-Type describes the REQUEST
                    // body and has no bearing on expectsJson(), so without this a lapsed
                    // admin re-auth window 302s to HTML and the button reports a
                    // meaningless error instead of a password prompt.
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ type: type, id: id })
            })
            .then(response => {
                if (!response.ok) throw new Error('Request failed');
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    button.textContent = @json(__("messages.done"));
                    // The row goes after a moment, so the word can be read first.
                    setTimeout(() => {
                        button.closest('tr').remove();
                    }, 1000);
                } else {
                    failed();
                }
            })
            .catch(error => {
                console.error('Error:', error);
                failed();
            });
        }
    </script>

</x-app-admin-layout>
