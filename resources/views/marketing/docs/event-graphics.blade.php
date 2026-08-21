<x-docs-page
    key="event-graphics"
    description="Learn how to use the Event Graphics feature to generate a shareable image of your upcoming events plus ready-to-paste text, using your own template variables."
    lede="Compose your upcoming events into one image and one block of text you can paste anywhere: a social post, a WhatsApp group, or an email."
    plan="pro"
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#header-footer-text">Header &amp; Footer Text</x-doc-nav-link>
        <x-doc-nav-link href="#text-template">Text Template</x-doc-nav-link>
        <x-doc-nav-link href="#quick-reference">Quick Reference</x-doc-nav-link>
        <x-doc-nav-link href="#variables">All Variables</x-doc-nav-link>
        <x-doc-nav-link href="#ai-prompt">AI Text Prompt</x-doc-nav-link>
        <x-doc-nav-link href="#email-scheduling">Email Scheduling</x-doc-nav-link>
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
            Event Graphics builds two things from your upcoming events at once: a single PNG image made from their flyers, and a block of formatted text listing the same events. Nothing is posted for you, so the result works anywhere:
        </p>
        <ul class="doc-list mb-6">
            <li>Social media posts (Instagram, Facebook, Twitter/X)</li>
            <li>WhatsApp and Telegram messages</li>
            <li>Email <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletters</a></li>
            <li>Anywhere else you can paste an image or some text</li>
        </ul>

        <div class="doc-callout doc-callout-plan mb-6">
            <div class="doc-callout-title">Pro feature</div>
            <p><x-doc-badge plan="pro" /> Generating event graphics is part of the <strong class="text-gray-900 dark:text-white">Pro</strong> plan. Uploading a flyer to an event is free on every plan. The AI text prompt and scheduled graphic emails are <strong class="text-gray-900 dark:text-white">Enterprise</strong>. A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install counts as Enterprise, so nothing on this page is held back by plan there.</p>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            To open it, go to your schedule in the admin panel and choose <strong>Events Graphic</strong> from the <strong>Actions</strong> menu.
        </p>

        <x-doc-screenshot id="event-graphics--graphic-page" alt="Event graphics page with generated graphic" loading="eager" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The image panel has <strong>Download</strong> and <strong>Copy Image</strong>; the text panel has <strong>Copy Text</strong>, plus <strong>Share</strong> on devices whose browser supports it. <strong>Save Settings</strong> stores your choices for next time and for the scheduled email; <strong>Run</strong> regenerates the preview.
        </p>

        <h3 class="doc-subheading">What goes on the graphic</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Every run reads your schedule's upcoming events, earliest first:
        </p>
        <ul class="doc-list mb-6">
            <li><strong class="text-gray-900 dark:text-white">Upcoming or ongoing only.</strong> An event that has started but not finished still counts.</li>
            <li><strong class="text-gray-900 dark:text-white">Flyer required for the image.</strong> Only events that have their own flyer image are drawn. Events without one still appear in the text, which needs no artwork.</li>
            <li><strong class="text-gray-900 dark:text-white">Hidden events stay hidden.</strong> Draft and internal events, unlisted events, password-protected events and cancelled events are never included.</li>
            <li><strong class="text-gray-900 dark:text-white">Up to 20 events.</strong> That is the ceiling; <strong>Number of Events</strong> can lower it.</li>
            <li><strong class="text-gray-900 dark:text-white">Recurring events can be left out</strong> with <strong>Exclude recurring events</strong> on the Automation tab, which affects both the image and the text.</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Every flyer also carries a QR code in its lower corner pointing at that event's page, so a printed or projected graphic still leads people to the listing.
        </p>

        <h3 class="doc-subheading">Layout Type</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The settings panel is split into three tabs: <strong>Graphic</strong>, <strong>Text</strong> and <strong>Automation</strong>. The Graphic tab starts with the layout.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Layout</th>
                        <th>How it looks</th>
                        <th>Best for</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Grid</strong></td>
                        <td>Flyers drawn at a uniform size and wrapped into rows</td>
                        <td>Instagram and Facebook posts</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Rows</strong></td>
                        <td>One row by default, and every flyer keeps its own proportions</td>
                        <td>Twitter/X posts and website banners</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">List</strong></td>
                        <td>A small flyer, then the event name, its short description, the venue and the date and time, with a separator between items</td>
                        <td>Instagram Stories and email newsletters</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Image Size</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <strong>Image Size</strong> fits the finished graphic to a fixed social-media shape. The graphic is scaled to fit inside the shape and centered, and the padding around it is filled with your schedule's own background, so nothing is ever cropped or stretched.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Option</th>
                        <th>Pixels</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>Auto (fit to content)</td><td>The natural size, which grows with the number of events</td></tr>
                    <tr><td>Square</td><td>1080 × 1080</td></tr>
                    <tr><td>Portrait</td><td>1080 × 1350</td></tr>
                    <tr><td>Story</td><td>1080 × 1920</td></tr>
                    <tr><td>Landscape</td><td>1200 × 630</td></tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            A fixed shape is a fixed number of pixels, so the more events you include the smaller each flyer is drawn inside it. Twenty events fill a grid roughly twice the width of a Square post, which halves every flyer and its text. If the artwork is coming out too small to read, lower <strong>Number of Events</strong> or switch back to Auto, which lets the graphic grow instead.
        </p>

        <h3 class="doc-subheading">The rest of the Graphic tab</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Show Text</strong></td>
                        <td>Draws a date strip across the top of each flyer. <strong>None</strong> (the default) draws nothing, <strong>Overlay</strong> puts a translucent dark band over the artwork, and <strong>Above</strong> puts a solid dark bar just above it. Grid and Rows only.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Overlay Text</strong></td>
                        <td>Appears once Show Text is not None. Replaces the date in that strip with your own short template, for example <code class="doc-inline-code">{date_dmy} | {time}</code>. Leave it blank for the date on its own.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Flyers Per Row</strong></td>
                        <td>How many flyers before wrapping to the next row, from 1 to 10, or Auto. Grid and Rows only.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Add Event Numbers</strong></td>
                        <td>Puts a numbered badge on each flyer. Use <code class="doc-inline-code">{number}</code> in the text template so the list matches the badges. Grid and Rows only, and it also limits the text to the events that have a flyer, so the numbering cannot drift.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Number of Events</strong></td>
                        <td>How many upcoming events to include, from 1 to 20. "All available" uses the maximum of 20.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Events Per Schedule</strong></td>
                        <td>Caps how many events any one talent or venue can contribute, from 1 to 10, so a single act or room cannot fill the graphic. It counts the talent and venue schedules attached to each event, not the schedule the graphic is for, and it fills the freed slots from further down the calendar. Because it counts venues too, the graphic can still come out shorter than Number of Events when your events do not spread across enough different talents and venues: a residency at one room with a cap of 2 gives you 2 events, however many are coming up. Applies to the image and the text alike. "Unlimited" is the default and changes nothing.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Nothing to draw</div>
            <p>If no upcoming event has a flyer image, the image panel says so instead of rendering. Add a flyer to at least one upcoming event, or use the text output on its own.</p>
        </div>
    </section>

    <!-- Header & Footer Text -->
    <section id="header-footer-text" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5" />
            </svg>
            Header &amp; Footer Text
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The last three settings on the Graphic tab brand the image itself. All three are optional, and all three are drawn in your schedule's own colors and reading direction.
        </p>
        <div class="doc-fields mb-6">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Header Image</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">Upload a JPG, PNG, GIF or WebP to run across the top of the graphic. It is scaled to the full width of the image and capped at 200 pixels tall, so a wide, short logo band works better than a tall one. Upload a new file to replace it, or remove it to go back to no header.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Header Text</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">A bold headline on one line, centered above the events. It shrinks automatically to fit the width and is truncated with an ellipsis if it still does not. Up to 200 characters.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Footer Text</h4>
                <p class="text-sm text-gray-600 dark:text-gray-400">A sign-off below the events, in a slightly softer tone than the events themselves. Up to two lines and 300 characters; extra lines are dropped.</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Header image and header text work together: the image sits at the top and the text lands directly below it, which gives you a logo with a tagline under it.
        </p>

        <h3 class="doc-subheading">Schedule Variables</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Header and footer text support a small set of schedule-wide variables. Unlike the per-event variables used in the <a href="#text-template" class="doc-link">text template</a> or in Overlay Text, these describe the schedule or the batch as a whole, not any single event.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td><code class="doc-inline-code">{schedule_name}</code></td><td>The schedule's display name</td></tr>
                    <tr><td><code class="doc-inline-code">{month_name}</code></td><td>Current month (e.g. "May"), translated</td></tr>
                    <tr><td><code class="doc-inline-code">{month_short}</code></td><td>Current month abbreviation (e.g. "May"), translated</td></tr>
                    <tr><td><code class="doc-inline-code">{month}</code></td><td>Current month number (e.g. "5")</td></tr>
                    <tr><td><code class="doc-inline-code">{month_pad}</code></td><td>Current month number, zero-padded (e.g. "05")</td></tr>
                    <tr><td><code class="doc-inline-code">{year}</code></td><td>Current year (e.g. "2026")</td></tr>
                    <tr><td><code class="doc-inline-code">{day_name}</code></td><td>Current weekday (e.g. "Wednesday"), translated</td></tr>
                    <tr><td><code class="doc-inline-code">{day_short}</code></td><td>Current weekday abbreviation (e.g. "Wed"), translated</td></tr>
                    <tr><td><code class="doc-inline-code">{first_event_date}</code></td><td>Date of the earliest event in the graphic (e.g. "May 3")</td></tr>
                    <tr><td><code class="doc-inline-code">{last_event_date}</code></td><td>Date of the latest event in the graphic (e.g. "May 28")</td></tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The date variables read "now" in your schedule's timezone. Per-event variables such as <code class="doc-inline-code">{event_name}</code> are not available here, because a header covers the whole batch.
        </p>

        <h3 class="doc-subheading">Examples</h3>
        <ul class="doc-list mb-6">
            <li><code class="doc-inline-code">Spring Lineup {month_name} {year}</code></li>
            <li><code class="doc-inline-code">{schedule_name} - Upcoming Events</code></li>
            <li><code class="doc-inline-code">{first_event_date} to {last_event_date}</code></li>
        </ul>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Fonts on the image</div>
            <p>The graphic is drawn with built-in Noto fonts, picked automatically from the characters in your text so Hebrew and Arabic render correctly. The font you choose in <a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">schedule styling</a> applies to your web pages, not to this image; the background and text colors do come from your schedule.</p>
        </div>
    </section>

    <!-- Text Template -->
    <section id="text-template" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 00-3.375-3.375h-1.5A1.125 1.125 0 0113.5 7.125v-1.5a3.375 3.375 0 00-3.375-3.375H8.25m0 12.75h7.5m-7.5 3H12M10.5 2.25H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 00-9-9z" />
            </svg>
            Text Template
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The <strong>Text</strong> tab controls the block of text next to the image. The template defines how one event is formatted, and it is repeated once per event, with a blank line between entries.
        </p>

        <x-doc-screenshot id="event-graphics--settings" alt="Event graphics settings" />

        <h3 class="doc-subheading">Default Template</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">If you leave the template blank, the following default format is used:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>Default Template</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>*{day_name}* {date_dmy} | {time}
*{event_name}*:
{venue} | {city}
{url}</code></pre>
        </div>

        <h3 class="doc-subheading">Example Output</h3>
        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>Generated Text</span>
            </div>
            <pre><code>*Wednesday* 15/3 | 20:00
*Summer Concert*:
Central Park | New York
https://example.com/event/summer-concert</code></pre>
        </div>

        <div class="doc-callout doc-callout-tip mt-6">
            <div class="doc-callout-title">Tip</div>
            <p>Use <code class="doc-inline-code">*text*</code> for bold formatting on WhatsApp and Telegram, or <code class="doc-inline-code">_text_</code> for italics.</p>
        </div>

        <h3 class="doc-subheading">Blank values clean themselves up</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            You do not need a separate template for events that have no venue or no price. If a variable comes back empty, a stranded <code class="doc-inline-code">|</code> separator around it is removed, and a line left with nothing but punctuation is dropped from that event's entry.
        </p>

        <h3 class="doc-subheading">The rest of the Text tab</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Setting</th>
                        <th>What it does</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Include all future events</strong></td>
                        <td>Lists every upcoming event in the text, not only the ones shown on the image. Ignored while Add Event Numbers is on, so the numbering keeps matching the badges.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Generate text in English</strong></td>
                        <td>Produces the text, and the dates and details on the image, in English instead of the schedule language. Shown only for a non-English schedule whose translation target is English.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Include HTTPS</strong></td>
                        <td>Keeps the <code class="doc-inline-code">https://</code> prefix on <code class="doc-inline-code">{url}</code>. Off by default, which is shorter and still auto-links in most apps.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Include Event ID</strong></td>
                        <td>Keeps the event id in <code class="doc-inline-code">{url}</code>. Off by default, which produces the clean slug-only link.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">An invitation at the end</div>
            <p>If your schedule accepts <a href="{{ route('marketing.docs.managing_schedules') }}#requests" class="doc-link">event requests</a>, a short "want to see your event here?" line and a link to your request page are appended after the last event. Turn off event requests on the schedule to remove it.</p>
        </div>
    </section>

    <!-- Quick Reference -->
    <section id="quick-reference" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 13.5l10.5-11.25L12 10.5h8.25L9.75 21.75 12 13.5H3.75z" />
            </svg>
            Quick Reference
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Most templates only need these essential variables. Copy the ones you need:
        </p>

        <div class="grid md:grid-cols-2 gap-4 mb-6">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Event Basics</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{event_name}</code> <span class="text-gray-600 dark:text-gray-400">Event name</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{short_description}</code> <span class="text-gray-600 dark:text-gray-400">Short description</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{url}</code> <span class="text-gray-600 dark:text-gray-400">Event link</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{description}</code> <span class="text-gray-600 dark:text-gray-400">Full description</span></div>
                </div>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Date &amp; Time</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{day_name}</code> <span class="text-gray-600 dark:text-gray-400">Wednesday</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{date_dmy}</code> <span class="text-gray-600 dark:text-gray-400">15/3</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{time}</code> <span class="text-gray-600 dark:text-gray-400">20:00</span></div>
                </div>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Location</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{venue}</code> <span class="text-gray-600 dark:text-gray-400">Venue name</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{city}</code> <span class="text-gray-600 dark:text-gray-400">City</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{address}</code> <span class="text-gray-600 dark:text-gray-400">Street</span></div>
                </div>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Tickets</h4>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{price}</code> <span class="text-gray-600 dark:text-gray-400">10, or blank if free</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{currency}</code> <span class="text-gray-600 dark:text-gray-400">USD</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{coupon_code}</code> <span class="text-gray-600 dark:text-gray-400">SAVE20</span></div>
                    <div class="flex justify-between gap-3"><code class="doc-inline-code">{coupon_discount}</code> <span class="text-gray-600 dark:text-gray-400">15%</span></div>
                </div>
            </div>
        </div>

        <p class="text-gray-600 dark:text-gray-400 text-sm">See <a href="#variables" class="doc-link">All Variables</a> below for the complete list including date formats, end times, and more.</p>
    </section>

    <!-- Variables -->
    <section id="variables" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            All Template Variables
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Use these variables in your template. They are replaced with the actual event data when the text is generated. An unknown variable is left in place, so a typo shows up as itself rather than disappearing.
        </p>

        <h3 class="doc-subheading">Date &amp; Time</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{day_name}</code></td>
                        <td>Full day name (translated)</td>
                        <td>Wednesday</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day_short}</code></td>
                        <td>Short day name (translated)</td>
                        <td>Wed</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_dmy}</code></td>
                        <td>Day/month format (a two-digit year is added when the event is not in the current year)</td>
                        <td>15/3</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_mdy}</code></td>
                        <td>Month/day format (a two-digit year is added when the event is not in the current year)</td>
                        <td>3/15</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_full_dmy}</code></td>
                        <td>Full date (day/month/year)</td>
                        <td>15/03/2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{date_full_mdy}</code></td>
                        <td>Full date (month/day/year)</td>
                        <td>03/15/2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month}</code></td>
                        <td>Month number</td>
                        <td>3</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_pad}</code></td>
                        <td>Month number (zero-padded)</td>
                        <td>03</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_name}</code></td>
                        <td>Full month name (translated)</td>
                        <td>March</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{month_short}</code></td>
                        <td>Short month name (translated)</td>
                        <td>Mar</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day}</code></td>
                        <td>Day of month</td>
                        <td>15</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{day_pad}</code></td>
                        <td>Day of month (zero-padded)</td>
                        <td>05</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{year}</code></td>
                        <td>Year</td>
                        <td>2025</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{time}</code></td>
                        <td>Start time (uses schedule's 24h setting)</td>
                        <td>20:00 or 8:00 PM</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{end_time}</code></td>
                        <td>End time (uses schedule's 24h setting). An event with no duration set is treated as two hours long.</td>
                        <td>22:00 or 10:00 PM</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{duration}</code></td>
                        <td>Duration in hours</td>
                        <td>2</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Dates and times are rendered in the schedule's timezone, so the image and the text always agree.
        </p>

        <h3 class="doc-subheading">Event Information</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{number}</code></td>
                        <td>1-based position in the list, matching the badge on the flyer when <strong>Add Event Numbers</strong> is on. Specific to the Event Graphics text panel; it has no value in calendar description templates.</td>
                        <td>1</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{event_name}</code></td>
                        <td>Event name</td>
                        <td>Summer Concert</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{short_description}</code></td>
                        <td>Short description</td>
                        <td>Live jazz with local artists</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{description}</code></td>
                        <td>Full description, converted to plain text (no markdown or HTML markup)</td>
                        <td>Join us for a night of music...</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{url}</code></td>
                        <td>Event URL, formatted by the Include HTTPS and Include Event ID settings</td>
                        <td>example.com/summer-concert</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Venue Information</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            All five are blank when the event has no venue.
        </p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{venue}</code></td>
                        <td>Venue name (translated)</td>
                        <td>Central Park</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{city}</code></td>
                        <td>City</td>
                        <td>New York</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{address}</code></td>
                        <td>Street address</td>
                        <td>123 Main St</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{state}</code></td>
                        <td>State/Province</td>
                        <td>NY</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{country}</code></td>
                        <td>Country</td>
                        <td>US</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading"><a href="{{ route('marketing.docs.tickets') }}" class="doc-link">Ticket</a> Information</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Example</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">{currency}</code></td>
                        <td>Currency code</td>
                        <td>USD</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{price}</code></td>
                        <td>Lowest ticket price. Blank when every ticket is free, when the event has no price, and when the event has no tickets at all, so a free event simply drops the line rather than printing a zero.</td>
                        <td>10</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{coupon_code}</code></td>
                        <td>Event coupon code</td>
                        <td>SAVE20</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">{coupon_discount}</code></td>
                        <td>What the coupon is worth. A percentage renders as <code class="doc-inline-code">15%</code>; a fixed discount renders as an amount in the event's currency. Blank when no discount is set, so the line drops rather than printing a zero.</td>
                        <td>15%</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Custom Fields <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            If you have defined <a href="{{ marketing_url('/features/custom-fields') }}" class="doc-link">Event Custom Fields</a> in your schedule settings, you can include their values in graphics using numbered variables.
        </p>

        @if (!empty($customFieldsData))
            {{-- Dynamic: Show user's actual custom fields --}}
            @foreach ($customFieldsData as $scheduleData)
                <h4 class="text-md font-medium text-gray-900 dark:text-white mb-2">{{ $scheduleData['role_name'] }}</h4>
                <div class="doc-table-wrap">
                    <table class="doc-table">
                        <thead>
                            <tr>
                                <th>Variable</th>
                                <th>Field Name</th>
                                <th>Type</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($scheduleData['fields'] as $index => $field)
                            <tr>
                                <td><code class="doc-inline-code">{custom_{{ $loop->iteration }}}</code></td>
                                <td>{{ $field['name'] }}</td>
                                <td>{{ ucfirst(str_replace('_', ' ', $field['type'] ?? 'string')) }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endforeach
        @else
            {{-- Static: Generic documentation for logged-out users or users without custom fields --}}
            <div class="doc-table-wrap">
                <table class="doc-table">
                    <thead>
                        <tr>
                            <th>Variable</th>
                            <th>Description</th>
                            <th>Example</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td><code class="doc-inline-code">{custom_1}</code></td>
                            <td>Value of the 1st custom field</td>
                            <td>John Smith</td>
                        </tr>
                        <tr>
                            <td><code class="doc-inline-code">{custom_2}</code></td>
                            <td>Value of the 2nd custom field</td>
                            <td>Room 101</td>
                        </tr>
                        <tr>
                            <td><code class="doc-inline-code">{custom_3}</code></td>
                            <td>Value of the 3rd custom field</td>
                            <td>Workshop</td>
                        </tr>
                        <tr>
                            <td colspan="3" class="text-gray-600 dark:text-gray-400 text-sm">...up to {custom_10}</td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endif

        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Tip</div>
            <p>Custom field variables correspond to the order your fields are defined in schedule settings. For example, if your first custom field is "Speaker Name", then <code class="doc-inline-code">{custom_1}</code> will show the speaker's name. A yes/no field prints as Yes or No.</p>
        </div>

        <div class="doc-callout doc-callout-info mb-6">
            <div class="doc-callout-title">Overlay Text uses a subset</div>
            <p>The same variables work in the Overlay Text on the flyer strip, with four exceptions: <code class="doc-inline-code">{url}</code>, <code class="doc-inline-code">{number}</code>, <code class="doc-inline-code">{month_pad}</code> and <code class="doc-inline-code">{day_pad}</code> are text-only and are left as they are there. Keep overlay text short: it is drawn on one line across the width of a flyer.</p>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Localization</div>
            <p>Date and time variables like <code class="doc-inline-code">{day_name}</code>, <code class="doc-inline-code">{month_name}</code>, and <code class="doc-inline-code">{time}</code> are automatically translated to your schedule's language and respect its 24-hour time setting. Text for a Hebrew or Arabic schedule is also marked so it pastes right-to-left into apps like WhatsApp, while the event links stay intact.</p>
            <p class="mt-2">If your schedule uses a non-English language and translates into English, you can turn on <strong>Generate text in English</strong> on the Text tab to produce the text, and the dates and event details on the graphic itself, in English instead. English translations of event and venue names are used when available, falling back to the original values.</p>
        </div>
    </section>

    <div class="doc-callout doc-callout-plan">
        <div class="doc-callout-title">One flyer at a time <x-doc-badge plan="enterprise" /></div>
        <p>Event Graphics composes flyers you already have. To create a flyer for a single event, Enterprise schedules can generate one with AI from the <a href="{{ route('marketing.docs.creating_events') }}#ai-flyer" class="doc-link">event edit page</a>.</p>
    </div>

    <!-- AI Prompt -->
    <section id="ai-prompt" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            AI Text Prompt
        </h2>
        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Enterprise feature</div>
            <p><x-doc-badge plan="enterprise" /> The AI Text Prompt field on the Text tab needs the <strong class="text-gray-900 dark:text-white">Enterprise</strong> plan. Below Enterprise the field is replaced by an upgrade note. On a <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install the plan is not the obstacle, but the instance needs its own AI key configured, otherwise the prompt is quietly skipped.</p>
        </div>

        <h3 class="doc-subheading">How It Works</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            The prompt runs after your template, on the finished text. It rewrites the text output only: the image is never changed, and the original events are passed along as reference data so the model does not have to invent names, venues or prices. Typical uses:
        </p>
        <ul class="doc-list mb-6">
            <li>Add emojis to make posts more engaging</li>
            <li>Translate the listing to another language</li>
            <li>Adjust formatting for a specific platform</li>
            <li>Add hashtags or mentions</li>
        </ul>
        <p class="text-gray-600 dark:text-gray-300 mb-6">
            Generating takes a moment longer with a prompt set, so the text appears first and is replaced when the AI finishes. Your prompt is treated strictly as formatting instructions, and if it cannot be applied the original text is kept. The same prompt is applied to the scheduled email described below.
        </p>

        <h3 class="doc-subheading">Example Prompts</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <code class="doc-inline-code">Add a calendar emoji before each date and a pin emoji before each venue</code>
            </div>
            <div class="doc-field">
                <code class="doc-inline-code">Translate to Spanish</code>
            </div>
            <div class="doc-field">
                <code class="doc-inline-code">Add relevant hashtags for Instagram</code>
            </div>
        </div>
    </section>

    <!-- Email Scheduling -->
    <section id="email-scheduling" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
            </svg>
            Email Scheduling
        </h2>
        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Enterprise feature</div>
            <p><x-doc-badge plan="enterprise" /> Scheduled graphic emails need the <strong class="text-gray-900 dark:text-white">Enterprise</strong> plan. The controls live on the <strong>Automation</strong> tab of the settings panel.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            Have the current graphic and its text emailed on a cadence, using whatever you last saved on the Graphic and Text tabs. This is an internal reminder rather than a campaign: it goes only to the addresses you type into <strong>Send To</strong>, never to the people following your schedule. To email your followers a designed campaign, use <a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">newsletters</a> instead.
        </p>

        <h3 class="doc-subheading">Configuration Options</h3>
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
                        <td><strong class="text-gray-900 dark:text-white">Enable scheduled emails</strong></td>
                        <td>Turns the schedule on. Saving with it on and no valid address is rejected.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Frequency</strong></td>
                        <td>Daily, Weekly, or Monthly</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Send on Days</strong></td>
                        <td>For Weekly: tick one or more days of the week. At least one is required.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Day of Month</strong></td>
                        <td>For Monthly: the day to send on. A day later than the month has, such as 31 in April, falls back to the last day of that month.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Send At</strong></td>
                        <td>The hour to send, in your schedule's timezone. Delivery is at or shortly after that hour, and each day or month gets at most one send.</td>
                    </tr>
                    <tr>
                        <td><strong class="text-gray-900 dark:text-white">Send To</strong></td>
                        <td>A comma-separated list of email addresses. Everyone on the list receives the same email.</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <h3 class="doc-subheading">Test Email</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">
            <strong>Send Test Email</strong> sends the real thing immediately, to the same <strong>Send To</strong> addresses, so save a valid address first. Use it to check the layout before you leave the schedule running.
        </p>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Nothing to send</div>
            <p>If no upcoming event has a flyer image when the send is due, no email goes out, and the schedule tries again at the next cadence. A test email reports the same thing instead of sending an empty graphic.</p>
        </div>
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
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a> - More ways to share your events</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events and upload the flyers this page composes</li>
            <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Schedule Styling</a> - The colors the graphic inherits</li>
            <li><a href="{{ route('marketing.docs.analytics') }}" class="doc-link">Analytics</a> - Track views and engagement from shared graphics</li>
            <li><a href="{{ route('marketing.docs.newsletters') }}" class="doc-link">Newsletters</a> - Send designed email campaigns to your audience</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Generate Event Graphics with Event Schedule",
            "description": "Learn how to use the Event Graphics feature to generate shareable images and text for your upcoming events.",
            "totalTime": "PT5M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Open Event Graphics",
                    "text": "Open your schedule in the admin panel and choose Events Graphic from the Actions menu.",
                    "url": "{{ url(route('marketing.docs.event_graphics')) }}#overview"
                },
                {
                    "@type": "HowToStep",
                    "name": "Choose a layout and size",
                    "text": "Pick Grid, Rows or List on the Graphic tab, then fit the result to a square, portrait, story or landscape shape.",
                    "url": "{{ url(route('marketing.docs.event_graphics')) }}#overview"
                },
                {
                    "@type": "HowToStep",
                    "name": "Customize the text template",
                    "text": "Edit the text template using variables like {event_name}, {date_dmy}, {time}, and {venue} to control how event details are formatted.",
                    "url": "{{ url(route('marketing.docs.event_graphics')) }}#text-template"
                },
                {
                    "@type": "HowToStep",
                    "name": "Download and share",
                    "text": "Download or copy the image and copy the text to share on Instagram, Facebook, Twitter/X, WhatsApp, Telegram, or in a newsletter.",
                    "url": "{{ url(route('marketing.docs.event_graphics')) }}#overview"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
