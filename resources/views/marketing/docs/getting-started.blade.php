<x-marketing-layout>
    <x-slot name="title">Getting Started - Event Schedule</x-slot>
    <x-slot name="description">Get started with Event Schedule. Learn how to create your account, set up your first schedule, and start sharing events.</x-slot>
    <x-slot name="keywords">getting started, event schedule tutorial, create schedule, event calendar setup, beginner guide</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>

    @include('marketing.docs.partials.styles')

    <!-- Hero Section -->
    <section class="relative bg-[#0a0a0f] py-16 overflow-hidden border-b border-white/5">
        <div class="absolute inset-0">
            <div class="absolute top-10 left-1/4 w-[400px] h-[400px] bg-cyan-600/20 rounded-full blur-[120px] animate-pulse-slow"></div>
            <div class="absolute bottom-10 right-1/4 w-[300px] h-[300px] bg-blue-600/20 rounded-full blur-[120px] animate-pulse-slow" style="animation-delay: 1.5s;"></div>
        </div>
        <div class="absolute inset-0 bg-[linear-gradient(rgba(255,255,255,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(255,255,255,0.03)_1px,transparent_1px)] bg-[size:50px_50px]"></div>

        <div class="relative z-10 max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <x-docs-breadcrumb currentTitle="Getting Started" />

            <div class="flex items-center gap-4 mb-4">
                <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-cyan-500/20">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                    </svg>
                </div>
                <h1 class="text-3xl md:text-4xl font-bold text-white">Getting Started</h1>
            </div>
            <p class="text-lg text-gray-400 max-w-3xl">
                Create your account and set up your first schedule in just a few minutes. This guide walks you through the basics.
            </p>
        </div>
    </section>

    <!-- Main Content -->
    <section class="bg-[#0a0a0f] py-12">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col lg:flex-row gap-10">
                <!-- Sidebar Navigation -->
                <aside class="lg:w-64 flex-shrink-0">
                    <nav class="lg:sticky lg:top-8 space-y-1">
                        <div class="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-3">On this page</div>
                        <a href="#create-account" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Create Your Account</a>
                        <a href="#create-schedule" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Create Your Schedule</a>
                        <a href="#schedule-types" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Schedule Types</a>
                        <a href="#customize" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Customize Your Schedule</a>
                        <a href="#next-steps" class="doc-nav-link block px-3 py-2 text-sm text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition-colors">Next Steps</a>
                    </nav>
                </aside>

                <!-- Content -->
                <div class="flex-1 min-w-0">
                    <div class="prose-dark">
                        <!-- Create Account -->
                        <section id="create-account" class="doc-section">
                            <h2 class="doc-heading">Create Your Account</h2>
                            <p class="text-gray-300 mb-6">Getting started with Event Schedule is quick and free. You can create an account using your email or sign in with Google or Facebook.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>Visit <a href="{{ route('sign_up') }}" class="text-cyan-400 hover:text-cyan-300">the registration page</a></li>
                                <li>Enter your name, email, and create a password (or use Google/Facebook)</li>
                                <li>Verify your email address by clicking the link we send you</li>
                                <li>You're ready to create your first schedule</li>
                            </ol>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Tip</div>
                                <p>Using Google or Facebook login is the fastest way to get started - no email verification required.</p>
                            </div>
                        </section>

                        <!-- Create Schedule -->
                        <section id="create-schedule" class="doc-section">
                            <h2 class="doc-heading">Create Your Schedule</h2>
                            <p class="text-gray-300 mb-6">A schedule is your event calendar - it's where all your events live. Each schedule gets its own unique URL that you can share with your audience.</p>

                            <ol class="doc-list doc-list-numbered mb-6">
                                <li>After logging in, click <strong class="text-white">"New Schedule"</strong> from your dashboard</li>
                                <li>Choose a schedule type (see below)</li>
                                <li>Enter your schedule name and pick a unique URL</li>
                                <li>Add optional details like location, description, and logo</li>
                                <li>Click <strong class="text-white">"Create"</strong> to finish</li>
                            </ol>

                            <div class="doc-callout doc-callout-info">
                                <div class="doc-callout-title">Your Schedule URL</div>
                                <p>Your schedule URL is how people find you. Choose something memorable and relevant to your brand. For example: <code class="doc-inline-code">{{ config('app.url') }}/your-schedule-name</code></p>
                            </div>
                        </section>

                        <!-- Schedule Types -->
                        <section id="schedule-types" class="doc-section">
                            <h2 class="doc-heading">Schedule Types</h2>
                            <p class="text-gray-300 mb-6">Event Schedule supports different types of schedules to match your needs:</p>

                            <div class="overflow-x-auto mb-6">
                                <table class="doc-table">
                                    <thead>
                                        <tr>
                                            <th>Type</th>
                                            <th>Best For</th>
                                            <th>Example</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><span class="font-semibold text-white">Talent</span></td>
                                            <td>Musicians, DJs, performers, speakers</td>
                                            <td>A band listing their upcoming shows</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Venue</span></td>
                                            <td>Bars, clubs, theaters, event spaces</td>
                                            <td>A club listing all their events</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Curator</span></td>
                                            <td>Promoters, bloggers, community organizers</td>
                                            <td>A local music blog listing concerts in the area</td>
                                        </tr>
                                        <tr>
                                            <td><span class="font-semibold text-white">Vendor</span></td>
                                            <td>Food trucks, market vendors, mobile businesses</td>
                                            <td>A food truck listing where they'll be each day</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <p class="text-gray-300">The schedule type affects how your events are displayed and what information is shown. You can change this later in your schedule settings.</p>
                        </section>

                        <!-- Customize -->
                        <section id="customize" class="doc-section">
                            <h2 class="doc-heading">Customize Your Schedule</h2>
                            <p class="text-gray-300 mb-6">Make your schedule your own with customization options:</p>

                            <div class="space-y-4 mb-6">
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Profile Information</h4>
                                    <p class="text-sm text-gray-400">Add your logo, description, website, and social links so visitors know who you are.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Location</h4>
                                    <p class="text-sm text-gray-400">For venues, add your address. This helps visitors find you and enables map integration.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Display Settings</h4>
                                    <p class="text-sm text-gray-400">Choose your timezone, date format, and language preferences.</p>
                                </div>
                                <div class="bg-white/5 rounded-xl p-4 border border-white/10">
                                    <h4 class="font-semibold text-white mb-2">Categories</h4>
                                    <p class="text-sm text-gray-400">Create custom categories to organize your events (e.g., "Live Music", "DJ Nights", "Comedy").</p>
                                </div>
                            </div>

                            <div class="doc-callout doc-callout-tip">
                                <div class="doc-callout-title">Pro Feature</div>
                                <p>Upgrade to Pro to remove Event Schedule branding, use a custom domain, and access advanced features like custom CSS styling.</p>
                            </div>
                        </section>

                        <!-- Next Steps -->
                        <section id="next-steps" class="doc-section">
                            <h2 class="doc-heading">Next Steps</h2>
                            <p class="text-gray-300 mb-6">Now that your schedule is set up, here's what to do next:</p>

                            <ul class="doc-list">
                                <li><a href="{{ route('marketing.docs.creating_events') }}" class="text-cyan-400 hover:text-cyan-300">Add your first events</a> - Learn how to create and import events</li>
                                <li><a href="{{ route('marketing.docs.sharing') }}" class="text-cyan-400 hover:text-cyan-300">Share your schedule</a> - Embed on your website and share on social media</li>
                                <li><a href="{{ route('marketing.docs.tickets') }}" class="text-cyan-400 hover:text-cyan-300">Set up ticketing</a> - Start selling tickets for your events</li>
                                <li><a href="{{ route('marketing.docs.event_graphics') }}" class="text-cyan-400 hover:text-cyan-300">Generate event graphics</a> - Create shareable images for social media</li>
                            </ul>
                        </section>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @include('marketing.docs.partials.scripts')
</x-marketing-layout>
