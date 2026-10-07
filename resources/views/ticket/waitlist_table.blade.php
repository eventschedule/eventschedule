{{-- The people waiting for a place at a sold-out event.

     WaitlistController::index() answers /waitlist with this view either way. Fetched by the
     Waitlist tab of ticket/sales (X-Requested-With), it is the list alone. VISITED, it used to be
     the same bare fragment: a table with no stylesheet, no sidebar and no way back, which is what
     anyone typing the address (and the route-load tests) got. A visit now gets a whole page, which
     draws this view a second time for its list. --}}
@if (! request()->ajax() && empty($embedded))
<x-app-admin-layout>
    <div class="page-shell">
        <x-page-header :title="__('messages.waitlist')" :lead="__('messages.waitlist_lead')"
                       :back="route('sales')" :back-label="__('messages.sales')">
            <x-slot name="actions">
                <a href="{{ request()->fullUrlWithQuery(['include_past' => request()->query('include_past') == 1 ? null : 1, 'page' => null]) }}" class="page-tool">
                    {{ request()->query('include_past') == 1 ? __('messages.hide_past_events') : __('messages.show_past_events') }}
                </a>
            </x-slot>
        </x-page-header>

        <div id="waitlist-table">
            @include('ticket.waitlist_table', ['embedded' => true])
        </div>
    </div>

    <script {!! nonce_attr() !!}>
    // A page of its own reloads where the tab re-fetches: sorting is an address, and removing
    // someone brings the page back without them.
    document.addEventListener('click', function(e) {
        var header = e.target.closest('[data-sort]');
        if (header) {
            var url = new URL(window.location.href);
            var sortBy = header.getAttribute('data-sort');
            var same = (url.searchParams.get('sort_by') || 'created_at') === sortBy;
            url.searchParams.set('sort_dir', same && (url.searchParams.get('sort_dir') || 'desc') === 'asc' ? 'desc' : 'asc');
            url.searchParams.set('sort_by', sortBy);
            url.searchParams.delete('page');
            window.location.href = url.toString();
            return;
        }

        var remove = e.target.closest('.js-waitlist-remove');
        if (! remove || ! confirm(@json(__('messages.are_you_sure')))) {
            return;
        }
        remove.disabled = true;
        fetch(@json(url('/waitlist/remove')) + '/' + remove.getAttribute('data-id'), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(function(response) {
            if (! response.ok) throw new Error('Request failed');
            window.location.reload();
        })
        .catch(function() {
            remove.disabled = false;
            alert(@json(__('messages.an_error_occurred')));
        });
    });
    </script>
</x-app-admin-layout>
@elseif ($entries->count() > 0)
<div class="ap-card rounded-xl overflow-hidden">
    <div class="page-scroll">
        {{-- Six columns where there were seven: the address sits under the name, as it does in
             every other list of the Sales page, so the list fits a laptop without scrolling. --}}
        <table class="page-table is-hover">
            <thead>
                <tr>
                    <x-page-sort column="name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.name') }}</x-page-sort>
                    <x-page-sort column="event_name" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.event') }}</x-page-sort>
                    <x-page-sort column="event_date" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.date') }}</x-page-sort>
                    <x-page-sort column="status" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.status') }}</x-page-sort>
                    <x-page-sort column="created_at" :sortBy="$sortBy ?? ''" :sortDir="$sortDir ?? 'desc'">{{ __('messages.created_at') }}</x-page-sort>
                    <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                </tr>
            </thead>
            <tbody>
                @foreach ($entries as $entry)
                @php
                    $waitlistTone = ['waiting' => 'is-info', 'notified' => 'is-warn', 'purchased' => 'is-on'][$entry->status] ?? '';
                    $waitlistWord = in_array($entry->status, ['waiting', 'notified', 'purchased']) ? $entry->status : 'expired';
                    // event_date is the day as the venue counts it, stored as text.
                    try {
                        $waitlistDay = $entry->event_date ? \Carbon\Carbon::parse($entry->event_date)->translatedFormat('M j, Y') : '';
                    } catch (\Exception $waitlistDayError) {
                        $waitlistDay = $entry->event_date;
                    }
                @endphp
                <tr>
                    <td class="c-main">
                        <span class="c-strong"><bdi>{{ $entry->name }}</bdi></span>
                        <span class="c-sub"><a href="mailto:{{ $entry->email }}" class="event-link sm:whitespace-nowrap" dir="ltr">{{ $entry->email }}</a></span>
                    </td>
                    <td class="c-wrap"><bdi>{{ $entry->event?->name }}</bdi></td>
                    <td class="c-date">{{ $waitlistDay }}</td>
                    <td><span class="event-status {{ $waitlistTone }}">{{ __('messages.'.$waitlistWord) }}</span></td>
                    <td class="c-date" data-label="{{ __('messages.created_at') }}">{{ $entry->created_at->translatedFormat('M j, Y g:ia') }}</td>
                    <td class="c-actions">
                        <button type="button" data-id="{{ \App\Utils\UrlUtils::encodeId($entry->id) }}" class="event-link is-danger js-waitlist-remove">{{ __('messages.remove') }}</button>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@if ($entries instanceof \Illuminate\Pagination\LengthAwarePaginator && $entries->hasPages())
<div class="page-pager">
    {{ $entries->links() }}
</div>
@endif
@else
<div class="ap-card rounded-xl">
    <x-page-empty :title="__('messages.waitlist_empty')" :text="request()->query('include_past') == 1 ? null : __('messages.waitlist_empty_upcoming')"
        icon="M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z" />
</div>
@endif
