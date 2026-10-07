{{-- How the four pages of the newsletters section open: Newsletters, Segments, Templates and
     Import emails. They are one section with four tabs. They used to be an index with three
     bordered buttons and three pages whose only way anywhere was a "Back" button, so going from
     Segments to Templates took two page loads through the list of newsletters.

     Expects $role (null only on the index of somebody with no schedule) and $tab, one of
     newsletters, segments, templates, import. The schedule picker is offered when there is more
     than one schedule to pick, and it keeps you on the tab you are on. --}}
@php
    $sectionRoles = $roles ?? auth()->user()->roles()->wherePivot('level', '!=', 'follower')->get();
    $sectionParam = $role ? ['role_id' => \App\Utils\UrlUtils::encodeId($role->id)] : [];
@endphp

<x-page-header :title="__('messages.newsletters')" :lead="__('messages.newsletters_lead')">
    @if ($role)
    <x-slot name="actions">
        @if ($sectionRoles->count() > 1)
        <div class="news-picker">
            <label for="role-filter" class="sr-only">{{ __('messages.schedule') }}</label>
            <select id="role-filter" data-searchable required
                class="block w-full rounded-lg border-gray-300 dark:border-gray-600 dark:bg-gray-700 dark:text-white shadow-sm focus:border-[var(--brand-blue)] focus:ring-[var(--brand-blue)] text-base">
                @foreach ($sectionRoles as $sectionRole)
                <option value="{{ \App\Utils\UrlUtils::encodeId($sectionRole->id) }}" @selected($role->id == $sectionRole->id)>{{ $sectionRole->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <x-brand-link href="{{ route('newsletter.create', $sectionParam) }}">
            <svg class="-ms-0.5 me-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            {{ __('messages.create_newsletter') }}
        </x-brand-link>
    </x-slot>
    @endif
</x-page-header>

@if ($role)
<x-page-tabs id="newsletter-tabs" :label="__('messages.newsletters')" :tabs="[
    ['label' => __('messages.newsletters'), 'href' => route('newsletter.index', $sectionParam), 'current' => $tab === 'newsletters'],
    ['label' => __('messages.segments'), 'href' => route('newsletter.segments', $sectionParam), 'current' => $tab === 'segments'],
    ['label' => __('messages.templates'), 'href' => route('newsletter.templates', $sectionParam), 'current' => $tab === 'templates'],
    ['label' => __('messages.import_emails'), 'href' => route('newsletter.import', $sectionParam), 'current' => $tab === 'import'],
]" />
@endif

@include('newsletter.partials._notices')

@once
<script {!! nonce_attr() !!}>
    {{-- Another schedule, the same tab. What narrowed or ordered the list belongs to the schedule
         that was showing, so it is dropped. --}}
    document.addEventListener('DOMContentLoaded', function() {
        var picker = document.getElementById('role-filter');
        if (! picker) {
            return;
        }
        picker.addEventListener('change', function() {
            if (! picker.value) {
                return;
            }
            var url = new URL(window.location.href);
            url.searchParams.set('role_id', picker.value);
            url.searchParams.delete('sort_by');
            url.searchParams.delete('sort_dir');
            url.searchParams.delete('page');
            window.location.href = url.toString();
        });
    });
</script>
@endonce
