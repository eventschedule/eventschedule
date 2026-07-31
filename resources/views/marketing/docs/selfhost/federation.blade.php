<x-docs-page
    key="selfhost/federation"
    description="Share public events from your selfhosted install with the eventschedule.com listings. Off by default, opted into per schedule, every listing links back to your site."
    lede="Share your public events with the eventschedule.com listings and send the discovery traffic back to your own site. An administrator turns the network on for the whole install, eventschedule.com approves it once, and each schedule then chooses whether to be listed."
    article-description="Federation shares the public events on a selfhosted Event Schedule install with the eventschedule.com listings. An administrator enables it for the whole install, eventschedule.com reviews that install once, and each schedule chooses whether its events are listed. Every listing links back to the event on the origin site."
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
