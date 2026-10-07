<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
    </x-slot>

    {{-- The first tab of the newsletters section: what has been written and sent for one
         schedule. One list for every width (.page-table): a table from a tablet up, a stack of
         rows on a phone, where the old table ran off the edge with Delete out of reach. --}}
    <div class="page-shell">
        @include('newsletter.partials._section', ['tab' => 'newsletters'])

        @if (! $role)
        <div class="ap-card rounded-xl">
            <x-page-empty
                :title="__('messages.no_schedules')"
                :text="__('messages.create_schedule_first')"
                icon="M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75" />
        </div>
        @else
        @include('newsletter.partials._verification-warning')

        <div class="page-head">
            <p class="page-lead">{{ __('messages.newsletters_list_lead') }}</p>
            @include('newsletter.partials._usage-meter')
        </div>

        @if ($newsletters->count() > 0)
        @php
            $roleParam = \App\Utils\UrlUtils::encodeId($role->id);
            $readerZone = auth()->user()->timezone ?? $role->timezone ?? 'UTC';
            $timeFormat = get_use_24_hour_time($role) ? 'M j, H:i' : 'M j, g:i A';
            $statusTones = ['scheduled' => 'is-warn', 'sending' => 'is-info', 'sent' => 'is-on'];
        @endphp
        <div class="ap-card rounded-xl overflow-hidden">
            <table class="page-table is-hover">
                <thead>
                    <tr>
                        <x-page-sort column="subject" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.subject') }}</x-page-sort>
                        <x-page-sort column="status" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.status') }}</x-page-sort>
                        <x-page-sort column="sent_count" :sortBy="$sortBy" :sortDir="$sortDir" class="c-num">{{ __('messages.recipients') }}</x-page-sort>
                        <x-page-sort column="open_rate" :sortBy="$sortBy" :sortDir="$sortDir" class="c-num">{{ __('messages.open_rate') }}</x-page-sort>
                        <x-page-sort column="click_rate" :sortBy="$sortBy" :sortDir="$sortDir" class="c-num">{{ __('messages.click_rate') }}</x-page-sort>
                        <x-page-sort column="created_at" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.created') }}</x-page-sort>
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
                            <a href="{{ $wasSent ? route('newsletter.stats', ['hash' => $hash, 'role_id' => $roleParam]) : route('newsletter.edit', ['hash' => $hash, 'role_id' => $roleParam]) }}" class="event-link"><bdi>{{ $newsletter->subject }}</bdi></a>
                            @if ($newsletter->ab_variant)
                            <span class="event-chip">{{ $newsletter->ab_variant }}</span>
                            @endif
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
                            <a href="{{ route('newsletter.edit', ['hash' => $hash, 'role_id' => $roleParam]) }}" class="event-link">{{ __('messages.edit') }}</a>
                            @endif
                            @if ($wasSent)
                            <a href="{{ route('newsletter.stats', ['hash' => $hash, 'role_id' => $roleParam]) }}" class="event-link">{{ __('messages.newsletter_stats') }}</a>
                            @endif
                            <form method="POST" action="{{ route('newsletter.clone', ['hash' => $hash, 'role_id' => $roleParam]) }}">
                                @csrf
                                <button type="submit" class="event-link event-link-quiet">{{ __('messages.clone') }}</button>
                            </form>
                            @if ($newsletter->status !== 'sending')
                            <form method="POST" action="{{ route('newsletter.delete', ['hash' => $hash, 'role_id' => $roleParam]) }}" class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
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
                <x-brand-link href="{{ route('newsletter.create', ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)]) }}">{{ __('messages.create_newsletter') }}</x-brand-link>
            </x-page-empty>
        </div>
        @endif
        @endif
    </div>

    @include('newsletter.partials._list-script', ['defaultSort' => 'created_at'])
</x-app-admin-layout>
