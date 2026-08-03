@props([
    'tier' => 'pro',
    'title' => null,
    // Bullets of what the tier actually unlocks. Pass translated strings.
    'bullets' => [],
    'subdomain' => null,
    'learnMoreUrl' => null,
    // Pass both to render a usage meter inside the card.
    'used' => null,
    'limit' => null,
    'meterLabel' => null,
    'usedText' => null,
    'remainingText' => null,
    // 'card' for an in-page panel, 'banner' for a compact at-limit strip.
    'variant' => 'card',
    'showPrice' => true,
    // The schedule, when the caller has it. Enables the trial CTA.
    'role' => null,
])

{{-- Selfhosted installs resolve to Enterprise, so there is no plan to explain and nothing to buy. --}}
@if (config('app.hosted'))
@php
    $isEnterprise = $tier === 'enterprise';
    $monthly = (int) config($isEnterprise
        ? 'services.stripe_platform.enterprise_price_monthly_amount'
        : 'services.stripe_platform.price_monthly_amount', $isEnterprise ? 29 : 9);
    $yearly = (int) config($isEnterprise
        ? 'services.stripe_platform.enterprise_price_yearly_amount'
        : 'services.stripe_platform.price_yearly_amount', $isEnterprise ? 290 : 90);

    // Nearly every free schedule is trial-eligible, and "start a free trial" converts far better
    // than "subscribe". Demo schedules see the explanation but never a way to buy.
    $isDemo = is_demo_role($role);
    $canTrial = $role && ! $isDemo && $role->isEligibleForTrial();

    $upgradeUrl = $subdomain
        ? route('role.subscribe', ['subdomain' => $subdomain, 'tier' => $tier])
        : marketing_url('/pricing');
    $upgradeLabel = $canTrial
        ? __('messages.start_free_trial')
        : ($isEnterprise ? __('messages.upgrade_to_enterprise') : __('messages.upgrade_to_pro_plan'));

    $showMeter = ! is_null($used) && ! is_null($limit);
@endphp

@if ($variant === 'banner')
    {{-- At-limit states are warnings, so they use the standard AP amber panel. --}}
    <div {{ $attributes->merge(['class' => 'bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3']) }}>
        <div class="flex items-start gap-2">
            {{-- A lock, not a warning triangle: a triangle says the organizer did something wrong. --}}
            <svg class="w-5 h-5 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
            <div class="min-w-0 flex-1">
                @if ($title)
                    <p class="text-sm font-semibold text-amber-900 dark:text-amber-100">{{ $title }}</p>
                @endif
                <div class="text-sm text-amber-800 dark:text-amber-200 {{ $title ? 'mt-0.5' : '' }}">{{ $slot }}</div>

                @if (! $isDemo)
                    <div class="mt-3 flex flex-wrap items-center gap-3">
                        @if ($learnMoreUrl)
                            <a href="{{ $learnMoreUrl }}" target="_blank" rel="noopener"
                               class="text-sm font-medium text-amber-900 dark:text-amber-100 underline">{{ __('messages.learn_more') }}</a>
                        @endif
                        <a href="{{ $upgradeUrl }}"
                           class="inline-flex items-center gap-1.5 rounded-lg bg-[var(--brand-button-bg)] px-4 py-2 text-sm font-semibold text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                            {{ $upgradeLabel }}
                            <svg class="w-4 h-4 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                        </a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@else
    <div {{ $attributes->merge(['class' => 'ap-card rounded-xl p-6 flex flex-col']) }}>
        <div class="flex items-center gap-3 mb-3">
            <div class="dashboard-icon p-2 rounded-xl bg-blue-50 dark:bg-blue-500/10">
                <svg class="w-5 h-5 text-[var(--brand-blue)]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <div class="min-w-0">
                @if ($title)
                    <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                        {{ $title }} <x-lock-badge :tier="$tier" />
                    </h3>
                @endif
            </div>
        </div>

        <div class="text-sm text-gray-600 dark:text-gray-400">{{ $slot }}</div>

        @if ($showMeter)
            <x-usage-meter
                variant="inline"
                class="mt-4"
                :label="$meterLabel ?? ($title ?? '')"
                :used="$used"
                :limit="$limit"
                :usedText="$usedText"
                :remainingText="$remainingText" />
        @endif

        @if (count($bullets))
            <ul class="mt-4 grid sm:grid-cols-2 gap-x-6 gap-y-2">
                @foreach ($bullets as $bullet)
                    <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                        <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                        </svg>
                        <span>{{ $bullet }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($showPrice && ! $isDemo)
            <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
                {{ __('messages.plan_price_line', ['monthly' => $monthly, 'yearly' => $yearly]) }}
                @if ($canTrial)
                    <span class="text-green-600 dark:text-green-400">{{ __('messages.plan_trial_note', ['days' => config('app.trial_days', 7)]) }}</span>
                @endif
            </p>
        @endif

        @if (! $isDemo)
            {{-- mt-auto so actions line up across cards of differing height, forward action last. --}}
            <div class="mt-auto pt-5 flex flex-wrap items-center gap-3">
                <x-secondary-link href="{{ marketing_url('/pricing') }}" target="_blank" rel="noopener">
                    {{ __('messages.compare_plans') }}
                </x-secondary-link>
                {{-- Same tab on purpose: an upgrade opened in a new tab has no back button, and the
                     original tab keeps showing the stale locked state after payment. --}}
                <x-brand-link href="{{ $upgradeUrl }}">{{ $upgradeLabel }}</x-brand-link>
                @if ($learnMoreUrl)
                    <a href="{{ $learnMoreUrl }}" target="_blank" rel="noopener"
                       class="text-sm font-medium text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">{{ __('messages.learn_more') }}</a>
                @endif
            </div>
        @endif
    </div>
@endif
@endif
