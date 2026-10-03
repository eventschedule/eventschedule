{{-- "Download my data" (GDPR Arts. 15 and 20): ProfileController::requestDataExport() queues
     App\Jobs\ExportPersonalData, which emails a signed link to the file. --}}
<section class="space-y-6">
    <header>
        <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100 flex items-center gap-2">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
            </svg>
            {{ __('messages.data_export_title') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('messages.data_export_description') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.data_export') }}">
        @csrf
        <x-primary-button>{{ __('messages.data_export_button') }}</x-primary-button>
    </form>
</section>
