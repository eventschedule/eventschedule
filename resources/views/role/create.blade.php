{{--
    The first-run form for a new schedule.

    /new/{type} used to render role/edit.blade.php - 9,680 lines, six section groups and about
    twenty-four sub-tabs - to somebody who had signed up ninety seconds earlier. Of the people who
    opened it, 28% never saved anything. The first two interactive things on that page were a greyed
    "AI Generator" (not Enterprise) and an amber "available on the Pro plan" panel, because a fresh
    Role is neither.

    Only three fields are required by RoleCreateRequest - name, timezone, email - and the last two
    are inherited from the user. Everything else here is a hidden input.

    THE HIDDEN INPUTS ARE LOAD-BEARING. RoleController::store() does $role->fill($request->all()),
    so an omitted field is not "use the column default", it is whatever fill() and the code after it
    leave behind:

      - type          roles.type is NOT NULL with no default, and store() takes no $type argument.
                      This input is the ONLY thing carrying the route parameter. Omit it: 500.
      - require_account  column default TRUE, but create() prefills ($type === 'curator'), i.e.
                      false for talent and venue. Omitting it makes every new talent and venue
                      demand an account from guests who want to submit.
      - accept_requests  belt and braces, not load-bearing: omitting it leaves the column default
                      of TRUE, which is what we want. It is explicit because the FULL form gets
                      this wrong - create() never prefills it, so its <x-toggle> renders OFF and
                      still posts its companion hidden 0, and every schedule made through that
                      form started out refusing requests. Stating it here keeps the two forms
                      producing the same row.
      - background / background_colors  create() picks 'image' plus a random gradient; the column
                      defaults are 'gradient' and NULL. And store() has
                      `if (! $request->background_colors) { ... custom_color1.', '.custom_color2 }`,
                      so with all three absent it writes the literal string ", " - which
                      hasConfiguredBackground() reads as set, and the guest page then emits
                      `linear-gradient(150deg, , )`. Every new schedule would render no background.
      - language_code / use_24_hour_time  inherited from the user; the column default is 'en', so a
                      Hebrew organizer's first schedule would silently be English.

    Safe to omit, because the column default and create()'s prefill agree: event_layout,
    announce_new_events, show_subscribe_panel, accent_color, font_family, font_color, header_style.

    tests/Feature/FirstScheduleFormTest.php compares a schedule saved from here against one saved
    from the full form, column by column, so a field dropped from this list fails the build.
--}}
{{--
    Two layouts, one form.

    /new/{type} serves everyone, not only first-timers. The bare shell keeps the first run focused,
    the way /getting-started is - but a returning user reaches this page from the dashboard's "New
    schedule" dropdown, and layouts/app.blade.php renders no navigation at all (its only anchor is
    the skip-to-content link), so they were landing somewhere with no nav, no cancel and no back.
    role/edit.blade.php, which this replaced at this route, opened <x-app-admin-layout>.

    The form itself lives in the partial so the two branches cannot drift apart.

    The bare-shell branch carries the platform manifest itself, exactly as getting-started.blade.php
    does and for the same reason: layouts/app.blade.php omits it because it is also the guest-portal
    shell, so each inner layout supplies its own. app-admin already includes it.
--}}
@php
    $hasSchedules = auth()->user()->member()->exists();
@endphp

@if ($hasSchedules)
<x-app-admin-layout>
    @include('role.partials.create-form', ['hasSchedules' => $hasSchedules])
</x-app-admin-layout>
@else
<x-app-layout :theme-variants="true" :title="__('messages.new_schedule') . ' | Event Schedule'">
    <x-slot name="head">
        @include('partials.web-app-manifest', ['platformApp' => true])
    </x-slot>

    @include('role.partials.create-form', ['hasSchedules' => $hasSchedules])
</x-app-layout>
@endif
