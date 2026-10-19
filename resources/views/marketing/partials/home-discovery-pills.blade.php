{{-- A platform admin's discovery pills for one card of the homepage rail, rendered by
     MarketingController::discoveryControls() and put on the card by the page's script, because
     the page itself is the edge's anonymous copy. The schedule's pill comes first and the event's
     at the end, where the one pill always was; the schedule's is drawn light so the two do not read
     as one control (#hp .hp-pill-schedule). $schedule is null where hiding it would not take this
     card away (a curator's listing). --}}
@if($event->is_hidden_from_discovery || $scheduleHidden)
    <span class="absolute top-2 z-20 rounded-full bg-amber-500/90 px-2 py-0.5 text-xs font-semibold text-white shadow-sm ltr:left-2 rtl:right-2">Hidden</span>
@endif

<div class="absolute top-2 z-20 flex items-center gap-1.5 ltr:right-2 rtl:left-2">
    @if($schedule)
        <form method="POST" action="{{ route('marketing.discovery.schedule', \App\Utils\UrlUtils::encodeId($schedule->id)) }}">
            @csrf
            <input type="hidden" name="hide" value="{{ $scheduleHidden ? 0 : 1 }}">
            <input type="hidden" name="anchor" value="discover">
            <button type="submit"
                title="{{ $scheduleHidden ? 'Restore' : 'Hide' }} {{ $schedule->name }}'s events {{ $scheduleHidden ? 'to' : 'from' }} the homepage, Browse and search"
                aria-label="{{ $scheduleHidden ? 'Restore' : 'Hide' }} {{ $schedule->name }}'s events {{ $scheduleHidden ? 'to' : 'from' }} the homepage, Browse and search"
                class="hp-pill-schedule inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold shadow-sm backdrop-blur-md transition-all">
                <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    @if($scheduleHidden)
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                    @else
                        <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                    @endif
                </svg>
                {{ $scheduleHidden ? 'Unhide schedule' : 'Hide schedule' }}
            </button>
        </form>
    @endif
    <form method="POST" action="{{ route('marketing.discovery.toggle', $event->hashedId()) }}">
        @csrf
        <input type="hidden" name="hide" value="{{ $event->is_hidden_from_discovery ? 0 : 1 }}">
        <input type="hidden" name="anchor" value="discover">
        <button type="submit"
            aria-label="{{ $event->is_hidden_from_discovery ? 'Restore event to discovery' : 'Hide event from discovery' }}"
            class="inline-flex items-center gap-1 whitespace-nowrap rounded-full px-2.5 py-1 text-xs font-semibold shadow-sm backdrop-blur-md transition-all {{ $event->is_hidden_from_discovery ? 'bg-green-600/90 text-white hover:bg-green-600' : 'bg-black/60 text-white hover:bg-black/80' }}">
            @if($event->is_hidden_from_discovery)
                <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                </svg>
                Unhide
            @else
                <svg aria-hidden="true" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l18 18" />
                </svg>
                Hide event
            @endif
        </button>
    </form>
</div>
