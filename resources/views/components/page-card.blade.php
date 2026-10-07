@props([
    'title' => null,
    // One quiet line under the title.
    'lead' => null,
    // A list or a table inside runs to the card's edges; the head keeps its padding.
    'flush' => false,
    // On a wide screen the title, its line and anything in `aside` stand on one side and what
    // the card holds on the other (.page-card.is-beside). A page sets it on all of its titled
    // cards that are not lists, or on none; a card with no title never invents one to split.
    'beside' => false,
])

{{-- A card on a page of the admin portal (partials/admin-page-styles, .page-card): what it holds,
     one line about it, then the thing. `aside` sits at the end of the head (a link, a small
     button, a figure); `foot` is a quiet last line. The title is an h2: the page's own name is the
     h1 of <x-page-header>. The title and the lead carry v-pre, as the header's do: a card may sit
     inside a Vue mount, where a name printed as text would be compiled as a template. --}}
<section {{ $attributes->merge(['class' => 'ap-card rounded-xl page-card'.($flush ? ' is-flush' : '').($beside && $title ? ' is-beside' : '')]) }}>
    @if ($title || isset($aside))
    <div class="page-card-head">
        <div class="min-w-0">
            @if ($title)
            <h2 class="page-card-title" v-pre>{{ $title }}</h2>
            @endif
            @if ($lead)
            <p class="page-card-lead" v-pre>{{ $lead }}</p>
            @endif
        </div>
        @isset($aside)
        <div class="page-actions">{{ $aside }}</div>
        @endisset
    </div>
    @endif
    {{ $slot }}
    @isset($foot)
    <div class="page-card-foot">{{ $foot }}</div>
    @endisset
</section>
