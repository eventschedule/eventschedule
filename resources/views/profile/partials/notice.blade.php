{{-- The bordered amber panel every warning on the settings page uses, with its triangle.
     $noticeText is the sentence; $noticeDemo set means "only in demo mode, and say that the
     section cannot be changed". The names are long on purpose: an @include also receives every
     variable of the view that includes it, so a short one could arrive from there by accident. --}}
@php
    $noticeIsDemo = ! empty($noticeDemo);
    $noticeSentence = $noticeIsDemo ? __('messages.demo_mode_settings_disabled') : ($noticeText ?? '');
@endphp
@if (! $noticeIsDemo || is_demo_mode())
<div @if (! empty($noticeId)) id="{{ $noticeId }}" @endif class="{{ $noticeClass ?? 'mb-5' }} form-kit-fields flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3" @if (! empty($noticeHidden)) hidden @endif>
    <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
        <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
    </svg>
    <p class="min-w-0 text-sm text-amber-800 dark:text-amber-200" data-notice-text>{{ $noticeSentence }}</p>
</div>
@endif
