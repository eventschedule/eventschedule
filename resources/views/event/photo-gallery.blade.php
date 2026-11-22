@php
    // A recurring event's dated gallery names its night in the title, as the layout's description
    // does: each is a page of its own (Event::getCanonicalPhotoGalleryUrl()). A gallery showing no
    // approved photo is noindex - pending ones are the poster's alone - and its og:image is the
    // first photo it does show, never another night's.
    $galleryDate = \App\Utils\GuestSeo::galleryDate($event, $occurrenceDate);
@endphp
<x-app-guest-layout :role="$role" :event="$event" :date="$date" :occurrence-date="$occurrenceDate" :fonts="$fonts" :otherRole="$otherRole" :galleryMode="true" :showMobileBackground="true" :no-index="$allPhotos->isEmpty()" :gallery-image="$allPhotos->first()?->photo_url" :page-title="$event->translatedName() . ' - ' . __('messages.photo_gallery') . ($galleryDate ? ' - ' . $galleryDate : '')">

<x-slot name="head">
    <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
</x-slot>

  <main>
    @php
    $accentColor = (isset($otherRole) && $otherRole && $otherRole->isClaimed())
        ? ($otherRole->accent_color ?? '#4E81FA')
        : ($role->accent_color ?? '#4E81FA');
    $contrastColor = accent_contrast_color($accentColor);
    // The shared viewer's items (partials/lightbox), in grid order.
    $allPhotoData = $allPhotos->map(fn($p) => [
        'src' => $p->photo_url,
        'thumb' => $p->photo_url,
        'meta' => $p->submitterName().' · '.$p->created_at->format('M j, Y g:ia'),
    ])->values();
    $canAddPhoto = ! $photoLimitReached && $event->isFanPhotosEnabled() && ! is_demo_role($role);
    @endphp

  {{-- The gallery on the stage (partials/guest-stage-styles): the event's way back, a large
       title, then the photos straight on the sheet. The upload form is the one card. Every colour
       is a stage token or the accent the guest layout checked against the stage, light and dark. --}}
  <style {!! nonce_attr() !!}>
    .gk-p-gallery { display: flex; flex-direction: column; gap: 2rem; }
    .gk-p-gal-head { display: flex; flex-wrap: wrap; align-items: flex-end; justify-content: space-between; gap: 1.25rem 2rem; }
    .gk-p-gal-words { display: flex; flex-direction: column; gap: .75rem; min-width: 0; }
    .gk-p-gallery .gk-title { font-size: clamp(2.25rem, 1.5rem + 2.8vw, 3.75rem); }
    .gk-p-gal-count { margin: 0; font-size: 1.0625rem; color: var(--gk-ink-2); }
    .gk-p-back { display: inline-flex; align-items: center; gap: .375rem; align-self: flex-start; max-width: 100%; font-size: .9375rem; font-weight: 500; color: var(--gk-ink-3); text-decoration: none; }
    .gk-p-back:hover { color: var(--gk-ink); text-decoration: underline; text-underline-offset: 3px; }
    .gk-p-back > span { min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    [dir="rtl"] .gk-p-back svg, .gk-p-gallery.rtl .gk-p-back svg { transform: scaleX(-1); }
    .gk-p-gal-acts { display: flex; flex-wrap: wrap; align-items: center; gap: .625rem; }
    .gk-p-gal-acts .gk-btn { text-decoration: none; }
    .gk-p-gal-wide { display: none; }
    @media (min-width: 40rem) { .gk-p-gal-wide { display: inline-flex; } }
    .gk-p-gal-ok { color: var(--gk-ok); }

    {{-- The upload form: a card to fill in. --}}
    .gk-p-drop { border: 2px dashed var(--gk-line); border-radius: 1rem; background: var(--gk-stage); cursor: pointer; transition: border-color var(--gk-swap); }
    .gk-p-drop:hover { border-color: color-mix(in srgb, var(--gk-ink) 35%, transparent); }
    .gk-p-drop-on { border-color: var(--es-accent-readable); }
    .gk-p-drop-empty { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: .5rem; padding: 2.5rem 1rem; font-size: .9375rem; color: var(--gk-ink-2); }
    .gk-p-drop-empty svg { width: 2.5rem; height: 2.5rem; color: var(--gk-ink-3); }
    .gk-p-drop-pick { position: relative; padding: .5rem; }
    .gk-p-drop-pick img { display: block; max-height: 12rem; margin-inline: auto; border-radius: .75rem; object-fit: cover; }
    .gk-p-drop-clear { position: absolute; top: .75rem; inset-inline-end: .75rem; display: grid; place-items: center; width: 1.75rem; height: 1.75rem; border: 0; border-radius: 999px; background: rgb(0 0 0 / .6); color: #fff; font-size: 1rem; line-height: 1; cursor: pointer; }
    .gk-p-upload { display: flex; flex-direction: column; gap: .875rem; }
    .gk-p-upload .gk-btn-lg { align-self: flex-start; }
    .gk-p-camera { width: 100%; border: 1px solid color-mix(in srgb, var(--es-accent-readable) 50%, transparent); background: transparent; color: var(--es-accent-readable); }
    @media (min-width: 40rem) { .gk-p-camera { display: none; } }
    .gk-p-upload input[type="text"], .gk-p-upload input[type="email"] { min-height: 3.125rem; border: 1px solid var(--gk-line); border-radius: .75rem; background: var(--gk-stage); color: var(--gk-ink); font-size: 1rem; }
    .gk-p-upload input::placeholder { color: var(--gk-ink-3); }

    {{-- The photos, straight on the sheet. --}}
    .gk-p-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(min(100%, 10.5rem), 1fr)); gap: .75rem; }
    @media (max-width: 39.99rem) { .gk-p-grid { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: .5rem; } }
    .gk-p-photo { position: relative; display: block; overflow: hidden; aspect-ratio: 1; border-radius: 1rem; background: var(--gk-stage-well); }
    .gk-p-photo img { display: block; width: 100%; height: 100%; object-fit: cover; transition: transform 300ms ease; }
    .gk-p-photo:hover img { transform: scale(1.04); }
    .gk-p-photo:focus-visible { outline: 2px solid var(--es-accent-readable); outline-offset: 2px; }
    .gk-p-photo-who { position: absolute; inset: auto 0 0; padding: 1.5rem .75rem .625rem; background: linear-gradient(to top, rgb(0 0 0 / .65), transparent); color: #fff; font-size: .875rem; font-weight: 600; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; opacity: 0; transition: opacity var(--gk-swap); }
    .gk-p-photo:hover .gk-p-photo-who, .gk-p-photo:focus-visible .gk-p-photo-who { opacity: 1; }
    .gk-p-pending .gk-p-photo img { opacity: .6; }
    .gk-p-pending .gk-chip { position: absolute; top: .5rem; inset-inline-end: .5rem; }
    .gk-p-gal-empty { display: flex; flex-direction: column; align-items: center; gap: .75rem; padding: 4rem 1rem; text-align: center; }
    .gk-p-gal-empty > svg { width: 3.5rem; height: 3.5rem; color: var(--gk-ink-3); }
    .gk-p-gal-empty h2 { margin: 0; font-size: 1.5rem; font-weight: 750; color: var(--gk-ink); }
    .gk-p-gal-empty p { margin: 0 0 .75rem; max-width: 24rem; color: var(--gk-ink-2); }

    {{-- The phone's bar: on the background below the sheet, in the sheet's raised colour. --}}
    .gk-p-gal-bar { position: fixed; inset: auto 0 0; z-index: 40; display: flex; align-items: center; gap: .75rem; padding: .75rem 1.125rem max(.75rem, env(safe-area-inset-bottom)); border-top: 1px solid var(--gk-line); background: var(--gk-stage-raised); color: var(--gk-ink); }
    @media (min-width: 40rem) { .gk-p-gal-bar { display: none; } }
    .gk-p-gal-bar-share { display: grid; flex: none; place-items: center; width: 3.25rem; height: 3.25rem; border: 1px solid var(--gk-line); border-radius: .875rem; background: transparent; color: inherit; cursor: pointer; }
    .gk-p-gal-bar .gk-btn { flex: 1 1 auto; }
    {{-- Room under the page for the bar, so the foot of the page is not behind it. --}}
    @media (max-width: 39.99rem) { body { padding-bottom: 5.5rem; } }
  </style>

  <div class="gk-stage gk-p-gallery {{ $role->isRtl() ? 'rtl' : '' }}">
    {{-- A small Vue app (the share button, the upload form), started below. Anything somebody else
         typed (the event's name, the schedule's own labels, who sent a photo) carries v-pre: the
         app compiles this markup as a template. --}}
    <div id="gallery-app" data-vue-root style="display: contents">

    {{-- Header --}}
    <header class="gk-p-gal-head">
      <div class="gk-p-gal-words">
        {{-- The page the visitor came from: the occurrence the URL asked for, or the series page,
             never the next occurrence the undated gallery fills in. `?: false`: with no date,
             getGuestUrl() would fall back to the series' first date. --}}
        <a href="{{ $event->getGuestUrl($subdomain, $occurrenceDate ?: false) }}" class="gk-p-back">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 flex-none" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
          <span v-pre dir="auto">{{ $event->translatedName() }}</span>
        </a>
        @if ($galleryDate)
        <p class="gk-dateline">{{ $galleryDate }}</p>
        @endif
        <h1 class="gk-title" v-pre>{{ $role->customLabel('photo_gallery') }}</h1>
        <p class="gk-p-gal-count">{{ trans_choice('messages.photo_count', $allPhotos->count(), ['count' => $allPhotos->count()]) }}</p>
      </div>
      <div class="gk-p-gal-acts">
        {{-- Share (the phone has its own, in the bar at the foot) --}}
        <button type="button" @click="share"
                data-share-title="{{ $event->translatedName() }} - {{ $role->customLabel('photo_gallery') }}"
                class="gk-btn gk-btn-secondary gk-btn-lg gk-p-gal-wide">
          <svg v-if="shareState !== 'copied'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
          <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 gk-p-gal-ok" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
          <template v-if="shareState === 'copied'"><span>{{ __('messages.copied') }}</span></template>
          <template v-else><span v-pre>{{ $role->customLabel('share') }}</span></template>
        </button>
        @if ($role->isPro() && $allPhotos->count() > 0)
        {{-- Download all (Pro only) --}}
        <a href="{{ route('event.download_photos', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}"
           class="gk-btn gk-btn-secondary gk-btn-lg">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
          {{ __('messages.download_all_photos') }}
        </a>
        @endif
        @if ($canAddPhoto)
        {{-- Add photo: the page's one primary action, last. --}}
        <button type="button" @click="toggleUpload" :aria-expanded="store.showUpload ? 'true' : 'false'" aria-controls="gallery-upload"
                class="gk-btn gk-btn-primary gk-btn-lg gk-p-gal-wide">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
          {{ __('messages.add_photo') }}
        </button>
        @endif
      </div>
    </header>

    {{-- Upload form (collapsible) --}}
    @if ($canAddPhoto)
    <div id="gallery-upload" v-show="store.showUpload" style="display: none" class="gk-card gk-band">
      <form method="POST" action="{{ route('event.submit_photo', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" enctype="multipart/form-data" class="gk-p-upload">
        @csrf
        <input type="hidden" name="return_to" value="gallery">
        @if ($event->days_of_week && $date)
        <input type="hidden" name="event_date" value="{{ $date }}">
        @endif
        <div @click="$refs.photoInput.click()"
             @dragover.prevent="dragging = true"
             @dragenter.prevent="dragging = true"
             @dragleave.prevent="dragging = false"
             @drop.prevent="onDrop"
             :class="['gk-p-drop', dragging ? 'gk-p-drop-on' : '']">
          <div v-if="!photoPreview" class="gk-p-drop-empty">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
            <span class="hidden sm:inline">{{ __('messages.drag_photo_or_click') }}</span>
            <span class="sm:hidden">{{ __('messages.choose_from_library') }}</span>
          </div>
          <div v-else class="gk-p-drop-pick">
            <img :src="photoPreview" alt="">
            <button type="button" @click.stop="clearPhoto" class="gk-p-drop-clear" aria-label="{{ __('messages.remove') }}">&times;</button>
          </div>
        </div>
        <button type="button" v-if="!photoPreview" @click="$refs.cameraInput.click()" class="gk-btn gk-btn-lg gk-p-camera">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
          {{ __('messages.take_photo') }}
        </button>
        <input ref="cameraInput" type="file" accept="image/*" capture="environment" class="hidden" @change="onCamera">
        <input ref="photoInput" type="file" name="photo" accept="image/*" class="hidden" @change="onPhoto">
        @include('partials.fan-content-guest-fields')
        <button v-show="photoPreview" style="display: none" type="submit" class="gk-btn gk-btn-primary gk-btn-lg">{{ __('messages.upload_photo') }}</button>
      </form>
    </div>
    @endif

    {{-- Pending photos: the poster's own, not yet approved. --}}
    @if ($myPendingPhotos->count() > 0)
    <div class="gk-p-grid gk-p-pending" v-pre>
      @foreach ($myPendingPhotos as $photo)
      <div id="pending-photo-{{ $photo->id }}" class="gk-p-photo">
        <img src="{{ $photo->photo_url }}" alt="{{ $event->translatedName() }}" loading="lazy">
        <span class="gk-chip gk-chip-few">{{ __('messages.pending_approval') }}</span>
      </div>
      @endforeach
    </div>
    @endif

    {{-- Photo grid or empty state --}}
    @if ($allPhotos->count() > 0)
    <div class="gk-p-grid" v-pre>
      @foreach ($allPhotos as $photo)
      <a href="{{ $photo->photo_url }}" data-lightbox-set="fan" data-lightbox-index="{{ $loop->index }}" class="gk-p-photo">
        <img src="{{ $photo->photo_url }}" alt="{{ $event->translatedName() }}" loading="lazy">
        <span class="gk-p-photo-who" dir="auto">{{ $photo->user?->first_name ?? $photo->user?->name ?? __('messages.user') }}</span>
      </a>
      @endforeach
    </div>
    @else
    {{-- Empty state --}}
    <div class="gk-p-gal-empty">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
      <h2>{{ __('messages.no_photos_yet') }}</h2>
      <p>{{ __('messages.be_first_to_share') }}</p>
      @if ($canAddPhoto)
      <button type="button" @click="toggleUpload" aria-controls="gallery-upload" class="gk-btn gk-btn-primary gk-btn-lg">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        {{ __('messages.add_photo') }}
      </button>
      @endif
    </div>
    @endif

    </div>{{-- #gallery-app --}}

    {{-- Outside the app on purpose: it carries its own script, which a Vue template drops, and
         Turnstile only auto-renders elements that are in the DOM and visible at script load, so a
         widget inside the folded upload card would stay unrendered and every guest upload would
         fail with an empty token. Its token is copied into the form on submit. --}}
    @if ($canAddPhoto)
      @include('partials.fan-content-turnstile')
    @endif
  </div>

  {{-- The photos in the shared viewer (partials/lightbox, included at the end of the page). --}}
  @if ($allPhotoData->count() > 0)
  <script {!! nonce_attr() !!}>(window.EsLightboxSets = window.EsLightboxSets || {}).fan = @json($allPhotoData, JSON_UNESCAPED_UNICODE | JSON_HEX_TAG);</script>
  @endif

  </main>

  {{-- Sticky mobile bottom bar: an app of its own, sharing the upload form's open state. --}}
  <div id="gallery-bar" data-vue-root class="gk-p-gal-bar">
    <button type="button" @click="share"
            data-share-title="{{ $event->translatedName() }} - {{ $role->customLabel('photo_gallery') }}"
            aria-label="{{ $role->customLabel('share') }}"
            class="gk-p-gal-bar-share">
      <svg v-if="shareState !== 'copied'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
      <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 gk-p-gal-ok" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
    </button>
    @if ($canAddPhoto)
    <button type="button" @click="openUpload" aria-controls="gallery-upload" class="gk-btn gk-btn-primary gk-btn-lg">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
      {{ __('messages.add_photo') }}
    </button>
    @endif
  </div>

<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function () {
    if (typeof Vue === 'undefined') return;

    // The upload form's open state, shared by the page's app and the phone's bar.
    var store = Vue.reactive({ showUpload: false });

    // Shares the page, or copies its address where the browser cannot share.
    function share(vm, e) {
        if (vm.shareState !== 'idle') return;
        var title = e.currentTarget.dataset.shareTitle;
        if (navigator.share) {
            vm.shareState = 'sharing';
            navigator.share({ title: title, url: window.location.href }).catch(function () {}).finally(function () { vm.shareState = 'idle'; });
        } else {
            if (navigator.clipboard) navigator.clipboard.writeText(window.location.href);
            vm.shareState = 'copied';
            setTimeout(function () { vm.shareState = 'idle'; }, 2000);
        }
    }

    function preview(vm, file) {
        var reader = new FileReader();
        reader.onload = function (ev) { vm.photoPreview = ev.target.result; };
        reader.readAsDataURL(file);
    }

    if (document.getElementById('gallery-app')) {
        Vue.createApp({
            data: function () { return { store: store, shareState: 'idle', dragging: false, photoPreview: null }; },
            methods: {
                share: function (e) { share(this, e); },
                toggleUpload: function () { store.showUpload = ! store.showUpload; },
                // The file the visitor dropped goes on the real input, so the form posts it.
                onDrop: function (e) {
                    this.dragging = false;
                    var f = e.dataTransfer && e.dataTransfer.files[0];
                    if (! f || ! f.type.startsWith('image/')) return;
                    var dt = new DataTransfer();
                    dt.items.add(f);
                    this.$refs.photoInput.files = dt.files;
                    preview(this, f);
                },
                onCamera: function (e) {
                    var f = e.target.files[0];
                    if (! f) return;
                    var dt = new DataTransfer();
                    dt.items.add(f);
                    this.$refs.photoInput.files = dt.files;
                    preview(this, f);
                },
                onPhoto: function (e) {
                    if (e.target.files[0]) preview(this, e.target.files[0]);
                },
                clearPhoto: function () {
                    this.photoPreview = null;
                    this.$refs.photoInput.value = '';
                    if (this.$refs.cameraInput) this.$refs.cameraInput.value = '';
                },
            },
        }).mount('#gallery-app[data-vue-root]');
    }

    if (document.getElementById('gallery-bar')) {
        Vue.createApp({
            data: function () { return { shareState: 'idle' }; },
            methods: {
                share: function (e) { share(this, e); },
                openUpload: function () {
                    store.showUpload = ! store.showUpload;
                    var reduce = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    window.scrollTo({ top: 0, behavior: reduce ? 'auto' : 'smooth' });
                },
            },
        }).mount('#gallery-bar[data-vue-root]');
    }
});
</script>

@if ($allPhotoData->count() > 0)
    @include('partials.lightbox', ['rtl' => $role->isRtl()])
@endif

</x-app-guest-layout>
