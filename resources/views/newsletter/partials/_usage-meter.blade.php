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
    <div class="news-meter">
        <x-usage-meter
            variant="inline"
            :label="__('messages.newsletter_usage')"
            :used="$newsletterUsed"
            :limit="$newsletterLimit"
            :usedText="__('messages.newsletters_used', ['used' => $newsletterUsed, 'limit' => $newsletterLimit])"
            :remainingText="__('messages.newsletters_remaining', ['count' => max(0, $newsletterLimit - $newsletterUsed)])"
            :upgradeUrl="$showUpgrade ? route('role.subscribe', ['subdomain' => $role->subdomain]) : null"
            :upgradeLabel="__('messages.newsletter_upgrade_plan')" />
    </div>
@endif
