<x-docs-page
    key="selfhost/ai"
    description="Configure Google Gemini or OpenAI for your selfhosted Event Schedule to enable AI event importing, agenda scanning, translations, and image generation."
    lede="Add one API key and every AI feature turns on: event importing, agenda scanning, translation, generated details, flyers and schedule styling."
    article-description="Configure Google Gemini and OpenAI for your selfhosted Event Schedule instance. Enable AI-powered event importing, agenda scanning, automatic translations, AI-generated text on event graphics, and image generation."
>
    <x-slot:toc>
        <x-doc-nav-link href="#overview">Overview</x-doc-nav-link>
        <x-doc-nav-link href="#features">AI Features</x-doc-nav-link>
        <x-doc-nav-link href="#gemini-api-key">Get a Gemini API Key</x-doc-nav-link>
        <x-doc-nav-link href="#openai-api-key">Get an OpenAI API Key</x-doc-nav-link>
        <x-doc-nav-link href="#configuration">Configuration</x-doc-nav-link>
        <x-doc-nav-link href="#troubleshooting">Troubleshooting</x-doc-nav-link>
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
        <p class="text-gray-600 dark:text-gray-300 mb-6">Event Schedule talks to two AI providers: <a href="https://ai.google.dev/" target="_blank" rel="noopener noreferrer" class="doc-link">Google Gemini</a> and <a href="https://platform.openai.com/" target="_blank" rel="noopener noreferrer" class="doc-link">OpenAI</a>. Either key on its own turns on every AI feature, text and images alike, because each request falls back to whichever provider is configured. When both keys are present the defaults split the work: Gemini answers text requests and OpenAI draws images. Nothing else changes if you skip this page, so treat AI as an accelerator rather than a dependency.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Every feature is unlocked</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">A selfhosted install resolves to the Enterprise tier, so the plan gates on agenda scanning, generated details, generated flyers, AI styling, graphic captions and WhatsApp event creation are all satisfied. The plan badges below are there so you can match this page against the hosted docs, not because anything is held back.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">No daily AI caps</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">The per-day request allowances that apply on eventschedule.com are switched off when the app is not hosted, so no schedule is ever told it has reached a daily limit. Your only ceilings are the quota and billing on your own provider account.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Requests leave your server</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Text and images are sent to Google or OpenAI for processing. If your deployment must not call third-party services, leave both keys unset and everything except the AI features keeps working. One caller is easy to miss: when an event or sub-schedule name contains no Latin characters, the app asks the text provider for an English rendering so it can build a readable URL slug, even with translation switched off.</p>
            </div>
        </div>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Optional Setup</div>
            <p>AI features are entirely optional. With no keys configured, the AI buttons are hidden and the import screen shows a <strong class="text-gray-900 dark:text-white">Setup Required: Gemini API Key</strong> panel walking through <strong class="text-gray-900 dark:text-white">Get API Key</strong>, <strong class="text-gray-900 dark:text-white">Add to Environment</strong> and <strong class="text-gray-900 dark:text-white">Restart Application</strong>. Despite the heading, an OpenAI key satisfies it just as well. Nothing else in the app is affected.</p>
        </div>
    </section>

    <!-- AI Features -->
    <section id="features" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.813 15.904L9 18.75l-.813-2.846a4.5 4.5 0 00-3.09-3.09L2.25 12l2.846-.813a4.5 4.5 0 003.09-3.09L9 5.25l.813 2.846a4.5 4.5 0 003.09 3.09L15.75 12l-2.846.813a4.5 4.5 0 00-3.09 3.09zM18.259 8.715L18 9.75l-.259-1.035a3.375 3.375 0 00-2.455-2.456L14.25 6l1.036-.259a3.375 3.375 0 002.455-2.456L18 2.25l.259 1.035a3.375 3.375 0 002.455 2.456L21.75 6l-1.036.259a3.375 3.375 0 00-2.455 2.456zM16.894 20.567L16.5 21.75l-.394-1.183a2.25 2.25 0 00-1.423-1.423L13.5 18.75l1.183-.394a2.25 2.25 0 001.423-1.423l.394-1.183.394 1.183a2.25 2.25 0 001.423 1.423l1.183.394-1.183.394a2.25 2.25 0 00-1.423 1.423z" />
            </svg>
            AI Features
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">These are every place a configured key changes what the admin panel can do. The plan badge records the gate that applies on the hosted service; on a selfhosted install all of them pass.</p>

        <h3 class="doc-subheading">Text and parsing</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event import <x-doc-badge plan="free" /></h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Choose <strong class="text-gray-900 dark:text-white">Import Events</strong> from the schedule's <strong class="text-gray-900 dark:text-white">Actions</strong> menu. Paste an email, a message or a block of flyer text, or drop in a picture of a poster, and the event name, date, time, duration, venue, performers, category and description come back filled in for review before anything is saved. The same parsing sits behind the <strong class="text-gray-900 dark:text-white">Auto-fill</strong> dropzone on the public submit-event form, so guests get it too. See the <a href="{{ route('marketing.docs.ai_import') }}" class="doc-link">AI Import guide</a>.</p>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Agenda scanning <x-doc-badge plan="enterprise" /></h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">In the <strong class="text-gray-900 dark:text-white">Agenda</strong> section of the event form, <strong class="text-gray-900 dark:text-white">Import from Image</strong> and <strong class="text-gray-900 dark:text-white">Import from Text</strong> read a set list, running order or conference agenda and fill in the event's parts. It adds parts to the one event you are editing, it does not create separate events. An optional instructions box steers the parsing and can be saved as the default for the schedule, and the source picture can be kept and shown on the event. On narrow screens the same tool also gets its own <strong class="text-gray-900 dark:text-white">Scan Agenda</strong> entry in the schedule's Actions menu, which pre-picks a recent event that has no agenda yet. See the <a href="{{ route('marketing.docs.scan_agenda') }}" class="doc-link">Scan Agenda guide</a>.</p>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Second-language translation <x-doc-badge plan="free" /></h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Turn on <strong class="text-gray-900 dark:text-white">Offer a second language to visitors</strong> in the schedule's <strong class="text-gray-900 dark:text-white">Details</strong> section and pick a <strong class="text-gray-900 dark:text-white">Translate into</strong> language. A scheduled task that runs every 15 minutes then fills the translated copy of the schedule name, short description, description, address, request terms, banner message and sponsor section title, and of every event and event part, so guests get a button to switch between the language you typed and the translation. Sub-schedule names and custom field names and options are translated as you save instead of waiting for the task. It is one target language per schedule, not a set of languages, and it needs the cron entry to be running.</p>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI Generator for details <x-doc-badge plan="enterprise" /></h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">An <strong class="text-gray-900 dark:text-white">AI Generator</strong> button appears beside the <strong class="text-gray-900 dark:text-white">Details</strong> heading on both the schedule form and the event form. On a schedule it offers Short Description and Description; on an event it offers Category, Flyer Image, Short Description and Description. You tick the fields to fill, can add extra instructions, save those instructions as the default for the schedule, and preview each result before applying it. Fields that already have a value are left unticked so nothing is overwritten by accident.</p>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Text on event graphics <x-doc-badge plan="enterprise" /></h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Shareable <a href="{{ route('marketing.docs.event_graphics') }}" class="doc-link">event graphics</a> build their caption from a template. AI rewriting only happens when you have entered an AI prompt in the graphic settings; with the prompt left empty the caption is the plain template output, key or no key.</p>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Event creation over WhatsApp <x-doc-badge plan="enterprise" /></h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Messages and images sent to your WhatsApp number are parsed into events by the same pipeline as the import screen, and land on the sender's default schedule, or on their only editable schedule if no default is set. This needs Twilio credentials as well as an AI key, and the sender's phone number has to be on their account profile. On a selfhosted install a saved profile phone number counts as verified straight away, with no SMS round trip. See <a href="{{ route('marketing.docs.saas.twilio') }}" class="doc-link">Twilio Integration</a>.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Image generation</h3>
        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Flyer image <x-doc-badge plan="enterprise" /></h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Tick <strong class="text-gray-900 dark:text-white">Flyer Image</strong> in the event's AI Generator and a portrait flyer, 3:4, is drawn from the event details. Style instructions are optional, and the result is previewed before it replaces the current image.</p>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI Style Generator <x-doc-badge plan="enterprise" /></h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">The <strong class="text-gray-900 dark:text-white">AI Generator</strong> button beside the schedule's <strong class="text-gray-900 dark:text-white">Style</strong> heading offers five fields: Profile Image, Header Image, Accent Color, Font and Background Image. The three images come from the image provider; the accent color and font come from the text provider. See <a href="{{ route('marketing.docs.schedule_styling') }}" class="doc-link">Schedule Styling</a>.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Auto Import from URLs and cities <x-doc-badge plan="selfhost" /></h3>
        <p class="text-gray-600 dark:text-gray-300 mb-4">An <strong class="text-gray-900 dark:text-white">Auto Import</strong> section appears in the schedule editor on selfhosted installs only, and it is the one AI feature that runs unattended. It is offered on every schedule type, but every event it creates is attached to the schedule as that event's <em>curator</em>, so it is really a curator tool. A scheduled task visits your sources once a day, so the cron entry has to be running and an AI key has to be set, otherwise the task reports that no key was found and stops without importing anything.</p>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Import URLs</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Add the address of a page that lists events, such as a venue calendar or a tour page. Each run fetches the page, follows the event links it finds, and asks the AI to pull the details out of each one. The crawler identifies itself as <code class="doc-inline-code">Event Schedule Bot</code>, honours the site's <code class="doc-inline-code">robots.txt</code>, refuses addresses that fail the outbound URL safety check, remembers event pages it has already imported so nothing arrives twice, and skips events whose date has passed or whose name or start time it could not read.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Import Cities</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Cities are a filter on the events found at those URLs, not a search of their own. Once one or more cities are listed, an event is only created when the city the AI read off the page matches one of them, and everything else is discarded. Leave the list empty to keep every event a URL yields. Adding a city without adding a URL imports nothing.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Test Import</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Once the schedule has been saved, a <strong class="text-gray-900 dark:text-white">Test Import</strong> button runs the importer against the values currently in the form and shows you its output, taking one event per URL. Use it to confirm a source is readable before you wait for the daily run.</p>
            </div>
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Reviewing what arrives</h4>
                <p class="text-gray-600 dark:text-gray-400 text-sm">Imported events arrive the same way a submitted event does. They only appear on the schedule straight away when it has <strong class="text-gray-900 dark:text-white">Accept requests</strong> on and <strong class="text-gray-900 dark:text-white">Require Approval</strong> off; otherwise they wait on the <strong class="text-gray-900 dark:text-white">Requests</strong> tab for you to approve. See <a href="{{ route('marketing.docs.creating_schedules') }}#auto-import" class="doc-link">Auto Import</a> in the schedule guide.</p>
            </div>
        </div>

        <h3 class="doc-subheading">Which key handles which request</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Request type</th>
                        <th>Default provider</th>
                        <th>Override</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>Event parsing, agenda scanning, translation, generated details, accent color and font, graphic captions</td>
                        <td>Gemini</td>
                        <td><code class="doc-inline-code">AI_TEXT_PROVIDER</code></td>
                    </tr>
                    <tr>
                        <td>Flyer images, profile, header and background images</td>
                        <td>OpenAI</td>
                        <td><code class="doc-inline-code">AI_IMAGE_PROVIDER</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Fallback beats preference</div>
            <p>If the provider named for a request type has no key, the request goes to the other provider instead. Naming OpenAI as your text provider while only <code class="doc-inline-code">GEMINI_API_KEY</code> is set is harmless: Gemini answers, and nothing errors.</p>
        </div>
    </section>

    <!-- Get a Gemini API Key -->
    <section id="gemini-api-key" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            Get a Gemini API Key
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">Gemini is the default provider for every text request, and it can draw images too, so this is the one key to add if you only add one. Follow these steps to get a Gemini API key from Google:</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer" class="doc-link">Google AI Studio</a></li>
            <li>Sign in with your Google account</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Create API Key</strong></li>
            <li>Select or create a Google Cloud project</li>
            <li>Copy the generated API key</li>
        </ol>

        <div class="doc-callout doc-callout-tip">
            <div class="doc-callout-title">Free Tier</div>
            <p>Google Gemini offers a free tier whose rate limits are enough for most selfhosted instances. Check <a href="https://ai.google.dev/pricing" target="_blank" rel="noopener noreferrer" class="doc-link">Google's pricing page</a> for current limits and pricing.</p>
        </div>
    </section>

    <!-- Get an OpenAI API Key -->
    <section id="openai-api-key" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 5.25a3 3 0 013 3m3 0a6 6 0 01-7.029 5.912c-.563-.097-1.159.026-1.563.43L10.5 17.25H8.25v2.25H6v2.25H2.25v-2.818c0-.597.237-1.17.659-1.591l6.499-6.499c.404-.404.527-1 .43-1.563A6 6 0 1121.75 8.25z" />
            </svg>
            Get an OpenAI API Key
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-6">This key is optional. Add it if you want OpenAI to draw the flyers and style images, or to answer text requests instead of Gemini. Follow these steps:</p>

        <ol class="doc-list doc-list-numbered mb-6">
            <li>Go to <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer" class="doc-link">OpenAI Platform</a></li>
            <li>Sign in or create an OpenAI account</li>
            <li>Click <strong class="text-gray-900 dark:text-white">Create new secret key</strong></li>
            <li>Give it a name (for example "Event Schedule") and copy the key</li>
        </ol>

        <div class="doc-callout doc-callout-info">
            <div class="doc-callout-title">Pricing</div>
            <p>OpenAI image generation is a paid API with no free tier, and each image costs a small amount based on size and quality. Check <a href="https://openai.com/api/pricing/" target="_blank" rel="noopener noreferrer" class="doc-link">OpenAI's pricing page</a> for current rates. If you would rather not pay per image, leave this key out and set <code class="doc-inline-code">AI_IMAGE_PROVIDER=gemini</code>.</p>
        </div>
    </section>

    <!-- Configuration -->
    <section id="configuration" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.325.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
            </svg>
            Configuration
        </h2>
        <p class="text-gray-600 dark:text-gray-300 mb-4">Add your API keys to the <code class="doc-inline-code">.env</code> file. One key is enough: <code class="doc-inline-code">GEMINI_API_KEY</code> on its own enables every AI feature, and so does <code class="doc-inline-code">OPENAI_API_KEY</code> on its own. Everything after the first two lines is optional.</p>

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

        <h3 class="doc-subheading">Variable Reference</h3>
        <div class="doc-table-wrap">
            <table class="doc-table">
                <thead>
                    <tr>
                        <th>Variable</th>
                        <th>Description</th>
                        <th>Default</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td><code class="doc-inline-code">GEMINI_API_KEY</code></td>
                        <td>Google Gemini key. On its own it enables every AI feature, images included.</td>
                        <td>Not set</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">OPENAI_API_KEY</code></td>
                        <td>OpenAI key. On its own it also enables every AI feature.</td>
                        <td>Not set</td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">AI_TEXT_PROVIDER</code></td>
                        <td>Who answers text requests: <code class="doc-inline-code">gemini</code> or <code class="doc-inline-code">openai</code>. Falls back to the other provider if the chosen one has no key. Only <code class="doc-inline-code">openai</code> switches the default, so a typo leaves Gemini in charge.</td>
                        <td><code class="doc-inline-code">gemini</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">AI_IMAGE_PROVIDER</code></td>
                        <td>Who draws images: <code class="doc-inline-code">gemini</code> or <code class="doc-inline-code">openai</code>. Same fallback applies. Only <code class="doc-inline-code">gemini</code> switches the default, so a typo leaves OpenAI in charge.</td>
                        <td><code class="doc-inline-code">openai</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GEMINI_TRANSLATION_MODEL</code></td>
                        <td>Gemini model used for the scheduled translation pass.</td>
                        <td><code class="doc-inline-code">{{ config('services.google.gemini_translation_model') }}</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GEMINI_CONTENT_MODEL</code></td>
                        <td>Gemini model used for every other text request: event parsing, agenda scanning, generated details and style values. Event-graphic caption rewriting is the exception: it picks from its own short model list defined in <code class="doc-inline-code">config/services.php</code>, not from this variable.</td>
                        <td><code class="doc-inline-code">{{ config('services.google.gemini_content_model') }}</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">GEMINI_IMAGE_MODEL</code></td>
                        <td>Gemini model used when Gemini draws the images.</td>
                        <td><code class="doc-inline-code">{{ config('services.google.gemini_image_model') }}</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">OPENAI_TRANSLATION_MODEL</code></td>
                        <td>OpenAI model used for the scheduled translation pass.</td>
                        <td><code class="doc-inline-code">{{ config('services.openai.translation_model') }}</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">OPENAI_CONTENT_MODEL</code></td>
                        <td>OpenAI model used for every other text request.</td>
                        <td><code class="doc-inline-code">{{ config('services.openai.content_model') }}</code></td>
                    </tr>
                    <tr>
                        <td><code class="doc-inline-code">OPENAI_IMAGE_MODEL</code></td>
                        <td>OpenAI model used when OpenAI draws the images.</td>
                        <td><code class="doc-inline-code">{{ config('services.openai.image_model') }}</code></td>
                    </tr>
                </tbody>
            </table>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mb-4 mt-6">After adding the keys, clear the config cache:</p>

        <div class="doc-code-block">
            <div class="doc-code-header">
                <span>bash</span>
                <button class="doc-copy-btn">Copy</button>
            </div>
            <pre><code>php artisan config:clear</code></pre>
        </div>

        <p class="text-gray-600 dark:text-gray-300 mt-6">That is it. The AI buttons appear throughout the admin panel as soon as a key is readable. The only AI feature that still needs switching on per schedule is second-language translation. The model variables are split into translation and content on purpose, so you can point batch translation at a cheap fast model and keep a more capable one for parsing and writing. If your deployment runs <code class="doc-inline-code">php artisan config:cache</code>, run it again after editing <code class="doc-inline-code">.env</code> or the old cached values will keep being served.</p>
    </section>

    <!-- Troubleshooting -->
    <section id="troubleshooting" class="doc-section">
        <h2 class="doc-heading">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-5 h-5 text-gray-400 dark:text-gray-500 flex-shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.75 6.75a4.5 4.5 0 01-4.884 4.484c-1.076-.091-2.264.071-2.95.904l-7.152 8.684a2.548 2.548 0 11-3.586-3.586l8.684-7.152c.833-.686.995-1.874.904-2.95a4.5 4.5 0 016.336-4.486l-3.276 3.276a3.004 3.004 0 002.25 2.25l3.276-3.276c.256.565.398 1.192.398 1.852z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M4.867 19.125h.008v.008h-.008v-.008z" />
            </svg>
            Troubleshooting
        </h2>

        <div class="doc-fields">
            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Setup Required: Gemini API Key" on the import screen</h4>
                <ul class="doc-list text-sm">
                    <li>This panel shows when neither key is readable, so check that <code class="doc-inline-code">GEMINI_API_KEY</code> or <code class="doc-inline-code">OPENAI_API_KEY</code> is set in <code class="doc-inline-code">.env</code></li>
                    <li>Run <code class="doc-inline-code">php artisan config:clear</code>, and <code class="doc-inline-code">php artisan config:cache</code> again if you cache your config</li>
                    <li>Confirm the web server user can read the <code class="doc-inline-code">.env</code> file</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">"Add GEMINI_API_KEY= or OPENAI_API_KEY= to the .env file to enable AI image generation"</h4>
                <ul class="doc-list text-sm">
                    <li>This is the reply from a flyer or style-image request when neither key is present</li>
                    <li>Either key satisfies it, so a Gemini-only install can still generate images by setting <code class="doc-inline-code">AI_IMAGE_PROVIDER=gemini</code></li>
                    <li>Run <code class="doc-inline-code">php artisan config:clear</code> after editing <code class="doc-inline-code">.env</code></li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI buttons are missing entirely</h4>
                <ul class="doc-list text-sm">
                    <li>The AI Generator and agenda import buttons are rendered only when a key is configured, so an unreadable key hides them rather than showing an error</li>
                    <li>Reload the schedule or event form after clearing the config cache</li>
                    <li>The two agenda import buttons sit at the foot of the <strong class="text-gray-900 dark:text-white">Agenda</strong> section of the event form, next to <strong class="text-gray-900 dark:text-white">Add Part</strong>, rather than in its heading</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI text requests failing or timing out</h4>
                <ul class="doc-list text-sm">
                    <li>Check that your server can make outbound HTTPS connections to <code class="doc-inline-code">generativelanguage.googleapis.com</code>, or to <code class="doc-inline-code">api.openai.com</code> if OpenAI is answering text</li>
                    <li>Verify your API key is valid and not expired at <a href="https://aistudio.google.com/apikey" target="_blank" rel="noopener noreferrer" class="doc-link">Google AI Studio</a></li>
                    <li>Check <code class="doc-inline-code">storage/logs/laravel.log</code> for specific error messages</li>
                    <li>Parsing a large image is the slowest request the app makes, so a low PHP <code class="doc-inline-code">max_execution_time</code> or proxy read timeout can cut it off</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">AI image generation failing</h4>
                <ul class="doc-list text-sm">
                    <li>Check that your server can make outbound HTTPS connections to <code class="doc-inline-code">api.openai.com</code></li>
                    <li>Verify your OpenAI API key is valid at <a href="https://platform.openai.com/api-keys" target="_blank" rel="noopener noreferrer" class="doc-link">OpenAI Platform</a></li>
                    <li>Ensure your OpenAI account has available credits</li>
                    <li>Some prompts may be rejected by OpenAI's content policy, so try adjusting your style instructions</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Translations never appear</h4>
                <ul class="doc-list text-sm">
                    <li>Translation runs on the scheduler, not on save, so confirm the cron entry for <code class="doc-inline-code">php artisan schedule:run</code> is installed and allow up to an hour</li>
                    <li>Nothing is translated unless <strong class="text-gray-900 dark:text-white">Offer a second language to visitors</strong> is on and <strong class="text-gray-900 dark:text-white">Translate into</strong> differs from the schedule's own language</li>
                    <li>Only empty translation fields are filled, so text you typed yourself is never overwritten</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Auto Import brings in nothing</h4>
                <ul class="doc-list text-sm">
                    <li>Use <strong class="text-gray-900 dark:text-white">Test Import</strong> first: it prints what the importer saw, which separates a source problem from a scheduling one. The button only appears once the schedule has been saved</li>
                    <li>The task runs once a day off the scheduler, so confirm the cron entry for <code class="doc-inline-code">php artisan schedule:run</code> is installed</li>
                    <li>A source whose <code class="doc-inline-code">robots.txt</code> disallows crawling is skipped, and so is any address that fails the outbound URL safety check</li>
                    <li>Cities filter rather than search, so a city that does not match the page's own wording rejects every event</li>
                    <li>Past-dated events, event pages already imported once, and pages with no readable name or start time are all skipped</li>
                    <li>Imported events you cannot see on the schedule are usually on the <strong class="text-gray-900 dark:text-white">Requests</strong> tab waiting for approval</li>
                </ul>
            </div>

            <div class="doc-field">
                <h4 class="font-semibold text-gray-900 dark:text-white mb-2">Rate limit errors</h4>
                <ul class="doc-list text-sm">
                    <li>Both APIs have rate limits. If you are hitting them, wait a few minutes and try again.</li>
                    <li>For Gemini, the free tier limits are published on <a href="https://ai.google.dev/pricing" target="_blank" rel="noopener noreferrer" class="doc-link">Google's pricing page</a>.</li>
                    <li>For OpenAI, rate limits depend on your account tier. Check <a href="https://platform.openai.com/account/limits" target="_blank" rel="noopener noreferrer" class="doc-link">your account limits</a> for details.</li>
                    <li>These come from the provider, not from Event Schedule: a selfhosted install applies no daily AI allowance of its own.</li>
                </ul>
            </div>
        </div>
    </section>
</x-docs-page>
