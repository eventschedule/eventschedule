{{-- The first-run screens' progress, at the end of their top bar: "Step 1 of 2" and a bar per step,
     filled up to this one (the 2026-11 design; /getting-started is step 1, the first schedule's
     form step 2). The words carry it for a screen reader; the bars are drawn for the eye only. --}}
@php
    $total = (int) ($total ?? 2);
    $current = (int) ($current ?? 1);
@endphp
<p class="flex items-center gap-2.5 text-[0.8125rem] font-medium text-gray-600 dark:text-gray-400" data-setup-step="{{ $current }}">
    <span>{{ __('messages.setup_step_of', ['current' => $current, 'total' => $total]) }}</span>
    <span class="flex gap-1" aria-hidden="true">
        @for ($step = 1; $step <= $total; $step++)
            <span class="block h-1 w-9 rounded-sm {{ $step <= $current ? 'bg-[var(--brand-blue)]' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
        @endfor
    </span>
</p>
