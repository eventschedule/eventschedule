<x-docs-page
    key="selfhost/google-wallet"
    title="Google Wallet Ticket Passes for Selfhost - Event Schedule"
    description="Let ticket buyers and free registrants save their ticket to Google Wallet: set up the issuer account and service account your selfhosted install needs."
    lede="Put an Add to Google Wallet button on every ticket, free registrations included. The pass carries the same QR code the ticket page shows, so it scans at your door unchanged. Off until you configure it."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#prerequisites">Prerequisites</x-doc-nav-link>
        <x-doc-nav-link href="#setup">Setup Instructions</x-doc-nav-link>
        <x-doc-nav-link href="#verify">Verifying it works</x-doc-nav-link>
        <x-doc-nav-link href="#privacy">What is sent to Google</x-doc-nav-link>
        <x-doc-nav-link href="#behaviour">How it works</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 011.063.852l-.708 2.836a.75.75 0 001.063.853l.041-.021M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9-3.75h.008v.008H12V8.25z" />
            </svg>
            Overview
        </h2>
        <p>This page is for whoever runs the install. Wallet passes are switched on for the whole install by three environment variables: there is no setting for them in the app, and nothing for a schedule owner to turn on. Once the variables are set, every eligible ticket carries an <strong>Add to Google Wallet</strong> badge, and tapping it saves a pass that holds the ticket's own QR code.</p>

        <h3 id="where-it-appears" class="doc-subheading">Where the Badge Appears</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Place</th>
                        <th>What the buyer sees</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>The ticket page</td>
                        <td>The badge in its own row along the bottom of the ticket</td>
                    </tr>
                    <tr>
                        <td>The order page</td>
                        <td>For a purchase that spans several events, one badge for each event</td>
                    </tr>
                    <tr>
                        <td>The ticket confirmation email</td>
                        <td>The badge under the button that opens the ticket</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>Which tickets are eligible is under <a href="#verify" class="doc-link">When no button appears</a>.</p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Included on every plan</div>
            <p>Wallet passes are free on every tier, and a selfhosted install resolves to Enterprise anyway, so nothing here is held back by a plan. It does need the environment variables below: without them no button renders anywhere and your install never contacts Google.</p>
        </div>
    </section>

    <!-- Prerequisites -->
    <section id="prerequisites" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75M21 12c0 1.268-.63 2.39-1.593 3.068a3.745 3.745 0 01-1.043 3.296 3.745 3.745 0 01-3.296 1.043A3.745 3.745 0 0112 21c-1.268 0-2.39-.63-3.068-1.593a3.746 3.746 0 01-3.296-1.043 3.745 3.745 0 01-1.043-3.296A3.745 3.745 0 013 12c0-1.268.63-2.39 1.593-3.068a3.745 3.745 0 011.043-3.296 3.746 3.746 0 013.296-1.043A3.746 3.746 0 0112 3c1.268 0 2.39.63 3.068 1.593a3.746 3.746 0 013.296 1.043 3.745 3.745 0 011.043 3.296A3.745 3.745 0 0121 12z" />
            </svg>
            Prerequisites
        </h2>
        <ul class="doc-list">
            <li>A Google account to run the issuer under. Passes are issued in this account's name, so it belongs to whoever operates the installation, not to individual schedule owners</li>
            <li>A Google Cloud project</li>
            <li>A publicly reachable HTTPS <code class="doc-inline-code">APP_URL</code>, if you want your schedule logos and event images on the pass. Google fetches those from your install; a LAN or plain-HTTP address gets a pass with no artwork rather than a broken one</li>
        </ul>
    </section>

    <!-- Setup -->
    <section id="setup" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Setup Instructions
        </h2>

        <h3 id="setup-issuer" class="doc-subheading">1. Create a Google Wallet issuer account</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Go to the <a href="https://pay.google.com/business/console" target="_blank" rel="noopener noreferrer" class="doc-link">Google Pay and Wallet Console</a></li>
            <li>Sign up for the Google Wallet API and accept the terms</li>
            <li>Copy your <strong>Issuer ID</strong>, a long number. This is <code class="doc-inline-code">GOOGLE_WALLET_ISSUER_ID</code></li>
        </ol>

        <h3 id="setup-service-account" class="doc-subheading">2. Enable the API and create a service account</h3>
        <ol class="doc-list doc-list-numbered">
            <li>In the <a href="https://console.cloud.google.com" target="_blank" rel="noopener noreferrer" class="doc-link">Google Cloud Console</a>, select or create a project</li>
            <li>Enable the <strong>Google Wallet API</strong> for it</li>
            <li>Create a <strong>service account</strong> and generate a <strong>JSON key</strong> for it, then download the key file</li>
        </ol>

        <h3 id="setup-access" class="doc-subheading">3. Give the service account access to the issuer</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Back in the Google Pay and Wallet Console, open <strong>Users</strong></li>
            <li>Invite the service account's email address, the one ending <code class="doc-inline-code">@....iam.gserviceaccount.com</code></li>
            <li>Set the access level to <strong>Developer</strong></li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">There is no Google Cloud IAM role to grant</div>
            <p>Authorization comes from the Users tab in the Wallet Console, not from Cloud IAM. If you go looking for a Wallet role under IAM you will not find one. Skipping this step is the usual cause of a button that appears but never produces a pass: the credentials are valid, they simply cannot write to your issuer.</p>
        </div>

        <h3 id="setup-env" class="doc-subheading">4. Configure the app</h3>
        <p>Add these to your <code class="doc-inline-code">.env</code>. <code class="doc-inline-code">.env.example</code> carries the same three lines, commented out, in its "Google Wallet passes (optional)" block, with a note on what leaves the install.</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">GOOGLE_WALLET_ISSUER_ID</span>=<span class="code-string">3388000000012345678</span>
<span class="code-variable">GOOGLE_WALLET_SERVICE_ACCOUNT</span>=<span class="code-string">/var/www/secrets/wallet-service-account.json</span>
<span class="code-variable">GOOGLE_WALLET_ID_PREFIX</span>=<span class="code-string">es</span></code></pre>
        </div>

        <h3 class="doc-subheading">Variable Reference</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">GOOGLE_WALLET_ISSUER_ID</code></td>
                        <td>The Issuer ID from step 1. Both this and the service account must be set, or the feature stays off</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GOOGLE_WALLET_SERVICE_ACCOUNT</code></td>
                        <td>Either an absolute path to the JSON key file, or the base64-encoded contents of that file. The second form is for hosts with no writable file mount, where config is an app spec rather than a <code class="doc-inline-code">.env</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GOOGLE_WALLET_ID_PREFIX</code></td>
                        <td>Namespaces the passes this installation creates. Defaults to <code class="doc-inline-code">es</code>. Only letters, digits, dots, underscores and hyphens are kept</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p>To use the base64 form:</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>Terminal</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>base64 -i wallet-service-account.json | tr -d '\n'</code></pre>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Give a staging install its own prefix</div>
            <p><strong>Google cannot delete a pass class or object once it exists</strong>, only expire it. Two installations that share one issuer account and one prefix will collide permanently, because the identifiers are derived from the event and sale IDs. If you run a staging copy against the same issuer, change <code class="doc-inline-code">GOOGLE_WALLET_ID_PREFIX</code> there.</p>
        </div>

        <p>If your install caches its config, run <code class="doc-inline-code">php artisan config:clear</code> (or re-run <code class="doc-inline-code">php artisan config:cache</code>) after editing <code class="doc-inline-code">.env</code>. On a cached-config install this is the difference between working and the button never appearing.</p>

        <h3 id="setup-publishing" class="doc-subheading">5. Request publishing access</h3>
        <p>A new issuer account starts in <strong>demo mode</strong>. Only Google accounts you have registered as test accounts in the Google Pay and Wallet Console can save a pass, and those passes carry a demo banner. Everyone else sees the button and gets nothing, which looks like a broken feature rather than a pending approval.</p>
        <p>Request publishing access from the console when you are ready to go live.</p>
    </section>

    <!-- Verify -->
    <section id="verify" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
            </svg>
            Verifying it works
        </h2>
        <p>There is no health check for this, so confirm it by hand:</p>
        <ol class="doc-list doc-list-numbered">
            <li>Open the page of a ticket whose order is complete: a paid ticket, or a free registration. An <strong>Add to Google Wallet</strong> badge should appear in its own row along the bottom of the ticket</li>
            <li>Tap it. You should be redirected to <code class="doc-inline-code">pay.google.com</code> with a pass ready to save</li>
            <li>Save it, then scan the pass's own QR from <strong>Sales &rarr; Scan Ticket</strong>. It should check the attendee in exactly as the on-page QR does</li>
        </ol>
        <p>While the issuer is in demo mode, step 2 only works for a Google account you registered as a test account.</p>
        <p>The same badge appears in the ticket confirmation email and, for a purchase that spans several events, on its order page, once for each event.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">When no button appears</div>
            <p>A badge is only offered for an order that is complete (paid, or a free registration), not deleted, not on an installment plan that has fallen behind, whose event is not cancelled, and which is not an appointment booking. Appointment bookings have their own manage page and never enter the QR ticket flow.</p>
        </div>
    </section>

    <!-- Privacy -->
    <section id="privacy" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751A11.959 11.959 0 0112 2.714z" />
            </svg>
            What is sent to Google
        </h2>
        <p>Nothing is sent for a buyer who never taps the button. When one does, your install sends Google:</p>
        <ul class="doc-list">
            <li>The attendee name, the event name, the venue name and address, and the start time, with the end time when the event has a length</li>
            <li>The ticket type, any seat labels, and the number of guests when a ticket admits more than one</li>
            <li><strong>The event's ticket notes</strong>, truncated to 200 characters. This is free text your organizers write, so it is worth knowing it leaves the install</li>
            <li>The schedule's name and accent colour, the event's public URL and, when <code class="doc-inline-code">APP_URL</code> is publicly reachable over HTTPS, the URLs of the schedule's profile image (the Event Schedule logo when it has none) and the event's image</li>
            <li>The venue's coordinates, when it has them</li>
            <li>A ticket number and the pass's own identifiers, which are derived from the ids of the order and the event</li>
            <li><strong>The ticket URL, which contains that sale's secret</strong></li>
        </ul>
        <p>The ticket URL has to be there: it is what the pass's QR code encodes, and your door scanner reads that exact URL.</p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Saved passes persist on Google's servers</div>
            <p>When a buyer saves a pass, Google creates a pass object on its side holding the data above, and <strong>it cannot be deleted afterwards</strong>, only expired. Take that into account if your installation has a data-retention or deletion obligation.</p>
        </div>
    </section>

    <!-- Behaviour -->
    <section id="behaviour" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
            </svg>
            How it works
        </h2>
        <ul class="doc-list">
            <li><strong>The class</strong> describes one occurrence: branding, venue, date and time. It is created once over the Wallet API and then cached, because a token carrying both the class and the ticket exceeds the 1800 characters Google documents as the safe length</li>
            <li><strong>The ticket</strong> rides inside the save link itself, so there is no extra API call per sale</li>
            <li><strong>Identifiers are derived</strong> from the event and sale IDs, so a buyer who taps twice gets the same pass rather than a second one</li>
            <li><strong>One pass per event:</strong> an order that spans several events gets a pass for each. A season pass is a single pass for its whole run rather than one per date, so its class carries no date</li>
        </ul>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Passes are never updated after they are created</div>
            <p>Neither half is patched once written. A saved pass keeps the details it was saved with, so cancelling or fully refunding an order does not remove it from anyone's phone, and re-tapping the button returns the original rather than a corrected one. The QR still stops working: your door scanner checks the order's live status and refuses a cancelled or fully refunded ticket exactly as the ticket page does. A partial refund leaves the order paid, so its pass keeps scanning.</p>
            <p class="mt-2">The class is written once per occurrence too. If you rename an event, move it or change its time <em>after</em> the first buyer has tapped, passes saved from then on still carry the original details.</p>
        </div>

        <h3 class="doc-subheading">When a pass stops showing</h3>
        <p>A normal ticket's pass expires three hours after the event ends (an event shorter than an hour, or with no length set, counts as an hour long), and Google moves it out of the main list. Two cases never expire and stay in the wallet indefinitely:</p>
        <ul class="doc-list">
            <li>A season pass whose ticket leaves <strong>Valid for (days)</strong> blank, because it has no expiry date to inherit</li>
            <li>An all-day event, or a recurring event with no start time, because there is no occurrence instant to measure from</li>
        </ul>

        <h3 class="doc-subheading">Very long passes drop optional details</h3>
        <p>Google caps the save link, so when a pass would not fit, the extra rows go first (the ticket notes and the guest count), then the seat list, then the ticket type. The QR code and the attendee name are always kept. The event name, venue and images never count against the cap, because they live on the class rather than in the link. Long ticket notes, a long seat list or a long ticket type are what trigger it, and text in Hebrew, Arabic, Cyrillic or Chinese takes two to three times the room of the same number of Latin letters.</p>
    </section>

    <!-- Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <h3 class="doc-subheading">The button never appears</h3>
        <p>Either the ticket is not eligible (see <a href="#verify" class="doc-link">When no button appears</a>), or your credentials did not load. A wrong path, a file the web user cannot read, a truncated or line-wrapped base64 paste, or JSON missing <code class="doc-inline-code">client_email</code> or <code class="doc-inline-code">private_key</code> all count as <em>unconfigured</em>: there is no error and nothing is logged, the button simply never renders. Check that the path resolves to a file and that the JSON decodes with both keys present. A stray space or newline in <code class="doc-inline-code">GOOGLE_WALLET_ISSUER_ID</code> is ignored.</p>

        <h3 class="doc-subheading">The buyer lands back on their ticket with an error</h3>
        <p>The error reads "The wallet pass could not be created. Please try again." It means the pass could not be built or the call to Google failed. Check <code class="doc-inline-code">storage/logs</code> for one of:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Log message</th>
                        <th>Usual cause</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">Google Wallet token exchange failed</code></td>
                        <td>A revoked, corrupt or wrong-project service-account key</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">Google Wallet class lookup failed</code></td>
                        <td>Most often the missing <strong>Developer</strong> grant from <a href="#setup-access" class="doc-link">step 3</a>. This check runs before the insert, so it is the line you will usually see</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">Google Wallet class insert failed</code></td>
                        <td>The issuer rejected the pass class, for example a malformed issuer ID</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">Google Wallet JWT exceeds the safe length</code></td>
                        <td>The pass is too long even with every optional detail dropped. All that is left by then is the ticket URL, the attendee name and the pass IDs, so look for an unusually long <code class="doc-inline-code">APP_URL</code> or <code class="doc-inline-code">GOOGLE_WALLET_ID_PREFIX</code></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>A failure is cached for five minutes, so fix the cause and wait a moment rather than retrying in a loop. The access token is cached for just under an hour.</p>

        <h3 class="doc-subheading">The pass saves for you but not for anyone else</h3>
        <p>The issuer is still in demo mode. See <a href="#setup-publishing" class="doc-link">step 5</a>.</p>

        <h3 class="doc-subheading">No arrival notification</h3>
        <p>The pass carries the venue's <strong>coordinates</strong>, not its address, and coordinates are only filled in when the venue is geocoded. That needs <code class="doc-inline-code">BACKEND_GOOGLE_KEY</code> to be set. Without a Maps key a venue can have a complete address and still have no coordinates, and no notification will fire. Coordinates are looked up when a venue is saved, so after adding the key, save each venue once more. Like the rest of the class, they only reach occurrences whose first pass is saved after that.</p>

        <h3 class="doc-subheading">The pass has no logo or banner image</h3>
        <p>Google fetches those from your installation, so they are only sent when <code class="doc-inline-code">APP_URL</code> is an HTTPS address Google could actually reach. A LAN or plain-HTTP install deliberately sends no image rather than shipping a broken one.</p>

        <h3 class="doc-subheading">The pass shows the Event Schedule logo, or old branding</h3>
        <p>The pass logo is the schedule's profile image, and the Event Schedule logo stands in when the schedule has none, so give the schedule a profile image. Branding is written into an occurrence's pass class once, when its first buyer taps the button, and the class is never rewritten after that. A new profile image, accent colour or schedule name therefore shows only on occurrences whose first pass is saved after the change.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Outbound access</div>
            <p>Your install needs to reach <code class="doc-inline-code">oauth2.googleapis.com</code> and <code class="doc-inline-code">walletobjects.googleapis.com</code>. Relevant if outbound traffic is restricted.</p>
        </div>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.19 8.688a4.5 4.5 0 011.242 7.244l-4.5 4.5a4.5 4.5 0 01-6.364-6.364l1.757-1.757m13.35-.622l1.757-1.757a4.5 4.5 0 00-6.364-6.364l-4.5 4.5a4.5 4.5 0 001.242 7.244" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.tickets') }}#wallet-passes" class="doc-link">Wallet passes in the user guide</a> - what a schedule owner and their buyers see</li>
            <li><a href="{{ route('marketing.docs.tickets') }}#check-in" class="doc-link">Check-in at the door</a> - the scanner a wallet pass is read by</li>
            <li><a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">Installation</a> - the other optional integrations</li>
        </ul>
    </section>
</x-docs-page>
