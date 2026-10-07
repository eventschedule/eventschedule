{{-- The head of a page that hangs from one of the admin's newsletter tabs (the builder, a
     segment, one newsletter's figures): the way back, named, and the page's own name. It is the
     portal's title row (.page-top) with an h2, because on an admin page the h1 belongs to the
     admin navigation above it. These pages had a "Back" button and, three of them, no admin
     navigation.

     Expects $title, $back and $backLabel; $lead is one optional quiet line. --}}
<header class="page-top news-admin">
    <div class="page-top-text">
        <a href="{{ $back }}" class="page-back">
            <svg fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            <span><bdi>{{ $backLabel }}</bdi></span>
        </a>
        <h2 class="page-title"><bdi>{{ $title }}</bdi></h2>
        @if (! empty($lead))
        <p class="page-lead">{{ $lead }}</p>
        @endif
    </div>
</header>
