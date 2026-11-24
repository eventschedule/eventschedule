{{-- What just happened: sales, new followers and newsletters, newest first
     (HomeDashboard::activity()).

     The icon says what kind of thing a row is, so the first line is the thing itself (the event,
     the person, the subject) and the kind opens the second. Nothing a phone cuts off is the part
     that matters. A sale names who bought; follower emails are shown here because this is a
     schedule owner's own page (CLAUDE.md, "Follower emails").

     A sale's amount is in the currency it was taken in. --}}
@php
    $rows = $activity['rows'] ?? [];
    // The 2026-11 design calls the card "Recent sales"; that is what it holds unless a follower or
    // a newsletter is among the rows, and then it is activity.
    $onlySales = $rows && collect($rows)->every(fn ($row) => $row['type'] === 'sale');
    $kinds = [
        'sale' => ['label' => __('messages.dash_sale'), 'tile' => 'bg-green-50 dark:bg-green-500/10', 'ink' => 'text-green-600 dark:text-green-400',
            'icon' => 'M16.5 6v.75m0 3v.75m0 3v.75m0 3V18m-9-5.25h5.25M7.5 15h3M3.375 5.25c-.621 0-1.125.504-1.125 1.125v3.026a2.999 2.999 0 010 5.198v3.026c0 .621.504 1.125 1.125 1.125h17.25c.621 0 1.125-.504 1.125-1.125v-3.026a2.999 2.999 0 010-5.198V6.375c0-.621-.504-1.125-1.125-1.125H3.375z'],
        'follower' => ['label' => __('messages.dash_new_follower'), 'tile' => 'bg-blue-50 dark:bg-blue-500/10', 'ink' => 'text-blue-600 dark:text-blue-400',
            'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
        'newsletter' => ['label' => __('messages.newsletter'), 'tile' => 'bg-amber-50 dark:bg-amber-500/10', 'ink' => 'text-amber-600 dark:text-amber-400',
            'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75'],
    ];
@endphp

{{-- 2026-11 redesign: a small capitals label with its link, and plain rows (the title, then who
     and how many, the amount at the end); no icon tile, the second line already says what kind. --}}
<section class="ap-card rounded-xl flex flex-col overflow-hidden self-start w-full" aria-labelledby="dashboard-activity">
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-2 flex items-baseline justify-between gap-3">
        <h2 id="dashboard-activity" class="text-xs font-semibold uppercase tracking-[0.06em] text-gray-500 dark:text-gray-400">{{ __($onlySales ? 'messages.recent_sales' : 'messages.recent_activity') }}</h2>
        <a href="{{ route('sales') }}" class="inline-flex items-center gap-1 text-[13px] font-semibold whitespace-nowrap text-[var(--brand-blue)] hover:underline">
            {{ __('messages.dash_all_sales') }}
            <svg class="w-3.5 h-3.5 {{ is_rtl() ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 4.5L21 12m0 0l-7.5 7.5M21 12H3" /></svg>
        </a>
    </div>

    @if ($rows)
    <ul class="px-4 sm:px-5 pb-2 divide-y divide-[rgb(var(--ap-border))]">
        @foreach ($rows as $row)
            @php
                $kind = $kinds[$row['type']];
                $second = collect([
                    $row['type'] === 'sale' ? null : $kind['label'],
                    $row['who'] ?? null,
                    $row['type'] === 'sale' ? trans_choice('messages.dash_tickets_count', $row['quantity'], ['count' => number_format($row['quantity'])]) : null,
                    $row['type'] === 'newsletter' ? __('messages.dash_sent_to', ['count' => number_format($row['sent'])]) : null,
                    $row['schedule'] ?? null,
                    $row['at']->diffForHumans(['short' => true]),
                ])->filter()->implode(' · ');
            @endphp
            <li class="flex items-center gap-3 py-3">
                <div class="min-w-0 flex-1">
                    <p class="break-words text-[15px] font-semibold tracking-[-0.01em] text-gray-900 dark:text-white" v-pre>{{ $row['title'] }}</p>
                    <p class="break-words text-[13px] leading-snug text-gray-600 dark:text-gray-400" v-pre>{{ $second }}</p>
                </div>
                @if (! empty($row['amount']))
                    <p class="shrink-0 text-[15px] font-semibold tabular-nums text-gray-900 dark:text-white" dir="ltr">{{ \App\Utils\MoneyUtils::format($row['amount'], $row['currency_code']) }}</p>
                @endif
            </li>
        @endforeach
    </ul>
    @else
        <p class="px-5 py-8 text-sm text-center text-gray-500 dark:text-gray-400">{{ __('messages.dash_no_activity') }}</p>
    @endif
</section>
