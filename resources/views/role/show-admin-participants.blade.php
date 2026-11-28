@php
    $directoryUrl = route('role.view_admin', [$subdomain, 'participants']);
    $encode = fn ($id) => \App\Utils\UrlUtils::encodeId($id);
@endphp
<div class="page-shell participant-directory space-y-4">
    <div class="page-head"><p class="page-lead">{{ __('backstage.lead') }}</p></div>
    @if (! $entry)
        <x-page-card :title="__('messages.add_participant')">
            <form method="post" action="{{ route('role.participants.store', $subdomain) }}" class="page-filters">
                @csrf
                <label>{{ __('messages.someone_new') }} <x-text-input type="text" name="name" maxlength="255" :value="old('name')" required /></label>
                <x-brand-button type="submit">{{ __('messages.add_participant') }}</x-brand-button>
            </form>
        </x-page-card>
        <form method="get" action="{{ $directoryUrl }}" class="page-filters">
            <label>{{ __('messages.name') }} <x-text-input type="search" name="q" maxlength="255" :value="$search" /></label>
            @if ($search !== '')<x-secondary-link :href="$directoryUrl">{{ __('messages.clear') }}</x-secondary-link>@endif
            <x-secondary-button type="submit">{{ __('messages.search') }}</x-secondary-button>
        </form>
        @if ($rows->isEmpty())
            <x-page-empty :title="$search !== '' ? __('messages.no_results_found') : __('backstage.empty')" />
        @else
            <x-page-card flush><table class="page-table"><thead><tr><th>{{ __('messages.name') }}</th><th class="c-actions">{{ __('messages.actions') }}</th></tr></thead><tbody>
                @foreach ($rows as $row)
                    <tr>
                        <td><bdi>{{ $row->name }}</bdi></td>
                        <td class="c-actions">
                            @if ($row->entry_id)
                                <span class="inline-flex items-center gap-3">
                                    <x-link :href="route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'participants', 'entry' => $encode($row->entry_id)])">{{ __('messages.view') }}</x-link>
                                    <x-link :href="route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'participants', 'entry' => $encode($row->entry_id), 'edit' => 1])">{{ __('messages.edit') }}</x-link>
                                </span>
                            @else
                                <form method="post" action="{{ route('role.participants.store', $subdomain) }}">@csrf
                                    <input type="hidden" name="participant" value="{{ $encode($row->participant_id) }}">
                                    <x-secondary-button type="submit">{{ __('messages.add') }}</x-secondary-button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody></table></x-page-card>
            {{ $rows->links() }}
        @endif
    @else
        @php
            $params = ['subdomain' => $subdomain, 'entry' => $encode($entry->entry_id)];
            $editing = request()->boolean('edit') || $errors->any();
            $website = $entry->website;
            $websiteLinkable = is_string($website) && filter_var($website, FILTER_VALIDATE_URL) && in_array(strtolower(parse_url($website, PHP_URL_SCHEME)), ['http', 'https'], true);
            $phoneHref = fn ($number) => 'tel:'.preg_replace('/[^0-9+*#,;]/', '', $number);
        @endphp
        <x-link :href="$directoryUrl">{{ __('messages.participants') }}</x-link>
        <div class="page-head">
            <h2 class="text-xl font-semibold"><bdi>{{ $entry->name }}</bdi></h2>
            <div class="page-actions">
                @if ($editing)
                    <x-secondary-link :href="route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'participants', 'entry' => $encode($entry->entry_id)])">{{ __('messages.cancel') }}</x-secondary-link>
                @else
                    <x-secondary-link :href="route('role.view_admin', ['subdomain' => $subdomain, 'tab' => 'participants', 'entry' => $encode($entry->entry_id), 'edit' => 1])">{{ __('messages.edit') }}</x-secondary-link>
                @endif
            </div>
        </div>
        <x-page-card :title="__('backstage.basic_info')">
            @if ($editing && $entry->isEditableBy(auth()->user()))
                <form method="post" action="{{ route('role.participants.basic_info', $params) }}" class="space-y-3">
                    @csrf @method('put')
                    @foreach (['email' => ['messages.email', 'email'], 'phone' => ['messages.phone_number', 'tel'], 'website' => ['messages.website', 'text']] as $field => [$label, $type])
                        <label class="block">{{ __($label) }} {{ __('messages.label_optional_suffix') }}
                            <x-text-input class="block w-full" :type="$type" :name="$field" :value="old($field, $entry->$field)" />
                        </label>
                    @endforeach
                    <div class="page-form-actions"><x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button></div>
                </form>
            @else
                <dl class="page-kv">
                    @if ($entry->email)<div><dt>{{ __('messages.email') }}</dt><dd><x-link :href="'mailto:'.$entry->email"><bdi>{{ $entry->email }}</bdi></x-link></dd></div>@endif
                    @if ($entry->phone)<div><dt>{{ __('messages.phone_number') }}</dt><dd><x-link :href="$phoneHref($entry->phone)"><bdi dir="ltr">{{ $entry->phone }}</bdi></x-link></dd></div>@endif
                    @if ($entry->website)<div><dt>{{ __('messages.website') }}</dt><dd>@if ($websiteLinkable)<x-link :href="$website" target="_blank" rel="noopener noreferrer"><bdi>{{ $website }}</bdi></x-link>@else<bdi>{{ $website }}</bdi>@endif</dd></div>@endif
                    @if (! $entry->email && ! $entry->phone && ! $entry->website)<div><dd class="text-[rgb(var(--ap-ink-3))]">{{ __('messages.none') }}</dd></div>@endif
                </dl>
            @endif
        </x-page-card>
        <x-page-card :title="__('backstage.promotion')">
            @if ($editing)
                <form method="post" action="{{ route('role.participants.promotion', $params) }}">
                    @csrf @method('put')
                    <textarea name="promotion" rows="4" maxlength="15000" class="w-full" aria-label="{{ __('backstage.promotion') }}">{{ old('promotion', $entry->promotion) }}</textarea>
                    <div class="page-form-actions"><x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button></div>
                </form>
            @elseif ($entry->promotion)
                <p class="whitespace-pre-wrap"><bdi>{{ $entry->promotion }}</bdi></p>
            @else
                <p class="text-[rgb(var(--ap-ink-3))]">{{ __('messages.none') }}</p>
            @endif
        </x-page-card>
        @foreach (['contacts' => __('backstage.contacts'), 'files' => __('backstage.files'), 'links' => __('messages.social_links')] as $kind => $heading)
            <x-page-card :title="$heading">
                @php
                    $items = $details[$kind];
                    $visibleItems = $kind === 'contacts' ? $items->take(4) : $items;
                    $hiddenItems = $kind === 'contacts' ? $items->skip(4) : collect();
                @endphp
                @if ($editing)
                    @foreach ($visibleItems as $item)
                        @include('role.partials.participant-directory-item')
                    @endforeach
                    @if ($hiddenItems->isNotEmpty())
                        <details class="participant-contacts-more" @if ($errors->any()) open @endif>
                            <summary>{{ __('messages.pending_action_show_more', ['count' => $hiddenItems->count()]) }}</summary>
                            @foreach ($hiddenItems as $item)
                                @include('role.partials.participant-directory-item')
                            @endforeach
                        </details>
                    @endif
                    @php($item = null)
                    @include('role.partials.participant-directory-item')
                @elseif ($kind === 'contacts')
                    @forelse ($visibleItems as $contact)
                        <div class="page-kv"><div><dt><bdi>{{ $contact->name }}</bdi>@if ($contact->label) <small>{{ $contact->label }}</small>@endif</dt>
                            <dd class="flex flex-wrap gap-x-4 gap-y-1">
                                @if ($contact->email)<x-link :href="'mailto:'.$contact->email"><bdi>{{ $contact->email }}</bdi></x-link>@endif
                                @if ($contact->phone)<x-link :href="$phoneHref($contact->phone)"><bdi dir="ltr">{{ $contact->phone }}</bdi></x-link>@endif
                                @if ($contact->website)<x-link :href="$contact->website" target="_blank" rel="noopener noreferrer"><bdi>{{ $contact->website }}</bdi></x-link>@endif
                            </dd>
                        </div></div>
                    @empty
                        <p class="text-[rgb(var(--ap-ink-3))]">{{ __('messages.none') }}</p>
                    @endforelse
                    @if ($hiddenItems->isNotEmpty())
                        <details class="participant-contacts-more">
                            <summary>{{ __('messages.pending_action_show_more', ['count' => $hiddenItems->count()]) }}</summary>
                            @foreach ($hiddenItems as $contact)
                                <div class="page-kv"><div><dt><bdi>{{ $contact->name }}</bdi>@if ($contact->label) <small>{{ $contact->label }}</small>@endif</dt><dd class="flex flex-wrap gap-x-4 gap-y-1">
                                    @if ($contact->email)<x-link :href="'mailto:'.$contact->email"><bdi>{{ $contact->email }}</bdi></x-link>@endif
                                    @if ($contact->phone)<x-link :href="$phoneHref($contact->phone)"><bdi dir="ltr">{{ $contact->phone }}</bdi></x-link>@endif
                                    @if ($contact->website)<x-link :href="$contact->website" target="_blank" rel="noopener noreferrer"><bdi>{{ $contact->website }}</bdi></x-link>@endif
                                </dd></div></div>
                            @endforeach
                        </details>
                    @endif
                @elseif ($kind === 'files')
                    @forelse ($items as $file)
                        <div class="page-kv"><div><dt><bdi>{{ $file->label ?: $file->filename }}</bdi></dt><dd><x-link :href="route('role.participants.download', $params + ['item' => $encode($file->id)])"><bdi>{{ $file->filename }}</bdi> · {{ number_format($file->bytes / 1024) }} KB</x-link></dd></div></div>
                    @empty
                        <p class="text-[rgb(var(--ap-ink-3))]">{{ __('messages.none') }}</p>
                    @endforelse
                @else
                    @forelse ($items as $link)
                        @php($platformLabels = ['spotify' => 'Spotify', 'apple_music' => 'Apple Music', 'bandcamp' => 'Bandcamp', 'soundcloud' => 'SoundCloud', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'linktree' => 'Linktree'])
                        <div class="participant-social-item"><span class="participant-social-name">{{ ($link->profileVideo ?? false) ? __('messages.video_link') : ($platformLabels[$link->platform] ?? __('messages.social_links')) }}</span><x-link class="participant-social-url" :href="$link->url" target="_blank" rel="noopener noreferrer"><bdi>{{ $link->url }}</bdi></x-link></div>
                    @empty
                        <p class="text-[rgb(var(--ap-ink-3))]">{{ __('messages.none') }}</p>
                    @endforelse
                @endif
            </x-page-card>
        @endforeach
        @if ($editing)
            <details><summary class="cursor-pointer">{{ __('messages.remove') }}</summary>
                <form method="post" action="{{ route('role.participants.destroy', $params) }}">@csrf @method('delete')
                    <p class="page-lead">{{ __('backstage.removal') }}</p><x-danger-button>{{ __('messages.remove') }}</x-danger-button>
                </form>
            </details>
        @endif
    @endif
</div>
