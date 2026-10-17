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

        @php
            $statsParams = ['role_id' => \App\Utils\UrlUtils::encodeId($role->id), 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)];
            $secondaryClasses = 'ap-secondary-btn inline-flex items-center justify-center px-4 py-3 border border-gray-300 dark:border-gray-600 rounded-lg font-semibold text-base text-gray-900 dark:text-gray-100 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)] focus:ring-offset-2 dark:focus:ring-offset-gray-800';
        @endphp

        <x-page-header
            :title="$newsletter->subject"
            :lead="$sentLead"
            :back="route('newsletter.index', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)])"
            :back-label="__('messages.newsletters')">
            {{-- What went out, and a copy to write the next one from. Clone is the only way a sent
                 newsletter could be looked at before. --}}
            <x-slot name="actions">
                <button type="button" class="{{ $secondaryClasses }}" id="news-view-mail" data-url="{{ route('newsletter.preview', $statsParams) }}">{{ __('messages.newsletter_view_email') }}</button>
                <form method="POST" action="{{ route('newsletter.clone', $statsParams) }}">
                    @csrf
                    <button type="submit" class="{{ $secondaryClasses }}">{{ __('messages.clone') }}</button>
                </form>
            </x-slot>
        </x-page-header>

        @include('newsletter.partials._stats')
    </div>

    @include('newsletter.partials._mail-dialog')
    @include('newsletter.partials._list-script', ['defaultSort' => 'opened_at'])
    @include('newsletter.partials._sending-refresh', ['sending' => ! empty($progress)])
</x-app-admin-layout>
