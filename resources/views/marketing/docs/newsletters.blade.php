<x-docs-page
    key="newsletters"
    description="Learn how to create, design, and send newsletters to your followers and ticket buyers with Event Schedule's built-in newsletter builder."
    lede="Compose branded emails and send them to your followers and ticket buyers. Newsletters are included on every plan, and you decide what goes out and when."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#newsletter-builder">Newsletter Builder</x-doc-nav-link>
        <x-doc-nav-link href="#block-types">Block Types</x-doc-nav-link>
        <x-doc-nav-link href="#templates">Templates</x-doc-nav-link>
        <x-doc-nav-link href="#style-customization">Style Customization</x-doc-nav-link>
        <x-doc-nav-link href="#recipients">Recipients & Segments</x-doc-nav-link>
        <x-doc-nav-link href="#managing-segments" sub>Managing Segments</x-doc-nav-link>
        <x-doc-nav-link href="#importing-emails" sub>Importing Emails</x-doc-nav-link>
        <x-doc-nav-link href="#sending">Sending</x-doc-nav-link>
        <x-doc-nav-link href="#ab-testing">A/B Testing</x-doc-nav-link>
        <x-doc-nav-link href="#analytics">Analytics</x-doc-nav-link>
        <x-doc-nav-link href="#managing">Managing Newsletters</x-doc-nav-link>
        <x-doc-nav-link href="#see-also">See Also</x-doc-nav-link>
    </x-slot:toc>

    <!-- Overview -->
    <section id="overview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Overview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Newsletters live under <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Newsletters</strong>, and every schedule you own or help manage has its own list. Use them to:
        </p>
        <ul class="doc-list mb-6">
            <li>Announce upcoming events and share your schedule</li>
            <li>Send weekly or monthly event digests</li>
            <li>Promote ticket sales and special offers</li>
            <li>Share news and updates with your community</li>
        </ul>

        <x-doc-screenshot id="newsletters--list" alt="Newsletter list" loading="eager" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The builder lays out your email as a stack of content blocks with a live preview beside it, and audience segments decide who receives it. Nothing goes out automatically: every newsletter is one you compose and send.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Newsletters are on every plan, and the limit counts recipients</div>
            <p>The monthly allowance counts <strong class="text-gray-900 dark:text-white">individual recipients, not newsletters</strong>. One newsletter sent to 100 followers uses 100 of the allowance, so on the Free plan a single send reaches at most 10 people.</p>
            <div class="doc-table-wrap mt-3">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Plan</th>
                            <th>Newsletter emails per month</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><span class="font-semibold text-gray-900 dark:text-white">Free</span></td>
                            <td>10</td>
                        </tr>
                        <tr>
                            <td><span class="font-semibold text-gray-900 dark:text-white">Pro</span></td>
                            <td>100</td>
                        </tr>
                        <tr>
                            <td><span class="font-semibold text-gray-900 dark:text-white">Enterprise</span></td>
                            <td>1,000</td>
                        </tr>
                        <tr>
                            <td><span class="font-semibold text-gray-900 dark:text-white">Selfhosted</span></td>
                            <td>Unlimited</td>
                        </tr>
                        <tr>
                            <td><span class="font-semibold text-gray-900 dark:text-white">Any plan with its own email settings</span></td>
                            <td>Unlimited</td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <p class="mt-3">Connect your own mail server under <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="doc-link">Integrations &rarr; Email</a> and the cap is lifted for that schedule, because the messages leave through your provider rather than ours.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A <strong class="text-gray-900 dark:text-white">Newsletter Email Usage</strong> meter sits at the top of the Newsletters, Create and Edit pages and reads "5 of 100 newsletter emails sent this month". Test sends are excluded from the count. Sending is checked against the whole recipient list up front: if a send would push you past the limit it is refused outright rather than delivered in part, so trim the segment or upgrade first.
        </p>
    </section>

    <!-- Newsletter Builder -->
    <section id="newsletter-builder" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Newsletter Builder
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Click <strong class="text-gray-900 dark:text-white">Create Newsletter</strong> to open the builder. The editing panel is split into three tabs, with a live preview pinned beside it on wide screens.
        </p>

        <x-doc-screenshot id="newsletters--create" alt="Newsletter builder" />

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Content</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The subject line (required) and the block list. <strong class="text-gray-900 dark:text-white">Add Block</strong> opens the block palette; click a block to add it or drag it onto the canvas. Blocks are reordered by their drag handle, and each one can be selected to edit its fields inline, cloned, or removed.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Style</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The <a href="#templates" class="doc-link">template</a> picker plus the colour, font and button settings described under <a href="#style-customization" class="doc-link">Style Customization</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Settings</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Recipients (which <a href="#recipients" class="doc-link">segments</a> to send to), <a href="#ab-testing" class="doc-link">A/B testing</a> once the newsletter has been saved, and the footer text that replaces your schedule name at the bottom of the email.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Buttons Along the Bottom</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Button</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Send a Test</span></td>
                        <td>Emails the current draft to your schedule's contact address</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Save as Template</span></td>
                        <td>Stores the blocks and style under a name you can reuse</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Schedule Newsletter</span></td>
                        <td>Picks a future date and time to send</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Save</span></td>
                        <td>Saves the draft without sending</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Send Now</span></td>
                        <td>Delivers immediately to the selected recipients</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-4 mb-4">
            Everything except <strong class="text-gray-900 dark:text-white">Save</strong> appears only after the newsletter has been saved once, so save your draft first.
        </p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>The preview refreshes as you type and renders the same HTML your recipients receive, so use it to check block order and spacing. On phones the preview is a fourth tab that opens in a new window. A new newsletter starts from your most recent one's template, style settings and selected segments, so a house style only has to be set up once.</p>
        </div>
    </section>

    <!-- Block Types -->
    <section id="block-types" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Block Types
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Build your newsletter by combining these content blocks. A new newsletter starts with your profile image and header banner (when your schedule has them), a heading, two text blocks, an events block and your social links, all of which you can remove.
        </p>

        <h3 class="doc-subheading">Content Blocks</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Heading</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Large text for section titles and headlines.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Heading Text, Heading Level (Header, Subheader or Section), Alignment (left, center or right)</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Text</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">A paragraph of Markdown, written in the same editor used elsewhere in the admin panel, with a toolbar for bold, italics, lists and links.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Content</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Events</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Pulls in your events with their flyer image, date and a link to the event page. Left on All Upcoming Events it lists up to ten upcoming or ongoing events in date order; hand-picked events are shown in the order you tick them. Either way it never includes drafts, cancelled events, unlisted events or password-protected ones.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> All Upcoming Events (on) or a hand-picked list of events, Event Layout (cards or list)</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Button</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Call-to-action button. It picks up the accent colour and corner style from the Style tab.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Button Text, Button URL, Alignment</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Image</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">One or more images, each with its own caption and link. Uploading a file requires a Pro plan <x-doc-badge plan="pro" />; on the Free plan you can still point the block at an image URL you host elsewhere. Uploads accept JPG, PNG, GIF and WebP up to 10&nbsp;MB.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Image URL or upload, Alt text, Caption, Link, Width, Alignment, and Layout (column, row or grid) once there is more than one image</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Video</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Email clients cannot play video, so this block shows the YouTube thumbnail with a play badge and links out to the video. Standard watch links, youtu.be links and Shorts links are all recognised.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> YouTube URL</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Quote</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Styled blockquote with attribution, for testimonials or press pull-quotes.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Quote Text, Author, Author Title</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sponsors</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Your <a href="{{ route('marketing.docs.creating_schedules') }}#engagement" class="doc-link">sponsor logos</a>, under the section title you set on the schedule.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Sponsor Source (from your schedule settings, or from the first event in the newsletter)</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Poll</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Shows the first active poll found across your upcoming events, with a button through to that event's page to vote. It has no fields to fill in, and renders nothing if no upcoming event has an active poll.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Layout &amp; Utility Blocks</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Divider</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Horizontal line to visually separate sections.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Divider Style (solid, dashed or dotted)</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Spacer</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Adjustable vertical gap between blocks.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Spacer Height, 5 to 200 pixels</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Social Links</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Icons linking to your profiles. The block is pre-filled from the website and social links on your schedule, and each row can be edited or removed.</p>
                <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Platform and URL per link</p>
            </div>
        </div>

        <h3 class="doc-subheading">Auto-populated Blocks</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Profile Image</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your schedule's profile image, centred like a logo. No fields: change the image on the schedule and every newsletter follows.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Header Banner</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your schedule's header image, full width at the top of the email. Also has no fields.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Leave the <strong>Events</strong> block on "All Upcoming Events" and a cloned newsletter always goes out with the current schedule, with no block editing between sends.</p>
        </div>
    </section>

    <!-- Templates -->
    <section id="templates" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 17.25v3.375c0 .621-.504 1.125-1.125 1.125h-9.75a1.125 1.125 0 01-1.125-1.125V7.875c0-.621.504-1.125 1.125-1.125H6.75a9.06 9.06 0 011.5.124m7.5 10.376h3.375c.621 0 1.125-.504 1.125-1.125V11.25c0-4.46-3.243-8.161-7.5-8.876a9.06 9.06 0 00-1.5-.124H9.375c-.621 0-1.125.504-1.125 1.125v3.5m7.5 10.375H9.375a1.125 1.125 0 01-1.125-1.125v-9.25m12 6.625v-1.875a3.375 3.375 0 00-3.375-3.375h-1.5a1.125 1.125 0 01-1.125-1.125v-1.5a3.375 3.375 0 00-3.375-3.375H9.75" />
            </svg>
            Templates
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Style</strong> tab opens with five built-in presets. Picking one rewrites the colour, font and button settings in a single click; your blocks, your footer text and the layout chosen on the Events block are left alone, so you can try presets on a finished draft without losing work.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Preset</th>
                        <th>Background</th>
                        <th>Accent</th>
                        <th>Font</th>
                        <th>Buttons</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Modern</span></td>
                        <td>White</td>
                        <td>Your schedule's accent colour</td>
                        <td>Arial</td>
                        <td>Rounded</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Classic</span></td>
                        <td>Warm off-white</td>
                        <td>Brown</td>
                        <td>Georgia</td>
                        <td>Square</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Minimal</span></td>
                        <td>White</td>
                        <td>Grey</td>
                        <td>Verdana</td>
                        <td>Rounded</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Bold</span></td>
                        <td>Near-black navy</td>
                        <td>Red</td>
                        <td>Arial</td>
                        <td>Rounded</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Compact</span></td>
                        <td>Light grey</td>
                        <td>Green</td>
                        <td>Trebuchet MS</td>
                        <td>Square</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mt-4 mb-6">
            Every value stays editable afterwards, so a preset is a starting point rather than a lock-in. Bold is the one preset with a dark background, and Compact tightens the padding and footer type as well as the palette.
        </p>

        <h3 class="doc-subheading">Saving Your Own Templates</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Once a design works, keep it. Saved templates store the blocks, the preset and the style settings under a name of your choosing.
        </p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>From a saved newsletter, click <strong class="text-gray-900 dark:text-white">Save as Template</strong> and give it a name, or open <strong class="text-gray-900 dark:text-white">Templates</strong> on the newsletter list page and click <strong class="text-gray-900 dark:text-white">Create Template</strong> to build one from scratch</li>
            <li>Your templates are listed under <strong class="text-gray-900 dark:text-white">Templates</strong> with <strong class="text-gray-900 dark:text-white">Use</strong>, <strong class="text-gray-900 dark:text-white">Edit</strong> and <strong class="text-gray-900 dark:text-white">Delete</strong> actions</li>
            <li>Whenever you have at least one saved template, a <strong class="text-gray-900 dark:text-white">Start from template</strong> picker appears at the top of the Create Newsletter page</li>
        </ol>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A template has no subject line and no recipients: those belong to the newsletter you create from it.
        </p>
    </section>

    <!-- Style Customization -->
    <section id="style-customization" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
            </svg>
            Style Customization
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Below the preset picker, the <strong class="text-gray-900 dark:text-white">Style Settings</strong> panel controls the whole email. Each colour has both a swatch picker and a hex field.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Option</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Background Color</span></td>
                        <td>The background behind the email body</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Accent Color</span></td>
                        <td>Buttons, links and highlighted elements. Defaults to your schedule's accent colour</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Text Color</span></td>
                        <td>Default colour for body text</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Font Family</span></td>
                        <td>Arial, Georgia, Verdana, Trebuchet MS or Courier New</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Button Style</span></td>
                        <td>Rounded or square corners on buttons</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Footer Text</span></td>
                        <td>On the Settings tab. Replaces the schedule name printed above the unsubscribe link</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-4 mb-4">
            The layout of the Events block is not a global setting: choose cards or list on the block itself.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>Email clients have varying CSS support, so the builder renders to table-based, inline-styled HTML with a small set of email-safe fonts. That is why the font list is short and why there is no free-form CSS here. Free schedules also carry a small "Powered by Event Schedule" line under the unsubscribe link; upgrading to Pro removes it.</p>
        </div>
    </section>

    <!-- Recipients & Segments -->
    <section id="recipients" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
            </svg>
            Recipients &amp; Segments
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong class="text-gray-900 dark:text-white">Recipients</strong> panel on the Settings tab lists your saved segments with a live recipient count beside each one. Tick as many as you need and the lists are merged. If you tick nothing, the newsletter goes to all your followers.
        </p>

        <h3 class="doc-subheading">Segment Types</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">All Followers</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Everyone who follows your schedule and has not opted out of emails. Best for general announcements and event digests. Learn how to <a href="{{ route('marketing.docs.sharing') }}#followers" class="doc-link">build your follower base</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Ticket Buyers</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Everyone who has bought a ticket or registered for one of your events, taken from the email on the order. Optionally narrow it to a single event. See <a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a>.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Manual</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A list of addresses you supply, either typed in or bulk <a href="#importing-emails" class="doc-link">imported</a>. Useful for a curated press or partner list.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Waitlist</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">People currently on a <a href="{{ route('marketing.docs.tickets') }}#waitlist" class="doc-link">waitlist</a>, either waiting or already notified. Can also be narrowed to a single event.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sub-schedule</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ticket buyers from events filed under one sub-schedule, for category-specific promotions. The option only appears once the schedule has at least one sub-schedule.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Who Actually Receives It</h3>
        <ul class="doc-list mb-6">
            <li>Addresses are matched case-insensitively and de-duplicated, so someone in two segments is emailed once and counts once against your allowance</li>
            <li>Anyone who has unsubscribed from this schedule is removed, as is anyone who has turned off emails on their account</li>
            <li>Every recipient is resolved at send time, so a follower who joins after you draft the newsletter is still included</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Schedule members are always included</div>
            <p>Team members on the schedule (the owner, admins and viewers) whose email is verified and who have not turned off emails always receive every newsletter, whichever segments are selected. This keeps the team in the loop on what is being sent. They count against the monthly allowance like anyone else.</p>
        </div>
    </section>

    <!-- Managing Segments -->
    <section id="managing-segments" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 3c2.755 0 5.455.232 8.083.678.533.09.917.556.917 1.096v1.044a2.25 2.25 0 01-.659 1.591l-5.432 5.432a2.25 2.25 0 00-.659 1.591v2.927a2.25 2.25 0 01-1.244 2.013L9.75 21v-6.568a2.25 2.25 0 00-.659-1.591L3.659 7.409A2.25 2.25 0 013 5.818V4.774c0-.54.384-1.006.917-1.096A48.32 48.32 0 0112 3z" />
            </svg>
            Managing Segments
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Segments are reusable, so build them once and pick them from the Settings tab on every future newsletter. Click <strong class="text-gray-900 dark:text-white">Segments</strong> on the newsletter list page to manage them.
        </p>

        <h3 class="doc-subheading">Creating a Segment</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Click <strong class="text-gray-900 dark:text-white">Segments</strong> on the newsletter list page</li>
            <li>Under <strong class="text-gray-900 dark:text-white">Create Segment</strong>, enter a name</li>
            <li>Choose the type: All Followers, Ticket Buyers, Manual, Waitlist or Sub-schedule</li>
            <li>Fill in whatever the type asks for next: an optional event filter for Ticket Buyers and Waitlist, the sub-schedule for Sub-schedule, or the address list for Manual (one per line, with an optional name after a comma)</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Create Segment</strong></li>
        </ol>

        <h3 class="doc-subheading">Editing and Deleting</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Each saved segment shows its type and its current recipient count. <strong class="text-gray-900 dark:text-white">Edit</strong> lets you rename it and change its event or sub-schedule filter, but not its type: to change the type, create a new segment. On a manual segment the edit page also lists the subscribers, where you can add one by name and email, correct an entry, or remove it.
        </p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            A segment that is still attached to a draft or scheduled newsletter cannot be deleted. Detach it there first, then delete.
        </p>
    </section>

    <!-- Importing Emails -->
    <section id="importing-emails" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5m-13.5-9L12 3m0 0l4.5 4.5M12 3v13.5" />
            </svg>
            Importing Emails
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Bring an existing mailing list across with the <strong class="text-gray-900 dark:text-white">Import Emails</strong> button on the newsletter list page. Up to 10,000 addresses can be imported at a time.
        </p>

        <h3 class="doc-subheading">Choosing a Segment</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Start by choosing where the contacts land: <strong class="text-gray-900 dark:text-white">Create new segment</strong> with a name you provide, or <strong class="text-gray-900 dark:text-white">Add to existing segment</strong>. Imports always go into a manual segment, so only manual segments appear in that dropdown.
        </p>

        <h3 class="doc-subheading">Import Methods</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Form Entry</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Type contacts in row by row with name and email. The email is required, the name is optional. Best for small lists or one-off additions.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Paste Emails</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Paste a block of text and click <strong class="text-gray-900 dark:text-white">Parse Emails</strong>. One address per line, <code class="doc-inline-code">Name &lt;email&gt;</code>, comma-separated addresses and "email, name" pairs are all understood.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Upload CSV</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Drop in a CSV of up to 10&nbsp;MB, then map each column to Email, Name or Skip. The importer guesses the mapping and shows a preview with the row count before anything is saved.</p>
            </div>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6 mb-4">
            Whichever method you use, the rows are validated first and any bad or duplicate address is reported by row number so you can fix it before confirming. Addresses already in the target segment are skipped rather than duplicated.
        </p>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Imported contacts also become followers</div>
            <p>Importing adds each address to the segment and follows your schedule with it, so imported people appear on your followers list and are reachable through the All Followers segment too. Only import lists that have agreed to hear from you, and remember that every recipient counts against your monthly allowance.</p>
        </div>
    </section>

    <!-- Sending -->
    <section id="sending" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 12L3.269 3.126A59.768 59.768 0 0121.485 12 59.77 59.77 0 013.27 20.876L5.999 12zm0 0h7.5" />
            </svg>
            Sending
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You have three ways to deliver a saved newsletter:
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Send Now</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Confirm the prompt and the newsletter moves to Sending. Messages go out in small batches a few seconds apart rather than all at once, so a large send takes a few minutes to finish.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule Newsletter</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Pick a future date and time, read in your own timezone. The send fires within a minute of that time. A banner on the edit page shows the scheduled time and offers <strong class="text-gray-900 dark:text-white">Cancel schedule</strong>, which returns it to Draft.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Send a Test</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Sends one copy to your schedule's contact email address, so set that address first. Test sends do not count against your monthly allowance and are excluded from the statistics, and there is a short cooling-off period between tests.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">Verification is required before the first send</div>
            <p>On eventschedule.com you must either <a href="{{ route('marketing.docs.creating_schedules') }}#integrations" class="doc-link">set up your own email settings</a> for the schedule or verify your phone number in your profile before newsletters can go out. Until then the Newsletters page shows a warning and every send is refused. Selfhosted installs are not affected.</p>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Tip</div>
            <p>Send a test first, every time. Images, links and dark-mode rendering are the three things that most often look different in a real inbox from the builder preview.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhosted: give the queue a real driver</div>
            <p>With the <code class="doc-inline-code">sync</code> queue driver an immediate send of more than 50 recipients is refused, because the whole delivery would have to happen inside one web request. Either schedule the newsletter instead or configure a background queue driver and a worker. See the <a href="{{ route('marketing.docs.selfhost.installation') }}" class="doc-link">selfhost installation guide</a>.</p>
        </div>
    </section>

    <!-- A/B Testing -->
    <section id="ab-testing" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
            </svg>
            A/B Testing
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            An A/B test sends two versions to a slice of your audience, then sends whichever performed better to everyone else. Open the <strong class="text-gray-900 dark:text-white">A/B Testing</strong> panel on the Settings tab of a saved newsletter.
        </p>

        <h3 class="doc-subheading">Setting Up a Test</h3>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Click <strong class="text-gray-900 dark:text-white">Create A/B Test</strong></li>
            <li>Choose what to vary: the <strong class="text-gray-900 dark:text-white">Subject</strong> or the <strong class="text-gray-900 dark:text-white">Content above events</strong></li>
            <li>Set the sample percentage, from 5% to 50% of your audience (20% by default)</li>
            <li>Set how long to wait before picking a winner, from 1 to 72 hours (4 by default)</li>
            <li>Choose the winning criterion: <strong class="text-gray-900 dark:text-white">Open rate</strong> or <strong class="text-gray-900 dark:text-white">Click rate</strong></li>
            <li>Save. Your newsletter becomes variant A and a copy is created as variant B, which opens for editing so you can make the change you want to test</li>
            <li>Send the test from variant B when both versions are ready</li>
        </ol>

        <h3 class="doc-subheading">What Happens Next</h3>
        <ul class="doc-list mb-6">
            <li>The sample is drawn at random and split evenly between A and B</li>
            <li>When the waiting period is up, the better-performing variant on your chosen criterion is marked as the winner</li>
            <li>The winner is then sent to everyone in the audience who was not in the sample, with no further action from you</li>
            <li>The <a href="#analytics" class="doc-link">statistics</a> page shows both variants side by side with their sent counts, open rates and click rates, and flags the winner</li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Note</div>
            <p>A test always has exactly two variants. The allowance check runs against the full audience before the sample goes out, so both stages have to fit inside your remaining monthly allowance. A/B testing also needs enough recipients to mean anything: on a list of a few dozen people the difference between variants is usually noise.</p>
        </div>
    </section>

    <!-- Analytics -->
    <section id="analytics" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
            </svg>
            Analytics
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Once a newsletter is sending or sent, its subject line on the list page links to a statistics page. The list itself also carries sortable Sent, Open Rate and Click Rate columns.
        </p>

        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Metric</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Sent</span></td>
                        <td>How many messages were delivered to the mail server</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Opens</span></td>
                        <td>Recipients who opened the email, and that as a percentage of sent</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Clicks</span></td>
                        <td>Recipients who clicked at least one link, and that as a percentage of sent</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Failed</span></td>
                        <td>Messages the mail server rejected or could not deliver</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Opens and clicks over time</span></td>
                        <td>Two charts plotting activity by day since the send</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Top clicked links</span></td>
                        <td>The ten most-clicked destinations, with their click counts</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Recipients</span></td>
                        <td>A sortable, paginated list of every recipient with their status and the time they first opened and clicked</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-4 mb-4">
            Opens are measured with a tracking pixel, so a recipient whose client blocks remote images counts as unopened even if they read the email. Treat the open rate as a floor and a trend rather than an exact number. Clicks are counted by routing each link through a redirect, which is also why link addresses in the delivered email look different from the ones you typed.
        </p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Newsletter traffic shows up in schedule analytics too</div>
            <p>Links are tagged as newsletter traffic on their way out, so visits arriving from a send appear under traffic sources in <a href="{{ route('marketing.docs.analytics') }}#web-analytics" class="doc-link">Analytics</a>. That is how you tell what a newsletter did after the click, not just up to it.</p>
        </div>
    </section>

    <!-- Managing Newsletters -->
    <section id="managing" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Managing Newsletters
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The newsletter list shows every newsletter for the selected schedule with a status badge. Use the schedule picker at the top left to switch between the schedules you manage.
        </p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Draft</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">In progress, not sent or scheduled. Editable at any time.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Scheduled</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Queued for a future time. Still editable, and <strong class="text-gray-900 dark:text-white">Cancel schedule</strong> on the edit page puts it back to Draft.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sending</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Delivery is under way. It cannot be edited or deleted while in this state, and statistics are already available.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sent</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Delivered. Open its statistics, or clone it to start the next one.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Cloning</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <strong class="text-gray-900 dark:text-white">Clone</strong> copies the blocks, style and recipients into a fresh draft and opens it, leaving the original untouched. Statistics and any A/B test are not carried over. This is the quickest way to run a recurring digest.
        </p>

        <h3 class="doc-subheading">Deleting</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <strong class="text-gray-900 dark:text-white">Delete</strong> removes a newsletter from the list. A newsletter that is currently sending cannot be deleted; wait for it to finish.
        </p>

        <h3 class="doc-subheading">Unsubscribes</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Every newsletter footer carries an unsubscribe link. One click opts that address out and it is excluded from every future send for that schedule, automatically and with no work from you. Unsubscribes are per schedule, so someone who leaves one of your schedules still hears from the others. Recipients can also turn off all email from their own account settings, which removes them everywhere.
        </p>
    </section>

    <!-- See Also -->
    <section id="see-also" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
            </svg>
            See Also
        </h2>
        <ul class="doc-list">
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a> - Grow the follower list your newsletters go to</li>
            <li><a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event Graphics</a> - Generate shareable images for social media</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events that appear in your newsletters</li>
            <li><a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a> - Track how newsletters drive schedule views</li>
            <li><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Selling Tickets</a> - Set up ticketing, waitlists and buyer lists</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Send Newsletters with Event Schedule",
            "description": "Create, design, and send newsletters to your followers and ticket buyers with Event Schedule's built-in newsletter builder.",
            "totalTime": "PT10M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Create a Newsletter",
                    "text": "Go to Admin Panel, then Newsletters, and click Create Newsletter to open the builder.",
                    "url": "{{ url(route('marketing.docs.newsletters')) }}#newsletter-builder"
                },
                {
                    "@type": "HowToStep",
                    "name": "Write the Subject and Add Content Blocks",
                    "text": "On the Content tab, enter the subject line, then add and arrange blocks such as headings, text, events, buttons and images.",
                    "url": "{{ url(route('marketing.docs.newsletters')) }}#block-types"
                },
                {
                    "@type": "HowToStep",
                    "name": "Choose a Template and Style",
                    "text": "On the Style tab, pick a preset (Modern, Classic, Minimal, Bold or Compact) and adjust the colors, font and button style.",
                    "url": "{{ url(route('marketing.docs.newsletters')) }}#templates"
                },
                {
                    "@type": "HowToStep",
                    "name": "Select Recipients",
                    "text": "On the Settings tab, tick the segments to send to: all followers, ticket buyers, a waitlist, a sub-schedule or a manual list. Selecting none sends to all followers.",
                    "url": "{{ url(route('marketing.docs.newsletters')) }}#recipients"
                },
                {
                    "@type": "HowToStep",
                    "name": "Send or Schedule",
                    "text": "Send a test to your schedule's email address first, then send the newsletter immediately or schedule it for a future date and time.",
                    "url": "{{ url(route('marketing.docs.newsletters')) }}#sending"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
