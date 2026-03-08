@props(['tier' => 'pro', 'learnMoreUrl' => null, 'subdomain' => null])

<div {{ $attributes->merge(['class' => 'text-center py-10 px-6 rounded-xl bg-gray-50 dark:bg-[#252526] border border-gray-200 dark:border-[#2d2d30]']) }}>
    <div class="mx-auto flex h-14 w-14 items-center justify-center rounded-full bg-gray-100 dark:bg-white/[0.05] border border-gray-200 dark:border-white/[0.08]">
        @if (isset($icon))
            {{ $icon }}
        @else
            <svg class="h-7 w-7 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
            </svg>
        @endif
    </div>
    <p class="mt-4 text-sm text-gray-500 dark:text-gray-400">
        {{ $slot }}
    </p>
    @if (config('app.hosted'))
    <div class="flex items-center justify-center gap-3 mt-5">
        @if ($learnMoreUrl)
        <a href="{{ $learnMoreUrl }}" target="_blank"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg text-gray-600 dark:text-gray-400 text-sm font-medium hover:text-gray-900 dark:hover:text-gray-200 hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-all duration-200">
            {{ __('messages.learn_more') }}
        </a>
        @endif
        <a href="{{ route('role.subscribe', ['subdomain' => $subdomain, 'tier' => $tier]) }}" target="_blank"
            class="inline-flex items-center gap-1.5 px-4 py-2 rounded-lg bg-white dark:bg-white/[0.06] border border-gray-200 dark:border-white/[0.08] shadow-sm dark:shadow-none text-[var(--brand-blue)] text-sm font-medium hover:bg-gray-50 dark:hover:bg-white/[0.10] transition-all duration-200">
            {{ $tier === 'enterprise' ? __('messages.upgrade_to_enterprise') : __('messages.upgrade_to_pro_plan') }}
            <svg class="w-4 h-4 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
        </a>
    </div>
    @endif
</div>
