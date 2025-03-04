<x-app-admin-layout>

    <h2 class="pt-2 my-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100x sm:truncate sm:text-2xl sm:tracking-tight">
        {{ __('messages.import_event') }}
    </h2>

    <form method="post"
        action="{{ route('event.import', ['subdomain' => $role->subdomain]) }}"
        enctype="multipart/form-data">

        @csrf

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">
                    
                        <div class="mb-6">
                            <x-input-label for="message" :value="__('messages.message')" />
                            <textarea id="message" name="message" rows="5"
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-[#4E81FA] dark:focus:border-[#4E81FA] focus:ring-[#4E81FA] dark:focus:ring-[#4E81FA] rounded-md shadow-sm">
                                
                            </textarea>
                            <x-input-error class="mt-2" :messages="$errors->get('message')" />
                        </div>

                    </div>
                </div>
            </div>
        </div>


    </form>

</x-app-admin-layout>