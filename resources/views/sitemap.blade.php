<?php
/*
 * The static marketing/docs pages of the sitemap, served as the /sitemap-pages.xml child of the
 * sitemap index by SitemapController. Adding a new page here is still all that is required.
 *
 * Schedules, sub-schedules, events and blog posts are NOT listed here: they are streamed in
 * chunks from SitemapController so memory does not scale with row count.
 *
 * $lastmodTag() renders the whole <lastmod> element, or nothing at all when config/sitemap_lastmod.php
 * has no date for that path - Google prefers an absent lastmod to one it can prove wrong. Rebuild
 * that manifest with `php artisan sitemap:lastmod` in any commit that edits a page. The path here must be
 * byte-identical to the one in the url() call above it, which is how the manifest is keyed.
 *
 * There is deliberately no <changefreq> or <priority>: Google ignores both. There are no hreflang
 * alternates either - marketing pages are English-only and the layout canonicalizes every ?lang=
 * variant onto the clean English URL.
 *
 * Nothing may be emitted before the XML declaration, so this note lives inside the PHP block.
 */
echo '<?xml version="1.0" encoding="UTF-8"?>'; ?>
<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
    <url>
        <loc>{{ url('/') }}</loc>
        {!! $lastmodTag('/') !!}
    </url>
    @if(config('app.is_nexus'))
    <url>
        <loc>{{ url('/features') }}</loc>
        {!! $lastmodTag('/features') !!}
    </url>
    <url>
        <loc>{{ url('/pricing') }}</loc>
        {!! $lastmodTag('/pricing') !!}
    </url>
    <url>
        <loc>{{ url('/about') }}</loc>
        {!! $lastmodTag('/about') !!}
    </url>
    <url>
        <loc>{{ url('/examples') }}</loc>
        {!! $lastmodTag('/examples') !!}
    </url>
    <url>
        <loc>{{ url('/browse') }}</loc>
        {!! $lastmodTag('/browse') !!}
    </url>
    <url>
        <loc>{{ url('/compare') }}</loc>
        {!! $lastmodTag('/compare') !!}
    </url>
    <url>
        <loc>{{ url('/eventbrite-alternative') }}</loc>
        {!! $lastmodTag('/eventbrite-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/luma-alternative') }}</loc>
        {!! $lastmodTag('/luma-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/ticket-tailor-alternative') }}</loc>
        {!! $lastmodTag('/ticket-tailor-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/google-calendar-alternative') }}</loc>
        {!! $lastmodTag('/google-calendar-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/meetup-alternative') }}</loc>
        {!! $lastmodTag('/meetup-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/dice-alternative') }}</loc>
        {!! $lastmodTag('/dice-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/brown-paper-tickets-alternative') }}</loc>
        {!! $lastmodTag('/brown-paper-tickets-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/splash-alternative') }}</loc>
        {!! $lastmodTag('/splash-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/sched-alternative') }}</loc>
        {!! $lastmodTag('/sched-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/whova-alternative') }}</loc>
        {!! $lastmodTag('/whova-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/accelevents-alternative') }}</loc>
        {!! $lastmodTag('/accelevents-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/tito-alternative') }}</loc>
        {!! $lastmodTag('/tito-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/addevent-alternative') }}</loc>
        {!! $lastmodTag('/addevent-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/pretix-alternative') }}</loc>
        {!! $lastmodTag('/pretix-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/humanitix-alternative') }}</loc>
        {!! $lastmodTag('/humanitix-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/eventzilla-alternative') }}</loc>
        {!! $lastmodTag('/eventzilla-alternative') !!}
    </url>
    <url>
        <loc>{{ url('/replace') }}</loc>
        {!! $lastmodTag('/replace') !!}
    </url>
    <url>
        <loc>{{ url('/google-forms-replacement') }}</loc>
        {!! $lastmodTag('/google-forms-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/mailchimp-replacement') }}</loc>
        {!! $lastmodTag('/mailchimp-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/canva-replacement') }}</loc>
        {!! $lastmodTag('/canva-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/linktree-replacement') }}</loc>
        {!! $lastmodTag('/linktree-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/google-sheets-replacement') }}</loc>
        {!! $lastmodTag('/google-sheets-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/calendly-replacement') }}</loc>
        {!! $lastmodTag('/calendly-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/surveymonkey-replacement') }}</loc>
        {!! $lastmodTag('/surveymonkey-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/doodle-replacement') }}</loc>
        {!! $lastmodTag('/doodle-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/qr-code-generator-replacement') }}</loc>
        {!! $lastmodTag('/qr-code-generator-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/squarespace-replacement') }}</loc>
        {!! $lastmodTag('/squarespace-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/notion-replacement') }}</loc>
        {!! $lastmodTag('/notion-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/trello-replacement') }}</loc>
        {!! $lastmodTag('/trello-replacement') !!}
    </url>
    <url>
        <loc>{{ url('/faq') }}</loc>
        {!! $lastmodTag('/faq') !!}
    </url>
    <url>
        <loc>{{ url('/docs/referral-program') }}</loc>
        {!! $lastmodTag('/docs/referral-program') !!}
    </url>
    <url>
        <loc>{{ url('/why-create-account') }}</loc>
        {!! $lastmodTag('/why-create-account') !!}
    </url>
    <url>
        <loc>{{ url('/features/ticketing') }}</loc>
        {!! $lastmodTag('/features/ticketing') !!}
    </url>
    <url>
        <loc>{{ url('/features/integrations') }}</loc>
        {!! $lastmodTag('/features/integrations') !!}
    </url>
    <url>
        <loc>{{ url('/selfhost') }}</loc>
        {!! $lastmodTag('/selfhost') !!}
    </url>
    <url>
        <loc>{{ url('/features/ai') }}</loc>
        {!! $lastmodTag('/features/ai') !!}
    </url>
    <url>
        <loc>{{ url('/features/gift-cards') }}</loc>
        {!! $lastmodTag('/features/gift-cards') !!}
    </url>
    <url>
        <loc>{{ url('/features/allocated-seating') }}</loc>
        {!! $lastmodTag('/features/allocated-seating') !!}
    </url>
    <url>
        <loc>{{ url('/features/calendar-sync') }}</loc>
        {!! $lastmodTag('/features/calendar-sync') !!}
    </url>
    <url>
        <loc>{{ url('/google-calendar') }}</loc>
        {!! $lastmodTag('/google-calendar') !!}
    </url>
    <url>
        <loc>{{ url('/outlook-calendar') }}</loc>
        {!! $lastmodTag('/outlook-calendar') !!}
    </url>
    <url>
        <loc>{{ url('/caldav') }}</loc>
        {!! $lastmodTag('/caldav') !!}
    </url>
    <url>
        <loc>{{ url('/stripe') }}</loc>
        {!! $lastmodTag('/stripe') !!}
    </url>
    <url>
        <loc>{{ url('/invoiceninja') }}</loc>
        {!! $lastmodTag('/invoiceninja') !!}
    </url>
    <url>
        <loc>{{ url('/features/analytics') }}</loc>
        {!! $lastmodTag('/features/analytics') !!}
    </url>
    <url>
        <loc>{{ url('/features/custom-fields') }}</loc>
        {!! $lastmodTag('/features/custom-fields') !!}
    </url>
    <url>
        <loc>{{ url('/features/custom-labels') }}</loc>
        {!! $lastmodTag('/features/custom-labels') !!}
    </url>
    <url>
        <loc>{{ url('/features/team-scheduling') }}</loc>
        {!! $lastmodTag('/features/team-scheduling') !!}
    </url>
    <url>
        <loc>{{ url('/features/sub-schedules') }}</loc>
        {!! $lastmodTag('/features/sub-schedules') !!}
    </url>
    <url>
        <loc>{{ url('/features/online-events') }}</loc>
        {!! $lastmodTag('/features/online-events') !!}
    </url>
    <url>
        <loc>{{ url('/open-source') }}</loc>
        {!! $lastmodTag('/open-source') !!}
    </url>
    <url>
        <loc>{{ url('/features/newsletters') }}</loc>
        {!! $lastmodTag('/features/newsletters') !!}
    </url>
    <url>
        <loc>{{ url('/features/recurring-events') }}</loc>
        {!! $lastmodTag('/features/recurring-events') !!}
    </url>
    <url>
        <loc>{{ url('/features/embed-calendar') }}</loc>
        {!! $lastmodTag('/features/embed-calendar') !!}
    </url>
    <url>
        <loc>{{ url('/features/embed-tickets') }}</loc>
        {!! $lastmodTag('/features/embed-tickets') !!}
    </url>
    <url>
        <loc>{{ url('/features/fan-videos') }}</loc>
        {!! $lastmodTag('/features/fan-videos') !!}
    </url>
    <url>
        <loc>{{ url('/features/polls') }}</loc>
        {!! $lastmodTag('/features/polls') !!}
    </url>
    <url>
        <loc>{{ url('/features/boost') }}</loc>
        {!! $lastmodTag('/features/boost') !!}
    </url>
    <url>
        <loc>{{ url('/features/private-events') }}</loc>
        {!! $lastmodTag('/features/private-events') !!}
    </url>
    <url>
        <loc>{{ url('/features/event-graphics') }}</loc>
        {!! $lastmodTag('/features/event-graphics') !!}
    </url>
    <url>
        <loc>{{ url('/features/white-label') }}</loc>
        {!! $lastmodTag('/features/white-label') !!}
    </url>
    <url>
        <loc>{{ url('/features/custom-css') }}</loc>
        {!! $lastmodTag('/features/custom-css') !!}
    </url>
    <url>
        <loc>{{ url('/features/custom-domain') }}</loc>
        {!! $lastmodTag('/features/custom-domain') !!}
    </url>
    <url>
        <loc>{{ url('/features/feedback') }}</loc>
        {!! $lastmodTag('/features/feedback') !!}
    </url>
    <url>
        <loc>{{ url('/features/availability') }}</loc>
        {!! $lastmodTag('/features/availability') !!}
    </url>
    <url>
        <loc>{{ url('/features/appointments') }}</loc>
        {!! $lastmodTag('/features/appointments') !!}
    </url>
    <url>
        <loc>{{ url('/features/carpool') }}</loc>
        {!! $lastmodTag('/features/carpool') !!}
    </url>
    <url>
        <loc>{{ url('/saas') }}</loc>
        {!! $lastmodTag('/saas') !!}
    </url>
    <url>
        <loc>{{ url('/for-talent') }}</loc>
        {!! $lastmodTag('/for-talent') !!}
    </url>
    <url>
        <loc>{{ url('/for-venues') }}</loc>
        {!! $lastmodTag('/for-venues') !!}
    </url>
    <url>
        <loc>{{ url('/for-curators') }}</loc>
        {!! $lastmodTag('/for-curators') !!}
    </url>
    <url>
        <loc>{{ url('/for-musicians') }}</loc>
        {!! $lastmodTag('/for-musicians') !!}
    </url>
    <url>
        <loc>{{ url('/for-djs') }}</loc>
        {!! $lastmodTag('/for-djs') !!}
    </url>
    <url>
        <loc>{{ url('/for-comedians') }}</loc>
        {!! $lastmodTag('/for-comedians') !!}
    </url>
    <url>
        <loc>{{ url('/for-circus-acrobatics') }}</loc>
        {!! $lastmodTag('/for-circus-acrobatics') !!}
    </url>
    <url>
        <loc>{{ url('/for-magicians') }}</loc>
        {!! $lastmodTag('/for-magicians') !!}
    </url>
    <url>
        <loc>{{ url('/for-spoken-word') }}</loc>
        {!! $lastmodTag('/for-spoken-word') !!}
    </url>
    <url>
        <loc>{{ url('/for-bars') }}</loc>
        {!! $lastmodTag('/for-bars') !!}
    </url>
    <url>
        <loc>{{ url('/for-nightclubs') }}</loc>
        {!! $lastmodTag('/for-nightclubs') !!}
    </url>
    <url>
        <loc>{{ url('/for-music-venues') }}</loc>
        {!! $lastmodTag('/for-music-venues') !!}
    </url>
    <url>
        <loc>{{ url('/for-theaters') }}</loc>
        {!! $lastmodTag('/for-theaters') !!}
    </url>
    <url>
        <loc>{{ url('/for-dance-groups') }}</loc>
        {!! $lastmodTag('/for-dance-groups') !!}
    </url>
    <url>
        <loc>{{ url('/for-theater-performers') }}</loc>
        {!! $lastmodTag('/for-theater-performers') !!}
    </url>
    <url>
        <loc>{{ url('/for-food-trucks-and-vendors') }}</loc>
        {!! $lastmodTag('/for-food-trucks-and-vendors') !!}
    </url>
    <url>
        <loc>{{ url('/for-comedy-clubs') }}</loc>
        {!! $lastmodTag('/for-comedy-clubs') !!}
    </url>
    <url>
        <loc>{{ url('/for-restaurants') }}</loc>
        {!! $lastmodTag('/for-restaurants') !!}
    </url>
    <url>
        <loc>{{ url('/for-breweries-and-wineries') }}</loc>
        {!! $lastmodTag('/for-breweries-and-wineries') !!}
    </url>
    <url>
        <loc>{{ url('/for-art-galleries') }}</loc>
        {!! $lastmodTag('/for-art-galleries') !!}
    </url>
    <url>
        <loc>{{ url('/for-community-centers') }}</loc>
        {!! $lastmodTag('/for-community-centers') !!}
    </url>
    <url>
        <loc>{{ url('/for-fitness-and-yoga') }}</loc>
        {!! $lastmodTag('/for-fitness-and-yoga') !!}
    </url>
    <url>
        <loc>{{ url('/for-workshop-instructors') }}</loc>
        {!! $lastmodTag('/for-workshop-instructors') !!}
    </url>
    <url>
        <loc>{{ url('/for-visual-artists') }}</loc>
        {!! $lastmodTag('/for-visual-artists') !!}
    </url>
    <url>
        <loc>{{ url('/for-farmers-markets') }}</loc>
        {!! $lastmodTag('/for-farmers-markets') !!}
    </url>
    <url>
        <loc>{{ url('/for-hotels-and-resorts') }}</loc>
        {!! $lastmodTag('/for-hotels-and-resorts') !!}
    </url>
    <url>
        <loc>{{ url('/for-libraries') }}</loc>
        {!! $lastmodTag('/for-libraries') !!}
    </url>
    <url>
        <loc>{{ url('/for-webinars') }}</loc>
        {!! $lastmodTag('/for-webinars') !!}
    </url>
    <url>
        <loc>{{ url('/for-live-concerts') }}</loc>
        {!! $lastmodTag('/for-live-concerts') !!}
    </url>
    <url>
        <loc>{{ url('/for-online-classes') }}</loc>
        {!! $lastmodTag('/for-online-classes') !!}
    </url>
    <url>
        <loc>{{ url('/for-virtual-conferences') }}</loc>
        {!! $lastmodTag('/for-virtual-conferences') !!}
    </url>
    <url>
        <loc>{{ url('/for-live-qa-sessions') }}</loc>
        {!! $lastmodTag('/for-live-qa-sessions') !!}
    </url>
    <url>
        <loc>{{ url('/for-watch-parties') }}</loc>
        {!! $lastmodTag('/for-watch-parties') !!}
    </url>
    <url>
        <loc>{{ url('/for-ai-agents') }}</loc>
        {!! $lastmodTag('/for-ai-agents') !!}
    </url>
    <url>
        <loc>{{ url('/use-cases') }}</loc>
        {!! $lastmodTag('/use-cases') !!}
    </url>
    <url>
        <loc>{{ url('/contact') }}</loc>
        {!! $lastmodTag('/contact') !!}
    </url>
    <url>
        <loc>{{ url('/docs') }}</loc>
        {!! $lastmodTag('/docs') !!}
    </url>
    <url>
        <loc>{{ url('/docs/getting-started') }}</loc>
        {!! $lastmodTag('/docs/getting-started') !!}
    </url>
    <url>
        <loc>{{ url('/docs/creating-schedules') }}</loc>
        {!! $lastmodTag('/docs/creating-schedules') !!}
    </url>
<url>
        <loc>{{ url('/docs/schedule-styling') }}</loc>
        {!! $lastmodTag('/docs/schedule-styling') !!}
    </url>
    <url>
        <loc>{{ url('/docs/creating-events') }}</loc>
        {!! $lastmodTag('/docs/creating-events') !!}
    </url>
    <url>
        <loc>{{ url('/docs/sharing') }}</loc>
        {!! $lastmodTag('/docs/sharing') !!}
    </url>
    <url>
        <loc>{{ url('/docs/tickets') }}</loc>
        {!! $lastmodTag('/docs/tickets') !!}
    </url>
    <url>
        <loc>{{ url('/docs/subscriptions') }}</loc>
        {!! $lastmodTag('/docs/subscriptions') !!}
    </url>
    <url>
        <loc>{{ url('/docs/gift-cards') }}</loc>
        {!! $lastmodTag('/docs/gift-cards') !!}
    </url>
    <url>
        <loc>{{ url('/docs/allocated-seating') }}</loc>
        {!! $lastmodTag('/docs/allocated-seating') !!}
    </url>
    <url>
        <loc>{{ url('/docs/appointments') }}</loc>
        {!! $lastmodTag('/docs/appointments') !!}
    </url>
    <url>
        <loc>{{ url('/docs/event-graphics') }}</loc>
        {!! $lastmodTag('/docs/event-graphics') !!}
    </url>
    <url>
        <loc>{{ url('/docs/newsletters') }}</loc>
        {!! $lastmodTag('/docs/newsletters') !!}
    </url>
    <url>
        <loc>{{ url('/docs/analytics') }}</loc>
        {!! $lastmodTag('/docs/analytics') !!}
    </url>
    <url>
        <loc>{{ url('/docs/account-settings') }}</loc>
        {!! $lastmodTag('/docs/account-settings') !!}
    </url>
    <url>
        <loc>{{ url('/docs/managing-schedules') }}</loc>
        {!! $lastmodTag('/docs/managing-schedules') !!}
    </url>
    <url>
        <loc>{{ url('/docs/boost') }}</loc>
        {!! $lastmodTag('/docs/boost') !!}
    </url>
    <url>
        <loc>{{ url('/docs/ai-import') }}</loc>
        {!! $lastmodTag('/docs/ai-import') !!}
    </url>
    <url>
        <loc>{{ url('/docs/scan-agenda') }}</loc>
        {!! $lastmodTag('/docs/scan-agenda') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost') }}</loc>
        {!! $lastmodTag('/docs/selfhost') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/installation') }}</loc>
        {!! $lastmodTag('/docs/selfhost/installation') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/stripe') }}</loc>
        {!! $lastmodTag('/docs/selfhost/stripe') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/google-calendar') }}</loc>
        {!! $lastmodTag('/docs/selfhost/google-calendar') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/microsoft-calendar') }}</loc>
        {!! $lastmodTag('/docs/selfhost/microsoft-calendar') !!}
    </url>
    <url>
        <loc>{{ url('/docs/saas') }}</loc>
        {!! $lastmodTag('/docs/saas') !!}
    </url>
    <url>
        <loc>{{ url('/docs/saas/custom-domains') }}</loc>
        {!! $lastmodTag('/docs/saas/custom-domains') !!}
    </url>
    <url>
        <loc>{{ url('/docs/saas/twilio') }}</loc>
        {!! $lastmodTag('/docs/saas/twilio') !!}
    </url>
    <url>
        <loc>{{ url('/docs/saas/federation') }}</loc>
        {!! $lastmodTag('/docs/saas/federation') !!}
    </url>
    <url>
        <loc>{{ url('/docs/saas/monetization') }}</loc>
        {!! $lastmodTag('/docs/saas/monetization') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/federation') }}</loc>
        {!! $lastmodTag('/docs/selfhost/federation') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/boost') }}</loc>
        {!! $lastmodTag('/docs/selfhost/boost') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/admin') }}</loc>
        {!! $lastmodTag('/docs/selfhost/admin') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/email') }}</loc>
        {!! $lastmodTag('/docs/selfhost/email') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/ai') }}</loc>
        {!! $lastmodTag('/docs/selfhost/ai') !!}
    </url>
    <url>
        <loc>{{ url('/docs/selfhost/accessibility') }}</loc>
        {!! $lastmodTag('/docs/selfhost/accessibility') !!}
    </url>
    <url>
        <loc>{{ url('/docs/developer/api') }}</loc>
        {!! $lastmodTag('/docs/developer/api') !!}
    </url>
    <url>
        <loc>{{ url('/docs/developer/webhooks') }}</loc>
        {!! $lastmodTag('/docs/developer/webhooks') !!}
    </url>
    <url>
        <loc>{{ url('/privacy') }}</loc>
        {!! $lastmodTag('/privacy') !!}
    </url>
    <url>
        <loc>{{ url('/accessibility') }}</loc>
        {!! $lastmodTag('/accessibility') !!}
    </url>
    <url>
        <loc>{{ url('/terms-of-service') }}</loc>
        {!! $lastmodTag('/terms-of-service') !!}
    </url>
    <url>
        <loc>{{ url('/self-hosting-terms-of-service') }}</loc>
        {!! $lastmodTag('/self-hosting-terms-of-service') !!}
    </url>
@php
    // The cookie policy exists only where an operator wrote one - there is no page
    // shipped for it - so it is listed only then, and for the same reason the
    // manifest has no date for it and it renders without a <lastmod>.
    //
    // Content only, never a url-only document: that case 302s off-domain, and a
    // <loc> that redirects away is a soft error to a crawler.
    $cookiePolicy = \App\Models\LegalDocument::index()['cookies'] ?? null;
@endphp
@if ($cookiePolicy && $cookiePolicy['has_content'] && ! $cookiePolicy['url'])
    <url>
        <loc>{{ url('/cookie-policy') }}</loc>
        {!! $lastmodTag('/cookie-policy') !!}
    </url>
@endif
    {{-- The blog lives on its own host when hosted, and url('/blog') 301s there, so the real URL
         is listed instead: a <loc> that redirects is a soft error to a crawler. The blog host's
         robots.txt points back at this sitemap, which is what authorises the cross-host entry.
         No <lastmod>: this section deliberately touches no database (it is the fallback the index
         falls back to when everything else is degraded), and the posts themselves are dated in
         the blog child sitemap. --}}
    <url>
        <loc>{{ blog_url() }}</loc>
    </url>
    @endif
</urlset>