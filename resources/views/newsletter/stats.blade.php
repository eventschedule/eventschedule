<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        <script src="{{ asset('js/chart.min.js') }}" {!! nonce_attr() !!}></script>
    </x-slot>

    {{-- How a sent newsletter did. The page is named by the newsletter's subject and leads back
         to the list it came from; what it shows is partials/_stats, shared with the platform
         admin's page. --}}
    <div class="page-shell">
        @php
            $readerZone = auth()->user()->timezone ?? $role->timezone ?? 'UTC';
            $sentLead = __('messages.newsletter_status_' . $newsletter->status);
            if ($newsletter->sent_at) {
                $sentLead .= ': ' . $newsletter->sent_at->copy()->timezone($readerZone)->translatedFormat(get_use_24_hour_time($role) ? 'M j, Y H:i' : 'M j, Y g:i A');
            }
        @endphp

        <x-page-header
            :title="$newsletter->subject"
            :lead="$sentLead"
            :back="route('newsletter.index', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)])"
            :back-label="__('messages.newsletters')" />

        @include('newsletter.partials._stats')
    </div>

    @include('newsletter.partials._list-script', ['defaultSort' => 'opened_at'])
</x-app-admin-layout>
