@php
    $eventUrl = $event->getGuestUrl();
    $cardRole = $event->getViewableRole();
    $imageUrl = $event->getImageUrl();
    $isHidden = $event->is_hidden_from_discovery;
    $isAdmin = auth()->check() && auth()->user()->isAdmin();
@endphp
@if($eventUrl)
<div class="group relative flex flex-col bg-white dark:bg-white/5 rounded-2xl border border-gray-200 dark:border-white/10 hover:shadow-lg hover:border-blue-400/50 dark:hover:border-blue-400/30 transition-all overflow-hidden {{ $isHidden ? 'opacity-60' : '' }}">

    {{-- Image header --}}
    <div class="relative aspect-[16/9] bg-gray-100 dark:bg-white/5 overflow-hidden">
        @if($imageUrl)
            <img src="{{ $imageUrl }}" alt="{{ $event->name }}" class="w-full h-full object-cover transition-transform duration-300 group-hover:scale-105" width="640" height="360" loading="lazy" decoding="async">
        @else
            <div class="w-full h-full bg-gradient-to-br from-[#4E81FA] to-sky-500 flex items-center justify-center text-white font-bold text-4xl select-none">
                {{ strtoupper(mb_substr($event->name, 0, 1)) }}
            </div>
        @endif
    </div>

    {{-- Body --}}
    <div class="p-5 flex flex-col flex-1">
        <h3 class="text-lg font-semibold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors mb-3 line-clamp-2">{{ $event->name }}</h3>
        <div class="mt-auto flex flex-col gap-2">
            @if($event->starts_at)
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg aria-hidden="true" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                    <span class="truncate">{{ $event->getShortDateRangeDisplay('M j, Y g:ia') }}</span>
                </div>
            @elseif($event->days_of_week)
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg aria-hidden="true" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    <span>{{ __('messages.recurring') }}</span>
                </div>
            @endif
            @if($cardRole)
                <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-400">
                    <svg aria-hidden="true" class="w-4 h-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                    </svg>
                    <span class="truncate">{{ $cardRole->name }}@if($cardRole->city) <span class="text-gray-400 dark:text-gray-500">&middot; {{ $cardRole->city }}</span>@endif</span>
                </div>
            @endif
        </div>
    </div>

    {{-- Full-card clickable overlay (stretched link) --}}
    <a href="{{ $eventUrl }}" target="_blank" rel="noopener" class="absolute inset-0 z-10" aria-label="{{ $event->name }}"></a>

    {{-- Hidden badge (admin-only context) --}}
    @if($isHidden)
        <span class="absolute top-2 ltr:left-2 rtl:right-2 z-20 px-2 py-0.5 text-xs font-semibold rounded-full bg-amber-500/90 text-white shadow-sm">Hidden</span>
    @endif

    {{-- Admin discovery toggle, above the overlay --}}
    @if($isAdmin)
        <form method="POST" action="{{ route('marketing.discovery.toggle', $event->hashedId()) }}" class="absolute top-2 ltr:right-2 rtl:left-2 z-20">
            @csrf
            <button type="submit"
                aria-label="{{ $isHidden ? 'Restore event to discovery' : 'Hide event from discovery' }}"
                class="inline-flex items-center gap-1 px-2.5 py-1 text-xs font-semibold rounded-full backdrop-blur-md shadow-sm transition-all {{ $isHidden ? 'bg-green-600/90 text-white hover:bg-green-600' : 'bg-black/60 text-white hover:bg-black/80' }}">
                @if($isHidden)
                    <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    </svg>
                    Unhide
                @else
                    <svg aria-hidden="true" class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    </svg>
                    Hide
                @endif
            </button>
        </form>
    @endif
</div>
@endif
