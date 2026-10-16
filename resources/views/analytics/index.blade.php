<x-app-admin-layout>

    <x-slot name="head">
        <script src="{{ asset('js/vue.global.prod.js') }}" {!! nonce_attr() !!}></script>
        <link rel="stylesheet" href="{{ asset('vendor/intl-tel-input/css/intlTelInput.css') }}">
        @include('analytics._styles')
    </x-slot>

    @php
        $encodedRoleId = \App\Utils\UrlUtils::encodeId($selectedRoleId);
        $encodedTabEventId = \App\Utils\UrlUtils::encodeId($tabEventId);
        // Every tab link carries the selection, the range and the grouping, so moving between tabs
        // never loses what was chosen. The Realtime link carries the schedule only when the live
        // view covers it ($realtimeRoleId): a schedule whose plan closed it to its admins is on
        // this page's picker, and pressing the tab with it selected must show all of theirs, not
        // a refusal.
        $tabLink = fn (?string $to, $roleId = null) => route('analytics', array_filter([
            'role_id' => $roleId ?? $encodedRoleId,
            'event_id' => $encodedTabEventId,
            'range' => $range,
            'period' => ($period ?? 'daily') !== ($defaultPeriod ?? 'daily') ? $period : null,
            'tab' => $to,
        ]));
        $analyticsTabs = [
            ['label' => __('messages.web_analytics'), 'href' => $tabLink(null), 'current' => $tab === 'web'],
            ! empty($realtimeAvailable) ? ['label' => __('messages.realtime'), 'href' => $tabLink('realtime', \App\Utils\UrlUtils::encodeId($realtimeRoleId ?? null) ?? ''), 'current' => $tab === 'realtime'] : null,
            ['label' => __('messages.revenue'), 'href' => $tabLink('revenue'), 'current' => $tab === 'revenue'],
            ['label' => __('messages.check_ins'), 'href' => $tabLink('checkins'), 'current' => $tab === 'checkins'],
        ];
        // A row of Top events (Web and Revenue) opens the tab on that event, under a schedule of
        // the viewer's that it is on, its own first: an event filter with no schedule selected
        // has no picker on screen to say it is there, or to clear it.
        $myRoleIds = $roles->pluck('id')->all();
        $eventLink = function ($event, ?string $to = null) use ($myRoleIds, $selectedRoleId, $range, $period, $defaultPeriod) {
            $roleId = $selectedRoleId
                ?: (in_array($event->creator_role_id, $myRoleIds) ? $event->creator_role_id : $event->roles->pluck('id')->first(fn ($id) => in_array($id, $myRoleIds)));

            return $roleId ? route('analytics', array_filter([
                'role_id' => \App\Utils\UrlUtils::encodeId($roleId),
                'event_id' => \App\Utils\UrlUtils::encodeId($event->id),
                'range' => $range,
                'period' => ($period ?? 'daily') !== ($defaultPeriod ?? 'daily') ? $period : null,
                'tab' => $to,
            ])) : null;
        };
    @endphp

    <div class="page-shell an">
        <x-page-header :title="__('messages.analytics')" :lead="__('messages.analytics_lead')" />

        {{-- Four short tabs: a strip on a phone too (it scrolls there, fades at the edge it runs
             on from and brings the open tab into view). Realtime is the second, beside Web
             Analytics, whose question it answers for the last half hour. --}}
        <x-page-tabs :tabs="$analyticsTabs" id="analytics-tabs" strip :label="__('messages.analytics')" />

        @include('analytics._filters')

        @if ($tab === 'revenue')
            @include('analytics._revenue')
        @elseif ($tab === 'checkins')
            @include('analytics._checkins')
        @elseif ($tab === 'realtime')
            @include('analytics._realtime')
        @else
            @include('analytics._web')
        @endif
    </div>

    {{-- Chart.js, then the page's own script: the filters, the cards' switches and the charts. --}}
    <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    @include('analytics._script')

</x-app-admin-layout>
