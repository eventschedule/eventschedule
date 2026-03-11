<x-marketing-layout>
    <x-slot name="title">AI Setup Documentation - Event Schedule</x-slot>
    <x-slot name="breadcrumbTitle">AI Setup</x-slot>
    <x-slot name="description">Configure Google Gemini and OpenAI for your selfhosted Event Schedule instance. Enable AI-powered event importing, agenda scanning, automatic translations, AI-generated text on event graphics, and image generation.</x-slot>
    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "TechArticle",
        "headline": "AI Setup Documentation - Event Schedule",
        "description": "Configure Google Gemini and OpenAI for your selfhosted Event Schedule instance. Enable AI-powered event importing, agenda scanning, automatic translations, AI-generated text on event graphics, and image generation.",
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
        "dateModified": "2026-03-11"
    }
    </script>
    </x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-white dark:bg-[#0a0a0f] py-16 overflow-hidden border-b border-gray-200 dark:border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-violet-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 grid-pattern"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="AI Setup" section="selfhost" sectionTitle="Selfhost" sectionRoute="marketing.docs.selfhost" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-violet-500/20">
                    <svg aria-hidden="true" class="w-6 h-6 text-violet-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">AI Setup</h1>
            </div>
            <p class="text-lg text-gray-500 dark:text-gray-400 max-w-3xl">
                Enable AI-powered features by connecting your Event Schedule instance to Google Gemini and OpenAI.
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
                        <a href="#features" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">AI Features</a>
                        <a href="#gemini-api-key" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Get a Gemini API Key</a>
                        <a href="#openai-api-key" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Get an OpenAI API Key</a>
                        <a href="#configuration" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Configuration</a>
                        <a href="#troubleshooting" class="doc-nav-link block px-3 py-2 text-sm text-gray-500 dark:text-gray-400 hover:text-gray-900 dark:hover:text-white hover:bg-gray-100 dark:hover:bg-white/5 rounded-lg transition-colors">Troubleshooting</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Overview -->
                        <section id="overview" class="doc-section">
                            <h2 class="doc-heading">Overview</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule supports two AI providers: <a href="https://ai.google.dev/" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">Google Gemini</a> and <a href="https://platform.openai.com/" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">OpenAI</a>. Configuring just one key enables all AI features - text and image generation. If both keys are configured, Gemini handles text and OpenAI handles images. These features are optional - Event Schedule works without them, but they can save significant time when creating and managing events.</p>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Optional Setup</div>
                                <p>AI features are entirely optional. If you don't configure API keys, Event Schedule will work normally with all non-AI features. AI-related buttons and options will either be hidden or show a message prompting you to configure the API keys.</p>
                            </div>
                        </section>

                        <!-- AI Features -->
                        <section id="features" class="doc-section">
                            <h2 class="doc-heading">AI Features</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">The following text-based features are available (uses Gemini if configured, otherwise OpenAI):</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI Import</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Paste unstructured text (email, message, flyer text) and AI will automatically extract event details like title, date, time, location, and description to create an event.</p>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Scan Agenda</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Upload a photo or screenshot of an event agenda, poster, or flyer. AI reads the image and extracts event information to create multiple events at once.</p>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI Text on Event Graphics</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">When generating event graphics for social media sharing, AI creates engaging promotional text tailored to the event.</p>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Automatic Translations</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">AI-powered translation of event details, schedule names, sub-schedule names, and custom fields into multiple languages.</p>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI Details Generator</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Generate short descriptions and full descriptions for your schedules and events using AI, based on the name and context.</p>
                                </div>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4">The following image generation features are available (uses OpenAI if configured, otherwise Gemini Imagen):</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI Flyer Generation</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Generate professional event flyer images from event details. Customize the style with optional instructions.</p>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI Style Generation (Images)</h4>
                                    <p class="text-gray-600 dark:text-gray-400 text-sm">Generate cohesive profile images, header images, and background images for your schedule branding. Works alongside Gemini-powered accent color and font selection.</p>
                                </div>
                            </div>
                        </section>

                        <!-- Get a Gemini API Key -->
                        <section id="gemini-api-key" class="doc-section">
                            <h2 class="doc-heading">Get a Gemini API Key</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Follow these steps to get a Gemini API key from Google:</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">Google AI Studio</a></li>
                                <li>Sign in with your Google account</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Create API Key</strong></li>
                                <li>Select or create a Google Cloud project</li>
                                <li>Copy the generated API key</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Free Tier</div>
                                <p>Google Gemini offers a free tier with generous rate limits that is sufficient for most selfhosted instances. Check <a href="https://ai.google.dev/pricing" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">Google's pricing page</a> for current limits and pricing.</p>
                            </div>
                        </section>

                        <!-- Get an OpenAI API Key -->
                        <section id="openai-api-key" class="doc-section">
                            <h2 class="doc-heading">Get an OpenAI API Key</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-6">Follow these steps to get an OpenAI API key for image generation:</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Go to <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">OpenAI Platform</a></li>
                                <li>Sign in or create an OpenAI account</li>
                                <li>Click <strong class="text-gray-900 dark:text-white">Create new secret key</strong></li>
                                <li>Give it a name (e.g., "Event Schedule") and copy the key</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Pricing</div>
                                <p>OpenAI image generation is a paid API. Each image costs a small amount based on size and quality. Check <a href="https://openai.com/api/pricing/" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">OpenAI's pricing page</a> for current rates.</p>
                            </div>
                        </section>

                        <!-- Configuration -->
                        <section id="configuration" class="doc-section">
                            <h2 class="doc-heading">Configuration</h2>
                            <p class="text-gray-600 dark:text-gray-300 mb-4">Add your API keys to the <code class="doc-inline-code">.env</code> file:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>.env</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code><span class="code-variable">GEMINI_API_KEY</span>=<span class="code-string">your-gemini-api-key-here</span>
<span class="code-variable">OPENAI_API_KEY</span>=<span class="code-string">your-openai-api-key-here</span>

<span class="code-comment"># Optional: choose which provider handles text and image requests</span>
<span class="code-comment"># Options: gemini, openai (defaults: text=gemini, image=openai)</span>
<span class="code-variable">AI_TEXT_PROVIDER</span>=<span class="code-string">gemini</span>
<span class="code-variable">AI_IMAGE_PROVIDER</span>=<span class="code-string">openai</span>

<span class="code-comment"># Optional: choose specific AI models (defaults shown)</span>
<span class="code-comment"># Translation models are used for batch translation tasks</span>
<span class="code-comment"># Content models are used for interactive features (event parsing, AI details, style generation, blog posts)</span>
<span class="code-variable">GEMINI_TRANSLATION_MODEL</span>=<span class="code-string">{{ config('services.google.gemini_translation_model') }}</span>
<span class="code-variable">GEMINI_CONTENT_MODEL</span>=<span class="code-string">{{ config('services.google.gemini_content_model') }}</span>
<span class="code-variable">GEMINI_IMAGE_MODEL</span>=<span class="code-string">{{ config('services.google.gemini_image_model') }}</span>
<span class="code-variable">OPENAI_TRANSLATION_MODEL</span>=<span class="code-string">{{ config('services.openai.translation_model') }}</span>
<span class="code-variable">OPENAI_CONTENT_MODEL</span>=<span class="code-string">{{ config('services.openai.content_model') }}</span>
<span class="code-variable">OPENAI_IMAGE_MODEL</span>=<span class="code-string">{{ config('services.openai.image_model') }}</span></code></pre>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mb-4 mt-6">After adding the keys, clear the config cache:</p>

                            <div class="doc-code-block">
                                <div class="doc-code-header">
                                    <span>bash</span>
                                    <button class="doc-copy-btn">Copy</button>
                                </div>
                                <pre><code>php artisan config:clear</code></pre>
                            </div>

                            <p class="text-gray-600 dark:text-gray-300 mt-6">That's it. AI features will be automatically available throughout the application once the keys are configured. Configuring just one key enables all AI features - the system automatically falls back to the available provider. If both keys are configured, Gemini handles text and OpenAI handles images by default. Use <code class="doc-inline-code">AI_TEXT_PROVIDER</code> and <code class="doc-inline-code">AI_IMAGE_PROVIDER</code> to change this. Translation and content models are configured separately so you can use a cheaper/faster model for batch translations and a more capable model for interactive content creation tasks.</p>
                        </section>

                        <!-- Troubleshooting -->
                        <section id="troubleshooting" class="doc-section">
                            <h2 class="doc-heading">Troubleshooting</h2>

                            <div class="space-y-4 mb-6">
                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Add GEMINI_API_KEY to the .env file" message</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Verify the key is set in your <code class="doc-inline-code">.env</code> file</li>
                                        <li>Run <code class="doc-inline-code">php artisan config:clear</code> to refresh the configuration</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Add OPENAI_API_KEY to the .env file" message</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Verify the key is set in your <code class="doc-inline-code">.env</code> file</li>
                                        <li>Run <code class="doc-inline-code">php artisan config:clear</code> to refresh the configuration</li>
                                        <li>This key is required for AI image generation features (flyers, style images)</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI text requests failing or timing out</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Check that your server can make outbound HTTPS connections to <code class="doc-inline-code">generativelanguage.googleapis.com</code></li>
                                        <li>Verify your API key is valid and not expired at <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">Google AI Studio</a></li>
                                        <li>Check <code class="doc-inline-code">storage/logs/laravel.log</code> for specific error messages</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI image generation failing</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Check that your server can make outbound HTTPS connections to <code class="doc-inline-code">api.openai.com</code></li>
                                        <li>Verify your OpenAI API key is valid at <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">OpenAI Platform</a></li>
                                        <li>Ensure your OpenAI account has available credits</li>
                                        <li>Some prompts may be rejected by OpenAI's content policy - try adjusting your style instructions</li>
                                    </ul>
                                </div>

                                <div class="bg-gray-100 dark:bg-white/5 rounded-xl p-4 border border-gray-200 dark:border-white/10">
                                    <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Rate limit errors</h4>
                                    <ul class="doc-list text-sm">
                                        <li>Both APIs have rate limits. If you're hitting them, wait a few minutes and try again.</li>
                                        <li>For Gemini, the free tier has generous limits. Check <a href="https://ai.google.dev/pricing" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">Google's pricing page</a> for details.</li>
                                        <li>For OpenAI, rate limits depend on your account tier. Check <a href="https://platform.openai.com/account/limits" target="_blank" rel="noopener noreferrer" class="text-blue-400 hover:text-blue-300">your account limits</a> for details.</li>
                                    </ul>
                                </div>
                            </div>
                        </section>

                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
