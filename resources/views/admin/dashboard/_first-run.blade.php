{{-- A new install: no schedule, no event, and no account but the one looking at this page
     (AdminDashboard::build()). One card that says so, in place of a page of cards each saying
     "nothing yet". Words only, no icon chip (2026-11). --}}
<section class="ap-card rounded-xl p-8 sm:p-10 flex flex-col items-center text-center">
    <h2 class="text-lg font-semibold text-gray-900 dark:text-white">@lang('messages.admin_dash_first_run_title')</h2>
    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400 max-w-md">@lang('messages.admin_dash_first_run_text')</p>
    <div class="mt-5">
        <x-brand-link :href="route('home')">@lang('messages.create_schedule')</x-brand-link>
    </div>
</section>
