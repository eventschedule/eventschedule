@php
    $customLogo = config('branding.logo_path');
    $logoAlt = branding_logo_alt();
@endphp

<div class="p-6">
    @if ($customLogo)
        <img class="h-10 md:h-12 w-auto" src="{{ branding_logo_url() }}" alt="{{ $logoAlt }}" />
    @else
        <img class="h-10 md:h-12 w-auto dark:hidden" src="{{ branding_logo_url('dark') }}" alt="{{ $logoAlt }}" />
        <img class="h-10 md:h-12 w-auto hidden dark:block" src="{{ branding_logo_url('light') }}" alt="{{ $logoAlt }}" />
    @endif
</div>
