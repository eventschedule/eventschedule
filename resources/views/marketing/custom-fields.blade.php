<x-marketing-layout>
    <x-slot name="title">Custom Fields | Event Metadata &amp; Attendee Forms - Event Schedule</x-slot>
    <x-slot name="description">Define custom event metadata fields and collect attendee information with flexible form fields including text, dropdown, date, and yes/no options.</x-slot>
    <x-slot name="keywords">custom fields, event metadata, ticket forms, event registration, attendee information, form builder, dropdown fields, date picker, event data collection</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    <style>
        /* Custom amber gradient for this page */
        .text-gradient {
            background: linear-gradient(135deg, #f59e0b 0%, #d97706 50%, #b45309 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }
    </style>

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-32 overflow-hidden">
        <!-- Animated background -->
        <div class="absolute inset-0">
            <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-amber-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-orange-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <!-- Grid -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                </svg>
                <span class="text-sm text-gray-300">Flexible data collection</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-white mb-8 leading-tight">
                Custom<br>
                <span class="text-gradient">Fields</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-400 max-w-3xl mx-auto mb-12">
                Collect the information you need from ticket buyers with flexible form fields.
            </p>

            <div class="flex flex-wrap justify-center gap-4">
                <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-amber-600 to-orange-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-amber-500/25">
                    Start for free
                    <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- Bento Grid Features -->
    <section class="bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">

                <!-- Five Field Types (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-900/50 to-orange-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <div class="flex-1">
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h7" />
                                </svg>
                                Field Types
                            </div>
                            <h3 class="text-3xl lg:text-4xl font-bold text-white mb-4">Five flexible field types</h3>
                            <p class="text-gray-400 text-lg mb-6">Choose the right input type for each piece of information you need to collect from attendees.</p>
                            <div class="flex flex-wrap gap-3">
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Text</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Multiline</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Yes/No</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Date</span>
                                <span class="px-3 py-1 rounded-full bg-white/10 text-gray-300 text-sm">Dropdown</span>
                            </div>
                        </div>
                        <div class="flex-shrink-0 w-full lg:w-auto">
                            <div class="animate-float">
                                <div class="bg-black/30 rounded-2xl p-5 border border-white/10 space-y-4 max-w-xs">
                                    <!-- Text field -->
                                    <div>
                                        <label class="text-gray-400 text-xs mb-1 block">Company Name</label>
                                        <div class="bg-white/10 rounded-lg px-3 py-2 text-white text-sm border border-white/10">Acme Corp</div>
                                    </div>
                                    <!-- Dropdown field -->
                                    <div>
                                        <label class="text-gray-400 text-xs mb-1 block">T-Shirt Size</label>
                                        <div class="bg-white/10 rounded-lg px-3 py-2 text-white text-sm border border-white/10 flex items-center justify-between">
                                            <span>Large</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <!-- Yes/No field -->
                                    <div>
                                        <label class="text-gray-400 text-xs mb-1 block">Vegetarian Meal?</label>
                                        <div class="bg-white/10 rounded-lg px-3 py-2 text-white text-sm border border-white/10 flex items-center justify-between">
                                            <span>Yes</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" /></svg>
                                        </div>
                                    </div>
                                    <!-- Date field -->
                                    <div>
                                        <label class="text-gray-400 text-xs mb-1 block">Date of Birth</label>
                                        <div class="bg-white/10 rounded-lg px-3 py-2 text-white text-sm border border-white/10 flex items-center justify-between">
                                            <span>1990-05-15</span>
                                            <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Per-Order Fields -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-900/50 to-red-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-orange-500/20 text-orange-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                        </svg>
                        Per-Order
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Event-level fields</h3>
                    <p class="text-gray-400 mb-6">Collect information once per order. Great for details that apply to the entire purchase.</p>

                    <div class="space-y-3">
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 border border-white/10">
                            <div class="w-8 h-8 rounded-lg bg-orange-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" /></svg>
                            </div>
                            <span class="text-white text-sm">Company Name</span>
                        </div>
                        <div class="flex items-center gap-3 p-3 rounded-xl bg-white/10 border border-white/10">
                            <div class="w-8 h-8 rounded-lg bg-orange-500/20 flex items-center justify-center">
                                <svg class="w-4 h-4 text-orange-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" /></svg>
                            </div>
                            <span class="text-white text-sm">Contact Phone</span>
                        </div>
                    </div>
                </div>

                <!-- Per-Ticket Fields -->
                <div class="bento-card relative overflow-hidden rounded-3xl bg-gradient-to-br from-yellow-900/50 to-amber-900/50 border border-white/10 p-8">
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-yellow-500/20 text-yellow-300 text-sm font-medium mb-4">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 5v2m0 4v2m0 4v2M5 5a2 2 0 00-2 2v3a2 2 0 110 4v3a2 2 0 002 2h14a2 2 0 002-2v-3a2 2 0 110-4V7a2 2 0 00-2-2H5z" />
                        </svg>
                        Per-Ticket
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3">Ticket-specific fields</h3>
                    <p class="text-gray-400 mb-6">Collect info for each ticket type. Only shown when that specific ticket is selected.</p>

                    <div class="space-y-3">
                        <div class="p-3 rounded-xl bg-yellow-500/10 border border-yellow-500/20">
                            <div class="text-yellow-300 text-xs font-medium mb-2">VIP Ticket</div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z" /></svg>
                                <span class="text-white text-sm">Seating Preference</span>
                            </div>
                        </div>
                        <div class="p-3 rounded-xl bg-white/10 border border-white/10">
                            <div class="text-gray-400 text-xs font-medium mb-2">Workshop Ticket</div>
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" /></svg>
                                <span class="text-white text-sm">Experience Level</span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Required or Optional (spans 2 cols) -->
                <div class="bento-card lg:col-span-2 relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-900/50 to-yellow-900/50 border border-white/10 p-8 lg:p-10">
                    <div class="grid md:grid-cols-2 gap-8 items-center">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-amber-500/20 text-amber-300 text-sm font-medium mb-4">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                Validation
                            </div>
                            <h3 class="text-3xl font-bold text-white mb-4">Required or optional</h3>
                            <p class="text-gray-400 text-lg">Mark fields as required to ensure you get the information you need, or leave them optional for flexibility. Up to 8 fields per level.</p>
                        </div>
                        <div class="bg-black/30 rounded-2xl p-6 border border-white/10">
                            <div class="space-y-4">
                                <div class="flex items-center justify-between p-3 rounded-xl bg-white/10 border border-white/10">
                                    <div class="flex items-center gap-3">
                                        <div class="w-5 h-5 rounded bg-amber-500 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <span class="text-white">Dietary Requirements</span>
                                    </div>
                                    <span class="text-amber-400 text-xs font-medium">Required</span>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-white/10 border border-white/10">
                                    <div class="flex items-center gap-3">
                                        <div class="w-5 h-5 rounded bg-amber-500 flex items-center justify-center">
                                            <svg class="w-3 h-3 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7" /></svg>
                                        </div>
                                        <span class="text-white">Emergency Contact</span>
                                    </div>
                                    <span class="text-amber-400 text-xs font-medium">Required</span>
                                </div>
                                <div class="flex items-center justify-between p-3 rounded-xl bg-white/5 border border-white/5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-5 h-5 rounded border border-white/30"></div>
                                        <span class="text-gray-400">How did you hear about us?</span>
                                    </div>
                                    <span class="text-gray-500 text-xs">Optional</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Event Metadata Fields Section -->
    <section class="bg-gradient-to-b from-[#0a0a0f] to-gray-900 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                    <svg class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.568 3H5.25A2.25 2.25 0 003 5.25v4.318c0 .597.237 1.17.659 1.591l9.581 9.581c.699.699 1.78.872 2.607.33a18.095 18.095 0 005.223-5.223c.542-.827.369-1.908-.33-2.607L11.16 3.66A2.25 2.25 0 009.568 3z" />
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 6h.008v.008H6V6z" />
                    </svg>
                    <span class="text-sm text-gray-300">Event Metadata</span>
                </div>
                <h2 class="text-3xl md:text-4xl font-bold text-white mb-4">
                    Event Custom Fields
                </h2>
                <p class="text-xl text-gray-400 max-w-3xl mx-auto">
                    Define custom metadata fields at the schedule level that appear when creating or editing events. Perfect for tracking speaker names, room numbers, session types, and more.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                <!-- How It Works -->
                <div class="bg-gradient-to-br from-amber-900/30 to-orange-900/30 rounded-3xl border border-white/10 p-8">
                    <h3 class="text-2xl font-bold text-white mb-6">How it works</h3>
                    <div class="space-y-6">
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 font-bold">1</div>
                            <div>
                                <h4 class="text-white font-medium mb-1">Define fields in schedule settings</h4>
                                <p class="text-gray-400 text-sm">Add up to 8 custom fields with names, types, and validation rules.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 font-bold">2</div>
                            <div>
                                <h4 class="text-white font-medium mb-1">Fill values when creating events</h4>
                                <p class="text-gray-400 text-sm">Fields appear automatically in the event edit form for your team to complete.</p>
                            </div>
                        </div>
                        <div class="flex gap-4">
                            <div class="flex-shrink-0 w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center text-amber-400 font-bold">3</div>
                            <div>
                                <h4 class="text-white font-medium mb-1">Use in graphics &amp; exports</h4>
                                <p class="text-gray-400 text-sm">Display values in event graphics using template variables like {custom_1}, {custom_2}, etc.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Use Cases -->
                <div class="bg-gradient-to-br from-orange-900/30 to-red-900/30 rounded-3xl border border-white/10 p-8">
                    <h3 class="text-2xl font-bold text-white mb-6">Common uses</h3>
                    <div class="grid grid-cols-2 gap-4">
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                            <div class="text-amber-400 text-sm font-medium mb-1">Conferences</div>
                            <p class="text-gray-400 text-xs">Speaker name, Topic, Session type</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                            <div class="text-amber-400 text-sm font-medium mb-1">Venues</div>
                            <p class="text-gray-400 text-xs">Room number, Capacity, A/V setup</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                            <div class="text-amber-400 text-sm font-medium mb-1">Festivals</div>
                            <p class="text-gray-400 text-xs">Stage, Genre, Age restriction</p>
                        </div>
                        <div class="p-4 rounded-xl bg-white/5 border border-white/10">
                            <div class="text-amber-400 text-sm font-medium mb-1">Workshops</div>
                            <p class="text-gray-400 text-xs">Skill level, Materials, Instructor</p>
                        </div>
                    </div>
                    <div class="mt-6 p-4 rounded-xl bg-amber-500/10 border border-amber-500/20">
                        <div class="flex items-start gap-3">
                            <svg class="w-5 h-5 text-amber-400 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            <div>
                                <div class="text-amber-300 text-sm font-medium mb-1">AI-Powered Import</div>
                                <p class="text-gray-400 text-xs">When importing events, AI automatically extracts custom field values from text and images.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Ticket Custom Fields Use Cases Section -->
    <section class="bg-gray-50 py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 mb-4">
                    Ticket custom fields use cases
                </h2>
                <p class="text-xl text-gray-500">
                    Collect the right information from attendees during checkout.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Dietary Restrictions -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Dietary Restrictions</h3>
                    <p class="text-gray-600 text-sm">
                        Ask about allergies and dietary preferences for catered events, workshops, and conferences.
                    </p>
                </div>

                <!-- T-Shirt Sizes -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">T-Shirt Sizes</h3>
                    <p class="text-gray-600 text-sm">
                        Collect clothing sizes for conferences, charity runs, or any event with swag.
                    </p>
                </div>

                <!-- Age Verification -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Age Verification</h3>
                    <p class="text-gray-600 text-sm">
                        Use date fields to collect birth dates for age-restricted events and venues.
                    </p>
                </div>

                <!-- Special Accommodations -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Accessibility Needs</h3>
                    <p class="text-gray-600 text-sm">
                        Allow attendees to request wheelchair access, sign language interpreters, or other accommodations.
                    </p>
                </div>

                <!-- Company Info -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">B2B Information</h3>
                    <p class="text-gray-600 text-sm">
                        Collect company names, job titles, and industry for professional conferences and networking events.
                    </p>
                </div>

                <!-- Emergency Contact -->
                <div class="bg-white rounded-2xl p-6 border border-gray-200 shadow-sm">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-500 to-orange-500 text-white rounded-xl flex items-center justify-center mb-4 shadow-lg shadow-amber-500/25">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                        </svg>
                    </div>
                    <h3 class="text-lg font-semibold text-gray-900 mb-2">Emergency Contacts</h3>
                    <p class="text-gray-600 text-sm">
                        Gather emergency contact information for outdoor adventures, sports events, and multi-day retreats.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Next Feature -->
    <section class="relative bg-[#0a0a0f] py-20 overflow-hidden">
        <!-- Animated background blobs matching Team Scheduling page's colors -->
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[300px] h-[300px] bg-cyan-600/20 rounded-full blur-[100px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[200px] h-[200px] bg-teal-600/20 rounded-full blur-[100px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <a href="{{ marketing_url('/team-scheduling') }}" class="group block">
                <div class="bg-gradient-to-br from-cyan-900/50 to-teal-900/50 rounded-3xl border border-white/10 p-8 lg:p-10 hover:scale-[1.02] transition-all duration-300">
                    <div class="flex flex-col lg:flex-row gap-8 items-center">
                        <!-- Text content -->
                        <div class="flex-1 text-center lg:text-left">
                            <h3 class="text-2xl lg:text-3xl font-bold text-white mb-3 group-hover:text-cyan-300 transition-colors">Team Scheduling</h3>
                            <p class="text-gray-400 text-lg mb-4">Invite team members via email, assign roles, and collaborate on events together.</p>
                            <span class="inline-flex items-center text-cyan-400 font-medium group-hover:gap-3 gap-2 transition-all">
                                Learn more
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                                </svg>
                            </span>
                        </div>

                        <!-- Mini mockup: Team member avatars with role badges -->
                        <div class="flex-shrink-0">
                            <div class="bg-black/30 rounded-xl border border-white/10 p-4 w-48 space-y-2">
                                <!-- Team member 1 -->
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-white/10">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-cyan-500 to-teal-500 flex items-center justify-center text-white text-[10px] font-semibold">JD</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-white text-xs font-medium truncate">John Doe</div>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded bg-cyan-500/20 text-cyan-300 text-[9px]">Owner</span>
                                </div>
                                <!-- Team member 2 -->
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-teal-500 to-emerald-500 flex items-center justify-center text-white text-[10px] font-semibold">AS</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-gray-300 text-xs font-medium truncate">Alice Smith</div>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded bg-teal-500/20 text-teal-300 text-[9px]">Admin</span>
                                </div>
                                <!-- Team member 3 -->
                                <div class="flex items-center gap-2 p-2 rounded-lg bg-white/5">
                                    <div class="w-7 h-7 rounded-full bg-gradient-to-br from-emerald-500 to-green-500 flex items-center justify-center text-white text-[10px] font-semibold">BJ</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="text-gray-300 text-xs font-medium truncate">Bob Jones</div>
                                    </div>
                                    <span class="px-1.5 py-0.5 rounded bg-emerald-500/20 text-emerald-300 text-[9px]">Follower</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="relative bg-gradient-to-br from-amber-600 to-orange-700 py-24 overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.05)_1px,transparent_1px)] bg-[size:32px_32px]"></div>

        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl lg:text-5xl font-bold text-white mb-6">
                Collect the data you need
            </h2>
            <p class="text-xl text-white/80 mb-10 max-w-2xl mx-auto">
                Set up custom fields for your events today. Free to use, no credit card required.
            </p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center justify-center px-8 py-4 text-lg font-semibold text-amber-600 bg-white rounded-2xl hover:scale-105 transition-all shadow-xl">
                Get Started Free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
