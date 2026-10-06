{{-- A section's heading: its icon and name, as the sidebar gives them (or its fuller name, where
     the sidebar uses a short one), and at most one quiet link on the other side. On a phone the
     name is already on the accordion header just above, so only the link is shown there (see the
     page's own styles). $settingsSection is passed by profile/edit; $asideUrl and $asideLabel by
     the section that has a link to offer. $settingsBlock is set for a section that shares its
     tab with others: its title is the only place its name is said, so it stays on a phone. --}}
@php $heading = $settingsSections[$settingsSection]; @endphp
<h2 class="form-kit-title{{ ($settingsBlock ?? false) ? ' settings-block-title' : '' }}">
    <span>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true">
            @foreach ($heading['icon'] as $iconPath)
            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $iconPath }}" />
            @endforeach
        </svg>
        {{ $heading['heading'] ?? $heading['label'] }}
    </span>
    @if (! empty($asideUrl))
    <span class="settings-title-aside"><x-link href="{{ $asideUrl }}" target="_blank">{{ $asideLabel }}</x-link></span>
    @endif
</h2>
