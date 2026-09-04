{{-- Self-canonical, deliberately.

     The body is the same partial the selfhost page renders, and this page briefly carried
     canonical="/docs/selfhost/federation" to consolidate the two. That contradicted the repo
     convention this page exists under: federation is available to every non-nexus install, so its
     docs are MIRRORED across both trees rather than living in one - which is why
     tests/Feature/FederationDocsTest.php asserts that both pages render, that both are in the docs
     search index, and that both are listed in sitemap.blade.php. A page you deliberately publish,
     link to from docs/saas/setup.blade.php, give a config/docs.php manifest entry and submit in the
     sitemap must not then tell Google to ignore it: that is the one combination Search Console
     files as "Alternate page with proper canonical tag", i.e. permanently unindexed.

     The title, description and lede are written for a SaaS operator rather than a selfhoster, which
     is what makes the two distinct pages sharing one body. If they should ever really consolidate,
     that is a product decision that has to move the test, the manifest entry, the search index and
     the setup-guide link together - not a canonical on its own. --}}
<x-docs-page
    key="saas/federation"
    title="Federation on a White-Label SaaS - Event Schedule"
    description="Share your customers' public events with the eventschedule.com listings. Each customer schedule opts in for itself, and every listing links back to you."
    lede="Share your customers' public events with the eventschedule.com listings and send the discovery traffic back to your platform. You turn the network on for the whole install, eventschedule.com approves it once, and each customer's schedule then chooses whether to be listed."
    article-description="Federation shares the public events on a multi-tenant Event Schedule install with the eventschedule.com listings. The operator enables it for the whole install, eventschedule.com reviews that install once, and each customer schedule chooses whether its events are listed. Every listing links back to the event on the operator's own site."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#enable">Turning it on</x-doc-nav-link>
        <x-doc-nav-link href="#per-schedule">Per-schedule control</x-doc-nav-link>
        <x-doc-nav-link href="#listings">What a listing looks like</x-doc-nav-link>
        <x-doc-nav-link href="#sync">Keeping it in sync</x-doc-nav-link>
        <x-doc-nav-link href="#privacy">What is shared</x-doc-nav-link>
    </x-slot:toc>

    @include('marketing.docs.partials.federation-content')
</x-docs-page>
