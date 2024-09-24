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
                @{{ event }}
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