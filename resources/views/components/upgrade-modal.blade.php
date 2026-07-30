@props([
    'name',
    'tier' => 'pro',
    'subdomain' => '',
    'learnMoreUrl' => null,
    // Overrides the generic "Pro Feature" heading with something that names the actual limit.
    'title' => null,
    // What the tier unlocks. A paywall that only says no converts far worse than one that explains.
    'bullets' => [],
])

@if (config('app.hosted'))
@php
    $heading = $title ?: ($tier === 'enterprise'
        ? __('messages.upgrade_feature_title_enterprise')
        : __('messages.upgrade_feature_title_pro'));
@endphp
<x-modal :name="$name" maxWidth="sm" :ariaLabel="$heading">
    <div class="p-6 text-center">
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-blue-100 dark:bg-blue-900/30 mb-4">
            <svg class="h-6 w-6 text-[var(--brand-blue)]" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M18,8A2,2 0 0,1 20,10V20A2,2 0 0,1 18,22H6C4.89,22 4,21.1 4,20V10A2,2 0 0,1 6,8H15V6A3,3 0 0,0 12,3A3,3 0 0,0 9,6H7A5,5 0 0,1 12,1A5,5 0 0,1 17,6V8H18M12,17A2,2 0 0,0 14,15A2,2 0 0,0 12,13A2,2 0 0,0 10,15A2,2 0 0,0 12,17Z" />
            </svg>
        </div>

        <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">
            {{ $heading }}
        </h3>

        <p class="text-sm text-gray-600 dark:text-gray-400 mb-2">
            {{ $slot }}
        </p>

        @if (count($bullets))
        <ul class="mx-auto mb-4 max-w-xs space-y-2 text-start">
            @foreach ($bullets as $bullet)
            <li class="flex items-start gap-2 text-sm text-gray-600 dark:text-gray-400">
                <svg class="w-4 h-4 mt-0.5 flex-shrink-0 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                </svg>
                <span>{{ $bullet }}</span>
            </li>
            @endforeach
        </ul>
        @endif

        @if ($learnMoreUrl)
        <p class="text-sm mb-4">
            <a href="{{ $learnMoreUrl }}" target="_blank" class="text-[var(--brand-blue)] hover:underline">{{ __('messages.learn_more') }} &rarr;</a>
        </p>
        @else
        <div class="mb-2"></div>
        @endif

        <div class="flex flex-row gap-3">
            <button type="button" x-on:click="$dispatch('close-modal', '{{ $name }}')"
                    class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-white dark:bg-gray-700 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-sm text-gray-700 dark:text-gray-300 shadow-sm transition-all duration-200 hover:bg-gray-50 dark:hover:bg-gray-600 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.cancel') }}
            </button>
            @if ($subdomain)
            {{-- Same tab: an upgrade in an orphan tab has no back button, and the original tab keeps
                 showing the stale locked state after payment. --}}
            <a href="{{ route('role.subscribe', ['subdomain' => $subdomain, 'tier' => $tier]) }}"
               class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.upgrade') }}
            </a>
            @else
            <a href="{{ marketing_url('/pricing') }}" target="_blank"
               class="flex-1 inline-flex items-center justify-center px-4 py-2.5 bg-[var(--brand-button-bg)] border border-transparent rounded-lg font-semibold text-sm text-white shadow-sm transition-all duration-200 hover:bg-[var(--brand-button-bg-hover)] focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800">
                {{ __('messages.upgrade') }}
            </a>
            @endif
        </div>
    </div>
</x-modal>
@endif
