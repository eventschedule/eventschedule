<x-app-layout :title="__('messages.payment_plan') . ($event ? ' - ' . $event->name : '')">

    <x-slot name="meta">
        @include('partials.private-page-meta')
    </x-slot>

    <main id="main-content" class="flex-1 p-4 sm:p-6" tabindex="0">
        <div class="max-w-2xl mx-auto">

            @if (session('error'))
                <div class="mb-4 rounded-lg p-3 text-sm bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-700 text-red-800 dark:text-red-200">
                    {{ session('error') }}
                </div>
            @endif

            @if (request()->boolean('paid'))
                <div class="mb-4 rounded-lg p-3 text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200">
                    {{ __('messages.thank_you') }}
                </div>
            {{-- Gated on the card actually being stored, not on the query string. Stripe redirects
                 here whether or not anything reached us, and this used to confirm a swap that a
                 doc-following install never received - leaving the buyer reassured while the cron
                 kept declining the card they had just replaced. When it is false the card panel
                 below still shows the truth, so silence is the honest answer. --}}
            @elseif (request()->boolean('updated') && $cardStored)
                <div class="mb-4 rounded-lg p-3 text-sm bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-700 text-green-800 dark:text-green-200">
                    {{ __('messages.update_payment_card') }}
                </div>
            @endif

            <div class="mb-6">
                {{-- v-pre is unnecessary here (no Vue mount on this page) but the values are still
                     user-controlled, so they stay inside Blade's escaping and nothing else. --}}
                <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100">{{ $event?->name }}</h1>
                @if ($sale)
                    <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">{{ $sale->name }}</p>
                @endif
            </div>

            @if ($plan->status === 'cancelled')
                <div class="ap-card rounded-xl p-6">
                    <p class="text-gray-700 dark:text-gray-300">{{ __('messages.installment_status_cancelled') }}</p>
                </div>
            @else
                @if ($plan->isDelinquent())
                    <div class="mb-4 rounded-lg p-3 flex items-start gap-2 bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-700">
                        <svg class="w-5 h-5 text-amber-600 dark:text-amber-400 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m-9.303 3.376c-.866 1.5.217 3.374 1.948 3.374h14.71c1.73 0 2.813-1.874 1.948-3.374L13.949 3.378c-.866-1.5-3.032-1.5-3.898 0L2.697 16.126ZM12 15.75h.007v.008H12v-.008Z" />
                        </svg>
                        <div class="text-sm text-amber-800 dark:text-amber-200">
                            <p class="font-semibold">{{ __('messages.ticket_payment_overdue') }}</p>
                            <p class="mt-1">{{ __('messages.ticket_on_hold_sub', ['amount' => \App\Utils\MoneyUtils::format($plan->amountRemaining(), $plan->currency)]) }}</p>
                        </div>
                    </div>
                @endif

                @include('partials.installment-plan-panel', ['plan' => $plan, 'variant' => 'light'])
            @endif

            @if ($sale && $event)
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-6">
                    <x-link href="{{ route('ticket.view', ['event_id' => \App\Utils\UrlUtils::encodeId($event->id), 'secret' => $sale->secret]) }}">
                        {{ __('messages.view_ticket') }}
                    </x-link>
                </p>
            @endif
        </div>
    </main>
</x-app-layout>
