<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts" :password-gate="true">

  <main>
    <div class="min-h-[60vh] flex items-center justify-center px-4">
      <div class="w-full max-w-md">
        <div class="bg-white dark:bg-gray-800 shadow-lg rounded-lg p-8 text-center">
          <div class="flex justify-center mb-4">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-12 h-12 text-gray-400 dark:text-gray-500">
              <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
            </svg>
          </div>

          <h1 class="text-xl font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ $event->translatedName() }}
          </h1>

          <p class="text-sm text-gray-500 dark:text-gray-400 mb-6">
            {{ __('messages.event_password_required') }}
          </p>

          @if (session('password_error'))
            <div class="mb-4 p-3 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-md">
              <p class="text-sm text-red-600 dark:text-red-400">{{ __('messages.incorrect_password') }}</p>
            </div>
          @endif

          <form method="POST" action="{{ route('event.check_password', ['subdomain' => $role->subdomain]) }}">
            @csrf
            <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">

            <div class="mb-4">
              <x-text-input type="password" name="password" class="w-full text-center" required autofocus
                placeholder="{{ __('messages.event_password') }}" />
            </div>

            <x-primary-button class="w-full justify-center">
              {{ __('messages.submit') }}
            </x-primary-button>
          </form>
        </div>
      </div>
    </div>
  </main>

</x-app-guest-layout>
