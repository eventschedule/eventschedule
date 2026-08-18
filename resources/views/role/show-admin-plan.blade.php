<div class="pt-5 container mx-auto">
    <div class="ap-card rounded-xl shadow-md p-8 border border-gray-100 pb-10">
        <h4 class="text-xl font-bold mb-6 flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
            <span class="text-gray-800 dark:text-gray-100">{{ __('messages.plan') }}</span>
        </h4>

        @php
            $subscription = $role->subscription('default');
            $subscriptionStatus = $role->subscriptionStatusLabel();
            $isOwner = auth()->user()->id == $role->user_id;
        @endphp

        {{-- Wind-down notice for an admin-granted plan that is now on a dated runway.
             These owners never started a trial - the plan was comped - so the generic
             "your trial expires" banner below would read as a mistake. Explains what is
             ending, what they keep for free, and what it costs to continue. --}}
        @php
            $isCompedWindDown = $role->plan_source === 'admin'
                && $role->onGenericTrial()
                && $isOwner;
            $windDownPrice = (int) config($role->plan_type === 'enterprise'
                ? 'services.stripe_platform.enterprise_price_monthly_amount'
                : 'services.stripe_platform.price_monthly_amount',
                $role->plan_type === 'enterprise' ? 29 : 9);
        @endphp

        @if ($isCompedWindDown)
        <div class="mb-6 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
            <div class="flex items-start gap-2">
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                </svg>
                <div>
                    <p class="font-medium text-amber-800 dark:text-amber-200">
                        {{ __('messages.comped_plan_ending_title', ['date' => $role->trial_ends_at->format('F j, Y')]) }}
                    </p>
                    <p class="mt-1 text-sm text-amber-700 dark:text-amber-300">
                        {{ __('messages.comped_plan_ending_body', ['price' => plan_price($windDownPrice)]) }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        {{-- Trial Expiration Warning Banner --}}
        @if (! $isCompedWindDown && $role->onGenericTrial() && $role->trialDaysRemaining() <= 30 && $isOwner)
        <div class="mb-6 p-4 rounded-lg {{ $role->trialDaysRemaining() <= 7 ? 'bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800' : 'bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800' }}">
            <div class="flex items-start">
                <svg class="w-5 h-5 {{ $role->trialDaysRemaining() <= 7 ? 'text-red-500' : 'text-yellow-500' }} mt-0.5 me-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                </svg>
                <div>
                    <p class="{{ $role->trialDaysRemaining() <= 7 ? 'text-red-800 dark:text-red-200' : 'text-yellow-800 dark:text-yellow-200' }} font-medium">
                        {{ __('messages.trial_expires_in_days', ['days' => $role->trialDaysRemaining()]) }}
                    </p>
                    <p class="{{ $role->trialDaysRemaining() <= 7 ? 'text-red-700 dark:text-red-300' : 'text-yellow-700 dark:text-yellow-300' }} text-sm mt-1">
                        {{ __('messages.add_payment_method_prompt') }}
                    </p>
                </div>
            </div>
        </div>
        @endif

        @php $planTier = $role->actualPlanTier(); @endphp

        {{-- The upgrade case for ads, shown only where it is actually true: this instance
             runs ads, and this schedule is on the tier that carries them. Removing them is a
             concrete Pro benefit alongside removing the branding footer. --}}
        @if ($planTier === 'free' && $role->showAds())
        <div class="mb-4 rounded-lg border border-amber-200 bg-amber-50 p-3 dark:border-amber-700 dark:bg-amber-900/20">
            <p class="flex items-start gap-2 text-sm text-amber-800 dark:text-amber-200">
                <svg class="mt-0.5 h-5 w-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                </svg>
                <span>@lang('messages.plan_ads_upgrade_prompt')</span>
            </p>
        </div>
        @endif

        <div class="space-y-4">
            {{-- Current Plan --}}
            <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 w-40">{{ __('messages.curent_plan') }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    @if ($planTier === 'enterprise')
                        {{ __('messages.enterprise') }}
                    @elseif ($planTier === 'pro')
                        {{ __('messages.pro_plan') }}
                    @else
                        {{ __('messages.free_plan') }}
                    @endif
                    @if ($planTier !== 'free' && $role->currentPlanTerm())
                        ({{ $role->currentPlanTerm() == 'yearly' ? __('messages.yearly') : __('messages.monthly') }})
                    @endif
                </span>
            </div>

            {{-- Subscription Status --}}
            @if ($subscription || $role->onGenericTrial())
            <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 w-40">{{ __('messages.status') }}</span>
                <span class="font-medium">
                    @if ($subscriptionStatus == 'trial')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300">
                            {{ __('messages.trial') }}
                        </span>
                    @elseif ($subscriptionStatus == 'active')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-300">
                            {{ __('messages.active') }}
                        </span>
                    @elseif ($subscriptionStatus == 'cancelled' || $subscriptionStatus == 'grace_period')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-300">
                            {{ __('messages.cancelled') }}
                        </span>
                    @elseif ($subscriptionStatus == 'past_due')
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-300">
                            {{ __('messages.past_due') }}
                        </span>
                    @else
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                            {{ __('messages.inactive') }}
                        </span>
                    @endif
                </span>
            </div>
            @endif

            {{-- Trial End Date --}}
            @if ($role->onGenericTrial())
            <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 w-40">{{ __('messages.trial_ends') }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ $role->trial_ends_at->format('F j, Y') }}
                    ({{ $role->trialDaysRemaining() }} {{ __('messages.days_remaining') }})
                </span>
            </div>
            @elseif ($subscription && $subscription->onTrial())
            <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 w-40">{{ __('messages.trial_ends') }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ $subscription->trial_ends_at->format('F j, Y') }}
                </span>
            </div>
            @endif

            {{-- Subscription End Date (for cancelled subscriptions) --}}
            @if ($subscription && $subscription->onGracePeriod())
            <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 w-40">{{ __('messages.access_until') }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ $subscription->ends_at->format('F j, Y') }}
                </span>
            </div>
            @endif

            {{-- Legacy: plan_expires for non-subscription users --}}
            @if (!$subscription && !$role->onGenericTrial() && in_array($role->plan_type, ['pro', 'enterprise']) && $role->plan_expires)
            <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 w-40">{{ __('messages.expires_on') }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ \Carbon\Carbon::parse($role->plan_expires)->format('F j, Y') }}
                </span>
            </div>
            @endif

            {{-- Payment Method --}}
            @if ($role->hasDefaultPaymentMethod())
            <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 w-40">{{ __('messages.payment_method') }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ ucfirst($role->pm_type) }} **** {{ $role->pm_last_four }}
                </span>
            </div>
            @endif
        </div>

        {{-- Usage meters. All three share <x-usage-meter>; the markup used to be hand-rolled and
             duplicated here and across three newsletter pages, which is how the thresholds drifted.
             Ticket sales come first: it is the allowance tied to revenue. --}}

        {{-- Paid ticket allowance --}}
        @if (config('app.hosted'))
        @php
            $ticketLimit = $role->ticketSaleLimit();
            $ticketUsed = $ticketLimit === null ? 0 : $role->ticketsSoldThisMonth();
            $ticketResetDate = $role->ticketAllowanceResetsAt()->translatedFormat('F j');
        @endphp
        <x-usage-meter
            variant="panel"
            divider
            :label="__('messages.ticket_allowance_usage')"
            :used="$ticketUsed"
            :limit="$ticketLimit"
            :usedText="$ticketLimit === null ? null : __('messages.tickets_sold_of', ['used' => $ticketUsed, 'limit' => $ticketLimit])"
            :unlimitedText="__('messages.ticket_allowance_unlimited')"
            :noteText="$ticketLimit === null ? null : __('messages.ticket_allowance_note', ['date' => $ticketResetDate])"
            :upgradeUrl="$ticketLimit !== null && config('cashier.key') ? route('role.subscribe', ['subdomain' => $role->subdomain]) : null"
            :upgradeLabel="__('messages.ticket_allowance_upgrade')" />
        @endif

        {{-- Newsletter Usage --}}
        @php $newsletterLimit = $role->newsletterLimit(); @endphp
        @if (config('app.hosted'))
        @php $newsletterUsed = $newsletterLimit === null ? 0 : $role->newslettersSentThisMonth(); @endphp
        <x-usage-meter
            variant="panel"
            divider
            :label="__('messages.newsletter_usage')"
            :used="$newsletterUsed"
            :limit="$newsletterLimit"
            :usedText="$newsletterLimit === null ? null : __('messages.newsletters_used', ['used' => $newsletterUsed, 'limit' => $newsletterLimit])"
            :remainingText="$newsletterLimit === null ? null : __('messages.newsletters_remaining', ['count' => max(0, $newsletterLimit - $newsletterUsed)])"
            :unlimitedText="__('messages.usage_unlimited')"
            :upgradeUrl="$newsletterLimit !== null && config('cashier.key') && $planTier !== 'enterprise' && $newsletterLimit < 1000 ? route('role.subscribe', ['subdomain' => $role->subdomain]) : null"
            :upgradeLabel="__('messages.newsletter_upgrade_plan')" />
        @endif

        {{-- Photo Usage --}}
        @php $photoLimit = $role->photoLimit(); @endphp
        @if (config('app.hosted'))
        @php $photoUsed = $photoLimit === null ? 0 : $role->photoCount(); @endphp
        <x-usage-meter
            variant="panel"
            divider
            :label="__('messages.photo_usage')"
            :used="$photoUsed"
            :limit="$photoLimit"
            :usedText="$photoLimit === null ? null : __('messages.photos_used', ['used' => $photoUsed, 'limit' => $photoLimit])"
            :remainingText="$photoLimit === null ? null : __('messages.photos_remaining', ['count' => max(0, $photoLimit - $photoUsed)])"
            :unlimitedText="__('messages.usage_unlimited')"
            :upgradeUrl="$photoLimit !== null && config('cashier.key') ? route('role.subscribe', ['subdomain' => $role->subdomain]) : null"
            :upgradeLabel="__('messages.photo_upgrade_plan')" />
        @endif
    </div>

    {{-- What the plan actually is. This is the page a confused owner lands on, so it is the right
         place to spell out what Free carries and what Pro adds, rather than only showing what is
         running out. --}}
    @if ($planTier === 'free' && config('app.hosted'))
    <div class="mt-4">
        <x-plan-gate
            tier="pro"
            :role="$role"
            :subdomain="$role->subdomain"
            :title="__('messages.plan_overview_title')"
            :learnMoreUrl="marketing_url('/pricing')"
            :bullets="[
                __('messages.ticket_allowance_pro_bullet_unlimited'),
                __('messages.ticket_allowance_pro_bullet_checkin'),
                __('messages.ticket_allowance_pro_bullet_promo'),
                __('messages.ticket_allowance_pro_bullet_waitlist'),
                __('messages.ticket_allowance_pro_bullet_passes'),
                __('messages.appointment_type_pro_bullet_unlimited'),
            ]">
            {{ __('messages.plan_overview_body', [
                'tickets' => $role->ticketSaleLimit() ?? 0,
                'types' => $role->appointmentTypeLimit() ?? 0,
            ]) }}
        </x-plan-gate>
    </div>
    @endif

    {{-- Owner-only actions. The card opens INSIDE the condition: every child here is itself
         conditional, so an unconditional card rendered an empty bordered box for team members and
         for selfhost owners with no cashier key. --}}
    @if ($isOwner)
    <div class="ap-card rounded-xl shadow-md p-8 border border-gray-100 mt-4">
        <div class="space-y-4">
        <div class="space-y-4">
            {{-- Subscribe Button (Free users or expired Pro/Enterprise) --}}
            @if (config('cashier.key') && !$role->hasActiveSubscription() && !$role->onGracePeriod() && ($role->onGenericTrial() || $role->plan_type == 'free' || ($role->plan_type == 'pro' && !$role->isPro())))
            <div>
                <a href="{{ route('role.subscribe', ['subdomain' => $role->subdomain]) }}"
                    class="relative overflow-hidden inline-flex items-center rounded-lg bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 px-4 py-2 text-sm font-semibold text-white shadow-lg shadow-blue-500/25 transition-all">
                    <span class="relative z-10">{{ __('messages.upgrade_to_pro_plan') }}</span>
                    <div class="absolute inset-0 animate-shimmer"></div>
                </a>
                @if ($role->isEligibleForTrial())
                <span class="ms-3 text-sm text-green-600 dark:text-green-400 font-medium">
                    {{ __('messages.free_trial_badge') }}
                </span>
                @endif
            </div>
            @endif

            {{-- Upgrade to Enterprise (for active Pro subscribers) --}}
            @if (config('cashier.key') && config('services.stripe_platform.enterprise_price_monthly') && config('services.stripe_platform.enterprise_price_yearly') && $role->hasActiveSubscription() && !$role->hasActiveEnterpriseSubscription() && $subscription && $subscription->active() && !$subscription->onGracePeriod())
            <div>
                <form action="{{ route('subscription.swap', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline form-confirm" data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $role->currentPlanTerm() == 'yearly' ? 'yearly' : 'monthly' }}">
                    <input type="hidden" name="tier" value="enterprise">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-amber-500 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-amber-400 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-amber-500">
                        {{ __('messages.upgrade_to_enterprise') }}
                    </button>
                </form>
                <span class="ms-3 text-sm text-gray-500 dark:text-gray-400">
                    {{ $role->currentPlanTerm() == 'yearly' ? plan_price(config('services.stripe_platform.enterprise_price_yearly_amount')) . '/' . __('messages.year') : plan_price(config('services.stripe_platform.enterprise_price_monthly_amount')) . '/' . __('messages.month') }}
                </span>
            </div>
            @endif

            {{-- Manage Subscription (Stripe Portal) --}}
            @if ($subscription && $role->stripe_id)
            <div>
                <a href="{{ route('subscription.portal', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center rounded-lg bg-white dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('messages.manage_subscription') }}
                </a>
            </div>
            @endif

            {{-- Swap Plan (Monthly/Yearly) --}}
            @if ($subscription && $subscription->active() && !$subscription->onGracePeriod())
            @php
                $isEnterpriseTier = $planTier === 'enterprise';
                $swapMonthlyAmount = $isEnterpriseTier ? config('services.stripe_platform.enterprise_price_monthly_amount') : config('services.stripe_platform.price_monthly_amount');
                $swapYearlyAmount = $isEnterpriseTier ? config('services.stripe_platform.enterprise_price_yearly_amount') : config('services.stripe_platform.price_yearly_amount');
            @endphp
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.switch_plan') }}:</span>
                @if ($role->currentPlanTerm() == 'monthly')
                <form action="{{ route('subscription.swap', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="plan" value="yearly">
                    <input type="hidden" name="tier" value="{{ $planTier }}">
                    <button type="submit" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium">
                        {{ __('messages.switch_to_yearly') }} ({{ plan_price($swapYearlyAmount) }}/{{ __('messages.year') }})
                    </button>
                </form>
                @else
                <form action="{{ route('subscription.swap', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="plan" value="monthly">
                    <input type="hidden" name="tier" value="{{ $planTier }}">
                    <button type="submit" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium">
                        {{ __('messages.switch_to_monthly') }} ({{ plan_price($swapMonthlyAmount) }}/{{ __('messages.month') }})
                    </button>
                </form>
                @endif
            </div>
            @endif

            {{-- Cancel Subscription --}}
            @if ($subscription && $subscription->active() && !$subscription->onGracePeriod())
            <div>
                <form action="{{ route('subscription.cancel', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline form-confirm" data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <button type="submit" class="text-sm text-red-600 dark:text-red-400 hover:text-red-500 font-medium">
                        {{ __('messages.cancel_subscription') }}
                    </button>
                </form>
            </div>
            @endif

            {{-- Resume Subscription --}}
            @if ($subscription && $subscription->onGracePeriod())
            <div>
                <form action="{{ route('subscription.resume', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline">
                    @csrf
                    <x-success-button type="submit">
                        {{ __('messages.resume_subscription') }}
                    </x-success-button>
                </form>
            </div>
            @endif

            {{-- Change to Free Plan (legacy) --}}
            @if (!$subscription && $role->plan_type == 'pro' && $role->isPro() && !is_demo_mode())
            <div>
                <form method="POST" action="{{ route('role.change_plan', ['subdomain' => $role->subdomain, 'plan_type' => 'free']) }}"
                    data-confirm="{{ __('messages.are_you_sure') }}">
                    @csrf
                    <button type="submit"
                        class="text-sm text-red-600 dark:text-red-400 hover:text-red-500 font-medium">
                        {{ __('messages.change_to_free_plan') }}
                    </button>
                </form>
            </div>
            @endif
        </div>
        </div>
    </div>
    @endif
</div>

@if (config('app.hosted'))
<div class="pt-5 container mx-auto">
    <div class="ap-card rounded-xl shadow-md p-8 border border-gray-100">
        <div class="flex items-start gap-4">
            <svg class="w-8 h-8 text-[var(--brand-blue)] flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 24 24">
                <path d="M21,12L14,5V9C7,10 4,15 3,20C5.5,16.5 9,14.9 14,14.9V19L21,12Z" />
            </svg>
            <div>
                <h4 class="text-lg font-semibold text-gray-800 dark:text-gray-100 mb-1">{{ __('messages.referral_program') }}</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400 mb-3">{{ __('messages.referral_plan_page_description') }}</p>
                <a href="{{ route('referrals') }}" class="text-[var(--brand-blue)] hover:underline font-medium text-sm">
                    {{ __('messages.view_referral_dashboard') }} &rarr;
                </a>
            </div>
        </div>
    </div>
</div>
@endif

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.link-confirm').forEach(function(link) {
        link.addEventListener('click', function(e) {
            if (!confirm(this.getAttribute('data-confirm'))) {
                e.preventDefault();
            }
        });
    });
});
</script>
