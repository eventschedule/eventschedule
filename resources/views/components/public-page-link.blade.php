@props(['href' => null, 'label' => null])
{{--
    The small arrow beside a schedule's or an event's name on /admin/dashboard
    that opens its guest page in a new tab (App\Utils\PublicPageUrl). Where the name itself already
    leads there, the same arrow is drawn inside the name's link instead (components/public-page-icon).

    In a Vue template pass v-bind:href, v-bind:aria-label and v-bind:title and no label: nothing
    static is printed for them, because Vue keeps the FIRST of two same-named attributes.
--}}
@php($text = $label !== null ? __('messages.admin_open_public_page', ['label' => $label]) : null)
<a @if ($href !== null) href="{{ $href }}" @endif target="_blank" rel="noopener noreferrer"
   @if ($text !== null) aria-label="{{ $text }}" title="{{ $text }}" @endif
   {{ $attributes->merge(['class' => 'inline-flex items-center justify-center w-6 h-6 shrink-0 rounded-md text-gray-500 dark:text-gray-400 hover:text-[var(--brand-blue)] hover:bg-gray-100 dark:hover:bg-white/[0.06] transition-all duration-200 focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]']) }}><x-public-page-icon /></a>
