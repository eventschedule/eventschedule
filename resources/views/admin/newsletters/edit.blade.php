<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        @include('newsletter.partials._builder-styles')
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'newsletters'])

    {{-- A draft or a scheduled platform newsletter in the builder. --}}
    <div class="page-shell">
        @php $newsletterHash = \App\Utils\UrlUtils::encodeId($newsletter->id); @endphp

        @include('admin.newsletters.partials._subpage-head', [
            'title' => __('messages.edit_newsletter'),
            'back' => route('admin.newsletters.index'),
            'backLabel' => __('messages.admin_newsletters'),
        ])

        @include('newsletter.partials._notices')

        @if ($newsletter->status === 'scheduled')
        <x-page-notice tone="info" class="news-notice">
            {{ __('messages.scheduled_for') }}: {{ $newsletter->scheduled_at->timezone(auth()->user()->timezone ?? 'UTC')->translatedFormat(get_use_24_hour_time(null) ? 'M j, Y H:i' : 'M j, Y g:i A') }}
            <x-slot name="action">
                <form method="POST" action="{{ route('admin.newsletters.cancel', ['hash' => $newsletterHash]) }}">
                    @csrf
                    <button type="submit" class="event-link">{{ __('messages.cancel_schedule') }}</button>
                </form>
            </x-slot>
        </x-page-notice>
        @endif

        <form method="POST" action="{{ route('admin.newsletters.update', ['hash' => $newsletterHash]) }}">
            @csrf
            @method('PUT')
            @php
                $isAdmin = true;
                $events = collect();
            @endphp
            @include('newsletter.partials._builder')
        </form>
    </div>
</x-app-admin-layout>
