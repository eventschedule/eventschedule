@props([
    'title',
    // One line on what the page is for.
    'lead' => null,
    // The page this one hangs from: its address and its NAME. The link reads "Newsletters", never
    // "Back": on a phone the menu is closed, and the name is the only thing saying where it leads.
    'back' => null,
    'backLabel' => null,
    // A class for the way back, where a page's script must hear it (leaving unsaved work).
    'backClass' => null,
    // A quiet line over the title, for a page with nowhere to go back to.
    'eyebrow' => null,
    // A schedule's picture, for a page that is about one schedule.
    'image' => null,
])

{{-- How every page of the admin portal opens (partials/admin-page-styles, "Every other page of the
     portal"): the way back, the page's name, one line on what it is for, and at the end of the row
     what can be done here (the `actions` slot, the button that goes on last).

     The title, the way back and the lead carry v-pre and <bdi>: they are often somebody's own text
     (a schedule's name, an event's), a page may be one Vue mount, and Vue compiles a text node it
     finds there. A title that must be live is not one for this component. --}}
<header {{ $attributes->merge(['class' => 'page-top']) }}>
    <div class="page-top-text{{ $image ? ' has-figure' : '' }}">
        @if ($image)
        <img src="{{ $image }}" alt="" class="page-top-figure">
        @endif
        <div>
            @if ($back)
            <a href="{{ $back }}" class="page-back{{ $backClass ? ' '.$backClass : '' }}">
                <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
                <span v-pre><bdi>{{ $backLabel ?? __('messages.back') }}</bdi></span>
            </a>
            @elseif ($eyebrow)
            <p class="event-eyebrow" v-pre><bdi>{{ $eyebrow }}</bdi></p>
            @endif
            <h1 class="page-title" v-pre><bdi>{{ $title }}</bdi></h1>
            @if ($lead)
            <p class="page-lead" v-pre>{{ $lead }}</p>
            @endif
            {{-- Where a page is about one thing that has a state (a feed: its health, its counts,
                 when it was last read), that line stands under the title in place of a lead. --}}
            @isset($status)
            {{ $status }}
            @endisset
        </div>
    </div>
    @if (isset($actions) && trim((string) $actions) !== '')
    <div class="page-actions">{{ $actions }}</div>
    @endif
</header>
