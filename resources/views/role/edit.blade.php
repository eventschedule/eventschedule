<x-app-layout>
    <x-slot name="header">
        {{ __('Sign Up') }}
    </x-slot>

    <form method="post" action="{{ route('role.store') }}">
        @csrf
        @method('post')

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow sm:rounded-lg">
                <div class="max-w-xl">                    
                    
                    <fieldset>
                        <x-input-label for="type" :value="__('Type')" />
                        <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                            <div class="flex items-center">
                                <input id="venue" name="type" type="radio" checked class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="venue" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Venue</label>
                            </div>
                            <div class="flex items-center">
                                <input id="talent" name="type" type="radio" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="talent" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Talent</label>
                            </div>
                            <div class="flex items-center">
                                <input id="vendor" name="type" type="radio" class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                <label for="vendor" class="ml-3 block text-sm font-medium leading-6 text-gray-900">Vendor</label>
                            </div>
                        </div>
                    </fieldset>

                    <div>
                        <x-input-label for="name" :value="__('Name')" />
                        <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', '')" required autofocus/>
                        <x-input-error class="mt-2" :messages="$errors->get('name')" />
                    </div>

                </div>
            </div>
        </div>
    </div>

    </form>

</x-app-layout>
