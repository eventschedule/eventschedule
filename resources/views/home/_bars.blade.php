{{-- A strip of small bars under a tile's figure: one per day, or one per minute for Realtime. They
     run the width of the tile. An empty day keeps a low grey stub, so the strip reads as a row of
     days with nothing in some of them and not as a gap in the data.

     $liveStrip marks the Realtime tile's strip, which home/_live-script redraws. Named so that no
     view including this one has a variable of the same name: an include is handed everything its
     parent holds, and while this read `$live` the tiles' own `$live` (the Realtime summary) marked
     all four strips, so the first refresh redrew Views, Followers and Revenue with minutes. --}}
@php
    $values = $values ?? [];
    $peak = max(1, ...($values ?: [0]));
@endphp
<span class="flex items-end justify-center gap-0.5 h-7 w-full" aria-hidden="true" @if (($liveStrip ?? false) === true) data-live-bars data-tone="{{ $tone }}" @endif>
    @foreach ($values as $value)
        <span class="flex-1 max-w-[0.5rem] rounded-sm {{ $value ? $tone : 'bg-gray-200 dark:bg-gray-700' }}" style="height: {{ $value ? max(14, (int) round($value / $peak * 100)) : 6 }}%"></span>
    @endforeach
</span>
