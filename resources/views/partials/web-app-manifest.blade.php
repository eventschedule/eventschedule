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

    The tenant manifest is also deliberately NOT installable - see AppController::scheduleManifest().
    This partial's job on a tenant page is to point Chrome at that document, so a WebAPK installed
    back when the static manifest was live re-brands itself off it, and to echo its theme colour.
    It is not to make anything installable.

    A tenant surface whose $role is null renders nothing, and that is the right answer rather than
    a fallback: no manifest at all only costs the page an install prompt nobody asked for, while
    the platform manifest would put us back in front of someone else's audience. Same for a page
    that belongs to neither (a visitor's carpool list) - it includes this partial not at all.
--}}
@php
    $manifestRole = $manifestRole ?? null;
    $platformApp = $platformApp ?? false;

    // The manifest omits theme_color when the schedule has cleared its accent; this tag must too,
    // or the address bar carries our blue on a page whose own manifest carries no colour at all.
    // Role::manifestThemeColor() is the single rule both halves read.
    $manifestThemeColor = $manifestRole?->manifestThemeColor();

    // CSP sends manifest-src 'self', so a cross-origin href is refused by the browser outright.
    // In hosted mode role.manifest lives inside Route::domain(), so route() always emits an
    // absolute tenant URL - while ticket.view and ticket.order are domain-less and render on
    // whatever host generated the link, which for a mailed ticket is the app subdomain. Only
    // advertise the manifest when it is same-origin; a missing manifest just costs an install
    // prompt, whereas a blocked one is a console error on every ticket page.
    $manifestUrl = null;

    if ($manifestRole) {
        $manifestUrl = route('role.manifest', ['subdomain' => $manifestRole->subdomain]);

        if (parse_url($manifestUrl, PHP_URL_HOST) !== request()->getHost()) {
            $manifestUrl = null;
        }
    }
@endphp
@if ($manifestRole)
    @if ($manifestUrl)
        <link rel="manifest" href="{{ $manifestUrl }}">
    @endif
    @if ($manifestThemeColor)
        <meta name="theme-color" content="{{ $manifestThemeColor }}">
    @endif
@elseif ($platformApp)
    <link rel="manifest" href="{{ route('app.manifest') }}">
    <meta name="theme-color" content="#4E81FA">
@endif
