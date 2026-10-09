{{--
    One post as a card. $card is the post. $cardLead makes it the front page's lead, the one card
    with a picture: the header pictures are a stock set of 27 shared by every post, so in a grid
    the same two or three sat side by side. $cardHeading is the heading level the page's outline
    needs here. $cardSection is false where the page already names the section (a section's own
    page, "Keep reading").
    Named $card* so nothing here reads a variable the including view happens to hold.
--}}
@php
    $cardLead = $cardLead ?? false;
    $cardHeading = $cardHeading ?? 'h2';
    $cardSection = $cardSection ?? true;
    $cardPicture = $cardLead && $card->featured_image_url ? webp_path($card->featured_image_url) : null;
@endphp
<a href="{{ route('blog.show', $card->slug) }}" class="blog-card {{ $cardLead ? 'is-lead' : '' }}">
    @if ($cardPicture)
        <div class="blog-card-img">
            <img src="{{ $cardPicture }}" alt="" width="1536" height="768" fetchpriority="high" decoding="async">
        </div>
    @endif
    <div class="blog-card-body">
        @if ($cardLead || $cardSection || $card->categoryKey() === 'by-event')
            <span class="blog-card-kicker">{{ $card->kicker() }}</span>
        @endif
        <{{ $cardHeading }} class="blog-card-title">{{ $card->title }}</{{ $cardHeading }}>
        @if ($card->excerpt)
            <p class="blog-card-text">{{ $card->excerpt }}</p>
        @endif
        @if ($card->published_at)
            <time class="blog-card-meta" datetime="{{ $card->published_at->toDateString() }}">{{ $card->formatted_published_at }}</time>
        @endif
    </div>
</a>
