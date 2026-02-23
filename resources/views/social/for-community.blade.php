<x-social-image-layout>
    <x-slot name="headStyles">
        <style>
            .text-gradient {
                background: linear-gradient(135deg, #f59e0b 0%, #f97316 50%, #ef4444 100%) !important;
                -webkit-background-clip: text !important;
                -webkit-text-fill-color: transparent !important;
                background-clip: text !important;
            }
        </style>
    </x-slot>

    <div class="relative w-[1200px] h-[630px] bg-[#0a0a0f] overflow-hidden">
        <!-- Background orbs -->
        <div class="absolute top-20 left-1/4 w-[500px] h-[500px] bg-amber-600/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-20 right-1/4 w-[400px] h-[400px] bg-orange-600/20 rounded-full blur-[120px]"></div>

        <!-- Grid pattern -->
        <div class="absolute inset-0 grid-pattern"></div>

        <!-- Logo -->
        <img src="{{ asset('images/light_logo.png') }}" alt="" class="absolute top-10 left-12 h-10">

        <!-- Content -->
        <div class="absolute inset-0 flex flex-col items-center justify-center px-16">
            <!-- Badge -->
            <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full glass border border-white/10 mb-8">
                <svg aria-hidden="true" class="w-4 h-4 text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10" />
                </svg>
                <span class="text-sm text-gray-300">For Curators & Communities</span>
            </div>

            <!-- Headline -->
            <h1 class="text-6xl font-bold text-white text-center mb-6 leading-tight">
                <span class="block">Curate local events</span>
                <span class="block text-gradient">for your community</span>
            </h1>

            <!-- Subtitle -->
            <p class="text-2xl text-gray-400 text-center max-w-2xl">
                Aggregate events from everywhere. Curate what's happening in your city or niche.
            </p>
        </div>
    </div>
</x-social-image-layout>
