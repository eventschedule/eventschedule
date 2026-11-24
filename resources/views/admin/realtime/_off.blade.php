{{-- Tracking switched off: one amber line with its one action, above the panels and never in a
     panel's place. Until 2026-11 it was a large centred card standing where the Visitors panel
     goes, and on hosted it left an empty block beside the Revenue and Active users cards; what
     Realtime records is said beside its switch in settings, which this action opens. --}}
<x-page-notice v-if="p.state === 'off'" tone="warn">
    <p>@{{ msg.trackingOffTitle }}</p>
    <x-slot:action>
        <a href="{{ $realtimeConfig['settingsUrl'] }}" class="text-amber-800 dark:text-amber-200 hover:underline focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)] rounded">@{{ msg.turnOn }}</a>
    </x-slot:action>
</x-page-notice>
