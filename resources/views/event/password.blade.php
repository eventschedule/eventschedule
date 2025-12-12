<x-app-guest-layout :role="$role" :event="$event" :date="$date">

  <main class="container mx-auto px-5 py-[80px]">
    <div class="max-w-md mx-auto bg-white p-8 rounded-lg shadow">
      <h2 class="text-xl font-semibold mb-4">{{ __('messages.event_password_required') }}</h2>
      <p class="mb-4 text-sm text-gray-700">{{ __('messages.event_password_prompt') }}</p>

      @if (session('error'))
        <div class="mb-4 text-red-600">{{ session('error') }}</div>
      @endif

      <form method="POST" action="{{ route('event.access', ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($event->id)]) }}">
        @csrf
        <input type="hidden" name="date" value="{{ request('date') }}" />
        <div class="mb-4">
          <label for="password" class="block text-sm font-medium text-gray-700">{{ __('messages.password') }}</label>
          <input id="password" name="password" type="password" class="mt-1 block w-full border-gray-300 rounded-md" required>
        </div>
        <div class="flex items-center justify-end">
          <button type="submit" class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md">{{ __('messages.submit') }}</button>
        </div>
      </form>
    </div>
  </main>

</x-app-guest-layout>
