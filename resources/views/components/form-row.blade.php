@props([
    'group',
    'tab',
    'title',
    'pane' => null,
    'summary' => null,
    'warn' => false,
    'muted' => false,
    'locked' => null,
])
{{-- A row that opens in place, where a tab inside a tab used to be: its name, then one line saying
     what it holds, so the setting can be read without opening it. `group` is the list the row
     belongs to (one row of a list is open at a time) and `pane` the id of the element it opens,
     which carries class event-subrow-body and the hidden attribute. The line is either given here
     (`summary`, for a page that reloads on save) or written by the page through FormKit.summary()
     under the key "group:tab". `muted` is for a line that says nothing is set ("Not connected"),
     which reads quieter than one that names something. Opened and closed by
     partials/form-kit-script. --}}
<button type="button" {{ $attributes->merge(['class' => 'event-subrow']) }}
    data-row-group="{{ $group }}" data-tab="{{ $tab }}"
    aria-expanded="false" aria-controls="{{ $pane ?? $group.'-tab-'.$tab }}">
    <span class="event-row-title">{{ $title }}</span>
    <span class="event-row-summary {{ $warn ? 'is-warn' : '' }} {{ $muted || $summary === '' ? 'is-empty' : '' }}" data-summary="{{ $group }}:{{ $tab }}"><bdi>{{ $summary }}</bdi></span>
    @if ($locked)
    <x-lock-badge :tier="$locked" class="event-row-lock" />
    @endif
    <svg class="event-row-chevron" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 8.25l-7.5 7.5-7.5-7.5" /></svg>
</button>
