<div class="pt-5 container mx-auto">
    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-8 border border-gray-100 dark:border-gray-700 pb-10">
        <h4 class="text-xl font-bold mb-6 flex justify-between items-center pb-4 border-b border-gray-200 dark:border-gray-700">
            <span class="text-gray-800 dark:text-gray-100">{{ __('messages.plan') }}</span>
        </h4>

        @php
            $subscription = $role->subscription('default');
            $subscriptionStatus = $role->subscriptionStatusLabel();
            $isOwner = auth()->user()->id == $role->user_id;
        @endphp

        {{-- Trial Expiration Warning Banner --}}
        @if ($role->onGenericTrial() && $role->trialDaysRemaining() <= 30 && $isOwner)
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

        <div class="space-y-4">
            {{-- Current Plan --}}
            <div class="flex items-center">
                <span class="text-gray-600 dark:text-gray-400 w-40">{{ __('messages.curent_plan') }}</span>
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    {{ $role->isPro() ? __('messages.pro_plan') : __('messages.free_plan') }}
                    @if ($role->isPro() && $role->currentPlanTerm())
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
            @if (!$subscription && !$role->onGenericTrial() && $role->plan_type == 'pro' && $role->plan_expires)
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

        {{-- Newsletter Usage --}}
        @php $newsletterLimit = $role->newsletterLimit(); @endphp
        @if ($newsletterLimit !== null)
        @php
            $newsletterUsed = $role->newslettersSentThisMonth();
            $newsletterPercent = $newsletterLimit > 0 ? min(100, round(($newsletterUsed / $newsletterLimit) * 100)) : 0;
            $newsletterRemaining = max(0, $newsletterLimit - $newsletterUsed);
            $barColor = $newsletterPercent > 80 ? 'bg-red-500' : ($newsletterPercent >= 50 ? 'bg-yellow-500' : 'bg-emerald-500');
            $barBgColor = $newsletterPercent > 80 ? 'bg-red-100 dark:bg-red-900/30' : ($newsletterPercent >= 50 ? 'bg-yellow-100 dark:bg-yellow-900/30' : 'bg-emerald-100 dark:bg-emerald-900/30');
        @endphp
        <div class="mt-6 pt-6 border-t border-gray-200 dark:border-gray-700">
            <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ __('messages.newsletter_usage') }}</h5>
            <div class="flex items-center justify-between mb-2">
                <span class="text-sm text-gray-600 dark:text-gray-400">
                    {{ __('messages.newsletters_used', ['used' => $newsletterUsed, 'limit' => $newsletterLimit]) }}
                </span>
                <span class="text-sm text-gray-500 dark:text-gray-400">
                    {{ __('messages.newsletters_remaining', ['count' => $newsletterRemaining]) }}
                </span>
            </div>
            <div class="w-full h-2.5 rounded-full {{ $barBgColor }}">
                <div class="h-2.5 rounded-full {{ $barColor }} transition-all" style="width: {{ $newsletterPercent }}%"></div>
            </div>
        </div>
        @endif

        {{-- Action Buttons --}}
        @if ($isOwner)
        <div class="pt-8 space-y-4">
            {{-- Add Payment Method / Subscribe Button --}}
            @if (config('cashier.key') && !$subscription && ($role->onGenericTrial() || $role->plan_type == 'free' || ($role->plan_type == 'pro' && !$role->isPro())))
            <div>
                <a href="{{ route('role.subscribe', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center rounded-md bg-indigo-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-indigo-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-indigo-600">
                    {{ __('messages.upgrade_to_pro_plan') }}
                </a>
                @if ($role->isEligibleForFreeYear())
                <span class="ms-3 text-sm text-green-600 dark:text-green-400 font-medium">
                    {{ __('messages.first_year_free') }}
                </span>
                @endif
            </div>
            @endif

            {{-- Manage Subscription (Stripe Portal) --}}
            @if ($subscription && $role->stripe_id)
            <div>
                <a href="{{ route('subscription.portal', ['subdomain' => $role->subdomain]) }}"
                    class="inline-flex items-center rounded-md bg-white dark:bg-gray-700 px-4 py-2 text-sm font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-50 dark:hover:bg-gray-600">
                    {{ __('messages.manage_subscription') }}
                </a>
            </div>
            @endif

            {{-- Swap Plan (Monthly/Yearly) --}}
            @if ($subscription && $subscription->active() && !$subscription->onTrial())
            <div class="flex items-center gap-4">
                <span class="text-sm text-gray-600 dark:text-gray-400">{{ __('messages.switch_plan') }}:</span>
                @if ($role->currentPlanTerm() == 'monthly')
                <form action="{{ route('subscription.swap', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="plan" value="yearly">
                    <button type="submit" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium">
                        {{ __('messages.switch_to_yearly') }} ($50/{{ __('messages.year') }})
                    </button>
                </form>
                @else
                <form action="{{ route('subscription.swap', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="plan" value="monthly">
                    <button type="submit" class="text-sm text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 font-medium">
                        {{ __('messages.switch_to_monthly') }} ($5/{{ __('messages.month') }})
                    </button>
                </form>
                @endif
            </div>
            @endif

            {{-- Cancel Subscription --}}
            @if ($subscription && $subscription->active() && !$subscription->onGracePeriod())
            <div>
                <form action="{{ route('subscription.cancel', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline"
                    onsubmit="return confirm('{{ __('messages.are_you_sure') }}')">
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
                <a href="{{ route('role.change_plan', ['subdomain' => $role->subdomain, 'plan_type' => 'free']) }}"
                    onclick="return confirm('{{ __('messages.are_you_sure') }}')"
                    class="text-sm text-red-600 dark:text-red-400 hover:text-red-500 font-medium">
                    {{ __('messages.change_to_free_plan') }}
                </a>
            </div>
            @endif
        </div>
        @endif
    </div>
</div>
