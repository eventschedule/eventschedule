{{--
    The list's own tools, in either header style: the filter and the list / calendar switch.

    They are grey on purpose: Follow is the one thing in a header in the schedule's colour, and
    these used to sit beside it as three more accent buttons. The ids are the list's contract
    (role/partials/calendar finds them by id): the filter is two buttons because the laptop's
    opens a dialog and the phone's a drawer, and the stylesheet shows one of them. The switch
    says which view is on with aria-pressed, which the list's script keeps current.

    Expects $role and $event.
--}}
@if (! $event)
@php $toolsLayout = $role->activeEventLayout(); @endphp
<div class="gk-head-tools">
    <button type="button" id="hero-filters-btn" class="gk-head-tool gk-head-tool-desk">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/></svg>
        <span>{{ $role->customLabel('filters') }}</span>
        <span id="hero-filters-badge" class="gk-head-badge" hidden></span>
    </button>
    <button type="button" id="hero-filters-btn-mobile" class="gk-head-tool gk-head-tool-phone">
        <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M14,12V19.88C14.04,20.18 13.94,20.5 13.71,20.71C13.32,21.1 12.69,21.1 12.3,20.71L10.29,18.7C10.06,18.47 9.96,18.16 10,17.87V12H9.97L4.21,4.62C3.87,4.19 3.95,3.56 4.38,3.22C4.57,3.08 4.78,3 5,3H19C19.22,3 19.43,3.08 19.62,3.22C20.05,3.56 20.13,4.19 19.79,4.62L14.03,12H14Z"/></svg>
        <span>{{ $role->customLabel('filters') }}</span>
        <span id="hero-filters-badge-mobile" class="gk-head-badge" hidden></span>
    </button>
    <div class="gk-head-seg" role="group" aria-label="{{ __('messages.view') }}">
        <button type="button" id="toggle-list-btn" class="gk-head-seg-btn" aria-pressed="{{ $toolsLayout === 'list' ? 'true' : 'false' }}" aria-label="{{ __('messages.list') }}" title="{{ __('messages.list') }}">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3,4H7V8H3V4M9,5V7H21V5H9M3,10H7V14H3V10M9,11V13H21V11H9M3,16H7V20H3V16M9,17V19H21V17H9"/></svg>
        </button>
        <button type="button" id="toggle-calendar-btn" class="gk-head-seg-btn" aria-pressed="{{ $toolsLayout === 'calendar' ? 'true' : 'false' }}" aria-label="{{ __('messages.calendar') }}" title="{{ __('messages.calendar') }}">
            <svg viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M9,10V12H7V10H9M13,10V12H11V10H13M17,10V12H15V10H17M19,3A2,2 0 0,1 21,5V19A2,2 0 0,1 19,21H5C3.89,21 3,20.1 3,19V5A2,2 0 0,1 5,3H6V1H8V3H16V1H18V3H19M19,19V8H5V19H19M9,14V16H7V14H9M13,14V16H11V14H13M17,14V16H15V14H17Z"/></svg>
        </button>
    </div>
</div>
@endif
