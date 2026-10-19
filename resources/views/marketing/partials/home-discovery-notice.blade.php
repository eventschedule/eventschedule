{{-- A platform admin's note after a discovery pill on the homepage rail (Hide schedule, Hide
     event), rendered by MarketingController::discoveryControls() for the page's script to put
     above the band: what it did, and Undo, the Hidden schedules list or the password
     confirmation it needs. --}}
<div @class(['hp-admin-note', 'is-bad' => ! empty($notice['bad'])]) role="status">
    <p>{{ $notice['text'] }}</p>
    @if (! empty($notice['undo']))
        <form method="POST" action="{{ $notice['undo']['action'] }}">
            @csrf
            <input type="hidden" name="hide" value="{{ $notice['undo']['hide'] ? 1 : 0 }}">
            <input type="hidden" name="anchor" value="discover">
            <button type="submit">Undo</button>
        </form>
    @endif
    @if (! empty($notice['list']))
        <a href="{{ $notice['list'] }}">Hidden schedules</a>
    @endif
    @if (! empty($notice['confirm']))
        <a href="{{ $notice['confirm'] }}">Confirm password</a>
    @endif
</div>
