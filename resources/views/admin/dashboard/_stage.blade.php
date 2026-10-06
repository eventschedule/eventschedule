{{-- Three segments for schedule, event and ticket type, and the step in words beside them. --}}
<span class="flex items-center gap-0.5 shrink-0" role="img" aria-label="{{ $label }}">
    @foreach ([1, 2, 3] as $step)
        <span class="h-1.5 w-4 rounded-full {{ $step <= $stage ? 'bg-[var(--brand-button-bg)]' : 'bg-gray-200 dark:bg-gray-600' }}"></span>
    @endforeach
</span>
<span class="text-xs truncate {{ $stage > 0 ? 'text-gray-700 dark:text-gray-300' : 'text-amber-700 dark:text-amber-400' }}">{{ $label }}</span>
