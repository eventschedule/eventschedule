{{-- The owner's actions on the Plan page (role/show-admin-plan). Every one is behind its own
     condition and this file renders nothing else, so that the page can ask whether there is
     anything to put in a card before it draws one. Keep it that way: text outside a condition
     here brings back the empty card. --}}
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

            {{-- The card-free selling trial. Also offered in the event editor, where the price is
                 typed; here because the dashboard's "paid tickets cannot be sold" to-do links to
                 this tab. A plain form is fine on this page: nothing unsaved to lose. --}}
            @if ($role->isEligibleForTicketTrial())
            <div>
                <form action="{{ route('subscription.ticket_trial', ['subdomain' => $role->subdomain]) }}" method="POST" class="inline">
                    @csrf
                    <input type="hidden" name="source" value="plan">
                    <button type="submit" class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                        {{ __('messages.ticket_trial_start', ['days' => (int) config('app.trial_days', 7)]) }}
                    </button>
                </form>
                <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.ticket_trial_plan_hint', ['days' => (int) config('app.trial_days', 7)]) }}</p>
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
                    {{ $role->currentPlanTerm() == 'yearly' ? plan_price(\App\Utils\PlatformPricing::enterpriseYearly()) . '/' . __('messages.year') : plan_price(\App\Utils\PlatformPricing::enterpriseMonthly()) . '/' . __('messages.month') }}
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
                $swapTierKey = $planTier === 'enterprise' ? 'enterprise' : 'pro';
                $swapMonthlyAmount = \App\Utils\PlatformPricing::amount($swapTierKey, 'monthly');
                $swapYearlyAmount = \App\Utils\PlatformPricing::amount($swapTierKey, 'yearly');
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
            {{-- Cancelling asks why, and never requires an answer: the reason is optional and the
                 cancel button is always live. The confirmation this replaced ("are you sure")
                 recorded nothing, which is why 18 cancellations came with no reason at all. --}}
            @if ($subscription && $subscription->active() && !$subscription->onGracePeriod())
            <div id="cancel-subscription-app">
                <button type="button" v-show="! open" @click="open = true" aria-controls="cancel-subscription-form" :aria-expanded="open ? 'true' : 'false'"
                    class="text-sm text-red-600 dark:text-red-400 hover:text-red-500 font-medium">
                    {{ __('messages.cancel_subscription') }}
                </button>
                <form id="cancel-subscription-form" v-show="open" style="display: none"
                    action="{{ route('subscription.cancel', ['subdomain' => $role->subdomain]) }}" method="POST"
                    class="max-w-xl space-y-4">
                    @csrf
                    <fieldset>
                        <legend class="text-base font-semibold text-gray-900 dark:text-gray-100">{{ __('messages.cancel_reason_question') }}</legend>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.cancel_reason_optional') }}</p>
                        <div class="mt-3 space-y-2">
                            @foreach (\App\Models\SubscriptionCancellation::REASONS as $reason)
                            <label class="flex items-center gap-3 text-sm text-gray-700 dark:text-gray-300 cursor-pointer">
                                <input type="radio" name="reason" value="{{ $reason }}"
                                    class="h-4 w-4 border-gray-300 dark:border-gray-600 text-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                {{ __('messages.cancel_reason_'.$reason) }}
                            </label>
                            @endforeach
                        </div>
                    </fieldset>
                    <div>
                        <x-input-label for="cancel_comment" :value="__('messages.cancel_reason_comment')" />
                        <textarea id="cancel_comment" name="comment" rows="3" maxlength="1000"
                            class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]"></textarea>
                    </div>
                    <p class="text-sm text-gray-500 dark:text-gray-400">{{ __('messages.cancel_keeps_until_period_end') }}</p>
                    <div class="flex flex-wrap items-center gap-3">
                        <button type="button" @click="open = false"
                            class="ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            {{ __('messages.keep_subscription') }}
                        </button>
                        <x-danger-button type="submit">{{ __('messages.cancel_subscription') }}</x-danger-button>
                    </div>
                </form>
            </div>
            <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
            <script {!! nonce_attr() !!}>
                Vue.createApp({ data: () => ({ open: false }) }).mount('#cancel-subscription-app');
            </script>
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
