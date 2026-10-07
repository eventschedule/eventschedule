<x-app-admin-layout>
    <x-slot name="head">
        @include('newsletter.partials._styles')
        <style {!! nonce_attr() !!}>
            /* The accounts a search finds, under the field that searches. */
            .news-results {
              position: absolute;
              z-index: 10;
              inset-inline: 0;
              max-height: 15rem;
              margin-top: 0.25rem;
              border: 1px solid rgb(var(--ap-border));
              border-radius: 0.5rem;
              background: rgb(var(--ap-surface));
              box-shadow: var(--ap-shadow-dropdown);
              overflow-y: auto;
            }
            .news-result {
              padding: 0.5rem 0.75rem;
              font-size: 0.875rem;
              color: rgb(var(--ap-ink));
              cursor: pointer;
            }
            .news-result:hover {
              background: rgb(var(--ap-surface-hover));
            }
            .news-result span {
              margin-inline-start: 0.5rem;
              color: rgb(var(--ap-ink-3));
            }
        </style>
    </x-slot>

    @include('admin.partials._navigation', ['active' => 'newsletters'])

    {{-- One of the platform's segments: its name, who it resolves to, and for a manual one the
         accounts in it. --}}
    <div class="page-shell">
        @php
            $segmentHash = \App\Utils\UrlUtils::encodeId($segment->id);
            $isManual = $segment->type === 'manual';
            $subscriberList = $isManual ? $subscribers->items() : $subscribers;
        @endphp

        @include('admin.newsletters.partials._subpage-head', [
            'title' => $segment->name,
            'back' => route('admin.newsletters.segments'),
            'backLabel' => __('messages.segments'),
        ])

        @include('newsletter.partials._notices')

        <div class="page-stack">
            <x-page-card beside :title="__('messages.edit_segment')">
                <form method="POST" action="{{ route('admin.newsletters.segment.update', ['hash' => $segmentHash]) }}">
                    @csrf
                    @method('PUT')
                    <div class="page-form-fields">
                        <div>
                            <x-input-label for="segment_name" :value="__('messages.name')" />
                            <x-text-input id="segment_name" name="name" type="text" class="mt-1 block w-full" :value="$segment->name" required />
                        </div>

                        <dl class="news-facts">
                            <div><dt>{{ __('messages.type') }}</dt> <dd>@include('admin.newsletters.partials._segment-type')</dd></div>
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
                <form method="POST" action="{{ route('admin.newsletters.segment.user.store', ['hash' => $segmentHash]) }}" id="add-user-form" class="news-inline-form">
                    @csrf
                    <input type="hidden" name="user_id" id="selected-user-id">
                    <div class="is-grow relative">
                        <x-text-input type="text" id="user-search-input" class="block w-full" :placeholder="__('messages.search_users')" :aria-label="__('messages.search_users')" autocomplete="off" />
                        <div id="user-search-results" class="news-results hidden"></div>
                    </div>
                    <x-brand-button type="submit" id="add-user-btn" disabled>{{ __('messages.add_subscriber') }}</x-brand-button>
                </form>
            </x-page-card>
            @endif

            {{-- Subscribers --}}
            <x-page-card flush :title="__('messages.subscribers') . ' (' . number_format($recipientCount) . ')'">
                @if (count($subscriberList) > 0)
                <table class="page-table">
                    <thead>
                        <tr>
                            <th scope="col">{{ __('messages.name') }}</th>
                            <th scope="col">{{ __('messages.email') }}</th>
                            @if ($isManual)
                            <th scope="col">{{ __('messages.date_added') }}</th>
                            <th scope="col"><span class="sr-only">{{ __('messages.actions') }}</span></th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($subscriberList as $subscriber)
                        <tr>
                            <td class="c-main c-strong">@if ($subscriber->name)<x-user-text><bdi>{{ $subscriber->name }}</bdi></x-user-text>@else<span class="c-quiet italic font-normal">{{ __('messages.no_name') }}</span>@endif</td>
                            <td class="c-wrap">{{ $subscriber->email }}</td>
                            @if ($isManual)
                            <td class="c-date">{{ $subscriber->created_at?->translatedFormat('M j, Y') }}</td>
                            <td class="c-actions">
                                <form method="POST" action="{{ route('admin.newsletters.segment.user.delete', ['hash' => $segmentHash, 'userHash' => \App\Utils\UrlUtils::encodeId($subscriber->id)]) }}" class="js-confirm-form" data-confirm="{{ __('messages.are_you_sure') }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="event-link is-danger">{{ __('messages.delete') }}</button>
                                </form>
                            </td>
                            @endif
                        </tr>
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
        @if ($segment->type === 'manual')
        // User search autocomplete
        (function() {
            const searchInput = document.getElementById('user-search-input');
            const resultsContainer = document.getElementById('user-search-results');
            const userIdInput = document.getElementById('selected-user-id');
            const addBtn = document.getElementById('add-user-btn');
            let searchTimer;

            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimer);
                const q = this.value.trim();

                // Reset selection when user types
                userIdInput.value = '';
                addBtn.disabled = true;

                if (q.length < 2) {
                    resultsContainer.classList.add('hidden');
                    return;
                }

                searchTimer = setTimeout(async function() {
                    try {
                        // X-Requested-With makes expectsJson() true, so a lapsed admin re-auth
                        // window answers 423 instead of a 302 that fetch follows into HTML -
                        // which res.json() would then choke on inside the silent catch below,
                        // leaving the autocomplete permanently and inexplicably dead.
                        const res = await fetch('{{ route("admin.users.search") }}?q=' + encodeURIComponent(q), {
                            headers: { 'X-Requested-With': 'XMLHttpRequest', 'Accept': 'application/json' }
                        });
                        if (!res.ok) throw new Error('Request failed');
                        const data = await res.json();

                        if (data.length > 0) {
                            resultsContainer.innerHTML = '';
                            data.forEach(function(user) {
                                const div = document.createElement('div');
                                div.className = 'news-result';
                                div.innerHTML = '<bdi>' + escapeHtml(user.name || '') + '</bdi>' +
                                    '<span>' + escapeHtml(user.email) + '</span>';
                                div.addEventListener('click', function() {
                                    userIdInput.value = user.id;
                                    searchInput.value = (user.name ? user.name + ' - ' : '') + user.email;
                                    resultsContainer.classList.add('hidden');
                                    addBtn.disabled = false;
                                });
                                resultsContainer.appendChild(div);
                            });
                            resultsContainer.classList.remove('hidden');
                        } else {
                            resultsContainer.classList.add('hidden');
                        }
                    } catch (err) {
                        resultsContainer.classList.add('hidden');
                    }
                }, 300);
            });

            // Hide results when clicking outside
            document.addEventListener('click', function(e) {
                if (!searchInput.contains(e.target) && !resultsContainer.contains(e.target)) {
                    resultsContainer.classList.add('hidden');
                }
            });

            function escapeHtml(text) {
                const div = document.createElement('div');
                div.textContent = text;
                return div.innerHTML;
            }
        })();
        @endif
    </script>
</x-app-admin-layout>
