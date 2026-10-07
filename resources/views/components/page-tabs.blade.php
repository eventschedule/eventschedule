@props([
    // Each: ['label' => , 'href' => , 'current' => bool, 'count' => int|null, 'waiting' => bool].
    'tabs',
    'label' => null,
    'id' => 'page-tabs',
    // Keep the strip on a phone too, and leave the dropdown out: for three or four short tabs, and
    // for a section inside the platform admin, whose own navigation is already a dropdown there.
    'strip' => false,
])

{{-- The tabs of a section whose pages are separate addresses (Newsletters, Boost, the platform
     admin's groups). ONE list feeds both the strip, from a tablet up, and the dropdown a phone
     gets: a tab given to one alone is missing for half the visitors. The strip fits where it can,
     and where it cannot it fades at the edge it runs on from and brings the current tab into view.
     A schedule's own tabs (role/show-admin) are the same strip, built in that file.

     A count is a quiet pill; `waiting` turns it blue, for things that wait on an answer. --}}
@php $tabs = array_values(array_filter($tabs)); @endphp

@unless ($strip)
<div class="ap-tabs-select md:hidden">
    <label for="{{ $id }}-select" class="sr-only">{{ $label ?? __('messages.select_a_tab') }}</label>
    <select id="{{ $id }}-select" data-page-tabs-select autocomplete="off" class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-800 dark:text-gray-100 focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)]">
        @if (! collect($tabs)->contains(fn ($t) => ! empty($t['current'])))
        <option value="" selected disabled>{{ $label ?? __('messages.select_a_tab') }}</option>
        @endif
        @foreach ($tabs as $pageTab)
        <option value="{{ $pageTab['href'] }}" @selected(! empty($pageTab['current']))>{{ $pageTab['label'] }}{{ ! empty($pageTab['count']) ? ' ('.number_format($pageTab['count']).')' : '' }}</option>
        @endforeach
    </select>
</div>
@endunless
<div class="ap-tabs-wrap {{ $strip ? 'is-always' : 'hidden md:block' }}" data-page-tabs>
    <nav class="ap-tabs" id="{{ $id }}" aria-label="{{ $label ?? __('messages.select_a_tab') }}">
        @foreach ($tabs as $pageTab)
        <a href="{{ $pageTab['href'] }}" class="ap-tab" @if (! empty($pageTab['current'])) aria-current="page" @endif>
            {{ $pageTab['label'] }}
            @if (! empty($pageTab['count']))
            <span class="ap-tab-count {{ ! empty($pageTab['waiting']) ? 'is-waiting' : '' }}">{{ number_format($pageTab['count']) }}</span>
            @endif
        </a>
        @endforeach
    </nav>
</div>

@once
<script {!! nonce_attr() !!}>
document.addEventListener('DOMContentLoaded', function() {
    // Bring the tab you are on into view, and say when there are more to either side.
    document.querySelectorAll('[data-page-tabs]').forEach(function(wrap) {
        var tabs = wrap.querySelector('.ap-tabs');
        if (! tabs) {
            return;
        }
        var paint = function() {
            var start = Math.abs(tabs.scrollLeft);
            wrap.classList.toggle('more-before', start > 4);
            wrap.classList.toggle('more-after', start + tabs.clientWidth < tabs.scrollWidth - 4);
        };
        var current = tabs.querySelector('[aria-current="page"], [aria-selected="true"]');
        var showCurrent = function() {
            if (current) {
                tabs.scrollLeft = current.offsetLeft - (tabs.clientWidth - current.offsetWidth) / 2;
            }
            paint();
        };
        showCurrent();
        // Again once the fonts are in: the labels change width and the tab moves.
        window.addEventListener('load', showCurrent);
        tabs.addEventListener('scroll', paint, { passive: true });
        window.addEventListener('resize', paint);
    });

    document.querySelectorAll('select[data-page-tabs-select]').forEach(function(select) {
        var here = select.value;
        select.addEventListener('change', function() {
            if (select.value) {
                window.location.href = select.value;
            }
        });
        // Back brings the page back with the option that was chosen still showing, and choosing
        // it again would fire nothing.
        window.addEventListener('pageshow', function() {
            select.value = here;
        });
    });
});
</script>
@endonce
