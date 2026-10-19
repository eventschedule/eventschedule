{{-- Cinematic poster card for the homepage events rail. Mirrors the logic of
     event-card.blade.php (guest URL guard; an admin's discovery pills are put in by the page)
     with a tall poster layout: the event image fills the card and a bottom scrim
     carries the details. The picture and its dark scrim are the whole card, so the
     styling is fixed (no dark: variants) although the band around it follows the theme. --}}
@php
    $eventUrl = $event->getGuestUrl();
    $cardRole = $event->getViewableRole();
    // The card renders at 320x427 on desktop and 72vw on phones, so it wants up to ~640 device
    // pixels at 2x - hence the srcset. src stays on the 480 (or the original, when there are no
    // derivatives at all) so nothing regresses for a row the pipeline has not reached.
    $imageUrl = $event->getImageUrl(480);
    $imageSrcset = $event->imageSrcset();
@endphp
@if($eventUrl)
<figure class="es-shot group relative w-[72vw] shrink-0 overflow-hidden rounded-2xl bg-gray-100 shadow-xl shadow-gray-900/10 ring-1 ring-gray-200 sm:w-[320px]" data-tilt="3" data-discovery-event="{{ $event->hashedId() }}">
    <div class="relative aspect-[3/4]">
        @if($imageUrl)
            <img src="{{ $imageUrl }}"@if($imageSrcset) srcset="{{ $imageSrcset }}" sizes="(min-width: 640px) 320px, 72vw"@endif alt="{{ $event->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" width="320" height="427" loading="lazy" decoding="async">
        @else
            <div class="flex h-full w-full select-none items-center justify-center bg-gradient-to-br from-[#4E81FA] to-sky-500 text-7xl font-black text-white">
                {{ strtoupper(mb_substr($event->name, 0, 1)) }}
            </div>
        @endif

        {{-- Scrim with event details --}}
        <div class="absolute inset-x-0 bottom-0 bg-gradient-to-t from-black/85 via-black/45 to-transparent px-4 pb-4 pt-20">
            <div class="mb-2 inline-flex items-center gap-1.5 rounded-full bg-white/15 px-2.5 py-1 text-xs font-semibold text-white backdrop-blur-sm">
                @if($event->starts_at)
                    <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="truncate">{{ $event->getShortDateRangeDisplay('M j, Y g:ia') }}</span>
                @elseif($event->days_of_week)
                    <svg aria-hidden="true" class="h-3.5 w-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>{{ __('messages.recurring') }}</span>
                @endif
            </div>
            <h3 class="mb-1 line-clamp-2 text-lg font-bold leading-snug text-white">{{ $event->name }}</h3>
            @if($cardRole)
                <p class="truncate text-sm text-white/75">{{ $cardRole->name }}@if($cardRole->city) <span class="text-white/50">&middot; {{ $cardRole->city }}</span>@endif</p>
            @endif
        </div>
    </div>

    {{-- Full-card clickable overlay (stretched link) --}}
    <a href="{{ $eventUrl }}" target="_blank" rel="noopener" class="absolute inset-0 z-10 rounded-2xl focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-[#4E81FA] focus-visible:ring-offset-2" aria-label="{{ $event->name }}"></a>

    {{-- A platform admin's pills and Hidden badge go here, put in by the page's script from
         MarketingController::discoveryControls(): the page is the edge's anonymous copy, so
         nothing drawn for an admin here would reach one (docs/CACHING.md). --}}
</figure>
@endif
