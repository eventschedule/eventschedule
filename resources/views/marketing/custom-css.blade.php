<x-marketing-layout>
    <x-slot name="title">Custom CSS | Advanced Schedule Styling - Event Schedule</x-slot>
    <x-slot name="description">Write your own CSS to customize every aspect of your schedule's appearance. Override styles, add animations, and create a unique look.</x-slot>
    <x-slot name="breadcrumbTitle">Custom CSS</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Custom CSS",
        "description": "Write your own CSS to customize every aspect of your schedule's appearance.",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": ["Web", "Android", "iOS"],
        "featureList": [
            "Custom CSS injection",
            "Override default styles",
            "Custom animations and effects",
            "Sanitized for security"
        ],
        "offers": {
            "@type": "Offer",
            "price": "5",
            "priceCurrency": "USD",
            "description": "Available on Pro plan"
        }
    }
    </script>
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "FAQPage",
        "mainEntity": [
            {
                "@type": "Question",
                "name": "How do I add Custom CSS to my schedule?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Go to Admin Panel, then Profile, then Edit and scroll to the styling section. You'll find a Custom CSS text area where you can write your CSS rules."
                }
            },
            {
                "@type": "Question",
                "name": "Is Custom CSS secure?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. All CSS is sanitized before being applied. JavaScript, external URLs, and potentially dangerous properties are filtered out while allowing modern CSS properties."
                }
            },
            {
                "@type": "Question",
                "name": "Can I use Custom CSS with the built-in styling options?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. Custom CSS works alongside all built-in styling options like colors, fonts, backgrounds, and header images. Use the built-in options for quick changes and Custom CSS for fine-tuning."
                }
            },
            {
                "@type": "Question",
                "name": "Which plan includes Custom CSS?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Custom CSS is available on Pro and Enterprise plans, as well as selfhosted installations."
                }
            }
        ]
    }
    </script>
    </x-slot>

    <style {!! nonce_attr() !!}>
        .text-gradient {
            background: linear-gradient(135deg, #7c3aed 0%, #a855f7 50%, #c084fc 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .dark .text-gradient {
            background: linear-gradient(135deg, #a78bfa 0%, #c084fc 50%, #d8b4fe 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-32 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-purple-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8 animate-reveal" style="opacity: 0;">
                <svg aria-hidden="true" class="w-4 h-4 text-violet-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Custom CSS</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight animate-reveal delay-100" style="opacity: 0;">
                Style it<br>
                <span class="text-gradient">your way</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-500 dark:text-gray-400 max-w-3xl mx-auto mb-12 animate-reveal delay-200" style="opacity: 0;">
                Write your own CSS to customize every pixel of your schedule. Override defaults, add animations, and create a look that's uniquely yours.
            </p>

            <div class="flex flex-wrap justify-center gap-4 animate-reveal delay-300" style="opacity: 0;">
                <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-violet-600 to-purple-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-violet-500/25">
                    Get started free
                    <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Full Control (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                Full Control
                            </div>
                            <h2 class="text-3xl lg:text-4xl font-bold text-gray-900 dark:text-white mb-4">Write real CSS</h2>
                            <p class="text-gray-600 dark:text-white/80 text-lg mb-6">No drag-and-drop limitations. Write actual CSS to override any style, add animations, adjust spacing, or completely transform how your schedule looks.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Override any style</span>
                                <span class="inline-flex items-center px-3 py-1 rounded-full bg-gray-300 dark:bg-white/10 text-gray-700 dark:text-gray-300 text-sm">Modern CSS supported</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="relative animate-float">
                                <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-2xl border border-gray-200 dark:border-white/10 p-5 w-72">
                                    <div class="flex items-center gap-2 mb-3">
                                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-yellow-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                        <span class="text-xs text-gray-400 ml-2">custom.css</span>
                                    </div>
                                    <div class="text-sm font-mono space-y-1">
                                        <div class="text-violet-600 dark:text-violet-400">.event-card {</div>
                                        <div class="pl-4 text-purple-600 dark:text-purple-400">border-radius: 1rem;</div>
                                        <div class="pl-4 text-purple-600 dark:text-purple-400">box-shadow: 0 4px 20px</div>
                                        <div class="pl-8 text-purple-600 dark:text-purple-400">rgba(0,0,0,0.1);</div>
                                        <div class="text-violet-600 dark:text-violet-400">}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Secure -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-100 to-violet-100 dark:from-purple-900 dark:to-violet-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-purple-100 text-purple-700 dark:bg-purple-500/20 dark:text-purple-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                        Secure
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Sanitized for safety</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-6">All CSS is sanitized before being applied. JavaScript, external URLs, and potentially dangerous properties are filtered out while modern CSS properties are fully supported.</p>

                    <div class="space-y-2">
                        <div class="flex items-center gap-3 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                            <svg aria-hidden="true" class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">No XSS vulnerabilities</span>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                            <svg aria-hidden="true" class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">No external requests</span>
                        </div>
                        <div class="flex items-center gap-3 px-3 py-2 bg-gray-100 dark:bg-white/5 rounded-lg">
                            <svg aria-hidden="true" class="w-4 h-4 text-green-500" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                            </svg>
                            <span class="text-gray-600 dark:text-gray-300 text-sm">Modern CSS supported</span>
                        </div>
                    </div>
                </div>

                <!-- Works with Built-in Styling -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 border border-gray-200 dark:border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                        <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                        Composable
                    </div>
                    <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-3">Layer on top</h2>
                    <p class="text-gray-600 dark:text-white/80 mb-6">Custom CSS works alongside all built-in styling options. Use the visual tools for quick changes and CSS for the finishing touches.</p>

                    <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10">
                        <div class="space-y-2 text-sm">
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-violet-500"></div>
                                <span class="text-gray-600 dark:text-gray-300">Colors & gradients</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-purple-500"></div>
                                <span class="text-gray-600 dark:text-gray-300">Fonts & typography</span>
                            </div>
                            <div class="flex items-center gap-2">
                                <div class="w-4 h-4 rounded bg-fuchsia-500"></div>
                                <span class="text-gray-600 dark:text-gray-300">+ Your Custom CSS</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pro Feature (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-100 to-violet-100 dark:from-purple-900 dark:to-violet-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-violet-100 text-violet-700 dark:bg-violet-500/20 dark:text-violet-300 text-sm font-medium mb-4">
                                <svg aria-hidden="true" class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" />
                                </svg>
                                Pro Feature
                            </div>
                            <h2 class="text-3xl font-bold text-gray-900 dark:text-white mb-4">Included with Pro</h2>
                            <p class="text-gray-600 dark:text-white/80 text-lg">Custom CSS is part of the Pro plan, alongside white-label branding, event graphics, ticketing, and more. Start free and upgrade when you need advanced styling.</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg aria-hidden="true" class="w-8 h-8 text-violet-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                                </svg>
                                <div class="text-violet-600 dark:text-violet-400 text-sm">Custom CSS</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg aria-hidden="true" class="w-8 h-8 text-violet-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                                </svg>
                                <div class="text-violet-600 dark:text-violet-400 text-sm">White Label</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg aria-hidden="true" class="w-8 h-8 text-violet-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <div class="text-violet-600 dark:text-violet-400 text-sm">Event Graphics</div>
                            </div>
                            <div class="bg-gray-100 dark:bg-[#0f0f14] rounded-xl p-4 border border-gray-200 dark:border-white/10 text-center">
                                <svg aria-hidden="true" class="w-8 h-8 text-violet-500 mx-auto mb-2" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                                </svg>
                                <div class="text-violet-600 dark:text-violet-400 text-sm">Ticketing</div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Use Cases Section -->
    <section class="bg-gray-50 dark:bg-[#0f0f14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-4">
                    What you can do with Custom CSS
                </h2>
                <p class="text-xl text-gray-500 dark:text-gray-400">
                    Go beyond the built-in styling options.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-8">
                <div class="block bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30 rounded-2xl p-6 border border-violet-200 dark:border-violet-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-violet-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Custom Layouts</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Adjust card sizes, spacing, and grid behavior for a unique layout.</p>
                </div>

                <div class="block bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30 rounded-2xl p-6 border border-violet-200 dark:border-violet-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-violet-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Animations</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Add hover effects, transitions, and CSS animations to your events.</p>
                </div>

                <div class="block bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30 rounded-2xl p-6 border border-violet-200 dark:border-violet-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-violet-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h7" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Fine-Tuning</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Adjust borders, shadows, spacing, and typography details.</p>
                </div>

                <div class="block bg-gradient-to-br from-violet-50 to-purple-50 dark:from-violet-900/30 dark:to-purple-900/30 rounded-2xl p-6 border border-violet-200 dark:border-violet-500/20 shadow-sm text-center">
                    <div class="inline-flex items-center justify-center w-16 h-16 rounded-2xl bg-violet-100 dark:bg-white/10 mb-6">
                        <svg aria-hidden="true" class="w-8 h-8 text-violet-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Brand Matching</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-sm">Match your website's exact styles for a seamless embed experience.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Guide & Next Feature -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-20 overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-violet-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-purple-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Read the guide -->
                <a href="{{ route('marketing.docs.schedule_styling') }}#custom-css" class="group block">
                    <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-violet-500/10 border border-violet-500/20 mb-6">
                            <svg aria-hidden="true" class="w-6 h-6 text-violet-500 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-violet-600 dark:group-hover:text-violet-400 transition-colors">Read the guide</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Learn how to write Custom CSS for your schedule.</p>
                        <span class="inline-flex items-center text-violet-500 dark:text-violet-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Read guide
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Next feature -->
                <a href="{{ marketing_url('/features/white-label') }}" class="group block">
                    <div class="h-full bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 rounded-3xl border border-violet-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300 flex flex-col">
                        <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-3 group-hover:text-violet-600 dark:group-hover:text-violet-300 transition-colors">White Label</h3>
                        <p class="text-gray-500 dark:text-gray-400 text-lg mb-4">Remove Event Schedule branding for a fully branded experience.</p>
                        <span class="inline-flex items-center text-violet-400 font-medium group-hover:gap-3 gap-2 transition-all mt-auto">
                            Learn more
                            <svg aria-hidden="true" class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                            </svg>
                        </span>
                    </div>
                </a>

                <!-- Popular with -->
                <div class="h-full bg-white dark:bg-white/5 rounded-3xl border border-gray-200 dark:border-white/10 p-8 lg:p-10">
                    <div class="inline-flex items-center justify-center w-12 h-12 rounded-2xl bg-violet-500/10 border border-violet-500/20 mb-6">
                        <svg aria-hidden="true" class="w-6 h-6 text-violet-500 dark:text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                    </div>
                    <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-4">Popular with</h3>
                    <div class="space-y-3">
                        <a href="{{ marketing_url('/for-venues') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-violet-300 dark:hover:border-violet-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Venues</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-violet-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-art-galleries') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-violet-300 dark:hover:border-violet-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Art Galleries</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-violet-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </a>
                        <a href="{{ marketing_url('/for-music-venues') }}" class="flex items-center justify-between p-3 rounded-xl bg-gray-50 dark:bg-white/5 border border-gray-200 dark:border-white/10 hover:bg-gray-100 dark:hover:bg-white/10 hover:border-violet-300 dark:hover:border-violet-500/30 transition-all group/link">
                            <span class="text-gray-900 dark:text-white font-medium">Music Venues</span>
                            <svg aria-hidden="true" class="w-4 h-4 text-gray-400 group-hover/link:text-violet-500 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                    Everything you need to know about Custom CSS.
                </p>
            </div>

            <div class="space-y-4" x-data="{ open: null }">
                <div class="bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 rounded-2xl border border-violet-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">How do I add Custom CSS to my schedule?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 1 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 1" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Go to Admin Panel, then Profile, then Edit and scroll to the styling section. You'll find a Custom CSS text area where you can write your CSS rules. Changes take effect immediately on save.</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-100 to-violet-100 dark:from-purple-900 dark:to-violet-900 rounded-2xl border border-purple-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Is Custom CSS secure?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 2 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 2" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Yes. All CSS is sanitized before being applied. JavaScript, external URLs, and potentially dangerous properties are filtered out while allowing modern CSS properties for styling.</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-violet-100 to-purple-100 dark:from-violet-900 dark:to-purple-900 rounded-2xl border border-violet-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Can I use Custom CSS with the built-in styling options?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 3 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 3" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Yes. Custom CSS works alongside all built-in styling options. Use the visual tools for quick changes like colors and fonts, and CSS for fine-tuning details like spacing, shadows, and animations.</p>
                    </div>
                </div>

                <div class="bg-gradient-to-br from-purple-100 to-violet-100 dark:from-purple-900 dark:to-violet-900 rounded-2xl border border-purple-200 dark:border-white/10 shadow-sm overflow-hidden">
                    <button @click="open = open === 4 ? null : 4" class="w-full flex items-center justify-between p-6 text-left cursor-pointer">
                        <h3 class="text-lg font-semibold text-gray-900 dark:text-white">Which plan includes Custom CSS?</h3>
                        <svg aria-hidden="true" class="w-5 h-5 text-gray-500 dark:text-gray-400 transition-transform duration-300 flex-shrink-0 ml-4" :class="{ 'rotate-180': open === 4 }" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <div x-show="open === 4" x-collapse>
                        <p class="px-6 pb-6 text-gray-600 dark:text-gray-400">Custom CSS is available on Pro and Enterprise plans, as well as selfhosted installations. Free plans include the full visual styling suite (colors, fonts, gradients, backgrounds).</p>
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
                    name="White Label"
                    description="Remove Event Schedule branding for a fully branded experience"
                    :url="marketing_url('/features/white-label')"
                    icon-color="blue"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Embed Calendar"
                    description="Embed your schedule on any website with an iframe"
                    :url="marketing_url('/features/embed-calendar')"
                    icon-color="sky"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
                <x-feature-link-card
                    name="Event Graphics"
                    description="Generate shareable images for social media"
                    :url="marketing_url('/features/event-graphics')"
                    icon-color="rose"
                >
                    <x-slot:icon>
                        <svg aria-hidden="true" class="w-5 h-5 text-rose-600 dark:text-rose-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </x-slot:icon>
                </x-feature-link-card>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-violet-600 to-purple-700 py-24 overflow-hidden">
        <div class="absolute inset-0 grid-overlay"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Ready to customize?
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Upgrade to Pro and unlock Custom CSS for complete control over your schedule's appearance.
            </p>
            <a href="{{ app_url('/sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-violet-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg aria-hidden="true" class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
