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

    @if (\App\Services\PersonalDataExportService::canDeliver())
    <form method="post" action="{{ route('profile.data_export') }}">
        @csrf
        <x-primary-button>{{ __('messages.data_export_button') }}</x-primary-button>
    </form>
    @else
    <div class="flex items-start gap-3 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700 rounded-lg p-3">
        <svg class="w-5 h-5 shrink-0 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126zM12 15.75h.007v.008H12v-.008z" />
        </svg>
        <p class="text-sm text-amber-800 dark:text-amber-200">{{ __('messages.data_export_unavailable') }}</p>
    </div>
    @endif
</section>
