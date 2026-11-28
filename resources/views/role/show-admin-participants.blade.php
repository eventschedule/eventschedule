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
                                <x-link :href="route('role.view_admin', [$subdomain, 'participants', 'entry' => $encode($row->entry_id)])">{{ __('messages.edit') }}</x-link>
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
        @endphp
        <x-link :href="$directoryUrl">{{ __('messages.participants') }}</x-link>
        <h2 class="text-xl font-semibold"><bdi>{{ $entry->name }}</bdi></h2>
        <x-page-card :title="__('backstage.basic_info')">
            <form method="post" action="{{ route('role.participants.basic_info', $params) }}" class="space-y-3">
                @csrf @method('put')
                @foreach (['participant_email' => ['messages.email', 'email'], 'participant_phone' => ['messages.phone_number', 'tel'], 'participant_website' => ['messages.website', 'url']] as $field => [$label, $type])
                    <label class="block">{{ __($label) }} {{ __('messages.label_optional_suffix') }}
                        <x-text-input class="block w-full" :type="$type" :name="$field" :value="old($field, $entry->$field)" />
                    </label>
                @endforeach
                <div class="page-form-actions"><x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button></div>
            </form>
        </x-page-card>
        <x-page-card :title="__('backstage.promotion')">
            <form method="post" action="{{ route('role.participants.promotion', $params) }}">
                @csrf @method('put')
                <textarea name="promotion" rows="4" maxlength="15000" class="w-full" aria-label="{{ __('backstage.promotion') }}">{{ old('promotion', $entry->promotion) }}</textarea>
                <div class="page-form-actions"><x-brand-button type="submit">{{ __('messages.save') }}</x-brand-button></div>
            </form>
        </x-page-card>
        @foreach (['contacts' => __('backstage.contacts'), 'files' => __('backstage.files'), 'links' => __('messages.social_links')] as $kind => $heading)
            <x-page-card :title="$heading">
                @foreach ($details[$kind]->concat([null]) as $item)
                    @php
                        $profileVideo = $item->profileVideo ?? false;
                        $editable = $item->editable ?? true;
                        $action = $profileVideo ? route('role.participants.profile_video', $params) : route('role.participants.item', $params + ['kind' => $kind, 'item' => $item ? $encode($item->id) : null]);
                        $retry = old('_directory_form') === $action;
                    @endphp
                    @if ($kind === 'links')
                    <div class="participant-social-item">
                    @else
                    <details class="py-2" @if ($retry) open @endif>
                        <summary class="cursor-pointer"><bdi>{{ $item ? ($item->name ?? $item->label ?? $item->filename) : __('messages.add') }}</bdi></summary>
                    @endif
                        @if ($kind === 'links' && $item)
                            @php($platformLabels = ['spotify' => 'Spotify', 'apple_music' => 'Apple Music', 'bandcamp' => 'Bandcamp', 'soundcloud' => 'SoundCloud', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'linktree' => 'Linktree'])
                            <span class="participant-social-name">{{ $profileVideo ? __('messages.video_link') : ($platformLabels[$item->platform] ?? __('messages.social_links')) }}</span>
                            <x-link class="participant-social-url" :href="$item->url" target="_blank" rel="noopener noreferrer"><bdi>{{ $item->url }}</bdi></x-link>
                        @endif
                        @if ($kind === 'files' && $item)
                            <x-link :href="route('role.participants.download', $params + ['item' => $encode($item->id)])"><bdi>{{ $item->filename }}</bdi> ({{ number_format($item->bytes / 1024) }} KB)</x-link>
                        @endif
                        @if ($editable)
                        @if (! ($kind === 'links' && $item))
                        <form method="post" action="{{ $action }}" class="{{ $kind === 'links' ? 'participant-social-row' : 'space-y-3' }}" @if ($kind === 'files' && ! $item) enctype="multipart/form-data" @endif>
                            @csrf
                            @if ($profileVideo)<input type="hidden" name="original_url" value="{{ $item->url }}">@endif
                            <input type="hidden" name="_directory_form" value="{{ $action }}">
                            @if ($kind === 'files' && ! $item)
                                <label class="block">{{ __('messages.choose_file') }} ({{ config('backstage.file_mb') }} MB)
                                    <input type="file" name="file" required accept=".pdf,.png,.jpg,.jpeg,.doc,.docx,.xls,.xlsx,.ppt,.pptx">
                                </label>
                            @endif
                            @if ($kind === 'links')
                                @if ($profileVideo)
                                    <span>{{ __('messages.video_link') }}</span>
                                @elseif (! $item)
                                    <select name="platform" aria-label="{{ __('backstage.platform') }}" required>
                                        @foreach (['spotify' => 'Spotify', 'apple_music' => 'Apple Music', 'bandcamp' => 'Bandcamp', 'soundcloud' => 'SoundCloud', 'instagram' => 'Instagram', 'facebook' => 'Facebook', 'tiktok' => 'TikTok', 'linktree' => 'Linktree'] as $platform => $platformLabel)<option value="{{ $platform }}">{{ $platformLabel }}</option>@endforeach
                                    </select>
                                @else
                                    <span>{{ $platformLabels[$item->platform] ?? $item->platform }}</span>
                                @endif
                                @if ($item)
                                    <x-link class="min-w-0 flex-1 truncate" :href="$item->url" target="_blank" rel="noopener noreferrer"><bdi>{{ $item->url }}</bdi></x-link>
                                @else
                                    <x-text-input class="min-w-0 flex-1" type="url" name="url" required :value="$retry ? old('url') : ''" />
                                @endif
                                @if (! $item)<x-secondary-button type="submit">{{ __('messages.add') }}</x-secondary-button>@endif
                            @else
                                @foreach (match($kind) { 'contacts' => ['name','label','email','phone','website'], default => ['label'] } as $field)
                                    <label class="block">{{ __('messages.'.(['label' => 'seating_text_label','phone' => 'phone_number'][$field] ?? $field)) }}
                                        @unless ($field === 'name') {{ __('messages.label_optional_suffix') }} @endunless
                                        <x-text-input class="block w-full" :type="['website' => 'url', 'email' => 'email', 'phone' => 'tel'][$field] ?? 'text'" :name="$field" :required="$field === 'name'" :value="$retry ? old($field) : ($item->$field ?? '')" />
                                    </label>
                                @endforeach
                                <x-secondary-button type="submit">{{ $item ? __('messages.save') : __('messages.add') }}</x-secondary-button>
                            @endif
                        </form>
                        @endif
                        @if ($item)
                            <form method="post" action="{{ $action }}" class="{{ $kind === 'links' ? 'participant-social-delete' : 'mt-2' }}">@csrf @method('delete')
                                @if ($profileVideo)<input type="hidden" name="original_url" value="{{ $item->url }}">@endif
                                @if ($kind === 'links')
                                    <button type="submit" class="participant-social-trash" aria-label="{{ __('messages.remove') }}" title="{{ __('messages.remove') }}"><svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M4 7h16m-10 4v6m4-6v6M5 7l1 13h12l1-13M9 7V4h6v3"/></svg></button>
                                @else<x-danger-button>{{ __('messages.remove') }}</x-danger-button>@endif
                            </form>
                        @endif
                        @endif
                    @if ($kind === 'links')
                    </div>
                    @else
                    </details>
                    @endif
                @endforeach
            </x-page-card>
        @endforeach
        <details><summary class="cursor-pointer">{{ __('messages.remove') }}</summary>
            <form method="post" action="{{ route('role.participants.destroy', $params) }}">@csrf @method('delete')
                <p class="page-lead">{{ __('backstage.removal') }}</p><x-danger-button>{{ __('messages.remove') }}</x-danger-button>
            </form>
        </details>
    @endif
</div>
