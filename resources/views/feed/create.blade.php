<x-app-admin-layout>

    {{-- Adding a feed, in two steps on one page. The address is checked first, which reads it
         once and writes nothing; what was found is shown beside the two choices that matter; then
         it is added. The way back names the page it leads to, and each form ends with the button
         that goes on. --}}
    @php
        $feedsUrl = route('role.view_admin', ['subdomain' => $role->subdomain, 'tab' => 'feeds']);
        $kind = $found['kind'] ?? null;
        $canSeeLeaving = $kind ? \App\Services\Feeds\FeedKind::canSeeLeaving($kind) : true;
        $zoneName = fn (string $zone) => str_replace('_', ' ', \Illuminate\Support\Str::afterLast($zone, '/'));
        $count = $found['count'] ?? null;
        $toAdd = $count === null ? null : max(0, $count - ($found['matched'] ?? 0));
        $leftOut = array_sum($found['skipped'] ?? []);
        $categories = $role->getEventCategories();
        $use24 = get_use_24_hour_time($role);
    @endphp

    @include('feed.partials.styles')

    <div class="page-shell page-col is-narrow">
        <x-page-header :title="__('messages.feeds_add')" :lead="__($found ? 'messages.feeds_added_lead' : 'messages.feeds_add_lead')"
            :back="$feedsUrl" :back-label="__('messages.feeds_tab')" />

        @unless ($found)
        {{-- Step one: the address. Posted, never put in a query string: for a private calendar it
             is the key to it. --}}
        <form method="post" action="{{ route('role.feeds.check', ['subdomain' => $role->subdomain]) }}">
            @csrf
            <div class="ap-card rounded-xl page-card">
                <x-input-label for="feed-address" :value="__('messages.feeds_address')" />
                <div class="feed-row-check">
                    <x-text-input id="feed-address" name="address" type="text" inputmode="url" class="block w-full" dir="ltr"
                        :value="$address" placeholder="https://" required autofocus autocomplete="off" autocapitalize="none" spellcheck="false" />
                    <x-brand-button type="submit">{{ __('messages.feeds_check') }}</x-brand-button>
                </div>

                @if ($problem)
                <x-page-notice tone="error" class="mt-4" role="status">
                    @if ($problem['reason'] === 'no_events')
                    <p class="font-semibold">{{ __('messages.feeds_problem_no_events_title') }}</p>
                    <p>{{ __('messages.feeds_problem_no_events_text') }}</p>
                    @elseif ($problem['reason'] === 'no_dates')
                    <p class="font-semibold">{{ __('messages.feeds_problem_no_events_title') }}</p>
                    <p>{{ trans_choice('messages.feeds_problem_no_dates', $problem['posts'], ['count' => number_format($problem['posts'])]) }} {{ __('messages.feeds_problem_no_events_text') }}</p>
                    @else
                    <p>{{ match ($problem['reason']) {
                        'invalid_url' => __('messages.feeds_problem_invalid_url'),
                        'sign_in_wall' => __('messages.feeds_problem_sign_in_wall', ['platform' => $problem['platform']]),
                        'already_added' => __('messages.feeds_problem_already_added'),
                        'google_private' => __('messages.feeds_problem_google_private'),
                        'unreachable' => __('messages.feeds_problem_unreachable'),
                        'http_error' => __('messages.feeds_problem_http_error', ['status' => $problem['status'] ?? '']),
                        'too_large' => __('messages.feeds_problem_too_large'),
                        'unsupported' => __('messages.feeds_problem_unsupported'),
                        default => __('messages.feeds_problem_failed'),
                    } }}</p>
                    @endif
                </x-page-notice>
                @endif

                <ul class="feed-works">
                    <li><b>{{ __('messages.feeds_works_calendar_title') }}</b> {{ __('messages.feeds_works_calendar_text') }}</li>
                    <li><b>{{ __('messages.feeds_works_rss_title') }}</b> {{ __('messages.feeds_works_rss_text') }}</li>
                    <li><b>{{ __('messages.feeds_works_page_title') }}</b> {{ __('messages.feeds_works_page_text') }}</li>
                </ul>
            </div>
        </form>
        @else

        {{-- Step two: what the address holds, and the two choices. Everything on this page from a
             source is somebody else's text: it is printed as text, apart from our own, and kept
             out of any template Vue might compile (v-pre). --}}
        <form method="post" action="{{ route('role.feeds.store', ['subdomain' => $role->subdomain]) }}">
            @csrf
            <input type="hidden" name="feed_token" value="{{ $found['token'] }}">

            <div class="page-stack">
                <section class="ap-card rounded-xl page-card is-flush">
                    <div class="page-card-head">
                        <div class="feed-found" role="status">
                            <span class="feed-found-mark {{ ($count ?? $found['posts']) ? '' : 'is-quiet' }}">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                            </span>
                            <div class="min-w-0">
                                <h2>
                                    @if ($count !== null)
                                    {{ trans_choice('messages.feeds_found_events', $count, ['count' => number_format($count)]) }}
                                    @else
                                    {{ trans_choice('messages.feeds_found_posts', $found['posts'], ['count' => number_format($found['posts'])]) }}
                                    @endif
                                </h2>
                                <p>{{ __('messages.feeds_found_kind_'.$kind) }}</p>
                                @if (($found['matched'] ?? 0) > 0)
                                <p>{{ trans_choice('messages.feeds_found_matched', $found['matched'], ['count' => number_format($found['matched'])]) }}</p>
                                @endif
                                @if ($leftOut > 0)
                                <p>{{ trans_choice('messages.feeds_found_left_out', $leftOut, ['count' => number_format($leftOut)]) }}</p>
                                @endif
                                <p><span class="feed-addr" dir="ltr">{{ $found['host'] }}</span> &nbsp;<a href="{{ route('role.feeds.create', ['subdomain' => $role->subdomain]) }}" class="event-link">{{ __('messages.feeds_change') }}</a></p>
                            </div>
                        </div>
                    </div>

                    @if ($found['sample'])
                    <p class="feed-sample-label">{{ trans_choice('messages.feeds_sample_label', count($found['sample']), ['count' => count($found['sample'])]) }}</p>
                    <ul class="feed-sample">
                        @foreach ($found['sample'] as $row)
                        <li v-pre><b><bdi>{{ $row['name'] }}</bdi></b><span>{{ \App\Services\Feeds\FeedSetup::when($row, $use24) }}@if ($row['venue'] !== '') &middot; <bdi>{{ $row['venue'] }}</bdi>@endif</span></li>
                        @endforeach
                    </ul>
                    <div class="page-card-foot">
                        @if ($found['source_time'] !== '')
                        <span v-pre>{{ __('messages.feeds_time_source', ['time' => $found['source_time']]) }}</span>
                        @endif
                        {{ __('messages.feeds_time_note', ['zone' => $zoneName($found['timezone'])]) }}
                    </div>
                    @endif
                </section>

                <section class="ap-card rounded-xl page-card">
                    <div class="feed-block">
                        <h3 id="feed-new">{{ __('messages.feeds_new_title') }}</h3>
                        <p>{{ __('messages.feeds_new_help') }}</p>
                        <div class="event-tiles feed-tiles-2" role="radiogroup" aria-labelledby="feed-new">
                            <label class="event-tile">
                                <input type="radio" name="publish_mode" value="publish" {{ old('publish_mode') === 'publish' ? 'checked' : '' }}>
                                <span class="event-tile-title">{{ __('messages.feeds_new_publish') }}</span>
                                <span class="event-tile-help">{{ __('messages.feeds_new_publish_help') }}</span>
                            </label>
                            <label class="event-tile">
                                <input type="radio" name="publish_mode" value="draft" {{ old('publish_mode', 'draft') === 'draft' ? 'checked' : '' }}>
                                <span class="event-tile-title">{{ __('messages.feeds_new_draft') }}</span>
                                <span class="event-tile-help">{{ __('messages.feeds_new_draft_help') }}</span>
                            </label>
                        </div>
                        <p class="is-after">{{ __('messages.feeds_new_note') }}</p>
                    </div>

                    <div class="feed-block">
                        <h3 id="feed-gone">{{ __('messages.feeds_gone_title') }}</h3>
                        @if ($canSeeLeaving)
                        <p>{{ __('messages.feeds_gone_help') }}</p>
                        <div class="event-tiles feed-tiles-3" role="radiogroup" aria-labelledby="feed-gone">
                            @foreach (['keep', 'cancel', 'delete'] as $action)
                            <label class="event-tile">
                                <input type="radio" name="left_action" value="{{ $action }}" {{ old('left_action', 'keep') === $action ? 'checked' : '' }}>
                                <span class="event-tile-title">{{ __('messages.feeds_gone_'.$action) }}</span>
                                <span class="event-tile-help">{{ __('messages.feeds_gone_'.$action.'_help') }}</span>
                            </label>
                            @endforeach
                        </div>
                        @else
                        {{-- A feed of posts lists its newest few: it cannot say an event is gone,
                             so the choice is not offered. --}}
                        <p>{{ __('messages.feeds_gone_cannot') }}</p>
                        <input type="hidden" name="left_action" value="keep">
                        @endif
                    </div>

                    <div class="event-subrows" style="margin-top: 1.5rem;">
                        <x-form-row group="feed" tab="more" pane="feed-more" :title="__('messages.feeds_more')"
                            :summary="__('messages.feeds_more_summary', ['zone' => $zoneName($found['timezone'])])" />
                        <div id="feed-more" class="event-subrow-body" hidden>
                            <div class="page-form-fields">
                                <div>
                                    <x-input-label for="feed-name" :value="__('messages.name')" />
                                    <x-text-input id="feed-name" name="name" type="text" class="mt-1 block w-full" maxlength="120" :value="old('name', $found['title'])" />
                                    <x-input-error class="mt-2" :messages="$errors->get('name')" />
                                </div>
                                @if ($role->groups->isNotEmpty())
                                <div>
                                    <x-input-label for="feed-group" :value="__('messages.subschedule')" />
                                    <select id="feed-group" name="group_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <option value="">{{ __('messages.none') }}</option>
                                        @foreach ($role->groups as $group)
                                        <option value="{{ \App\Utils\UrlUtils::encodeId($group->id) }}" {{ old('group_id') === \App\Utils\UrlUtils::encodeId($group->id) ? 'selected' : '' }}>{{ $group->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                @endif
                                <div>
                                    <x-input-label for="feed-category" :value="__('messages.category')" />
                                    <select id="feed-category" name="category_id" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        <option value="">{{ __('messages.feeds_category_auto') }}</option>
                                        @foreach ($categories as $category)
                                        <option value="{{ $category['id'] }}" {{ (string) old('category_id') === (string) $category['id'] ? 'selected' : '' }}>{{ $category['name'] }}</option>
                                        @endforeach
                                    </select>
                                    <p class="event-hint">{{ __('messages.feeds_category_help') }}</p>
                                </div>
                                <div>
                                    <x-input-label for="feed-zone" :value="__('messages.feeds_zone')" />
                                    <select id="feed-zone" name="source_timezone" class="mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
                                        {{-- The shared list, which keeps a zone PHP's own list does not
                                             name: without it the select fell on its first entry. --}}
                                        <x-timezone-options :selected="old('source_timezone', $found['timezone'])" />
                                    </select>
                                    <p class="event-hint">{{ __('messages.feeds_zone_help') }}</p>
                                    <x-input-error class="mt-2" :messages="$errors->get('source_timezone')" />
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="page-form-actions">
                <x-secondary-link :href="$feedsUrl">{{ __('messages.cancel') }}</x-secondary-link>
                {{-- In "Publish them" the button says how many events that publishes, so pressing
                     it is never a surprise. The words are both here; which shows follows the
                     choice above (the script at the foot), and without script it is the plain one. --}}
                <x-brand-button type="submit" id="feed-add">
                    <span data-feed-add="draft">{{ __('messages.feeds_add_submit') }}</span>
                    @if ($toAdd)
                    <span data-feed-add="publish" hidden>{{ trans_choice('messages.feeds_add_and_publish', $toAdd, ['count' => number_format($toAdd)]) }}</span>
                    @endif
                </x-brand-button>
            </div>
        </form>

        @include('partials.form-kit-script')
        <script {!! nonce_attr() !!}>
            document.addEventListener('DOMContentLoaded', function () {
                var publish = document.querySelector('[data-feed-add="publish"]');
                var plain = document.querySelector('[data-feed-add="draft"]');
                if (!publish || !plain) return;
                function show() {
                    var chosen = document.querySelector('input[name="publish_mode"]:checked');
                    var publishing = !!chosen && chosen.value === 'publish';
                    publish.hidden = !publishing;
                    plain.hidden = publishing;
                }
                document.querySelectorAll('input[name="publish_mode"]').forEach(function (input) {
                    input.addEventListener('change', show);
                });
                show();
            });
        </script>
        @endunless
    </div>
</x-app-admin-layout>
