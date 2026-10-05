{{-- The setup guide's line in the sidebar: the way to it from any page, and the way back after
     it was hidden. Rendered twice per page (the drawer and the desktop rail both include the
     navigation), so no ids. A button carries the action when the guide is hidden, not a hidden
     input: FirstScheduleFormTest reads every input on the page. --}}
@php $sgNav = \App\Utils\SetupGuide::line(); @endphp
@if ($sgNav)
<li>
    @if (! empty($sgNav['hidden']))
    <form method="POST" action="{{ route('home.setup_guide') }}">
        @csrf
        <button type="submit" name="action" value="restore" data-setup-guide-nav
            class="ms-12 block w-[calc(100%-3rem)] rounded-lg px-2 py-1.5 text-start text-sm font-medium text-gray-400 transition-all duration-200 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
            {{ __('messages.setup_guide_title') }}
        </button>
    </form>
    @else
    <a href="{{ route('home') }}#setup-guide" data-setup-guide-nav
        class="ms-12 block rounded-lg px-2 py-1.5 text-sm font-medium text-gray-400 no-underline transition-all duration-200 hover:text-white focus:outline-none focus-visible:ring-2 focus-visible:ring-[var(--brand-blue)]">
        {{ __('messages.setup_guide_title') }}
    </a>
    @endif
</li>
@endif
