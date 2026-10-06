{{-- Recovery codes, shown once: when two-factor is switched on and when new ones are made. --}}
@if (session('two_factor_recovery_codes'))
<div class="event-add-box">
    <p class="text-sm font-medium text-gray-900 dark:text-gray-100 mb-1">{{ __('messages.two_factor_recovery_codes_title') }}</p>
    <p class="text-xs text-gray-500 dark:text-gray-400 mb-3">{{ __('messages.two_factor_recovery_codes_warning') }}</p>
    <div class="grid grid-cols-2 gap-1">
        @foreach (session('two_factor_recovery_codes') as $code)
            <code class="text-sm font-mono text-gray-700 dark:text-gray-300 select-all">{{ $code }}</code>
        @endforeach
    </div>
</div>
@endif
