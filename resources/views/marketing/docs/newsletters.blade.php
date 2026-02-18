<x-marketing-layout>
    <x-slot name="title">Newsletters Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Newsletters</x-slot>
    <x-slot name="description">Learn how to create, design, and send newsletters to your followers and ticket buyers with Event Schedule's built-in newsletter builder.</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Newsletters Documentation - Event Schedule",
        "description": "Learn how to create, design, and send newsletters to your followers and ticket buyers with Event Schedule's built-in newsletter builder.",
        "author": {
            "@type": "Organization",
            "name": "Event Schedule"
        },
        "publisher": {
            "@type": "Organization",
            "name": "Event Schedule",
            "logo": {
                "@type": "ImageObject",
                "url": "{{ config('app.url') }}/images/light_logo.png",
                "width": 712,
                "height": 140
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
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Newsletters" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-rose-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Newsletters</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Create and send branded newsletters to your followers and ticket buyers. Announce upcoming events, share digests, and keep your audience engaged.
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
                        <a href="#newsletter-builder" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Newsletter Builder</a>
                        <a href="#block-types" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Block Types</a>
                        <a href="#templates" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Templates</a>
                        <a href="#style-customization" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Style Customization</a>
                        <a href="#recipients" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Recipients & Segments</a>
                        <a href="#sending" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Sending</a>
                        <a href="#ab-testing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">A/B Testing</a>
                        <a href="#analytics" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Analytics</a>
                        <a href="#managing" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Managing Newsletters</a>
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
                                Newsletters let you send professionally designed emails directly to your audience. Use them to:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Announce upcoming events and share your schedule</li>
                                <li>Send weekly or monthly event digests</li>
                                <li>Promote ticket sales and special offers</li>
                                <li>Share news and updates with your community</li>
                            </ul>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The newsletter builder provides a drag-and-drop interface with live preview, pre-built templates, and audience segmentation so you can reach the right people with the right message.
                            </p>
                        </section>

                        <!-- Newsletter Builder -->
                        <section id="newsletter-builder" class="doc-section">
                            <h2 class="doc-heading">Newsletter Builder</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The builder is organized into three tabs:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Content</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Add and arrange content blocks using drag-and-drop. Each block can be edited inline, reordered, or removed. A live preview updates in real time as you build.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Style</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Customize colors, fonts, and button styles. Choose a template as a starting point or design from scratch.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Settings</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Configure subject line, preview text, recipients, and scheduling. Set up A/B testing variants here.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Use the live preview to see exactly how your newsletter will appear in your recipients' inboxes before sending.</p>
                            </div>
                        </section>

                        <!-- Block Types -->
                        <section id="block-types" class="doc-section">
                            <h2 class="doc-heading">Block Types</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Build your newsletter by combining these content blocks:
                            </p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Block</th>
                                            <th>Description</th>
                                            <th>Options</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Heading</span></td>
                                            <td>Large text for section titles and headlines</td>
                                            <td>Level (H1/H2/H3), alignment (left/center/right)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Text</span></td>
                                            <td>Rich text content with Markdown editor</td>
                                            <td>Markdown formatting via EasyMDE editor</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Events</span></td>
                                            <td>Automatically displays your upcoming events with flyer images, dates, and links</td>
                                            <td>All or selected events; cards or list layout</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Button</span></td>
                                            <td>Call-to-action button with customizable text and link</td>
                                            <td>Custom text, URL, alignment</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Image</span></td>
                                            <td>Upload or link to an image with optional alt text</td>
                                            <td>URL, alt text, width, alignment</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Divider</span></td>
                                            <td>Horizontal line to separate sections</td>
                                            <td>Style (solid/dashed/dotted)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Spacer</span></td>
                                            <td>Vertical spacing between blocks</td>
                                            <td>Height (5 to 200px)</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Social Links</span></td>
                                            <td>Icons linking to your social media profiles</td>
                                            <td>Website, Facebook, Instagram, Twitter/X, YouTube, TikTok, LinkedIn</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Profile Image</span></td>
                                            <td>Your schedule's profile image, displayed as a centered logo</td>
                                            <td>Auto-populated from schedule settings</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Header Banner</span></td>
                                            <td>Full-width banner image at the top of your newsletter</td>
                                            <td>Auto-populated from schedule settings</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>The <strong>Events</strong> block automatically pulls in your upcoming events, so your newsletter always shows the latest schedule without manual updates.</p>
                            </div>
                        </section>

                        <!-- Templates -->
                        <section id="templates" class="doc-section">
                            <h2 class="doc-heading">Templates</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Start with a pre-designed template and customize it to match your brand. Five templates are available:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Modern</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Clean layout with bold accent colors and rounded elements. Great for a contemporary look.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Classic</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Traditional newsletter layout with a structured header, body, and footer. Professional and familiar.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Minimal</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Stripped-down design that puts content first. Uses plenty of whitespace and subtle typography.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Bold</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Eye-catching design with large headings and vibrant colors. Ideal for promotions and announcements.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Compact</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Dense layout that fits more content into less space. Perfect for event-heavy digests.</p>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                After selecting a template, you can fully customize every aspect including colors, fonts, and content blocks.
                            </p>
                        </section>

                        <!-- Style Customization -->
                        <section id="style-customization" class="doc-section">
                            <h2 class="doc-heading">Style Customization</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Fine-tune the visual appearance of your newsletter using the Style tab:
                            </p>

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
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Background Color</span></td>
                                            <td>The outer background color of the email</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Accent Color</span></td>
                                            <td>Used for buttons, links, and highlighted elements</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Text Color</span></td>
                                            <td>Default color for body text</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Font Family</span></td>
                                            <td>Choose from a selection of email-safe fonts</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Button Style</span></td>
                                            <td>Rounded or square buttons with customizable colors</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Event Layout</span></td>
                                            <td>Cards or list - how events are displayed in the Events block</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>Email clients have varying CSS support. The builder uses email-safe styles to ensure your newsletter looks consistent across Gmail, Outlook, Apple Mail, and other clients.</p>
                            </div>
                        </section>

                        <!-- Recipients & Segments -->
                        <section id="recipients" class="doc-section">
                            <h2 class="doc-heading">Recipients & Segments</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Choose who receives your newsletter by selecting a segment:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">All Followers</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Send to everyone who follows your schedule. Best for general announcements and event digests. Learn how to <a href="{{ route('marketing.docs.sharing') }}#followers" class="text-cyan-400 hover:text-cyan-300">build your follower base</a>.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Ticket Buyers</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Target people who have purchased <a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">tickets</a> to your events. Ideal for promoting similar upcoming events.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Manual</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Enter specific email addresses. Useful for targeted sends to a curated list of recipients.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sub-schedule</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Send to ticket buyers from a specific sub-schedule. Perfect for category-specific promotions.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Sending -->
                        <section id="sending" class="doc-section">
                            <h2 class="doc-heading">Sending</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                You have three options for delivering your newsletter:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Send Now</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Immediately delivers the newsletter to all selected recipients.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Schedule for Later</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Pick a date and time to send automatically. The newsletter is queued and sent at the scheduled time.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Send Test</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Sends a preview to your own email address so you can verify formatting and content before the real send.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Always send a test email first to check that images load correctly and the layout looks good across different email clients.</p>
                            </div>
                        </section>

                        <!-- A/B Testing -->
                        <section id="ab-testing" class="doc-section">
                            <h2 class="doc-heading">A/B Testing</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Test different versions of your newsletter to find what resonates best with your audience.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">How It Works</h3>
                            <ul class="doc-list mb-6">
                                <li>Create two or more variants with different <strong>subject lines</strong> or <strong>newsletter content</strong></li>
                                <li>Choose a sample size from <strong>5% to 50%</strong> of your audience to receive the initial test</li>
                                <li>Set an evaluation period from <strong>1 to 72 hours</strong> (default: 4 hours) for results to accumulate</li>
                                <li>Pick a winner criterion: <strong>open rate</strong> or <strong>click rate</strong></li>
                                <li>The winning variant is automatically sent to the remaining recipients after the evaluation period</li>
                            </ul>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Note</div>
                                <p>A/B testing works best with larger audiences. With smaller lists, the results may not be statistically significant.</p>
                            </div>
                        </section>

                        <!-- Analytics -->
                        <section id="analytics" class="doc-section">
                            <h2 class="doc-heading">Analytics</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                After sending a newsletter, track its performance with detailed analytics:
                            </p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Metric</th>
                                            <th>Description</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Open Rate</span></td>
                                            <td>Percentage of recipients who opened the email</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Click Tracking</span></td>
                                            <td>Number and percentage of recipients who clicked a link</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Top Links</span></td>
                                            <td>Which links received the most clicks</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-gray-900 dark:text-white">Timeline</span></td>
                                            <td>When opens and clicks occurred over time</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Use these insights to refine your content, sending times, and subject lines for future newsletters.
                            </p>
                        </section>

                        <!-- Managing Newsletters -->
                        <section id="managing" class="doc-section">
                            <h2 class="doc-heading">Managing Newsletters</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Your newsletters are organized by status in the admin panel:
                            </p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Draft</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Newsletters in progress that haven't been sent or scheduled yet. You can continue editing at any time.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Scheduled</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Newsletters queued for future delivery. You can cancel or reschedule before the send time.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sending</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Currently in progress. Emails are being delivered to recipients.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sent</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Delivered newsletters with analytics available. You can view performance or clone to create a new newsletter based on it.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Cancelled</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Newsletters that were scheduled but cancelled before delivery.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Cloning</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Clone any newsletter to create a copy with the same content, style, and settings. This is useful for recurring newsletters where you want to keep the same design but update the content.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Unsubscribe Management</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Every newsletter includes an unsubscribe link. When a recipient unsubscribes, they are automatically removed from future sends. You can view and manage unsubscribed contacts in your admin panel.
                            </p>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - More ways to reach your audience</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Event Graphics</a> - Generate shareable images for social media</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events that appear in your newsletters</li>
                                <li><a href="{{ route('marketing.docs.analytics') }}" class="text-cyan-400 hover:text-cyan-300">Analytics</a> - Track how newsletters drive schedule views</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Selling Tickets</a> - Set up ticketing for your events</li>
                            </ul>
                        </section>

                        @include('marketing.docs.partials.navigation')
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')

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
                "name": "Add Content Blocks",
                "text": "Use the Content tab to add and arrange blocks such as headings, text, events, buttons, and images using drag-and-drop.",
                "url": "{{ url(route('marketing.docs.newsletters')) }}#block-types"
            },
            {
                "@type": "HowToStep",
                "name": "Choose a Template",
                "text": "Select a pre-designed template (Modern, Classic, Minimal, Bold, or Compact) and customize colors, fonts, and button styles.",
                "url": "{{ url(route('marketing.docs.newsletters')) }}#templates"
            },
            {
                "@type": "HowToStep",
                "name": "Select Recipients",
                "text": "Choose a segment for your newsletter: all followers, ticket buyers, a specific sub-schedule, or a manual list of email addresses.",
                "url": "{{ url(route('marketing.docs.newsletters')) }}#recipients"
            },
            {
                "@type": "HowToStep",
                "name": "Send or Schedule",
                "text": "Send the newsletter immediately, schedule it for a future date and time, or send a test email to preview it first.",
                "url": "{{ url(route('marketing.docs.newsletters')) }}#sending"
            }
        ]
    }
    </script>
</x-marketing-layout>
