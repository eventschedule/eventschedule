{{-- The setup guide's ring, drawn on the server: one segment per step, clockwise from the top,
     each one full or empty and never part-filled. This is the still picture; the component
     (resources/js/components/SetupGuideRing.vue) draws the same geometry and animates it.
     Round caps add half the stroke to each end of a dash, hence the subtraction. --}}
@php
    $sgSize = (int) ($size ?? 20);
    $sgStroke = (float) ($stroke ?? max(2, round($sgSize / 9)));
    $sgTotal = max(1, (int) ($total ?? 3));
    $sgDone = (int) ($done ?? 0);
    $sgRadius = ($sgSize - $sgStroke) / 2;
    $sgRound = 2 * M_PI * $sgRadius;
    $sgGap = $sgTotal > 1 ? $sgStroke * 0.9 + 2 : 0;
    $sgDash = max(0.5, $sgRound / $sgTotal - $sgGap - $sgStroke);
    $sgCentre = $sgSize / 2;
@endphp
<svg width="{{ $sgSize }}" height="{{ $sgSize }}" viewBox="0 0 {{ $sgSize }} {{ $sgSize }}" class="shrink-0" aria-hidden="true" focusable="false">
    @for ($sgIndex = 0; $sgIndex < $sgTotal; $sgIndex++)
    <circle cx="{{ $sgCentre }}" cy="{{ $sgCentre }}" r="{{ round($sgRadius, 3) }}" fill="none"
        stroke="{{ $sgIndex < $sgDone ? 'var(--brand-blue)' : 'rgb(var(--ap-border-strong))' }}"
        stroke-width="{{ $sgStroke }}" stroke-linecap="round"
        stroke-dasharray="{{ round($sgDash, 3) }} {{ round($sgRound - $sgDash, 3) }}"
        stroke-dashoffset="{{ round(-($sgIndex * $sgRound / $sgTotal + $sgGap / 2 + $sgStroke / 2), 3) }}"
        transform="rotate(-90 {{ $sgCentre }} {{ $sgCentre }})" />
    @endfor
</svg>
