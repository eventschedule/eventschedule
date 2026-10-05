{{-- Flashed by EventController::importDone() when an import added events, and by importUndo()
     when one was taken back. The same shape as the first-event panel above it: what happened,
     where it lives, and what to do next. Event names are the owner's own text, hence x-user-text. --}}
@php
    $eventsImported = session('events_imported');
    $importUndone = session('import_undone');
@endphp

@if (is_array($eventsImported))
@php
    $importedCount = (int) $eventsImported['count'];
    $importedNames = array_slice($eventsImported['names'] ?? [], 0, 3);
    $guestUrl = $role->getGuestUrl();
@endphp
<div class="pb-4">
    <div class="ap-card rounded-xl p-6" role="status">
        <div class="flex items-start gap-3">
            <div class="p-2 rounded-xl shrink-0 bg-green-50 dark:bg-green-500/10">
                <svg class="w-5 h-5 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="min-w-0 flex-1">
                <h3 class="text-base font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('messages.import_panel_title', ['count' => $importedCount]) }}
                </h3>
                <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                    <x-user-text dir="auto" class="font-medium text-gray-700 dark:text-gray-300">{{ implode(', ', $importedNames) }}</x-user-text>
                    @if ($importedCount > count($importedNames))
                        {{ __('messages.import_panel_more', ['count' => $importedCount - count($importedNames)]) }}
                    @endif
                </p>
            </div>
        </div>

        {{-- For someone with a setup guide this panel is also where their page first fills up:
             the guide adds its "Your page" strip here rather than showing a second panel. --}}
        @include('partials.setup-guide', ['place' => 'strip'])

        @if ($guestUrl)
        {{-- data-setup-share: copying the schedule's own address is the guide's "share" step. --}}
        <x-copy-link id="imported-schedule-url" :value="$guestUrl" class="mt-4" :label="__('messages.schedule_link')" data-setup-share="link" />
        @endif

        {{-- The way back is quiet and at the start; the way on is at the end. --}}
        <div class="mt-4 flex flex-wrap items-center gap-3">
            <div class="order-last -ms-2 w-full sm:order-none sm:w-auto">
                @include('event.partials.import-undo', ['count' => $importedCount])
            </div>
            <span class="hidden flex-1 sm:block"></span>
            @if ($importedCount >= 5)
            <x-secondary-link href="{{ route('event.show_import_ai', ['subdomain' => $role->subdomain]) }}">
                {{ __('messages.import_panel_import_more') }}
            </x-secondary-link>
            @endif
            @if ($guestUrl)
            <x-secondary-link href="{{ $guestUrl }}" target="_blank">
                {{ __('messages.import_panel_view_schedule') }}
            </x-secondary-link>
            @endif
            @if ($importedCount >= 5)
            {{-- A calendar worth showing: the next thing most people came for is to put it on
                 their own site. --}}
            <x-brand-button type="button" class="js-import-embed">
                {{ __('messages.import_panel_embed') }}
            </x-brand-button>
            @else
            <x-secondary-link href="#" class="js-import-embed">
                {{ __('messages.import_panel_embed') }}
            </x-secondary-link>
            <x-brand-link href="{{ route('event.show_import_ai', ['subdomain' => $role->subdomain]) }}">
                {{ __('messages.import_panel_add_more') }}
            </x-brand-link>
            @endif
        </div>
    </div>
</div>

<script {!! nonce_attr() !!}>
    {{-- Bound to the button itself, and stopped there: the embed dialog closes on any click that
         reaches the document from outside it, so a click left to bubble opens it and shuts it. --}}
    document.querySelectorAll('.js-import-embed').forEach(function (trigger) {
        trigger.addEventListener('click', function (e) {
            if (typeof openEmbedModal !== 'function') {
                return;
            }
            e.preventDefault();
            e.stopPropagation();
            // The calendar, whichever widget the dialog showed last.
            openEmbedModal('calendar');
        });
    });
</script>
@elseif (is_array($importUndone))
<div class="pb-4">
    <div class="ap-card flex flex-wrap items-center justify-between gap-3 rounded-xl p-6" role="status">
        <p class="text-sm text-gray-700 dark:text-gray-300">
            {{ __('messages.import_undone', ['count' => (int) $importUndone['removed']]) }}
            @if (! empty($importUndone['kept']))
                {{ __('messages.import_undone_kept', ['count' => (int) $importUndone['kept']]) }}
            @endif
        </p>
        <x-secondary-link href="{{ route('event.show_import_ai', ['subdomain' => $role->subdomain]) }}">
            {{ __('messages.import_again') }}
        </x-secondary-link>
    </div>
</div>
@endif
