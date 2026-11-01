<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        @include('newsletter.partials._builder-styles')
    </x-slot>

    {{-- A draft or a scheduled newsletter in the builder. --}}
    <div class="page-shell">
        @php
            $roleParam = \App\Utils\UrlUtils::encodeId($role->id);
            $newsletterParams = ['role_id' => $roleParam, 'hash' => \App\Utils\UrlUtils::encodeId($newsletter->id)];
        @endphp

        <x-page-header
            :title="__('messages.edit_newsletter')"
            :back="route('newsletter.index', ['role_id' => $roleParam])"
            :back-label="__('messages.newsletters')">
            <x-slot name="actions">@include('newsletter.partials._usage-meter')</x-slot>
        </x-page-header>

        @include('newsletter.partials._notices')
        @include('newsletter.partials._verification-warning')

        @if ($newsletter->isRepeating())
        @php
            $lastCopy = \App\Models\Newsletter::where('repeat_parent_id', $newsletter->id)->whereIn('status', ['sending', 'sent'])->orderByDesc('id')->first();
            $repeatFormat = get_use_24_hour_time($role) ? 'M j, Y H:i' : 'M j, Y g:i A';
        @endphp
        {{-- A repeating newsletter: what it does, when it next goes, and how it went last time. --}}
        <x-page-notice tone="info" class="news-notice">
            {{ __('messages.newsletter_repeats_notice_' . $newsletter->repeatEvery(), ['date' => $newsletter->scheduled_at->copy()->timezone($newsletter->repeatZone())->translatedFormat($repeatFormat).' ('.$newsletter->repeatZone().')']) }}
            @if ($lastCopy)
            {{ __('messages.newsletter_last_sent_notice', ['date' => ($lastCopy->sent_at ?? $lastCopy->events_as_of ?? $lastCopy->created_at)->copy()->timezone($newsletter->repeatZone())->translatedFormat('M j'), 'count' => number_format($lastCopy->sent_count)]) }}
            @endif
            <x-slot name="action">
                <form method="POST" action="{{ route('newsletter.cancel', $newsletterParams) }}">
                    @csrf
                    <button type="submit" class="event-link">{{ __('messages.newsletter_stop_repeating') }}</button>
                </form>
            </x-slot>
        </x-page-notice>
        @elseif ($newsletter->status === 'scheduled')
        <x-page-notice tone="info" class="news-notice">
            {{ __('messages.scheduled_for') }}: {{ $newsletter->scheduled_at->timezone(auth()->user()->timezone ?? $role->timezone ?? 'UTC')->translatedFormat(get_use_24_hour_time($role) ? 'M j, Y H:i' : 'M j, Y g:i A') }}
            <x-slot name="action">
                <form method="POST" action="{{ route('newsletter.cancel', $newsletterParams) }}">
                    @csrf
                    <button type="submit" class="event-link">{{ __('messages.cancel_schedule') }}</button>
                </form>
            </x-slot>
        </x-page-notice>
        @endif

        <form method="POST" action="{{ route('newsletter.update', $newsletterParams) }}">
            @csrf
            @method('PUT')
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
