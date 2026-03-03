<x-marketing-layout>
    <x-slot name="title">Referral Program | Event Schedule - Earn Free Months</x-slot>
    <x-slot name="description">Earn free months of Event Schedule by referring other event organizers. Share your referral link, they subscribe, and you both win.</x-slot>
    <x-slot name="breadcrumbTitle">Referral Program</x-slot>

    {{-- Hero --}}
    <section class="pt-24 pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h1 class="text-4xl sm:text-5xl font-bold text-gray-900 dark:text-white mb-6">
                Earn Free Months by
                <span class="text-gradient">Referring Organizers</span>
            </h1>
            <p class="text-xl text-gray-600 dark:text-gray-400 max-w-2xl mx-auto">
                Know other event organizers? Share your referral link and earn free months of Event Schedule when they subscribe and stay for 30 days.
            </p>
        </div>
    </section>

    {{-- How It Works --}}
    <section class="pb-16">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-12">How It Works</h2>
            <div class="grid md:grid-cols-3 gap-8">
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[#4E81FA] flex items-center justify-center mx-auto mb-4 text-2xl font-bold">1</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Share Your Link</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        Get your unique referral link from your dashboard and share it with fellow event organizers.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[#4E81FA] flex items-center justify-center mx-auto mb-4 text-2xl font-bold">2</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">They Subscribe</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        When they sign up using your link and subscribe to a Pro or Enterprise plan, you're on your way.
                    </p>
                </div>
                <div class="text-center">
                    <div class="w-16 h-16 rounded-full bg-blue-100 dark:bg-blue-900/50 text-[#4E81FA] flex items-center justify-center mx-auto mb-4 text-2xl font-bold">3</div>
                    <h3 class="text-xl font-semibold text-gray-900 dark:text-white mb-2">Earn Your Credit</h3>
                    <p class="text-gray-600 dark:text-gray-400">
                        After they stay subscribed for 30 days, you earn a free month to apply to any of your schedules.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- Rewards --}}
    <section class="pb-16">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="text-3xl font-bold text-center text-gray-900 dark:text-white mb-12">Rewards</h2>
            <div class="grid md:grid-cols-2 gap-8">
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-300 mb-4">Pro Referral</div>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">1 Free Month</div>
                    <p class="text-gray-600 dark:text-gray-400">of Pro ($5 value)</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-4">When your referral subscribes to Pro</p>
                </div>
                <div class="bg-white dark:bg-gray-800 rounded-xl border border-gray-200 dark:border-gray-700 p-8 text-center">
                    <div class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-amber-100 text-amber-800 dark:bg-amber-900 dark:text-amber-300 mb-4">Enterprise Referral</div>
                    <div class="text-4xl font-bold text-gray-900 dark:text-white mb-2">1 Free Month</div>
                    <p class="text-gray-600 dark:text-gray-400">of Enterprise ($15 value)</p>
                    <p class="text-sm text-gray-500 dark:text-gray-500 mt-4">When your referral subscribes to Enterprise</p>
                </div>
            </div>
        </div>
    </section>

    {{-- CTA --}}
    <section class="pb-24">
        <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <div class="bg-gradient-to-r from-blue-50 to-cyan-50 dark:from-blue-900/20 dark:to-cyan-900/20 rounded-2xl border border-blue-200 dark:border-blue-800 p-10">
                <h2 class="text-2xl font-bold text-gray-900 dark:text-white mb-4">Ready to Start Earning?</h2>
                <p class="text-gray-600 dark:text-gray-400 mb-6">
                    Log in to get your unique referral link, or sign up to create your free account.
                </p>
                @auth
                    <a href="{{ route('referrals') }}"
                       class="inline-flex items-center rounded-md bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-blue-500/25 transition-all">
                        Get Your Referral Link
                    </a>
                @else
                    <a href="{{ route('register') }}"
                       class="inline-flex items-center rounded-md bg-gradient-to-r from-blue-600 to-sky-600 hover:from-blue-500 hover:to-sky-500 px-6 py-3 text-base font-semibold text-white shadow-lg shadow-blue-500/25 transition-all">
                        Sign Up Free
                    </a>
                    <p class="mt-3 text-sm text-gray-500 dark:text-gray-400">
                        Already have an account? <a href="{{ route('login') }}" class="text-[#4E81FA] hover:underline font-medium">Log in</a>
                    </p>
                @endauth
            </div>
        </div>
    </section>

</x-marketing-layout>
