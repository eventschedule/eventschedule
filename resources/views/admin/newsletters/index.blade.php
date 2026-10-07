<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'newsletters'])

    {{-- The platform's own newsletters, to the people who have an account. The same list a
         schedule's owner has (.page-table: a table from a tablet up, rows on a phone). --}}
    <div class="page-shell">
        @include('admin.newsletters.partials._section', ['tab' => 'newsletters'])

        <div class="page-head">
            <p class="page-lead">{{ __('messages.admin_newsletters_lead') }}</p>
            <div class="page-actions">
                <x-brand-link href="{{ route('admin.newsletters.create') }}">
                    <svg class="-ms-0.5 me-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                        <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    {{ __('messages.create_admin_newsletter') }}
                </x-brand-link>
            </div>
        </div>

        @if ($newsletters->count() > 0)
        @php
            $readerZone = auth()->user()->timezone ?? 'UTC';
            $timeFormat = get_use_24_hour_time(null) ? 'M j, H:i' : 'M j, g:i A';
            $statusTones = ['scheduled' => 'is-warn', 'sending' => 'is-info', 'sent' => 'is-on'];
        @endphp
        <div class="ap-card rounded-xl overflow-hidden">
            <table class="page-table is-hover">
                <thead>
                    <tr>
                        <th scope="col">{{ __('messages.subject') }}</th>
                        <th scope="col">{{ __('messages.status') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.recipients') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.open_rate') }}</th>
                        <th scope="col" class="c-num">{{ __('messages.click_rate') }}</th>
                        <th scope="col">{{ __('messages.created') }}</th>
                        <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($newsletters as $newsletter)
                    @php
                        $hash = \App\Utils\UrlUtils::encodeId($newsletter->id);
                        $wasSent = in_array($newsletter->status, ['sent', 'sending']);
                        $opens = $newsletter->sent_count > 0 ? round(($newsletter->open_count / $newsletter->sent_count) * 100, 1) . '%' : null;
                        $clicks = $newsletter->sent_count > 0 ? round(($newsletter->click_count / $newsletter->sent_count) * 100, 1) . '%' : null;
                        // The day that goes with the status: when it went out, or when it will.
                        $statusAt = $newsletter->status === 'scheduled' ? $newsletter->scheduled_at : ($wasSent ? $newsletter->sent_at : null);
                    @endphp
                    <tr>
                        <td class="c-main c-strong">
                            <a href="{{ $wasSent ? route('admin.newsletters.stats', ['hash' => $hash]) : route('admin.newsletters.edit', ['hash' => $hash]) }}" class="event-link"><bdi>{{ $newsletter->subject }}</bdi></a>
                        </td>
                        <td>
                            <span class="event-status {{ $statusTones[$newsletter->status] ?? '' }}">{{ __('messages.newsletter_status_' . $newsletter->status) }}</span>
                            @if ($statusAt)
                            <span class="c-sub whitespace-nowrap">{{ $statusAt->copy()->timezone($readerZone)->translatedFormat($timeFormat) }}</span>
                            @endif
                        </td>
                        <td class="c-num" data-label="{{ __('messages.recipients') }}">{{ $wasSent ? number_format($newsletter->sent_count) : '' }}</td>
                        <td class="c-num" data-label="{{ __('messages.open_rate') }}">{{ $opens }}</td>
                        <td class="c-num" data-label="{{ __('messages.click_rate') }}">{{ $clicks }}</td>
                        <td class="c-date">{{ $newsletter->created_at->translatedFormat('M j, Y') }}</td>
                        <td class="c-actions">
                            @if ($newsletter->status === 'draft' || $newsletter->status === 'scheduled')
                            <a href="{{ route('admin.newsletters.edit', ['hash' => $hash]) }}" class="event-link">{{ __('messages.edit') }}</a>
                            @endif
                            @if ($wasSent)
                            <a href="{{ route('admin.newsletters.stats', ['hash' => $hash]) }}" class="event-link">{{ __('messages.newsletter_stats') }}</a>
                            @endif
                            <form method="POST" action="{{ route('admin.newsletters.clone', ['hash' => $hash]) }}">
                                @csrf
                                <button type="submit" class="event-link event-link-quiet">{{ __('messages.clone') }}</button>
                            </form>
                            @if ($newsletter->status !== 'sending')
                            <form method="POST" action="{{ route('admin.newsletters.delete', ['hash' => $hash]) }}" class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
                            </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if ($newsletters->hasPages())
        <div class="page-pager">
            {{ $newsletters->links() }}
        </div>
        @endif
        @else
        <div class="ap-card rounded-xl">
            <x-page-empty
                :title="__('messages.no_newsletters')"
                :text="__('messages.no_newsletters_description')"
                icon="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75">
                <x-brand-link href="{{ route('admin.newsletters.create') }}">{{ __('messages.create_admin_newsletter') }}</x-brand-link>
            </x-page-empty>
        </div>
        @endif
    </div>

    @include('newsletter.partials._list-script')
</x-app-admin-layout>
