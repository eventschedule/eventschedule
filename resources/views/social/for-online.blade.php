<x-social-image-layout>
    <div class="relative w-[1200px] h-[630px] bg-[#0a0a0f] overflow-hidden">
        <!-- Background orbs -->
        <div class="absolute bottom-0 left-[-10%] w-[60%] h-[60%] bg-emerald-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute top-0 right-[-5%] w-[50%] h-[50%] bg-teal-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[40%] h-[40%] bg-green-600/10 rounded-full blur-[100px]"></div>

        <!-- Grid pattern -->
        <div class="absolute inset-0 grid-pattern"></div>

        <!-- Logo -->
        <img src="{{ asset('images/light_logo.png') }}" alt="" class="absolute top-10 left-12 h-10">

        <!-- Content -->
        <div class="absolute inset-0 flex flex-col items-center justify-center px-16">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" />
                </svg>
                <span class="text-sm text-gray-300">Online Events</span>
            </div>

            <!-- Headline -->
            <h1 class="text-6xl font-bold text-white text-center mb-6 leading-tight">
                <span class="block">Go live,</span>
                <span class="block text-gradient">anywhere</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-2xl text-gray-400 text-center max-w-2xl">
                Schedule and sell online classes with built-in registration, recurring sessions, and email notifications.
            </p>
        </div>
    </div>
</x-social-image-layout>
