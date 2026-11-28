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
                @php
                    $items = $details[$kind];
                    $visibleItems = $kind === 'contacts' ? $items->take(4) : $items;
                    $hiddenItems = $kind === 'contacts' ? $items->skip(4) : collect();
                @endphp
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
            </x-page-card>
        @endforeach
        <details><summary class="cursor-pointer">{{ __('messages.remove') }}</summary>
            <form method="post" action="{{ route('role.participants.destroy', $params) }}">@csrf @method('delete')
                <p class="page-lead">{{ __('backstage.removal') }}</p><x-danger-button>{{ __('messages.remove') }}</x-danger-button>
            </form>
        </details>
    @endif
</div>
