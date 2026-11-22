{{-- noindex: served at the event's own URL while it is locked, so what would be indexed is the lock. --}}
<x-app-guest-layout :role="$role" :event="$event" :date="$date" :fonts="$fonts" :password-gate="true" :page-title="__('messages.password')" :no-index="true">

  @include('partials.guest-form-pages-styles')
  <main>
    {{-- One small sheet: the lock, the event's name, the field. Nothing else about the event is
         shown until the password is given. --}}
    <div class="gk-stage gk-pg gk-pg-sm">
      <div class="gk-pg-head">
        <span class="gk-pg-mark" aria-hidden="true">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z" />
          </svg>
        </span>

        <h1 class="gk-title" dir="auto">{{ $event->translatedName() }}</h1>

        <p class="gk-pg-lead">{{ __('messages.event_password_required') }}</p>
      </div>

      @if (session('password_error'))
        <div class="gk-note gk-note-bad mt-6" role="alert">
          <p class="gk-note-body">{{ __('messages.incorrect_password') }}</p>
        </div>
      @endif

      <form method="POST" action="{{ route('event.check_password', ['subdomain' => $role->subdomain]) }}" class="mt-8 flex flex-col gap-4">
        @csrf
        {{-- A form anybody can post: the decoy field every public form carries. --}}
        <x-honeypot />
        <input type="hidden" name="event_id" value="{{ \App\Utils\UrlUtils::encodeId($event->id) }}">
        {{-- The occurrence the visitor opened, so the answer returns them to it. The controller
             re-checks it is still an occurrence before redirecting anywhere near it. --}}
        @if (! empty($returnDate))
        <input type="hidden" name="date" value="{{ $returnDate }}">
        @endif

        <div>
          <label for="event_password_input" class="gk-pg-label">{{ __('messages.event_password') }}</label>
          <input type="password" id="event_password_input" name="password" class="gk-pg-input" required autofocus autocomplete="off">
        </div>

        <button type="submit" class="gk-btn gk-btn-primary gk-cta-main gk-pg-cta-wide">
          {{ __('messages.submit') }}
        </button>
      </form>
    </div>
  </main>

</x-app-guest-layout>
