@props([
    'label',
    'used' => 0,
    'limit' => null,
    // 'panel' for the Plan tab, 'inline' for a compact one-liner inside another panel.
    'variant' => 'panel',
    // Pre-rendered strings, not nouns. newsletters_used, photos_used and the ticket keys pluralize
    // differently per language, so a shared "N of M {noun}" key could not be translated properly.
    'usedText' => null,
    'remainingText' => null,
    'unlimitedText' => null,
    'noteText' => null,
    'upgradeUrl' => null,
    'upgradeLabel' => null,
    // Renders the separator the Plan tab stacks its meters with.
    'divider' => false,
])

@php
    $used = max(0, (int) $used);
    $isUnlimited = is_null($limit);
    $limit = $isUnlimited ? null : max(0, (int) $limit);

    $percent = $isUnlimited || $limit === 0 ? 0 : min(100, (int) round(($used / $limit) * 100));
    $remaining = $isUnlimited ? null : max(0, $limit - $used);

    // Deliberately no red band. Red reads as an error, and "you used everything you were given" is
    // success. The copy and the actions carry the escalation instead. >= 80 rather than > 80,
    // because exactly 80% used to fall through to the healthy colour.
    $fill = $percent >= 50 ? 'bg-amber-500' : 'bg-emerald-500';

    // Neutral track on every state, per the AP "depth through shades, not colour" rule. A track
    // tinted to match the fill makes the empty portion shout as loudly as the used portion.
    $track = 'bg-gray-200 dark:bg-white/[0.08]';

    $exhausted = ! $isUnlimited && $used >= $limit;
    $barHeight = $variant === 'inline' ? 'h-1.5' : 'h-2.5';

    // One unit out of a large allowance rounds to 0% and renders an invisible bar.
    $width = $percent === 0 && $used > 0 ? 2 : $percent;
@endphp

<div {{ $attributes->merge(['class' => $divider ? 'mt-6 pt-6 border-t border-gray-200 dark:border-gray-700' : '']) }}>
    @if ($variant === 'panel')
        <h5 class="text-sm font-semibold text-gray-700 dark:text-gray-300 mb-3">{{ $label }}</h5>
    @endif

    @if ($isUnlimited)
        <div class="flex items-center gap-2 text-sm text-gray-600 dark:text-gray-400">
            <svg class="w-4 h-4 flex-shrink-0 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
            </svg>
            <span>{{ $unlimitedText ?? __('messages.usage_unlimited') }}</span>
        </div>
    @else
        <div class="flex items-center justify-between gap-3 mb-2 text-sm">
            <span class="text-gray-600 dark:text-gray-400">
                {{ $usedText ?? __('messages.usage_used_of', ['used' => number_format($used), 'limit' => number_format($limit)]) }}
            </span>
            <span class="{{ $exhausted ? 'text-amber-700 dark:text-amber-400 font-medium' : 'text-gray-500 dark:text-gray-400' }} whitespace-nowrap">
                {{ $remainingText ?? __('messages.usage_remaining', ['count' => number_format($remaining)]) }}
            </span>
        </div>

        {{-- aria-valuetext because a bare "48" tells a screen reader nothing useful, and the
             remaining count is always present in words so nothing is signalled by colour alone. --}}
        <div class="w-full {{ $barHeight }} rounded-full {{ $track }}"
             role="progressbar"
             aria-label="{{ $label }}"
             aria-valuenow="{{ $used }}"
             aria-valuemin="0"
             aria-valuemax="{{ $limit }}"
             aria-valuetext="{{ $usedText ?? __('messages.usage_used_of', ['used' => number_format($used), 'limit' => number_format($limit)]) }}">
            <div class="{{ $barHeight }} rounded-full {{ $fill }} transition-all duration-200" style="width: {{ $width }}%"></div>
        </div>
    @endif

    @if ($noteText)
        <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">{{ $noteText }}</p>
    @endif

    @if ($upgradeUrl && $upgradeLabel)
        <p class="mt-2 text-xs">
            <a href="{{ $upgradeUrl }}" class="text-[var(--brand-blue)] hover:underline font-medium">{{ $upgradeLabel }}</a>
        </p>
    @endif
</div>
