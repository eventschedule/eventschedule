<x-app-admin-layout>

    {{-- One feed: what waits for somebody first (a decision, then drafts to look over), and after
         that what the feed has been doing. Everything it shows of an event is somebody else's
         text: printed as text, with v-pre, and names in <bdi>. --}}
    @php
        $feedsUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'feeds']);
        $hash = \App\Utils\UrlUtils::encodeId($feed->id);
        $here = ['subdomain' => $role->subdomain, 'hash' => $hash];
        $use24 = get_use_24_hour_time($role);
        $zone = $role->captureTimezone();
        $clock = $use24 ? 'H:i' : 'g:i A';
        $at = fn ($moment) => $moment->copy()->setTimezone($zone);
        // An event's start, as its schedule shows it.
        $starts = fn ($event) => $event->starts_at ? $event->getStartDateTime(null, true) : null;
        $whenOf = fn ($moment) => $moment ? $moment->translatedFormat('D j M').', '.$moment->format($clock) : '';
        $failingSince = $feed->last_success_at ?? $feed->created_at;
        $failing = $feed->failure_count > 0 && $failingSince->lt(now()->subDay());
        $held = count($feed->held_leaving ?? []);
        $reads = $feed->stats['reads'] ?? [];
        // Whether a run reads this feed at all. Where none does, nothing is "being published"
        // and nothing is read next, and the page must not say so.
        $reading = $allowed && ! $feed->isPaused();
        $requested = $reading ? $feed->items()->whereNotNull('publish_requested_at')->count() : 0;
        // A try that is due is "within a minute", never "2 minutes ago".
        $nextTry = $feed->next_check_at && $feed->next_check_at->gt(now()->addMinute()) ? $feed->next_check_at : now()->addSeconds(90);
        $readText = match (true) {
            // Beside "Not read since Thursday" the last good read is not news; the next try is.
            $failing => __('messages.feeds_trying_again', ['when' => $nextTry->diffForHumans()]),
            $feed->last_success_at !== null => __('messages.feeds_read_at', ['time' => $at($feed->last_success_at)->format($clock), 'next' => $at($feed->next_check_at ?? now()->addHour())->format($clock)]),
            default => __('messages.feeds_read_soon'),
        };
        // The feed's state in a word: the mark in the header and the title of the card when
        // nothing waits, which said "Up to date" over a feed that was paused or could not be read.
        [$stateTone, $stateText] = match (true) {
            ! $allowed => ['', __('messages.feeds_status_off_plan')],
            $feed->isPaused() => ['is-warn', __('messages.feeds_status_paused')],
            $failing => ['is-bad', $feed->last_success_at ? __('messages.feeds_status_failing', ['date' => $at($feed->last_success_at)->translatedFormat('D j M')]) : __('messages.feeds_status_failing_never')],
            $feed->baseline_done_at === null => ['', __($feed->last_success_at ? 'messages.feeds_status_first' : 'messages.feeds_status_never')],
            default => ['is-on', __('messages.feeds_status_ok')],
        };
        // Where no run publishes for it, Publish all does a page of them in the request.
        $publishNow = ! $reading && $waiting->total() > $publishAtOnce;
    @endphp

    @include('feed.partials.styles')

    <div class="page-shell">
        <x-page-header :title="$feed->name" :back="$feedsUrl" :back-label="__('messages.feeds_tab')">
            <x-slot name="status">
                <div class="feed-line">
                    @if ($reading && $feed->decide_count > 0)
                    <span class="event-status is-warn">{{ trans_choice('messages.feeds_status_decide', $feed->decide_count, ['count' => number_format($feed->decide_count)]) }}</span>
                    @else
                    <span class="event-status {{ $stateTone }}">{{ $stateText }}</span>
                    @endif
                    @if ($waiting->total() > 0)
                    <a href="#waiting" class="event-link">{{ trans_choice('messages.feeds_waiting', $waiting->total(), ['count' => number_format($waiting->total())]) }}</a>
                    @endif
                    <span>{{ trans_choice('messages.feeds_events_count', $eventsCount, ['count' => number_format($eventsCount)]) }}</span>
                    @if ($reading)
                    <span>{{ $readText }}</span>
                    <form method="post" action="{{ route('role.feeds.read', $here) }}">@csrf<button type="submit" class="event-link">{{ __('messages.feeds_read_now') }}</button></form>
                    @endif
                    @if ($canUndo)
                    <form method="post" action="{{ route('role.feeds.undo', $here) }}" data-confirm="{{ __('messages.feeds_undo_confirm') }}">@csrf<button type="submit" class="event-link event-link-quiet">{{ __('messages.feeds_undo') }}</button></form>
                    @endif
                </div>
                {{-- The site and the kind. Never the address: for a private calendar it is the key to it. --}}
                <div class="event-list-sub" style="margin-top:0.25rem"><span class="feed-addr" dir="ltr">{{ $feed->host }}</span> &middot; {{ __('messages.feeds_kind_'.$feed->kind) }}</div>
            </x-slot>
            <x-slot name="actions">
                {{-- Resume is the way on for a paused feed, so it comes last; Pause is a plain
                     button in the secondary link's clothes, because it posts. --}}
                @if (! $feed->isPaused())
                <form method="post" action="{{ route('role.feeds.pause', $here) }}">@csrf<button type="submit" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">{{ __('messages.pause') }}</button></form>
                @endif
                <x-secondary-link :href="route('role.feeds.edit', $here)">{{ __('messages.feeds_edit') }}</x-secondary-link>
                @if ($feed->isPaused() && $allowed)
                <form method="post" action="{{ route('role.feeds.resume', $here) }}">@csrf<x-brand-button type="submit">{{ __('messages.resume') }}</x-brand-button></form>
                @endif
            </x-slot>
        </x-page-header>

        @if (! $allowed)
        <x-page-notice tone="warn" class="mb-4">{{ __('messages.feeds_not_read_off_plan') }}</x-page-notice>
        @elseif ($feed->isPaused())
        <x-page-notice tone="warn" class="mb-4">{{ __('messages.feeds_paused_'.($feed->pause_reason ?: 'owner')) }}</x-page-notice>
        @endif
        @if ($held > 0)
        <x-page-notice tone="warn" class="mb-4">{{ trans_choice('messages.feeds_held', $held, ['count' => number_format($held)]) }}</x-page-notice>
        @endif
        @if ($feed->stats['continues_tomorrow'] ?? false)
        <x-page-notice tone="info" class="mb-4">{{ __('messages.feeds_continues_tomorrow') }}</x-page-notice>
        @endif

        <div class="page-stack">
            @if ($decisions->isNotEmpty())
            {{-- "People have signed up for these" only where they have for every one: an event
                 held because of the owner's own work on it has nobody signed up. --}}
            <x-page-card flush id="decide" :title="__('messages.feeds_decide_card_title')" :lead="$signedUp->min() > 0 ? __('messages.feeds_decide_card_lead') : null">
                <table class="page-table feed-decide">
                    <colgroup><col style="width:24rem"><col><col style="width:18rem"></colgroup>
                    <thead><tr>
                        <th scope="col">{{ __('messages.feeds_col_event') }}</th>
                        <th scope="col">{{ __('messages.feeds_col_says') }}</th>
                        <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                    </tr></thead>
                    <tbody>
                        @foreach ($decisions as $item)
                        @php
                            $event = $item->event;
                            $decision = $item->pending['decide'];
                            $moved = ($decision['kind'] ?? '') === 'moved';
                            $itemHash = \App\Utils\UrlUtils::encodeId($item->id);
                            $people = $signedUp[$item->id];
                            $newStart = $moved && isset($decision['starts_at']) ? \Carbon\Carbon::parse($decision['starts_at'], 'UTC')->setTimezone($event->scheduleTimezone()) : null;
                            $says = match (true) {
                                $newStart !== null => __('messages.feeds_says_moved_time', ['when' => $whenOf($newStart)]),
                                $moved => __('messages.feeds_says_moved_place'),
                                ($decision['kind'] ?? '') === 'cancelled' => __('messages.feeds_says_cancelled'),
                                default => __('messages.feeds_says_gone'),
                            };
                        @endphp
                        <tr>
                            <td class="c-main">
                                <div class="event-list-name feed-name"><a href="{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" v-pre><bdi>{{ $event->name }}</bdi></a></div>
                                <div class="event-list-sub">{{ $whenOf($starts($event)) }}</div>
                            </td>
                            <td>
                                <div class="feed-says">{{ $says }}</div>
                                @if ($people > 0)
                                <div class="event-list-sub">{{ trans_choice('messages.feeds_signed_up', $people, ['count' => number_format($people)]) }}</div>
                                @endif
                            </td>
                            <td class="c-actions">
                                <form method="post" action="{{ route('role.feeds.decide', $here + ['item' => $itemHash]) }}">
                                    @csrf
                                    <input type="hidden" name="answer" value="keep">
                                    <button type="submit" class="event-link">{{ __($moved ? 'messages.feeds_keep_as_is' : 'messages.feeds_keep_event') }}</button>
                                </form>
                                <button type="button" class="event-link {{ $moved ? '' : 'is-danger' }}" data-feed-confirm="confirm-{{ $itemHash }}" aria-expanded="false" aria-controls="confirm-{{ $itemHash }}">{{ __($moved ? 'messages.feeds_move_event' : 'messages.feeds_cancel_event') }}&hellip;</button>
                            </td>
                        </tr>
                        {{-- What pressing it does, and whether the people who signed up are told,
                             said before it is done: the same question the event's own Cancel asks. --}}
                        <tr class="feed-confirm" id="confirm-{{ $itemHash }}" hidden>
                            <td colspan="3">
                                <form method="post" action="{{ route('role.feeds.decide', $here + ['item' => $itemHash]) }}" class="feed-confirm-box">
                                    @csrf
                                    <input type="hidden" name="answer" value="apply">
                                    <h3 tabindex="-1" v-pre>{{ __($moved ? 'messages.feeds_confirm_move_title' : 'messages.feeds_confirm_cancel_title', ['name' => $event->name]) }}</h3>
                                    <p>{{ $moved ? $says.'. '.__('messages.feeds_confirm_move_text') : __('messages.feeds_confirm_cancel_text') }}</p>
                                    {{-- Offered only where the email would reach somebody. --}}
                                    @if ($canTell[$item->id])
                                    <x-toggle name="notify" :id="'notify-'.$itemHash" :checked="true" :label="__('messages.feeds_confirm_notify')" :help="__('messages.feeds_confirm_notify_help')" />
                                    <textarea name="note" rows="2" maxlength="280" class="block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]" placeholder="{{ __('messages.feeds_confirm_note') }}"></textarea>
                                    @endif
                                    <div class="page-form-actions">
                                        <button type="button" class="event-link" data-feed-never="confirm-{{ $itemHash }}">{{ __('messages.feeds_never_mind') }}</button>
                                        @if ($moved)
                                        <x-brand-button type="submit" size="sm">{{ __('messages.feeds_move_event') }}</x-brand-button>
                                        @else
                                        <x-danger-button type="submit">{{ __('messages.feeds_cancel_event') }}</x-danger-button>
                                        @endif
                                    </div>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-page-card>
            @endif

            @if ($waiting->total() > 0)
            <x-page-card flush id="waiting" :title="__('messages.feeds_waiting_title')" :lead="__('messages.feeds_waiting_lead')">
                <x-slot name="aside">
                    @if ($requested > 0)
                    <span class="event-list-sub" role="status">{{ trans_choice('messages.feeds_publishing_requested', $requested, ['count' => number_format($requested)]) }}</span>
                    @endif
                    {{-- Enter on a tick box presses the form's first button that can be pressed
                         (Chrome and Firefox both; a disabled one is passed over, which is why this
                         is not one), and the first one here used to delete. This one stands in
                         front and asks for nothing: the script at the foot stops it, and without
                         script the page answers it with itself. --}}
                    <button type="submit" form="feed-review" name="action" value="none" hidden tabindex="-1" aria-hidden="true" data-feed-nothing></button>
                    {{-- For what is ticked. Shown once something is (the script at the foot); with no
                         script they are simply there. Skip deletes for good, so it asks first. --}}
                    <button type="submit" form="feed-review" name="action" value="skip" class="page-tool" data-feed-bulk data-confirm="{{ __('messages.feeds_skip_selected_confirm') }}">{{ __('messages.feeds_skip_selected') }}</button>
                    <button type="submit" form="feed-review" name="action" value="publish" class="page-tool" data-feed-bulk>{{ __('messages.feeds_publish_selected') }}</button>
                    <button type="submit" form="feed-publish-all" class="page-tool" style="border-color:transparent;background:var(--brand-button-bg);color:#fff">{{ $publishNow ? __('messages.feeds_publish_next', ['count' => number_format($publishAtOnce)]) : __('messages.feeds_publish_all', ['count' => number_format($waiting->total())]) }}</button>
                </x-slot>
                <form method="post" id="feed-review" action="{{ route('role.feeds.review', $here) }}">
                    @csrf
                    <table class="page-table feed-waiting">
                        <colgroup><col style="width:2.75rem"><col><col style="width:15rem"></colgroup>
                        <thead><tr>
                            <th scope="col" class="c-tick"><input type="checkbox" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900" aria-label="{{ __('messages.feeds_select_all') }}" data-feed-tick-all></th>
                            <th scope="col">{{ __('messages.feeds_col_event_soonest') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                        </tr></thead>
                        <tbody>
                            @foreach ($waiting as $item)
                            @php
                                $event = $item->event;
                                $start = $starts($event);
                                $itemHash = \App\Utils\UrlUtils::encodeId($item->id);
                                $venue = $event->venue;
                                $missing = array_filter([
                                    ! ($event->getAttributes()['flyer_image_url'] ?? null) && ! $item->image_pending ? __('messages.feeds_no_picture') : null,
                                    ! $venue && ! $event->event_url ? __('messages.feeds_no_place') : null,
                                ]);
                            @endphp
                            <tr>
                                <td class="c-tick"><input type="checkbox" name="items[]" value="{{ $itemHash }}" class="rounded border-gray-300 dark:border-gray-600 dark:bg-gray-900" aria-label="{{ __('messages.feeds_select_one', ['name' => $event->name]) }}" data-feed-tick></td>
                                <td class="c-main">
                                    <div class="feed-draft">
                                        @if ($start)
                                        <span class="feed-date" aria-hidden="true"><b>{{ $start->format('j') }}</b>{{ $start->translatedFormat('M') }}</span>
                                        @endif
                                        <div class="min-w-0">
                                            <div class="event-list-name feed-name" v-pre><bdi>{{ $event->name }}</bdi></div>
                                            <div class="event-list-sub" v-pre>{{ $start ? $start->translatedFormat('D').', '.$start->format($clock) : '' }}@if ($venue) &middot; <bdi>{{ $venue->getDisplayName() }}</bdi>@endif</div>
                                            @if ($missing)
                                            <div class="feed-missing">{{ implode(', ', $missing) }}</div>
                                            @endif
                                        </div>
                                    </div>
                                </td>
                                <td class="c-actions">
                                    <a href="{{ $event->getGuestUrl($role->subdomain) }}" target="_blank" rel="noopener" class="event-link">{{ __('messages.view') }}</a>
                                    <a href="{{ route('event.edit', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}" class="event-link">{{ __('messages.edit') }}</a>
                                    <button type="submit" name="skip_one" value="{{ $itemHash }}" class="event-link event-link-quiet" data-confirm="{{ __('messages.feeds_skip_confirm') }}">{{ __('messages.feeds_skip') }}</button>
                                    <button type="submit" name="publish_one" value="{{ $itemHash }}" class="event-link">{{ __('messages.publish') }}</button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </form>
                <x-slot name="foot">
                    {{ __('messages.feeds_range', ['from' => number_format($waiting->firstItem()), 'to' => number_format($waiting->lastItem()), 'total' => number_format($waiting->total())]) }}
                    @if ($waiting->previousPageUrl())
                    &nbsp;&middot;&nbsp; <a href="{{ $waiting->previousPageUrl() }}#waiting" class="event-link">{{ __('messages.previous') }}</a>
                    @endif
                    @if ($waiting->nextPageUrl())
                    &nbsp;&middot;&nbsp; <a href="{{ $waiting->nextPageUrl() }}#waiting" class="event-link">{{ __('messages.next') }}</a>
                    @endif
                </x-slot>
            </x-page-card>
            {{-- Its own form, named by the button above: a form cannot stand inside another. --}}
            <form method="post" id="feed-publish-all" action="{{ route('role.feeds.publish_all', $here) }}" data-confirm="{{ $publishNow ? __('messages.feeds_publish_next_confirm', ['count' => number_format($publishAtOnce)]) : __('messages.feeds_publish_all_confirm', ['count' => number_format($waiting->total())]) }}">@csrf</form>
            @elseif ($decisions->isEmpty())
            <div class="ap-card rounded-xl">
                <x-page-empty compact :title="$stateText" :text="__('messages.feeds_nothing_waiting')" />
            </div>
            @endif

            @if ($reads)
            <x-page-card flush :title="__('messages.feeds_reads_title')">
                <table class="page-table feed-reads">
                    <colgroup><col style="width:13rem"><col></colgroup>
                    <thead><tr>
                        <th scope="col">{{ __('messages.feeds_col_when') }}</th>
                        <th scope="col">{{ __('messages.feeds_col_changed') }}</th>
                    </tr></thead>
                    <tbody>
                        @foreach ($reads as $read)
                        @php
                            $readAt = $at(\Carbon\Carbon::parse($read['at']));
                            $parts = array_filter([
                                ($read['created'] ?? 0) ? trans_choice('messages.feeds_read_added', $read['created'], ['count' => number_format($read['created'])]) : null,
                                ($read['updated'] ?? 0) ? trans_choice('messages.feeds_read_updated', $read['updated'], ['count' => number_format($read['updated'])]) : null,
                                ($read['matched'] ?? 0) ? trans_choice('messages.feeds_read_matched', $read['matched'], ['count' => number_format($read['matched'])]) : null,
                                ($read['held'] ?? 0) ? trans_choice('messages.feeds_read_held', $read['held'], ['count' => number_format($read['held'])]) : null,
                                ($read['left_out'] ?? 0) ? trans_choice('messages.feeds_read_left_out', $read['left_out'], ['count' => number_format($read['left_out'])]) : null,
                            ]);
                        @endphp
                        <tr>
                            <td class="c-main c-date">{{ $readAt->translatedFormat('D j M') }}, {{ $readAt->format($clock) }}</td>
                            <td>{{ implode(', ', $parts) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-page-card>
            @endif

        </div>
    </div>

    <script {!! nonce_attr() !!}>
        document.addEventListener('DOMContentLoaded', function () {
            // Ticking the head ticks the page.
            var all = document.querySelector('[data-feed-tick-all]');
            var ticks = Array.prototype.slice.call(document.querySelectorAll('[data-feed-tick]'));
            var bulk = Array.prototype.slice.call(document.querySelectorAll('[data-feed-bulk]'));
            function showBulk() {
                var any = ticks.some(function (tick) { return tick.checked; });
                bulk.forEach(function (button) { button.hidden = !any; });
            }
            if (all) {
                all.addEventListener('change', function () {
                    ticks.forEach(function (tick) { tick.checked = all.checked; });
                    showBulk();
                });
            }
            ticks.forEach(function (tick) { tick.addEventListener('change', showBulk); });
            showBulk();

            // A decision opens what it will do under its row, and takes the focus there.
            function setOpen(id, open) {
                var row = document.getElementById(id);
                var button = document.querySelector('[data-feed-confirm="' + id + '"]');
                if (!row) return;
                row.hidden = !open;
                if (button) button.setAttribute('aria-expanded', open ? 'true' : 'false');
                if (open) {
                    var title = row.querySelector('h3');
                    if (title) title.focus();
                } else if (button) {
                    button.focus();
                }
            }
            document.addEventListener('click', function (event) {
                // What Enter on a tick box presses: nothing happens.
                if (event.target.closest && event.target.closest('[data-feed-nothing]')) {
                    event.preventDefault();
                    return;
                }
                var open = event.target.closest ? event.target.closest('[data-feed-confirm]') : null;
                var never = event.target.closest ? event.target.closest('[data-feed-never]') : null;
                if (open) setOpen(open.getAttribute('data-feed-confirm'), open.getAttribute('aria-expanded') !== 'true');
                if (never) setOpen(never.getAttribute('data-feed-never'), false);
            });
            document.addEventListener('keydown', function (event) {
                if (event.key !== 'Escape') return;
                var row = event.target.closest ? event.target.closest('.feed-confirm') : null;
                if (row) setOpen(row.id, false);
            });
        });
    </script>
</x-app-admin-layout>
