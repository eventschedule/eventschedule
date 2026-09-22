{{--
    The small label above a marketing hero headline, rendered INSIDE the <h1>.

    Hero headlines are ad copy ("Paste it once. It is never wrong again."), so on their own they
    carry no search query. The eyebrow above them is where the page names what it is ("Embed an
    event calendar on your website"), and moving it into the heading puts that phrase in the h1
    without rewriting the tagline. config/marketing_keywords.php names the phrase each page must
    carry, and MarketingKeywordMapTest reads it back out of the rendered h1.

    It has to look exactly like the standalone pill or tag it replaced, which is the whole job of
    the two spans:

    - The outer span is a block with `es-hero-eyebrow` (resources/css/marketing.css), which undoes
      what an h1 hands down: the display size, weight, tracking, leading, face, stretch and
      text-wrap: balance. That puts the inner span back in the same context it had as a sibling of
      the h1 (body text, 16px on a 1.5 line), so its line box, baseline and margins land exactly
      where they used to.
    - The inner span is the pill or tag itself: pass the old element's classes unchanged,
      margins included. A tag that used to be a <p> needs `block` added, since a span is inline.

    Because the outer span is a block that sizes to the h1, an inline-flex pill still follows the
    hero's own text-align: centred in a centred hero, at the start edge in a left-aligned one.

    `spacing` puts a margin on the outer span instead, for the rare eyebrow whose gap to the
    headline used to be the h1's own top margin (for-musicians).

    Colour is not reset: a pill's text sets its own. Keep any icon aria-hidden, because the eyebrow
    is now part of the heading's accessible name.
--}}
@props(['spacing' => null])
<span @class(['es-hero-eyebrow', $spacing])><span {{ $attributes }}>{{ $slot }}</span></span>
