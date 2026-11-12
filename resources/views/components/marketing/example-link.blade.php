{{--
    A quiet text link under an audience page's hero buttons to ONE live example schedule that fits
    the page: a step between reading and signing up, for a visitor who wants to see what theirs
    would look like. Text only (a hero stays the headline, the lede and the buttons). It opens in a
    new tab, as /examples does, and the example's own pages carry a bar back to sign-up
    (partials/example-schedule-bar).

    Props:
      subdomain  one of the schedules App\Utils\ExampleSchedules lists; the address comes from
                 there, and a name it does not list draws nothing
      ink        a CSS colour for the words, for a page with a palette of its own (its own ink
                 token); otherwise the page's quiet grey
    The slot is the link's words.

    Plain CSS below rather than new Tailwind classes, which would draw nothing until the next build.
--}}
@props(['subdomain', 'ink' => null])
@php
    $exampleSchedule = collect(\App\Utils\ExampleSchedules::byCategory())->flatten(1)->firstWhere('subdomain', $subdomain);
@endphp
@if ($exampleSchedule)
    @once
        <style {!! nonce_attr() !!}>
            .es-example-link { margin-top: 1.25rem; font-size: .9375rem; line-height: 1.5; }
            .es-example-link a { font-weight: 500; text-decoration: underline; text-decoration-thickness: 1px; text-underline-offset: .25em; text-decoration-color: color-mix(in srgb, currentColor 40%, transparent); transition: text-decoration-color .2s ease; }
            .es-example-link a:hover, .es-example-link a:focus-visible { text-decoration-color: currentColor; }
        </style>
    @endonce
    <p {{ $attributes->class(['es-example-link']) }} data-example-link="{{ $subdomain }}">
        @if ($ink)
            <x-link :href="$exampleSchedule['url']" target="_blank" class="text-gray-600 dark:text-gray-300" :style="'color: '.$ink">{{ $slot }}</x-link>
        @else
            <x-link :href="$exampleSchedule['url']" target="_blank" class="text-gray-600 dark:text-gray-300">{{ $slot }}</x-link>
        @endif
    </p>
@endif
