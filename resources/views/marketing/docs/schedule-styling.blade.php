<x-marketing-layout>
    <x-slot name="title">Schedule Styling - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">Schedule Styling</x-slot>
    <x-slot name="description">Customize your schedule's appearance with colors, fonts, backgrounds, and more. Make your schedule uniquely yours.</x-slot>
    <x-slot name="keywords">schedule styling, customize schedule, event calendar design, custom branding, schedule colors, schedule fonts</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

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
                    <svg class="w-6 h-6 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
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
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Make your schedule uniquely yours with extensive styling options. Access styling settings in <strong class="text-gray-900 dark:text-white">Admin Panel &rarr; Profile &rarr; Edit</strong>, then scroll to the styling section.</p>

                            <p class="text-gray-600 dark:text-gray-300 mb-6">All styling changes update in real-time, so you can experiment freely and see exactly how your schedule will look before saving.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Getting Started</div>
                                <p>If you haven't set up your schedule's basic information yet, see <a href="{{ route('marketing.docs.schedule_basics') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Basics</a> first.</p>
                            </div>
                        </section>

                        <!-- Event Layout -->
                        <section id="event-layout" class="doc-section">
                            <h2 class="doc-heading">Event Layout</h2>
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
                            <h2 class="doc-heading">Profile Image</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Upload a profile image (logo, photo, or avatar) that represents your schedule. This appears at the top of your schedule page and in social media previews.</p>
                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Best Practices</div>
                                <p>Use a <strong>square image</strong> (1:1 aspect ratio) for best results. Recommended minimum size is 400x400 pixels. PNG or JPG formats work best.</p>
                            </div>
                        </section>

                        <!-- Header Images -->
                        <section id="header-images" class="doc-section">
                            <h2 class="doc-heading">Header Images</h2>
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
                            <h2 class="doc-heading">Background Options</h2>
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
                            <h2 class="doc-heading">Color Scheme</h2>
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
                            <h2 class="doc-heading">Typography</h2>
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

                        <!-- Custom CSS -->
                        <section id="custom-css" class="doc-section">
                            <h2 class="doc-heading">Custom CSS</h2>
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
                            <h2 class="doc-heading">Live Preview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">All styling changes update in real-time. The preview panel shows exactly how your schedule will look to visitors, so you can experiment freely without publishing incomplete changes.</p>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Don't forget to click <strong class="text-gray-900 dark:text-white">Save</strong> when you're happy with your changes. The preview is just a preview - nothing is saved until you explicitly save.</p>
                            </div>
                        </section>

                        <!-- See Also -->
                        <section id="see-also" class="doc-section">
                            <h2 class="doc-heading">See Also</h2>
                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.schedule_basics') }}" class="text-cyan-400 hover:text-cyan-300">Schedule Basics</a> - Configure name, type, location, and settings</li>
                                <li><a href="{{ route('marketing.docs.creating_schedules') }}" class="text-cyan-400 hover:text-cyan-300">Advanced Schedule Settings</a> - Subschedules, calendar integrations, and auto-import</li>
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
