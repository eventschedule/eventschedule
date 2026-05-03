<x-marketing-layout>
    <x-slot name="title">Web accessibility (selfhost) - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Web accessibility</x-slot>
    <x-slot name="description">Guidance for selfhost operators on accessibility declarations, environment configuration, and user-generated content.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Web accessibility for selfhost operators",
        "description": "Guidance for selfhost operators on accessibility declarations, environment configuration, and user-generated content.",
        "author": { "@type": "Organization", "name": "Event Schedule" },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": { "@type": "ImageObject", "url": "{{ config('app.url') }}/images/light_logo.png", "width": 712, "height": 140 }
        },
        "mainEntityOfPage": { "@type": "WebPage", "@id": "{{ url()->current() }}" },
        "datePublished": "2026-05-03",
        "dateModified": "2026-05-03"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0 grid-pattern"></div>
        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Web accessibility" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />
            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-sky-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-sky-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4a4 4 0 014 4v1h1a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2h1V8a4 4 0 118 0z" />
                    </svg>
                </div>
                <h1 id="overview" class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white scroll-mt-24">Web accessibility (selfhost)</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Event Schedule ships with a public accessibility declaration on the hosted marketing site, an in-app accessibility panel, and skip links. When you selfhost on your own domain, you are usually responsible for your own legal compliance and for publishing your own accessibility statement for visitors who use your installation.
            </p>
        </div>
    </section>

    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1" aria-label="On this page">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#your-declaration" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Your declaration</a>
                        <a href="#configuration" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Configuration</a>
                        <a href="#audit" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Audit backlog</a>
                        <a href="#template" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Template text</a>
                    </nav>
                </aside>

                <div class="flex-1 prose prose-lg dark:prose-invert max-w-none">
                    <h2 id="your-declaration" class="scroll-mt-24">Your declaration on your domain</h2>
                    <p>
                        The hosted <x-link href="{{ marketing_url('/accessibility') }}">accessibility page</x-link> applies to Event Schedule&apos;s own marketing and product URLs. It does not replace a statement on <strong>your</strong> hostname. If you offer services to the public in jurisdictions with web accessibility rules (for example Israel, the EU, or the UK), work with qualified counsel and publish a declaration that matches your deployment, languages, and contact channels.
                    </p>

                    <h2 id="configuration" class="scroll-mt-24">Environment variables</h2>
                    <p>
                        The product reads optional settings from <code class="doc-inline-code">config/accessibility.php</code>. You can override them in <code class="doc-inline-code">.env</code>:
                    </p>
                    <ul>
                        <li><code class="doc-inline-code">ACCESSIBILITY_CONTACT_EMAIL</code> – inbox for accessibility reports (defaults to <code class="doc-inline-code">legal@eventschedule.com</code> if unset; set to your own address).</li>
                        <li><code class="doc-inline-code">ACCESSIBILITY_WCAG_TARGET_LABEL</code> – label shown in translated declaration text (default <code class="doc-inline-code">WCAG 2.1 Level AA</code>).</li>
                        <li><code class="doc-inline-code">ACCESSIBILITY_REFERENCE_IS_5568</code> – set to <code class="doc-inline-code">false</code> to hide the extra Israeli Standard 5568 note block.</li>
                        <li><code class="doc-inline-code">ACCESSIBILITY_RESPONSE_SLA_BUSINESS_DAYS</code> – first-response SLA shown on the declaration (default 10).</li>
                        <li><code class="doc-inline-code">ACCESSIBILITY_LAST_REVIEWED</code> – ISO date string shown as &quot;last reviewed&quot; (default baked in config; update when you change the deployment).</li>
                    </ul>

                    <h2 id="audit" class="scroll-mt-24">Audit backlog (internal)</h2>
                    <p>
                        Keep a short internal list of pages (marketing home, login, dashboard, public schedule, checkout) and track keyboard-only navigation, focus order, form errors, and contrast. When you close a gap, update your public declaration and bump <code class="doc-inline-code">ACCESSIBILITY_LAST_REVIEWED</code>. The hosted declaration lists representative known limitations; mirror that practice on your site.
                    </p>

                    <h2 id="template" class="scroll-mt-24">Starter template (adapt with counsel)</h2>
                    <p class="text-sm text-gray-500 dark:text-gray-400">Replace bracketed fields. This is not legal advice.</p>
                    <pre class="rounded-xl bg-gray-100 dark:bg-[#1A1A1A] p-4 text-sm overflow-x-auto"><code>[Organization name] accessibility statement

Scope: [your domain and services]

We are committed to improving access for people with disabilities. We aim to align with [WCAG 2.1 AA / other target after legal review].

Conformance: [partial / full] as of [date]. Known limitations: [list].

Third parties: [payment provider, analytics, maps, user content].

Feedback: contact [role] at [email]. We aim to respond within [N] business days.

Last updated: [date]</code></pre>

                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-6">
                        {{ __('accessibility.selfhost_doc_summary') }}
                    </p>
                </div>
            </div>
        </div>
    </section>
</x-marketing-layout>
