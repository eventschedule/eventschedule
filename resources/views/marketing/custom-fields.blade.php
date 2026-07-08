<x-marketing-layout>
    <x-slot name="title">Custom Fields | Event Metadata &amp; Attendee Forms - Event Schedule</x-slot>
    <x-slot name="description">Collect exactly the info you need from ticket buyers. Add custom text, dropdown, date, and yes/no fields to your events and tickets.</x-slot>
    <x-slot name="breadcrumbTitle">Custom Fields</x-slot>

    <x-slot name="structuredData">
    <script type="application/ld+json" {!! nonce_attr() !!}>
    {
        "@context": "https://schema.org",
        "@type": "SoftwareApplication",
        "name": "Event Schedule - Custom Fields",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "description": "Define custom event metadata fields and collect attendee information with flexible form fields including text, dropdown, date, and yes/no options.",
        "featureList": [
            "Custom event metadata fields",
            "Attendee information collection",
            "Text fields",
            "Dropdown menus",
            "Date pickers",
            "Yes/No fields"
        ],
        "offers": {
            "@type": "Offer",
            "price": "0",
            "priceCurrency": "USD"
        },
        "url": "{{ url()->current() }}",
        "provider": {
            "@type": "Organization",
            "name": "Event Schedule"
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
                "name": "What types of custom fields can I create?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "You can create text fields, dropdowns, checkboxes, and date fields. Use them for anything from dietary preferences to t-shirt sizes to special requests."
                }
            },
            {
                "@type": "Question",
                "name": "Where do custom fields appear?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Custom fields appear on the ticket purchase form and reservation form. Attendees fill them out when buying tickets or reserving spots for your events."
                }
            },
            {
                "@type": "Question",
                "name": "Can I export custom field data?",
                "acceptedAnswer": {
                    "@type": "Answer",
                    "text": "Yes. All custom field responses are included when you export your ticket sales or attendee data. You can download everything as a spreadsheet."
                }
            }
        ]
    }
    </script>
    </x-slot>

    {{-- Motion gate: hidden pre-reveal states only apply when this class is present,
         so no-JS visitors, crawlers, and reduced-motion users always see everything. --}}
    <script {!! nonce_attr() !!}>
        if (!window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            document.documentElement.classList.add('es-anim');
        }
    </script>

    <style {!! nonce_attr() !!}>
        /* For custom-fields "The Form" styles. The shared es-* motion system lives in
           marketing.css; this holds the amber glow gradient, the drifting form-builder
           card, and the checkbox-fill motif. */
        .text-gradient-cf {
            background: linear-gradient(135deg, #d97706, #f59e0b, #ea580c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(217, 119, 6, 0.3);
        }
        .dark .text-gradient-cf {
            background: linear-gradient(135deg, #fbbf24, #f59e0b, #fb923c);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            text-shadow: 0 0 40px rgba(251, 191, 36, 0.3);
        }
        @keyframes es-cf-float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        .es-cf-float { animation: es-cf-float 6s ease-in-out infinite; }

        /* Checkbox-fill motif: boxes tick in a wave, like a form being completed. */
        .es-checks { display: flex; align-items: center; }
        .es-check {
            flex: 0 0 auto; width: 12px; height: 12px; border-radius: 3px;
            border: 1.5px solid rgba(217, 119, 6, 0.55);
            animation: es-check-fill var(--ck-dur, 2.6s) ease-in-out infinite;
            animation-delay: var(--ck-delay, 0s);
        }
        @keyframes es-check-fill {
            0%, 100% { background: transparent; box-shadow: none; }
            50% { background: rgba(217, 119, 6, 0.75); box-shadow: 0 0 8px rgba(217, 119, 6, 0.5); }
        }
        @media (prefers-reduced-motion: reduce) {
            .es-cf-float, .es-check { animation: none !important; }
            .es-check { background: rgba(217, 119, 6, 0.35); }
        }
    </style>

    <!-- ============================================================ -->
    <!-- 1. Hero: custom fields                                       -->
    <!-- ============================================================ -->
    <section class="es-hero relative flex min-h-[calc(80svh-4rem)] items-center overflow-hidden bg-white py-16 dark:bg-[#0a0a0f] noise">
        <div class="absolute inset-0" aria-hidden="true">
            <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 25% 70%, rgba(217, 119, 6, 0.3), rgba(217, 119, 6, 0) 65%);"></div>
            <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 32%, rgba(234, 88, 12, 0.28), rgba(234, 88, 12, 0) 65%);"></div>
            <div class="es-aurora es-aurora-3" style="background: radial-gradient(circle at 50% 50%, rgba(251, 191, 36, 0.14), rgba(251, 191, 36, 0) 60%);"></div>
            <div class="es-rays absolute inset-0"></div>
            <div class="grid-pattern absolute inset-0 bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_75%_65%_at_50%_40%,black_25%,transparent_75%)]"></div>
            <!-- Checkbox line along the bottom edge -->
            <div class="es-checks absolute bottom-0 left-0 right-0 mx-auto hidden h-16 max-w-4xl items-center justify-center gap-4 px-8 opacity-50 md:flex" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                @for ($i = 0; $i < 30; $i++)
                    @php $dur = 2 + ($i % 6) * 0.26; $delay = ($i % 12) * 0.16; @endphp
                    <span class="es-check" style="--ck-dur: {{ $dur }}s; --ck-delay: {{ $delay }}s;"></span>
                @endfor
            </div>
        </div>

        <div class="pointer-events-none relative z-10 mx-auto w-full max-w-5xl px-4 text-center sm:px-6 lg:px-8">
            <div class="es-fade-up es-d-1 mb-8 inline-flex items-center gap-3 rounded-full glass px-5 py-2.5">
                <svg aria-hidden="true" class="h-5 w-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm font-medium tracking-wide text-gray-600 dark:text-gray-300">Flexible data collection</span>
            </div>

            <h1 class="es-balance mb-6 text-[2.6rem] font-black leading-[1.05] tracking-tight text-gray-900 dark:text-white sm:text-6xl lg:text-7xl">
                <span class="es-mask"><span class="es-mask-line">Custom</span></span>
                <span class="es-mask es-mask-2"><span class="es-mask-line"><span class="text-gradient-cf">Fields</span></span></span>
            </h1>

            <p class="es-fade-up es-d-2 mx-auto mb-10 max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl">
                Collect the information you need from ticket buyers with flexible form fields.
            </p>

            <div class="es-fade-up es-d-3 flex flex-col items-center justify-center gap-4 sm:flex-row">
                <a href="{{ app_url('/sign_up') }}" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl bg-gradient-to-r from-amber-600 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-lg shadow-amber-500/25 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                    Start Free Trial
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
                <a href="{{ route('marketing.docs.creating_schedules') }}#customize-custom-fields" class="group pointer-events-auto inline-flex items-center justify-center gap-2 rounded-2xl glass px-7 py-4 text-lg font-semibold text-gray-800 transition-all duration-200 hover:-translate-y-0.5 hover:shadow-lg dark:text-white">
                    Read the Custom Fields guide
                    <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                </a>
            </div>
        </div>

    </section>

    <!-- ============================================================ -->
    <!-- 2. Bento features                                           -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="100">

                <!-- Five field types (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="flex flex-col items-center gap-8 lg:flex-row">
                            <div class="flex-1">
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" /></svg>
                                    Field Types
                                </div>
                                <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white lg:text-4xl">Five flexible field types</h2>
                                <p class="mb-6 text-lg text-gray-500 dark:text-gray-400">Choose the right input type for each piece of information you need to collect from attendees.</p>
                                <div class="flex flex-wrap gap-3">
                                    @foreach (['Text', 'Multiline', 'Yes/No', 'Date', 'Dropdown'] as $ft)
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-3 py-1 text-sm text-gray-700 dark:bg-white/10 dark:text-gray-300">{{ $ft }}</span>
                                    @endforeach
                                </div>
                            </div>
                            <div class="w-full shrink-0 lg:w-auto" aria-hidden="true">
                                <div class="animate-float">
                                    <div class="max-w-xs space-y-4 rounded-2xl border border-gray-200 bg-gray-100 p-5 dark:border-white/10 dark:bg-[#0f0f14]">
                                        @foreach ([['Company Name', 'Acme Corp', false], ['T-Shirt Size', 'Large', true], ['Vegetarian Meal?', 'Yes', true], ['Date of Birth', '1990-05-15', true]] as $fi => [$lbl, $val, $chev])
                                            <div class="es-ai-field" style="--i: {{ $fi }};">
                                                <label class="mb-1 block text-xs text-gray-500 dark:text-gray-400">{{ $lbl }}</label>
                                                <div class="flex items-center justify-between rounded-lg border border-gray-200 bg-gray-200 px-3 py-2 text-sm text-gray-900 dark:border-white/10 dark:bg-white/10 dark:text-white">
                                                    <span>{{ $val }}</span>
                                                    @if ($chev)<svg aria-hidden="true" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>@endif
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Event-level fields -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-orange-200 bg-orange-100 px-3 py-1.5 text-sm font-medium text-orange-700 dark:border-orange-800/30 dark:bg-orange-900/40 dark:text-orange-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                            Per-Order
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Event-level fields</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Collect information once per order. Great for details that apply to the entire purchase.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            @foreach ([['Company Name', 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'], ['Contact Phone', 'M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z']] as $oi => [$lbl, $path])
                                <div class="es-ai-field flex items-center gap-3 rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: {{ $oi }};">
                                    <div class="flex h-8 w-8 items-center justify-center rounded-lg bg-orange-500/20"><svg aria-hidden="true" class="h-4 w-4 text-orange-500 dark:text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $path }}" /></svg></div>
                                    <span class="text-sm text-gray-900 dark:text-white">{{ $lbl }}</span>
                                </div>
                            @endforeach
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Ticket-specific fields -->
                <div class="es-bento group relative" data-tilt="5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04]">
                        <div class="mb-5 inline-flex items-center gap-2 self-start rounded-full border border-yellow-200 bg-yellow-100 px-3 py-1.5 text-sm font-medium text-yellow-700 dark:border-yellow-800/30 dark:bg-yellow-900/40 dark:text-yellow-300">
                            <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg>
                            Per-Ticket
                        </div>
                        <h2 class="mb-3 text-2xl font-bold text-gray-900 dark:text-white">Ticket-specific fields</h2>
                        <p class="mb-6 text-gray-500 dark:text-gray-400">Collect info for each ticket type. Only shown when that specific ticket is selected.</p>
                        <div class="mt-auto space-y-3" aria-hidden="true">
                            <div class="es-ai-field rounded-xl border border-yellow-500/20 bg-yellow-500/10 p-3" style="--i: 0;">
                                <div class="mb-2 text-xs font-medium text-yellow-700 dark:text-yellow-300">VIP Ticket</div>
                                <div class="flex items-center gap-2"><svg aria-hidden="true" class="h-4 w-4 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg><span class="text-sm text-gray-900 dark:text-white">Seating Preference</span></div>
                            </div>
                            <div class="es-ai-field rounded-xl border border-gray-200 bg-gray-100 p-3 dark:border-white/10 dark:bg-white/10" style="--i: 1;">
                                <div class="mb-2 text-xs font-medium text-gray-500 dark:text-gray-400">Workshop Ticket</div>
                                <div class="flex items-center gap-2"><svg aria-hidden="true" class="h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg><span class="text-sm text-gray-900 dark:text-white">Experience Level</span></div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

                <!-- Required or optional (2 cols) -->
                <div class="es-bento group relative md:col-span-2" data-tilt="3.5" data-reveal="panel">
                    <div class="es-tilt-inner relative flex h-full flex-col overflow-hidden rounded-3xl border border-gray-200 bg-white p-7 dark:border-white/10 dark:bg-white/[0.04] lg:p-9">
                        <div class="grid items-center gap-8 md:grid-cols-2">
                            <div>
                                <div class="mb-5 inline-flex items-center gap-2 rounded-full border border-amber-200 bg-amber-100 px-3 py-1.5 text-sm font-medium text-amber-700 dark:border-amber-800/30 dark:bg-amber-900/40 dark:text-amber-300">
                                    <svg aria-hidden="true" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                                    Validation
                                </div>
                                <h2 class="mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white">Required or optional</h2>
                                <p class="text-lg text-gray-500 dark:text-gray-400">Mark fields as required to ensure you get the information you need, or leave them optional for flexibility. Up to 10 fields per level.</p>
                            </div>
                            <div class="rounded-2xl border border-gray-200 bg-gray-100 p-6 dark:border-white/10 dark:bg-[#0f0f14]" aria-hidden="true">
                                <div class="space-y-4">
                                    @foreach ([['Dietary Requirements', true], ['Emergency Contact', true], ['How did you hear about us?', false]] as $vi => [$lbl, $req])
                                        <div class="es-ai-field flex items-center justify-between rounded-xl border p-3 {{ $req ? 'border-gray-200 bg-gray-200 dark:border-white/10 dark:bg-white/10' : 'border-gray-100 bg-gray-100 dark:border-white/5 dark:bg-white/5' }}" style="--i: {{ $vi }};">
                                            <div class="flex items-center gap-3">
                                                @if ($req)
                                                    <div class="flex h-5 w-5 items-center justify-center rounded bg-amber-500"><svg aria-hidden="true" class="h-3 w-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg></div>
                                                    <span class="text-gray-900 dark:text-white">{{ $lbl }}</span>
                                                @else
                                                    <div class="h-5 w-5 rounded border border-gray-300 dark:border-white/30"></div>
                                                    <span class="text-gray-500 dark:text-gray-400">{{ $lbl }}</span>
                                                @endif
                                            </div>
                                            <span class="text-xs font-medium {{ $req ? 'text-amber-500 dark:text-amber-400' : 'text-gray-500 dark:text-gray-400' }}">{{ $req ? 'Required' : 'Optional' }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="es-glare" aria-hidden="true"></div>
                        <div class="es-ring-glow" aria-hidden="true"></div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 3. Event custom fields (metadata)                           -->
    <!-- ============================================================ -->
    <section class="bg-gray-50 py-20 dark:bg-[#0f0f14] lg:py-24">
        <div class="mx-auto max-w-7xl px-4 sm:px-6 lg:px-8">
            <div class="mb-14 text-center">
                <div class="mb-6 inline-flex items-center gap-2 rounded-full glass px-4 py-2" data-reveal>
                    <svg aria-hidden="true" class="h-4 w-4 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" /><path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" /></svg>
                    <span class="text-sm text-gray-600 dark:text-gray-300">Event Metadata</span>
                </div>
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Event <span class="text-gradient-cf">custom fields</span></h2>
                <p class="mx-auto max-w-3xl text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.05s;">Define custom metadata fields at the schedule level that appear when creating or editing events. Perfect for tracking speaker names, room numbers, session types, and more.</p>
            </div>

            <div class="grid grid-cols-1 gap-4 lg:grid-cols-2" data-reveal-group="80">
                <div data-reveal class="ap-card rounded-3xl border border-amber-200 bg-amber-50 p-8 dark:border-amber-500/20 dark:bg-amber-900/20">
                    <h3 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">How it works</h3>
                    <div class="space-y-6">
                        @foreach ([['Define fields in schedule settings', 'Add up to 10 custom fields with names, types, and validation rules.'], ['Fill values when creating events', 'Fields appear automatically in the event edit form for your team to complete.'], ['Use in graphics & exports', 'Display values in event graphics using template variables like {custom_1}, {custom_2}, etc.']] as $hi => [$title, $desc])
                            <div class="flex gap-4">
                                <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl bg-amber-500/20 font-bold text-amber-600 dark:text-amber-400">{{ $hi + 1 }}</div>
                                <div><h4 class="mb-1 font-medium text-gray-900 dark:text-white">{{ $title }}</h4><p class="text-sm text-gray-500 dark:text-gray-400">{{ $desc }}</p></div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div data-reveal class="ap-card rounded-3xl border border-orange-200 bg-orange-50 p-8 dark:border-orange-500/20 dark:bg-orange-900/20">
                    <h3 class="mb-6 text-2xl font-bold text-gray-900 dark:text-white">Common uses</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @foreach ([['Conferences', 'Speaker name, Topic, Session type'], ['Venues', 'Room number, Capacity, A/V setup'], ['Festivals', 'Stage, Genre, Age restriction'], ['Workshops', 'Skill level, Materials, Instructor']] as [$cat, $vals])
                            <div class="rounded-xl border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
                                <div class="mb-1 text-sm font-medium text-amber-500 dark:text-amber-400">{{ $cat }}</div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">{{ $vals }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-6 rounded-xl border border-amber-500/20 bg-amber-500/10 p-4">
                        <div class="flex items-start gap-3">
                            <svg aria-hidden="true" class="mt-0.5 h-5 w-5 text-amber-500 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <div>
                                <div class="mb-1 text-sm font-medium text-amber-700 dark:text-amber-300">AI-Powered Import</div>
                                <p class="text-xs text-gray-500 dark:text-gray-400">When importing events, AI automatically extracts custom field values from text and images.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 4. Ticket custom fields use cases (dark band)               -->
    <!-- ============================================================ -->
    @php
        $useCases = [
            ['Dietary Restrictions', 'Ask about allergies and dietary preferences for catered events, workshops, and conferences.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />'],
            ['T-Shirt Sizes', 'Collect clothing sizes for conferences, charity runs, or any event with swag.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />'],
            ['Age Verification', 'Use date fields to collect birth dates for age-restricted events and venues.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />'],
            ['Accessibility Needs', 'Allow attendees to request wheelchair access, sign language interpreters, or other accommodations.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />'],
            ['B2B Information', 'Collect company names, job titles, and industry for professional conferences and networking events.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />'],
            ['Emergency Contacts', 'Gather emergency contact information for outdoor adventures, sports events, and multi-day retreats.', '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />'],
        ];
    @endphp
    <section class="bg-gray-50 px-2 py-14 dark:bg-[#0f0f14] sm:px-4 lg:py-20">
        <div class="es-band-dark noise relative overflow-hidden rounded-[2.5rem] border border-white/[0.06] px-4 py-16 sm:px-6 lg:px-8 lg:py-20 2xl:mx-auto 2xl:max-w-[100rem]">
            <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 30% 25%, rgba(217, 119, 6, 0.24), rgba(217, 119, 6, 0) 60%); opacity: 0.6;"></div>
                <div class="es-aurora es-aurora-2" style="background: radial-gradient(circle at 75% 70%, rgba(234, 88, 12, 0.2), rgba(234, 88, 12, 0) 60%); opacity: 0.55;"></div>
                <div class="grid-overlay absolute inset-0 opacity-25"></div>
                <div class="es-checks absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-4xl items-center justify-center gap-4 px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                    @for ($i = 0; $i < 28; $i++)
                        @php $dur = 2 + ($i % 6) * 0.26; $delay = ($i % 12) * 0.16; @endphp
                        <span class="es-check" style="--ck-dur: {{ $dur }}s; --ck-delay: {{ $delay }}s;"></span>
                    @endfor
                </div>
            </div>

            <div class="relative z-10 mx-auto max-w-6xl">
                <div class="mx-auto mb-14 max-w-2xl text-center">
                    <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-white md:text-5xl" data-reveal>Ticket custom fields <span class="text-gradient-cf">use cases</span></h2>
                    <p class="text-lg text-gray-300 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Collect the right information from attendees during checkout.</p>
                </div>

                <div class="grid grid-cols-1 gap-6 md:grid-cols-2 lg:grid-cols-3" data-reveal-group="70">
                    @foreach ($useCases as [$title, $desc, $icon])
                        <div data-reveal class="rounded-2xl border border-white/10 bg-white/[0.04] p-6 transition-all hover:-translate-y-1 hover:bg-white/[0.07]">
                            <div class="mb-4 flex h-12 w-12 items-center justify-center rounded-xl bg-gradient-to-br from-amber-500 to-orange-500 text-white shadow-lg shadow-amber-500/25">
                                <svg aria-hidden="true" class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">{!! $icon !!}</svg>
                            </div>
                            <h3 class="mb-2 text-lg font-semibold text-white">{{ $title }}</h3>
                            <p class="text-sm text-gray-400">{{ $desc }}</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 5. Next feature                                             -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-5xl px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 gap-4 md:grid-cols-2" data-reveal-group="80">

                <a href="{{ marketing_url('/features/team-scheduling') }}" data-reveal class="group block">
                    <div class="flex h-full flex-col rounded-3xl border border-cyan-200 bg-gradient-to-br from-cyan-100 to-teal-100 p-8 transition-all duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:from-cyan-900 dark:to-teal-900 lg:p-10">
                        <div class="flex flex-1 flex-col items-center gap-8 lg:flex-row">
                            <div class="flex flex-1 flex-col text-center lg:text-left">
                                <h3 class="mb-3 text-2xl font-bold text-gray-900 transition-colors group-hover:text-cyan-600 dark:text-white dark:group-hover:text-cyan-300 lg:text-3xl">Team Scheduling</h3>
                                <p class="mb-4 text-lg text-gray-500 dark:text-white/80">Invite team members via email, assign roles, and collaborate on events together.</p>
                                <span class="mt-auto inline-flex items-center gap-2 font-medium text-cyan-500 transition-all group-hover:gap-3 dark:text-cyan-400">
                                    Learn more
                                    <svg aria-hidden="true" class="h-5 w-5 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" /></svg>
                                </span>
                            </div>
                            <div class="shrink-0" aria-hidden="true">
                                <div class="w-48 space-y-2 rounded-xl border border-gray-200 bg-gray-100 p-4 dark:border-white/10 dark:bg-[#0f0f14]">
                                    @foreach ([['JD', 'John Doe', 'Owner', 'from-cyan-500 to-teal-500', 'bg-cyan-100 text-cyan-700 dark:bg-cyan-500/20 dark:text-cyan-300', true], ['AS', 'Alice Smith', 'Admin', 'from-teal-500 to-emerald-500', 'bg-teal-100 text-teal-700 dark:bg-teal-500/20 dark:text-teal-300', false], ['BJ', 'Bob Jones', 'Follower', 'from-emerald-500 to-green-500', 'bg-emerald-100 text-emerald-700 dark:bg-emerald-500/20 dark:text-emerald-300', false]] as [$in, $nm, $role, $ag, $rb, $active])
                                        <div class="flex items-center gap-2 rounded-lg p-2 {{ $active ? 'bg-gray-200 dark:bg-white/10' : 'bg-gray-100 dark:bg-white/5' }}">
                                            <div class="flex h-7 w-7 items-center justify-center rounded-full bg-gradient-to-br {{ $ag }} text-[10px] font-semibold text-white">{{ $in }}</div>
                                            <div class="min-w-0 flex-1"><div class="truncate text-xs font-medium {{ $active ? 'text-gray-900 dark:text-white' : 'text-gray-600 dark:text-gray-300' }}">{{ $nm }}</div></div>
                                            <span class="inline-flex items-center rounded px-1.5 py-0.5 text-[9px] {{ $rb }}">{{ $role }}</span>
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>
                </a>

                <div data-reveal class="ap-card flex h-full flex-col rounded-3xl border border-gray-200 bg-white p-8 dark:border-white/10 dark:bg-white/5 lg:p-10">
                    <div class="mb-6 inline-flex h-12 w-12 items-center justify-center rounded-2xl border border-sky-500/20 bg-sky-500/10">
                        <svg aria-hidden="true" class="h-6 w-6 text-sky-500 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z" /></svg>
                    </div>
                    <h3 class="mb-4 text-xl font-bold text-gray-900 dark:text-white">Popular with</h3>
                    <div class="space-y-3">
                        @foreach ([['/for-venues', 'Venues'], ['/for-hotels-and-resorts', 'Hotels & Resorts'], ['/for-fitness-and-yoga', 'Fitness & Yoga']] as [$href, $label])
                            <a href="{{ marketing_url($href) }}" class="group/link flex items-center justify-between rounded-xl border border-gray-200 bg-gray-50 p-3 transition-all hover:border-sky-300 hover:bg-gray-100 dark:border-white/10 dark:bg-white/5 dark:hover:border-sky-500/30 dark:hover:bg-white/10">
                                <span class="font-medium text-gray-900 dark:text-white">{{ $label }}</span>
                                <svg aria-hidden="true" class="h-4 w-4 text-gray-400 transition-colors group-hover/link:text-sky-500 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" /></svg>
                            </a>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 6. FAQ                                                      -->
    <!-- ============================================================ -->
    <section class="bg-gray-100 py-20 dark:bg-black/30 lg:py-28">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <div class="mx-auto mb-14 max-w-3xl text-center">
                <h2 class="es-balance mb-4 text-3xl font-black tracking-tight text-gray-900 dark:text-white md:text-5xl" data-reveal>Frequently asked <span class="text-gradient-cf">questions</span></h2>
                <p class="text-lg text-gray-500 dark:text-gray-400 sm:text-xl" data-reveal style="--reveal-delay: 0.1s;">Everything you need to know about custom fields.</p>
            </div>

            <div class="space-y-4" data-reveal-group="80">
                @foreach ([
                    ['What types of custom fields can I create?', 'You can create text fields, dropdowns, checkboxes, and date fields. Use them for anything from dietary preferences to t-shirt sizes to special requests.'],
                    ['Where do custom fields appear?', 'Custom fields appear on the ticket purchase form and reservation form. Attendees fill them out when buying tickets or reserving spots for your events.'],
                    ['Can I export custom field data?', 'Yes. All custom field responses are included when you export your ticket sales or attendee data. You can download everything as a spreadsheet.'],
                ] as [$q, $a])
                    <details name="faq" data-reveal class="group/faq overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm dark:border-white/10 dark:bg-white/[0.04]">
                        <summary class="flex cursor-pointer items-center justify-between p-6">
                            <h3 class="text-lg font-semibold text-gray-900 dark:text-white">{{ $q }}</h3>
                            <svg aria-hidden="true" class="w-5 h-5 shrink-0 text-gray-500 transition-transform duration-300 group-open/faq:rotate-180 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                            </svg>
                        </summary>
                        <p class="faq-answer px-6 pb-6 text-gray-600 dark:text-gray-400">{{ $a }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 7. Related features                                         -->
    <!-- ============================================================ -->
    <section class="bg-white py-20 dark:bg-[#0a0a0f]">
        <div class="mx-auto max-w-3xl px-4 sm:px-6 lg:px-8">
            <h2 class="mb-8 text-center text-2xl font-black tracking-tight text-gray-900 dark:text-white md:text-3xl" data-reveal>Related features</h2>
            <div class="space-y-3" data-reveal-group="70">
                <div data-reveal>
                    <x-feature-link-card name="Ticketing" description="Sell tickets with QR check-in and zero platform fees" :url="marketing_url('/features/ticketing')" icon-color="sky">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-sky-600 dark:text-sky-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Embed Calendar" description="Embed your event schedule on any website" :url="marketing_url('/features/embed-calendar')" icon-color="blue">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
                <div data-reveal>
                    <x-feature-link-card name="Team Scheduling" description="Invite team members to manage your schedule together" :url="marketing_url('/features/team-scheduling')" icon-color="amber">
                        <x-slot:icon><svg aria-hidden="true" class="w-5 h-5 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" /></svg></x-slot:icon>
                    </x-feature-link-card>
                </div>
            </div>
        </div>
    </section>

    <!-- ============================================================ -->
    <!-- 8. Finale                                                   -->
    <!-- ============================================================ -->
    <section id="claim" class="relative scroll-mt-24 bg-white px-2 py-16 dark:bg-[#0a0a0f] sm:px-4 lg:py-24">
        <div class="mx-auto max-w-6xl">
            <div class="es-finale-panel noise relative overflow-hidden rounded-[2.5rem] border border-white/10 px-6 py-16 text-center shadow-2xl shadow-amber-500/20 sm:px-12 lg:py-24" data-confetti data-reveal="panel">
                <div class="pointer-events-none absolute inset-0" aria-hidden="true">
                    <div class="es-aurora es-aurora-1" style="background: radial-gradient(circle at 50% 20%, rgba(217, 119, 6, 0.3), rgba(217, 119, 6, 0) 60%); opacity: 0.7;"></div>
                    <div class="grid-overlay absolute inset-0 opacity-30"></div>
                    <div class="es-checks absolute bottom-0 left-0 right-0 mx-auto flex h-14 max-w-3xl items-center justify-center gap-4 px-8 opacity-30" style="mask-image: linear-gradient(to right, transparent, black 15%, black 85%, transparent);">
                        @for ($i = 0; $i < 24; $i++)
                            @php $dur = 2 + ($i % 6) * 0.26; $delay = ($i % 12) * 0.16; @endphp
                            <span class="es-check" style="--ck-dur: {{ $dur }}s; --ck-delay: {{ $delay }}s;"></span>
                        @endfor
                    </div>
                </div>

                <div class="relative z-10">
                    <h2 class="es-balance mx-auto mb-6 max-w-3xl text-3xl font-black tracking-tight text-white md:text-5xl">
                        Collect the data you <span class="text-gradient-cf">need</span>
                    </h2>
                    <p class="mx-auto mb-10 max-w-2xl text-lg text-gray-300 sm:text-xl">
                        Set up custom fields for your events today. Available on Pro plans.
                    </p>

                    <div class="mx-auto flex max-w-2xl flex-col items-stretch justify-center gap-3 sm:flex-row">
                        <label for="es-claim-input" class="sr-only">Your schedule name</label>
                        <div dir="ltr" class="es-claim flex min-w-0 flex-1 items-center rounded-2xl border border-white/15 bg-white/[0.07] px-5 py-4 backdrop-blur-md transition-all">
                            <input id="es-claim-input" type="text" placeholder="your-schedule" autocomplete="off" spellcheck="false" maxlength="30"
                                class="min-w-0 flex-1 border-0 bg-transparent p-0 text-right font-mono text-sm font-semibold text-white placeholder-gray-500 focus:outline-none focus:ring-0 sm:text-base">
                            <span class="shrink-0 select-none font-mono text-sm text-gray-400 sm:text-base">.eventschedule.com</span>
                        </div>
                        <a href="{{ app_url('/sign_up') }}" class="group relative inline-flex shrink-0 items-center justify-center gap-2 overflow-hidden rounded-2xl bg-gradient-to-r from-amber-600 to-orange-600 px-8 py-4 text-lg font-semibold text-white shadow-xl shadow-amber-500/30 transition-all duration-200 hover:-translate-y-0.5 hover:scale-[1.02] hover:shadow-2xl hover:shadow-amber-500/40">
                            <span class="relative z-10 flex items-center gap-2">
                                Start Free Trial
                                <svg aria-hidden="true" class="h-5 w-5 transition-transform group-hover:translate-x-1 rtl:rotate-180" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                            <span class="absolute inset-0 animate-shimmer" aria-hidden="true"></span>
                        </a>
                    </div>

                    <p class="mt-6 text-sm text-gray-400">Available on Pro plans</p>
                </div>
            </div>
        </div>
    </section>

    <script src="{{ asset('vendor/canvas-confetti/confetti.browser.min.js') }}" {!! nonce_attr() !!} defer></script>
    @vite('resources/js/marketing-home.js')
</x-marketing-layout>
