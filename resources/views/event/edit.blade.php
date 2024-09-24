<x-app-admin-layout>

<x-slot name="head">
  <script src="https://unpkg.com/vue@3/dist/vue.global.prod.js"></script>
</x-slot>

<div id="app">
  <h2 class="pt-2 mt-4 text-xl font-bold leading-7 text-gray-900 dark:text-gray-100 sm:truncate sm:text-2xl sm:tracking-tight">
    {{ $title }}
  </h2>

  <form method="POST"
        action="{{ $event->exists ? route('event.update', ['subdomain' => $subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)]) : route('event.store', ['subdomain' => $subdomain]) }}"
        enctype="multipart/form-data">

        @csrf

        @if ($event->exists)
        @method('put')
        @endif

        <div class="py-5">
            <div class="max-w-7xl mx-auto space-y-6">
                
                <div class="p-4 sm:p-8 bg-white dark:bg-gray-800 shadow-md sm:rounded-lg">
                    <div class="max-w-xl">                                                
                        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100 mb-6">
                            {{ __('messages.venue') }}
                        </h2>

                        <fieldset>
                            <div class="mt-2 mb-6 space-y-6 sm:flex sm:items-center sm:space-x-10 sm:space-y-0">
                                <div class="flex items-center">
                                    <input id="use_existing" name="vendor_type" type="radio" value="use_existing" CHECKED
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="use_existing"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.use_existing') }}</label>
                                </div>
                                <div class="flex items-center">
                                    <input id="search_create" name="vendor_type" type="radio" value="search_create"
                                        class="h-4 w-4 border-gray-300 text-indigo-600 focus:ring-indigo-600">
                                    <label for="search_create"
                                        class="ml-3 block text-sm font-medium leading-6 text-gray-900 dark:text-gray-100">{{ __('messages.search_create') }}</label>
                                </div>
                            </div>
                        </fieldset>

                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<script>
  const { createApp, ref } = Vue

  createApp({
    data() {
      return {
        event: @json($event),
      }
    },
    setup() {
      return {
        
      }
    }
  }).mount('#app')
</script>

</x-app-admin-layout>