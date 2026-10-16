{{-- A tab with nothing to show yet: the card its numbers will fill, drawn quiet (zeros, the grid,
     a flat line), with what would be here and the one thing to do over it. Given emptyTitle,
     emptyText, emptyFigures (the names of the tab's figures), emptyHref and emptyLabel (the last
     two may be null). --}}
<section class="ap-card rounded-xl an-hero an-ghost">
    <div class="an-ghost-figs" aria-hidden="true">
        @foreach ($emptyFigures as $i => $figure)
        <div class="an-fig {{ $i === 0 ? 'is-lead' : '' }}"><p class="an-fig-label">{{ $figure }}</p><p class="an-fig-value">0</p></div>
        @endforeach
    </div>
    <div class="an-ghost-plot" aria-hidden="true"></div>
    <div class="an-ghost-say">
        <h2 v-pre>{{ $emptyTitle }}</h2>
        <p v-pre>{{ $emptyText }}</p>
        @if ($emptyHref)
        <div class="an-ghost-act">
            <x-secondary-link :href="$emptyHref" target="_blank">{{ $emptyLabel }}</x-secondary-link>
        </div>
        @endif
    </div>
</section>
