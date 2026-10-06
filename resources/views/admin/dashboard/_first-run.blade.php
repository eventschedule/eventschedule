{{-- A new install: no schedule, no event, and no account but the one looking at this page
     (AdminDashboard::build()). One card that says so, in place of a page of cards each saying
     "nothing yet". --}}
<section class="ap-card rounded-xl p-8 sm:p-10 flex flex-col items-center text-center">
    <div class="dashboard-icon p-2 rounded-xl bg-gray-100 dark:bg-white/[0.06]">
        <svg class="w-5 h-5 text-gray-500 dark:text-gray-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Utils\RealtimeIcons::PATHS['schedule'] }}" />
        </svg>
    </div>
    <h2 class="mt-4 text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.admin_dash_first_run_title')</h2>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-md">@lang('messages.admin_dash_first_run_text')</p>
    <div class="mt-5">
        <x-brand-link :href="route('home')">@lang('messages.create_schedule')</x-brand-link>
    </div>
</section>
