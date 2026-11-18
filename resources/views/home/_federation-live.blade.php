{{-- Told once to each admin, after the first sync that came back approved with events on the
     network (FederationService::noteLive(), takeLiveCard()). "See them" opens this install's own
     listings on the network, the link the network sends back with every sync. --}}
@php
    $liveUrl = \App\Models\Setting::get('federation_listings_url')
        ?: rtrim((string) config('app.nexus_url'), '/').'/browse?show=network';
@endphp
<section class="ap-card rounded-xl p-4 sm:p-5" aria-labelledby="federation-live-title" v-pre>
    <div class="flex flex-col gap-3 sm:flex-row sm:items-center sm:gap-4">
        <div class="flex items-start gap-3 min-w-0 flex-1">
            <span class="dashboard-icon p-2 rounded-xl bg-green-50 dark:bg-green-500/10 text-green-600 dark:text-green-400 shrink-0" style="--icon-glow: rgba(34, 197, 94, 0.35)">
                <svg class="w-5 h-5" aria-hidden="true" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8"><circle cx="12" cy="12" r="9" /><path stroke-linecap="round" d="M3 12h18M12 3c2.5 2.7 3.8 5.7 3.8 9s-1.3 6.3-3.8 9c-2.5-2.7-3.8-5.7-3.8-9S9.5 5.7 12 3z" /></svg>
            </span>
            <div class="min-w-0">
                <h2 id="federation-live-title" class="text-base font-semibold text-gray-900 dark:text-white">
                    {{ trans_choice('messages.federation_live_title', $liveCount, ['count' => number_format($liveCount)]) }}
                </h2>
                <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">{{ __('messages.federation_invite_fact_backlink') }}</p>
            </div>
        </div>
        <x-brand-link :href="$liveUrl" target="_blank" rel="noopener" class="w-full sm:w-auto shrink-0">{{ __('messages.federation_live_see') }}</x-brand-link>
    </div>
</section>
