<x-marketing-layout>
    <x-slot name="title">Schedule Styling - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Schedule Styling</x-slot>
    <x-slot name="description">Customize your schedule's appearance with colors, fonts, backgrounds, and more. Make your schedule uniquely yours.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "Schedule Styling - Event Schedule",
        "description": "Customize your schedule's appearance with colors, fonts, backgrounds, and more. Make your schedule uniquely yours.",
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
        "dateModified": "2026-02-01"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Schedule Styling" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">Schedule Styling</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Customize your schedule's visual appearance with colors, fonts, backgrounds, and more. All changes preview in real-time.
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
                        <a href="#event-layout" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Event Layout</a>
                        <a href="#profile-image" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Profile Image</a>
                        <a href="#header-images" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Header Images</a>
                        <a href="#backgrounds" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Background Options</a>
                        <a href="#color-scheme" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Color Scheme</a>
                        <a href="#typography" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Typography</a>
                        <a href="#ai-style-generator" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">AI Style Generator</a>
                        <a href="#remove-branding" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Remove Branding</a>
                        <a href="#custom-css" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Custom CSS</a>
                        <a href="#live-preview" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Live Preview</a>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Make your schedule uniquely yours with extensive styling options. Access styling settings in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>, then scroll to the styling section.</p>

                            <x-doc-screenshot id="schedule-styling--section-style" alt="Schedule styling settings" loading="eager" />

                            <p class="text-gray-600 dark:text-gray-300 mb-6">All styling changes update in real-time, so you can experiment freely and see exactly how your schedule will look before saving.</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">New here?</div>
                                <p>If you haven't created your account and first schedule yet, start with <a href="{{ route('marketing.docs.getting_started') }}" class="text-cyan-400 hover:text-cyan-300">Getting Started</a>.</p>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Getting Started</div>
                                <p>If you haven't set up your schedule's basic information yet, see <a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Creating Schedules</a> first.</p>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Choose how your events are displayed on your schedule page:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Grid Layout</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Events displayed in a card grid. Best for schedules with event images and visual appeal.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">List Layout</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Events displayed in a compact list. Best for text-heavy schedules or when you have many events.</p>
                                </div>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Upload a profile image (logo, photo, or avatar) that represents your schedule. This appears at the top of your schedule page and in social media previews.</p>
                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Best Practices</div>
                                <p>Use a <strong>square image</strong> (1:1 aspect ratio) for best results. Recommended minimum size is 400x400 pixels. PNG or JPG formats work best.</p>
                            </div>
                        </section>

                        <!-- Header Images -->
                        <section id="header-images" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 15.75l5.159-5.159a2.25 2.25 0 013.182 0l5.159 5.159m-1.5-1.5l1.409-1.409a2.25 2.25 0 013.182 0l2.909 2.909m-18 3.75h16.5a1.5 1.5 0 001.5-1.5V6a1.5 1.5 0 00-1.5-1.5H3.75A1.5 1.5 0 002.25 6v12a1.5 1.5 0 001.5 1.5zm10.5-11.25h.008v.008h-.008V8.25zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z" />
                                </svg>
                                Header Images
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add a header image to create a visual banner at the top of your schedule page. You have three options:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Preset Headers</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose from 31 professionally designed headers including: 5am_Club, Arena, Fitness_Morning, Concert, Festival, Jazz_Club, Neon_Lights, Skyline, Stadium, and many more. Perfect for getting started quickly.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom Header Upload</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Upload your own header image. Recommended dimensions: 1200x400 pixels or similar wide aspect ratio.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">No Header</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Opt for a cleaner look without a header image. Your profile image and name take center stage.</p>
                                </div>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Customize your schedule's background with colors, gradients, or images.</p>

                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3 mt-6">Solid Color</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Choose any color using the hex color picker. Enter a specific hex code (e.g., <code class="doc-inline-code">#1a1a2e</code>) or use the visual picker.</p>

                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3 mt-6">Gradient Backgrounds</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Choose from 200+ beautiful preset gradients or create your own:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Preset Gradients</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Browse gradients by name: Omolon, Purple Dream, Netflix, Sunset, Ocean Blue, Emerald Water, Cosmic Fusion, and 190+ more. Hover to preview, click to apply.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom 2-Color Gradients</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Pick two colors to create your own gradient. Perfect for matching your brand colors exactly.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Rotation Control</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Adjust the gradient angle from 0 to 360 degrees. Create vertical, horizontal, diagonal, or any angle you want.</p>
                                </div>
                            </div>

                            <h3 class="text-lg font-medium text-gray-900 dark:text-white mb-3 mt-6">Background Images</h3>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add depth with background images:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Preset Backgrounds</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Choose from 37 preset backgrounds including: Abstract_Sunrise, Ocean, Mountains, City_Lights, Stars, Geometric, Marble, and more. All optimized for performance.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Custom Image Upload</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Upload your own background image. Images are automatically optimized. Dark, subtle patterns work best to maintain readability.</p>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Set your accent color to match your brand:</p>
                            <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10 mb-6">
                                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Accent Color</h4>
                                <p class="text-sm text-gray-500 dark:text-gray-400">This color is used for buttons, links, icons, and interactive elements throughout your schedule. Event Schedule automatically calculates contrasting text colors for accessibility.</p>
                            </div>
                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Accessibility</div>
                                <p>Choose an accent color with good contrast. Event Schedule automatically adjusts text colors to ensure readability, but vibrant, saturated colors tend to work best.</p>
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
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Choose from 200+ Google Fonts to give your schedule a unique personality:</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Font Categories</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Sans-serif</strong> (clean, modern), <strong class="text-gray-900 dark:text-white">Serif</strong> (classic, elegant), <strong class="text-gray-900 dark:text-white">Display</strong> (bold, attention-grabbing), <strong class="text-gray-900 dark:text-white">Decorative</strong> (unique, artistic).</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Live Preview</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">See how each font looks on your schedule in real-time before committing. Test readability with your content.</p>
                                </div>
                            </div>
                        </section>

                        <!-- AI Style Generator -->
                        <section id="ai-style-generator" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
                                </svg>
                                AI Style Generator <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Enterprise</span>
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Let AI generate a cohesive visual style for your schedule. The AI style generator can create a matching accent color, font selection, profile image, header image, and background image based on your schedule's name and type.</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">How It Works</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">Click the <strong class="text-gray-900 dark:text-white">AI</strong> button in the Style section header, select which elements to generate, and optionally provide style instructions (e.g., "modern and minimal with blue tones"). The AI will generate all selected elements with a consistent visual theme.</p>
                                </div>
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Generated Elements</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400"><strong class="text-gray-900 dark:text-white">Accent color</strong> and <strong class="text-gray-900 dark:text-white">font</strong> are generated first, then images are created using the chosen color for a cohesive look. You can select any combination of profile image, header image, background image, accent color, and font.</p>
                                </div>
                            </div>
                            <div class="bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6">
                                <p class="text-sm text-blue-800 dark:text-blue-300"><strong>Note:</strong> Selfhosted installations require a <x-link href="https://ai.google.dev/" target="_blank">Gemini API key</x-link> and an <x-link href="https://platform.openai.com/" target="_blank">OpenAI API key</x-link> configured in the environment settings for AI style features to work.</p>
                            </div>
                        </section>

                        <!-- Remove Branding -->
                        <section id="remove-branding" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Remove Branding <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800 dark:bg-amber-900/30 dark:text-amber-400 ml-2">Pro</span>
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">By default, schedules display a small "Powered by Event Schedule" badge. With the Pro plan, you can remove this branding for a fully white-labeled experience.</p>
                            <div class="space-y-3 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">White-Label Your Schedule</h4>
                                    <p class="text-sm text-gray-500 dark:text-gray-400">When branding is removed, your schedule appears entirely as your own - no Event Schedule branding is visible to visitors. This is ideal for businesses, venues, and organizations that want a professional, branded presence.</p>
                                </div>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Branding is automatically removed for all Pro and Enterprise plans. Selfhosted installations also have branding removed by default.</p>
                        </section>

                        <!-- Custom CSS -->
                        <section id="custom-css" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.25 6.75L22.5 12l-5.25 5.25m-10.5 0L1.5 12l5.25-5.25m7.5-3l-4.5 16.5" />
                                </svg>
                                Custom CSS
                            </h2>
                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Custom CSS is available on Pro plans and for <a href="{{ route('marketing.docs.selfhost') }}" class="text-cyan-400 hover:text-cyan-300">selfhosted</a> installations.</p>
                            </div>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">For advanced customization, write your own CSS to modify any aspect of your schedule's appearance:</p>
                            <ul class="doc-list mb-6">
                                <li>Override default styles for complete control</li>
                                <li>Add custom animations and effects</li>
                                <li>Fine-tune spacing, borders, and shadows</li>
                                <li>CSS is sanitized for security - modern properties supported</li>
                            </ul>
                        </section>

                        <!-- Live Preview -->
                        <section id="live-preview" class="doc-section">
                            <h2 class="doc-heading">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 17.25v1.007a3 3 0 01-.879 2.122L7.5 21h9l-.621-.621A3 3 0 0115 18.257V17.25m6-12V15a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 15V5.25m18 0A2.25 2.25 0 0018.75 3H5.25A2.25 2.25 0 003 5.25m18 0V12a2.25 2.25 0 01-2.25 2.25H5.25A2.25 2.25 0 013 12V5.25" />
                                </svg>
                                Live Preview
                            </h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">All styling changes update in real-time. The preview panel shows exactly how your schedule will look to visitors, so you can experiment freely without publishing incomplete changes.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Don't forget to click <strong class="text-gray-900 dark:text-white">Save</strong> when you're happy with your changes. The preview is just a preview - nothing is saved until you explicitly save.</p>
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
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Creating Schedules</a> - Configure details, settings, sub-schedules, and integrations</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Event Graphics</a> - Generate shareable images for social media</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Sharing Your Schedule</a> - Embed and share your schedule</li>
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Creating Events</a> - Add events to see your styling in action</li>
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
        "name": "How to Style Your Event Schedule",
        "description": "Customize your schedule's appearance with colors, fonts, backgrounds, and more. All changes preview in real-time.",
        "totalTime": "PT5M",
        "step": [
            {
                "@type": "HowToStep",
                "name": "Choose Event Layout",
                "text": "Select grid layout for visual schedules with event images, or list layout for compact, text-heavy schedules.",
                "url": "{{ url(route('marketing.docs.schedule_styling')) }}#event-layout"
            },
            {
                "@type": "HowToStep",
                "name": "Upload Profile and Header Images",
                "text": "Upload a square profile image and choose a header from 31 presets, upload a custom header, or opt for no header.",
                "url": "{{ url(route('marketing.docs.schedule_styling')) }}#profile-image"
            },
            {
                "@type": "HowToStep",
                "name": "Set Background and Colors",
                "text": "Choose a solid color, gradient from 200+ presets, or background image. Set your accent color for buttons and links.",
                "url": "{{ url(route('marketing.docs.schedule_styling')) }}#backgrounds"
            },
            {
                "@type": "HowToStep",
                "name": "Choose Typography",
                "text": "Select from 200+ Google Fonts across sans-serif, serif, display, and decorative categories with live preview.",
                "url": "{{ url(route('marketing.docs.schedule_styling')) }}#typography"
            }
        ]
    }
    </script>
</x-marketing-layout>
