@props(['badge' => null])

@php
    // AdminAlertService::badges() entries: ['count' => int, 'color' => 'red'|'amber'|'blue'].
    // Red is reserved for something broken - informational queues must not borrow it,
    // or a permanently non-zero row (unverified schedules, uncredited referrals) would
    // leave the nav looking broken forever.
    $count = (int) ($badge['count'] ?? 0);

    $tone = match ($badge['color'] ?? 'red') {
        'amber' => 'bg-amber-500',
        'blue' => 'bg-[var(--brand-button-bg)]',
        default => 'bg-red-500',
    };
@endphp

@if ($count > 0)
    <span class="ms-1.5 inline-flex items-center justify-center min-w-[1.25rem] h-5 px-1 text-xs font-bold text-white {{ $tone }} rounded-full">{{ $count }}</span>
@endif
