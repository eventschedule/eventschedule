{{-- The line under a field of the partner request form that says what changing it does. While
     the ad runs ($bpHintWarn) a change there pauses it, so the line is the kit's amber notice in
     small (a tinted panel with its border and triangle, never coloured text alone). --}}
@if (filled($bpHintText))
@if ($bpHintWarn)
<p class="mt-2 flex items-start gap-2 rounded-lg border border-amber-200 bg-amber-50 p-2 text-sm text-amber-800 dark:border-amber-700 dark:bg-amber-900/20 dark:text-amber-200">
    <svg class="mt-0.5 h-4 w-4 flex-shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
    </svg>
    <span>{{ $bpHintText }}</span>
</p>
@else
<p class="bp-hint">{{ $bpHintText }}</p>
@endif
@endif
