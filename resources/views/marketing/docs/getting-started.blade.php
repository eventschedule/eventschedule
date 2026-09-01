<x-docs-page
    key="getting-started"
    description="Get started with Event Schedule. Learn how to create your account, set up your first schedule, and start sharing events."
    lede="Go from zero to a live event calendar in a few minutes. No credit card required, and the free plan has no time limit."
>
    <x-slot:toc>
        <x-doc-nav-link href="#create-account">Create Your Account</x-doc-nav-link>
        <x-doc-nav-link href="#create-schedule">Create Your Schedule</x-doc-nav-link>
        <x-doc-nav-link href="#schedule-types">Schedule Types</x-doc-nav-link>
        <x-doc-nav-link href="#customize">Customize Your Schedule</x-doc-nav-link>
        <x-doc-nav-link href="#faq">FAQ</x-doc-nav-link>
        <x-doc-nav-link href="#next-steps">Next Steps</x-doc-nav-link>
    </x-slot:toc>

    <!-- Create Account -->
    <section id="create-account" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19 7.5v3m0 0v3m0-3h3m-3 0h-3m-2.25-4.125a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zM4 19.235v-.11a6.375 6.375 0 0112.75 0v.109A12.318 12.318 0 0110.374 21c-2.331 0-4.512-.645-6.374-1.766z" />
            </svg>
            Create Your Account
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Signing up is free and takes no credit card. All you need is an email address you can check right away, because Event Schedule confirms it with a code before the account is created.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open <a href="{{ app_url('/sign_up') }}" class="doc-link">the sign-up page</a> and type your email address in the <strong class="text-gray-900 dark:text-white">Email</strong> field.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">"Send Code"</strong>. A six-digit code is emailed to that address and stays valid for 10 minutes. The email field locks so the code cannot drift out of sync with it.</li>
            <li>The rest of the form appears. Fill in your <strong class="text-gray-900 dark:text-white">Full Name</strong>, a <strong class="text-gray-900 dark:text-white">Password</strong> of at least 8 characters, and the <strong class="text-gray-900 dark:text-white">Verification Code</strong> from the email.</li>
            <li>Tick <strong class="text-gray-900 dark:text-white">"I accept the Terms of Service and Privacy Policy"</strong>, then click <strong class="text-gray-900 dark:text-white">"Sign Up"</strong>.</li>
            <li>You are signed in immediately, with the email already verified, and Event Schedule asks you to pick a schedule type.</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">If the code does not arrive, click <strong class="text-gray-900 dark:text-white">"Send Code"</strong> again to get a fresh one. You can request up to five codes per hour for the same address. Your timezone and language are detected from your browser, so there is nothing to choose during sign-up.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Signing up with Google is quicker</div>
            <p>Click <strong>"Sign up with Google"</strong> instead and there is no code to enter: Google has already confirmed the address, so the account is created verified and you land straight on the schedule-type chooser. Either way, your data is yours, and we never share or sell your information.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhosted installs work differently</div>
            <p>On a selfhosted server the sign-up page doubles as the setup wizard: it asks for your MySQL details first, and the first account created there becomes the instance admin. After that, sign-up is closed unless you enable <code class="doc-inline-code">ALLOW_REGISTRATION</code>. See <a href="{{ route('marketing.docs.selfhost.installation') }}#user-accounts" class="doc-link">User Accounts and Registration</a> for the details.</p>
        </div>

        <x-doc-screenshot id="getting-started--dashboard" alt="Event Schedule dashboard showing the month calendar, the sidebar list of schedules, and the New Schedule button" loading="eager" />
    </section>

    <!-- Create Schedule -->
    <section id="create-schedule" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5" />
            </svg>
            Create Your Schedule
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A schedule is your event calendar: it is where your events live, and it gets its own public page that you share with your audience. Creating one is a single form, and almost none of it is required. Only <strong class="text-gray-900 dark:text-white">Schedule Name</strong> and <strong class="text-gray-900 dark:text-white">Email</strong> have to be filled in, plus <strong class="text-gray-900 dark:text-white">Street Address</strong> on a Venue schedule.</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li><strong class="text-gray-900 dark:text-white">Choose the type.</strong> Straight after sign-up you get a welcome screen with three cards, Talent, Venue and Curator. Pick the one that fits (see <a href="#schedule-types" class="doc-link">Schedule Types</a> below). If you would rather look around first, click <strong class="text-gray-900 dark:text-white">"Skip for now"</strong> and come back later.</li>
            <li><strong class="text-gray-900 dark:text-white">Name it.</strong> The <strong class="text-gray-900 dark:text-white">Details</strong> section opens on its <strong class="text-gray-900 dark:text-white">General</strong> tab, where <strong class="text-gray-900 dark:text-white">Schedule Name</strong> is required. Short Description (up to 200 characters) and Description are optional, and Description is a Markdown editor. For a Talent schedule the name is prefilled with your own name.</li>
            <li><strong class="text-gray-900 dark:text-white">Check the contact email.</strong> Still in Details, on the <strong class="text-gray-900 dark:text-white">Contact Info</strong> tab, <strong class="text-gray-900 dark:text-white">Email</strong> is required and is prefilled with your account email. Phone and Website are optional. The third tab, <strong class="text-gray-900 dark:text-white">Localization</strong>, is prefilled from your account and is worth a glance if this schedule runs in a different timezone.</li>
            <li><strong class="text-gray-900 dark:text-white">Add the address, if you are a venue.</strong> Venue schedules get an <strong class="text-gray-900 dark:text-white">Address</strong> section, and <strong class="text-gray-900 dark:text-white">Street Address</strong> there is required. Use <strong class="text-gray-900 dark:text-white">Validate Address</strong> and <strong class="text-gray-900 dark:text-white">View Map</strong> to confirm you have it right.</li>
            <li><strong class="text-gray-900 dark:text-white">Fill in anything else you already know.</strong> Every remaining section in the sidebar is optional and can be changed later. See <a href="#customize" class="doc-link">Customize Your Schedule</a> for what each one holds.</li>
            <li><strong class="text-gray-900 dark:text-white">Click "Save".</strong> The button sits at the bottom of the left sidebar on a wide screen, and in a fixed bar at the bottom of the page on a narrow one. Event Schedule creates the schedule and opens its admin panel on the <strong class="text-gray-900 dark:text-white">Schedule</strong> tab.</li>
        </ol>

        <p class="text-gray-600 dark:text-gray-300 mb-6">Already have an account and want another schedule? Use the <strong class="text-gray-900 dark:text-white">"New Schedule"</strong> dropdown in the top right of the dashboard: it offers the same three types and opens the same form.</p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Your schedule URL</div>
            <p>You do not pick the URL on the create form. It is generated from the schedule name, so a schedule called "Blue Note Jazz" becomes <code class="doc-inline-code">{{ route('role.view_guest', ['subdomain' => 'blue-note-jazz']) }}</code>. To change it afterwards, open <strong>Edit Schedule &rarr; Settings &rarr; General</strong>, then click <strong>Edit</strong> under <strong>Schedule URL</strong>. Do it early: changing the URL breaks any link you have already shared.</p>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">The contact email has to be verified</div>
            <p>A schedule only goes live once its contact email is confirmed. Until then its public page redirects visitors back to the home page and the <strong>View Schedule</strong> button is greyed out. Keeping the prefilled account email means it is verified from the start. If you enter a different address, Event Schedule emails it a verification link and shows a <strong>"Please verify the email address"</strong> banner with a <strong>Resend Email</strong> button until you click it. The same banner comes back if you change the address later. (A verified phone number counts too, but the email is the route almost everyone takes.)</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Schedules are public</div>
            <p>The create form says so too: <strong>your schedule will be publicly visible</strong>. If you want to work on events before anyone sees them, save them as <a href="{{ route('marketing.docs.creating_events') }}#draft" class="doc-link">Drafts</a> rather than trying to hide the schedule.</p>
        </div>
    </section>

    <!-- Schedule Types -->
    <section id="schedule-types" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
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

        <p class="text-gray-600 dark:text-gray-300 mb-4">The type is not just a label: it changes which parts of the admin panel you get.</p>

        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Talent</strong> schedules prefill the schedule name with your own name, and add an <a href="{{ route('marketing.docs.managing_schedules') }}#availability" class="doc-link">Availability</a> tab to the admin panel for marking the days you can be booked <x-doc-badge plan="enterprise" link /></li>
            <li><strong class="text-gray-900 dark:text-white">Venue</strong> schedules get an <strong class="text-gray-900 dark:text-white">Address</strong> section with address validation and a map, so visitors can find you and events inherit the location. Street Address is the one extra required field. They also get a <a href="{{ route('marketing.docs.allocated_seating') }}#build" class="doc-link">Seating</a> tab for drawing a reusable plan of the room and selling reserved seats from it <x-doc-badge plan="enterprise" link /></li>
            <li><strong class="text-gray-900 dark:text-white">Curator</strong> schedules get a <a href="{{ route('marketing.docs.managing_schedules') }}#videos" class="doc-link">Videos</a> tab for matching YouTube videos to the talent you list, and they ask visitors to sign in before submitting an event. That is the <strong class="text-gray-900 dark:text-white">Require Account</strong> toggle under <strong class="text-gray-900 dark:text-white">Engagement &rarr; Requests</strong>, which starts on for curators and off for venues. Talent schedules do not have it.</li>
        </ul>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">The type is fixed once the schedule is saved</div>
            <p>There is no setting for changing a schedule from Talent to Venue later. While you are still on the create form for your <em>first</em> schedule, the <strong>"Choose a different type"</strong> link next to the type badge at the top takes you back to the chooser. After saving, create a second schedule of the other type instead: one account can run all three.</p>
        </div>
    </section>

    <!-- Customize -->
    <section id="customize" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
            </svg>
            Customize Your Schedule
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Everything below lives on one page. Open your schedule's admin panel and click <strong class="text-gray-900 dark:text-white">"Edit Schedule"</strong>, then use the sidebar to jump between sections. Nothing here is required to publish, so start with Details and Style and come back later. The table follows the sidebar order.</p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Section</th>
                        <th>What you set there</th>
                        <th>Shown when</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Details</span></td>
                        <td>Three tabs. <strong class="text-gray-900 dark:text-white">General</strong> holds the name, Short Description and the Markdown Description. <strong class="text-gray-900 dark:text-white">Localization</strong> holds the language, an optional second language to offer translations in, the timezone your event times are read in, and a 24-hour clock toggle. <strong class="text-gray-900 dark:text-white">Contact Info</strong> holds the email, phone and website.</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Address</span></td>
                        <td>Street address, city, state, postal code and country, with <strong class="text-gray-900 dark:text-white">Validate Address</strong> and <strong class="text-gray-900 dark:text-white">View Map</strong>. This is what puts a map on your page and on your events.</td>
                        <td>Venue schedules</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Style</span></td>
                        <td>Three tabs. <strong class="text-gray-900 dark:text-white">Branding</strong> takes your <strong class="text-gray-900 dark:text-white">Square Profile Image</strong> (your logo), accent colour and font. <strong class="text-gray-900 dark:text-white">Background</strong> covers the header style, header image and page background. <strong class="text-gray-900 dark:text-white">Advanced</strong> holds the default event layout and custom CSS. Full reference in <a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Schedule Styling</a>. Custom CSS requires <x-doc-badge plan="pro" link /></td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Videos &amp; Links</span></td>
                        <td>Your social links and featured YouTube videos.</td>
                        <td>After the first save</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Customize</span></td>
                        <td>Four tabs. <strong class="text-gray-900 dark:text-white">Sub-schedules</strong> creates colour-coded groupings such as "Live Music", "DJ Nights" or "Comedy" that visitors can filter by; they organize and colour-code, and do not hide anything. The other three are <strong class="text-gray-900 dark:text-white">Custom Fields</strong>, <strong class="text-gray-900 dark:text-white">Categories</strong> and <strong class="text-gray-900 dark:text-white">Custom Labels</strong>. Custom fields and custom labels require <x-doc-badge plan="pro" link /></td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Settings</span></td>
                        <td>Tabs for <strong class="text-gray-900 dark:text-white">General</strong> (including the Schedule URL), <strong class="text-gray-900 dark:text-white">Notifications</strong> and <strong class="text-gray-900 dark:text-white">Advanced</strong>.</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Engagement</span></td>
                        <td>Event requests from visitors, fan content, post-event feedback, carpooling and sponsor logos, each on its own tab.</td>
                        <td>Always</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Gift Cards</span></td>
                        <td>Sell balance-tracked gift cards that buyers send to a recipient by email. Requires <x-doc-badge plan="pro" link /></td>
                        <td>After the first save</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Auto Import</span></td>
                        <td>Pull events automatically from URLs or a city search.</td>
                        <td>Selfhosted installs</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Integrations</span></td>
                        <td>Tabs for <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong>, <strong class="text-gray-900 dark:text-white">CalDAV Calendar</strong> and <strong class="text-gray-900 dark:text-white">Advanced</strong>, plus <strong class="text-gray-900 dark:text-white">Email Settings</strong> on eventschedule.com.</td>
                        <td>Always</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-6 mt-6">Every one of these sections is covered field by field in <a href="{{ route('marketing.docs.creating_schedules') }}#settings" class="doc-link">Creating Schedules</a>.</p>

        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">What the free plan leaves out</div>
            <p>Almost nothing on this page needs a paid plan. The free plan runs unlimited events, syncs calendars, takes RSVPs, embeds your calendar and even sells tickets with no platform fee, up to 25 paid tickets a month. Pro removes that cap for unlimited ticket sales and adds the live check-in dashboard (scanning tickets at the door is free on every plan), event graphics, custom fields, custom CSS and removing the Event Schedule branding. Enterprise adds custom domains, extra team members, availability and the AI generation features. Compare them on the <a href="{{ route('marketing.pricing') }}" class="doc-link">pricing page</a>. A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install resolves to Enterprise, so nothing is held back there.</p>
        </div>
    </section>

    <!-- FAQ -->
    <section id="faq" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.879 7.519c1.171-1.025 3.071-1.025 4.242 0 1.172 1.025 1.172 2.687 0 3.712-.203.179-.43.326-.67.442-.745.361-1.45.999-1.45 1.827v.75M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-9 5.25h.008v.008H12v-.008z" />
            </svg>
            Frequently Asked Questions
        </h2>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Can I have multiple schedules?</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Yes. One account can own up to 50 schedules on eventschedule.com, and a selfhosted install has no limit. This is how you run several bands, venues or organizations side by side, and how you mix types, since a schedule's type cannot be changed after it is saved.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How do I change my schedule URL?</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Open your schedule's admin panel, click <strong class="text-gray-900 dark:text-white">"Edit Schedule"</strong>, go to <strong class="text-gray-900 dark:text-white">Settings &rarr; General</strong> and click <strong class="text-gray-900 dark:text-white">Edit</strong> under <strong class="text-gray-900 dark:text-white">Schedule URL</strong>. See <a href="{{ route('marketing.docs.creating_schedules') }}#settings" class="doc-link">Schedule Settings</a> for details. Changing it breaks existing links, so do it before you start sharing.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What is the difference between the schedule types?</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Talent</strong> is your events at various venues, and adds an Availability tab (Enterprise). <strong class="text-gray-900 dark:text-white">Venue</strong> is various events at your venue, and adds an Address section with a map. <strong class="text-gray-900 dark:text-white">Curator</strong> is various events at various venues, and adds a Videos tab. Pick carefully: the type is fixed once you save.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Can I import events from my existing calendar?</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Yes, and it is free. Connect a calendar under <strong class="text-gray-900 dark:text-white">Edit Schedule &rarr; Integrations</strong>, which has a tab for <strong class="text-gray-900 dark:text-white">Google Calendar</strong>, <strong class="text-gray-900 dark:text-white">Outlook Calendar</strong> and <strong class="text-gray-900 dark:text-white">CalDAV Calendar</strong>. For each one you pick a direction: push your events to the calendar, pull its events in, or both. You can also <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">import events with AI</a> from pasted text or a photo of a flyer.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Is Event Schedule free?</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Yes, with no time limit and no credit card. The free plan covers unlimited events, your own schedule URL, calendar sync, analytics, RSVP with capacity limits, embedding your calendar, one appointment type, selling up to 25 paid tickets a month with no platform fee, and 10 newsletter emails a month (each recipient counts as one email, so one send to 100 followers uses 100). Pro is {{ plan_price($proMonthly) }} a month and removes the ticket cap, Enterprise is {{ plan_price($entMonthly) }} a month for custom domains and team features, and both start with a 7-day free trial.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">My schedule page will not open. What is wrong?</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Almost always an unverified contact email. A schedule stays offline until its email address is confirmed, so check your inbox for the verification link, or use the <strong class="text-gray-900 dark:text-white">Resend Email</strong> button on the yellow banner at the top of the admin panel.</p>
            </div>
        </div>
    </section>

    <!-- Next Steps -->
    <section id="next-steps" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12.75 15l3-3m0 0l-3-3m3 3h-7.5M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
            Next Steps
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Your schedule is live. The obvious next move is to add an event, which is the third step of the three-step indicator on the schedule-type chooser: create account, create schedule, create event.</p>

        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Add your first events</a> - Create events by hand, clone them, or import them</li>
            <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="doc-link">Configure your schedule</a> - Settings, sub-schedules, event requests, and calendar sync</li>
            <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Style your schedule</a> - Colors, fonts, headers, and backgrounds</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Share your schedule</a> - Embed it on your website and post it to social media</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Set up ticketing</a> - Sell tickets on your own Stripe account with no platform fee, free up to 25 paid tickets a month</li>
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
                        "text": "Open your schedule's admin panel, click Edit Schedule, go to Settings and then General, and click Edit under Schedule URL. Changing it breaks existing links, so do it before you start sharing."
                    }
                },
                {
                    "@type": "Question",
                    "name": "What is the difference between the schedule types?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Talent is your events at various venues, and adds an Availability tab (Enterprise). Venue is various events at your venue, and adds an Address section with a map. Curator is various events at various venues, and adds a Videos tab. Pick carefully: the type is fixed once you save."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Can I import events from my existing calendar?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes, and it is free. Connect a calendar under Edit Schedule and then Integrations, which has a tab for Google Calendar, Outlook Calendar and CalDAV Calendar. For each one you pick a direction: push your events to the calendar, pull its events in, or both. You can also import events with AI from pasted text or a photo of a flyer."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Is Event Schedule free?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Yes, with no time limit and no credit card. The free plan covers unlimited events, your own schedule URL, calendar sync, analytics, RSVP with capacity limits, embedding your calendar, one appointment type, selling up to 25 paid tickets a month with no platform fee, and 10 newsletter emails a month (each recipient counts as one email). Pro is {{ plan_price($proMonthly) }} a month and removes the ticket cap, Enterprise is {{ plan_price($entMonthly) }} a month for custom domains and team features, and both start with a 7-day free trial."
                    }
                },
                {
                    "@type": "Question",
                    "name": "My schedule page will not open. What is wrong?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Almost always an unverified contact email. A schedule stays offline until its email address is confirmed, so check your inbox for the verification link, or use the Resend Email button on the yellow banner at the top of the admin panel."
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
                    "text": "Enter your email on the sign-up page and click Send Code, then fill in your full name, a password of at least 8 characters and the six-digit code from the email, accept the terms and click Sign Up. Signing up with Google skips the code.",
                    "url": "{{ url(route('marketing.docs.getting_started')) }}#create-account"
                },
                {
                    "@type": "HowToStep",
                    "name": "Create Your Schedule",
                    "text": "Choose Talent, Venue or Curator, enter the schedule name, check the prefilled contact email, and click Save. The schedule URL is generated from the name and can be changed later in Settings.",
                    "url": "{{ url(route('marketing.docs.getting_started')) }}#create-schedule"
                },
                {
                    "@type": "HowToStep",
                    "name": "Choose Your Schedule Type",
                    "text": "Talent suits performers, Venue suits event spaces, and Curator suits promoters and organizers. Each type unlocks different admin panel tabs, and the type is fixed once the schedule is saved.",
                    "url": "{{ url(route('marketing.docs.getting_started')) }}#schedule-types"
                },
                {
                    "@type": "HowToStep",
                    "name": "Customize Your Schedule",
                    "text": "Open Edit Schedule to add your logo and description, set the language and timezone, add sub-schedules, and pick your colors and fonts.",
                    "url": "{{ url(route('marketing.docs.getting_started')) }}#customize"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
