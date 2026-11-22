{{-- noindex: a form, not content. Every schedule has one, and none is worth a search result. --}}
<x-app-guest-layout :role="$role" :showMobileBackground="true" :page-title="__('messages.import_events')" :no-index="true">

@php
  // The cover the schedule shows on its own page, or none (Role::headerImageUrl()).
  $coverUrl = $role->headerImageUrl();
@endphp

@include('partials.guest-form-pages-styles')
{{-- The form below is event/import, which the admin portal's importer shares. Its cards and
     fields are given the stage's look here, under this page's class, and nowhere else: the
     portal keeps its own. --}}
<style {!! nonce_attr() !!}>
    .gk-pg-import .gs-layout { display: grid; grid-template-columns: minmax(0, 1fr); gap: 2rem; }
    .gk-pg-import .gs-head { display: flex; flex-direction: column; align-items: flex-start; gap: 1rem; min-width: 0; }
    @media (min-width: 64rem) {
        .gk-pg-import .gs-layout { grid-template-columns: minmax(0, 19rem) minmax(0, 1fr); column-gap: clamp(2rem, 4vw, 3.5rem); align-items: start; }
    }
    .gk-pg-import .gk-title { font-size: clamp(2.125rem, 1.5rem + 2.2vw, 3.25rem); }
    .gk-pg-import .gs-main { min-width: 0; }
    .gk-pg-import .gs-main .ap-card { border: 1px solid var(--gk-card-edge); border-radius: 1.5rem; background: var(--gk-stage-raised); color: var(--gk-ink); box-shadow: none; }
    :where(.dark) .gk-pg-import .gs-main .ap-card { border-color: transparent; }
    .gk-pg-import .gs-main .ap-card::before,
    .gk-pg-import .gs-main .ap-card::after { content: none; }
    .gk-pg-import .gs-main :is(input[type="text"], input[type="email"], input[type="password"], input[type="url"], input[type="number"], input[type="tel"], select, textarea) { border-color: var(--gk-line); border-radius: .75rem; background-color: var(--gk-stage); color: var(--gk-ink); }
    .gk-pg-import .gs-main :is(input, select, textarea):focus { border-color: var(--es-accent-readable); box-shadow: 0 0 0 3px color-mix(in srgb, var(--es-accent-readable) 24%, transparent); }
    .gk-pg-import .gs-main :is(.text-gray-900, .dark\:text-gray-100, .dark\:text-white) { color: var(--gk-ink); }
    .gk-pg-import .gs-main :is(.text-gray-700, .text-gray-600, .dark\:text-gray-300) { color: var(--gk-ink-2); }
    .gk-pg-import .gs-main :is(.text-gray-500, .text-gray-400, .dark\:text-gray-400) { color: var(--gk-ink-3); }
    {{-- A card inside the card (the AI setup guide is a card of its own) is flat. --}}
    .gk-pg-import .gs-main .ap-card .ap-card { margin: 0; padding: 0; border: 0; border-radius: 0; background: none; }
    {{-- The form's own buttons (add a picture, send): the schedule's colour. --}}
    .gk-pg-import .gk-imp-go { background: var(--es-accent); color: var(--es-accent-text); border: 1px solid var(--es-accent-edge); }
    .gk-pg-import .gk-imp-go:hover { filter: brightness(1.08); }
    @media (max-width: 39.99rem) {
        .gk-pg-import .gs-main .ap-card { width: auto; margin-inline: -1.125rem; border-inline: 0; border-radius: 0; }
    }
</style>

  <main>
      {{-- One sheet: the schedule's cover across its top when it has one, the head, and the
           form beside it. --}}
      <div class="gk-stage gk-pg gk-pg-import">
        @if ($coverUrl)
        <div class="gk-pg-cover">
          <img src="{{ $coverUrl }}" alt="" decoding="async" fetchpriority="high">
        </div>
        @endif
        <div class="gs-layout">
          <header class="gs-head">
            @include('role.partials.form-page-who', ['whoRole' => $role, 'whoLink' => $role->getGuestUrl() ?: null, 'whoName' => $role->getDisplayName(true)])
            <h1 class="gk-title">
              {{ __('messages.add_event') }}
            </h1>
          </header>

          <div class="gs-main">
            @include('event.import')
          </div>
        </div>
      </div>
  </main>

</x-app-guest-layout>