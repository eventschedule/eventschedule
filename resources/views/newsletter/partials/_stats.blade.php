{{-- How one sent newsletter did: the figures as a strip, the two curves, the links that were
     clicked and who received it. Shared by the schedule owner's page and the platform admin's,
     which were two copies that had drifted (the admin's list had no names and no sorting).

     The owner sees the recipients' names and addresses here: they are that schedule's own
     followers and subscribers (CLAUDE.md, "Follower emails are visible on all
     schedule-owner-facing surfaces").

     Expects $newsletter, $recipients, $topLinks, and optionally $abTest, $role (the schedule, for
     its clock), $sortBy and $sortDir (headings sort only when the controller sorts). --}}
@php
    $statsRole = $role ?? null;
    $readerZone = auth()->user()->timezone ?? $statsRole?->timezone ?? 'UTC';
    $timeFormat = get_use_24_hour_time($statsRole) ? 'M j, H:i' : 'M j, g:i A';
    $rate = fn ($count, $of) => $of > 0 ? round(($count / $of) * 100, 1) . '%' : null;
    $failedCount = $newsletter->recipients()->where('status', 'failed')->count();
    $sortable = isset($sortBy);
    $recipientTones = ['sent' => 'is-on', 'pending' => 'is-warn', 'failed' => 'is-bad'];
    $recipientWords = ['sent' => __('messages.sent'), 'pending' => __('messages.pending'), 'failed' => __('messages.failed')];
    $abTest = $abTest ?? null;
@endphp

<div class="page-stack">
    <div class="ap-card rounded-xl page-stats is-auto">
        <div class="page-stat">
            <div class="page-stat-value">{{ number_format($newsletter->sent_count) }}</div>
            <div class="page-stat-label">{{ __('messages.sent') }}</div>
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ number_format($newsletter->open_count) }}</div>
            <div class="page-stat-label">{{ __('messages.opens') }}</div>
            @if ($rate($newsletter->open_count, $newsletter->sent_count))
            <div class="page-stat-sub">{{ $rate($newsletter->open_count, $newsletter->sent_count) }}</div>
            @endif
        </div>
        <div class="page-stat">
            <div class="page-stat-value">{{ number_format($newsletter->click_count) }}</div>
            <div class="page-stat-label">{{ __('messages.clicks') }}</div>
            @if ($rate($newsletter->click_count, $newsletter->sent_count))
            <div class="page-stat-sub">{{ $rate($newsletter->click_count, $newsletter->sent_count) }}</div>
            @endif
        </div>
        <div class="page-stat">
            <div class="page-stat-value {{ $failedCount > 0 ? 'is-bad' : '' }}">{{ number_format($failedCount) }}</div>
            <div class="page-stat-label">{{ __('messages.failed') }}</div>
        </div>
    </div>

    {{-- A/B test comparison --}}
    @if ($abTest && $abTest->newsletters->count() >= 2)
    <x-page-card :title="__('messages.ab_test_results')">
        <div class="page-grid2">
            @foreach ($abTest->newsletters->whereIn('ab_variant', ['A', 'B']) as $variant)
            <div class="news-variant {{ $abTest->winner_variant === $variant->ab_variant ? 'is-winner' : '' }}">
                <div class="news-variant-head">
                    <span>{{ __('messages.variant') }} {{ $variant->ab_variant }}</span>
                    @if ($abTest->winner_variant === $variant->ab_variant)
                    <span class="event-status is-on">{{ __('messages.winner') }}</span>
                    @endif
                </div>
                <p>{{ __('messages.subject') }}: <bdi>{{ $variant->subject }}</bdi></p>
                <div class="page-stats">
                    <div class="page-stat">
                        <div class="page-stat-value">{{ number_format($variant->sent_count) }}</div>
                        <div class="page-stat-label">{{ __('messages.sent') }}</div>
                    </div>
                    <div class="page-stat">
                        <div class="page-stat-value">{{ $variant->sent_count > 0 ? round(($variant->open_count / $variant->sent_count) * 100, 1) : 0 }}%</div>
                        <div class="page-stat-label">{{ __('messages.open_rate') }}</div>
                    </div>
                    <div class="page-stat">
                        <div class="page-stat-value">{{ $variant->sent_count > 0 ? round(($variant->click_count / $variant->sent_count) * 100, 1) : 0 }}%</div>
                        <div class="page-stat-label">{{ __('messages.click_rate') }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </x-page-card>
    @endif

    {{-- Charts --}}
    <div class="page-grid2">
        <x-page-card :title="__('messages.opens_over_time')">
            <div class="news-chart"><canvas id="openChart"></canvas></div>
        </x-page-card>
        <x-page-card :title="__('messages.clicks_over_time')">
            <div class="news-chart"><canvas id="clickChart"></canvas></div>
        </x-page-card>
    </div>

    {{-- Top clicked links --}}
    @if ($topLinks->isNotEmpty())
    <x-page-card :title="__('messages.top_clicked_links')">
        <dl class="page-kv news-links">
            @foreach ($topLinks as $link)
            <div>
                <dt><a href="{{ $link->url }}" target="_blank" rel="noopener noreferrer" class="event-link"><bdi>{{ $link->url }}</bdi></a></dt>
                <dd>{{ number_format($link->click_count) }} <span class="font-normal">{{ __('messages.clicks') }}</span></dd>
            </div>
            @endforeach
        </dl>
    </x-page-card>
    @endif

    {{-- Recipients --}}
    <x-page-card flush :title="__('messages.recipients')">
        @if ($recipients->count() > 0)
        <table class="page-table">
            <thead>
                <tr>
                    @if ($sortable)
                    <x-page-sort column="name" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.name') }}</x-page-sort>
                    <x-page-sort column="email" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.email') }}</x-page-sort>
                    <x-page-sort column="status" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.status') }}</x-page-sort>
                    <x-page-sort column="opened_at" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.opened') }}</x-page-sort>
                    <x-page-sort column="clicked_at" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.clicked') }}</x-page-sort>
                    @else
                    <th scope="col">{{ __('messages.name') }}</th>
                    <th scope="col">{{ __('messages.email') }}</th>
                    <th scope="col">{{ __('messages.status') }}</th>
                    <th scope="col">{{ __('messages.opened') }}</th>
                    <th scope="col">{{ __('messages.clicked') }}</th>
                    @endif
                </tr>
            </thead>
            <tbody>
                @foreach ($recipients as $recipient)
                <tr>
                    <td class="c-main c-strong">@if ($recipient->name)<x-user-text><bdi>{{ $recipient->name }}</bdi></x-user-text>@else<span class="c-quiet italic font-normal">{{ __('messages.no_name') }}</span>@endif</td>
                    <td class="c-wrap">{{ $recipient->email }}</td>
                    <td><span class="event-status {{ $recipientTones[$recipient->status] ?? '' }}">{{ $recipientWords[$recipient->status] ?? $recipient->status }}</span></td>
                    <td class="c-date" data-label="{{ __('messages.opened') }}">@if ($recipient->opened_at){{ \Carbon\Carbon::parse($recipient->opened_at)->timezone($readerZone)->translatedFormat($timeFormat) }}@if ($recipient->open_count > 1) <span class="c-quiet">({{ $recipient->open_count }}x)</span>@endif @endif</td>
                    <td class="c-date" data-label="{{ __('messages.clicked') }}">@if ($recipient->clicked_at){{ \Carbon\Carbon::parse($recipient->clicked_at)->timezone($readerZone)->translatedFormat($timeFormat) }}@if ($recipient->click_count > 1) <span class="c-quiet">({{ $recipient->click_count }}x)</span>@endif @endif</td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @if ($recipients->hasPages())
        <div class="page-card-foot">
            {{ $recipients->links() }}
        </div>
        @endif
        @else
        <x-page-empty compact :title="__('messages.no_recipients')" />
        @endif
    </x-page-card>
</div>

<script {!! nonce_attr() !!}>
    function initCharts() {
        {{-- The axes take the page's own ink and hairline, so they follow whichever of the six
             palettes is showing; they were two greys picked for "light" and "dark". --}}
        const css = getComputedStyle(document.documentElement);
        const token = (name) => {
            const value = css.getPropertyValue(name).trim();
            return value ? 'rgb(' + value.split(/\s+/).join(', ') + ')' : undefined;
        };
        const textColor = token('--ap-ink-3');
        const gridColor = token('--ap-border');
        const brand = css.getPropertyValue('--brand-blue').trim();

        const openData = @json($openTimeline);
        const clickData = @json($clickTimeline);

        const options = {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: {
                x: { ticks: { color: textColor }, grid: { color: gridColor } },
                y: { ticks: { color: textColor, precision: 0 }, grid: { color: gridColor }, beginAtZero: true },
            }
        };

        new Chart(document.getElementById('openChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: openData.map(d => d.date),
                datasets: [{
                    label: @json(__('messages.opens')),
                    data: openData.map(d => d.count),
                    borderColor: brand,
                    backgroundColor: 'rgba(78, 129, 250, 0.1)',
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: options
        });

        new Chart(document.getElementById('clickChart').getContext('2d'), {
            type: 'line',
            data: {
                labels: clickData.map(d => d.date),
                datasets: [{
                    label: @json(__('messages.clicks')),
                    data: clickData.map(d => d.count),
                    borderColor: '#10B981',
                    backgroundColor: 'rgba(16, 185, 129, 0.1)',
                    fill: true,
                    tension: 0.3,
                }]
            },
            options: options
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCharts);
    } else {
        initCharts();
    }
</script>
