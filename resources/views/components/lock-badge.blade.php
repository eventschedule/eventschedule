@props(['tier' => 'pro'])

{{-- The small "Pro" chip that marks a control the current plan cannot use. Deliberately neutral
     grey rather than brand-coloured: it labels a boundary, it is not a call to action. --}}
<span {{ $attributes->merge(['class' => 'inline-flex items-center gap-1 rounded-full bg-gray-100 dark:bg-white/[0.06] border border-gray-200 dark:border-white/[0.08] px-2 py-0.5 text-[11px] font-medium text-gray-500 dark:text-gray-400 align-middle']) }}>
    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
    </svg>
    {{ $tier === 'enterprise' ? __('messages.enterprise') : __('messages.pro') }}
</span>
