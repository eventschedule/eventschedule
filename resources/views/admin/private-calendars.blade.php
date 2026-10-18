<x-app-admin-layout>
    @include('admin.partials._navigation', ['active' => 'private-calendars'])

    {{-- Schedules a calendar import made into somebody's private calendar (CalendarExposureWatch):
         meeting passcodes, dial-in codes, links carrying a meeting's password, an email thread
         made into an event, on a public page. One row per schedule, the most still public first.
         Nothing is done from here but Mark reviewed: the steps are on the schedule's own page,
         after the evidence. What is shown is read again from the events as they are now. --}}
    @php
        $when = fn ($moment) => $moment ? \Illuminate\Support\Carbon::parse($moment)->setTimezone(auth()->user()->timezone ?: config('app.timezone'))->translatedFormat('M j, Y') : null;
    @endphp

    <div class="page-head">
        <p class="page-lead">@lang('messages.private_calendars_lead')</p>
    </div>

    <div class="page-shell page-stack">
        <div class="page-subhead">
            <h2>@lang('messages.private_calendars_title')</h2>
            <p>@lang('messages.private_calendars_list_lead')</p>
        </div>

        <div>
            @if ($rows->isNotEmpty())
            <div class="ap-card rounded-xl overflow-hidden">
                <div class="page-scroll">
                    <table class="page-table">
                        <thead>
                            <tr>
                                <th scope="col">@lang('messages.schedule')</th>
                                <th scope="col">@lang('messages.private_calendars_col_found')</th>
                                <th scope="col" class="c-num">@lang('messages.private_calendars_col_public')</th>
                                <th scope="col">@lang('messages.calendar_sync')</th>
                                <th scope="col">@lang('messages.private_calendars_col_last')</th>
                                <th scope="col"><span class="sr-only">@lang('messages.actions')</span></th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($rows as $row)
                            @php($rowHash = \App\Utils\UrlUtils::encodeId($row['role']->id))
                            <tr>
                                <td class="c-main c-wrap">
                                    <a href="{{ route('admin.private_calendars.show', ['hash' => $rowHash]) }}" class="c-strong"><bdi>{{ $row['role']->name }}</bdi></a>
                                    <span class="c-sub" dir="ltr">{{ $row['role']->subdomain }}</span>
                                </td>
                                <td class="c-wrap" data-label="{{ __('messages.private_calendars_col_found') }}">
                                    <span class="privacy-kinds">
                                        @foreach ($row['kinds'] as $kind)
                                        <span class="event-chip">{{ \App\Services\CalendarExposureWatch::kindName($kind) }}</span>
                                        @endforeach
                                    </span>
                                    @foreach ($row['snippets'] as $snippet)
                                    <span class="privacy-match"><bdi>{{ $snippet['html'] }}</bdi></span>
                                    @endforeach
                                </td>
                                <td class="c-num" data-label="{{ __('messages.private_calendars_col_public') }}">{{ number_format($row['public_now']) }}</td>
                                <td class="c-wrap" data-label="{{ __('messages.calendar_sync') }}">
                                    @forelse ($row['syncs'] as $sync)
                                    <span class="privacy-sync">
                                        {{ $sync['label'] }}
                                        <span class="c-sub">{{ $sync['paused'] ? __('messages.private_calendars_paused') : $sync['direction'] }}</span>
                                        @if ($sync['main'])
                                        <span class="event-chip">@lang('messages.private_calendars_main_calendar')</span>
                                        @endif
                                    </span>
                                    @empty
                                    <span class="c-sub">@lang('messages.private_calendars_no_sync')</span>
                                    @endforelse
                                </td>
                                <td class="c-date" data-label="{{ __('messages.private_calendars_col_last') }}">
                                    @if ($row['last'])
                                    {{ $row['last']['label'] }}
                                    <span class="c-sub">{{ $when($row['last']['at']) }}</span>
                                    @else
                                    <span class="c-sub">@lang('messages.private_calendars_none_yet')</span>
                                    @endif
                                </td>
                                <td class="c-actions">
                                    <a href="{{ route('admin.private_calendars.show', ['hash' => $rowHash]) }}" class="event-link">@lang('messages.review')</a>
                                    <form method="POST" action="{{ route('admin.private_calendars.reviewed', ['hash' => $rowHash]) }}">
                                        @csrf
                                        <input type="hidden" name="from" value="list">
                                        <button type="submit" class="event-link">@lang('messages.private_calendars_mark_reviewed')</button>
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
                <x-page-empty compact :title="__('messages.private_calendars_none_title')" :text="__('messages.private_calendars_none_text')" />
            </div>
            @endif

            @if ($page->hasPages())
            <div class="page-pager">{{ $page->links() }}</div>
            @endif
        </div>
    </div>

    <x-slot name="head">
        <style {!! nonce_attr() !!}>
            /* What was found: the kinds as chips on one line, then each snippet on a line of its
               own, the match marked by the kit (.spam-word). */
            .privacy-kinds {
              display: flex;
              flex-wrap: wrap;
              gap: 0.25rem;
              margin-bottom: 0.25rem;
            }
            .privacy-match,
            .privacy-sync {
              display: block;
            }
            .privacy-match {
              font-size: 0.8125rem;
              color: rgb(var(--ap-ink-2));
            }
            .privacy-match + .privacy-match,
            .privacy-sync + .privacy-sync {
              margin-top: 0.25rem;
            }
        </style>
    </x-slot>
</x-app-admin-layout>
