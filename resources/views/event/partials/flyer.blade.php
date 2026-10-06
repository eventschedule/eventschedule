{{-- The event form's flyer field. One block, included in one of two places (event/edit): beside
     the name and date, or, on a first event, under the location - where it does not push the
     place off the first screen. Its ids are wired once by the form's script, so it must be
     rendered exactly once. --}}
<div class="event-basics-flyer">
<div class="mb-6">
<x-input-label :value="__('messages.flyer_image')" />
<input id="flyer_image" name="flyer_image" type="file" class="hidden"
        accept="image/png, image/jpeg" />
    <div id="flyer_image_choose" style="{{ ($event->flyer_image_url || ($clonedFlyerImage ?? null)) ? 'display:none' : '' }}">
        <div class="mt-1 event-flyer-pick">
            <button type="button" id="flyer-choose-btn" class="event-flyer-tile">
                <svg fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
                </svg>
                <span class="event-flyer-title">{{ __('messages.choose_file') }}</span>
                <span class="event-flyer-help">{{ __('messages.flyer_drop_hint') }}</span>
            </button>
            <span id="flyer_image_filename" class="text-sm text-gray-500 dark:text-gray-400"></span>
        </div>
        <x-input-error class="mt-2" :messages="$errors->get('flyer_image')" />
        <p id="image_size_warning" class="mt-2 text-sm text-red-600 dark:text-red-400" style="display: none;">
            {{ __('messages.image_size_warning') }}
        </p>
    </div>

    <div id="image_preview" class="mt-3 relative inline-block" style="display: none;">
        <img id="preview_img" src="#" alt="Preview" style="max-height:120px" class="rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" data-lightbox-src />
        <button type="button" id="clear-flyer-preview-btn" style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;" class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg></button>
    </div>

    @php
        $clonedFlyerImage = $clonedFlyerImage ?? null;
        $clonedFlyerImageUrl = $clonedFlyerImageUrl ?? null;
        // For a clone the event isn't saved yet (no id), so the preview shows the
        // source image with an empty hash, routing the delete button into
        // deleteFlyer()'s client-side branch instead of a server call.
        $flyerPreviewUrl = $event->flyer_image_url ?: $clonedFlyerImageUrl;
        $flyerDeleteHash = $event->id ? \App\Utils\UrlUtils::encodeId($event->id) : '';
    @endphp
    @if ($flyerPreviewUrl)
    <div id="flyer_image_existing" class="relative inline-block mt-4 pt-1">
        <img src="{{ $flyerPreviewUrl }}" alt="{{ __('messages.flyer_image') }}" style="max-height:120px" class="rounded-lg border border-gray-200 dark:border-gray-600 cursor-pointer" id="flyer_preview" data-lightbox-src="{{ $flyerPreviewUrl }}" />
        <button type="button"
            id="delete-flyer-btn"
            data-url="{{ route('event.delete_image', ['subdomain' => $subdomain]) }}"
            data-hash="{{ $flyerDeleteHash }}"
            data-token="{{ csrf_token() }}"
            style="width: 20px; height: 20px; min-width: 20px; min-height: 20px;"
            class="absolute -top-2 -right-2 bg-red-500 hover:bg-red-600 text-white rounded-full flex items-center justify-center">
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
            </svg>
        </button>
    </div>
    @endif
    @if ($clonedFlyerImage)
    <input type="hidden" name="clone_flyer_image" id="clone_flyer_image" value="{{ $clonedFlyerImage }}" />
    @endif
</div>
</div>

