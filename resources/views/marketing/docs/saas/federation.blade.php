{{-- The body is the same partial the selfhost page renders, so the two pages
     consolidate onto one URL: this one canonicals to /docs/selfhost/federation. --}}
<x-docs-page
    key="saas/federation"
    title="Federation on a White-Label SaaS - Event Schedule"
    description="Share your customers' public events with the eventschedule.com listings. Each customer schedule opts in for itself, and every listing links back to you."
    canonical="/docs/selfhost/federation"
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
