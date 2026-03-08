<x-marketing-layout>
    <x-slot name="title">AI-Powered Features | Generate, Create & Import - Event Schedule</x-slot>
    <x-slot name="description">AI-powered event management. Generate flyers, write descriptions, create brand style, parse text and images, scan agendas, translate to 11 languages, create events via WhatsApp, and automate with a full API for AI agents.</x-slot>
    <x-slot name="breadcrumbTitle">AI Features</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "Service",
        "name": "Event Schedule AI Features",
        "description": "AI-powered event management. Generate flyers, write descriptions, create brand style, parse text and images, scan agendas, translate to 11 languages, create events via WhatsApp, and automate with a full API for AI agents.",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule",
            "url": "{{ config('app.url') }}"
        },
        "serviceType": "AI-Powered Event Management"
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "What formats does the AI parser support?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The AI can parse plain text, images (JPEG, PNG, GIF, WebP), screenshots, flyers, agendas, and setlists. It handles both single events and multi-event documents, extracting each event individually."
                }
            },
            {
                "@type": "Question",
                "name": "Which AI features are free vs. Enterprise?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "AI event parsing and instant translation are available on all plans including the free tier. Style generation, content generation, flyer generation, graphic text processing, and WhatsApp event creation are Enterprise features."
                }
            },
            {
                "@type": "Question",
                "name": "How accurate is the AI?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The AI is highly accurate for standard event formats. You always get to review and edit the results before saving. You can also add custom prompts to guide the AI for your specific format."
                }
            },
            {
                "@type": "Question",
                "name": "What can AI Style Generation create?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "AI Style Generation creates a complete visual identity for your schedule including profile image, header image, background image, accent color, and font. You can add custom style instructions and regenerate individual elements."
                }
            },
            {
                "@type": "Question",
                "name": "How does WhatsApp event creation work?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Send a text message or photo of a flyer to your schedule's WhatsApp number. AI parses the details using Gemini, auto-generates a flyer, and adds the event to your schedule automatically."
                }
            },
            {
                "@type": "Question",
                "name": "What languages are supported?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "The AI can parse events in any language and extract details correctly. The translation feature supports 11 languages including English, Spanish, French, German, Italian, Portuguese, Dutch, Hebrew, Arabic, Estonian, and Russian."
                }
            },
            {
                "@type": "Question",
                "name": "Can AI agents use Event Schedule programmatically?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Event Schedule provides a full REST API with OpenAPI 3.0 specification, llms.txt for AI discovery, and agents.json for defining agent workflows. AI agents can create, update, and manage events programmatically with smart creation features and webhook notifications."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        /* Page-specific typing animation */
        @keyframes typing {
            0%, 100% { opacity: 1; }
            50% { opacity: 0; }
        }
        .animate-typing { animation: typing 1s ease-in-out infinite; }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">AI-Powered</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                AI-powered<br>
                <span class="text-gradient">features</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                From smart import to content generation and visual branding. Paste text, drop an image, or let AI write your descriptions and create your style.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                    Try it free
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>

            <p class="mt-6 text-gray-500 dark:text-gray-400 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ route('marketing.docs.ai_import') }}" class="underline hover:text-blue-600 dark:hover:text-blue-400 transition-colors">Read the AI Import guide</a>
            </p>

        </div>
    </section>


    <!-- Bento Grid Section B: Generate Content and Style -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Generate content and style with AI</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">AI creates your visual identity and writes your content.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- AI Style Generation (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                Style Generation
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Generate your brand style</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-6">AI creates a complete visual identity for your schedule - profile image, header image, background image, accent color, and font. Add style instructions to guide the look.</p>
                            <div class="flex flex-wrap gap-3 mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Profile image</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Header</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Background</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Accent color</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Font</span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">Enterprise</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-5 max-w-xs w-full">
                                <div class="text-xs text-gray-500 dark:text-gray-400 mb-3">Generated elements</div>
                                <div class="space-y-2.5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-sky-500/20 flex items-center justify-center flex-shrink-0">
                                            <svg aria-hidden="true" class="w-3.5 h-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="text-gray-900 dark:text-white text-sm">Profile image</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-sky-500/20 flex items-center justify-center flex-shrink-0">
                                            <svg aria-hidden="true" class="w-3.5 h-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="text-gray-900 dark:text-white text-sm">Header image</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-sky-500/20 flex items-center justify-center flex-shrink-0">
                                            <svg aria-hidden="true" class="w-3.5 h-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="text-gray-900 dark:text-white text-sm">Background image</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-sky-500/20 flex items-center justify-center flex-shrink-0">
                                            <div class="w-3.5 h-3.5 rounded-full bg-sky-400"></div>
                                        </div>
                                        <span class="text-gray-900 dark:text-white text-sm">Accent color</span>
                                    </div>
                                    <div class="flex items-center gap-3">
                                        <div class="w-7 h-7 rounded-full bg-sky-500/20 flex items-center justify-center flex-shrink-0">
                                            <svg aria-hidden="true" class="w-3.5 h-3.5 text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </div>
                                        <span class="text-gray-900 dark:text-white text-sm">Font</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- AI Schedule Details -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                        </svg>
                        Content
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Write schedule descriptions</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Generate polished short and full descriptions for your schedule. Save custom prompts to maintain your tone.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 mb-2">
                            <svg aria-hidden="true" class="w-4 h-4 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                            </svg>
                            <span class="text-xs text-gray-500 dark:text-gray-400">AI-generated</span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            A vibrant live music hub featuring jazz, blues, and soul performances every week...
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">Enterprise</span>
                    </div>
                </div>

                <!-- AI Event Details -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                        </svg>
                        Content
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Generate event details</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">AI writes event category, short description, and full description based on the event name and context.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="space-y-2 text-sm">
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/60">Category:</span><span class="text-gray-900 dark:text-white">Music</span></div>
                            <div class="flex justify-between"><span class="text-gray-500 dark:text-white/60">Short:</span><span class="text-gray-900 dark:text-white">Live jazz trio</span></div>
                            <div class="text-gray-600 dark:text-gray-300 text-xs mt-1 leading-relaxed">An evening of smooth jazz featuring...</div>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">Enterprise</span>
                    </div>
                </div>

                <!-- AI Text on Graphics -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                        Graphics
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">AI text for graphic emails</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Process your event list text through AI before sending graphic emails. Add flair, reformat, or customize.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-2 mb-2">
                            <svg aria-hidden="true" class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09z" />
                            </svg>
                            <span class="text-xs text-gray-500 dark:text-gray-400">AI-processed</span>
                        </div>
                        <div class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                            This week's highlights...<br>
                            <span class="text-amber-600 dark:text-amber-300">Jazz Night - Fri 8pm</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">Enterprise</span>
                    </div>
                </div>

                <!-- Generate Event Flyers -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-teal-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                        </svg>
                        AI Flyer Generation
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Generate event flyers</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Enter event details and let AI create a professional, eye-catching flyer. Customize the style and regenerate until it's perfect.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl border border-gray-200 dark:border-white/10 overflow-hidden">
                        <div class="bg-gradient-to-br from-teal-600 to-cyan-700 p-4 text-center text-white">
                            <div class="text-sm font-bold mb-0.5">Summer Jazz Festival</div>
                            <div class="text-[10px] opacity-80 mb-1">Saturday, July 12, 2025</div>
                            <div class="w-8 h-0.5 bg-white/40 mx-auto mb-1"></div>
                            <div class="text-[10px] opacity-70">Central Park Amphitheater</div>
                        </div>
                        <div class="p-3 flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-[10px]">One-click</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-[10px]">Custom style</span>
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-[10px]">Regenerate</span>
                        </div>
                    </div>
                    <div class="mt-4">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">Enterprise</span>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Built for AI Agents -->
    <section class="relative bg-gray-50 dark:bg-[#0f0f14] py-24 border-t border-gray-200 dark:border-white/5 overflow-hidden">
        <!-- Parallax background blobs -->
        <div class="absolute inset-0">
            <div class="absolute top-20 right-1/4 w-[400px] h-[400px] bg-blue-600/10 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 left-1/4 w-[300px] h-[300px] bg-sky-600/10 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                <!-- Text side -->
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-6">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                        API for AI Agents
                    </div>
                    <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                        Built for <span class="text-gradient">AI agents</span>
                    </h2>
                    <p class="text-lg text-gray-500 dark:text-gray-400 mb-8">
                        A full REST API with OpenAPI 3.0 specification, llms.txt for AI discovery, and agents.json for defining agent workflows. Let AI agents create, update, and manage events programmatically.
                    </p>
                    <div class="flex flex-wrap gap-3 mb-8">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-sm font-medium">REST API</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-sm font-medium">OpenAPI 3.0</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-sm font-medium">llms.txt</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-sm font-medium">agents.json</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-sm font-medium">Webhooks</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full bg-white dark:bg-white/10 border border-gray-200 dark:border-white/10 text-gray-700 dark:text-gray-300 text-sm font-medium">Smart creation</span>
                    </div>
                    <a href="{{ marketing_url('/for-ai-agents') }}" class="inline-flex items-center text-blue-500 dark:text-blue-400 font-medium hover:gap-3 gap-2 transition-all text-lg">
                        Learn more
                        <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </a>
                </div>

                <!-- Visual side -->
                <div class="flex flex-col items-center gap-6">
                    <!-- API request/response demo -->
                    <div class="w-full max-w-md">
                        <div class="bg-white dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-5 mb-4">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">POST</span>
                                <span class="text-sm text-gray-500 dark:text-gray-400 font-mono">/api/events</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-black/30 rounded-lg p-3 font-mono text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                <span class="text-gray-400">{</span><br>
                                &nbsp;&nbsp;<span class="text-blue-600 dark:text-blue-400">"name"</span>: <span class="text-emerald-600 dark:text-emerald-400">"Jazz Night"</span>,<br>
                                &nbsp;&nbsp;<span class="text-blue-600 dark:text-blue-400">"date"</span>: <span class="text-emerald-600 dark:text-emerald-400">"2025-03-15"</span>,<br>
                                &nbsp;&nbsp;<span class="text-blue-600 dark:text-blue-400">"venue"</span>: <span class="text-emerald-600 dark:text-emerald-400">"Blue Note"</span><br>
                                <span class="text-gray-400">}</span>
                            </div>
                        </div>
                        <!-- Arrow -->
                        <div class="flex justify-center my-2">
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                            </svg>
                        </div>
                        <!-- Response -->
                        <div class="bg-gradient-to-br from-blue-500/10 to-sky-500/10 rounded-2xl border border-blue-400/30 p-5">
                            <div class="flex items-center gap-2 mb-3">
                                <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-mono font-bold bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300">201</span>
                                <span class="text-sm text-emerald-600 dark:text-emerald-400 font-medium">Created</span>
                            </div>
                            <div class="bg-white/50 dark:bg-black/20 rounded-lg p-3 font-mono text-xs text-gray-600 dark:text-gray-300 leading-relaxed">
                                <span class="text-gray-400">{</span><br>
                                &nbsp;&nbsp;<span class="text-blue-600 dark:text-blue-400">"id"</span>: <span class="text-amber-600 dark:text-amber-400">4829</span>,<br>
                                &nbsp;&nbsp;<span class="text-blue-600 dark:text-blue-400">"name"</span>: <span class="text-emerald-600 dark:text-emerald-400">"Jazz Night"</span>,<br>
                                &nbsp;&nbsp;<span class="text-blue-600 dark:text-blue-400">"status"</span>: <span class="text-emerald-600 dark:text-emerald-400">"published"</span><br>
                                <span class="text-gray-400">}</span>
                            </div>
                        </div>
                    </div>
                    <!-- File badges -->
                    <div class="flex flex-wrap justify-center gap-3">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-600 dark:text-gray-300 font-mono">
                            <svg aria-hidden="true" class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            llms.txt
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-600 dark:text-gray-300 font-mono">
                            <svg aria-hidden="true" class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            agents.json
                        </span>
                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-lg bg-white dark:bg-white/5 border border-gray-200 dark:border-white/10 text-sm text-gray-600 dark:text-gray-300 font-mono">
                            <svg aria-hidden="true" class="w-3.5 h-3.5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            openapi.json
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Bento Grid Section A: Import and Parse -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">Import and parse events with AI</h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">Paste text, drop an image, or send a WhatsApp message.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Smart Event Parsing (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                                </svg>
                                Smart Parsing
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Parse any format</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-6">Flyers, emails, agendas, and setlists. Paste the text or drop an image and AI extracts event name, date, time, venue, and description. Multi-event images are parsed into individual events automatically.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Text parsing</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Image recognition</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Any language</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Agendas & setlists</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <!-- Input side -->
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-4 max-w-xs">
                                    <div class="text-xs text-gray-400 dark:text-gray-400 mb-2">Paste or drop</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-mono leading-relaxed">
                                        Jazz Night at Blue Note<br>
                                        Friday, March 15 at 8pm<br>
                                        Featuring the Sarah Johnson Trio<br>
                                        Tickets: $25 at the door
                                    </div>
                                </div>
                                <!-- Arrow -->
                                <div class="flex justify-center my-2">
                                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <!-- Output side -->
                                <div class="bg-gradient-to-br from-blue-500/20 to-sky-500/20 rounded-2xl border border-blue-400/30 p-4 max-w-xs">
                                    <div class="text-xs text-blue-700 dark:text-blue-300 mb-2">Extracted</div>
                                    <div class="space-y-2 text-sm">
                                        <div class="flex justify-between"><span class="text-blue-700 dark:text-white/60">Name:</span><span class="text-blue-900 dark:text-white">Jazz Night</span></div>
                                        <div class="flex justify-between"><span class="text-blue-700 dark:text-white/60">Date:</span><span class="text-blue-900 dark:text-white">Mar 15, 8:00 PM</span></div>
                                        <div class="flex justify-between"><span class="text-blue-700 dark:text-white/60">Venue:</span><span class="text-blue-900 dark:text-white">Blue Note</span></div>
                                        <div class="flex justify-between"><span class="text-blue-700 dark:text-white/60">Talent:</span><span class="text-blue-900 dark:text-white">Sarah Johnson Trio</span></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Instant Translation -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-sky-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129" />
                        </svg>
                        Translation
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Instant translation</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Translate your entire schedule automatically. Support for 11 languages including English, Spanish, French, German, and more.</p>

                    <div class="flex flex-wrap gap-2 justify-center">
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">EN</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">ES</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">FR</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">DE</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">IT</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">PT</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">NL</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">HE</span>
                        <span class="inline-flex items-center px-3 py-1.5 rounded-lg bg-gray-200 dark:bg-white/10 text-gray-900 dark:text-white text-sm font-medium">AR</span>
                    </div>
                </div>

                <!-- Venue & Talent Matching -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 border border-emerald-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" />
                        </svg>
                        Smart Linking
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Auto-link venues & talent</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">AI matches parsed events to existing venues and performers in your schedule using fuzzy matching.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="flex items-center gap-3 mb-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg aria-hidden="true" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Blue Note Jazz Club</div>
                                <div class="text-emerald-600 dark:text-emerald-400 text-xs">Matched to existing venue</div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                <svg aria-hidden="true" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                </svg>
                            </div>
                            <div>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Sarah Johnson Trio</div>
                                <div class="text-emerald-600 dark:text-emerald-400 text-xs">Matched to existing talent</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Multiple Formats (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-sky-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-sky-100 text-sky-700 dark:bg-sky-500/20 dark:text-sky-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Multiple Formats
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Any input works</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg">Text, images, screenshots, and flyers. Drop it in and AI figures out the rest.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg aria-hidden="true" class="w-8 h-8 mx-auto mb-2 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                                </svg>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Text</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg aria-hidden="true" class="w-8 h-8 mx-auto mb-2 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Images</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg aria-hidden="true" class="w-8 h-8 mx-auto mb-2 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Flyers</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg aria-hidden="true" class="w-8 h-8 mx-auto mb-2 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 10h16M4 14h16M4 18h16" />
                                </svg>
                                <div class="text-gray-900 dark:text-white text-sm font-medium">Agendas</div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Scan a Printed Agenda (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-amber-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                                </svg>
                                Agenda Scanning
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Scan a printed agenda</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-6">Point your camera at a printed agenda or upload an image. AI reads each line item and populates your event's parts automatically - sessions, acts, segments, and more.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Camera capture</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Image upload</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Auto-populate parts</span>
                            </div>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="relative">
                                <!-- Printed agenda -->
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 max-w-xs mb-4">
                                    <div class="text-xs text-gray-400 dark:text-gray-400 mb-2">Printed agenda</div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300 font-mono leading-relaxed">
                                        9:00 AM - Opening Keynote<br>
                                        10:30 AM - Panel Discussion<br>
                                        12:00 PM - Lunch Break<br>
                                        1:30 PM - Workshop A
                                    </div>
                                </div>
                                <!-- Arrow -->
                                <div class="flex justify-center my-2">
                                    <svg aria-hidden="true" class="w-6 h-6 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3" />
                                    </svg>
                                </div>
                                <!-- Extracted parts -->
                                <div class="bg-gradient-to-br from-amber-500/20 to-orange-500/20 rounded-2xl border border-amber-400/30 p-4 max-w-xs">
                                    <div class="text-xs text-amber-700 dark:text-amber-300 mb-2">Event parts</div>
                                    <div class="space-y-1.5">
                                        <div class="flex items-center gap-2 p-1.5 rounded-lg bg-amber-400/20">
                                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-900 dark:text-white text-xs">Opening Keynote</span>
                                            <span class="ml-auto text-amber-700 dark:text-amber-300 text-[10px]">9:00 AM</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-1.5 rounded-lg bg-amber-400/10">
                                            <div class="w-1.5 h-1.5 rounded-full bg-orange-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Panel Discussion</span>
                                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-[10px]">10:30 AM</span>
                                        </div>
                                        <div class="flex items-center gap-2 p-1.5 rounded-lg bg-amber-400/10">
                                            <div class="w-1.5 h-1.5 rounded-full bg-amber-400"></div>
                                            <span class="text-gray-600 dark:text-gray-300 text-xs">Workshop A</span>
                                            <span class="ml-auto text-gray-500 dark:text-gray-400 text-[10px]">1:30 PM</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Custom AI Prompts -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-blue-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-100 text-blue-700 dark:bg-blue-500/20 dark:text-blue-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                        Custom Prompts
                    </div>
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Guide the AI</h3>
                    <p class="text-gray-500 dark:text-white/80 mb-6">Add custom instructions to help AI understand your agenda format. Set prompts per event or as a default for your entire schedule.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="text-xs text-gray-500 dark:text-gray-400 mb-2">Custom prompt</div>
                        <div class="bg-blue-500/10 rounded-lg p-3 border border-blue-400/20">
                            <div class="text-blue-900 dark:text-blue-200 text-xs font-mono leading-relaxed">
                                "Each line is a session.<br>
                                Format: time - speaker - topic.<br>
                                Ignore lunch breaks."
                            </div>
                        </div>
                        <div class="flex items-center gap-2 mt-3 text-[10px] text-gray-500 dark:text-gray-400">
                            <svg aria-hidden="true" class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Per-event or schedule-wide
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Event Creation (spans 3 cols) -->
                <div class="bento-card lg:col-span-3 relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-emerald-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                WhatsApp
                            </div>
                            <h3 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Create events via WhatsApp</h3>
                            <p class="text-gray-500 dark:text-white/80 text-lg mb-6">Send a text message or photo of a flyer to your schedule's WhatsApp number. AI parses the details, generates a flyer, and adds the event automatically.</p>
                            <div class="flex flex-wrap gap-3 mb-4">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Text or photo</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Auto-flyer</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Auto-curate</span>
                            </div>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-700 dark:bg-amber-500/20 dark:text-amber-300 border border-amber-200 dark:border-amber-500/30">Enterprise</span>
                        </div>
                        <div class="flex items-center justify-center">
                            <div class="relative max-w-xs w-full">
                                <!-- Incoming message -->
                                <div class="bg-gray-200 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-4 mb-3">
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="w-6 h-6 rounded-full bg-emerald-500/20 flex items-center justify-center">
                                            <svg aria-hidden="true" class="w-3 h-3 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                                            </svg>
                                        </div>
                                        <span class="text-xs text-gray-500 dark:text-gray-400">Incoming</span>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300 leading-relaxed">
                                        Jazz Night at Blue Note<br>
                                        Friday March 15 at 8pm
                                    </div>
                                </div>
                                <!-- Reply -->
                                <div class="bg-gradient-to-br from-emerald-500/20 to-green-500/20 rounded-2xl border border-emerald-400/30 p-4">
                                    <div class="flex items-center gap-2 mb-2">
                                        <svg aria-hidden="true" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                                        </svg>
                                        <span class="text-emerald-700 dark:text-emerald-300 text-xs font-medium">Event created</span>
                                    </div>
                                    <div class="text-sm text-gray-600 dark:text-gray-300">
                                        Jazz Night added to your schedule with auto-generated flyer.
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- How it Works -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    How it works
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Four steps from input to output.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        1
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Pick a feature</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Import events, generate content, create your style, or process graphic text.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        2
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Provide input</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Paste text, drop an image, or add custom instructions.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        3
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">AI does the work</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        AI generates the result in seconds.
                    </p>
                </div>

                <div class="text-center">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-sky-500 text-white text-2xl font-bold rounded-2xl flex items-center justify-center mx-auto mb-6 shadow-lg shadow-blue-500/25">
                        4
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 dark:text-white mb-2">Review and save</h3>
                    <p class="text-gray-600 dark:text-gray-400 text-sm">
                        Preview, edit if needed, and apply.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Powered by Google Gemini -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24 border-t border-gray-200 dark:border-white/5">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <div class="inline-flex items-center justify-center w-20 h-20 rounded-3xl bg-gradient-to-br from-blue-500/20 to-sky-500/20 border border-blue-500/30 mb-8">
                    <svg aria-hidden="true" class="w-10 h-10 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456z" />
                    </svg>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Powered by Google Gemini
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400 max-w-2xl mx-auto">
                    Fast, accurate AI that works with text and images in any language.
                </p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Free tier</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Google offers a generous free tier for Gemini API</p>
                </div>
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Fast</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Events parsed and content generated in seconds</p>
                </div>
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Multimodal</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Works with text, images, screenshots, and flyers</p>
                </div>
                <div class="bg-white dark:bg-white/5 rounded-2xl p-6 border border-gray-200 dark:border-white/10 text-center">
                    <div class="text-2xl font-bold text-gray-900 dark:text-white mb-1">Any language</div>
                    <p class="text-gray-500 dark:text-gray-400 text-sm">Parse and generate content in any language</p>
                </div>
            </div>

            <div class="text-center">
                <p class="text-sm text-gray-500 dark:text-gray-400">
                    Selfhosted users bring their own Gemini API key.
                    <x-link href="{{ route('marketing.docs.selfhost.ai') }}">Learn more</x-link>
                </p>
            </div>
        </div>
    </section>

    <!-- Guide & Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-sky-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-cyan-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.creating_events') }}" class="group block">
                    <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-blue-500/10 border border-blue-500/20 mb-6">
                            <svg aria-hidden="true" class="w-6 h-6 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">Read the guide</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Learn how to use all AI-powered features.</p>
                        <span class="inline-flex items-center text-blue-500 dark:text-blue-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Read guide
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Next feature -->
                <a href="{{ route('marketing.newsletters') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 rounded-3xl border border-sky-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-sky-600 dark:group-hover:text-sky-300 transition-colors">Newsletters</h3>
                        <p class="text-gray-500 dark:text-white/80 text-lg mb-4">Send branded newsletters to followers and ticket buyers with drag-and-drop building and A/B testing.</p>
                        <span class="inline-flex items-center text-sky-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Learn more
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Popular with -->
                <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-sky-500/10 border border-sky-500/20 mb-6">
                        <svg aria-hidden="true" class="w-6 h-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-curators') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Curators</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-musicians') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Musicians</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-venues') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-sky-300 dark:hover:border-sky-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Venues</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-sky-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="bg-gray-100 dark:bg-black/30 py-24">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    Frequently asked questions
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Everything you need to know about AI features.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What formats does the AI parser support?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            The AI can parse plain text, images (JPEG, PNG, GIF, WebP), screenshots, flyers, agendas, and setlists. It handles both single events and multi-event documents, extracting each event individually.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 rounded-2xl border border-sky-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Which AI features are free vs. Enterprise?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            AI event parsing and instant translation are available on all plans including the free tier. Style generation, content generation, flyer generation, graphic text processing, and WhatsApp event creation are Enterprise features.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-blue-100 to-cyan-100 dark:from-blue-900 dark:to-cyan-900 rounded-2xl border border-blue-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How accurate is the AI?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            The AI is highly accurate for standard event formats. You always get to review and edit the results before saving. You can also add custom prompts to guide the AI for your specific format.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-teal-100 dark:from-emerald-900 dark:to-teal-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What can AI Style Generation create?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            AI Style Generation creates a complete visual identity for your schedule including profile image, header image, background image, accent color, and font. You can add custom style instructions to guide the look and regenerate individual elements.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 rounded-2xl border border-emerald-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 5 ? null : 5" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            How does WhatsApp event creation work?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 5 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 5" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Send a text message or photo of a flyer to your schedule's WhatsApp number. AI parses the details using Gemini, auto-generates a flyer, and adds the event to your schedule automatically.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 rounded-2xl border border-sky-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 6 ? null : 6" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            What languages are supported?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 6 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 6" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            The AI can parse events in any language and extract details correctly. The translation feature supports 11 languages including English, Spanish, French, German, Italian, Portuguese, Dutch, Hebrew, Arabic, Estonian, and Russian.
                        </p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-gray-100 to-slate-100 dark:from-gray-800 dark:to-slate-800 rounded-2xl border border-gray-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 7 ? null : 7" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">
                            Can AI agents use Event Schedule programmatically?
                        </h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300" :class="{ 'rotate-180': open === 7 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 7" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">
                            Yes. Event Schedule provides a full REST API with OpenAPI 3.0 specification, llms.txt for AI discovery, and agents.json for defining agent workflows. AI agents can create, update, and manage events programmatically with smart creation features and webhook notifications.
                        </p>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Related Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-20">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-8 text-center">Related features</h2>
            <div class="space-y-3">
                <x-feature-link-card
                    name="Event Graphics"
                    description="Auto-generated shareable images for Instagram, WhatsApp, and email"
                    :url="marketing_url('/features/event-graphics')"
                    icon-color="orange"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-orange-600 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Calendar Sync"
                    description="Two-way sync with Google Calendar"
                    :url="marketing_url('/features/calendar-sync')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Recurring Events"
                    description="Set events to repeat on any schedule automatically"
                    :url="marketing_url('/features/recurring-events')"
                    icon-color="green"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Custom Fields"
                    description="Collect additional info from attendees with custom form fields"
                    :url="marketing_url('/features/custom-fields')"
                    icon-color="amber"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="API for AI Agents"
                    description="REST API with OpenAPI spec, llms.txt, and agents.json for AI agent workflows"
                    :url="marketing_url('/for-ai-agents')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-blue-600 to-sky-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Let AI do the heavy lifting
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                From smart import to content generation and visual branding. AI handles the tedious work so you can focus on your events.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-blue-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>

    <!-- Product Schema for Rich Snippets -->
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule AI Features",
        "applicationCategory": "BusinessApplication",
        "applicationSubCategory": "AI Event Management Software",
        "operatingSystem": "Web",
        "description": "AI-powered event management. Generate flyers, write descriptions, create brand style, parse text and images, scan agendas, translate to 11 languages, create events via WhatsApp, and automate with a full API for AI agents.",
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD",
            "description": "Included free"
        },
        "featureList": [
            "AI-powered text parsing",
            "Image and flyer import",
            "Agenda and setlist parsing",
            "Multi-event image extraction",
            "Automatic event extraction",
            "AI agenda scanning for event parts",
            "Custom AI prompts",
            "Instant translation",
            "AI style generation",
            "AI content generation",
            "AI flyer generation",
            "AI graphic text processing",
            "WhatsApp event creation",
            "REST API for AI agents",
            "OpenAPI 3.0 specification",
            "llms.txt AI discovery",
            "agents.json workflows",
            "Webhook notifications",
            "Google Gemini powered"
        ],
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
        }
    }
    </script>
</x-marketing-layout>
