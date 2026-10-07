<x-app-admin-layout>
    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* On a phone the action leads the row and the details take a line of their own: the
               table used to run off the edge with the action cut at "auth.log". */
            @media (max-width: 639.98px) {
              .page-table .c-lead {
                order: -1;
              }
              .page-table .c-line {
                flex: 1 1 100%;
              }
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'audit-log'])

    {{-- Who did what on this installation, newest first. The figures are one strip, as on the
         Queue and Logs pages beside it; the filters are one row; the list is the kit's, a stack
         of rows on a phone. --}}
    <div class="page-head">
        <p class="page-lead">{{ __('messages.admin_audit_log_lead') }}</p>
    </div>

    <div class="page-shell page-stack">
        <div class="ap-card rounded-xl page-stats is-auto">
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($totalEntries) }}</div>
                <div class="page-stat-label">{{ __('messages.total_entries') }}</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($entriesToday) }}</div>
                <div class="page-stat-label">{{ __('messages.entries_today') }}</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value {{ $failedAuthToday > 0 ? 'is-bad' : '' }}">{{ number_format($failedAuthToday) }}</div>
                <div class="page-stat-label">{{ __('messages.failed_auth_today') }}</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($uniqueIpsToday) }}</div>
                <div class="page-stat-label">{{ __('messages.unique_ips_today') }}</div>
            </div>
        </div>

        @php
            $filtered = request()->filled('category') || request()->filled('from') || request()->filled('to') || request()->filled('search') || request()->filled('user_id');
            $fieldClass = 'rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-gray-300 shadow-sm text-sm';
        @endphp
        <form method="GET" action="{{ route('admin.audit_log') }}" class="page-filters">
            <label class="page-filter">
                <span>{{ __('messages.category') }}</span>
                <select name="category" class="{{ $fieldClass }}">
                    <option value="">{{ __('messages.all') }}</option>
                    @foreach ($categories as $cat)
                        @php
                            // The categories that have a word in the reader's language use it;
                            // the rest are the log's own names for a kind of entry.
                            $catLabel = match($cat) {
                                'api' => 'API',
                                'google_calendar' => 'Google Calendar',
                                'admin' => __('messages.admin'),
                                'boost' => __('messages.boost'),
                                'event' => __('messages.event'),
                                'sale' => __('messages.sales'),
                                'schedule' => __('messages.schedule'),
                                'subscription' => __('messages.subscription'),
                                default => ucfirst(str_replace('_', ' ', $cat)),
                            };
                        @endphp
                        <option value="{{ $cat }}" {{ request('category') === $cat ? 'selected' : '' }}>{{ $catLabel }}</option>
                    @endforeach
                </select>
            </label>
            <div class="page-filter-pair">
                <label class="page-filter">
                    <span>{{ __('messages.from') }}</span>
                    <input type="text" name="from" value="{{ request('from') }}" class="datepicker-filter w-36 {{ $fieldClass }}" placeholder="{{ __('messages.from') }}" autocomplete="off">
                </label>
                <label class="page-filter">
                    <span>{{ __('messages.to') }}</span>
                    <input type="text" name="to" value="{{ request('to') }}" class="datepicker-filter w-36 {{ $fieldClass }}" placeholder="{{ __('messages.to') }}" autocomplete="off">
                </label>
            </div>
            <label class="page-filter is-grow">
                <span>{{ __('messages.search') }}</span>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="{{ __('messages.search_audit_log') }}" class="w-full {{ $fieldClass }}">
            </label>
            <div class="is-end">
                @if ($filtered)
                <x-secondary-link :href="route('admin.audit_log')">{{ __('messages.clear') }}</x-secondary-link>
                @endif
                <x-brand-button type="submit">{{ __('messages.filter') }}</x-brand-button>
            </div>
        </form>

        @if ($logs->isEmpty())
        <div class="ap-card rounded-xl">
            <x-page-empty :title="__('messages.no_audit_log_entries')"
                icon="M9 12h3.75M9 15h3.75M9 18h3.75m3 .75H18a2.25 2.25 0 002.25-2.25V6.108c0-1.135-.845-2.098-1.976-2.192a48.424 48.424 0 00-1.123-.08m-5.801 0c-.065.21-.1.433-.1.664 0 .414.336.75.75.75h4.5a.75.75 0 00.75-.75 2.25 2.25 0 00-.1-.664m-5.8 0A2.251 2.251 0 0113.5 2.25H15c1.012 0 1.867.668 2.15 1.586m-5.8 0c-.376.023-.75.05-1.124.08C9.095 4.01 8.25 4.973 8.25 6.108V8.25m0 0H4.875c-.621 0-1.125.504-1.125 1.125v11.25c0 .621.504 1.125 1.125 1.125h9.75c.621 0 1.125-.504 1.125-1.125V9.375c0-.621-.504-1.125-1.125-1.125H8.25zM6.75 12h.008v.008H6.75V12zm0 3h.008v.008H6.75V15zm0 3h.008v.008H6.75V18z">
                @if ($filtered)
                <x-secondary-link :href="route('admin.audit_log')">{{ __('messages.clear') }}</x-secondary-link>
                @endif
            </x-page-empty>
        </div>
        @else
        <div class="ap-card rounded-xl overflow-hidden">
            <table class="page-table">
                <thead>
                    <tr>
                        <x-page-sort column="created_at" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.time') }}</x-page-sort>
                        <x-page-sort column="user_id" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.user') }}</x-page-sort>
                        <x-page-sort column="action" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.action') }}</x-page-sort>
                        <x-page-sort column="ip_address" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.ip_address') }}</x-page-sort>
                        <x-page-sort column="metadata" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.details') }}</x-page-sort>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($logs as $log)
                    @php
                        // A dot and the action's own name: what an operator searches the log for.
                        // The pill used to be coloured by CATEGORY (auth blue, api orange, sale
                        // yellow...), so a failed sign-in wore the same blue as a good one. Now
                        // only what went wrong is coloured.
                        $actionTone = preg_match('/fail|denied|blocked|locked|invalid/', $log->action) ? 'is-bad' : '';
                    @endphp
                    <tr>
                        <td class="c-date">{{ $log->created_at->format('M j, Y H:i:s') }}</td>
                        <td class="c-strong whitespace-nowrap">
                            @if ($log->user)
                                <bdi>{{ $log->user->name }}</bdi>
                            @else
                                <span class="c-quiet">-</span>
                            @endif
                        </td>
                        <td class="c-main c-lead c-mono"><span class="event-status {{ $actionTone }}"><bdi dir="ltr">{{ $log->action }}</bdi></span></td>
                        <td class="c-quiet c-mono"><bdi dir="ltr">{{ $log->ip_address }}</bdi></td>
                        <td class="c-quiet c-wrap c-line">@if ($log->metadata)<bdi>{{ Str::limit($log->metadata, 160) }}</bdi>@elseif ($log->model_type){{ class_basename($log->model_type) }} #{{ $log->model_id }}@endif</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($logs->hasPages())
        <div class="page-pager">{{ $logs->links() }}</div>
        @endif
        @endif
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function() {
            var fpLocale = window.flatpickrLocales ? window.flatpickrLocales[window.appLocale] : null;
            var localeConfig = fpLocale ? { locale: fpLocale } : {};
            document.querySelectorAll('.datepicker-filter').forEach(function(el) {
                flatpickr(el, Object.assign({
                    allowInput: true,
                    enableTime: false,
                    altInput: true,
                    altFormat: "M j, Y",
                    dateFormat: "Y-m-d",
                }, localeConfig));
            });
        });
        document.addEventListener('click', function(e) {
            var header = e.target.closest('[data-sort]');
            if (header) {
                var url = new URL(window.location.href);
                var currentSort = url.searchParams.get('sort_by') || 'created_at';
                var currentDir = url.searchParams.get('sort_dir') || 'desc';
                var sortBy = header.getAttribute('data-sort');
                url.searchParams.set('sort_by', sortBy);
                url.searchParams.set('sort_dir', currentSort === sortBy && currentDir === 'asc' ? 'desc' : 'asc');
                // A new order starts at its first page: page 3 of the old order is no place in the new one.
                url.searchParams.delete('page');
                window.location.href = url.toString();
            }
        });
    </script>

</x-app-admin-layout>
