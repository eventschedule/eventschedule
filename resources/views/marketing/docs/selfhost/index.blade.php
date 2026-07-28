<x-docs-page
    key="selfhost/index"
    title="Selfhost Documentation - Event Schedule"
    heading="Selfhost Installation"
    description="Technical documentation for selfhosting Event Schedule. Learn to install, configure email, set up Stripe payments, enable AI, and run your own instance."
    lede="Deploy Event Schedule on your own server. Full control, complete customization, no vendor lock-in."
    :with-toc="false"
>
    {{-- The 9 cards below used to be hand-written here, each duplicating a
         page's title, blurb and icon from what is now config/docs.php - and
         each carrying a saturated dark:from-*-900 gradient fill, which is why
         a wall of them read as a rainbow wash in dark mode. --}}

    <section id="guides" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="server" />
            Selfhost guides
        </h2>
        <p class="mb-6">
            Start with the installation guide, then add only the integrations you need. Every page here applies to a single-tenant install on your own domain; if you are running a multi-tenant platform, see the <x-link :href="route('marketing.docs.saas.setup')">SaaS documentation</x-link> as well.
        </p>

        <x-docs.card-grid group="selfhost" except="selfhost/index" accent="sky" />
    </section>

    <section id="more" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="book" />
            Beyond selfhosting
        </h2>
        <p class="mb-6">
            The rest of the documentation applies to every deployment.
        </p>

        <div class="doc-seealso">
            <a href="{{ route('marketing.docs') }}" class="doc-seealso-item doc-unstyled">
                <span class="doc-seealso-title">All documentation</span>
                <span class="doc-seealso-blurb">User guides, SaaS setup and the developer API.</span>
                <svg class="doc-seealso-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                </svg>
            </a>
            <a href="{{ route('marketing.docs.saas.setup') }}" class="doc-seealso-item doc-unstyled">
                <span class="doc-seealso-title">Run it as a SaaS</span>
                <span class="doc-seealso-blurb">Multi-tenant routing, custom domains and plans.</span>
                <svg class="doc-seealso-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                </svg>
            </a>
        </div>
    </section>
</x-docs-page>
