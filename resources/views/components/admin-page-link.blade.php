@props(['href' => null, 'label' => null])
{{--
    The small control beside a schedule's name on /admin/dashboard that opens the schedule in the
    platform admin (/admin/schedules/{id}/edit), in the same tab. It stands next to the arrow of
    components/public-page-link, which opens the guest page in a new tab, so either is one press
    away wherever a schedule or one of its events is named.

    In a Vue template pass v-bind:href, v-bind:aria-label and v-bind:title and no label: nothing
    static is printed for them, because Vue keeps the FIRST of two same-named attributes.
--}}
@php($text = $label !== null ? __('messages.admin_open_in_admin', ['label' => $label]) : null)
<a @if ($href !== null) href="{{ $href }}" @endif
   @if ($text !== null) aria-label="{{ $text }}" title="{{ $text }}" @endif
   {{ $attributes->merge(['class' => 'inline-flex items-center justify-center w-6 h-6 shrink-0 rounded-md text-gray-500 dark:text-gray-400 hover:text-[var(--brand-blue)] hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]']) }}><svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="{{ \App\Utils\RealtimeIcons::PATHS['manage'] }}" /></svg></a>
