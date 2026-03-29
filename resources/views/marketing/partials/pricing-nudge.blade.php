<section class="bg-white dark:bg-[#0a0a0f] py-20">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="text-2xl md:text-3xl font-bold text-gray-900 dark:text-white mb-4">
            Free forever. Upgrade when you're ready.
        </h2>
        <div class="flex flex-col sm:flex-row justify-center gap-6 mt-8 mb-8">
            <div class="flex-1 max-w-xs mx-auto sm:mx-0">
                <div class="text-3xl font-bold text-gray-900 dark:text-white">$0</div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Scheduling, calendar sync, followers, and notifications</div>
            </div>
            <div class="hidden sm:block w-px bg-gray-200 dark:bg-white/10"></div>
            <div class="flex-1 max-w-xs mx-auto sm:mx-0">
                <div class="text-3xl font-bold text-gray-900 dark:text-white">$5<span class="text-base font-normal text-gray-500 dark:text-gray-400">/mo</span></div>
                <div class="text-sm text-gray-500 dark:text-gray-400 mt-1">Ticketing, newsletters, custom domain, and branding</div>
            </div>
        </div>
        <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">Zero platform fees on ticket sales. You only pay Stripe's processing fee.</p>
        <a href="{{ marketing_url('/pricing') }}" class="inline-flex items-center text-blue-600 dark:text-blue-400 hover:underline font-medium">
            See all plans
            <svg aria-hidden="true" class="ml-1 w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6" />
            </svg>
        </a>
    </div>
</section>
