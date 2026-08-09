{{--
    The <link rel="manifest"> and matching theme colour for this page.

    Deliberately NOT in layouts/app.blade.php. That file is the shell for the guest portal as well
    as the admin portal - app-guest.blade.php opens with <x-app-layout> exactly like
    app-admin.blade.php does - so a single value there brands every schedule's site as ours. A
    manifest names the app a browser installs, and once installed Android hands it every link a
    visitor opens on that host and shows its icon as the launch splash first. One static
    public/manifest.webmanifest naming "Event Schedule" therefore put our logo full screen in
    front of every schedule's audience. See AppController::manifest().

    Include one of two ways:

      @include('partials.web-app-manifest', ['manifestRole' => $role])   tenant surface
      @include('partials.web-app-manifest', ['platformApp' => true])     admin portal

    A tenant surface whose $role is null renders nothing, and that is the right answer rather than
    a fallback: no manifest at all only costs the page an install prompt nobody asked for, while
    the platform manifest would put us back in front of someone else's audience. Same for a page
    that belongs to neither (a visitor's carpool list) - it includes this partial not at all.
--}}
@php
    $manifestRole = $manifestRole ?? null;
    $platformApp = $platformApp ?? false;
@endphp
@if ($manifestRole)
    <link rel="manifest" href="{{ route('role.manifest', ['subdomain' => $manifestRole->subdomain]) }}">
    <meta name="theme-color" content="{{ $manifestRole->accent_color ?: '#4E81FA' }}">
@elseif ($platformApp)
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#4E81FA">
@endif
