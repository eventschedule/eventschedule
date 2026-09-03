<x-docs-page
    key="schedule-styling"
    description="Customize your schedule's appearance with colors, fonts, backgrounds, and more. Make your schedule uniquely yours."
    lede="Customize your schedule's visual appearance with colors, fonts, backgrounds, and more. All changes preview in real-time."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#event-layout">Event Layout</x-doc-nav-link>
        <x-doc-nav-link href="#header-style">Header Style</x-doc-nav-link>
        <x-doc-nav-link href="#profile-image">Profile Image</x-doc-nav-link>
        <x-doc-nav-link href="#header-images">Header Images</x-doc-nav-link>
        <x-doc-nav-link href="#backgrounds">Background Options</x-doc-nav-link>
        <x-doc-nav-link href="#color-scheme">Color Scheme</x-doc-nav-link>
        <x-doc-nav-link href="#typography">Typography</x-doc-nav-link>
        <x-doc-nav-link href="#ai-style-generator">AI Style Generator</x-doc-nav-link>
        <x-doc-nav-link href="#remove-branding">Remove Branding</x-doc-nav-link>
        <x-doc-nav-link href="#custom-css">Custom CSS</x-doc-nav-link>
        <x-doc-nav-link href="#live-preview">Live Preview</x-doc-nav-link>
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
        <p class="text-gray-600 dark:text-gray-300 mb-4">Every appearance setting for a schedule lives in one place:</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Open the schedule in the admin panel and click <strong class="text-gray-900 dark:text-white">Edit Schedule</strong>.</li>
            <li>Choose <strong class="text-gray-900 dark:text-white">Style</strong> in the section list on the left (or from the accordion on mobile).</li>
            <li>Work through the three sub-tabs, watching the preview beside the form.</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Save</strong>. Nothing is applied to your public page until you do.</li>
        </ol>

        <x-doc-screenshot id="schedule-styling--section-style" alt="Schedule styling settings" loading="eager" />

        <p class="text-gray-600 dark:text-gray-300 mb-4">The Style section is split into three sub-tabs:</p>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Sub-tab</th>
                        <th>What it holds</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Branding</span></td>
                        <td>Square Profile Image, Accent Color, Font Family</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Background</span></td>
                        <td>Background Type (Gradient, Solid or Image) and the controls for whichever you pick: gradient colors and rotation, a solid color, or a background image</td>
                    </tr>
                    <tr>
                        <td><span class="font-semibold text-gray-900 dark:text-white">Advanced</span></td>
                        <td>Header Style, Header Image, Default Layout, Custom CSS</td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">New here?</div>
            <p>If you have not created your account and first schedule yet, start with <a href="{{ route('marketing.docs.getting_started') }}" class="doc-link">Getting Started</a>. To set the schedule's name, description, links and other non-visual details, see <a href="{{ route('marketing.docs.creating_schedules') }}" class="doc-link">Creating Schedules</a>.</p>
        </div>
    </section>

    <!-- Event Layout -->
    <section id="event-layout" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
            </svg>
            Event Layout
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Choose how your events are displayed on your schedule page, under <strong class="text-gray-900 dark:text-white">Edit Schedule &rarr; Style &rarr; Advanced &rarr; Default Layout</strong>. Two layouts exist:</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Calendar</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Events laid out on a month grid, one cell per day, loaded one month at a time. Best when the shape of the month matters, such as a venue with something on most nights.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">List</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Events in date order as full-width cards, loaded well beyond the current month. Best when each event deserves its own space, or when they are spread thinly across the year.</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">This is only the default. Visitors can switch between the two with the layout toggle in the header, and their choice is remembered in their own browser for that schedule. It never changes what anyone else sees, and it does not change your setting.</p>
        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Per-embed override</div>
            <p>Embeds ignore the visitor's saved preference, so the site doing the embedding stays in control. An embed can also override your default with <code class="doc-inline-code">?layout=calendar</code> or <code class="doc-inline-code">?layout=list</code>, so one page can carry the same schedule twice in both layouts. See <a href="{{ route('marketing.docs.sharing') }}#embed-parameters" class="doc-link">Embed URL Parameters</a>.</p>
        </div>
    </section>

    <!-- Header Style -->
    <section id="header-style" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 8.25V18a2.25 2.25 0 002.25 2.25h13.5A2.25 2.25 0 0021 18V8.25m-18 0V6a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 6v2.25m-18 0h18" />
            </svg>
            Header Style
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Choose how the header appears at the top of your public schedule page under <strong class="text-gray-900 dark:text-white">Edit Schedule &rarr; Style &rarr; Advanced &rarr; Header Style</strong>. Schedules use <strong class="text-gray-900 dark:text-white">Banner</strong> unless you change it, and the live preview updates as you switch between the two.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Banner</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A large header carrying your header image, a prominent logo, the schedule name, description and social links. The most spacious option, and the only one that can show a header image or a logo wall.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Compact</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A slim full-width bar at the top of the page with a small logo and the schedule name on one side, and the action cluster on the other: follow, submit and manage buttons, the filter button, and the calendar or list toggle. Your description and social or contact links move to a strip just beneath the bar.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Header image and Compact</div>
            <p>The header image applies to the <strong class="text-gray-900 dark:text-white">Banner</strong> style only. Compact draws no header image at all, but nothing is lost: the description and social links appear in the strip below the bar. If a schedule was set to the old <strong class="text-gray-900 dark:text-white">Minimal</strong> style, it now reads as Compact, which is the same slim row under a clearer name.</p>
        </div>
    </section>

    <!-- Profile Image -->
    <section id="profile-image" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0012 15.75a7.488 7.488 0 00-5.982 2.975m11.963 0a9 9 0 10-11.963 0m11.963 0A8.966 8.966 0 0112 21a8.966 8.966 0 01-5.982-2.275M15 9.75a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Profile Image
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Upload a logo, photo or avatar that represents your schedule under <strong class="text-gray-900 dark:text-white">Edit Schedule &rarr; Style &rarr; Branding &rarr; Square Profile Image</strong>. It does three jobs:</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Your header</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A large logo in the Banner header style, or a small square avatar beside your name in the Compact style.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Your social preview</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">It becomes the preview image when your schedule page is shared to a chat app or social network. Without it, the generic Event Schedule image is used instead.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Talent and venue logo walls</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">It is the logo other schedules show for you in their <a href="#header-images" class="doc-link">logo wall header</a>. A connected schedule with no profile image is skipped there.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-tip mb-6">
            <div class="doc-callout-title">Best practices</div>
            <p>Use a <strong class="text-gray-900 dark:text-white">square image</strong> (1:1 aspect ratio). Recommended minimum size is 400x400 pixels. PNG and JPG are accepted, and uploads must be under 2.5 MB. Use the small red cross on the thumbnail to remove an image you have already saved.</p>
        </div>

        <h3 class="doc-subheading">It becomes your favicon too <x-doc-badge plan="pro" /></h3>
        <p class="text-gray-600 dark:text-gray-300">On Pro, your profile image also becomes the icon in the browser tab on your public pages, including ticket pages. On every plan it becomes the home-screen icon when a visitor saves your schedule to their phone, and a schedule that has uploaded no image gets a plain calendar glyph there rather than the Event Schedule logo. Nothing to configure: upload a profile image and it is used automatically. This is the same reason a square image matters, since a tab icon is cropped to a square either way.</p>
    </section>

    <!-- Header Images -->
    <section id="header-images" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
            </svg>
            Header Images
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Header Image</strong> dropdown on the Advanced tab creates the visual banner at the top of your schedule page. It offers four kinds of option, in this order:</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">None</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The default. A cleaner look with no header image, so your profile image and name take centre stage.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Venue logo wall (or Talent logo wall)</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A grid of logos instead of a picture. On a talent or curator schedule the option is called <strong class="text-gray-900 dark:text-white">Venue logo wall</strong> and shows the venues hosting your approved events; on a venue schedule it is called <strong class="text-gray-900 dark:text-white">Talent logo wall</strong> and shows the talent performing at them. Each logo links to that schedule's page, and you can drag the list under the dropdown to set the order. Only connected schedules that have a profile image appear, so the wall stays empty until at least one does, and the edit page warns you when that is the case.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Upload your own header. Use a wide image, around 1200x400 pixels or a similar aspect ratio. PNG or JPG, under 2.5 MB.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">31 preset headers</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ready-made headers listed alphabetically, including 5am Club, Arena, Fitness Morning, Music Potential, Networking and Bagels, Ready to Dance, The Stage Awaits and Yoga and Wellness. Use the arrows beside the dropdown to flick through them with the preview open.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Needs the Banner header style</div>
            <p>Header images and the logo wall are drawn by the <strong class="text-gray-900 dark:text-white">Banner</strong> <a href="#header-style" class="doc-link">header style</a> only. Choosing one while the schedule is set to Compact has no visible effect.</p>
        </div>
    </section>

    <!-- Background Options -->
    <section id="backgrounds" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.098 19.902a3.75 3.75 0 005.304 0l6.401-6.402M6.75 21A3.75 3.75 0 013 17.25V4.125C3 3.504 3.504 3 4.125 3h5.25c.621 0 1.125.504 1.125 1.125v4.072M6.75 21a3.75 3.75 0 003.75-3.75V8.197M6.75 21h13.125c.621 0 1.125-.504 1.125-1.125v-5.25c0-.621-.504-1.125-1.125-1.125h-4.072M10.5 8.197l2.88-2.88c.438-.439 1.15-.439 1.59 0l3.712 3.713c.44.44.44 1.152 0 1.59l-2.879 2.88M6.75 17.25h.008v.008H6.75v-.008z" />
            </svg>
            Background Options
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Background</strong> tab starts with a <strong class="text-gray-900 dark:text-white">Background Type</strong> choice of Gradient, Solid or Image. Picking one reveals its own controls and hides the others.</p>

        <h3 class="doc-subheading">Gradient</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Pick a preset from the searchable <strong class="text-gray-900 dark:text-white">Colors</strong> dropdown, or choose <strong class="text-gray-900 dark:text-white">Custom</strong> at the top of it to mix your own.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Preset gradients</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Over 380 named gradients from uiGradients, listed alphabetically: Cosmic Fusion, Emerald Water, Netflix, Omolon, Purple Dream, Sunset and many more. The arrows beside the dropdown step through them so you can watch the preview change.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom two-color gradient</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choosing Custom reveals two color pickers and a swatch of the result, so you can match your brand colors exactly.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Rotation</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">A slider from 0 to 360 degrees sets the gradient angle, so you can make it vertical, horizontal, diagonal or anything between. It applies to preset and custom gradients alike.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Solid</h3>
        <p class="text-gray-600 dark:text-gray-300 mb-6">A single <strong class="text-gray-900 dark:text-white">Background Color</strong> picker. Use your operating system's color picker or type a six-digit hex code such as <code class="doc-inline-code">#1a1a2e</code>.</p>

        <h3 class="doc-subheading">Image</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">37 preset backgrounds</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Ready-made backgrounds listed alphabetically, including Abstract Sunrise, Bookshelf, Desert Retreat, Flower Field, Greyscale, River of Dreams and Stormy Night. Selecting one shows a thumbnail below the dropdown.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom upload</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Choose <strong class="text-gray-900 dark:text-white">Custom</strong> at the top of the dropdown to upload your own. PNG or JPG, under 2.5 MB. Dark, low-contrast images work best, because your event cards and text sit on top of them.</p>
            </div>
        </div>
    </section>

    <!-- Color Scheme -->
    <section id="color-scheme" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
            </svg>
            Color Scheme
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">One <strong class="text-gray-900 dark:text-white">Accent Color</strong> picker on the Branding tab drives every highlighted element on your public pages. New schedules start on a standard blue.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Where it appears</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Buttons and calls to action, the follow and submit controls, the calendar and list layout toggles, the filter button, and highlights on your event pages.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Text on top of it is calculated</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Event Schedule measures the brightness of your accent color and puts black text on light accents and white text on dark ones, so a button label stays readable whatever you choose. There is nothing to set.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Accessibility</div>
            <p>The automatic black-or-white text keeps buttons legible, but it cannot rescue an accent that disappears into your background. Check the preview with your chosen background, and pick a saturated color that clearly separates from it.</p>
        </div>
    </section>

    <!-- Typography -->
    <section id="typography" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 016-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 01-3.827-5.802" />
            </svg>
            Typography
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">The <strong class="text-gray-900 dark:text-white">Font Family</strong> dropdown on the Branding tab offers more than 230 Google Fonts. It sets the typeface for your schedule name and headings on your public pages.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Finding a font</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The dropdown is searchable, so you can type a name you already know. The arrows beside it step to the previous or next font alphabetically, which is the quickest way to browse.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Live sample</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Your schedule's own name is rendered in the chosen font directly under the dropdown, and the preview panel updates too, so you can judge readability on your real text before saving.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Event graphics use their own font</div>
            <p>This setting styles web pages. <a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event graphics</a> are drawn as images on the server with a bundled Noto Sans family (including Hebrew and Arabic variants), so changing your font here will not change them.</p>
        </div>
    </section>

    <!-- AI Style Generator -->
    <section id="ai-style-generator" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            AI Style Generator <x-doc-badge plan="enterprise" />
        </h2>
        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Enterprise feature</div>
            <p><x-doc-badge plan="enterprise" /> AI style generation needs the <strong class="text-gray-900 dark:text-white">Enterprise</strong> plan. A <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install counts as Enterprise, so nothing here is held back by plan there, but it does need its own AI keys (see below).</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Let AI put together a coherent look for your schedule from its name, type and description, so the color, font and images belong to each other rather than being chosen one at a time.</p>
        <ol class="doc-list doc-list-numbered mb-6">
            <li>Click <strong class="text-gray-900 dark:text-white">AI Generator</strong> in the top-right corner of the Style section header.</li>
            <li>Tick which of the five elements to generate: profile image, header image, background image, accent color, or font. The dialog marks the ones you have already set, so you can leave them alone.</li>
            <li>Optionally add style instructions, for example "modern and minimal with blue tones". Tick the box to save them for next time.</li>
            <li>Generate, then review the previews in the dialog. <strong class="text-gray-900 dark:text-white">Accept</strong> drops the results into the Style form, <strong class="text-gray-900 dark:text-white">Discard</strong> throws them away, and nothing reaches your public page until you click Save on the form itself.</li>
        </ol>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Color and font come first</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">The accent color and font are generated in one step, then the images are generated with that color passed in, so they share a palette. If you leave the profile image out of the selection, the existing one is described to the model instead, so a new header still matches it.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Daily limits</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">On the hosted platform, AI text and AI image generation each have a daily allowance per schedule, shared with the other AI features. A selfhosted install has no such limit. AI generation is disabled in demo mode.</p>
            </div>
        </div>
        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Selfhosted API keys</div>
            <p>Selfhosted installs supply their own keys. The accent color and font need a <a href="https://ai.google.dev/" target="_blank" rel="noopener" class="doc-link">Gemini API key</a>. Image generation uses an <a href="https://platform.openai.com/" target="_blank" rel="noopener" class="doc-link">OpenAI API key</a> when one is set and falls back to Gemini when it is not, so a Gemini key alone is enough to use every part of the generator. The button does not appear at all until at least one key is configured.</p>
        </div>
    </section>

    <!-- Remove Branding -->
    <section id="remove-branding" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
            </svg>
            Remove Branding <x-doc-badge plan="pro" />
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Free schedules on the hosted platform carry a dark "Powered by Event Schedule" strip at the foot of their public pages. There is no switch for it: it is removed automatically the moment the schedule is on <strong class="text-gray-900 dark:text-white">Pro</strong> or <strong class="text-gray-900 dark:text-white">Enterprise</strong>, and it returns if the plan lapses.</p>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">What a paid plan removes</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">Five surfaces at once: the footer strip, the "create your own" card on your event pages, the credit line printed under your embed snippets, the same line inside a ticket or RSVP embed, and the footer of your newsletter emails. Ads, where a platform runs them on free schedules, follow the same tier. Embeds of your calendar never carry any of it, on any plan.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">White-label your schedule</h4>
                <p class="text-sm text-gray-500 dark:text-gray-400">With those gone, a hosted schedule reads entirely as your own to visitors. Pair it with a <a href="{{ route('marketing.docs.creating_schedules') }}#custom-domain" class="doc-link">custom domain</a> on Enterprise and nothing on the page points back at Event Schedule.</p>
            </div>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A single-tenant <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> install has no plan tiers at all, so the strip, the event-page card, the embed and newsletter lines and ads are all absent by default, with nothing to buy. If instead you run your own multi-tenant platform on Event Schedule, those surfaces follow each tenant's tier exactly as they do on eventschedule.com, because the strip is the growth prompt of whoever runs the platform.</p>
        <p class="text-gray-600 dark:text-gray-300 mb-4">One small "Event Schedule" credit stays in the corner of public pages on every install that is not eventschedule.com itself. It is the attribution the Attribution Assurance License asks for in return for the application, so there is no setting that removes it. On a single-tenant selfhost it is on every page. On a platform of your own it is on every schedule you charge for, and a free schedule there carries the operator's footer strip in its place, because a page never shows both credits at once.</p>
    </section>

    <!-- Custom CSS -->
    <section id="custom-css" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
            </svg>
            Custom CSS <x-doc-badge plan="pro" />
        </h2>
        <div class="doc-callout doc-callout-plan">
            <div class="doc-callout-title">Pro feature</div>
            <p><x-doc-badge plan="pro" /> The Custom CSS box is editable on <strong class="text-gray-900 dark:text-white">Pro</strong> and <strong class="text-gray-900 dark:text-white">Enterprise</strong>, and on <a href="{{ route('marketing.docs.selfhost') }}" class="doc-link">selfhosted</a> installs, which count as Enterprise. Below Pro the box is shown read-only: CSS you saved earlier is kept, not deleted, and becomes editable again when you upgrade.</p>
        </div>
        <p class="text-gray-600 dark:text-gray-300 mb-4">When the built-in controls do not reach far enough, write your own CSS under <strong class="text-gray-900 dark:text-white">Edit Schedule &rarr; Style &rarr; Advanced &rarr; Custom CSS</strong>. It is added to the stylesheet of your public pages, which means your schedule page, your event pages and embeds of them.</p>
        <ul class="doc-list mb-6">
            <li>Override any of the built-in styles for complete control</li>
            <li>Fine-tune spacing, borders, radii and shadows</li>
            <li>Add your own animations and effects</li>
            <li>Up to 10,000 characters per schedule</li>
        </ul>
        <div class="doc-callout doc-callout-warning">
            <div class="doc-callout-title">What the sanitizer strips</div>
            <p>Saved CSS is cleaned before it is stored, so a few constructs are silently removed rather than rejected with an error. Do not rely on <code class="doc-inline-code">@import</code>, <code class="doc-inline-code">@font-face</code>, <code class="doc-inline-code">@charset</code>, <code class="doc-inline-code">expression()</code>, <code class="doc-inline-code">javascript:</code> URLs, <code class="doc-inline-code">behavior</code> or <code class="doc-inline-code">binding</code>, or any <code class="doc-inline-code">url()</code> pointing at an <code class="doc-inline-code">http:</code>, <code class="doc-inline-code">https:</code> or <code class="doc-inline-code">data:</code> address. Everything else, including modern layout and animation properties, is kept as written. If a rule seems to vanish after saving, check it against that list first.</p>
        </div>
    </section>

    <!-- Live Preview -->
    <section id="live-preview" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
            </svg>
            Live Preview
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">A <strong class="text-gray-900 dark:text-white">Preview</strong> panel sits beside the Style form on wide screens and below it on narrow ones. It stays visible across all three sub-tabs and redraws as you change the header style, images, background, accent color and font, so you can experiment freely without publishing anything half-finished.</p>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Save when you are happy</div>
            <p>The preview is only a preview. Click <strong class="text-gray-900 dark:text-white">Save</strong> at the bottom of the edit page to apply your changes, then open your schedule page in a new tab to see the real thing, including any custom CSS, which the preview panel does not render.</p>
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
            <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="doc-link">Creating Schedules</a> - Configure details, settings, sub-schedules, and integrations</li>
            <li><a href="{{ route('marketing.docs.managing_schedules') }}" class="doc-link">Managing Schedules</a> - Plans, team members, and booking requests</li>
            <li><a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">Event Graphics</a> - Generate shareable images for social media</li>
            <li><a href="{{ route('marketing.docs.sharing') }}" class="doc-link">Sharing Your Schedule</a> - Embed and share your schedule</li>
            <li><a href="{{ route('marketing.docs.creating_events') }}" class="doc-link">Creating Events</a> - Add events to see your styling in action</li>
        </ul>
    </section>


    <x-slot:schema>
        <script type="application/ld+json" {!! nonce_attr() !!}>
        {
            "@context": "https://schema.org",
            "@type": "HowTo",
            "name": "How to Style Your Event Schedule",
            "description": "Customize your schedule's appearance with colors, fonts, backgrounds, and more. All changes preview in real-time.",
            "totalTime": "PT5M",
            "step": [
                {
                    "@type": "HowToStep",
                    "name": "Choose Event Layout",
                    "text": "Set the default layout for your schedule page: the month calendar grid, or a list of full-width event cards.",
                    "url": "{{ url(route('marketing.docs.schedule_styling')) }}#event-layout"
                },
                {
                    "@type": "HowToStep",
                    "name": "Upload Profile and Header Images",
                    "text": "Upload a square profile image and choose a header from 31 presets, upload a custom header, display a logo wall of your venues, or use no header.",
                    "url": "{{ url(route('marketing.docs.schedule_styling')) }}#profile-image"
                },
                {
                    "@type": "HowToStep",
                    "name": "Set Background and Colors",
                    "text": "Choose a gradient from over 380 presets, a solid color, or a background image. Set your accent color for buttons and links.",
                    "url": "{{ url(route('marketing.docs.schedule_styling')) }}#backgrounds"
                },
                {
                    "@type": "HowToStep",
                    "name": "Choose Typography",
                    "text": "Select from more than 230 Google Fonts using the searchable dropdown, with a live sample of your schedule name.",
                    "url": "{{ url(route('marketing.docs.schedule_styling')) }}#typography"
                }
            ]
        }
        </script>
    </x-slot:schema>
</x-docs-page>
