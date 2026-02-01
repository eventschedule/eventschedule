<x-marketing-layout>
    <x-slot name="title">Use Cases | Event Schedule for Every Industry</x-slot>
    <x-slot name="description">Discover how Event Schedule works for musicians, venues, comedians, restaurants, art galleries, and more. Find your industry and see how to share events, sell tickets, and grow your audience.</x-slot>
    <x-slot name="keywords">event schedule use cases, musician schedule, venue calendar, comedy club events, restaurant events, brewery events, art gallery schedule, community center events</x-slot>
    <x-slot name="socialImage">social/features.png</x-slot>
    <x-slot name="breadcrumbTitle">Use Cases</x-slot>

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
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-gray-200 dark:border-white/10 mb-8">
                <svg class="w-4 h-4 text-blue-600 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-sm text-gray-600 dark:text-gray-300">Use Cases</span>
            </div>

            <h1 class="text-5xl md:text-6xl lg:text-7xl font-bold text-gray-900 dark:text-white mb-8 leading-tight">
                Built for every<br>
                <span class="text-gradient">stage and venue</span>
            </h1>

            <p class="text-xl md:text-2xl text-gray-600 dark:text-gray-400 max-w-3xl mx-auto mb-12">
                Whether you're a performer sharing gigs or a venue filling seats, Event Schedule is designed for you.
            </p>
        </div>
    </section>

    <!-- For Talent Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ marketing_url('/for-talent') }}" class="group inline-flex items-center gap-2">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">For Talent</h2>
                    <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-12 max-w-3xl">Musicians, DJs, performers, and artists who want to share their upcoming shows and build their audience.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Musicians -->
                <a href="{{ marketing_url('/for-musicians') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Musicians</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Share your gigs, sync calendars, and let fans follow your shows.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Solo Artists</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Rock & Pop Bands</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Jazz Musicians</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Cover Bands</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Tribute Acts</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Session Musicians</span>
                        </div>
                    </div>
                </a>

                <!-- DJs -->
                <a href="{{ marketing_url('/for-djs') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">DJs</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Promote your sets, manage bookings, and grow your following.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Resident DJs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Touring DJs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">B2B Partners</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Underground DJs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Open Format DJs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Producers</span>
                        </div>
                    </div>
                </a>

                <!-- Comedians -->
                <a href="{{ marketing_url('/for-comedians') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-orange-100 dark:from-amber-900 dark:to-orange-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Comedians</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">List your shows, sell tickets, and build your comedy brand.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Stand-Up Comics</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Improv Performers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Sketch Comedy Groups</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Open Mic Regulars</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Touring Headliners</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Comedy Hosts & MCs</span>
                        </div>
                    </div>
                </a>

                <!-- Circus & Acrobatics -->
                <a href="{{ marketing_url('/for-circus-acrobatics') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-100 to-cyan-100 dark:from-rose-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-rose-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Circus & Acrobatics</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Showcase performances and manage tour dates with ease.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Aerialists</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Circus Troupes</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Fire Performers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Contortionists</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Jugglers & Prop Artists</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Stilt Walkers</span>
                        </div>
                    </div>
                </a>

                <!-- Magicians -->
                <a href="{{ marketing_url('/for-magicians') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Magicians</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Book more shows and let audiences find your next performance.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Close-Up Magicians</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Stage Illusionists</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Mentalists</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Children's Entertainers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Corporate Magicians</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Variety Artists</span>
                        </div>
                    </div>
                </a>

                <!-- Spoken Word -->
                <a href="{{ marketing_url('/for-spoken-word') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-teal-100 to-cyan-100 dark:from-teal-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-teal-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Spoken Word</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Share your readings, slams, and open mic nights with your community.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Slam Poets</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Spoken Word Artists</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Page Poets</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Open Mic Hosts</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Literary Curators</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Workshop Leaders</span>
                        </div>
                    </div>
                </a>

                <!-- Dance Groups -->
                <a href="{{ marketing_url('/for-dance-groups') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-rose-100 dark:from-cyan-900 dark:to-rose-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Dance Groups</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Promote performances, classes, and recitals in one place.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Ballet Companies</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Hip-Hop Crews</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Ballroom & Latin</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Contemporary & Modern</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Folk & Cultural</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Dance Fitness</span>
                        </div>
                    </div>
                </a>

                <!-- Theater Performers -->
                <a href="{{ marketing_url('/for-theater-performers') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Theater Performers</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Share your productions and auditions with theater fans.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Musical Theater</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Drama & Straight Plays</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Community Theater</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Improv & Sketch</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Experimental & Fringe</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Children's & Youth</span>
                        </div>
                    </div>
                </a>

                <!-- Food Trucks & Vendors -->
                <a href="{{ marketing_url('/for-food-trucks-and-vendors') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-orange-100 to-red-100 dark:from-orange-900 dark:to-red-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-orange-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Food Trucks & Vendors</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Let customers know where to find you every day of the week.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Food Trucks</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Coffee & Beverage Carts</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">BBQ & Smoker Trucks</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Catering Businesses</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Pop-up Kitchens</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Market Vendors</span>
                        </div>
                    </div>
                </a>

                <!-- Fitness & Yoga Instructors -->
                <a href="{{ marketing_url('/for-fitness-and-yoga') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-100 to-green-100 dark:from-emerald-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-emerald-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Fitness & Yoga Instructors</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Share your class schedule and let students follow your sessions.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Yoga Teachers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Personal Trainers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Pilates Instructors</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">CrossFit Coaches</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Group Fitness</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Meditation Guides</span>
                        </div>
                    </div>
                </a>

                <!-- Workshop Instructors -->
                <a href="{{ marketing_url('/for-workshop-instructors') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Workshop Instructors</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">List your workshops and courses to fill every seat.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Cooking Classes</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Pottery & Ceramics</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Photography</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Craft & Maker</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Art Teachers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Music Lessons</span>
                        </div>
                    </div>
                </a>

                <!-- Visual Artists -->
                <a href="{{ marketing_url('/for-visual-artists') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-cyan-100 dark:from-sky-900 dark:to-cyan-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Visual Artists</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Announce exhibitions, open studios, and art fairs to collectors.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Painters & Illustrators</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Sculptors</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Photographers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Printmakers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Mixed Media</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Digital Artists</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- For Venues Section -->
    <section class="bg-gray-50 dark:bg-[#0d0d14] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 mb-4">
                <a href="{{ marketing_url('/for-venues') }}" class="group inline-flex items-center gap-2">
                    <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors">For Venues</h2>
                    <svg class="w-6 h-6 text-gray-400 group-hover:text-blue-600 dark:group-hover:text-blue-400 transition-colors" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                    </svg>
                </a>
            </div>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-12 max-w-3xl">Bars, clubs, theaters, and event spaces that host regular events and need to keep their calendar updated.</p>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
                <!-- Bars & Pubs -->
                <a href="{{ marketing_url('/for-bars') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-100 to-yellow-100 dark:from-amber-900 dark:to-yellow-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-amber-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Bars & Pubs</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Keep your entertainment calendar fresh and bring in crowds.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Craft Beer Bars</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Wine Bars</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Sports Bars</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Cocktail Lounges</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Irish & British Pubs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Dive Bars</span>
                        </div>
                    </div>
                </a>

                <!-- Nightclubs -->
                <a href="{{ marketing_url('/for-nightclubs') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Nightclubs</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Promote DJ lineups, themed nights, and special events.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Dance Clubs & EDM</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Hip-Hop & Urban</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Latin Clubs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Rooftop Clubs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Underground & Warehouse</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">VIP Lounges</span>
                        </div>
                    </div>
                </a>

                <!-- Music Venues -->
                <a href="{{ marketing_url('/for-music-venues') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-blue-100 to-sky-100 dark:from-blue-900 dark:to-sky-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Music Venues</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Manage concert schedules and sell tickets for every show.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Concert Halls</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Live Music Bars</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Jazz Clubs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Folk & Acoustic</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Rock & Indie Venues</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Outdoor Amphitheaters</span>
                        </div>
                    </div>
                </a>

                <!-- Theaters -->
                <a href="{{ marketing_url('/for-theaters') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-red-100 to-rose-100 dark:from-red-900 dark:to-rose-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-red-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Theaters</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Share your season schedule and sell tickets for every production.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Community Theaters</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Regional Theaters</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Black Box Theaters</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Dinner Theaters</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Children's Theaters</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Outdoor Amphitheaters</span>
                        </div>
                    </div>
                </a>

                <!-- Comedy Clubs -->
                <a href="{{ marketing_url('/for-comedy-clubs') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-yellow-100 to-amber-100 dark:from-yellow-900 dark:to-amber-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-yellow-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Comedy Clubs</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Fill seats with a lineup calendar your audience will love.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Stand-up Clubs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Improv Theaters</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Open Mic Venues</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Comedy Bars</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Sketch Comedy Venues</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Live Podcast Studios</span>
                        </div>
                    </div>
                </a>

                <!-- Restaurants -->
                <a href="{{ marketing_url('/for-restaurants') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-100 to-emerald-100 dark:from-green-900 dark:to-emerald-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-green-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Restaurants</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Promote special dinners, live music nights, and tasting events.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Fine Dining</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Wine Bars & Tapas</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Farm-to-Table</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Supper Clubs</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Casual Dining & Bistros</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Chef's Tables</span>
                        </div>
                    </div>
                </a>

                <!-- Breweries & Wineries -->
                <a href="{{ marketing_url('/for-breweries-and-wineries') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-lime-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Breweries & Wineries</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Share tastings, tap takeovers, live music, and seasonal events.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Craft Breweries</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Brewpubs & Taprooms</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Wineries & Vineyards</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Cideries & Orchards</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Meaderies & Distilleries</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Taproom-Only</span>
                        </div>
                    </div>
                </a>

                <!-- Art Galleries -->
                <a href="{{ marketing_url('/for-art-galleries') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-cyan-100 to-teal-100 dark:from-cyan-900 dark:to-teal-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-cyan-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Art Galleries</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Promote exhibitions, openings, and artist talks to collectors and fans.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Contemporary Art</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Fine Art Studios</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Photography Galleries</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Craft & Maker Studios</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Artist Collectives</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Pop-up Spaces</span>
                        </div>
                    </div>
                </a>

                <!-- Community Centers -->
                <a href="{{ marketing_url('/for-community-centers') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Community Centers</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Keep your community informed about classes, meetings, and events.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Recreation Centers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Senior Centers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Youth Centers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Cultural Centers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Neighborhood Centers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Faith-Based Centers</span>
                        </div>
                    </div>
                </a>

                <!-- Farmers Markets -->
                <a href="{{ marketing_url('/for-farmers-markets') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-lime-100 to-green-100 dark:from-lime-900 dark:to-green-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-lime-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Farmers Markets</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Share your market schedule and build a loyal shopper community.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Weekly Farmers Markets</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Artisan & Craft Markets</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Flea Markets</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Holiday Markets</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Night Markets</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Specialty Food Markets</span>
                        </div>
                    </div>
                </a>

                <!-- Hotels & Resorts -->
                <a href="{{ marketing_url('/for-hotels-and-resorts') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-100 to-amber-100 dark:from-slate-900 dark:to-amber-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-slate-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Hotels & Resorts</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Elevate the guest experience with activity calendars and events.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Boutique Hotels</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Beach Resorts</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Conference Hotels</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Spa & Wellness</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Mountain Lodges</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Casino Hotels</span>
                        </div>
                    </div>
                </a>

                <!-- Libraries -->
                <a href="{{ marketing_url('/for-libraries') }}" class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-sky-100 to-blue-100 dark:from-sky-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 hover:scale-[1.02] transition-all">
                    <div class="absolute inset-0 bg-gradient-to-br from-sky-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                    <div class="relative">
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white mb-2">Libraries</h3>
                        <p class="text-gray-600 dark:text-gray-300 text-sm mb-4">Share programs, author events, and community activities with patrons.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Public Libraries</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">University Libraries</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Community Reading Rooms</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Children's Libraries</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Archive Centers</span>
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Mobile Libraries</span>
                        </div>
                    </div>
                </a>
            </div>
        </div>
    </section>

    <!-- For Curators Section -->
    <section class="bg-white dark:bg-[#0a0a0f] py-24">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center gap-3 mb-4">
                <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white">For Curators</h2>
            </div>
            <p class="text-lg text-gray-600 dark:text-gray-400 mb-12 max-w-3xl">Event promoters, bloggers, and community organizers who aggregate and share events from multiple sources.</p>

            <a href="{{ marketing_url('/for-curators') }}" class="group block relative overflow-hidden rounded-3xl bg-gradient-to-br from-slate-100 to-blue-100 dark:from-slate-900 dark:to-blue-900 border border-gray-200 dark:border-white/10 p-8 lg:p-10 hover:scale-[1.01] transition-all">
                <div class="absolute inset-0 bg-gradient-to-br from-blue-600/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="relative">
                    <h3 class="text-2xl lg:text-3xl font-bold text-gray-900 dark:text-white mb-3">Curate events from across your scene</h3>
                    <p class="text-gray-600 dark:text-gray-300 text-base lg:text-lg mb-6 max-w-3xl">Aggregate events from multiple venues and performers into one shareable schedule. Be the go-to source for what's happening in your community.</p>
                    <div class="flex flex-wrap gap-2 mb-8">
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Event Promoters</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Music Bloggers</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Community Organizers</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Scene Guides</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Local Media</span>
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full bg-gray-200 dark:bg-white/10 text-gray-600 dark:text-gray-300 text-xs">Tourism Boards</span>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                        <div class="rounded-2xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 p-5">
                            <div class="w-9 h-9 rounded-lg bg-blue-500/20 flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
                                </svg>
                            </div>
                            <h4 class="text-gray-900 dark:text-white font-semibold text-sm mb-1">AI Import</h4>
                            <p class="text-gray-400 text-xs">Paste a URL or image, AI extracts event details</p>
                        </div>
                        <div class="rounded-2xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 p-5">
                            <div class="w-9 h-9 rounded-lg bg-blue-500/20 flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z" />
                                </svg>
                            </div>
                            <h4 class="text-gray-900 dark:text-white font-semibold text-sm mb-1">Aggregation</h4>
                            <p class="text-gray-400 text-xs">Pull events from venues, performers, and other curators</p>
                        </div>
                        <div class="rounded-2xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 p-5">
                            <div class="w-9 h-9 rounded-lg bg-blue-500/20 flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                            </div>
                            <h4 class="text-gray-900 dark:text-white font-semibold text-sm mb-1">Approval Workflow</h4>
                            <p class="text-gray-400 text-xs">Review and approve events before publishing</p>
                        </div>
                        <div class="rounded-2xl bg-gray-100 dark:bg-white/5 border border-gray-200 dark:border-white/10 p-5">
                            <div class="w-9 h-9 rounded-lg bg-blue-500/20 flex items-center justify-center mb-3">
                                <svg class="w-5 h-5 text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <h4 class="text-gray-900 dark:text-white font-semibold text-sm mb-1">Build Your Following</h4>
                            <p class="text-gray-400 text-xs">Followers get notified when you add events</p>
                        </div>
                    </div>

                    <span class="inline-flex items-center text-blue-600 dark:text-blue-400 font-semibold group-hover:text-blue-700 dark:group-hover:text-blue-300 transition-colors">
                        Learn more
                        <svg class="ml-2 w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                        </svg>
                    </span>
                </div>
            </a>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="bg-gray-50 dark:bg-[#0d0d14] py-24">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl md:text-4xl font-bold text-gray-900 dark:text-white mb-6">Ready to share your events?</h2>
            <p class="text-xl text-gray-600 dark:text-gray-400 mb-10">Create your free schedule and start reaching your audience today.</p>
            <a href="{{ route('sign_up') }}" class="inline-flex items-center px-8 py-4 text-lg font-semibold text-white bg-gradient-to-r from-blue-600 to-sky-600 rounded-2xl hover:scale-105 transition-all shadow-lg shadow-blue-500/25">
                Get started for free
                <svg class="ml-2 w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
                </svg>
            </a>
        </div>
    </section>
</x-marketing-layout>
