{{-- A brand-new organizer has nothing to count yet. In place of four zeros: the one thing worth
     waiting for, and what happens when it comes.

     Two states, one shape. The card is the same height in both and ends on the same line as the
     card beside it, with one footer link in both, so nothing on the page moves when the first
     visitor arrives. home/_live-script turns it; once turned it does not turn back (the visitor
     came, whether or not they are still there).

     "Opened your page", not "is on your page": a visitor who declined cookies sends one page view
     and nothing after it, so that someone came is known and that they stayed is not. And "which
     page", not "where from": a visitor's source is never shown beside a visitor.

     The link to share is in the setup guide above while that is on the page. This card carries it
     only when the guide is gone, so the two never offer the same button a card apart. --}}
@php
    $came = ($live['views_30m'] ?? 0) > 0;
    $shareHere = \App\Utils\SetupGuide::surface() !== 'section' && ! empty($dashboard['page']);
    $dot = fn (string $tone) => '<span class="relative inline-flex w-2.5 h-2.5 shrink-0"><span class="absolute inset-0 rounded-full '.$tone.' opacity-60 motion-safe:animate-ping"></span><span class="relative inline-flex w-2.5 h-2.5 rounded-full '.$tone.'"></span></span>';
@endphp

<section class="ap-card rounded-xl flex flex-col overflow-hidden w-full" aria-labelledby="dashboard-first-visitor" data-first-visitor>
    <div class="px-4 sm:px-5 pt-4 sm:pt-5 pb-3 flex items-center gap-3">
        <div class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10" style="--icon-glow: rgba(34, 197, 94, 0.15)">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Utils\RealtimeIcons::PATHS['signal'] }}" /></svg>
        </div>
        <h2 id="dashboard-first-visitor" class="text-base font-semibold text-gray-900 dark:text-white">{{ __('messages.realtime') }}</h2>
    </div>

    <div class="px-4 sm:px-5 pb-4 min-h-[5.5rem]" aria-live="polite">
        <div data-first-visitor-waiting class="{{ $came ? 'hidden' : '' }}">
            <p class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">{!! $dot('bg-gray-400') !!}{{ __('messages.dash_waiting_title') }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.dash_waiting_body') }}</p>
        </div>
        <div data-first-visitor-came class="{{ $came ? '' : 'hidden' }}">
            <p class="flex items-center gap-2 text-base font-semibold text-gray-900 dark:text-white">{!! $dot('bg-green-500') !!}{{ __('messages.dash_first_title') }}</p>
            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">{{ __('messages.dash_first_body') }}</p>
        </div>

        @if ($shareHere)
            <div class="mt-3 flex flex-wrap items-center gap-2">
                <code class="min-w-0 flex-1 truncate rounded-lg px-3 py-1.5 text-sm text-gray-700 dark:text-gray-300" style="background: var(--ap-tint-sunken)" dir="ltr">{{ preg_replace('~^https?://~', '', rtrim($dashboard['page'], '/')) }}</code>
                <button type="button" data-copy-link="{{ $dashboard['page'] }}" data-copied="{{ __('messages.copied') }}"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 border border-gray-300 dark:border-gray-600 rounded-lg text-sm font-semibold text-gray-900 dark:text-gray-100 bg-white dark:bg-gray-700 transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-800 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">{{ __('messages.copy_link') }}</button>
            </div>
        @endif
    </div>

    <div class="mt-auto px-4 sm:px-5 py-3 flex items-center justify-end gap-3" style="border-top: 1px solid var(--ap-hairline)">
        <x-link :href="route('analytics', ['tab' => 'realtime'])" class="inline-flex items-center gap-1 text-sm font-medium whitespace-nowrap">
            {{ __('messages.dash_open_realtime') }}
            <svg class="w-3.5 h-3.5 {{ is_rtl() ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
        </x-link>
    </div>
</section>
