<x-social-image-layout>
    <div class="relative w-[1200px] h-[630px] bg-[#0a0a0f] overflow-hidden">
        <!-- Background orbs -->
        <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-blue-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-sky-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-blue-600/10 rounded-full blur-[150px]"></div>

        <!-- Grid pattern -->
        <div class="absolute inset-0 grid-pattern"></div>

        <!-- Logo -->
        <img src="{{ asset('images/light_logo.png') }}" alt="" class="absolute top-10 left-12 h-10">

        <!-- Content -->
        <div class="absolute inset-0 flex flex-col items-center justify-center px-16">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <span class="relative flex h-2 w-2">
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-500"></span>
                </span>
                <span class="text-sm text-gray-300">Free White-Label Platform</span>
            </div>

            <!-- Headline -->
            <h1 class="text-6xl font-bold text-white text-center mb-6 leading-tight">
                <span class="block">Your platform, your brand,</span>
                <span class="block text-gradient">zero cost</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-2xl text-gray-400 text-center max-w-2xl">
                Launch your own ticketing SaaS business. Set your own prices, keep 100% of what you charge your customers, and build your brand.
            </p>
        </div>
    </div>
</x-social-image-layout>
