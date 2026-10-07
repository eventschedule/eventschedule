<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
    </x-slot>

    {{-- One segment: its name, who it resolves to, and for a manual one the way to add and change
         the people in it. --}}
    <div class="page-shell">
        @php
            $roleParam = \App\Utils\UrlUtils::encodeId($role->id);
            $segmentParams = ['role_id' => $roleParam, 'hash' => \App\Utils\UrlUtils::encodeId($segment->id)];
            $isManual = $segment->type === 'manual';
            $subscriberList = $isManual ? $subscribers->items() : $subscribers;
        @endphp

        <x-page-header
            :title="$segment->name"
            :back="route('newsletter.segments', ['role_id' => $roleParam])"
            :back-label="__('messages.segments')" />

        @include('newsletter.partials._notices')

        <div class="page-stack">
            <x-page-card beside :title="__('messages.edit_segment')">
                <form method="POST" action="{{ route('newsletter.segment.update', $segmentParams) }}">
                    @csrf
                    @method('PUT')
                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="segment_name" :value="__('messages.name')" />
                            <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" :value="$segment->name" required />
                        </div>

                        <dl class="news-facts">
                            <div><dt>{{ __('messages.type') }}</dt> <dd>{{ \App\Models\NewsletterSegment::typeLabel($segment->type) }}</dd></div>
                            @if (! empty($eventName))
                            <div><dt>{{ __('messages.event') }}</dt> <dd><bdi>{{ $eventName }}</bdi></dd></div>
                            @endif
                            <div><dt>{{ __('messages.recipients') }}</dt> <dd>{{ number_format($recipientCount) }}</dd></div>
                            <div><dt>{{ __('messages.created') }}</dt> <dd>{{ $segment->created_at->translatedFormat('M j, Y') }}</dd></div>
                        </dl>
                    </div>

                    <div class="page-form-actions">
                        <x-brand-button type="submit">{{ __('messages.save_changes') }}</x-brand-button>
                    </div>
                </form>
            </x-page-card>

            {{-- Add subscriber (manual segments only) --}}
            @if ($isManual)
            <x-page-card beside :title="__('messages.add_subscriber')">
                <form method="POST" action="{{ route('newsletter.segment.user.store', $segmentParams) }}" class="news-inline-form" autocomplete="off">
                    @csrf
                    <x-text-input name="name" type="text" class="is-grow" :placeholder="__('messages.name')" :aria-label="__('messages.name')" data-1p-ignore data-lpignore="true" />
                    <x-text-input name="email" type="email" class="is-grow" :placeholder="__('messages.email')" :aria-label="__('messages.email')" required data-1p-ignore data-lpignore="true" />
                    <x-brand-button type="submit">{{ __('messages.add_subscriber') }}</x-brand-button>
                </form>
                <x-slot name="foot">
                    <x-link href="{{ route('newsletter.import', ['role_id' => $roleParam]) }}">{{ __('messages.import_in_bulk') }}</x-link>
                </x-slot>
            </x-page-card>
            @endif

            {{-- Subscribers --}}
            <x-page-card flush :title="__('messages.subscribers') . ' (' . number_format($recipientCount) . ')'">
                @if (count($subscriberList) > 0)
                <table class="page-table">
                    <thead>
                        <tr>
                            @if ($isManual)
                            <x-page-sort column="name" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.name') }}</x-page-sort>
                            <x-page-sort column="email" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.email') }}</x-page-sort>
                            <x-page-sort column="created_at" :sortBy="$sortBy" :sortDir="$sortDir">{{ __('messages.date_added') }}</x-page-sort>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                            @else
                            <th scope="col">{{ __('messages.name') }}</th>
                            <th scope="col">{{ __('messages.email') }}</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriberList as $subscriber)
                        @if ($isManual)
                        @php $encodedId = \App\Utils\UrlUtils::encodeId($subscriber->id); @endphp
                        {{-- Display row --}}
                        <tr data-display-row="{{ $encodedId }}">
                            <td class="c-main c-strong">@if ($subscriber->name)<x-user-text><bdi>{{ $subscriber->name }}</bdi></x-user-text>@else<span class="c-quiet italic font-normal">{{ __('messages.no_name') }}</span>@endif</td>
                            <td class="c-wrap">{{ $subscriber->email }}</td>
                            <td class="c-date">{{ $subscriber->created_at?->translatedFormat('M j, Y') }}</td>
                            <td class="c-actions">
                                <button type="button" data-segment="{{ $encodedId }}" class="js-segment-edit-toggle event-link">{{ __('messages.edit') }}</button>
                                <form method="POST" action="{{ route('newsletter.segment.user.delete', $segmentParams + ['userHash' => $encodedId]) }}" class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                        </tr>
                        {{-- Edit row --}}
                        <tr data-edit-row="{{ $encodedId }}" class="news-edit-row" hidden>
                            <td colspan="4">
                                <form method="POST" action="{{ route('newsletter.segment.user.update', $segmentParams + ['userHash' => $encodedId]) }}" class="news-inline-form">
                                    @csrf
                                    @method('PUT')
                                    <x-text-input name="name" type="text" class="is-grow text-sm" :value="$subscriber->name" :placeholder="__('messages.name')" :aria-label="__('messages.name')" />
                                    <x-text-input name="email" type="email" class="is-grow text-sm" :value="$subscriber->email" :placeholder="__('messages.email')" :aria-label="__('messages.email')" />
                                    <button type="button" class="js-segment-edit-toggle page-tool">{{ __('messages.cancel') }}</button>
                                    <x-brand-button type="submit" size="sm">{{ __('messages.save_changes') }}</x-brand-button>
                                </form>
                            </td>
                        </tr>
                        @else
                        {{-- Read-only row for a segment that is worked out, not kept by hand --}}
                        <tr>
                            <td class="c-main c-strong">@if ($subscriber->name)<x-user-text><bdi>{{ $subscriber->name }}</bdi></x-user-text>@else<span class="c-quiet italic font-normal">{{ __('messages.no_name') }}</span>@endif</td>
                            <td class="c-wrap">{{ $subscriber->email }}</td>
                        </tr>
                        @endif
                        @endforeach
                    </tbody>
                </table>

                @if ($isManual && $subscribers instanceof \Illuminate\Pagination\LengthAwarePaginator && $subscribers->hasPages())
                <div class="page-card-foot">
                    {{ $subscribers->links() }}
                </div>
                @endif

                @if (! $isManual && $recipientCount > 50)
                <div class="page-card-foot">{{ __('messages.showing_first_of', ['count' => number_format($recipientCount)]) }}</div>
                @endif
                @else
                <x-page-empty compact :title="__('messages.no_subscribers')" />
                @endif
            </x-page-card>
        </div>
    </div>

    @include('newsletter.partials._list-script')
    <script {!! nonce_attr() !!}>
        {{-- One row at a time is changed in place: its line gives way to a small form. The rows
             are shown and put away with the hidden attribute, never an inline style. --}}
        (function() {
            var currentEditId = null;
            function show(id, editing) {
                var display = document.querySelector('[data-display-row="' + id + '"]');
                var edit = document.querySelector('[data-edit-row="' + id + '"]');
                if (display && edit) {
                    display.hidden = editing;
                    edit.hidden = ! editing;
                }
            }
            document.addEventListener('click', function(e) {
                var toggle = e.target.closest ? e.target.closest('.js-segment-edit-toggle') : null;
                if (! toggle) {
                    return;
                }
                var id = toggle.getAttribute('data-segment') || null;
                if (currentEditId) {
                    show(currentEditId, false);
                }
                currentEditId = id;
                if (id) {
                    show(id, true);
                    var first = document.querySelector('[data-edit-row="' + id + '"] input[name="name"]');
                    if (first) {
                        first.focus();
                    }
                }
            });
        })();
    </script>
</x-app-admin-layout>
