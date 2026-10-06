{{--
    The body of the Gallery section on the event form and the schedule form: the plan gate for a
    free schedule, the "hidden" banner for a downgraded one, and the editor itself.

    On the event form the whole section sits inside the form's Vue app, which provides
    galleryStore and friends. The schedule form has no Vue app, so there the editor gets an island
    of its own (#gallery-editor-app, mounted by role/edit.blade.php) and the rest stays plain HTML.

    Expects: $galleryContext ('event' | 'schedule'), $galleryMode, $galleryRole,
    $galleryCanUpgrade, $galleryImageCount. Optional: $galleryWrapperClass, the column the form's
    other tabs use (the event form's are wider than the schedule form's).
--}}
@php
    $galleryIsEvent = $galleryContext === 'event';
    $galleryLearnMore = marketing_url($galleryIsEvent ? '/docs/creating-events#gallery' : '/docs/creating-schedules#gallery');
@endphp
<div class="{{ $galleryWrapperClass ?? 'max-w-xl' }}">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2 flex items-center gap-2">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6">
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
        </svg>
        {{ __('messages.gallery') }}
    </h2>
    <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
        {{ $galleryIsEvent ? __('messages.gallery_help_event') : __('messages.gallery_help_schedule') }}
    </p>

    @if ($galleryMode === 'locked')
        @if ($galleryCanUpgrade)
            <x-plan-gate
                tier="pro"
                :role="$galleryRole"
                :subdomain="$galleryRole->subdomain"
                :learnMoreUrl="$galleryLearnMore"
                :title="__('messages.gallery_upsell_title')"
                :bullets="[
                    __('messages.gallery_upsell_bullet_photos', ['max' => \App\Utils\GalleryUtils::maxImages()]),
                    __('messages.gallery_upsell_bullet_viewer'),
                    __('messages.gallery_upsell_bullet_fans'),
                ]">
                {{ __('messages.gallery_upsell_body') }}
            </x-plan-gate>
        @else
            <p class="text-sm text-gray-600 dark:text-gray-300" v-pre>{{ __('messages.gallery_managed_by', ['schedule' => $galleryRole->translatedName()]) }}</p>
        @endif
    @else
        @if ($galleryMode === 'downgraded')
            @if ($galleryCanUpgrade)
                <x-plan-gate
                    variant="banner"
                    tier="pro"
                    class="mb-4"
                    :role="$galleryRole"
                    :subdomain="$galleryRole->subdomain"
                    :learnMoreUrl="$galleryLearnMore"
                    :title="__('messages.gallery_hidden_title')">
                    {{ trans_choice('messages.gallery_hidden_body', $galleryImageCount, ['count' => $galleryImageCount]) }}
                </x-plan-gate>
            @else
                <p class="mb-4 text-sm text-gray-600 dark:text-gray-300" v-pre>{{ __('messages.gallery_managed_by', ['schedule' => $galleryRole->translatedName()]) }}</p>
            @endif
        @elseif ($galleryMode === 'readonly')
            <p class="mb-4 text-sm text-gray-600 dark:text-gray-300">{{ __('messages.demo_mode_restriction') }}</p>
        @endif

        @if ($galleryIsEvent)
            <gallery-editor :store="galleryStore" mode="{{ $galleryMode }}" :fan-photos="galleryFanPhotos" :unsaved-label="galleryUnsavedLabel" :recurring-hint="galleryRecurringHint"></gallery-editor>
        @else
            <div id="gallery-editor-app">
                <gallery-editor :store="galleryStore" mode="{{ $galleryMode }}" :unsaved-label="galleryUnsavedLabel"></gallery-editor>
            </div>
        @endif
    @endif
</div>
