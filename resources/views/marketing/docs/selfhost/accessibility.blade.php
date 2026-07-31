<x-docs-page
    key="selfhost/accessibility"
    title="Web accessibility (selfhost) - Event Schedule"
    description="What the skip link and accessibility panel actually do, the ACCESSIBILITY_* configuration keys, and how to publish your own declaration on your own domain."
    lede="Event Schedule ships a skip link on every page and an opt-in accessibility panel for public schedules. When you selfhost on your own domain, the declaration that covers those pages is yours to write and yours to publish."
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Two accessibility features ship in every install: a skip link on every page, and an accessibility panel you can switch on per schedule. Everything else - the legal declaration for your hostname, the audit behind it, and the remediation work - belongs to you as the operator, in the same way the privacy policy on your domain does.
        </p>

        <h3 class="doc-subheading">The skip link</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Every page starts with a <strong class="text-gray-900 dark:text-white">Skip to main content</strong> link that stays visually hidden until it takes keyboard focus, then jumps to the <code class="doc-inline-code">#main-content</code> landmark. The admin portal, the sign-in and registration screens and the public schedule pages all render it, and it is translated with the rest of the interface. Nothing to configure.
        </p>

        <h3 class="doc-subheading">The accessibility panel <x-doc-badge plan="free" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            A floating <strong class="text-gray-900 dark:text-white">Accessibility options</strong> button in the bottom corner of a public schedule page opens a small panel of display adjustments. It is <strong class="text-gray-900 dark:text-white">off by default on every schedule</strong> and carries no plan gate, so it is available on Free schedules and on every selfhosted install.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Control</th>
                        <th>Choices</th>
                        <th>What it changes</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('accessibility.toolbar_font_size') }}</td>
                        <td>{{ __('accessibility.toolbar_font_default') }}, {{ __('accessibility.toolbar_font_medium') }}, {{ __('accessibility.toolbar_font_large') }}</td>
                        <td>Sets the root text size to 100%, 106.25% or 112.5%, so the whole page scales instead of one block of copy reflowing on its own.</td>
                    </tr>
                    <tr>
                        <td>{{ __('accessibility.toolbar_high_contrast') }}</td>
                        <td>On or off</td>
                        <td>Lays a fixed, full-viewport <code class="doc-inline-code">backdrop-filter: contrast(1.08)</code> over the page. It is an overlay rather than a filter on the page itself, because a filtered ancestor becomes the containing block for its <code class="doc-inline-code">position: fixed</code> descendants and would drop them to the bottom of the document.</td>
                    </tr>
                    <tr>
                        <td>{{ __('accessibility.toolbar_underline_links') }}</td>
                        <td>On or off</td>
                        <td>Underlines links so they are never signalled by colour alone. Pill-shaped controls are left as they are, because an underline reads as a mistake there.</td>
                    </tr>
                    <tr>
                        <td>{{ __('accessibility.toolbar_reduce_motion') }}</td>
                        <td>On or off</td>
                        <td>Clamps every animation and transition to 0.01ms and turns smooth scrolling off.</td>
                    </tr>
                    <tr>
                        <td>{{ __('accessibility.toolbar_reset') }}</td>
                        <td>Action</td>
                        <td>Returns all four controls to their defaults.</td>
                    </tr>
                    <tr>
                        <td>{{ __('accessibility.toolbar_hide_widget') }}</td>
                        <td>Action, signed-in visitors only</td>
                        <td>Hides the launcher on that browser after a confirmation. It comes back from the <strong class="text-gray-900 dark:text-white">Accessibility</strong> tab on the visitor's own profile settings, which only appears once the widget has been hidden.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Where the choices are stored</div>
            <p>The panel writes to the visitor's browser storage, never to your database, so a preference follows one browser on one device and does not travel with an account. Nothing here needs a cookie banner entry beyond what your own policy already says about local storage.</p>
        </div>

        <h3 class="doc-subheading">Turn the panel on for a schedule</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the schedule and go to its edit page</li>
            <li>Choose <strong class="text-gray-900 dark:text-white">Settings</strong> in the section list, then the <strong class="text-gray-900 dark:text-white">Advanced</strong> tab</li>
            <li>Turn on <strong class="text-gray-900 dark:text-white">Show Accessibility Widget</strong></li>
            <li>Save. The launcher appears in the bottom corner of that schedule's public pages</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The setting is per schedule, so repeat it for each one you publish. See <a href="{{ route('marketing.docs.creating_schedules') }}#settings-advanced" class="doc-link">Advanced settings</a> in the schedule guide. The panel is a guest-portal feature: the admin portal gets the skip link but not the panel, so an operator auditing their own back office should test it with the keyboard rather than with these controls.
        </p>
    </section>

    <!-- Your declaration -->
    <section id="your-declaration" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="shield" />
            Your declaration on your domain
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The <a href="{{ marketing_url('/accessibility') }}" class="doc-link">accessibility statement on eventschedule.com</a> applies to Event Schedule's own marketing and product URLs. It does not cover <strong class="text-gray-900 dark:text-white">your</strong> hostname, your configuration, or the events your users publish. If you offer services to the public in a jurisdiction with web accessibility rules (for example Israel, the EU, or the UK), work with qualified counsel and publish a declaration that matches your deployment, your languages, and your contact channels.
        </p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Your install does not serve /accessibility</div>
            <p>That declaration page is registered only on the network site. On a selfhosted install the path is not a marketing page at all, so you cannot simply point people at <code class="doc-inline-code">/accessibility</code> on your own domain and expect the shipped text to appear. Publish your statement wherever suits you and link it from your footer or a schedule page.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The accessibility panel carries an <strong class="text-gray-900 dark:text-white">{{ __('accessibility.toolbar_declaration') }}</strong> link at the bottom, and that link is built from your marketing URL setting. Until you change it, the link sends your visitors to Event Schedule's statement rather than yours. See <a href="#configuration" class="doc-link">Environment variables</a> below.
        </p>
    </section>

    <!-- Configuration -->
    <section id="configuration" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="cog" />
            Environment variables
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Five optional settings live in <code class="doc-inline-code">config/accessibility.php</code>. Each one reads an environment variable, so you can override it in <code class="doc-inline-code">.env</code> without editing the file.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Default</th>
                        <th>What it sets</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">ACCESSIBILITY_CONTACT_EMAIL</code></td>
                        <td><code class="doc-inline-code">contact@eventschedule.com</code></td>
                        <td>The inbox the declaration tells people to write to. Set it to an address you actually watch.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">ACCESSIBILITY_WCAG_TARGET_LABEL</code></td>
                        <td><code class="doc-inline-code">WCAG 2.1 Level AA</code></td>
                        <td>The standard named in the declaration. It is a label only: changing it does not change the product, so it should match what your review actually concluded.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">ACCESSIBILITY_REFERENCE_IS_5568</code></td>
                        <td><code class="doc-inline-code">true</code></td>
                        <td>Adds a short note about Israeli Standard 5568 to the commitment clause. Setting it to <code class="doc-inline-code">false</code> drops that note only: the main commitment sentence names the standard either way, so edit the translation string if you need it gone entirely.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">ACCESSIBILITY_RESPONSE_SLA_BUSINESS_DAYS</code></td>
                        <td><code class="doc-inline-code">10</code></td>
                        <td>The first-response target, in business days, printed in the feedback clause.</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">ACCESSIBILITY_LAST_REVIEWED</code></td>
                        <td><code class="doc-inline-code">2026-05-03</code></td>
                        <td>The "last reviewed" date, as <code class="doc-inline-code">YYYY-MM-DD</code>. Bump it whenever you re-check the deployment.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">These five keys feed one view</div>
            <p>They are read by the shipped declaration page, which your install does not route. Keeping your values here is still worth doing, because the translated clauses take <code class="doc-inline-code">:email</code>, <code class="doc-inline-code">:sla</code>, <code class="doc-inline-code">:wcag_target</code> and <code class="doc-inline-code">:date</code> placeholders, so a statement page of your own can reuse both the strings and the settings instead of hard-coding them.</p>
        </div>

        <h3 class="doc-subheading">Point the panel's statement link at your site</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The <strong class="text-gray-900 dark:text-white">{{ __('accessibility.toolbar_declaration') }}</strong> link inside the panel is built from <code class="doc-inline-code">APP_MARKETING_URL</code>, which defaults to <code class="doc-inline-code">https://eventschedule.com</code>. Set it to your own site and the link resolves against your domain instead:
        </p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button type="button" class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">APP_MARKETING_URL</span>=<span class="code-string">https://example.com</span></code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            That variable is shared with the rest of the product's outbound marketing links, so change it only if you are ready for all of them to point at your site, and make sure the resulting <code class="doc-inline-code">/accessibility</code> path there actually serves your statement.
        </p>
    </section>

    <!-- Audit backlog -->
    <section id="audit" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="clipboard" />
            Audit backlog (internal)
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            A declaration is only honest if something behind it is being checked. Keep a short internal list of the journeys that matter on your install and re-walk them on a schedule you can keep:
        </p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Sign in and registration</li>
            <li>The dashboard and the schedule editor</li>
            <li>A public schedule page in each layout you publish</li>
            <li>An event page, including the follow and request flows</li>
            <li>Ticket checkout, if you sell tickets</li>
            <li>The admin panel, if your operators use it</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">
            On each one, check keyboard-only navigation, focus order and visible focus, form error messages, and text contrast in both light and dark mode. When you close a gap, update your published declaration and bump <code class="doc-inline-code">ACCESSIBILITY_LAST_REVIEWED</code>.
        </p>

        <h3 class="doc-subheading">Limitations worth naming</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The hosted declaration names its known gaps rather than claiming full conformance, and the same code runs on your install, so these are a reasonable starting point for your own list:
        </p>
        <ul class="doc-list mb-6">
            <li>{{ __('accessibility.gap_calendar') }}</li>
            <li>{{ __('accessibility.gap_legacy_ui') }}</li>
            <li>{{ __('accessibility.gap_embeds') }}</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            The third one is the one selfhost operators tend to underestimate. Descriptions, images, embeds and outbound links on your site are written by your users, not by you, and no setting makes them accessible. If that matters for your obligations, the lever is editorial: tell schedule owners what you expect, and use the request approval flow to check submissions before they go live.
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
