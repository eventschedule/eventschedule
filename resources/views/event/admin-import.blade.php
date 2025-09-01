<x-app-admin-layout>

    <div class="flex justify-between items-center gap-6 my-6 pb-2">
        @if (is_rtl())
            <!-- RTL Layout: Cancel button on left, title on right -->
            <div class="flex items-center gap-3">
                <button onclick="history.back()" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.cancel') }}
                </button>
            </div>
            
            <div class="text-right">
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100x sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_events') }}
                </h2>
            </div>
        @else
            <!-- LTR Layout: Title on left, cancel button on right -->
            <div>
                <h2 class="text-xl font-bold leading-7 text-gray-900 dark:text-gray-100x sm:truncate sm:text-2xl sm:tracking-tight">
                    {{ __('messages.import_events') }}
                </h2>
            </div>

            <div class="flex items-center gap-3">
                <button onclick="history.back()" type="button" class="px-4 py-2 bg-gray-200 dark:bg-gray-700 text-gray-900 dark:text-gray-100 rounded-md hover:bg-gray-300 dark:hover:bg-gray-600 transition-colors">
                    {{ __('messages.cancel') }}
                </button>
            </div>
        @endif
    </div>

    @include('event.import')

</x-app-admin-layout>