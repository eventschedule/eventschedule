<x-app-layout>

    <form method="POST" action="{{ $event->exists ? url('/update') : route('role.store') }}">

        @csrf
        @if($event->exists)
        @method('put')
        @endif

        <div class="py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">

                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('Details') }}
                        </h2>
                    </div>
                </div>
            </div>
        </div>

    </form>

</x-app-layout>