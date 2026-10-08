<x-app-admin-layout>

    {{-- A feed's own settings, at the form's measure: its name, the two choices it was added
         with, and what its events are filed under. Removing it is here too, after the form and
         apart from it. The address itself is never shown: for a private calendar it is the key. --}}
    @php
        $here = ['subdomain' => $role->subdomain, 'hash' => \App\Utils\UrlUtils::encodeId($feed->id)];
        $feedUrl = route('role.feeds.show', $here);
        $categories = $role->getEventCategories();
        $select = 'mt-1 block w-full rounded-lg border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]';
        $group = old('group_id', $feed->group_id ? \App\Utils\UrlUtils::encodeId($feed->group_id) : '');
    @endphp

    @include('feed.partials.styles')

    <div class="page-shell page-col is-narrow">
        <x-page-header :title="__('messages.feeds_edit')" :lead="__('messages.feeds_edit_lead')" :back="$feedUrl" :back-label="$feed->name" />

        <form method="post" action="{{ route('role.feeds.update', $here) }}">
            @csrf
            @method('put')
            <div class="page-stack">
                <section class="ap-card rounded-xl page-card">
                    <div class="page-form-fields">
                        <div>
                            <span class="block font-medium text-sm text-gray-700 dark:text-gray-300">{{ __('messages.feeds_reads_from') }}</span>
                            <p class="mt-1 text-sm text-gray-900 dark:text-gray-100"><span class="feed-addr" dir="ltr">{{ $feed->host }}</span> &middot; {{ __('messages.feeds_kind_'.$feed->kind) }}</p>
                            <p class="event-hint">{{ __('messages.feeds_reads_from_help') }}</p>
                        </div>
                        <div>
                            <x-input-label for="feed-name" :value="__('messages.name')" />
                            <x-text-input id="feed-name" name="name" type="text" class="mt-1 block w-full" maxlength="120" required :value="old('name', $feed->name)" />
                            <p class="event-hint">{{ __('messages.feeds_name_help') }}</p>
                            <x-input-error class="mt-2" :messages="$errors->get('name')" />
                        </div>
                    </div>
                </section>

                <section class="ap-card rounded-xl page-card">
                    <div class="feed-block">
                        <h3 id="feed-new">{{ __('messages.feeds_new_title') }}</h3>
                        <p>{{ __('messages.feeds_new_help') }}</p>
                        <div class="event-tiles feed-tiles-2" role="radiogroup" aria-labelledby="feed-new">
                            @foreach (['publish', 'draft'] as $mode)
                            <label class="event-tile">
                                <input type="radio" name="publish_mode" value="{{ $mode }}" {{ old('publish_mode', $feed->publish_mode) === $mode ? 'checked' : '' }}>
                                <span class="event-tile-title">{{ __('messages.feeds_new_'.$mode) }}</span>
                                <span class="event-tile-help">{{ __('messages.feeds_new_'.$mode.'_help') }}</span>
                            </label>
                            @endforeach
                        </div>
                    </div>

                    <div class="feed-block">
                        <h3 id="feed-gone">{{ __('messages.feeds_gone_title') }}</h3>
                        @if ($feed->can_see_leaving)
                        <p>{{ __('messages.feeds_gone_help') }}</p>
                        <div class="event-tiles feed-tiles-3" role="radiogroup" aria-labelledby="feed-gone">
                            @foreach (['keep', 'cancel', 'delete'] as $action)
                            <label class="event-tile">
                                <input type="radio" name="left_action" value="{{ $action }}" {{ old('left_action', $feed->left_action) === $action ? 'checked' : '' }}>
                                <span class="event-tile-title">{{ __('messages.feeds_gone_'.$action) }}</span>
                                <span class="event-tile-help">{{ __('messages.feeds_gone_'.$action.'_help') }}</span>
                            </label>
                            @endforeach
                        </div>
                        @else
                        <p>{{ __('messages.feeds_gone_cannot') }}</p>
                        @endif
                    </div>

                    <div class="feed-block">
                        <div class="page-form-fields">
                            @if ($role->groups->isNotEmpty())
                            <div>
                                <x-input-label for="feed-group" :value="__('messages.subschedule')" />
                                <select id="feed-group" name="group_id" class="{{ $select }}">
                                    <option value="">{{ __('messages.none') }}</option>
                                    @foreach ($role->groups as $option)
                                    <option value="{{ \App\Utils\UrlUtils::encodeId($option->id) }}" {{ $group === \App\Utils\UrlUtils::encodeId($option->id) ? 'selected' : '' }}>{{ $option->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @endif
                            <div>
                                <x-input-label for="feed-category" :value="__('messages.category')" />
                                <select id="feed-category" name="category_id" class="{{ $select }}">
                                    <option value="">{{ __('messages.feeds_category_auto') }}</option>
                                    @foreach ($categories as $category)
                                    <option value="{{ $category['id'] }}" {{ (string) old('category_id', $feed->category_id) === (string) $category['id'] ? 'selected' : '' }}>{{ $category['name'] }}</option>
                                    @endforeach
                                </select>
                                <p class="event-hint">{{ __('messages.feeds_category_help') }}</p>
                            </div>
                            <div>
                                <x-input-label for="feed-zone" :value="__('messages.feeds_zone')" />
                                <select id="feed-zone" name="source_timezone" class="{{ $select }}">
                                    @foreach (timezone_identifiers_list() as $zone)
                                    <option value="{{ $zone }}" {{ old('source_timezone', $feed->source_timezone) === $zone ? 'selected' : '' }}>{{ str_replace('_', ' ', $zone) }}</option>
                                    @endforeach
                                </select>
                                <p class="event-hint">{{ __('messages.feeds_zone_edit_help') }}</p>
                                <x-input-error class="mt-2" :messages="$errors->get('source_timezone')" />
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="page-form-actions">
                <x-secondary-link :href="$feedUrl">{{ __('messages.cancel') }}</x-secondary-link>
                <x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button>
            </div>
        </form>

        {{-- Removing it, with the one thing to choose: what becomes of its events. --}}
        <x-page-card class="mt-4" :title="__('messages.feeds_remove')" :lead="__('messages.feeds_remove_text')">
            <form method="post" action="{{ route('role.feeds.destroy', $here) }}" data-confirm="{{ __('messages.feeds_remove_title') }}">
                @csrf
                @method('delete')
                <label class="feed-radio"><input type="radio" name="its_events" value="keep" checked><span>{{ __('messages.feeds_remove_keep') }}<small>{{ __('messages.feeds_remove_keep_help') }}</small></span></label>
                @if ($eventsCount > 0)
                <label class="feed-radio"><input type="radio" name="its_events" value="delete"><span>{{ __('messages.feeds_remove_delete') }}<small>{{ __('messages.feeds_remove_delete_help') }}</small></span></label>
                @endif
                <div class="page-form-actions" style="justify-content:flex-start">
                    <x-danger-button type="submit">{{ __('messages.feeds_remove') }}</x-danger-button>
                </div>
            </form>
        </x-page-card>
    </div>
</x-app-admin-layout>
