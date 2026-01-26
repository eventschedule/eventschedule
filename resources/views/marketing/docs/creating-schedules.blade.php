<x-marketing-layout>
    <x-slot name="title">Creating Schedules - Event Schedule</x-slot>
    <x-slot name="description">Learn how to create and configure your schedule in Event Schedule. Customize styling, colors, fonts, backgrounds, and more.</x-slot>
    <x-slot name="keywords">create schedule, schedule settings, customize schedule, event calendar styling, schedule configuration, custom branding</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-16 overflow-hidden border-b border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Creating Schedules" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">Creating Schedules</h1>
            </div>
            <p class="text-lg text-gray-400 max-w-3xl">
                Set up your schedule and customize its appearance with colors, fonts, backgrounds, and more.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#introduction" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Introduction</a>
                        <a href="#basic-information" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Basic Information</a>
                        <a href="#location" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Location & Address</a>
                        <a href="#contact" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Contact Information</a>
                        <a href="#styling" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Styling Your Schedule</a>
                        <a href="#settings" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Schedule Settings</a>
                        <a href="#subschedules" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Subschedules</a>
                        <a href="#auto-import" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Auto Import</a>
                        <a href="#calendar-integrations" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Calendar Integrations</a>
                        <a href="#email-settings" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Email Settings</a>
                        <a href="#next-steps" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Next Steps</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Introduction -->
                        <section id="introduction" class="doc-section">
                            <h2 class="doc-heading">Introduction</h2>
                            <p class="text-gray-300 mb-6">A schedule is your event calendar - it's where all your events live. Each schedule gets its own unique URL that you can share with your audience. Before diving into the settings, make sure you've <a href="{{ route('marketing.docs.getting_started') }}" class="text-cyan-400 hover:text-cyan-300">created your account</a>.</p>

                            <p class="text-gray-300 mb-6">Event Schedule supports three types of schedules, each designed for different use cases:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Best For</th>
                                            <th>Key Features</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-white">Talent</span></td>
                                            <td>Musicians, DJs, performers, speakers</td>
                                            <td>Events display venues, focused on "where you'll be"</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Venue</span></td>
                                            <td>Bars, clubs, theaters, event spaces</td>
                                            <td>Full address support, map integration</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Curator</span></td>
                                            <td>Promoters, bloggers, community organizers</td>
                                            <td>Aggregate events from multiple sources</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>You can change your schedule type at any time in the settings. The type affects how your events are displayed and what information is shown.</p>
                            </div>
                        </section>

                        <!-- Basic Information -->
                        <section id="basic-information" class="doc-section">
                            <h2 class="doc-heading">Basic Information</h2>
                            <p class="text-gray-300 mb-6">The Details section contains your schedule's core identity information.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Schedule Name</h4>
                                    <p class="text-sm text-gray-400">Your schedule's display name. This appears at the top of your schedule page and in search results. Use your band name, venue name, or organization name.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">English Name</h4>
                                    <p class="text-sm text-gray-400">If your schedule name is in a non-English language, you can provide an English translation. This helps with discoverability and accessibility for international visitors.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Description</h4>
                                    <p class="text-sm text-gray-400">A bio or description of your schedule. Supports <strong class="text-white">Markdown formatting</strong> for links, bold text, lists, and more. Tell visitors what you're about and what kind of events they can expect.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Location & Address -->
                        <section id="location" class="doc-section">
                            <h2 class="doc-heading">Location & Address</h2>
                            <p class="text-gray-300 mb-6">For <strong class="text-white">Venue</strong> schedules, you can add a full physical address. This enables map integration and helps visitors find your location.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Street Address</h4>
                                    <p class="text-sm text-gray-400">Your venue's street address (e.g., "123 Main Street").</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">City, State/Province, Postal Code</h4>
                                    <p class="text-sm text-gray-400">Fill in your city, state or province, and postal/zip code for complete address information.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Country</h4>
                                    <p class="text-sm text-gray-400">Select your country from the dropdown. This is used for address formatting and map display.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Address Validation</div>
                                <p>When you enter an address, Event Schedule validates it and generates map coordinates. This powers the interactive map on your schedule page.</p>
                            </div>
                        </section>

                        <!-- Contact Information -->
                        <section id="contact" class="doc-section">
                            <h2 class="doc-heading">Contact Information</h2>
                            <p class="text-gray-300 mb-6">Add contact details so visitors can reach you. These appear on your public schedule page.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Email Address</h4>
                                    <p class="text-sm text-gray-400">A public contact email for inquiries. Use the <strong class="text-white">"Show email"</strong> toggle to control whether this is visible to visitors.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Phone Number</h4>
                                    <p class="text-sm text-gray-400">A contact phone number. Displayed as a clickable link on mobile devices.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Website URL</h4>
                                    <p class="text-sm text-gray-400">Link to your main website. Opens in a new tab when visitors click it.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">City/Country</h4>
                                    <p class="text-sm text-gray-400">For non-Venue schedules (Talent, Curator), you can specify your city and country. This appears on your profile without requiring a full street address.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Styling Your Schedule -->
                        <section id="styling" class="doc-section">
                            <h2 class="doc-heading">Styling Your Schedule</h2>
                            <p class="text-gray-300 mb-6">Make your schedule uniquely yours with extensive styling options. All changes preview in real-time so you can see exactly how your schedule will look.</p>

                            <!-- Event Layout -->
                            <h3 class="text-xl font-semibold text-white mb-4 mt-8">Event Layout</h3>
                            <p class="text-gray-300 mb-4">Choose how your events are displayed:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Grid Layout</h4>
                                    <p class="text-sm text-gray-400">Events displayed in a card grid. Best for schedules with event images and visual appeal.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">List Layout</h4>
                                    <p class="text-sm text-gray-400">Events displayed in a compact list. Best for text-heavy schedules or when you have many events.</p>
                                </div>
                            </div>

                            <!-- Profile Image -->
                            <h3 class="text-xl font-semibold text-white mb-4 mt-8">Profile Image</h3>
                            <p class="text-gray-300 mb-4">Upload a profile image (logo, photo, or avatar) that represents your schedule.</p>
                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Best Practices</div>
                                <p>Use a <strong>square image</strong> (1:1 aspect ratio) for best results. Recommended minimum size is 400x400 pixels. PNG or JPG formats work best.</p>
                            </div>

                            <!-- Header Images -->
                            <h3 class="text-xl font-semibold text-white mb-4 mt-8">Header Images</h3>
                            <p class="text-gray-300 mb-4">Add a header image to create a visual banner at the top of your schedule page. You have three options:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Preset Headers</h4>
                                    <p class="text-sm text-gray-400">Choose from 32 professionally designed headers including: 5am_Club, Arena, Fitness_Morning, Concert, Festival, Jazz_Club, Neon_Lights, Skyline, Stadium, and many more. Perfect for getting started quickly.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Custom Header Upload</h4>
                                    <p class="text-sm text-gray-400">Upload your own header image. Recommended dimensions: 1200x400 pixels or similar wide aspect ratio.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">No Header</h4>
                                    <p class="text-sm text-gray-400">Opt for a cleaner look without a header image. Your profile image and name take center stage.</p>
                                </div>
                            </div>

                            <!-- Background Options -->
                            <h3 class="text-xl font-semibold text-white mb-4 mt-8">Background Options</h3>
                            <p class="text-gray-300 mb-4">Customize your schedule's background with colors, gradients, or images.</p>

                            <h4 class="text-lg font-medium text-white mb-3 mt-6">Solid Color</h4>
                            <p class="text-gray-300 mb-4">Choose any color using the hex color picker. Enter a specific hex code (e.g., <code class="doc-inline-code">#1a1a2e</code>) or use the visual picker.</p>

                            <h4 class="text-lg font-medium text-white mb-3 mt-6">Gradient Backgrounds</h4>
                            <p class="text-gray-300 mb-4">Choose from 200+ beautiful preset gradients or create your own:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Preset Gradients</h4>
                                    <p class="text-sm text-gray-400">Browse gradients by name: Omolon, Purple Dream, Netflix, Sunset, Ocean Blue, Emerald Water, Cosmic Fusion, and 190+ more. Hover to preview, click to apply.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Custom 2-Color Gradients</h4>
                                    <p class="text-sm text-gray-400">Pick two colors to create your own gradient. Perfect for matching your brand colors exactly.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Rotation Control</h4>
                                    <p class="text-sm text-gray-400">Adjust the gradient angle from 0 to 360 degrees. Create vertical, horizontal, diagonal, or any angle you want.</p>
                                </div>
                            </div>

                            <h4 class="text-lg font-medium text-white mb-3 mt-6">Background Images</h4>
                            <p class="text-gray-300 mb-4">Add depth with background images:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Preset Backgrounds</h4>
                                    <p class="text-sm text-gray-400">Choose from 37 preset backgrounds including: Abstract_Sunrise, Ocean, Mountains, City_Lights, Stars, Geometric, Marble, and more. All optimized for performance.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Custom Image Upload</h4>
                                    <p class="text-sm text-gray-400">Upload your own background image. Images are automatically optimized. Dark, subtle patterns work best to maintain readability.</p>
                                </div>
                            </div>

                            <!-- Color Scheme -->
                            <h3 class="text-xl font-semibold text-white mb-4 mt-8">Color Scheme</h3>
                            <p class="text-gray-300 mb-4">Set your accent color to match your brand:</p>
                            <div class="bg-white/5 rounded-xl p-4 border border-white/10 mb-6">
                                <h4 class="font-semibold text-white mb-2">Accent Color</h4>
                                <p class="text-sm text-gray-400">This color is used for buttons, links, icons, and interactive elements throughout your schedule. Event Schedule automatically calculates contrasting text colors for accessibility.</p>
                            </div>
                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Accessibility</div>
                                <p>Choose an accent color with good contrast. Event Schedule automatically adjusts text colors to ensure readability, but vibrant, saturated colors tend to work best.</p>
                            </div>

                            <!-- Typography -->
                            <h3 class="text-xl font-semibold text-white mb-4 mt-8">Typography</h3>
                            <p class="text-gray-300 mb-4">Choose from 200+ Google Fonts to give your schedule a unique personality:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Font Categories</h4>
                                    <p class="text-sm text-gray-400"><strong class="text-white">Sans-serif</strong> (clean, modern), <strong class="text-white">Serif</strong> (classic, elegant), <strong class="text-white">Display</strong> (bold, attention-grabbing), <strong class="text-white">Decorative</strong> (unique, artistic).</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Live Preview</h4>
                                    <p class="text-sm text-gray-400">See how each font looks on your schedule in real-time before committing. Test readability with your content.</p>
                                </div>
                            </div>

                            <!-- Custom CSS -->
                            <h3 class="text-xl font-semibold text-white mb-4 mt-8">Custom CSS</h3>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Custom CSS is available on Pro plans and for selfhosted installations.</p>
                            </div>
                            <p class="text-gray-300 mb-4">For advanced customization, write your own CSS to modify any aspect of your schedule's appearance:</p>
                            <ul class="doc-list mb-6">
                                <li>Override default styles for complete control</li>
                                <li>Add custom animations and effects</li>
                                <li>Fine-tune spacing, borders, and shadows</li>
                                <li>CSS is sanitized for security - modern properties supported</li>
                            </ul>

                            <!-- Live Preview -->
                            <h3 class="text-xl font-semibold text-white mb-4 mt-8">Live Preview</h3>
                            <p class="text-gray-300 mb-4">All styling changes update in real-time. The preview panel shows exactly how your schedule will look to visitors, so you can experiment freely without publishing incomplete changes.</p>
                        </section>

                        <!-- Schedule Settings -->
                        <section id="settings" class="doc-section">
                            <h2 class="doc-heading">Schedule Settings</h2>
                            <p class="text-gray-300 mb-6">Configure how your schedule works and behaves.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Schedule URL / Subdomain</h4>
                                    <p class="text-sm text-gray-400">Your unique URL identifier. On the hosted version, this becomes <code class="doc-inline-code">yourname.eventschedule.com</code>. Choose something memorable and easy to type.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Custom Domain</h4>
                                    <p class="text-sm text-gray-400"><span class="text-cyan-400 text-xs font-medium">PRO</span> Use your own domain (e.g., <code class="doc-inline-code">events.yourbrand.com</code>) instead of a subdomain. Requires DNS configuration.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Language</h4>
                                    <p class="text-sm text-gray-400">Choose from 9 supported languages: English, Spanish, German, French, Italian, Portuguese, Hebrew, Dutch, and Arabic. This affects the interface language on your schedule page.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Timezone</h4>
                                    <p class="text-sm text-gray-400">Set your schedule's timezone. All event times are displayed in this timezone. Important for audiences in multiple regions.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Time Format</h4>
                                    <p class="text-sm text-gray-400">Choose between 12-hour (2:00 PM) or 24-hour (14:00) time format based on your audience's preference.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Accept Event Requests</h4>
                                    <p class="text-sm text-gray-400">Allow others to submit events to your schedule. Great for venues accepting booking requests or curators accepting submissions.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Require Approval</h4>
                                    <p class="text-sm text-gray-400">When enabled, submitted events go to a pending queue for your approval before appearing publicly.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Request Terms</h4>
                                    <p class="text-sm text-gray-400">Add custom terms or guidelines that submitters must agree to when requesting events.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Unlisted Schedule</h4>
                                    <p class="text-sm text-gray-400">Make your schedule private - it won't appear in search results or public listings. Only people with the direct link can access it.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Subschedules -->
                        <section id="subschedules" class="doc-section">
                            <h2 class="doc-heading">Subschedules (Categories)</h2>
                            <p class="text-gray-300 mb-6">Organize your events into categories using subschedules. This helps visitors filter and find events that interest them.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Creating Groups</h4>
                                    <p class="text-sm text-gray-400">Add groups/categories like "Live Music", "DJ Nights", "Comedy Shows", or "Workshops". Each group gets its own URL and can be filtered.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Group Name & English Name</h4>
                                    <p class="text-sm text-gray-400">Like schedules, groups can have localized names with English translations for multilingual support.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">URL Slugs</h4>
                                    <p class="text-sm text-gray-400">Each category gets a URL slug (e.g., <code class="doc-inline-code">/live-music</code>) so visitors can bookmark and share filtered views.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Auto Import -->
                        <section id="auto-import" class="doc-section">
                            <h2 class="doc-heading">Auto Import</h2>
                            <p class="text-gray-300 mb-6">Automatically import events from external sources to keep your schedule up-to-date without manual entry.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Import from URLs</h4>
                                    <p class="text-sm text-gray-400">Add URLs of event pages, venue calendars, or artist websites. Event Schedule's AI will automatically parse and import events from these sources on a regular schedule.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Import by City Search</h4>
                                    <p class="text-sm text-gray-400">Search for events by city name to automatically discover and import local events. Great for curators building comprehensive local calendars.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Calendar Integrations -->
                        <section id="calendar-integrations" class="doc-section">
                            <h2 class="doc-heading">Calendar Integrations</h2>
                            <p class="text-gray-300 mb-6">Sync your schedule with external calendar systems for seamless event management.</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Google Calendar Sync</h4>
                                    <p class="text-sm text-gray-400">Connect your Google Calendar for bidirectional sync. Events created in either place stay synchronized automatically. Supports webhook-based real-time updates.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">CalDAV Sync</h4>
                                    <p class="text-sm text-gray-400">Connect to any CalDAV-compatible calendar (Apple Calendar, Fastmail, Nextcloud, etc.) for cross-platform synchronization.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Sync Direction Options</h4>
                                    <p class="text-sm text-gray-400">Choose one-way sync (import only or export only) or two-way sync to keep both calendars in perfect harmony.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Selfhost Note</div>
                                <p>Google Calendar integration requires API credentials configuration. See the <a href="{{ route('marketing.docs.selfhost.google_calendar') }}" class="text-cyan-400 hover:text-cyan-300">selfhost Google Calendar docs</a> for setup instructions.</p>
                            </div>
                        </section>

                        <!-- Email Settings -->
                        <section id="email-settings" class="doc-section">
                            <h2 class="doc-heading">Email Settings</h2>
                            <p class="text-gray-300 mb-6">Configure email delivery for your schedule's notifications and communications.</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Availability</div>
                                <p>Custom email settings are available for selfhosted installations and Pro plans.</p>
                            </div>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">SMTP Configuration</h4>
                                    <p class="text-sm text-gray-400">Configure your own SMTP server for sending emails. This gives you full control over deliverability and lets you use your email provider.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Custom Sender Address</h4>
                                    <p class="text-sm text-gray-400">Send emails from your own domain (e.g., <code class="doc-inline-code">events@yourdomain.com</code>) instead of the default Event Schedule address.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Next Steps -->
                        <section id="next-steps" class="doc-section">
                            <h2 class="doc-heading">Next Steps</h2>
                            <p class="text-gray-300 mb-6">Now that your schedule is configured, here's what to do next:</p>

                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Add your first events</a> - Learn how to create and import events</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Share your schedule</a> - Embed on your website and share on social media</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Set up ticketing</a> - Start selling tickets for your events</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Generate event graphics</a> - Create shareable images for social media</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
