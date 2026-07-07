{{-- Cinematic poster card for the homepage events rail. Mirrors the logic of
     event-card.blade.php (guest URL guard, hidden state, admin discovery toggle)
     with a tall poster layout: the event image fills the card and a bottom scrim
     carries the details. Rendered inside the always-light band, so styling is
     fixed (no dark: variants). --}}
@php
    $eventUrl = $event->getGuestUrl();
    $cardRole = $event->getViewableRole();
    $imageUrl = $event->getImageUrl();
    $isHidden = $event->is_hidden_from_discovery;
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp
@if($eventUrl)
<figure class="es-shot group relative w-[72vw] shrink-0 overflow-hidden rounded-2xl bg-gray-100 shadow-xl shadow-gray-900/10 ring-1 ring-gray-200 sm:w-[320px] {{ $isHidden ? 'opacity-60' : '' }}" data-tilt="3">
    <div class="relative aspect-[3/4]">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $event->name }}" class="h-full w-full object-cover transition-transform duration-500 group-hover:scale-105" width="320" height="427" loading="lazy" decoding="async">
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

    {{-- Hidden badge (admin-only context) --}}
    @if($isHidden)
        <span class="absolute top-2 z-20 rounded-full bg-amber-500/90 px-2 py-0.5 text-xs font-semibold text-white shadow-sm ltr:left-2 rtl:right-2">Hidden</span>
    @endif

    {{-- Admin discovery toggle, above the overlay --}}
    @if($isAdmin)
        <form method="POST" action="{{ route('marketing.discovery.toggle', $event->hashedId()) }}" class="absolute top-2 z-20 ltr:right-2 rtl:left-2">
            @csrf
            <button type="submit"
                aria-label="{{ $isHidden ? 'Restore event to discovery' : 'Hide event from discovery' }}"
                class="inline-flex items-center gap-1 rounded-full px-2.5 py-1 text-xs font-semibold shadow-sm backdrop-blur-md transition-all {{ $isHidden ? 'bg-green-600/90 text-white hover:bg-green-600' : 'bg-black/60 text-white hover:bg-black/80' }}">
                @if($isHidden)
                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Unhide
                @else
                    <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                    Hide
                @endif
            </button>
        </form>
    @endif
</figure>
@endif
