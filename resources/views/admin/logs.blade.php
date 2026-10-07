<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* A small button that destroys something: the weight of .page-tool, in red. */
            .page-tool.is-danger {
              border-color: rgba(220, 38, 38, 0.4);
              color: #b91c1c;
            }
            .page-tool.is-danger:hover {
              border-color: #dc2626;
              background: rgba(220, 38, 38, 0.08);
              color: #b91c1c;
            }
            .dark .page-tool.is-danger,
            .dark .page-tool.is-danger:hover {
              border-color: rgba(248, 113, 113, 0.5);
              color: #f87171;
            }
            /* A log line: the message in the list, what came with it underneath when opened. */
            .sys-log {
              display: flex;
              align-items: flex-start;
              gap: 0.5rem;
              min-width: 0;
            }
            .sys-log > details,
            .sys-log > span {
              flex: 1;
              min-width: 0;
              overflow-wrap: anywhere;
            }
            .sys-log summary {
              cursor: pointer;
            }
            .sys-log summary:hover {
              color: var(--brand-blue);
            }
            .sys-pre {
              max-height: 16rem;
              margin: 0.5rem 0 0;
              overflow-y: auto;
              border-radius: 0.5rem;
              padding: 0.75rem;
              background: var(--ap-tint-1);
              font-family: ui-monospace, SFMono-Regular, Menlo, Consolas, monospace;
              font-size: 0.75rem;
              line-height: 1.5;
              white-space: pre-wrap;
              overflow-wrap: anywhere;
              color: rgb(var(--ap-ink-2));
            }
            .sys-copy {
              flex: none;
              border: 0;
              border-radius: 0.375rem;
              padding: 0.25rem;
              background: none;
              color: rgb(var(--ap-ink-4));
              cursor: pointer;
              transition: color 0.2s, background-color 0.2s;
            }
            .sys-copy:hover {
              background: var(--ap-tint-2);
              color: var(--brand-blue);
            }
            .sys-copy:focus-visible {
              outline: 2px solid var(--brand-blue);
              outline-offset: 1px;
            }
            .sys-copy svg {
              width: 1rem;
              height: 1rem;
            }
            /* The line under the list: what the file is, and the one thing that empties it. */
            .sys-foot-row {
              display: flex;
              flex-wrap: wrap;
              align-items: center;
              justify-content: space-between;
              gap: 0.75rem 1.5rem;
            }
            .sys-foot-row p {
              margin: 0;
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-3));
            }
            .page-table td {
              vertical-align: top;
            }
            /* The kit's subhead leaves room above itself for a page it stands alone on; in a
               stack the gap is already there. */
            .page-stack .page-subhead {
              margin-top: 0.5rem;
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'logs'])

    {{-- The application log (storage/logs/laravel.log): what is wrong with it first, the figures
         as one strip, the errors that keep coming back, then the newest lines. Download is the
         page's tool; Clear log, which cannot be undone, stands apart from it at the foot. --}}
    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_logs_lead') }}</p>
        @if ($fileExists && $fileSize > 0)
        <div class="page-actions">
            <a href="{{ route('admin.logs.download') }}" class="page-tool">
                <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                </svg>
                {{ __('messages.download_log') }}
            </a>
        </div>
        @endif
    </div>

    <div class="page-shell page-stack">
        <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

        @if (!$fileExists)
            <div class="ap-card rounded-xl">
                <x-page-empty :title="__('messages.no_log_file_found')"
                    :text="__('messages.no_log_file_found_at').' storage/logs/laravel.log'"
                    icon="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </div>
        @else
            @php
                $errorCount = ($levelCounts['ERROR'] ?? 0) + ($levelCounts['CRITICAL'] ?? 0) + ($levelCounts['EMERGENCY'] ?? 0) + ($levelCounts['ALERT'] ?? 0);
                $warningCount = $levelCounts['WARNING'] ?? 0;
                $filtered = request('level') || request('search');
                // The kit's status mark for a level: red for what failed, amber for a warning,
                // blue for what is only worth noting, quiet for the rest.
                $levelTone = fn ($level) => match ($level) {
                    'EMERGENCY', 'ALERT', 'CRITICAL', 'ERROR' => 'is-bad',
                    'WARNING' => 'is-warn',
                    'NOTICE', 'INFO' => 'is-info',
                    default => '',
                };
                $fieldClass = 'rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm';
            @endphp

            @if ($errorCount > 0 || $fileSize > 100 * 1024 * 1024)
            <x-page-notice tone="error" :title="__('messages.log_health_issues')">
                <ul class="mt-1 list-disc ps-5 space-y-1">
                    @if ($errorCount > 0)
                    <li>{{ __('messages.error_level_entries', ['count' => number_format($errorCount)]) }}</li>
                    @endif
                    @if ($fileSize > 100 * 1024 * 1024)
                    <li>{{ __('messages.log_file_over_size', ['size' => number_format($fileSize / 1024 / 1024, 0)]) }}</li>
                    @endif
                </ul>
            </x-page-notice>
            @endif

            <div class="ap-card rounded-xl page-stats is-auto">
                <div class="page-stat">
                    <div class="page-stat-value"><bdi dir="ltr">{{ number_format($fileSize / 1024 / 1024, 2) }} MB</bdi></div>
                    <div class="page-stat-label">{{ __('messages.file_size') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value">{{ number_format($totalCount) }}</div>
                    <div class="page-stat-label">{{ __('messages.total_entries') }}</div>
                    @if ($fileSize > 5 * 1024 * 1024)
                    <div class="page-stat-sub">{{ __('messages.from_last_5_mb') }}</div>
                    @endif
                </div>
                <div class="page-stat">
                    <div class="page-stat-value {{ $errorCount > 0 ? 'is-bad' : '' }}">{{ number_format($errorCount) }}</div>
                    <div class="page-stat-label">{{ __('messages.errors') }}</div>
                </div>
                <div class="page-stat">
                    <div class="page-stat-value {{ $warningCount > 0 ? 'is-warn' : '' }}">{{ number_format($warningCount) }}</div>
                    <div class="page-stat-label">{{ __('messages.warnings') }}</div>
                </div>
            </div>

            @if ($repeatedErrors->count() > 0)
            <x-page-card :title="__('messages.repeated_errors')" flush>
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col" class="c-num">{{ __('messages.log_col_count') }}</th>
                            <th scope="col">{{ __('messages.log_col_level') }}</th>
                            <th scope="col">{{ __('messages.error') }}</th>
                            <th scope="col">{{ __('messages.log_col_last_seen') }}</th>
                            <th scope="col">{{ __('messages.log_col_first_seen') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($repeatedErrors as $error)
                        @php
                            $copyText = '[' . $error['level'] . '] ' . $error['message'] . "\n\n" . 'Occurred ' . $error['count'] . ' times, last seen ' . $error['last_seen'] . ', first seen ' . $error['first_seen'];
                            if ($error['stack_trace']) {
                                $copyText .= "\n\nStack trace:\n" . $error['stack_trace'];
                            }
                            $long = mb_strlen($error['message']) > 150;
                        @endphp
                        <tr>
                            <td class="c-num c-strong" data-label="{{ __('messages.log_col_count') }}">{{ number_format($error['count']) }}</td>
                            <td><span class="event-status {{ $levelTone($error['level']) }}">{{ $error['level'] }}</span></td>
                            <td class="c-main c-mono">
                                <div class="sys-log">
                                    @if ($error['stack_trace'] || $long)
                                    <details>
                                        <summary><bdi dir="ltr">{{ Str::limit($error['message'], 150) }}</bdi></summary>
                                        {{-- A message cut at 150 characters could not be read in full anywhere. --}}
                                        @if ($long)
                                        <pre class="sys-pre" dir="ltr">{{ $error['message'] }}</pre>
                                        @endif
                                        @if ($error['stack_trace'])
                                        <pre class="sys-pre" dir="ltr">{{ $error['stack_trace'] }}</pre>
                                        @endif
                                    </details>
                                    @else
                                    <span><bdi dir="ltr">{{ $error['message'] }}</bdi></span>
                                    @endif
                                    <button type="button" class="js-copy-error sys-copy" title="{{ __('messages.log_copy_error') }}" aria-label="{{ __('messages.log_copy_error') }}" data-copy="{{ $copyText }}">
                                        <svg class="js-copy-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                        </svg>
                                        <svg class="js-check-icon hidden text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </button>
                                </div>
                            </td>
                            <td class="c-date" data-label="{{ __('messages.log_col_last_seen') }}"><bdi dir="ltr">{{ $error['last_seen'] }}</bdi></td>
                            <td class="c-date" data-label="{{ __('messages.log_col_first_seen') }}"><bdi dir="ltr">{{ $error['first_seen'] }}</bdi></td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-page-card>
            @endif

            <div>
                <div class="page-subhead">
                    <h2>{{ __('messages.recent_log_entries') }}</h2>
                    @if ($entries->count() > 0)
                    <p>
                        @if ($filtered)
                            {{ __('messages.showing_filtered_entries', ['shown' => number_format($entries->count()), 'total' => number_format($totalCount)]) }}
                        @elseif ($entries->count() < $totalCount)
                            {{ __('messages.showing_entries', ['shown' => number_format($entries->count()), 'total' => number_format($totalCount)]) }}
                        @else
                            {{ __('messages.n_entries', ['count' => number_format($totalCount)]) }}
                        @endif
                    </p>
                    @endif
                </div>

                <form method="GET" action="{{ route('admin.logs') }}" class="page-filters">
                    <label class="page-filter">
                        <span>{{ __('messages.log_col_level') }}</span>
                        <select name="level" class="{{ $fieldClass }}">
                            <option value="">{{ __('messages.all_levels') }}</option>
                            @foreach ($levels as $level)
                            <option value="{{ $level }}" {{ request('level') === $level ? 'selected' : '' }}>{{ $level }}</option>
                            @endforeach
                        </select>
                    </label>
                    <label class="page-filter is-grow">
                        <span>{{ __('messages.search') }}</span>
                        <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_messages') }}" class="w-full {{ $fieldClass }}">
                    </label>
                    <div class="is-end">
                        @if ($filtered)
                        <x-secondary-link :href="route('admin.logs')">{{ __('messages.clear') }}</x-secondary-link>
                        @endif
                        <x-brand-button type="submit">{{ __('messages.filter') }}</x-brand-button>
                    </div>
                </form>

                @if ($entries->count() > 0)
                <div class="ap-card rounded-xl overflow-hidden">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">{{ __('messages.time') }}</th>
                                <th scope="col">{{ __('messages.log_col_level') }}</th>
                                <th scope="col">{{ __('messages.message') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($entries as $entry)
                            @php
                                $isErrorLevel = in_array($entry['level'], ['ERROR', 'CRITICAL', 'EMERGENCY', 'ALERT']);
                                $prettyContext = $entry['context']
                                    ? (json_encode(json_decode($entry['context']), JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) ?: $entry['context'])
                                    : null;
                                // json_encode(null) is the string "null": a context that is not JSON
                                // printed that word instead of itself.
                                if ($prettyContext === 'null') {
                                    $prettyContext = $entry['context'];
                                }
                                if ($isErrorLevel) {
                                    $copyText = '[' . $entry['level'] . '] [' . $entry['timestamp'] . '] ' . $entry['message'];
                                    if ($prettyContext) {
                                        $copyText .= "\n\nContext:\n" . $prettyContext;
                                    }
                                    if ($entry['stack_trace']) {
                                        $copyText .= "\n\nStack trace:\n" . $entry['stack_trace'];
                                    }
                                }
                                $long = mb_strlen($entry['message']) > 150;
                            @endphp
                            <tr>
                                <td class="c-date"><bdi dir="ltr">{{ $entry['timestamp'] }}</bdi></td>
                                <td><span class="event-status {{ $levelTone($entry['level']) }}">{{ $entry['level'] }}</span></td>
                                <td class="c-main c-mono">
                                    <div class="sys-log">
                                        @if ($entry['stack_trace'] || $prettyContext || $long)
                                        <details>
                                            <summary><bdi dir="ltr">{{ Str::limit($entry['message'], 150) }}</bdi></summary>
                                            @if ($long)
                                            <pre class="sys-pre" dir="ltr">{{ $entry['message'] }}</pre>
                                            @endif
                                            @if ($prettyContext)
                                            <pre class="sys-pre" dir="ltr">{{ $prettyContext }}</pre>
                                            @endif
                                            @if ($entry['stack_trace'])
                                            <pre class="sys-pre" dir="ltr">{{ $entry['stack_trace'] }}</pre>
                                            @endif
                                        </details>
                                        @else
                                        <span><bdi dir="ltr">{{ $entry['message'] }}</bdi></span>
                                        @endif
                                        @if ($isErrorLevel)
                                        <button type="button" class="js-copy-error sys-copy" title="{{ __('messages.log_copy_error') }}" aria-label="{{ __('messages.log_copy_error') }}" data-copy="{{ $copyText }}">
                                            <svg class="js-copy-icon" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                            </svg>
                                            <svg class="js-check-icon hidden text-green-500" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @else
                <div class="ap-card rounded-xl">
                    <x-page-empty :title="__('messages.no_log_entries')" compact />
                </div>
                @endif
            </div>

            {{-- Clearing asks first (data-confirm on the form, which the layout's one handler
                 reads) and stands away from Download: the two used to sit side by side, the
                 destroying one in capitals. --}}
            @if ($fileSize > 0)
            <div class="ap-card rounded-xl page-card sys-foot-row">
                <p><bdi dir="ltr">storage/logs/laravel.log</bdi> &middot; <bdi dir="ltr">{{ number_format($fileSize / 1024 / 1024, 2) }} MB</bdi></p>
                <form method="POST" action="{{ route('admin.logs.clear') }}" data-confirm="{{ __('messages.confirm_clear_log') }}">
                    @csrf
                    <button type="submit" class="page-tool is-danger">
                        <svg fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M14.74 9l-.346 9m-4.788 0L9.26 9m9.968-3.21c.342.052.682.107 1.022.166m-1.022-.165L18.16 19.673a2.25 2.25 0 01-2.244 2.077H8.084a2.25 2.25 0 01-2.244-2.077L4.772 5.79m14.456 0a48.108 48.108 0 00-3.478-.397m-12 .562c.34-.059.68-.114 1.022-.165m0 0a48.11 48.11 0 013.478-.397m7.5 0v-.916c0-1.18-.91-2.164-2.09-2.201a51.964 51.964 0 00-3.32 0c-1.18.037-2.09 1.022-2.09 2.201v.916m7.5 0a48.667 48.667 0 00-7.5 0" />
                        </svg>
                        {{ __('messages.clear_log') }}
                    </button>
                </form>
            </div>
            @endif
        @endif
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('click', function(e) {
            var button = e.target.closest('.js-copy-error');
            if (!button) return;

            var text = button.getAttribute('data-copy');
            navigator.clipboard.writeText(text).then(function() {
                var copyIcon = button.querySelector('.js-copy-icon');
                var checkIcon = button.querySelector('.js-check-icon');
                copyIcon.classList.add('hidden');
                checkIcon.classList.remove('hidden');
                setTimeout(function() {
                    copyIcon.classList.remove('hidden');
                    checkIcon.classList.add('hidden');
                }, 1500);
            });
        });
    </script>

</x-app-admin-layout>
