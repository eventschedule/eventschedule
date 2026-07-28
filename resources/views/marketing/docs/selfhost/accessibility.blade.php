<x-docs-page
    key="selfhost/accessibility"
    title="Web accessibility (selfhost) - Event Schedule"
    description="Guidance for selfhost operators on accessibility declarations, environment configuration, and user-generated content."
    lede="Event Schedule ships an accessibility panel and skip links, but when you selfhost on your own domain you are responsible for your own compliance and for publishing your own statement."
    article-headline="Web accessibility for selfhost operators"
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#your-declaration">Your declaration</x-doc-nav-link>
        <x-doc-nav-link href="#configuration">Configuration</x-doc-nav-link>
        <x-doc-nav-link href="#audit">Audit backlog</x-doc-nav-link>
        <x-doc-nav-link href="#template">Template text</x-doc-nav-link>
    </x-slot:toc>

    {{-- This page previously used `prose prose-lg dark:prose-invert`, which is
         dead markup in this repo - @tailwindcss/typography is not installed, so
         it rendered as an edge-to-edge wall of body-size text with no heading
         hierarchy. Rebuilt on the real docs primitives. --}}

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="accessibility" />
            Overview
        </h2>
        <p>
            Event Schedule ships with a public accessibility declaration on the hosted marketing site, an in-app accessibility panel, and skip links. When you selfhost on your own domain, you are usually responsible for your own legal compliance and for publishing your own accessibility statement for visitors who use your installation.
        </p>
    </section>

    <!-- Your declaration -->
    <section id="your-declaration" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="shield" />
            Your declaration on your domain
        </h2>
        <p>
            The hosted <x-link href="{{ marketing_url('/accessibility') }}">accessibility page</x-link> applies to Event Schedule&apos;s own marketing and product URLs. It does not replace a statement on <strong>your</strong> hostname. If you offer services to the public in jurisdictions with web accessibility rules (for example Israel, the EU, or the UK), work with qualified counsel and publish a declaration that matches your deployment, languages, and contact channels.
        </p>
    </section>

    <!-- Configuration -->
    <section id="configuration" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="cog" />
            Environment variables
        </h2>
        <p>
            The product reads optional settings from <code class="doc-inline-code">config/accessibility.php</code>. You can override them in <code class="doc-inline-code">.env</code>:
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4><code class="doc-inline-code">ACCESSIBILITY_CONTACT_EMAIL</code></h4>
                <p>Inbox for accessibility reports. Defaults to <code class="doc-inline-code">legal@eventschedule.com</code> if unset - set it to your own address.</p>
            </div>
            <div class="doc-field">
                <h4><code class="doc-inline-code">ACCESSIBILITY_WCAG_TARGET_LABEL</code></h4>
                <p>Label shown in the translated declaration text. Default <code class="doc-inline-code">WCAG 2.1 Level AA</code>.</p>
            </div>
            <div class="doc-field">
                <h4><code class="doc-inline-code">ACCESSIBILITY_REFERENCE_IS_5568</code></h4>
                <p>Set to <code class="doc-inline-code">false</code> to hide the extra Israeli Standard 5568 note block.</p>
            </div>
            <div class="doc-field">
                <h4><code class="doc-inline-code">ACCESSIBILITY_RESPONSE_SLA_BUSINESS_DAYS</code></h4>
                <p>First-response SLA shown on the declaration. Default 10.</p>
            </div>
            <div class="doc-field">
                <h4><code class="doc-inline-code">ACCESSIBILITY_LAST_REVIEWED</code></h4>
                <p>ISO date string shown as "last reviewed". Defaults to the value baked into config - update it when you change the deployment.</p>
            </div>
        </div>
    </section>

    <!-- Audit backlog -->
    <section id="audit" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="clipboard" />
            Audit backlog (internal)
        </h2>
        <p>
            Keep a short internal list of pages (marketing home, login, dashboard, public schedule, checkout) and track keyboard-only navigation, focus order, form errors, and contrast. When you close a gap, update your public declaration and bump <code class="doc-inline-code">ACCESSIBILITY_LAST_REVIEWED</code>. The hosted declaration lists representative known limitations; mirror that practice on your site.
        </p>
    </section>

    <!-- Template text -->
    <section id="template" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="mail" />
            Starter template (adapt with counsel)
        </h2>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Not legal advice</div>
            <p>Replace every bracketed field, and have the result reviewed by qualified counsel before you publish it.</p>
        </div>

        <div class="doc-code-block doc-code-block--wrap">
            <div class="doc-code-header">
                <span>text</span>
                <button type="button" class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>[Organization name] accessibility statement

Scope: [your domain and services]

We are committed to improving access for people with disabilities. We aim to align with [WCAG 2.1 AA / other target after legal review].

Conformance: [partial / full] as of [date]. Known limitations: [list].

Third parties: [payment provider, analytics, maps, user content].

Feedback: contact [role] at [email]. We aim to respond within [N] business days.

Last updated: [date]</code></pre>
        </div>

        <p class="text-sm text-gray-500 dark:text-gray-400">
            {{ __('accessibility.selfhost_doc_summary') }}
        </p>
    </section>
</x-docs-page>
