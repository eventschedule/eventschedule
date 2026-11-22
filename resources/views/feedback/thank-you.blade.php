<x-app-guest-layout :role="$role" :event="$event" :fonts="$fonts" :noIndex="true" :page-title="($event ? $event->translatedName() . ' - ' : '') . __('messages.feedback')">

    @include('partials.guest-form-pages-styles')
    <style {!! nonce_attr() !!}>
        .gk-pg-thanks .gk-pg-star { width: 1.75rem; height: 1.75rem; color: var(--gk-ink-3); }
        {{-- The rating given, in the stars' own gold: a rating is read the same on every page. --}}
        .gk-pg-thanks .gk-pg-star-on { color: #f5b301; }
        .gk-pg-thanks .gk-pg-quote { margin: 0; padding: 1rem 1.125rem; border-radius: .875rem; background: var(--gk-stage); color: var(--gk-ink-2); font-size: 1rem; line-height: 1.6; text-align: start; overflow-wrap: anywhere; }
    </style>

    <div class="gk-stage gk-pg gk-pg-sm gk-pg-thanks" style="font-family: '{{ str_replace('_', ' ', $role->font_family) }}', sans-serif;">

        <div class="gk-pg-head">
            @include('role.partials.form-page-who', ['whoRole' => $role, 'whoLink' => $role->getGuestUrl() ?: null])
            <p class="gk-dateline">
                <span>{{ $event->is_multi_day ? $event->getDateRangeDisplay($sale->event_date) : $event->getStartDateTime($sale->event_date, true)->translatedFormat('F j, Y') }}</span><span class="gk-dateline-dot" aria-hidden="true">·</span><bdi dir="ltr">{{ $event->getStartEndTime($sale->event_date) }}</bdi>
            </p>
            <h1 class="gk-title" dir="auto">{{ $event->name }}</h1>
        </div>

        {{-- Thank you card --}}
        <div class="gk-card gk-band gk-pg-flush mt-8 flex flex-col items-center gap-4 text-center">
            <span class="gk-pg-mark gk-pg-mark-ok" aria-hidden="true">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.75" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M4.5 12.75l6 6 9-13.5" />
                </svg>
            </span>

            <div>
                <h2 class="gk-h gk-h-lg">{{ __('messages.feedback_thank_you') }}</h2>
                <p class="gk-pg-lead">{{ __('messages.feedback_thank_you_message') }}</p>
            </div>

            {{-- Show submitted rating --}}
            <div class="flex justify-center gap-1" role="img" aria-label="{{ $existingFeedback->rating }} {{ __('messages.stars') }}">
                @for ($i = 1; $i <= 5; $i++)
                <svg class="gk-pg-star {{ $i <= $existingFeedback->rating ? 'gk-pg-star-on' : '' }}" viewBox="0 0 24 24" fill="{{ $i <= $existingFeedback->rating ? 'currentColor' : 'none' }}" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M11.48 3.499a.562.562 0 011.04 0l2.125 5.111a.563.563 0 00.475.345l5.518.442c.499.04.701.663.321.988l-4.204 3.602a.563.563 0 00-.182.557l1.285 5.385a.562.562 0 01-.84.61l-4.725-2.885a.563.563 0 00-.586 0L6.982 20.54a.562.562 0 01-.84-.61l1.285-5.386a.562.562 0 00-.182-.557l-4.204-3.602a.563.563 0 01.321-.988l5.518-.442a.563.563 0 00.475-.345L11.48 3.5z" />
                </svg>
                @endfor
            </div>

            @if ($existingFeedback->comment)
            <p class="gk-pg-quote w-full" dir="auto">{{ $existingFeedback->comment }}</p>
            @endif
        </div>

        {{-- Fan content CTA --}}
        @if ($event->isFanContentEnabled() && $event->getGuestUrl())
        @php
            $types = [];
            if ($event->isFanPhotosEnabled()) $types[] = mb_strtolower(__('messages.fan_photos_enabled'));
            if ($event->isFanVideosEnabled()) $types[] = mb_strtolower(__('messages.fan_videos_enabled'));
            if ($event->isFanCommentsEnabled()) $types[] = mb_strtolower(__('messages.fan_comments_enabled'));
        @endphp
        <section class="gk-sec gk-sec-rule mt-10">
            <h2 class="gk-h">{{ __('messages.feedback_share_content') }}</h2>
            <p class="gk-pg-lead">{{ __('messages.feedback_share_content_description', ['types' => implode(', ', $types)]) }}</p>
            <a href="{{ $event->getGuestUrl() }}#gp-fan-content" class="gk-btn gk-btn-primary gk-cta-main mt-5">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                </svg>
                {{ __('messages.feedback_share_content_link') }}
            </a>
        </section>
        @endif

    </div>

</x-app-guest-layout>
