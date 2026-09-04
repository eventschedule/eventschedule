<x-docs-page
    key="saas/twilio"
    description="Set up Twilio for SMS phone verification, SMS invitations and WhatsApp event creation in your SaaS Event Schedule deployment."
    lede="Set up Twilio to enable SMS phone verification, SMS invitations and WhatsApp event creation across your Event Schedule deployment."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#create-account">Create a Twilio Account</x-doc-nav-link>
        <x-doc-nav-link href="#environment">Environment Setup</x-doc-nav-link>
        <x-doc-nav-link href="#phone-verification">Phone Verification</x-doc-nav-link>
        <x-doc-nav-link href="#whatsapp">WhatsApp Setup</x-doc-nav-link>
        <x-doc-nav-link href="#testing">Testing</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Twilio is an optional integration. One Twilio account serves the whole deployment: you configure it once in <code class="doc-inline-code">.env</code>, and every schedule on the platform uses it. There is nothing for an individual schedule owner to connect. Twilio powers exactly three things:</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Feature</th>
                        <th>What it does</th>
                        <th>Requires</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Phone verification</strong></td>
                        <td>Users verify their account phone number, and editors verify a schedule's public phone number, with a 6-digit code sent by SMS</td>
                        <td>Hosted deployments</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">SMS invitations</strong></td>
                        <td>When an invited team member, venue or talent has a phone number but no email address on file, the sign-up link goes out by SMS instead of email</td>
                        <td>Hosted deployments</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">WhatsApp event creation</strong></td>
                        <td>An organizer sends a text message or a flyer photo to your Twilio number and AI turns it into an event on their default schedule</td>
                        <td>Enterprise plan, plus an AI key</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">What Twilio is not used for</div>
            <p>Event Schedule never sends SMS or WhatsApp messages to attendees, ticket buyers or followers. Ticket confirmations, event change notices and newsletters are all email. The only outbound WhatsApp messages the app sends are replies to a message that someone has just sent to your Twilio number, so there is no broadcast or reminder channel to plan for.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>Twilio is entirely optional. If it is not configured, the app skips SMS and WhatsApp without errors: the verification controls are hidden, invitations fall back to email, and the WhatsApp webhook simply does nothing.</p>
        </div>
    </section>

    <!-- Create a Twilio Account -->
    <section id="create-account" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            Create a Twilio Account
        </h2>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Sign up for a Twilio account at <code class="doc-inline-code">twilio.com</code></li>
            <li>From the Twilio Console dashboard, note your <strong class="text-gray-900 dark:text-white">Account SID</strong> and <strong class="text-gray-900 dark:text-white">Auth Token</strong></li>
            <li>Navigate to <strong class="text-gray-900 dark:text-white">Phone Numbers</strong> &rarr; <strong class="text-gray-900 dark:text-white">Manage</strong> &rarr; <strong class="text-gray-900 dark:text-white">Buy a number</strong></li>
            <li>Purchase a phone number with <strong class="text-gray-900 dark:text-white">SMS</strong> capability</li>
            <li>If you want WhatsApp event creation, register that same number as a WhatsApp sender as well. Event Schedule sends WhatsApp from the number you put in <code class="doc-inline-code">TWILIO_FROM_NUMBER</code>, so it does not need a second number.</li>
        </ol>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Twilio provides trial credit for new accounts, which is sufficient for testing. Note that a trial account can only message numbers you have added as verified caller IDs, so a code that never arrives during testing is usually the trial restriction rather than a misconfiguration. You can upgrade to a paid account when you are ready to go live.</p>
        </div>
    </section>

    <!-- Environment Setup -->
    <section id="environment" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Environment Setup
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add the following variables to your <code class="doc-inline-code">.env</code> file:</p>

        <pre class="doc-code-block"><code>TWILIO_SID=your_account_sid
TWILIO_AUTH_TOKEN=your_auth_token
TWILIO_FROM_NUMBER=+1234567890</code></pre>

        <p class="text-gray-600 dark:text-gray-300 mb-6 mt-4">All three are required. If any one of them is empty, both SMS and WhatsApp stay switched off: the app writes a warning to the log and carries on rather than failing.</p>

        <h3 class="doc-subheading">Variable reference</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">TWILIO_SID</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your Twilio Account SID. Find it on the <strong>Twilio Console</strong> dashboard, displayed prominently at the top of the page.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">TWILIO_AUTH_TOKEN</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your Twilio Auth Token. Found on the same Console dashboard page. Click to reveal the token and copy it. The same token is used to authenticate outgoing requests and to validate the signature on incoming WhatsApp webhooks, so rotating it in Twilio means updating it here too.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">TWILIO_FROM_NUMBER</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The Twilio phone number to send from, in E.164 format (e.g., <code class="doc-inline-code">+15551234567</code>). This must be a number you have purchased or verified in your Twilio account.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400">WhatsApp uses this same number, sent as <code class="doc-inline-code">whatsapp:</code> plus the value above. There is no separate WhatsApp variable.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>If you cache your configuration, run <code class="doc-inline-code">php artisan config:clear</code> after editing <code class="doc-inline-code">.env</code>, or the old values keep being used.</p>
        </div>
    </section>

    <!-- Phone Number Verification -->
    <section id="phone-verification" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c0 8.284 6.716 15 15 15h2.25a2.25 2.25 0 002.25-2.25v-1.372c0-.516-.351-.966-.852-1.091l-4.423-1.106c-.44-.11-.902.055-1.173.417l-.97 1.293c-.282.376-.769.542-1.21.38a12.035 12.035 0 01-7.143-7.143c-.162-.441.004-.928.38-1.21l1.293-.97c.363-.271.527-.734.417-1.173L6.963 3.102a1.125 1.125 0 00-1.091-.852H4.5A2.25 2.25 0 002.25 4.5v2.25z" />
            </svg>
            Phone Number Verification
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once Twilio is configured, a verification control appears next to every saved but unverified phone number, in two places:</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Account settings</strong> - the <strong class="text-gray-900 dark:text-white">Phone Number</strong> field on a user's own profile. While the number is unverified the page reads "Your phone number is unverified." with a <strong class="text-gray-900 dark:text-white">Click here to verify your phone</strong> link underneath.</li>
            <li><strong class="text-gray-900 dark:text-white">Schedule settings, Details &rarr; Contact Info</strong> - the schedule's <strong class="text-gray-900 dark:text-white">Phone Number</strong> field. Any editor of the schedule can run the verification, and the result belongs to the schedule rather than to the person who ran it.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Both controls are hosted-only. A single-tenant selfhosted install does not show them even with Twilio configured.</p>

        <h3 class="doc-subheading">How it works</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Enter the number and save. The field has a country selector and stores the number in E.164 format (e.g., <code class="doc-inline-code">+15551234567</code>); the verify link only appears once a number has been saved.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Click here to verify your phone</strong>. A 6-digit code is sent by SMS, reading "Your Event Schedule verification code is: ...".</li>
            <li>Type the code into the box that appears and click <strong class="text-gray-900 dark:text-white">Verify</strong>. The code is valid for 10 minutes.</li>
            <li>On success the number is marked verified and the control disappears. Editing the number later clears the verification and the control comes back.</li>
        </ol>

        <h3 class="doc-subheading">What a verified number unlocks</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">A public phone number.</strong> A schedule's phone is only shown to visitors when it has been verified <em>and</em> the <strong class="text-gray-900 dark:text-white">Show phone number</strong> toggle is on. The same rule governs a venue's phone number on an event page.</li>
            <li><strong class="text-gray-900 dark:text-white">Platform discovery.</strong> A schedule qualifies for the platform's public listings once either its email address or its phone number is verified.</li>
            <li><strong class="text-gray-900 dark:text-white">WhatsApp.</strong> Incoming WhatsApp messages are matched to an account by verified phone number, so nobody can create events by WhatsApp until their account phone is verified.</li>
            <li><strong class="text-gray-900 dark:text-white">Claiming.</strong> When a user verifies their account phone, any unclaimed schedule carrying the same number and created within the past year is attached to that account as owner, and becomes their default schedule if they do not already have one.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Rate limiting</div>
            <p>To prevent abuse, a phone number can be sent at most 5 codes per hour. After 5 wrong entries within 10 minutes the pending code is discarded and a fresh one has to be requested.</p>
        </div>
    </section>

    <!-- WhatsApp Setup -->
    <section id="whatsapp" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8.625 12a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H8.25m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0H12m4.125 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm0 0h-.375M21 12c0 4.556-4.03 8.25-9 8.25a9.764 9.764 0 01-2.555-.337A5.972 5.972 0 015.41 20.97a5.969 5.969 0 01-.474-.065 4.48 4.48 0 00.978-2.025c.09-.457-.133-.901-.467-1.226C3.93 16.178 3 14.189 3 12c0-4.556 4.03-8.25 9-8.25s9 3.694 9 8.25z" />
            </svg>
            WhatsApp Setup
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">WhatsApp on Event Schedule is inbound-first: an organizer messages your Twilio number, the app creates the event, and the confirmation goes back on the same thread. To accept those messages, your Twilio number has to be registered as a WhatsApp sender and pointed at the app's webhook.</p>

        <h3 class="doc-subheading">Register as a WhatsApp sender</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In the Twilio Console, go to <strong class="text-gray-900 dark:text-white">Messaging</strong> &rarr; <strong class="text-gray-900 dark:text-white">Senders</strong> &rarr; <strong class="text-gray-900 dark:text-white">WhatsApp Senders</strong></li>
            <li>Click <strong class="text-gray-900 dark:text-white">Add WhatsApp Sender</strong> and follow the guided setup</li>
            <li>Submit your business profile for Meta approval</li>
            <li>Once approved, your number can send and receive WhatsApp messages</li>
        </ol>

        <h3 class="doc-subheading">Configure the webhook URL</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Set the incoming message webhook so Event Schedule can receive WhatsApp messages:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In the Twilio Console, go to your WhatsApp Sender settings</li>
            <li>Set the webhook URL to: <code class="doc-inline-code">https://yourdomain.com/api/whatsapp/webhook</code></li>
            <li>Set the HTTP method to <strong class="text-gray-900 dark:text-white">POST</strong></li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">The webhook lives on your main application domain rather than on a tenant subdomain, it needs no authentication, and it accepts at most 60 requests per minute.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Every request is signature checked</div>
            <p>The app recomputes Twilio's <code class="doc-inline-code">X-Twilio-Signature</code> from your auth token and the exact URL Twilio called. If they do not match, the request is dropped silently and an empty reply is returned, so the URL you register has to match the URL the app sees, scheme included. If a proxy or load balancer terminates TLS in front of the app, make sure it is trusted so the app still builds an <code class="doc-inline-code">https://</code> URL.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">No message templates to submit</div>
            <p>WhatsApp only allows free-form messages within 24 hours of the recipient's last message. Every message Event Schedule sends is an immediate reply to a message that has just arrived, so it is always inside that window. There are no campaigns or reminders to schedule and no message templates to get approved.</p>
        </div>

        <h3 class="doc-subheading">Creating events by WhatsApp <x-doc-badge plan="enterprise" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Once the sender and the webhook are live, an organizer can send event details as text, or a photo of a flyer or poster, and AI parses the content into an event on their default schedule.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">An incoming message has to satisfy all of the following, or the sender gets an explanatory reply instead of an event:</p>
        <ul class="doc-list mb-6">
            <li>The sending number belongs to a user account whose phone number has been <a href="#phone-verification" class="doc-link">verified</a>.</li>
            <li>That user has a <strong class="text-gray-900 dark:text-white">Default schedule</strong> set in their account settings, or is an editor of exactly one schedule.</li>
            <li>The message carries text, an image, or both. Only the first attachment is read, and only if it is an image.</li>
            <li>Your deployment has an AI key configured (<code class="doc-inline-code">GEMINI_API_KEY</code>, or <code class="doc-inline-code">OPENAI_API_KEY</code>). It is the same parser used by AI import in the admin portal.</li>
        </ul>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The reply carries the new event's name, link and date. If the parser recognises the event as one that already exists, it replies with a link to it rather than creating a duplicate.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-4">For user-facing instructions on creating events via WhatsApp, see the <a href="{{ route('marketing.docs.creating_events') }}#whatsapp" class="doc-link">Creating Events guide</a>.</p>
    </section>

    <!-- Testing -->
    <section id="testing" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
            </svg>
            Testing
        </h2>

        <h3 class="doc-subheading">Testing SMS</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Requesting a phone verification code is the quickest end-to-end test, because the code is sent immediately rather than queued. Watch the Laravel log while you do it:</p>
        <pre class="doc-code-block"><code>tail -f storage/logs/laravel.log</code></pre>
        <p class="text-gray-600 dark:text-gray-300 mb-4 mt-4">If a variable is missing, the app logs <code class="doc-inline-code">Twilio SMS not configured, skipping SMS send</code> (or <code class="doc-inline-code">Twilio not configured, skipping WhatsApp send</code>) and carries on. If Twilio is configured but rejects the send, the failure is logged with the HTTP status and Twilio's response body, which usually names the problem outright.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Invitation messages are dispatched to the queue instead, so a stopped queue worker looks exactly like a broken Twilio account. Check the worker before you check the credentials.</p>

        <h3 class="doc-subheading">Testing WhatsApp</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Twilio provides a WhatsApp sandbox for testing without requiring Meta approval:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>In the Twilio Console, go to <strong class="text-gray-900 dark:text-white">Messaging</strong> &rarr; <strong class="text-gray-900 dark:text-white">Try it out</strong> &rarr; <strong class="text-gray-900 dark:text-white">Send a WhatsApp message</strong></li>
            <li>Follow the instructions to join the sandbox by sending the join code from your phone to the Twilio sandbox number</li>
            <li>Point the sandbox's incoming-message webhook at <code class="doc-inline-code">https://yourdomain.com/api/whatsapp/webhook</code> using <strong class="text-gray-900 dark:text-white">POST</strong></li>
            <li>Set <code class="doc-inline-code">TWILIO_FROM_NUMBER</code> to the sandbox number while you are testing. Replies are always sent from whatever that variable holds, so a mismatch shows up as an event that gets created without any confirmation coming back.</li>
        </ol>

        <h3 class="doc-subheading">Troubleshooting</h3>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">No reply at all.</strong> The signature check almost certainly failed, and by design that produces an empty response rather than an error. Compare the URL in Twilio's Console debugger with the URL the app builds, and confirm the auth token matches.</li>
            <li><strong class="text-gray-900 dark:text-white">"Your phone number is not linked to an account."</strong> The sending number does not match a user with a verified phone number. Verify it in account settings first.</li>
            <li><strong class="text-gray-900 dark:text-white">"No default schedule set."</strong> The user edits more than one schedule and has not chosen a <strong class="text-gray-900 dark:text-white">Default schedule</strong> in account settings.</li>
            <li><strong class="text-gray-900 dark:text-white">"Could not create event."</strong> The AI parser returned nothing usable, or no AI key is configured. The log entry for the request has the detail.</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>The Twilio sandbox is for development only. For production use, you must complete the WhatsApp sender registration and Meta approval process.</p>
        </div>
    </section>
</x-docs-page>
