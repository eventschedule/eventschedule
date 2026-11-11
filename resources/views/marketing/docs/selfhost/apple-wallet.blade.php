<x-docs-page
    key="selfhost/apple-wallet"
    title="Apple Wallet Ticket Passes for Selfhost - Event Schedule"
    description="Let ticket buyers and free registrants save their ticket to Apple Wallet: set up the Pass Type ID and certificate your selfhosted install signs passes with."
    lede="Put an Add to Apple Wallet button on every ticket, free registrations included. The pass carries the same QR code the ticket page shows, so it scans at your door unchanged. Off until you configure it."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#prerequisites">Prerequisites</x-doc-nav-link>
        <x-doc-nav-link href="#setup">Setup Instructions</x-doc-nav-link>
        <x-doc-nav-link href="#verify">Verifying it works</x-doc-nav-link>
        <x-doc-nav-link href="#privacy">What is sent to Apple</x-doc-nav-link>
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
        <p>This page is for whoever runs the install. Apple Wallet passes are switched on for the whole install by environment variables: there is no setting for them in the app, and nothing for a schedule owner to turn on. Once they are set, every eligible ticket carries an <strong>Add to Apple Wallet</strong> button, and tapping it on an iPhone or a Mac opens a pass that holds the ticket's own QR code. It works beside <a href="{{ route('marketing.docs.selfhost.google_wallet') }}" class="doc-link">Google Wallet</a>, and either can be on without the other.</p>

        <h3 id="where-it-appears" class="doc-subheading">Where the Button Appears</h3>
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
                        <td>The button under the QR code, first on an iPhone, iPad or Mac</td>
                    </tr>
                    <tr>
                        <td>The order page</td>
                        <td>For a purchase that spans several events, one button for each event</td>
                    </tr>
                    <tr>
                        <td>The ticket confirmation and reminder emails</td>
                        <td>The button under the button that opens the ticket</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p>A ticket is eligible when it is paid (a free registration counts), not deleted, for an event that is not cancelled and is not an appointment booking, and its payment plan, if it has one, is not behind. Google Wallet uses the same rule.</p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Included on every plan</div>
            <p>Wallet passes are free on every tier, and a selfhosted install resolves to Enterprise anyway. Without the environment variables below no button renders anywhere, no email carries a link, and the pass address answers 404.</p>
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
            <li>A paid <a href="https://developer.apple.com/programs/" target="_blank" rel="noopener noreferrer" class="doc-link">Apple Developer Program</a> membership. Passes are signed in this account's name, so it belongs to whoever operates the installation</li>
            <li>A Mac with Keychain Access to make the certificate request, or OpenSSL on any machine</li>
            <li>PHP's <code class="doc-inline-code">openssl</code>, <code class="doc-inline-code">zip</code> and <code class="doc-inline-code">gd</code> extensions, which the install already needs. Without them the button stays hidden rather than failing</li>
        </ul>

    </section>

    <!-- Setup Instructions -->
    <section id="setup" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11.42 15.17L17.25 21A2.652 2.652 0 0021 17.25l-5.877-5.877M11.42 15.17l2.496-3.03c.317-.384.74-.626 1.208-.766M11.42 15.17l-4.655 5.653a2.548 2.548 0 11-3.586-3.586l6.837-5.63m5.108-.233c.55-.164 1.163-.188 1.743-.14a4.5 4.5 0 004.486-6.336l-3.276 3.277a3.004 3.004 0 01-2.25-2.25l3.276-3.276a4.5 4.5 0 00-6.336 4.486c.091 1.076-.071 2.264-.904 2.95l-.102.085m-1.745 1.437L5.909 7.5H4.5L2.25 3.75l1.5-1.5L7.5 4.5v1.409l4.26 4.26m-1.745 1.437l1.745-1.437m6.615 8.206L15.75 15.75M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Setup Instructions
        </h2>
        <h3 id="setup-pass-type" class="doc-subheading">1. Create a Pass Type ID</h3>
        <ol class="doc-list doc-list-numbered">
            <li>In <a href="https://developer.apple.com/account/resources/identifiers/list/passTypeId" target="_blank" rel="noopener noreferrer" class="doc-link">Certificates, Identifiers &amp; Profiles</a>, add an identifier of the type <strong>Pass Type IDs</strong></li>
            <li>Give it a reverse-domain name such as <code class="doc-inline-code">pass.com.example.tickets</code>. This is <code class="doc-inline-code">APPLE_WALLET_PASS_TYPE_ID</code></li>
            <li>Your <strong>Team ID</strong>, ten characters, is in the top right of the developer site and under Membership. This is <code class="doc-inline-code">APPLE_WALLET_TEAM_ID</code></li>
        </ol>

        <h3 id="setup-certificate" class="doc-subheading">2. Create its certificate</h3>
        <ol class="doc-list doc-list-numbered">
            <li>Open the Pass Type ID and choose <strong>Create Certificate</strong></li>
            <li>Upload a certificate signing request: in Keychain Access, <strong>Certificate Assistant &gt; Request a Certificate From a Certificate Authority</strong>, saved to disk</li>
            <li>Download the certificate and open it, so Keychain pairs it with the private key the request made</li>
            <li>In Keychain Access, select the certificate with its key and <strong>Export</strong> it as a <code class="doc-inline-code">.p12</code> file with a password</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">A .p12 that will not open</div>
            <p>A <code class="doc-inline-code">.p12</code> exported from an older Keychain can use an algorithm OpenSSL 3 only reads with its legacy provider, and the button then stays hidden. Convert it once to a PEM file holding the certificate and the key, and use that instead: <code class="doc-inline-code">openssl pkcs12 -legacy -in pass.p12 -out pass.pem -nodes</code>, then set <code class="doc-inline-code">APPLE_WALLET_CERTIFICATE</code> to the PEM and leave the password empty.</p>
        </div>

        <h3 id="setup-env" class="doc-subheading">3. Configure the app</h3>
        <p>Add these to your <code class="doc-inline-code">.env</code>. <code class="doc-inline-code">.env.example</code> carries the same lines, commented out, in its "Apple Wallet passes (optional)" block.</p>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>.env</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code><span class="code-variable">APPLE_WALLET_PASS_TYPE_ID</span>=<span class="code-string">pass.com.example.tickets</span>
<span class="code-variable">APPLE_WALLET_TEAM_ID</span>=<span class="code-string">ABCDE12345</span>
<span class="code-variable">APPLE_WALLET_CERTIFICATE</span>=<span class="code-string">/var/www/secrets/pass.p12</span>
<span class="code-variable">APPLE_WALLET_CERTIFICATE_PASSWORD</span>=<span class="code-string">the-export-password</span></code></pre>
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
                        <td><code class="doc-inline-code">APPLE_WALLET_PASS_TYPE_ID</code></td>
                        <td>The Pass Type ID from step 1</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APPLE_WALLET_TEAM_ID</code></td>
                        <td>Your developer Team ID</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APPLE_WALLET_CERTIFICATE</code></td>
                        <td>Either an absolute path to the <code class="doc-inline-code">.p12</code> (or a PEM file holding the certificate and its key), or the base64-encoded contents of that file, for hosts with no writable file mount</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APPLE_WALLET_CERTIFICATE_PASSWORD</code></td>
                        <td>The export password of the <code class="doc-inline-code">.p12</code>, or of the key in a PEM file</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">APPLE_WALLET_WWDR_CERTIFICATE</code></td>
                        <td>Optional. Apple's WWDR G4 intermediate certificate, which every pass signature carries, ships with the app (valid until December 2030). Set this to a path or base64 PEM only when Apple issues a newer one</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 id="setup-badge" class="doc-subheading">4. Optional: Apple's badge</h3>
        <p>Apple licenses its <strong>Add to Apple Wallet</strong> artwork to each developer account for its own passes, so the app does not ship it, and the button is plain text until you add it. Download the badges from <a href="https://developer.apple.com/wallet/add-to-apple-wallet-guidelines/" target="_blank" rel="noopener noreferrer" class="doc-link">Apple's guidelines</a> and save each language as <code class="doc-inline-code">public/images/wallet/apple/{language}.svg</code> for the pages and <code class="doc-inline-code">{language}.png</code> for email, for example <code class="doc-inline-code">en.svg</code> and <code class="doc-inline-code">en.png</code>. English stands in for a language you have not added. The folder is in <code class="doc-inline-code">.gitignore</code> and is not part of the release, so an update leaves your files where they are; in Docker, mount it as a volume or the files go with the next rebuilt image.</p>

    </section>

    <!-- Verifying it works -->
    <section id="verify" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 12.75V12A2.25 2.25 0 014.5 9.75h15A2.25 2.25 0 0121.75 12v.75m-8.69-6.44l-2.12-2.12a1.5 1.5 0 00-1.061-.44H4.5A2.25 2.25 0 002.25 6v12a2.25 2.25 0 002.25 2.25h15A2.25 2.25 0 0021.75 18V9a2.25 2.25 0 00-2.25-2.25h-5.379a1.5 1.5 0 01-1.06-.44z" />
            </svg>
            Verifying it works
        </h2>
        <ol class="doc-list doc-list-numbered">
            <li>Register for a free event on your install, or buy a ticket</li>
            <li>Open the ticket page: the <strong>Add to Apple Wallet</strong> button is under the QR code</li>
            <li>Tap it on an iPhone, or click it in Safari on a Mac: Wallet shows the pass and offers <strong>Add</strong></li>
            <li>Scan the pass's QR code with the door scanner: it admits the ticket exactly as the ticket page's code does</li>
        </ol>

    </section>

    <!-- What is sent to Apple -->
    <section id="privacy" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75L11.25 15 15 9.75m-3-7.036A11.959 11.959 0 013.598 6 11.99 11.99 0 003 9.749c0 5.592 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.31-.21-2.571-.598-3.751A11.959 11.959 0 0112 2.714z" />
            </svg>
            What is sent to Apple
        </h2>
        <p>Nothing. The pass is a file your install builds and signs when the buyer taps the button, and the buyer's device opens it. It names no web service, so Apple's servers never hear from your install, and the buyer's device never reports back to it. The pass holds the event's name, date and venue, the schedule's name, colour and picture, the ticket type, the attendee's name, the number of guests, any seats, your ticket notes and the link to the ticket page.</p>

    </section>

    <!-- How it works -->
    <section id="behaviour" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23-.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
            </svg>
            How it works
        </h2>
        <ul class="doc-list">
            <li><strong>A snapshot.</strong> The pass is built at the moment it is added and is not updated afterwards, as Google's is not. A changed time or venue reaches the buyer by email; the back of the pass links to the live ticket page</li>
            <li><strong>On the lock screen.</strong> Wallet raises the pass around the start of the event, in the event's own time zone</li>
            <li><strong>Afterwards.</strong> The pass is greyed out three hours after the event ends (an hour after the start when the event has no length). A season pass expires when the pass itself does</li>
            <li><strong>Colours and picture.</strong> The schedule's colour is the background, with white or near-black text, whichever reads; its profile picture is the icon and logo, and the app's own icon stands in when it has none</li>
        </ul>

    </section>

    <!-- Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
            </svg>
            Troubleshooting
        </h2>
        <h3 class="doc-subheading">No button appears</h3>
        <p>All of the pass type id, the team id and a certificate that opens with its password must be set; one missing, an expired or mismatched certificate and key, or a missing <code class="doc-inline-code">zip</code> or <code class="doc-inline-code">openssl</code> extension keeps the feature off: nothing is shown to buyers, and the app's log (<code class="doc-inline-code">storage/logs/laravel.log</code>) says which one, in a warning that starts <code class="doc-inline-code">Apple Wallet:</code> and is written at most once an hour. Check the values, then see <a href="#setup-certificate" class="doc-link">A .p12 that will not open</a>. The ticket must also be eligible (see <a href="#overview" class="doc-link">Overview</a>).</p>

        <h3 class="doc-subheading">The iPhone says the pass cannot be added</h3>
        <p>The pass type id or team id does not match the certificate, or the certificate was made for another Pass Type ID. Wallet checks that the signature, the pass type id and the team id all belong together.</p>

        <h3 class="doc-subheading">The button is plain text</h3>
        <p>Apple's badge is not installed. See <a href="#setup-badge" class="doc-link">step 4</a>.</p>

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
            <li><a href="{{ route('marketing.docs.selfhost.google_wallet') }}" class="doc-link">Google Wallet</a> - the same for Android</li>
            <li><a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">Installation</a> - the other optional integrations</li>
        </ul>

    </section>
</x-docs-page>
