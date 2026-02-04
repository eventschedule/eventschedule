<x-app-admin-layout>

    <div class="flex justify-between items-center gap-6 pb-6">
        @if (is_rtl())
            <!-- RTL Layout: Cancel button on left, title on right -->
            <div class="flex items-center gap-3">
                <button onclick="history.back()" type="button" class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.back') }}
                </button>
            </div>
            
            <div class="flex items-center text-end">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_events') }}
                </h2>
            </div>
        @else
            <!-- LTR Layout: Title on left, cancel button on right -->
            <div class="flex items-center">
                @if ($role->profile_image_url)
                    <div class="pe-4">
                        <img src="{{ $role->profile_image_url }}" class="rounded-lg h-14 w-14 flex-none">
                    </div>
                @endif
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_events') }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="history.back()" type="button" class="inline-flex items-center justify-center rounded-md bg-gray-200 dark:bg-gray-700 px-5 py-3 text-base font-semibold text-gray-900 dark:text-gray-100 shadow-sm ring-1 ring-inset ring-gray-300 dark:ring-gray-600 hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.back') }}
                </button>
            </div>
        @endif
    </div>

    @include('event.import')

</x-app-admin-layout>