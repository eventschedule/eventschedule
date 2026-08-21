{{--
    Head metadata for a guest page that is private and must carry no identity of ours.

    layouts/app.blade.php's default meta block - the @else on its `meta` slot - exists for OUR
    surfaces: it names "Event Schedule" in og:title and og:site_name and offers
    /images/social/home.png as og:image. A ticket, an order, an installment plan and a payment
    interstitial all belong to the schedule that sold them, and ticket links do get forwarded, so
    inheriting that default put our advert and our name in the link preview of somebody else's
    purchase. Those four views set no `meta` slot, which is how they landed on it.

    These pages are noindex either way, so the answer is to keep the robots tag and say nothing
    else. No og:image at all beats an og:image of ours. See GuestSocialImageTest.

    No canonical either, deliberately. The shell's default emitted url()->current(), which on
    ticket.view is /ticket/view/{event_id}/{secret} - so the tag restated the sale secret in the
    markup. Not a leak on its own (the URL is the secret, and the page is noindex), but a canonical
    on a noindex page does nothing at all, so keeping it only preserved the echo.
--}}
<meta name="robots" content="noindex, nofollow">
