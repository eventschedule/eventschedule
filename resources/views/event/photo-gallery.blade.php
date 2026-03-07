<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts" :otherRole="$otherRole" :galleryMode="true" :showMobileBackground="true">

  <main>
    @php
    $accentColor = (isset($otherRole) && $otherRole && $otherRole->isClaimed())
        ? ($otherRole->accent_color ?? '#4E81FA')
        : ($role->accent_color ?? '#4E81FA');
    $contrastColor = accent_contrast_color($accentColor);
    $allPhotoData = $allPhotos->map(fn($p) => [
        'url' => $p->photo_url,
        'name' => $p->user?->first_name ?? $p->user?->name ?? __('messages.user'),
        'date' => $p->created_at->format('M j, Y g:ia'),
    ])->values();
    @endphp

  <div class="container mx-auto max-w-5xl px-4 sm:px-5 pt-4 pb-20 sm:pb-8">
    <div class="bg-white/95 dark:bg-gray-900/95 backdrop-blur-sm sm:rounded-2xl p-5 sm:p-6">

    {{-- Header --}}
    <div class="mb-6">
      <a href="{{ $event->getGuestUrl($subdomain, $date) }}" class="inline-flex items-center gap-1.5 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-700 dark:hover:text-gray-300 mb-3 transition-colors">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 {{ $role->isRtl() ? 'rotate-180' : '' }}" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M10.5 19.5 3 12m0 0 7.5-7.5M3 12h18" /></svg>
        {{ $event->translatedName() }}
      </a>
      <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h1 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $role->customLabel('photo_gallery') }}</h1>
          <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ trans_choice('messages.photo_count', $allPhotos->count(), ['count' => $allPhotos->count()]) }}</p>
        </div>
        <div class="flex items-center gap-2">
          {{-- Share button (desktop) --}}
          <div x-data="{ shareState: 'idle' }" class="hidden sm:block">
            <button type="button"
                    data-share-title="{{ $event->translatedName() }} - {{ $role->customLabel('photo_gallery') }}"
                    @click="
                      if (shareState !== 'idle') return;
                      if (navigator.share) {
                        shareState = 'sharing';
                        navigator.share({ title: $event.currentTarget.dataset.shareTitle, url: window.location.href }).finally(() => shareState = 'idle');
                      } else {
                        navigator.clipboard.writeText(window.location.href);
                        shareState = 'copied';
                        setTimeout(() => shareState = 'idle', 2000);
                      }
                    "
                    class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg border border-gray-300 dark:border-white/[0.1] shadow-sm hover:shadow-md hover:bg-gray-100 dark:hover:bg-white/[0.08] transition-all duration-200">
              <template x-if="shareState !== 'copied'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
              </template>
              <template x-if="shareState === 'copied'">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4 text-green-600 dark:text-green-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
              </template>
              <span x-text="shareState === 'copied' ? '{{ __('messages.copied') }}' : '{{ $role->customLabel('share') }}'"></span>
            </button>
          </div>
          @if ($role->isPro() && $allPhotos->count() > 0)
          {{-- Download all (Pro only) --}}
          <a href="{{ route('event.download_photos', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}"
             class="inline-flex items-center gap-1.5 px-3 py-2 text-sm font-medium text-gray-700 dark:text-gray-300 rounded-lg border border-gray-300 dark:border-white/[0.1] shadow-sm hover:shadow-md hover:bg-gray-100 dark:hover:bg-white/[0.08] transition-all duration-200">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75V16.5M16.5 12 12 16.5m0 0L7.5 12m4.5 4.5V3" /></svg>
            {{ __('messages.download_all_photos') }}
          </a>
          @endif
          @if (! $photoLimitReached && $event->isFanPhotosEnabled() && ! is_demo_role($role))
          {{-- Add photo button --}}
          <button x-data @click="$dispatch('toggle-upload')"
                  class="hidden sm:inline-flex items-center gap-1.5 px-4 py-3 text-base font-semibold rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
                  style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
            {{ __('messages.add_photo') }}
          </button>
          @endif
        </div>
      </div>
    </div>

    {{-- Upload form (collapsible) --}}
    @if (! $photoLimitReached && $event->isFanPhotosEnabled() && ! is_demo_role($role))
    <div x-data="{ showUpload: false, dragging: false, photoPreview: null }"
         @toggle-upload.window="showUpload = !showUpload"
         class="mb-6">
      <div x-show="showUpload" x-cloak x-transition class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-4">
        <form method="POST" action="{{ route('event.submit_photo', ['subdomain' => $role->subdomain, 'event_hash' => $event->hashedId()]) }}" enctype="multipart/form-data" class="flex flex-col gap-3">
          @csrf
          <input type="hidden" name="return_to" value="gallery">
          @if ($event->days_of_week && $date)
          <input type="hidden" name="event_date" value="{{ $date }}">
          @endif
          <div @click="$refs.photoInput.click()"
               @dragover.prevent="dragging = true"
               @dragenter.prevent="dragging = true"
               @dragleave.prevent="dragging = false"
               @drop.prevent="dragging = false; if ($event.dataTransfer.files[0] && $event.dataTransfer.files[0].type.startsWith('image/')) { const f = $event.dataTransfer.files[0]; const dt = new DataTransfer(); dt.items.add(f); $refs.photoInput.files = dt.files; const r = new FileReader(); r.onload = e => photoPreview = e.target.result; r.readAsDataURL(f); }"
               class="rounded-lg border-2 border-dashed cursor-pointer transition-colors"
               :class="dragging ? '' : 'border-gray-300 dark:border-gray-600 hover:border-gray-400 dark:hover:border-gray-500'"
               :style="dragging ? 'border-color: {{ $accentColor }}' : ''">
            <div x-show="!photoPreview" class="flex flex-col items-center justify-center py-8 text-gray-500 dark:text-gray-400">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-10 h-10 mb-2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
              <span class="text-sm hidden sm:inline">{{ __('messages.drag_photo_or_click') }}</span>
              <span class="text-sm sm:hidden">{{ __('messages.choose_from_library') }}</span>
            </div>
            <div x-show="photoPreview" class="relative p-2">
              <img :src="photoPreview" class="rounded-lg max-h-48 mx-auto object-cover">
              <button type="button" @click.stop="photoPreview = null; $refs.photoInput.value = ''; $refs.cameraInput.value = ''" class="absolute top-3 {{ $role->isRtl() ? 'left-3' : 'right-3' }} bg-black/60 hover:bg-black/80 text-white rounded-full w-6 h-6 flex items-center justify-center text-sm leading-none">&times;</button>
            </div>
          </div>
          <button type="button" x-show="!photoPreview" @click="$refs.cameraInput.click()"
                  class="sm:hidden w-full inline-flex items-center justify-center gap-2 px-4 py-3 text-sm font-medium rounded-lg border-2 transition-colors"
                  style="border-color: {{ $accentColor }}; color: {{ $accentColor }}">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
            {{ __('messages.take_photo') }}
          </button>
          <input x-ref="cameraInput" type="file" accept="image/*" capture="environment" class="hidden"
                 @change="if ($event.target.files[0]) { const f = $event.target.files[0]; const dt = new DataTransfer(); dt.items.add(f); $refs.photoInput.files = dt.files; const r = new FileReader(); r.onload = e => photoPreview = e.target.result; r.readAsDataURL(f); }">
          <input x-ref="photoInput" type="file" name="photo" accept="image/*" class="hidden" @change="if ($event.target.files[0]) { const r = new FileReader(); r.onload = e => photoPreview = e.target.result; r.readAsDataURL($event.target.files[0]); }">
          <button x-show="photoPreview" type="submit" class="self-start font-semibold text-base px-4 py-3 rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md" style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">{{ __('messages.upload_photo') }}</button>
        </form>
      </div>
    </div>
    @endif

    </div>

    {{-- Pending photos --}}
    @if ($myPendingPhotos->count() > 0)
    <div class="mt-6 mb-6">
      <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3 opacity-60">
        @foreach ($myPendingPhotos as $photo)
        <div id="pending-photo-{{ $photo->id }}" class="relative aspect-square rounded-lg overflow-hidden">
          <img src="{{ $photo->photo_url }}" alt="{{ $event->translatedName() }}" class="w-full h-full object-cover" loading="lazy">
          <span class="absolute top-2 {{ $role->isRtl() ? 'left-2' : 'right-2' }} inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/50 dark:text-amber-300">{{ __('messages.pending_approval') }}</span>
        </div>
        @endforeach
      </div>
    </div>
    @endif

    {{-- Photo grid or empty state --}}
    @if ($allPhotos->count() > 0)
    <div class="mt-6 grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-5 gap-3">
      @foreach ($allPhotos as $photo)
      <button x-data @click="$dispatch('open-lightbox', { url: '{{ $photo->photo_url }}' })"
              class="group relative aspect-square rounded-lg overflow-hidden cursor-pointer focus:outline-none focus:ring-2 focus:ring-offset-2 dark:focus:ring-offset-gray-900" style="--tw-ring-color: {{ $accentColor }};">
        <img src="{{ $photo->photo_url }}" alt="{{ $event->translatedName() }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" loading="lazy">
        <div class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-200">
          <div class="absolute bottom-0 inset-x-0 p-3">
            <p class="text-white text-sm font-medium truncate">{{ $photo->user?->first_name ?? $photo->user?->name ?? __('messages.user') }}</p>
          </div>
        </div>
      </button>
      @endforeach
    </div>
    @else
    {{-- Empty state --}}
    <div class="mt-6 flex flex-col items-center justify-center py-20 text-center">
      <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-16 h-16 text-gray-300 dark:text-gray-600 mb-4" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
      <h2 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">{{ __('messages.no_photos_yet') }}</h2>
      <p class="text-gray-500 dark:text-gray-400 mb-6 max-w-sm">{{ __('messages.be_first_to_share') }}</p>
      @if (! $photoLimitReached && $event->isFanPhotosEnabled() && ! is_demo_role($role))
      <button x-data @click="$dispatch('toggle-upload')"
              class="inline-flex items-center gap-1.5 px-4 py-3 text-base font-semibold rounded-lg transition-all duration-200 hover:scale-105 hover:shadow-md"
              style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" /></svg>
        {{ __('messages.add_photo') }}
      </button>
      @endif
    </div>
    @endif

  </div>

  {{-- Shared photo lightbox --}}
  @if ($allPhotoData->count() > 0)
  <div x-data="{
         lbOpen: false,
         lbPhotos: {{ Js::from($allPhotoData) }},
         lbIndex: 0,
         lbTouchStartX: 0,
         lbRtl: {{ $role->isRtl() ? 'true' : 'false' }},
         openAt(url) {
           let idx = this.lbPhotos.findIndex(p => p.url === url);
           this.lbIndex = idx >= 0 ? idx : 0;
           this.lbOpen = true;
           document.body.style.overflow = 'hidden';
         },
         close() {
           this.lbOpen = false;
           document.body.style.overflow = '';
         },
         prev() {
           this.lbIndex = (this.lbIndex - 1 + this.lbPhotos.length) % this.lbPhotos.length;
         },
         next() {
           this.lbIndex = (this.lbIndex + 1) % this.lbPhotos.length;
         }
       }"
       @open-lightbox.window="openAt($event.detail.url)"
       @keydown.escape.window="if (lbOpen) close()"
       @keydown.left.window="if (lbOpen) { lbRtl ? next() : prev() }"
       @keydown.right.window="if (lbOpen) { lbRtl ? prev() : next() }">
    <template x-teleport="body">
      <div x-show="lbOpen" x-cloak
           @click.self="close()"
           class="fixed inset-0 z-[70] flex items-center justify-center bg-black/90"
           style="font-family: sans-serif"
           @touchstart.passive="lbTouchStartX = $event.changedTouches[0].screenX"
           @touchend="
             let dx = $event.changedTouches[0].screenX - lbTouchStartX;
             if (Math.abs(dx) > 50) {
               if (lbRtl) { dx > 0 ? next() : prev(); }
               else { dx > 0 ? prev() : next(); }
             }
           ">
        {{-- Close button --}}
        <button @click="close()" class="absolute top-3 {{ $role->isRtl() ? 'left-3' : 'right-3' }} text-white/80 hover:text-white text-4xl leading-none z-10 w-10 h-10 flex items-center justify-center">&times;</button>
        {{-- Counter --}}
        <div x-show="lbPhotos.length > 1" class="absolute top-4 left-1/2 -translate-x-1/2 text-white/70 text-sm tabular-nums z-10" x-text="(lbIndex + 1) + ' / ' + lbPhotos.length"></div>
        {{-- Prev button (desktop) --}}
        <button x-show="lbPhotos.length > 1" @click.stop="lbRtl ? next() : prev()" class="hidden sm:flex absolute {{ $role->isRtl() ? 'right-3' : 'left-3' }} top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors z-10">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" /></svg>
        </button>
        {{-- Next button (desktop) --}}
        <button x-show="lbPhotos.length > 1" @click.stop="lbRtl ? prev() : next()" class="hidden sm:flex absolute {{ $role->isRtl() ? 'left-3' : 'right-3' }} top-1/2 -translate-y-1/2 w-10 h-10 items-center justify-center rounded-full bg-white/10 hover:bg-white/20 text-white transition-colors z-10">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5" /></svg>
        </button>
        {{-- Image --}}
        <img :src="lbPhotos[lbIndex].url" class="max-w-[96vw] max-h-[90vh] object-contain pointer-events-none" alt="">
        {{-- Caption --}}
        <div class="absolute bottom-0 inset-x-0 flex items-center justify-between px-4 py-3 text-sm text-white/80 z-10" @click.stop>
          <span x-text="lbPhotos[lbIndex].name"></span>
          <span x-text="lbPhotos[lbIndex].date"></span>
        </div>
      </div>
    </template>
  </div>
  @endif

  </main>

  {{-- Sticky mobile bottom bar --}}
  <div x-data="{ shareState: 'idle' }"
       class="sm:hidden fixed bottom-0 inset-x-0 z-40 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 px-5 py-3 shadow-lg" style="font-family: sans-serif; padding-bottom: max(0.75rem, env(safe-area-inset-bottom));">
    <div class="flex items-center gap-3 {{ $role->isRtl() ? 'rtl' : '' }}">
      {{-- Mobile share button --}}
      <button type="button"
              data-share-title="{{ $event->translatedName() }} - {{ $role->customLabel('photo_gallery') }}"
              @click="
                if (shareState !== 'idle') return;
                if (navigator.share) {
                  shareState = 'sharing';
                  navigator.share({ title: $event.currentTarget.dataset.shareTitle, url: window.location.href }).finally(() => shareState = 'idle');
                } else {
                  navigator.clipboard.writeText(window.location.href);
                  shareState = 'copied';
                  setTimeout(() => shareState = 'idle', 2000);
                }
              "
              class="flex-shrink-0 w-[48px] h-[48px] inline-flex items-center justify-center rounded-xl border border-gray-300 dark:border-gray-600 text-gray-600 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors">
        <template x-if="shareState !== 'copied'">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M7.217 10.907a2.25 2.25 0 1 0 0 2.186m0-2.186c.18.324.283.696.283 1.093s-.103.77-.283 1.093m0-2.186 9.566-5.314m-9.566 7.5 9.566 5.314m0 0a2.25 2.25 0 1 0 3.935 2.186 2.25 2.25 0 0 0-3.935-2.186Zm0-12.814a2.25 2.25 0 1 0 3.933-2.185 2.25 2.25 0 0 0-3.933 2.185Z" /></svg>
        </template>
        <template x-if="shareState === 'copied'">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-green-600 dark:text-green-400" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5" /></svg>
        </template>
      </button>
      @if (! $photoLimitReached && $event->isFanPhotosEnabled() && ! is_demo_role($role))
      {{-- Add photo CTA --}}
      <button type="button"
              @click="$dispatch('toggle-upload'); window.scrollTo({ top: 0, behavior: 'smooth' })"
              class="flex-1 justify-center rounded-md px-6 py-3 text-lg font-semibold shadow-sm transition-all duration-200 hover:scale-105 hover:shadow-lg inline-flex items-center gap-2"
              style="background-color: {{ $accentColor }}; color: {{ $contrastColor }};">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M6.827 6.175A2.31 2.31 0 0 1 5.186 7.23c-.38.054-.757.112-1.134.175C2.999 7.58 2.25 8.507 2.25 9.574V18a2.25 2.25 0 0 0 2.25 2.25h15A2.25 2.25 0 0 0 21.75 18V9.574c0-1.067-.75-1.994-1.802-2.169a47.865 47.865 0 0 0-1.134-.175 2.31 2.31 0 0 1-1.64-1.055l-.822-1.316a2.192 2.192 0 0 0-1.736-1.039 48.774 48.774 0 0 0-5.232 0 2.192 2.192 0 0 0-1.736 1.039l-.821 1.316Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M16.5 12.75a4.5 4.5 0 1 1-9 0 4.5 4.5 0 0 1 9 0Z" /></svg>
        {{ __('messages.add_photo') }}
      </button>
      @endif
    </div>
  </div>

</x-app-guest-layout>
