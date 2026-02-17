<x-marketing-layout>
    <x-slot name="title">Event Graphics Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Event Graphics</x-slot>
    <x-slot name="description">Learn how to use the Event Graphics feature to generate shareable images and text for your upcoming events.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Event Graphics Documentation - Event Schedule",
        "description": "Learn how to use the Event Graphics feature to generate shareable images and text for your upcoming events.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png"
            }
        },
        "mainEntityOfPage": {
            "@type": "WebPage",
            "@id": "{{ url()->current() }}"
        }
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-rose-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Event Graphics" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Event Graphics</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Generate shareable images and formatted text for your upcoming events. Perfect for social media, messaging apps, and newsletters.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-white dark:bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#overview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Overview</a>
                        <a href="#text-template" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Text Template</a>
                        <a href="#quick-reference" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Quick Reference</a>
                        <a href="#variables" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">All Variables</a>
                        <a href="#ai-prompt" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">AI Text Prompt</a>
                        <a href="#screen-capture" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Screen Capture</a>
                        <a href="#email-scheduling" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Email Scheduling</a>
                        <a href="#see-also" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">See Also</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The Event Graphics feature generates a visual collage of your upcoming events along with formatted text that you can copy and share. It's perfect for:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Social media posts (Instagram, Facebook, Twitter)</li>
                                <li>WhatsApp and Telegram messages</li>
                                <li>Email <a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">newsletters</a></li>
                                <li>Website embeds</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The generated graphic displays up to 10 upcoming events with their flyer images in a grid or list layout. The text output provides event details in a format optimized for copying and pasting.
                            </p>
                        </section>

                        <!-- Text Template -->
                        <section id="text-template" class="doc-section">
                            <h2 class="doc-heading">Text Template</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The text template defines how each event is formatted in the generated text output. You can customize this template using variables that are automatically replaced with event data.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Default Template</h3>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Example Output</h3>
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
                        </section>

                        <!-- Quick Reference -->
                        <section id="quick-reference" class="doc-section">
                            <h2 class="doc-heading">Quick Reference</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Most templates only need these essential variables. Copy the ones you need:
                            </p>

                            <div class="grid md:grid-cols-2 gap-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Event Basics</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><code class="text-rose-300">{event_name}</code> <span class="text-gray-500">Event Name</span></div>
                                        <div class="flex justify-between"><code class="text-rose-300">{short_description}</code> <span class="text-gray-500">Short Description</span></div>
                                        <div class="flex justify-between"><code class="text-rose-300">{url}</code> <span class="text-gray-500">Event link</span></div>
                                        <div class="flex justify-between"><code class="text-rose-300">{description}</code> <span class="text-gray-500">Description</span></div>
                                    </div>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Date & Time</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><code class="text-rose-300">{day_name}</code> <span class="text-gray-500">Wednesday</span></div>
                                        <div class="flex justify-between"><code class="text-rose-300">{date_dmy}</code> <span class="text-gray-500">15/3</span></div>
                                        <div class="flex justify-between"><code class="text-rose-300">{time}</code> <span class="text-gray-500">20:00</span></div>
                                    </div>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Location</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><code class="text-rose-300">{venue}</code> <span class="text-gray-500">Venue name</span></div>
                                        <div class="flex justify-between"><code class="text-rose-300">{city}</code> <span class="text-gray-500">City</span></div>
                                        <div class="flex justify-between"><code class="text-rose-300">{address}</code> <span class="text-gray-500">Street</span></div>
                                    </div>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-3">Tickets</h4>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><code class="text-rose-300">{price}</code> <span class="text-gray-500">10 or Free</span></div>
                                        <div class="flex justify-between"><code class="text-rose-300">{currency}</code> <span class="text-gray-500">USD</span></div>
                                    </div>
                                </div>
                            </div>

                            <p class="text-gray-400 text-sm">See <a href="#variables" class="text-rose-400 hover:text-rose-300">All Variables</a> below for the complete list including date formats, end times, and more.</p>
                        </section>

                        <!-- Variables -->
                        <section id="variables" class="doc-section">
                            <h2 class="doc-heading">All Template Variables</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Use these variables in your template. They will be replaced with the actual event data when generating text.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Date & Time</h3>
                            <div class="overflow-x-auto mb-6">
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
                                            <td>Day/month format</td>
                                            <td>15/3</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{date_mdy}</code></td>
                                            <td>Month/day format</td>
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
                                            <td>End time (uses schedule's 24h setting)</td>
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Event Information</h3>
                            <div class="overflow-x-auto mb-6">
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
                                            <td><code class="doc-inline-code">{event_name}</code></td>
                                            <td>Event Name</td>
                                            <td>Summer Concert</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{short_description}</code></td>
                                            <td>Short Description</td>
                                            <td>Live jazz with local artists</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{description}</code></td>
                                            <td>Description</td>
                                            <td>Join us for a night of music...</td>
                                        </tr>
                                        <tr>
                                            <td><code class="doc-inline-code">{url}</code></td>
                                            <td>Event URL</td>
                                            <td>https://...</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Venue Information</h3>
                            <div class="overflow-x-auto mb-6">
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

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4"><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Ticket</a> Information</h3>
                            <div class="overflow-x-auto mb-6">
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
                                            <td>Lowest ticket price (or "Free")</td>
                                            <td>10</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Custom Fields</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                If you have defined <a href="{{ marketing_url('/features/custom-fields') }}" class="text-rose-400 hover:text-rose-300">Event Custom Fields</a> in your schedule settings, you can include their values in graphics using numbered variables.
                            </p>

                            @if (!empty($customFieldsData))
                                {{-- Dynamic: Show user's actual custom fields --}}
                                @foreach ($customFieldsData as $scheduleData)
                                    <h4 class="text-md font-medium text-gray-200 mb-2">{{ $scheduleData['role_name'] }}</h4>
                                    <div class="overflow-x-auto mb-6">
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
                                <div class="overflow-x-auto mb-6">
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
                                                <td colspan="3" class="text-gray-400 text-sm">...up to {custom_8}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            @endif

                            <div class="doc-callout doc-callout-tip mb-6">
                                <div class="doc-callout-title">Tip</div>
                                <p>Custom field variables correspond to the order your fields are defined in schedule settings. For example, if your first custom field is "Speaker Name", then <code class="doc-inline-code">{custom_1}</code> will show the speaker's name.</p>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Localization</div>
                                <p>Date and time variables like <code class="doc-inline-code">{day_name}</code>, <code class="doc-inline-code">{month_name}</code>, and <code class="doc-inline-code">{time}</code> are automatically translated to your schedule's language and respect its 24-hour time setting.</p>
                            </div>
                        </section>

                        <!-- AI Prompt -->
                        <section id="ai-prompt" class="doc-section">
                            <h2 class="doc-heading">AI Text Prompt</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-rose-500/20 text-rose-300 mr-2">Pro Feature</span>
                                Use AI to transform the generated text with custom instructions.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How It Works</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                After the text is generated from your template, you can apply AI transformations using the AI Text Prompt field. This allows you to:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Add emojis to make posts more engaging</li>
                                <li>Translate text to another language</li>
                                <li>Adjust formatting for specific platforms</li>
                                <li>Add hashtags or mentions</li>
                            </ul>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Example Prompts</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <code class="text-rose-300">Add a calendar emoji before each date and a pin emoji before each venue</code>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <code class="text-rose-300">Translate to Spanish</code>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <code class="text-rose-300">Add relevant hashtags for Instagram</code>
                                </div>
                            </div>
                        </section>

                        <!-- Screen Capture -->
                        <section id="screen-capture" class="doc-section">
                            <h2 class="doc-heading">Screen Capture Rendering</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/20 text-blue-300 mr-2">Enterprise Feature</span>
                                Use browser-based rendering for the graphic image instead of the default server-side generation.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">When to Use</h3>
                            <ul class="doc-list mb-6">
                                <li>Better support for right-to-left (RTL) languages like Hebrew and Arabic</li>
                                <li>More accurate rendering of special characters and fonts</li>
                                <li>Pixel-perfect representation of your schedule's styling</li>
                            </ul>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Screen capture rendering may take slightly longer than server-side generation due to the browser rendering process.</p>
                            </div>
                        </section>

                        <!-- Email Scheduling -->
                        <section id="email-scheduling" class="doc-section">
                            <h2 class="doc-heading">Email Scheduling</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-blue-500/20 text-blue-300 mr-2">Enterprise Feature</span>
                                Automatically send event graphics to your followers on a schedule. For designed email campaigns with more formatting options, see <a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">Newsletters</a>.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Configuration Options</h3>
                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Option</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Frequency</span></td>
                                            <td>Daily, Weekly, or Monthly</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Send On</span></td>
                                            <td>Day of week (for weekly) or day of month (for monthly)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Send At</span></td>
                                            <td>Hour of day to send the email</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Recipients</span></td>
                                            <td>Comma-separated list of email addresses</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Test Email</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Use the "Send Test Email" button to preview exactly what recipients will receive. The test email is sent to your account email address.
                            </p>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - More ways to share your events</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to generate graphics for</li>
                                <li><a href="{{ route('marketing.docs.schedule_styling') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Styling</a> - Customize your schedule's appearance</li>
                                <li><a href="{{ route('marketing.docs.analytics') }}" class="text-cyan-400 hover:text-cyan-300">Analytics</a> - Track views and engagement from shared graphics</li>
                                <li><a href="{{ route('marketing.docs.newsletters') }}" class="text-cyan-400 hover:text-cyan-300">Newsletters</a> - Send designed email campaigns to your audience</li>
                            </ul>
                        </section>

                        @include('marketing.docs.partials.navigation')
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')

    <!-- HowTo Schema for Rich Snippets -->
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
                "name": "Access Event Graphics",
                "text": "Open your schedule's admin panel and navigate to the Event Graphics section to start generating shareable content.",
                "url": "{{ url(route('marketing.docs.event_graphics')) }}#overview"
            },
            {
                "@type": "HowToStep",
                "name": "Customize the Text Template",
                "text": "Edit the text template using variables like {event_name}, {date_dmy}, {time}, and {venue} to control how event details are formatted.",
                "url": "{{ url(route('marketing.docs.event_graphics')) }}#text-template"
            },
            {
                "@type": "HowToStep",
                "name": "Generate Graphics",
                "text": "Generate a visual collage of your upcoming events along with formatted text ready for copying and sharing.",
                "url": "{{ url(route('marketing.docs.event_graphics')) }}#quick-reference"
            },
            {
                "@type": "HowToStep",
                "name": "Share on Social Media",
                "text": "Copy the generated image and text to share on Instagram, Facebook, Twitter, WhatsApp, Telegram, or include in newsletters.",
                "url": "{{ url(route('marketing.docs.event_graphics')) }}#overview"
            }
        ]
    }
    </script>
</x-marketing-layout>
