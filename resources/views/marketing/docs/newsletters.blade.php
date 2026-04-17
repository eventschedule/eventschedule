<x-marketing-layout>
    <x-slot name="title">Newsletters Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Newsletters</x-slot>
    <x-slot name="description">Learn how to create, design, and send newsletters to your followers and ticket buyers with Event Schedule's built-in newsletter builder.</x-slot>
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
        },
        "datePublished": "2024-01-01",
        "dateModified": "2026-03-08"
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
                        <a href="#managing-segments" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">Managing Segments</a>
                        <a href="#importing-emails" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors pl-6">Importing Emails</a>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Overview
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Newsletters let you send professionally designed emails directly to your audience. Use them to:
                            </p>
                            <ul class="doc-list mb-6">
                                <li>Announce upcoming events and share your schedule</li>
                                <li>Send weekly or monthly event digests</li>
                                <li>Promote ticket sales and special offers</li>
                                <li>Share news and updates with your community</li>
                            </ul>

                            <x-doc-screenshot id="newsletters--list" alt="Newsletter list" loading="eager" />

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                The newsletter builder provides a drag-and-drop interface with live preview, pre-built templates, and audience segmentation so you can reach the right people with the right message.
                            </p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Sending Limits by Plan</div>
                                <p>Newsletter sending limits vary by plan tier:</p>
                                <ul class="doc-list mt-2">
                                    <li><strong class="text-gray-900 dark:text-white">Free:</strong> 10 newsletter emails per month</li>
                                    <li><strong class="text-gray-900 dark:text-white">Pro:</strong> 100 newsletter emails per month</li>
                                    <li><strong class="text-gray-900 dark:text-white">Enterprise:</strong> 1,000 newsletter emails per month</li>
                                    <li><strong class="text-gray-900 dark:text-white">Selfhosted</strong> (with own email settings): Unlimited</li>
                                </ul>
                                <p class="mt-2">A usage meter at the top of the Newsletters page shows how many emails you have sent this month relative to your plan limit (e.g. "5 of 100 sent").</p>
                            </div>
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
                                The builder is organized into three tabs:
                            </p>

                            <x-doc-screenshot id="newsletters--create" alt="Newsletter builder" />

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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z" />
                                </svg>
                                Block Types
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Build your newsletter by combining these content blocks:
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Content Blocks</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Heading</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Large text for section titles and headlines.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Text, Level (H1/H2/H3), Alignment (left/center/right)</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Text</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Rich text content with full formatting support.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Content (Markdown editor with formatting)</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Events</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Automatically displays your upcoming events with flyer images, dates, and links.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Event selection (all or specific), Layout (cards or list)</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Button</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Call-to-action button with customizable text and link.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Text, URL, Alignment (left/center/right)</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Image</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Upload or link to one or more images. Add a caption, make the image clickable with a link URL, and pick a layout when using multiple images.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> URL, Alt text, Caption, Link URL, Width, Alignment (left/center/right), Layout (column, row, or grid) when adding multiple images</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Video</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Embed a YouTube video thumbnail with a play button that links to the video.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> YouTube URL (thumbnail auto-extracted)</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Offer</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Promotional offer card with pricing, coupon code, and call-to-action button.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Title, Description, Original price, Sale price, Coupon code, Button text, Button URL, Alignment</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Quote</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Styled blockquote with attribution for testimonials or highlights.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Quote text, Author, Author title</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Sponsors</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Displays sponsor logos with names and tier badges. Choose to show sponsors from your schedule settings or from the first event.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Source (from schedule or from first event)</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Poll</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Displays the first active poll from your upcoming events with a "Vote Now" button linking to the event page. Auto-populated from your events.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Layout & Utility Blocks</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Divider</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Horizontal line to visually separate sections.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Style (solid/dashed/dotted)</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Spacer</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Adjustable vertical spacing between blocks.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Height (5 to 200px)</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Social Links</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400 mb-2">Icons linking to your social media profiles.</p>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Fields:</strong> Platform links (Website, Facebook, Instagram, Twitter/X, YouTube, TikTok, LinkedIn)</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Auto-populated Blocks</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Profile Image</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Your schedule's profile image, displayed as a centered logo. Auto-populated from schedule settings.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Header Banner</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Full-width banner image at the top of your newsletter. Auto-populated from schedule settings.</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>The <strong>Events</strong> block automatically pulls in your upcoming events, so your newsletter always shows the latest schedule without manual updates.</p>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42" />
                                </svg>
                                Style Customization
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M18 18.72a9.094 9.094 0 003.741-.479 3 3 0 00-4.682-2.72m.94 3.198l.001.031c0 .225-.012.447-.037.666A11.944 11.944 0 0112 21c-2.17 0-4.207-.576-5.963-1.584A6.062 6.062 0 016 18.719m12 0a5.971 5.971 0 00-.941-3.197m0 0A5.995 5.995 0 0012 12.75a5.995 5.995 0 00-5.058 2.772m0 0a3 3 0 00-4.681 2.72 8.986 8.986 0 003.74.477m.94-3.197a5.971 5.971 0 00-.94 3.197M15 6.75a3 3 0 11-6 0 3 3 0 016 0zm6 3a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0zm-13.5 0a2.25 2.25 0 11-4.5 0 2.25 2.25 0 014.5 0z" />
                                </svg>
                                Recipients & Segments
                            </h2>
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

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Schedule members are always included</div>
                                <p>Team members on the schedule (owner, admins, and viewers) who have email verified and are subscribed always receive every newsletter, regardless of the segment selected. This keeps the team in the loop on what's being sent.</p>
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
                                Create reusable audience segments that can be selected when sending newsletters. Access the segment manager by clicking the <strong class="text-gray-900 dark:text-white">Segments</strong> button on the newsletter list page.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Creating a Segment</h3>
                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Click <strong class="text-gray-900 dark:text-white">Segments</strong> on the newsletter list page</li>
                                <li>Enter a name for the segment</li>
                                <li>Choose a segment type:
                                    <ul class="doc-list mt-2">
                                        <li><strong class="text-gray-900 dark:text-white">All Followers</strong> - everyone who follows your schedule</li>
                                        <li><strong class="text-gray-900 dark:text-white">Ticket Buyers</strong> - people who have purchased tickets</li>
                                        <li><strong class="text-gray-900 dark:text-white">Manual</strong> - a custom list of email addresses you provide</li>
                                    </ul>
                                </li>
                                <li>For manual segments, enter email addresses (one per line)</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Create Segment</strong></li>
                            </ol>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">
                                Existing segments are listed with their type and recipient count. You can edit or delete any segment from this page. When creating a newsletter, your saved segments appear as selectable options in the Settings tab.
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
                                Bulk import contacts into a newsletter segment. Access the import tool by clicking the <strong class="text-gray-900 dark:text-white">Import Emails</strong> button on the newsletter list page.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Choosing a Segment</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">
                                Before importing, choose where to add the contacts: create a new segment with a name you provide, or add to an existing manual segment.
                            </p>

                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-4">Import Methods</h3>
                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Form Entry</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Add contacts one at a time using name and email fields. Best for small lists or individual additions.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Paste Emails</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Paste a list of email addresses in various formats (one per line, comma-separated, or with names). The importer automatically parses the entries.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Upload CSV</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Upload a CSV file and map columns to email and name fields. The importer auto-detects columns and shows a preview before importing.</p>
                                </div>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.75 3.104v5.714a2.25 2.25 0 01-.659 1.591L5 14.5M9.75 3.104c-.251.023-.501.05-.75.082m.75-.082a24.301 24.301 0 014.5 0m0 0v5.714c0 .597.237 1.17.659 1.591L19.8 15.3M14.25 3.104c.251.023.501.05.75.082M19.8 15.3l-1.57.393A9.065 9.065 0 0112 15a9.065 9.065 0 00-6.23.693L5 14.5m14.8.8l1.402 1.402c1.232 1.232.65 3.318-1.067 3.611A48.309 48.309 0 0112 21c-2.773 0-5.491-.235-8.135-.687-1.718-.293-2.3-2.379-1.067-3.61L5 14.5" />
                                </svg>
                                A/B Testing
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                                </svg>
                                Analytics
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                Managing Newsletters
                            </h2>
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
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-10.5 6L21 3m0 0h-5.25M21 3v5.25" />
                                </svg>
                                See Also
                            </h2>
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
