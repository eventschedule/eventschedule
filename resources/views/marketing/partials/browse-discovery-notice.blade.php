{{-- A platform admin's note after a discovery button (MarketingController::backToDiscovery()):
     what it did, and Undo, the Hidden schedules list or the password confirmation it needs.
     $anchor is the section it is printed in, which Undo comes back to. --}}
<div @class(['bw-flash', 'bw-note', 'bw-flash-bad' => ! empty($notice['bad'])]) role="status">
    <span>{{ $notice['text'] }}</span>
    @if (! empty($notice['undo']))
        <form method="POST" action="{{ $notice['undo']['action'] }}">
            @csrf
            <input type="hidden" name="hide" value="{{ $notice['undo']['hide'] ? 1 : 0 }}">
            <input type="hidden" name="anchor" value="{{ $anchor }}">
            <button type="submit">Undo</button>
        </form>
    @endif
    @if (! empty($notice['list']) && $anchor !== 'hidden-schedules')
        <a href="#hidden-schedules">Hidden schedules</a>
    @endif
    @if (! empty($notice['confirm']))
        <a href="{{ $notice['confirm'] }}">Confirm password</a>
    @endif
</div>
