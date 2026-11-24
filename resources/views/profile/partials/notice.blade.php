{{-- The amber band every warning on the settings page uses.
     $noticeText is the sentence; $noticeDemo set means "only in demo mode, and say that the
     section cannot be changed". The names are long on purpose: an @include also receives every
     variable of the view that includes it, so a short one could arrive from there by accident. --}}
@php
    $noticeIsDemo = ! empty($noticeDemo);
    $noticeSentence = $noticeIsDemo ? __('messages.demo_mode_settings_disabled') : ($noticeText ?? '');
@endphp
@if (! $noticeIsDemo || is_demo_mode())
{{-- 2026-11 redesign: a soft amber band, no triangle. --}}
<div @if (! empty($noticeId)) id="{{ $noticeId }}" @endif class="{{ $noticeClass ?? 'mb-5' }} form-kit-fields flex items-start gap-3 bg-amber-100/80 dark:bg-amber-900/25 rounded-2xl px-4 py-3.5" @if (! empty($noticeHidden)) hidden @endif>
    <p class="min-w-0 text-[0.9375rem] text-gray-900 dark:text-amber-100" data-notice-text>{{ $noticeSentence }}</p>
</div>
@endif
