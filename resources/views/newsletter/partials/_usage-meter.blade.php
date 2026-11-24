{{-- The newsletter allowance line, shared by index / create / edit.

     These three pages each carried an identical copy of this block, and the Plan tab carried a
     fourth with a progress bar the others lacked. They all render the usage-meter component, so
     the thresholds and wording cannot drift apart again.

     It is a quiet meter beside the page's own line (.news-meter, in partials/_styles): it used to
     be a bar across the whole page, between the buttons and the list, at the weight of a warning
     while saying "0 of 10".

     Expects $role in scope. Renders nothing when the schedule has no limit (selfhost, or its own
     SMTP configured). --}}
@if (($role ?? null) && $role->newsletterLimit() !== null)
    @php
        $newsletterLimit = $role->newsletterLimit();
        $newsletterUsed = $role->newslettersSentThisMonth();
        $showUpgrade = config('cashier.key')
            && $role->actualPlanTier() !== 'enterprise'
            && $newsletterLimit < 1000;
    @endphp
    @php
        // The 2026-11 redesign's meter: "**0** of 1,000 emails sent this month" over a thin blue bar.
        // What is left is said to a screen reader with the rest; on the page the bar says it.
        $newsletterLeft = max(0, $newsletterLimit - $newsletterUsed);
        $newsletterPercent = $newsletterLimit > 0 ? min(100, (int) round($newsletterUsed / $newsletterLimit * 100)) : 0;
        $newsletterWidth = $newsletterPercent === 0 && $newsletterUsed > 0 ? 2 : $newsletterPercent;
        $newsletterUsedPlain = __('messages.newsletters_used', ['used' => number_format($newsletterUsed), 'limit' => number_format($newsletterLimit)]);
    @endphp
    <div class="news-meter">
        <p class="news-meter-line">{!! __('messages.newsletters_used', ['used' => '<strong>'.e(number_format($newsletterUsed)).'</strong>', 'limit' => e(number_format($newsletterLimit))]) !!}</p>
        <div class="news-meter-track" role="progressbar"
            aria-label="{{ __('messages.newsletter_usage') }}"
            aria-valuenow="{{ $newsletterUsed }}" aria-valuemin="0" aria-valuemax="{{ $newsletterLimit }}"
            aria-valuetext="{{ $newsletterUsedPlain }} · {{ __('messages.newsletters_remaining', ['count' => number_format($newsletterLeft)]) }}">
            <div @class(['news-meter-fill', 'is-full' => $newsletterLeft === 0]) style="width: {{ $newsletterWidth }}%"></div>
        </div>
        @if ($showUpgrade)
        <p class="news-meter-upgrade"><a href="{{ route('role.subscribe', ['subdomain' => $role->subdomain]) }}" class="event-link">{{ __('messages.newsletter_upgrade_plan') }}</a></p>
        @endif
    </div>
@endif
