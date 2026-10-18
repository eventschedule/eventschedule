<x-app-admin-layout>

    {{-- What a calendar sync is keeping off the schedule's public page (CalendarReviewController).
         Everything it shows of an event is the owner's calendar's own text: printed as text, with
         v-pre, names in <bdi>, and codes and links (a snippet) in an ltr <bdi> so they read in a
         right-to-left page. --}}
    @php
        $here = ['subdomain' => $role->subdomain];
        $zone = $role->captureTimezone();
        $clock = get_use_24_hour_time($role) ? 'H:i' : 'g:i A';
        $providerName = fn (?string $source) => \App\Services\CalendarArrival::providerName($source);
        $reasonLabel = fn (string $reason) => match ($reason) {
            \App\Utils\CalendarPrivacy::HOLD_MEETING_DETAILS => __('messages.calendar_hold_meeting_details'),
            \App\Utils\CalendarPrivacy::HOLD_EMAIL_THREAD => __('messages.calendar_hold_email_thread'),
            \App\Services\CalendarArrival::SOURCE_PRIVATE => __('messages.calendar_hold_private'),
            \App\Services\CalendarArrival::SOURCE_DECLINED => __('messages.calendar_hold_declined'),
            \App\Services\CalendarArrival::SOURCE_CANCELLED => __('messages.calendar_hold_cancelled'),
            \App\Services\CalendarArrival::SOURCE_NOT_AN_EVENT => __('messages.calendar_hold_not_an_event'),
            default => __('messages.calendar_hold_review'),
        };
        $content = [\App\Utils\CalendarPrivacy::HOLD_MEETING_DETAILS, \App\Utils\CalendarPrivacy::HOLD_EMAIL_THREAD];
        $tabs = collect(\App\Http\Controllers\CalendarReviewController::SHOWS)->map(fn ($s) => [
            'label' => __('messages.calendar_review_show_'.$s),
            'href' => route('role.calendar_review', $here + ['show' => $s]),
            'current' => $show === $s,
            'count' => $counts[$s] ?: null,
            'waiting' => $s === 'waiting',
        ])->all();
    @endphp

    @include('feed.partials.styles')

    <style {!! nonce_attr() !!}>
        .calrev-snippet { margin-top: 0.375rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); overflow-wrap: anywhere; }
        .calrev-snippet bdi { unicode-bidi: isolate; }
        .calrev-advice { margin-top: 0.25rem; font-size: 0.8125rem; color: rgb(var(--ap-ink-3)); }
        .calrev-chips { display: flex; flex-wrap: wrap; gap: 0.375rem; margin-top: 0.375rem; }
    </style>

    <div class="page-shell">
        <x-page-header :title="__('messages.calendar_review_title')" :lead="__('messages.calendar_review_lead')"
                       :back="route('role.view_admin', $here + ['tab' => 'schedule'])" :back-label="$role->getDisplayName()">
            @if ($show === 'waiting' && $reviewCount > 0 && ! $publishingCount)
            <x-slot name="actions">
                <button type="submit" form="calendar-publish-all" class="inline-flex items-center justify-center rounded-lg bg-[var(--brand-button-bg)] px-4 py-3 text-base font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
                    {{ trans_choice('messages.calendar_publish_all', $reviewCount, ['count' => number_format($reviewCount)]) }}
                </button>
            </x-slot>
            @endif
        </x-page-header>

        <x-page-flash :keys="['success' => 'success', 'error' => 'error']" />

        <div class="page-stack">
            <x-page-tabs strip :tabs="$tabs" :label="__('messages.calendar_review_title')" id="calendar-review-tabs" />

            <form method="get" action="{{ route('role.calendar_review', $here) }}" class="page-filters" role="search">
                <input type="hidden" name="show" value="{{ $show }}">
                <div class="page-filter is-grow">
                    <label for="calendar-review-q" class="sr-only">{{ __('messages.search') }}</label>
                    <x-text-input id="calendar-review-q" name="q" type="search" :value="$search" placeholder="{{ __('messages.search') }}" />
                </div>
                <div class="page-filter is-end">
                    <x-secondary-button type="submit">{{ __('messages.search') }}</x-secondary-button>
                </div>
            </form>

            @if ($publishingCount)
            <x-page-notice tone="info">{{ trans_choice('messages.calendar_publishing_now', $publishingCount, ['count' => number_format($publishingCount)]) }}</x-page-notice>
            @endif

            @if ($show === 'waiting' && $reviewCount > 0 && $reviewCount < $counts['waiting'])
            <x-page-notice tone="info">{{ __('messages.calendar_publish_all_skips') }}</x-page-notice>
            @endif

            @if ($groups->isEmpty())
            <div class="ap-card rounded-xl">
                <x-page-empty :title="$search !== '' ? __('messages.calendar_review_empty_search') : match ($show) {
                    'taken' => __('messages.calendar_review_empty_taken'),
                    'hidden' => __('messages.calendar_review_empty_hidden'),
                    default => __('messages.calendar_review_empty_title'),
                }" :text="__('messages.calendar_review_empty_text')" />
            </div>
            @else
            <x-page-card flush id="calendar-review">
                <x-slot name="aside">
                    <button type="submit" form="calendar-review-form" name="action" value="none" hidden tabindex="-1" aria-hidden="true" data-calrev-nothing></button>
                    @if ($show !== 'hidden')
                    <button type="submit" form="calendar-review-form" name="action" value="hide" class="page-tool">{{ __('messages.calendar_keep_selected_hidden') }}</button>
                    @endif
                    <button type="submit" form="calendar-review-form" name="action" value="publish" class="page-tool" data-confirm="{{ __('messages.calendar_publish_selected_confirm') }}">{{ __('messages.calendar_publish_selected') }}</button>
                </x-slot>
                <form method="post" id="calendar-review-form" action="{{ route('role.calendar_review.decide', $here) }}">
                    @csrf
                    <input type="hidden" name="show" value="{{ $show }}">
                    <input type="hidden" name="q" value="{{ $search }}">
                    <table class="page-table feed-waiting">
                        <colgroup><col style="width:2.75rem"><col><col style="width:17rem"></colgroup>
                        <thead><tr>
                            <th scope="col" class="c-tick"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900" aria-label="{{ __('messages.feeds_select_all') }}" data-calrev-tick-all></th>
                            <th scope="col">{{ __('messages.feeds_col_event_soonest') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                        </tr></thead>
                        <tbody>
                            @foreach ($groups as $group)
                            @php
                                $event = $group['event'];
                                $dates = $group['holds']->count();
                                $start = $event->starts_at ? $event->getStartDateTime(null, true) : null;
                                $held = in_array($group['reason'], $content, true);
                                $provider = $providerName($event->import_source);
                                // Whether taking the details out in the calendar brings it back by
                                // itself: a hide the sync made with somewhere to go back to. Not
                                // one the operator made, nor a draft that waits for a look anyway.
                                $firstHold = $group['holds']->first();
                                $comesBack = $firstHold->was_visibility !== null && $firstHold->held_by === null;
                            @endphp
                            <tr>
                                <td class="c-tick"><input type="checkbox" name="events[]" value="{{ $group['ids'] }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900" aria-label="{{ __('messages.feeds_select_one', ['name' => $event->name]) }}" data-calrev-tick></td>
                                <td class="c-main">
                                    <div class="feed-draft">
                                        @if ($start)
                                        <span class="feed-date" aria-hidden="true"><b>{{ $start->format('j') }}</b>{{ $start->translatedFormat('M') }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="event-list-name feed-name" v-pre><bdi>{{ $event->name }}</bdi></div>
                                            <div class="event-list-sub" v-pre>
                                                @if ($dates > 1)
                                                {{ trans_choice('messages.calendar_review_dates', $dates, ['count' => number_format($dates)]) }}
                                                @elseif ($start)
                                                {{ $start->translatedFormat('D').', ' }}<bdi dir="ltr">{{ $start->format($clock) }}</bdi>
                                                @endif
                                            </div>
                                            <div class="calrev-chips">
                                                <span class="event-status {{ $held ? 'is-warn' : '' }}">{{ $reasonLabel($group['reason']) }}</span>
                                            </div>
                                            @if ($held && $group['snippet'])
                                            <div class="calrev-snippet" v-pre><bdi>{!! $group['snippet'] !!}</bdi></div>
                                            <div class="calrev-advice">{{ $comesBack ? __('messages.calendar_review_advice_publishes', ['provider' => $provider]) : __('messages.calendar_review_advice_drafts', ['provider' => $provider]) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="c-actions">
                                    @if ($dates === 1)
                                    <a href="{{ $event->getGuestUrl($role->subdomain) }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view') }}</a>
                                    <a href="{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="event-link">{{ __('messages.edit') }}</a>
                                    @endif
                                    @if ($show !== 'hidden')
                                    <button type="submit" name="hide_one" value="{{ $group['ids'] }}" class="event-link event-link-quiet">{{ __('messages.calendar_keep_hidden') }}</button>
                                    @endif
                                    <button type="submit" name="publish_one" value="{{ $group['ids'] }}" class="event-link"
                                            @if ($held) data-confirm="{{ __('messages.calendar_publish_details_confirm') }}" @endif>{{ __('messages.publish') }}</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
                @if ($more)
                <x-slot name="foot">{{ __('messages.calendar_review_more') }}</x-slot>
                @endif
            </x-page-card>
            @endif
        </div>
    </div>

    @if ($show === 'waiting' && $reviewCount > 0 && ! $publishingCount)
    <form method="post" id="calendar-publish-all" action="{{ route('role.calendar_review.publish_all', $here) }}" data-confirm="{{ trans_choice('messages.calendar_publish_all_confirm', $reviewCount, ['count' => number_format($reviewCount)]) }}">@csrf</form>
    @endif

    <script {!! nonce_attr() !!}>
    document.addEventListener('DOMContentLoaded', function () {
        var all = document.querySelector('[data-calrev-tick-all]');
        var ticks = Array.prototype.slice.call(document.querySelectorAll('[data-calrev-tick]'));
        if (all) {
            all.addEventListener('change', function () {
                ticks.forEach(function (tick) { tick.checked = all.checked; });
            });
        }
        // Enter in a tick box presses the form's first button; that one asks for nothing.
        var nothing = document.querySelector('[data-calrev-nothing]');
        if (nothing) {
            nothing.addEventListener('click', function (event) { event.preventDefault(); });
        }
    });
    </script>
</x-app-admin-layout>
