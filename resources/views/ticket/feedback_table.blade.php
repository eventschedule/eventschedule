{{-- The Feedback tab of ticket/sales, fetched into #feedback-table (and again on a sort or a
     page of answers). The pipeline as one strip of figures, then its three stages: mail still to
     be sent, mail sent and not answered, and the answers. Each list is drawn once for every width
     (.page-table); the old tab drew each twice, and its four hand-made stat cards took a screen
     and a half of a phone before the first name. --}}
@php
    $feedbackDay = function ($day) {
        // event_date is the day as the venue counts it, stored as text.
        if (! $day) {
            return '';
        }
        try {
            return \Carbon\Carbon::parse($day)->translatedFormat('M j, Y');
        } catch (\Exception $feedbackDayError) {
            return $day;
        }
    };
    $feedbackStar = 'M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z';
    // When the hourly run will get to a queue. Counted from the clock: Carbon's diffInHours() is
    // signed, so "hours until a time still to come" was negative, every wait read "in ~1 hours"
    // and the day was never shown.
    $feedbackSendsIn = function ($when) {
        $hours = (int) ceil(($when->getTimestamp() - now()->getTimestamp()) / 3600);
        if ($hours <= 1) {
            return __('messages.feedback_sends_within_hour');
        }
        if ($hours < 24) {
            return __('messages.feedback_sends_in', ['count' => $hours]);
        }

        return $when->translatedFormat('M j, g:i A');
    };
@endphp

<div class="page-stack">
    <div class="ap-card rounded-xl page-stats is-auto">
        <div class="page-stat">
            <div class="page-stat-value">{{ $pendingCount }}</div>
            <div class="page-stat-label">{{ __('messages.feedback_pending') }}</div>
            <div class="page-stat-sub">
                @if ($nextSendAt)
                {{ __('messages.feedback_next_send', ['time' => $feedbackSendsIn($nextSendAt)]) }}
                @else
                {{ __('messages.feedback_none_pending') }}
                @endif
            </div>
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ $awaitingCount }}</div>
            <div class="page-stat-label">{{ __('messages.feedback_sent') }}</div>
            <div class="page-stat-sub">{{ __('messages.feedback_awaiting_response') }}</div>
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ $feedbackCount }}</div>
            <div class="page-stat-label">{{ __('messages.feedback_responded') }}</div>
            @if ($averageRating)
            <div class="page-stat-sub">
                <span class="sales-stars" aria-hidden="true">
                    @for ($i = 1; $i <= 5; $i++)
                    <svg class="{{ $i <= round($averageRating) ? 'is-on' : '' }}" viewBox="0 0 24 24" fill="{{ $i <= round($averageRating) ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feedbackStar }}" /></svg>
                    @endfor
                </span>
                {{ $averageRating }}
            </div>
            @endif
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ $responseRate }}%</div>
            <div class="page-stat-label">{{ __('messages.feedback_response_rate') }}</div>
        </div>
    </div>

    {{-- Still to be sent, by event. --}}
    @if ($pendingCount > 0)
    <details class="ap-card rounded-xl page-card is-flush sales-fold" open>
        <summary class="page-card-head">
            <span class="sales-fold-title">
                <svg class="sales-fold-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                <h2 class="page-card-title">{{ __('messages.feedback_pending_emails') }}</h2>
                <span class="event-chip">{{ $pendingCount }}</span>
            </span>
        </summary>
        <div class="sales-fold-bar">
            <span>
                {{ __('messages.feedback_checked_hourly') }}
                @if ($excludedCount > 0)
                &middot; {{ __('messages.feedback_excluded_note', ['count' => $excludedCount]) }}
                @endif
            </span>
            <span class="page-actions">
                {{-- Calling the queue off comes first and sending it goes last: the forward
                     button is the one at the end. --}}
                <form method="POST" action="{{ route('sales.cancel_feedback') }}">
                    @csrf
                    <button type="submit" class="event-link is-danger" data-confirm="{{ __('messages.feedback_cancel_confirm', ['count' => $pendingCount]) }}">{{ __('messages.feedback_cancel_all', ['count' => $pendingCount]) }}</button>
                </form>
                @if ($readyToSendCount > 0)
                <form method="POST" action="{{ route('sales.send_feedback_now') }}">
                    @csrf
                    <button type="submit" class="page-tool" data-confirm="{{ __('messages.feedback_send_now_confirm', ['count' => $readyToSendCount]) }}">{{ __('messages.feedback_send_now', ['count' => $readyToSendCount]) }}</button>
                </form>
                @endif
            </span>
        </div>
        <table class="page-table">
            <thead>
                <tr>
                    <th scope="col">{{ __('messages.event') }}</th>
                    <th scope="col">{{ __('messages.date') }}</th>
                    <th scope="col">{{ __('messages.attendee') }}</th>
                    <th scope="col">{{ __('messages.feedback_estimated_send') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($pendingGroups as $pendingGroup)
                <tr>
                    <td class="c-main">
                        <div class="sales-customer">
                            <button type="button" class="sales-open" data-toggle-row="pending-{{ $loop->index }}" aria-expanded="false">
                                <svg fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                                <span class="sr-only">{{ __('messages.details') }}</span>
                            </button>
                            <span class="c-strong c-wrap"><bdi>{{ $pendingGroup->event_name }}</bdi></span>
                        </div>
                    </td>
                    <td class="c-date">{{ $feedbackDay($pendingGroup->event_date) }}</td>
                    <td>{{ __('messages.feedback_attendees_count', ['count' => $pendingGroup->count]) }}</td>
                    <td>
                        @if ($pendingGroup->estimated_send_at->isPast())
                        <span class="event-status is-on">{{ $feedbackSendsIn($pendingGroup->estimated_send_at) }}</span>
                        @else
                        {{ $feedbackSendsIn($pendingGroup->estimated_send_at) }}
                        @endif
                    </td>
                </tr>
                <tr class="detail-row-pending-{{ $loop->index }} sales-detail hidden">
                    <td colspan="4" class="c-main">
                        <ul class="sales-lines sales-detail-body">
                            @foreach ($pendingGroup->sales as $pendingSale)
                            <li><span><bdi>{{ $pendingSale->name }}</bdi></span> <span dir="ltr">{{ $pendingSale->email }}</span></li>
                            @endforeach
                        </ul>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </details>
    @endif

    {{-- Sent, and not answered yet. --}}
    @if ($awaitingCount > 0)
    <details class="ap-card rounded-xl page-card is-flush sales-fold" open>
        <summary class="page-card-head">
            <span class="sales-fold-title">
                <svg class="sales-fold-arrow" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" /></svg>
                <h2 class="page-card-title">{{ __('messages.feedback_sent_awaiting') }}</h2>
                <span class="event-chip">{{ $awaitingCount }}</span>
            </span>
        </summary>
        <table class="page-table is-hover">
            <thead>
                <tr>
                    <th scope="col">{{ __('messages.attendee') }}</th>
                    <th scope="col">{{ __('messages.event') }}</th>
                    <th scope="col">{{ __('messages.date') }}</th>
                    <th scope="col">{{ __('messages.feedback_sent') }}</th>
                    <th scope="col"><span class="sr-only">{{ __('messages.feedback_resend') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($awaitingSales as $awaitingSale)
                <tr>
                    <td class="c-main c-strong"><bdi>{{ $awaitingSale->name }}</bdi></td>
                    <td class="c-wrap"><bdi>{{ $awaitingSale->event->name ?? '' }}</bdi></td>
                    <td class="c-date">{{ $feedbackDay($awaitingSale->event_date) }}</td>
                    <td class="c-date" data-label="{{ __('messages.feedback_sent') }}">{{ $awaitingSale->feedback_sent_at->translatedFormat('M j, Y g:i A') }}</td>
                    <td class="c-actions">
                        <button type="button" class="event-link" data-resend-feedback="{{ \App\Utils\UrlUtils::encodeId($awaitingSale->id) }}">{{ __('messages.feedback_resend') }}</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if ($awaitingCount > 50)
        <div class="page-card-foot">{{ __('messages.feedback_more_results', ['count' => $awaitingCount - 50]) }}</div>
        @endif
    </details>
    @endif

    {{-- The answers. --}}
    <section class="ap-card rounded-xl page-card is-flush">
        <div class="page-card-head">
            <div class="min-w-0">
                <h2 class="page-card-title">{{ __('messages.feedback_responses') }}</h2>
            </div>
            @if ($feedbacks->count() > 0)
            <div class="page-actions">
                <a href="{{ route('sales.export_feedback') }}" class="page-tool">
                    <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                    </svg>
                    {{ __('messages.feedback_export') }}
                </a>
            </div>
            @endif
        </div>
        @if ($feedbacks->count() > 0)
        <div class="page-scroll">
            <table class="page-table is-wide is-hover">
                <thead>
                    <tr>
                        <x-page-sort column="attendee_name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.attendee') }}</x-page-sort>
                        <x-page-sort column="event_name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.event') }}</x-page-sort>
                        <x-page-sort column="event_date" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.date') }}</x-page-sort>
                        <x-page-sort column="rating" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.rating') }}</x-page-sort>
                        <th scope="col">{{ __('messages.comment') }}</th>
                        <x-page-sort column="created_at" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.submitted') }}</x-page-sort>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($feedbacks as $feedback)
                    <tr>
                        <td class="c-main c-strong"><bdi>{{ $feedback->sale->name ?? '' }}</bdi></td>
                        <td class="c-wrap"><bdi>{{ $feedback->event->name ?? '' }}</bdi></td>
                        <td class="c-date">{{ $feedbackDay($feedback->event_date) }}</td>
                        <td>
                            <span class="sales-stars" role="img" aria-label="{{ $feedback->rating }}/5">
                                @for ($i = 1; $i <= 5; $i++)
                                <svg class="{{ $i <= $feedback->rating ? 'is-on' : '' }}" viewBox="0 0 24 24" fill="{{ $i <= $feedback->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $feedbackStar }}" /></svg>
                                @endfor
                            </span>
                        </td>
                        {{-- The whole comment, on every width: the table used to cut it at eighty
                             characters, and only the phone's cards showed what was said. --}}
                        <td class="c-wrap sales-comment">@if ($feedback->comment)<bdi>{{ $feedback->comment }}</bdi>@endif</td>
                        <td class="c-date" data-label="{{ __('messages.submitted') }}">{{ $feedback->created_at->translatedFormat('M j, Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <x-page-empty compact :title="__('messages.feedback_no_responses')" :text="__('messages.feedback_enabled_help')" />
        @endif
    </section>

    @if ($feedbacks->hasPages())
    <div class="page-pager">
        {{ $feedbacks->links() }}
    </div>
    @endif
</div>
