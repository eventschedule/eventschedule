<x-docs-page
    key="getting-started"
    title="Getting Started: Account and First Schedule - Event Schedule"
    description="Create your Event Schedule account, set up your first schedule and choose its type, then share it and sell tickets. Free, no credit card, no time limit."
    lede="Go from zero to a live event calendar in a few minutes. No credit card required, and the free plan has no time limit."
>
    <x-slot:toc>
        <x-doc-nav-link href="#create-account">Create Your Account</x-doc-nav-link>
        <x-doc-nav-link href="#create-schedule">Create Your Schedule</x-doc-nav-link>
        <x-doc-nav-link href="#schedule-types">Schedule Types</x-doc-nav-link>
        <x-doc-nav-link href="#customize">Customize Your Schedule</x-doc-nav-link>
        <x-doc-nav-group label="Your Dashboard" href="#dashboard">
            <x-doc-nav-link href="#setup-guide">The setup guide</x-doc-nav-link>
            <x-doc-nav-link href="#dashboard-states">Nothing to count yet</x-doc-nav-link>
        </x-doc-nav-group>
        <x-doc-nav-link href="#faq">FAQ</x-doc-nav-link>
        <x-doc-nav-link href="#next-steps">Next Steps</x-doc-nav-link>
    </x-slot:toc>

    <!-- Create Account -->
    <section id="create-account" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
            </svg>
            Create Your Account
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Signing up is free and takes no credit card. All you need is an email address you can check right away, because Event Schedule confirms it with a code before the account is created.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <a href="{{ app_url('/sign_up') }}" class="doc-link">the sign-up page</a>, type your email address in the <strong class="text-gray-900 dark:text-white">Email</strong> field, and tick <strong class="text-gray-900 dark:text-white">"I accept the Terms of Service and Privacy Policy"</strong>.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">"Continue"</strong>. A six-digit code is emailed to that address and stays valid for 10 minutes. If the address looks like a typo of a common provider (gmial.com, say), the page offers the corrected address first, so no code is sent to an inbox nobody reads.</li>
            <li>Type or paste the code into the six boxes. It is checked straight away: the boxes turn green when it is right, or red, with the reason, when it is not.</li>
            <li>Enter your <strong class="text-gray-900 dark:text-white">Full Name</strong> and a <strong class="text-gray-900 dark:text-white">Password</strong> of at least 8 characters, then click <strong class="text-gray-900 dark:text-white">"Create Account"</strong>.</li>
            <li>You are signed in immediately, with the email already verified, and Event Schedule asks you to pick a schedule type.</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">If the code does not arrive, <strong class="text-gray-900 dark:text-white">"Didn't receive the code? Resend code"</strong> appears under the boxes after 30 seconds, with a note on where else to look (the spam folder, and the email's subject line, which carries the code too). You can request up to five codes per hour for the same address. Typed the wrong address? Click <strong class="text-gray-900 dark:text-white">"Use a different email"</strong> to go back a step. Your timezone and language are detected from your browser, so there is nothing to choose during sign-up.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Already confirmed an email sign-up on someone's schedule? That set up an account on your address with no password yet. Sign up with the same address and the form completes that account, so the schedules you follow come with it. If you added a password back then, sign in instead.</p>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Signing up with Google is quicker. Where the page offers <strong class="text-gray-900 dark:text-white">"Continue with Google"</strong>, click it instead and there is no code to enter: Google has already confirmed the address, so the account is created verified and you land straight on the schedule-type chooser. Either way, your data is yours, and we never share or sell your information.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhosted installs work differently</div>
            <p>On a selfhosted server the sign-up page doubles as the setup wizard: it asks for your MySQL details first, and the first account created there becomes the instance admin. After that, sign-up is closed unless you enable <code class="doc-inline-code">ALLOW_REGISTRATION</code>. See <a href="{{ route('marketing.docs.selfhost.installation') }}#user-accounts" class="doc-link">User Accounts and Registration</a> for the details.</p>
        </div>
    </section>

    <!-- Create Schedule -->
    <section id="create-schedule" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Create Your Schedule
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A schedule is your event calendar: it is where your events live, and it gets its own public page that you share with your audience. Creating one is a short form with one field to fill in, <strong class="text-gray-900 dark:text-white">Schedule Name</strong>, plus <strong class="text-gray-900 dark:text-white">Street Address</strong> on a Venue schedule. From there a setup guide walks you to a live page: it shows as a small ring above the form, and its first line says <strong class="text-gray-900 dark:text-white">"Two steps to your live schedule"</strong>.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Choose the type.</strong> Straight after sign-up you get a welcome screen that asks <strong class="text-gray-900 dark:text-white">"What best describes you?"</strong> over three cards, Talent, Venue and Curator. Pick the one that fits (see <a href="#schedule-types" class="doc-link">Schedule Types</a> below). If you would rather look around first, click <strong class="text-gray-900 dark:text-white">"Skip for now"</strong> and come back later.</li>
            <li><strong class="text-gray-900 dark:text-white">Name it.</strong> The form asks for a <strong class="text-gray-900 dark:text-white">Schedule Name</strong> and, for a Venue, a <strong class="text-gray-900 dark:text-white">Street Address</strong>. That is all it needs. For a Talent schedule the name is prefilled with your own name.</li>
            <li><strong class="text-gray-900 dark:text-white">Check the two lines underneath.</strong> The first is your account's email address, which becomes the schedule's contact email. The second says which timezone the schedule's times are in, with <strong class="text-gray-900 dark:text-white">Change</strong> beside it. If your device is set to a different timezone from your account, the picker opens by itself with the device's timezone selected. The email can be changed afterwards, along with everything else, on the schedule form.</li>
            <li><strong class="text-gray-900 dark:text-white">Click "Save and continue".</strong> Event Schedule creates the schedule and, because it is your first, takes you straight to adding an event, with the setup guide alongside. Everything else (description, images, colours, integrations) is optional and waits for you on the schedule form. See <a href="#customize" class="doc-link">Customize Your Schedule</a> for what each section holds.</li>
        </ol>

        <x-doc-screenshot id="getting-started--create-form" alt="The short form a new Venue schedule starts on: Schedule Name, Street Address, the account email and timezone lines, and the save button" />

        <p class="text-gray-600 dark:text-gray-300 mb-6">Already have an account and want another schedule? Use the <strong class="text-gray-900 dark:text-white">"New Schedule"</strong> menu at the top of the dashboard (on a phone it is behind the three-dot button beside Add Event): it offers the same three types and opens the same form, where the button says <strong class="text-gray-900 dark:text-white">"Save"</strong> and you land on the new schedule's own page.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Your schedule URL</div>
            <p>You do not pick the URL on the create form. It is generated from the schedule name, so a schedule called "Blue Note Jazz" becomes <code class="doc-inline-code">{{ route('role.view_guest', ['subdomain' => 'blue-note-jazz']) }}</code>. To change it afterwards, open <strong>Edit Schedule &rarr; Settings</strong> and click <strong>Edit</strong> beside <strong>Schedule URL</strong>. Do it early: changing the URL breaks any link you have already shared.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Schedules are public, and the create form says so: <strong class="text-gray-900 dark:text-white">your schedule will be publicly visible</strong>. If you want to work on events before anyone sees them, save them as <a href="{{ route('marketing.docs.creating_events') }}#draft" class="doc-link">Drafts</a> rather than trying to hide the schedule.</p>

        <h3 id="contact-email" class="doc-subheading">The contact email has to be verified</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">On eventschedule.com a schedule is only live while its contact email is confirmed. A new schedule takes your account's email, which you confirmed when you signed up, so it is live from the start. If you change the address later (<strong class="text-gray-900 dark:text-white">Edit Schedule &rarr; Details &rarr; Contact Info</strong>), Event Schedule emails the new one a verification link and shows a <strong class="text-gray-900 dark:text-white">"Please verify the email address"</strong> banner with a <strong class="text-gray-900 dark:text-white">Resend Email</strong> button on the schedule's pages until you click it. Until then the public page shows visitors a page-not-found, you and your team are taken into the app when you open it signed in, and the schedule's address with its <strong class="text-gray-900 dark:text-white">View</strong> link is left out of the top of its pages. (A verified phone number counts too, but the email is the route almost everyone takes.) A selfhosted install treats the address as verified.</p>
    </section>

    <!-- Schedule Types -->
    <section id="schedule-types" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Schedule Types
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">There are exactly three schedule types. The quickest way to choose is to ask what stays the same across your events: the performer, the place, or neither.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Type</th>
                        <th>Best For</th>
                        <th>Pattern</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Talent</span></td>
                        <td>Musicians, DJs, performers, speakers</td>
                        <td>Your events at various venues</td>
                        <td>A band listing their upcoming shows</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Venue</span></td>
                        <td>Bars, clubs, theaters, event spaces</td>
                        <td>Various events at your venue</td>
                        <td>A club listing everything on its stage</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Curator</span></td>
                        <td>Promoters, bloggers, community organizers</td>
                        <td>Various events at various venues</td>
                        <td>A local music blog listing concerts in the area</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">The type is not just a label: it changes which tabs the schedule's own page has and which sections its form offers.</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Talent</strong> schedules prefill the schedule name with your own name, and add an <a href="{{ route('marketing.docs.managing_schedules') }}#availability" class="doc-link">Availability</a> tab to the admin panel for marking the days you can be booked <x-doc-badge plan="enterprise" link /></li>
            <li><strong class="text-gray-900 dark:text-white">Venue</strong> schedules get an <a href="{{ route('marketing.docs.creating_schedules') }}#address" class="doc-link">Address</a> section, so visitors can find you and your event pages can show a map. Street Address is the one extra required field. They also get a <a href="{{ route('marketing.docs.allocated_seating') }}#build" class="doc-link">Seating plans</a> tab for drawing a reusable plan of the room and selling reserved seats from it <x-doc-badge plan="enterprise" link /></li>
            <li><strong class="text-gray-900 dark:text-white">Curator</strong> schedules get an <a href="{{ route('marketing.docs.creating_schedules') }}#event-sources" class="doc-link">Event Sources</a> section that pulls in every event published by the talent and venue schedules you pick, a <a href="{{ route('marketing.docs.managing_schedules') }}#videos" class="doc-link">Videos</a> tab for matching YouTube videos to the talent you list, and they ask visitors to sign in before submitting an event. That is the <strong class="text-gray-900 dark:text-white">Require Account</strong> switch in the <strong class="text-gray-900 dark:text-white">Requests</strong> row of the form's Engagement section, which starts on for curators and off for venues. Talent schedules do not have it.</li>
        </ul>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">The type is fixed once the schedule is saved</div>
            <p>There is no setting for changing a schedule from Talent to Venue later. While you are still on the create form for your <em>first</em> schedule, the <strong>"Choose a different type"</strong> link beside the save button takes you back to the chooser. After saving, create a second schedule of the other type instead: one account can run all three.</p>
        </div>
    </section>

    <!-- Customize -->
    <section id="customize" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
            </svg>
            Customize Your Schedule
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Everything below lives on one page, the schedule form. Choose the schedule in the sidebar and click <strong class="text-gray-900 dark:text-white">"Edit Schedule"</strong> at the top of its page, then use the list on the left to move between sections. What everyone fills in is on the page as a section opens; the rest sits in rows that open in place and say their current setting while closed. One <strong class="text-gray-900 dark:text-white">Save</strong>, in the bar at the bottom, saves every section. Nothing here is required to publish, so start with Details and Style and come back later. The table follows the form's order.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>What you set there</th>
                        <th>Shown for</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Details</span></td>
                        <td>The name, Short Description, the Markdown Description and an announcement bar <x-doc-badge plan="pro" link />. Two rows under them: <strong class="text-gray-900 dark:text-white">Language and time</strong> holds the language, an optional second language to offer translations in, the timezone your event times are read in, and a 24-hour clock switch. <strong class="text-gray-900 dark:text-white">Contact Info</strong> holds the email, phone and website.</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Event Sources</span></td>
                        <td>Pick talent and venue schedules, and every event they publish appears on yours automatically.</td>
                        <td>Curator schedules</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Address</span></td>
                        <td>Street address, city, state, postal code and country, with <strong class="text-gray-900 dark:text-white">View Map</strong> and <strong class="text-gray-900 dark:text-white">Validate Address</strong>. The address is shown on your page and on your events, where it can also draw a map.</td>
                        <td>Venue schedules</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Style</span></td>
                        <td>Your <strong class="text-gray-900 dark:text-white">Square Profile Image</strong> (your logo), accent colour and font, with a preview of your page beside them. Four rows under them, in the order of your page: <strong class="text-gray-900 dark:text-white">Header</strong> (the header style and header image), <strong class="text-gray-900 dark:text-white">Background</strong>, <strong class="text-gray-900 dark:text-white">Events</strong> (the default layout and the event animation) and <strong class="text-gray-900 dark:text-white">Custom CSS</strong>. Full reference in <a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Schedule Styling</a>. Custom CSS requires <x-doc-badge plan="pro" link /></td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Gallery</span></td>
                        <td>Photos of your venue, past events or your team, shown on your schedule page. Requires <x-doc-badge plan="pro" link /></td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Videos &amp; Links</span></td>
                        <td>Your featured YouTube videos and your social links. Each social link also answers at a short address on your schedule URL, which counts its clicks.</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Customize</span></td>
                        <td>Four rows. <strong class="text-gray-900 dark:text-white">Sub-schedules</strong> creates colour-coded parts such as "Live Music", "DJ Nights" or "Comedy" that visitors can filter by; they organize and colour-code, and do not hide anything. The other three are <strong class="text-gray-900 dark:text-white">Custom Fields</strong>, <strong class="text-gray-900 dark:text-white">Categories</strong> and <strong class="text-gray-900 dark:text-white">Custom Labels</strong>. Custom fields and custom labels require <x-doc-badge plan="pro" link /></td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Settings</span></td>
                        <td>The <strong class="text-gray-900 dark:text-white">Schedule URL</strong>, with rows for <strong class="text-gray-900 dark:text-white">Notifications</strong> and <strong class="text-gray-900 dark:text-white">Advanced</strong>.</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Engagement</span></td>
                        <td>Event requests from visitors, fan content, post-event feedback, carpooling and sponsor logos, each in its own row.</td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Gift Cards</span></td>
                        <td>Sell balance-tracked gift cards that buyers send to a recipient by email. Requires <x-doc-badge plan="pro" link /></td>
                        <td>Every schedule</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Auto Import</span></td>
                        <td>Read events from a list of web pages once a day. An optional list of cities keeps only the events held there.</td>
                        <td>Selfhosted installs</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Integrations</span></td>
                        <td>Rows for <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong>, <strong class="text-gray-900 dark:text-white">CalDAV Calendar</strong> and <strong class="text-gray-900 dark:text-white">Calendar text and feeds</strong>, with <strong class="text-gray-900 dark:text-white">Email Settings</strong> first on eventschedule.com.</td>
                        <td>Every schedule</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6 mt-6">Every one of these sections is covered field by field in <a href="{{ route('marketing.docs.creating_schedules') }}#schedule-form" class="doc-link">Creating Schedules</a>.</p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">What the free plan leaves out</div>
            <p>Almost nothing on this page needs a paid plan. The free plan runs unlimited events, syncs calendars, takes unlimited RSVPs and free registrations, embeds your calendar and makes event graphics. Putting a price on a ticket is what needs Pro, and Pro also adds the live check-in dashboard (scanning tickets at the door is free on every plan), custom fields, custom CSS and removing the Event Schedule branding. There is no platform fee on any plan, and money always goes to your own Stripe, PayPal or other account. Enterprise adds custom domains, extra team members, availability and the AI generation features. Compare them on the <a href="{{ route('marketing.pricing') }}" class="doc-link">pricing page</a>. A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install resolves to Enterprise, so nothing is held back there.</p>
        </div>
    </section>

    <!-- Dashboard -->
    <section id="dashboard" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Your Dashboard
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">The dashboard is the page you land on after signing in, and the first entry in the sidebar. It covers every schedule you own or administer. Each number on it is a link to the page that explains it.</p>

        <x-doc-screenshot id="getting-started--dashboard" alt="Event Schedule dashboard: Customize, New Schedule and Add Event at the top, four numbers for views, followers, revenue and Realtime, and a row for each schedule" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">From top to bottom:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Part</th>
                        <th>What it shows</th>
                        <th>Where it leads</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Title row</td>
                        <td><strong class="text-gray-900 dark:text-white">View page</strong> (with one schedule), <strong class="text-gray-900 dark:text-white">Customize</strong>, <strong class="text-gray-900 dark:text-white">New Schedule</strong> and, last, <strong class="text-gray-900 dark:text-white">Add Event</strong>, the main button. On a phone the first three are behind a three-dot button.</td>
                        <td>Add Event opens the event form. With several schedules it asks which one first.</td>
                    </tr>
                    <tr>
                        <td>Needs attention</td>
                        <td>One chip for each thing waiting on you, such as event requests or an overdue payment. With several schedules a chip says which one. Shown only while something is waiting.</td>
                        <td>The page where you deal with it</td>
                    </tr>
                    <tr>
                        <td>Views</td>
                        <td>Page views of your schedules in the period, a bar for every day, and the change against the same number of days before</td>
                        <td>Analytics</td>
                    </tr>
                    <tr>
                        <td>Followers</td>
                        <td>Followers in total, a bar for every day, and how many are new in the period</td>
                        <td>Your followers. With several schedules, the list of schedules below, where each one's are a click away.</td>
                    </tr>
                    <tr>
                        <td>Revenue</td>
                        <td>Money taken in the period, in the currency it was taken in, a bar for every day, and the number of sales</td>
                        <td>Sales</td>
                    </tr>
                    <tr>
                        <td>Realtime</td>
                        <td>Page views of your pages in the last 5 minutes and visitors on them now, or <strong class="text-gray-900 dark:text-white">Quiet right now</strong>, with a bar for every minute of the last half hour. Where live traffic is not switched on for the site, this place shows your number of upcoming events.</td>
                        <td>The <a href="{{ route('marketing.docs.analytics') }}#realtime" class="doc-link">Realtime tab</a> of Analytics. Upcoming events opens the calendar below, while the calendar is on the page.</td>
                    </tr>
                    <tr>
                        <td>Your schedules</td>
                        <td>With two or more schedules, a row for each with its upcoming events, views, followers and visitors now</td>
                        <td>That schedule's own page</td>
                    </tr>
                    <tr>
                        <td>Coming up</td>
                        <td>Your next five events, soonest first. A weekly or other repeating event is listed once, at its next date. Each row shows tickets sold or sign-ups and its views.</td>
                        <td>The event. An event on today that takes tickets or sign-ups also offers <strong class="text-gray-900 dark:text-white">Check-in</strong> <x-doc-badge plan="pro" link /></td>
                    </tr>
                    <tr>
                        <td>Recent Activity</td>
                        <td>The latest sales, new followers and newsletters</td>
                        <td><strong class="text-gray-900 dark:text-white">All sales</strong>, at the foot of the card</td>
                    </tr>
                    <tr>
                        <td>Calendar</td>
                        <td>The month view of all your events</td>
                        <td>Each event</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6"><strong class="text-gray-900 dark:text-white">Customize</strong> chooses the period the numbers cover (7, 14 or 30 days; 30 unless you change it) and which cards sit under them: Coming up, Recent Activity, Top Events, Traffic Sources, Newsletters, Boosts and the Calendar. The four numbers are always shown.</p>

        <h3 id="setup-guide" class="doc-subheading">The setup guide</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">For the first 30 days after you create your first schedule, a <strong class="text-gray-900 dark:text-white">Setup guide</strong> card sits under the dashboard's title, with a picture of your own page beside its steps. Creating your account and your schedule are already ticked. The next step, your first published event, is what makes the page live. Three more follow, and each can be skipped:</p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Add more events</strong> - done at three events, or one that repeats</li>
            <li><strong class="text-gray-900 dark:text-white">Share your schedule</strong> - done when you copy your link or the code that puts your events on your own website</li>
            <li><strong class="text-gray-900 dark:text-white">Tickets or free entry?</strong> - done when an event of yours has tickets or sign-up, or you answer that it needs none. A curator that only lists other schedules' events is not asked</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A ring fills as you go, and a line says how close you are, for example <strong class="text-gray-900 dark:text-white">One step from live</strong>. On the other pages of that schedule it is a small ring that leads back to the card, and a <strong class="text-gray-900 dark:text-white">Setup guide</strong> line under Dashboard in the sidebar does the same from anywhere. <strong class="text-gray-900 dark:text-white">Hide setup guide</strong> puts it away, and the sidebar line brings it back. Once the guide is gone, the same place on the dashboard holds <strong class="text-gray-900 dark:text-white">Next steps</strong>: suggestions such as publishing a draft or connecting a payment method, each of which you can dismiss.</p>

        <h3 id="dashboard-states" class="doc-subheading">When there is nothing to count yet</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Until your pages have had a first view, follower or sale, the numbers are left out: you see your event and, where live traffic is switched on for the site, a card that waits for your first visitor. If you only have view access to a schedule, the dashboard lists the schedules you were given and what is coming up on them, without numbers. If you run no schedule, it shows the tickets you hold and what the schedules you follow have coming up.</p>
    </section>

    <!-- FAQ -->
    <section id="faq" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
            </svg>
            Frequently Asked Questions
        </h2>

        <div class="doc-faqs">
            <x-doc-faq question="Can I have multiple schedules?">
                <p>Yes. One account can own up to 50 schedules on eventschedule.com, and a selfhosted install has no limit. This is how you run several bands, venues or organizations side by side, and how you mix types, since a schedule's type cannot be changed after it is saved.</p>
            </x-doc-faq>
            <x-doc-faq question="How do I change my schedule URL?">
                <p>Choose the schedule in the sidebar, click <strong class="text-gray-900 dark:text-white">"Edit Schedule"</strong>, open the <strong class="text-gray-900 dark:text-white">Settings</strong> section and click <strong class="text-gray-900 dark:text-white">Edit</strong> beside <strong class="text-gray-900 dark:text-white">Schedule URL</strong>. See <a href="{{ route('marketing.docs.creating_schedules') }}#settings-general" class="doc-link">Schedule URL</a> for details. Changing it breaks existing links, so do it before you start sharing.</p>
            </x-doc-faq>
            <x-doc-faq question="What is the difference between the schedule types?">
                <p><strong class="text-gray-900 dark:text-white">Talent</strong> is your events at various venues, and adds an Availability tab (Enterprise). <strong class="text-gray-900 dark:text-white">Venue</strong> is various events at your venue, and adds an Address section and a Seating plans tab (Enterprise). <strong class="text-gray-900 dark:text-white">Curator</strong> is various events at various venues, and adds Event Sources and a Videos tab. Pick carefully: the type is fixed once you save.</p>
            </x-doc-faq>
            <x-doc-faq question="Can I import events from my existing calendar?">
                <p>Yes, and it is free. Connect a calendar under <strong class="text-gray-900 dark:text-white">Edit Schedule &rarr; Integrations</strong>, which has a row for <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong> and <strong class="text-gray-900 dark:text-white">CalDAV Calendar</strong>. For each one you pick a direction: push your events to the calendar, pull its events in, or both. For a one-time copy instead, <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">import events</a> by pasting a link to a calendar or an events page, some text or a photo of a flyer.</p>
            </x-doc-faq>
            <x-doc-faq question="Is Event Schedule free?">
                <p>Yes, with no time limit and no credit card. The free plan covers unlimited events, your own schedule URL, calendar sync, analytics, unlimited RSVP with capacity limits, embedding your calendar, one appointment type, and 10 newsletter emails a month (each recipient counts as one email, so one send to 100 followers uses 100). Selling a ticket that carries a price is a Pro feature: Pro is {{ plan_price($proMonthly) }} a month, Enterprise is {{ plan_price($entMonthly) }} a month for custom domains and team features, and both start with a 7-day free trial. There is no platform fee on any plan.</p>
            </x-doc-faq>
            <x-doc-faq question="How do I get paid for tickets?">
                <p>Connect your own account under <strong class="text-gray-900 dark:text-white">Settings &rarr; Payment Methods</strong>: Stripe, PayPal, Payfast (South African rand only) or Invoice Ninja. A payment link, or cash at the door, works too. You pick the method on each event, and the money goes to your own account with no platform fee on any plan. A Stripe or PayPal sale can be refunded in full or in part from the Sales page, and the money goes back through the provider; any other sale can be marked as refunded there. See <a href="{{ route('marketing.docs.account_settings') }}#payments" class="doc-link">Payment Methods</a> and <a href="{{ route('marketing.docs.tickets') }}#managing-sales" class="doc-link">Managing Sales</a>.</p>
            </x-doc-faq>
            <x-doc-faq question="How will people hear about my new events?">
                <p>Share your schedule link, or embed the calendar on your own site. Visitors can leave their name and email in the sign-up panel on your schedule page, and once they confirm they get a digest of the new events your schedule publishes, at most one every few days. Anyone can also subscribe to your calendar feed, which keeps your events current in their own calendar app, and if you switch on the &ldquo;Notify me&rdquo; card, a visitor can press <strong class="text-gray-900 dark:text-white">Tell me when tickets go on sale</strong> on an event that is not on sale yet to hear about that event alone. See <a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a>.</p>
            </x-doc-faq>
            <x-doc-faq question="Someone already made a page for my act or venue. How do I claim it?">
                <p>If a promoter, venue or curator listed you on an event before you had an account, Event Schedule created a page for you at that moment. Open it and press <strong class="text-gray-900 dark:text-white">Claim this page</strong>. Signed out, you are taken to sign up, and signing up with the email address on the page hands it to you. Signed in with a verified account on that email address or phone number, you confirm and the page is yours; signed in with a different one, the page shows the contact it answers to in masked form, so you know which account to use. Claiming makes you the owner, and the schedules that already listed you keep listing you. If you cannot reach that contact, ask whoever listed you to correct it. If the page is not about you at all, press <strong class="text-gray-900 dark:text-white">This is not me</strong> instead. See <a href="{{ route('marketing.docs.creating_events') }}#claim" class="doc-link">Pages Created for Others</a>.</p>
            </x-doc-faq>
            <x-doc-faq question="My schedule page will not open. What is wrong?">
                <p>Almost always an <a href="#contact-email" class="doc-link">unverified contact email</a>. A schedule stays offline until its email address is confirmed, so check your inbox for the verification link, or use the <strong class="text-gray-900 dark:text-white">Resend Email</strong> button on the yellow banner at the top of the schedule's pages.</p>
            </x-doc-faq>
        </div>
    </section>

    <!-- Next Steps -->
    <section id="next-steps" class="doc-section">
        <h2 class="doc-heading">
            <svg aria-hidden="true" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-500 dark:text-gray-400 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 15l3-3m0 0l-3-3m3 3h-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Next Steps
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Your schedule has its address. What makes it worth sharing is an event on it, which is where the <a href="#setup-guide" class="doc-link">setup guide</a> sends you next.</p>

        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Add your first events</a> - Create events by hand, clone them, or import them</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="doc-link">Configure your schedule</a> - Settings, sub-schedules, event requests, and calendar sync</li>
            <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Style your schedule</a> - Colors, fonts, headers, and backgrounds</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Share your schedule</a> - Embed it on your website and post it to social media</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Set up ticketing</a> - Sell tickets through Stripe, PayPal or another payment method with no platform fee; a ticket that carries a price needs Pro</li>
            <li><a href="{{ route('marketing.docs.account_settings') }}" class="doc-link">Account settings</a> - Your profile, password, payments, and API access</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                {
                    "@type": "Question",
                    "name": "Can I have multiple schedules?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes. One account can own up to 50 schedules on eventschedule.com, and a selfhosted install has no limit. This is how you run several bands, venues or organizations side by side, and how you mix types, since a schedule's type cannot be changed after it is saved."
                    }
                },
                {
                    "@type": "Question",
                    "name": "How do I change my schedule URL?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Choose the schedule in the sidebar, click Edit Schedule, open the Settings section and click Edit beside Schedule URL. Changing it breaks existing links, so do it before you start sharing."
                    }
                },
                {
                    "@type": "Question",
                    "name": "What is the difference between the schedule types?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Talent is your events at various venues, and adds an Availability tab (Enterprise). Venue is various events at your venue, and adds an Address section and a Seating plans tab (Enterprise). Curator is various events at various venues, and adds Event Sources and a Videos tab. Pick carefully: the type is fixed once you save."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Can I import events from my existing calendar?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes, and it is free. Connect a calendar under Edit Schedule and then Integrations, which has a row for Google Calendar, Outlook Calendar and CalDAV Calendar. For each one you pick a direction: push your events to the calendar, pull its events in, or both. For a one-time copy instead, use Import Events and paste a link to a calendar or an events page, some text or a photo of a flyer."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Is Event Schedule free?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes, with no time limit and no credit card. The free plan covers unlimited events, your own schedule URL, calendar sync, analytics, unlimited RSVP with capacity limits, embedding your calendar, one appointment type, and 10 newsletter emails a month (each recipient counts as one email). Selling a ticket that carries a price is a Pro feature: Pro is {{ plan_price($proMonthly) }} a month, Enterprise is {{ plan_price($entMonthly) }} a month for custom domains and team features, and both start with a 7-day free trial. There is no platform fee on any plan."
                    }
                },
                {
                    "@type": "Question",
                    "name": "How do I get paid for tickets?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Connect your own account under Settings and then Payment Methods: Stripe, PayPal, Payfast (South African rand only) or Invoice Ninja. A payment link, or cash at the door, works too. You pick the method on each event, and the money goes to your own account with no platform fee on any plan. A Stripe or PayPal sale can be refunded in full or in part from the Sales page, and the money goes back through the provider; any other sale can be marked as refunded there."
                    }
                },
                {
                    "@type": "Question",
                    "name": "How will people hear about my new events?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Share your schedule link, or embed the calendar on your own site. Visitors can leave their name and email in the sign-up panel on your schedule page, and once they confirm they get a digest of the new events your schedule publishes, at most one every few days. Anyone can also subscribe to your calendar feed, which keeps your events current in their own calendar app, and if you switch on the “Notify me” card, a visitor can press Tell me when tickets go on sale on an event that is not on sale yet to hear about that event alone."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Someone already made a page for my act or venue. How do I claim it?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "If a promoter, venue or curator listed you on an event before you had an account, Event Schedule created a page for you at that moment. Open it and press Claim this page. Signed out, you are taken to sign up, and signing up with the email address on the page hands it to you. Signed in with a verified account on that email address or phone number, you confirm and the page is yours; signed in with a different one, the page shows the contact it answers to in masked form, so you know which account to use. Claiming makes you the owner, and the schedules that already listed you keep listing you. If you cannot reach that contact, ask whoever listed you to correct it. If the page is not about you at all, press This is not me instead."
                    }
                },
                {
                    "@type": "Question",
                    "name": "My schedule page will not open. What is wrong?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Almost always an unverified contact email. A schedule stays offline until its email address is confirmed, so check your inbox for the verification link, or use the Resend Email button on the yellow banner at the top of the schedule's pages."
                    }
                }
            ]
        }
        </script>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "Getting Started with Event Schedule",
            "description": "Learn how to create your account, set up your first schedule, and start sharing events with Event Schedule.",
            "totalTime": "PT5M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Create Your Account",
                    "text": "Enter your email on the sign-up page, accept the terms and click Continue, then type the six-digit code from the email, your full name and a password of at least 8 characters, and click Create Account. Continuing with Google skips the code.",
                    "url": "{{ url(route('marketing.docs.getting_started')) }}#create-account"
                },
                {
                    "@type": "HowToStep",
                    "name": "Create Your Schedule",
                    "text": "Choose Talent, Venue or Curator, enter the schedule name, and click Save and continue. The schedule takes your account's email as its contact email. The schedule URL is generated from the name and can be changed later in the form's Settings section.",
                    "url": "{{ url(route('marketing.docs.getting_started')) }}#create-schedule"
                },
                {
                    "@type": "HowToStep",
                    "name": "Choose Your Schedule Type",
                    "text": "Talent suits performers, Venue suits event spaces, and Curator suits promoters and organizers. Each type has its own tabs and form sections, and the type is fixed once the schedule is saved.",
                    "url": "{{ url(route('marketing.docs.getting_started')) }}#schedule-types"
                },
                {
                    "@type": "HowToStep",
                    "name": "Customize Your Schedule",
                    "text": "Choose the schedule in the sidebar and click Edit Schedule to add your logo and description, set the language and timezone, add sub-schedules, and pick your colors and fonts.",
                    "url": "{{ url(route('marketing.docs.getting_started')) }}#customize"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
