<x-docs-page
    key="selfhost/index"
    title="Selfhost Documentation - Event Schedule"
    heading="Selfhost Event Schedule"
    description="Technical documentation for selfhosting Event Schedule: installation, email, Stripe payments, AI, calendar sync, federation and the admin panel."
    lede="Run Event Schedule on your own server and your own database. A single-tenant install has no plan tiers, so every Pro and Enterprise feature is switched on."
    :with-toc="false"
>
    {{-- The cards below used to be hand-written here, each duplicating a
         page's title, blurb and icon from what is now config/docs.php - and
         each carrying a saturated dark:from-*-900 gradient fill, which is why
         a wall of them read as a rainbow wash in dark mode. --}}

    <section id="guides" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="server" />
            Selfhost guides
        </h2>
        <p class="mb-6">
            Start with <a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">Installation</a>, then add only the integrations you need. Each guide lists the <code class="doc-inline-code">.env</code> keys it requires. If you want to host schedules for other people, each on its own subdomain, follow the <a href="{{ route('marketing.docs.saas.setup') }}" class="doc-link">SaaS documentation</a> after installing.
        </p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title"><x-doc-badge plan="selfhost" /> No plan gates</div>
            <p>
                Setting <code class="doc-inline-code">IS_HOSTED=false</code> makes every schedule resolve to the top tier, so the Pro and Enterprise badges you see elsewhere in these docs do not apply to your install: ticketing and QR check-in, unlimited paid ticket sales, custom fields, waitlists, event graphics, webhooks, custom CSS, internal and unlisted events, team members and unlimited newsletters are all available, and the AI features come with them once you add an AI key. The differences run the other way instead, and they are listed below.
            </p>
        </div>

        <x-docs.card-grid group="selfhost" except="selfhost/index" accent="sky" />

        <h3 class="doc-subheading">Where to start</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>
                <strong>Install the app.</strong> It runs on ordinary PHP hosting: PHP 8.2 or newer, MySQL 5.7 or MariaDB 10.3, a web server whose document root is the <code class="doc-inline-code">public</code> directory, and HTTPS. Create an empty database, upload the release files, set the file permissions, then open your domain: the sign-up page doubles as the first-run setup wizard, and it takes your database details, runs the migrations, writes <code class="doc-inline-code">.env</code> and creates your account. Finish with the cron entry that runs the scheduler every minute. It is not optional: the scheduler drives the queue worker, so without it email and calendar sync quietly stop.
                <a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">Installation</a>
            </li>
            <li>
                <strong>Set up email.</strong> Nothing that emails a person works until a mail driver is configured, including password resets, ticket confirmations, booking requests and newsletters. One driver in <code class="doc-inline-code">.env</code> serves the whole instance.
                <a href="{{ route('marketing.docs.selfhost.email') }}" class="doc-link">Email Setup</a>
            </li>
            <li>
                <strong>Add Stripe</strong> if you sell tickets, passes, gift cards or paid appointments. One account can collect for the whole install, or, with Stripe Connect, each event owner can connect their own and be paid directly. No platform fee is ever added to a sale. Invoice Ninja is the alternative, and it needs no <code class="doc-inline-code">.env</code> configuration at all.
                <a href="{{ route('marketing.docs.selfhost.stripe') }}" class="doc-link">Stripe Integration</a>
            </li>
            <li>
                <strong>Add an AI key</strong> (Google Gemini or OpenAI) if you want AI event import, agenda scanning, translation or auto import. Everything else works without one.
                <a href="{{ route('marketing.docs.selfhost.ai') }}" class="doc-link">AI Setup</a>
            </li>
            <li>
                <strong>Connect calendars.</strong> Google and Outlook sync each need your own OAuth credentials in <code class="doc-inline-code">.env</code>; CalDAV needs no server-side configuration, so a schedule can connect one straight away.
                <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="doc-link">Google Calendar</a>,
                <a href="{{ route('marketing.docs.selfhost.microsoft_calendar') }}" class="doc-link">Outlook Calendar</a>
            </li>
            <li>
                <strong>Then the optional pieces.</strong>
                <a href="{{ route('marketing.docs.selfhost.federation') }}" class="doc-link">Federation</a> lists your public events on eventschedule.com and links each one back to your site, and stays off until you turn it on in the admin panel;
                <a href="{{ route('marketing.docs.selfhost.boost') }}" class="doc-link">Boost</a> runs Meta ads from inside the app, billed to the one Meta ad account you configure, so every campaign spends your money;
                the <a href="{{ route('marketing.docs.selfhost.admin') }}" class="doc-link">admin panel</a> at <code class="doc-inline-code">/admin</code> gives you instance-wide monitoring and settings, and the account the setup wizard created is already an instance admin; and the
                <a href="{{ route('marketing.docs.selfhost.accessibility') }}" class="doc-link">accessibility</a> guide covers what to put in your own accessibility statement.
            </li>
        </ol>

        <h3 class="doc-subheading">How a selfhosted install differs</h3>
        <p class="mb-6">
            Same codebase either way. <code class="doc-inline-code">IS_HOSTED=false</code> is what selects the middle column.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Area</th>
                        <th>Your own server</th>
                        <th>eventschedule.com</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Plans</span></td>
                        <td>No tiers. Every schedule has every feature.</td>
                        <td>Free, Pro and Enterprise</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Schedule addresses</span></td>
                        <td>Paths under your domain, e.g. <code class="doc-inline-code">yourdomain.com/my-schedule</code>, set in the <strong>Path</strong> field of a schedule's settings</td>
                        <td>A subdomain, e.g. <code class="doc-inline-code">my-schedule.eventschedule.com</code>, and Enterprise can point its own domain at a schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Accounts</span></td>
                        <td>The setup wizard creates the first account, which becomes the instance admin, then sign-up closes. Set <code class="doc-inline-code">ALLOW_REGISTRATION=true</code> to reopen it.</td>
                        <td>Anyone can sign up</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Team members</span></td>
                        <td>Unlimited</td>
                        <td>The owner only, until Enterprise, which allows up to five members per schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Email delivery</span></td>
                        <td>One mail driver in <code class="doc-inline-code">.env</code> for the whole instance</td>
                        <td>Per-schedule SMTP in the <strong>Email Settings</strong> tab of a schedule's Integrations section. Hosted only, so that tab is hidden on your install.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Newsletter allowance</span></td>
                        <td>Unlimited</td>
                        <td>10 recipients a month on Free, 100 on Pro, 1,000 on Enterprise, and unlimited for a schedule sending through its own SMTP. Each recipient counts as one.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Paid ticket allowance</span></td>
                        <td>Unlimited</td>
                        <td>25 paid tickets a month on Free, unlimited on Pro and Enterprise. Free tickets and RSVPs never count.</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Auto Import</span></td>
                        <td>An <a href="{{ route('marketing.docs.creating_schedules') }}#auto-import" class="doc-link">Auto Import</a> section in a schedule's settings takes Import URLs and Import Cities, and runs once a day from the scheduler. Needs an AI key.</td>
                        <td>Not available</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Updates</span></td>
                        <td><a href="{{ route('marketing.docs.account_settings') }}#app-update" class="doc-link">Settings &rarr; App Update</a> compares your installed version against the latest release and applies it in one click, or run <code class="doc-inline-code">php artisan app:update</code></td>
                        <td>Updated for you</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Attribution</span></td>
                        <td>No "Powered by" footer, but every public schedule page keeps a small Event Schedule chip in the corner, which the license asks you to leave in place. Embedded views do not carry it.</td>
                        <td>Branding is removed on Pro and Enterprise</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </section>

    <section id="more" class="doc-section">
        <h2 class="doc-heading">
            <x-docs.icon name="book" />
            Beyond selfhosting
        </h2>
        <p class="mb-6">
            The rest of the documentation applies to every deployment: the user guide describes the same screens your install runs.
        </p>

        <div class="doc-seealso">
            <a href="{{ route('marketing.docs.getting_started') }}" class="doc-seealso-item doc-unstyled">
                <span class="doc-seealso-title">Getting Started</span>
                <span class="doc-seealso-blurb">Create your first schedule and add events.</span>
                <svg class="doc-seealso-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                </svg>
            </a>
            <a href="{{ route('marketing.docs') }}" class="doc-seealso-item doc-unstyled">
                <span class="doc-seealso-title">All documentation</span>
                <span class="doc-seealso-blurb">User guides, SaaS setup and the developer API.</span>
                <svg class="doc-seealso-arrow" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 7l5 5-5 5M6 12h12" />
                </svg>
            </a>
            <a href="{{ route('marketing.docs.developer.api') }}" class="doc-seealso-item doc-unstyled">
                <span class="doc-seealso-title">REST API and webhooks</span>
                <span class="doc-seealso-blurb">Drive your instance from your own tools.</span>
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

        <p class="mt-6">
            Event Schedule is released under the Attribution Assurance License, an OSI-approved licence adapted from the BSD licence, which is why the credit chip stays on a selfhosted install. The <a href="{{ route('marketing.open_source') }}" class="doc-link">open source page</a> links the repository, the releases and the license text.
        </p>
    </section>
</x-docs-page>
