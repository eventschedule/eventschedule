<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'copies'])

    {{-- Sites that may be serving a copy of the marketing pages (App\Services\MarketingCopyWatch).
         Two lists: the likely copies, which the admin alert points at, and every site being
         watched. Nothing is done to a site here but recording what the operator decided. --}}
    @php
        $day = fn ($moment) => $moment ? $moment->copy()->setTimezone(auth()->user()->timezone ?: config('app.timezone'))->translatedFormat('M j, Y') : null;
        $tones = ['copy' => 'is-bad', 'contacted' => 'is-warn', 'clear' => 'is-on', 'unreachable' => 'is-info', 'unchecked' => '', 'dismissed' => ''];
        $status = fn ($row) => '<span class="event-status '.($tones[$row->status] ?? '').'">'.e(__('messages.copies_status_'.$row->status)).'</span>';
        $markerNames = fn ($row) => collect($row->markers ?? [])->map(fn ($kind) => __('messages.copies_marker_'.$kind))->implode(', ');
    @endphp

    <div class="page-head">
        <p class="page-lead">@lang('messages.copies_lead')</p>
    </div>

    <div class="page-shell page-stack">
        <div class="ap-card rounded-xl page-stats is-auto">
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($counts['copies']) }}</div>
                <div class="page-stat-label">@lang('messages.copies_likely')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($counts['contacted']) }}</div>
                <div class="page-stat-label">@lang('messages.copies_status_contacted')</div>
            </div>
            <div class="page-stat">
                <div class="page-stat-value">{{ number_format($counts['watched']) }}</div>
                <div class="page-stat-label">@lang('messages.copies_watched')</div>
            </div>
        </div>

        {{-- ===================== Likely copies ===================== --}}
        <div class="page-subhead" id="copies">
            <h2>@lang('messages.copies_likely')</h2>
            <p>@lang('messages.copies_likely_lead')</p>
        </div>

        @if ($likely->isNotEmpty())
        <div class="ap-card rounded-xl overflow-hidden">
            <div class="page-scroll">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.copies_col_site')</th>
                            <th scope="col">@lang('messages.copies_col_found')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col">@lang('messages.copies_col_found_on')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($likely as $row)
                        @php $hash = \App\Utils\UrlUtils::encodeId($row->id); @endphp
                        <tr>
                            <td class="c-main c-wrap">
                                <a href="{{ $row->checked_url ?: 'https://'.$row->host.'/' }}" target="_blank" rel="noopener noreferrer nofollow" class="c-strong" dir="ltr">{{ $row->host }}</a>
                                @if ($row->page_title)
                                <span class="c-sub"><bdi>{{ \Illuminate\Support\Str::limit($row->page_title, 90) }}</bdi></span>
                                @endif
                            </td>
                            <td class="c-wrap" data-label="{{ __('messages.copies_col_found') }}">{{ $markerNames($row) }}</td>
                            <td data-label="{{ __('messages.status') }}">{!! $status($row) !!}</td>
                            <td class="c-date" data-label="{{ __('messages.copies_col_found_on') }}">{{ $day($row->copy_found_at) }}</td>
                            <td class="c-actions">
                                <form method="POST" action="{{ route('admin.copies.check', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="event-link">@lang('messages.copies_check_now')</button>
                                </form>
                                @if ($row->status === 'copy')
                                <form method="POST" action="{{ route('admin.copies.decide', ['hash' => $hash]) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="contacted">
                                    <button type="submit" class="event-link">@lang('messages.copies_mark_contacted')</button>
                                </form>
                                @endif
                                <form method="POST" action="{{ route('admin.copies.decide', ['hash' => $hash]) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="dismissed">
                                    <button type="submit" class="event-link">@lang('messages.copies_not_a_copy')</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @else
        <div class="ap-card rounded-xl">
            <x-page-empty compact :title="__('messages.copies_none_title')" :text="__('messages.copies_none_text')" />
        </div>
        @endif

        {{-- ===================== Watched sites ===================== --}}
        <div class="page-subhead" id="watched">
            <h2>@lang('messages.copies_watched')</h2>
            <p>@lang('messages.copies_watched_lead')</p>
        </div>

        @if ($watched->total() > 0)
        <div class="ap-card rounded-xl overflow-hidden">
            <div class="page-scroll">
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">@lang('messages.copies_col_site')</th>
                            <th scope="col">@lang('messages.copies_col_source')</th>
                            <th scope="col" class="c-num">@lang('messages.copies_col_visits')</th>
                            <th scope="col">@lang('messages.copies_col_checked')</th>
                            <th scope="col">@lang('messages.status')</th>
                            <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($watched as $row)
                        @php $hash = \App\Utils\UrlUtils::encodeId($row->id); @endphp
                        <tr>
                            <td class="c-main c-wrap">
                                <a href="https://{{ $row->host }}/" target="_blank" rel="noopener noreferrer nofollow" class="c-strong" dir="ltr">{{ $row->host }}</a>
                                @if ($row->page_title)
                                <span class="c-sub"><bdi>{{ \Illuminate\Support\Str::limit($row->page_title, 90) }}</bdi></span>
                                @endif
                            </td>
                            <td data-label="{{ __('messages.copies_col_source') }}">@lang('messages.copies_source_'.$row->source)</td>
                            <td class="c-num" data-label="{{ __('messages.copies_col_visits') }}">{{ number_format($row->visits) }}</td>
                            <td class="c-date" data-label="{{ __('messages.copies_col_checked') }}">{{ $day($row->checked_at) ?? __('messages.copies_not_yet') }}</td>
                            <td data-label="{{ __('messages.status') }}">{!! $status($row) !!}</td>
                            <td class="c-actions">
                                @if ($row->status === 'dismissed')
                                <form method="POST" action="{{ route('admin.copies.decide', ['hash' => $hash]) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="watch">
                                    <button type="submit" class="event-link">@lang('messages.copies_watch_again')</button>
                                </form>
                                @else
                                <form method="POST" action="{{ route('admin.copies.check', ['hash' => $hash]) }}">
                                    @csrf
                                    <button type="submit" class="event-link">@lang('messages.copies_check_now')</button>
                                </form>
                                <form method="POST" action="{{ route('admin.copies.decide', ['hash' => $hash]) }}">
                                    @csrf
                                    <input type="hidden" name="action" value="dismissed">
                                    <button type="submit" class="event-link">@lang('messages.copies_not_a_copy')</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        @if ($watched->hasPages())
        <div class="page-pager">{{ $watched->links() }}</div>
        @endif
        @else
        <div class="ap-card rounded-xl">
            <x-page-empty compact :title="__('messages.copies_watched_none_title')" :text="__('messages.copies_watched_none_text')" />
        </div>
        @endif
    </div>
</x-app-admin-layout>
