{{--
    Row actions for one booking, shared by the desktop table and the mobile cards.
    Expects: $row (from appointment-bookings.blade.php), $role, $isViewer.
--}}
@php
    use App\Utils\UrlUtils;

    $s = $row['sale'];
    $e = $row['event'];
    $eventHash = UrlUtils::encodeId($e->id);
    $rowActionClass = 'px-3 py-1.5 rounded-lg text-sm font-medium transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-[var(--brand-blue)]';
@endphp

@if (! $isViewer && $row['pending'] && ! $row['cancelled'])
    {{-- Approving used to require a trip to the Requests tab; this posts to the same routes.
         Destructive first, forward action last. --}}
    <form method="POST" action="{{ route('event.decline', ['subdomain' => $role->subdomain, 'hash' => $eventHash]) }}"
          class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
        @csrf
        <input type="hidden" name="redirect_to" value="appointments">
        <button type="submit" class="{{ $rowActionClass }} text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10">{{ __('messages.decline') }}</button>
    </form>
    <form method="POST" action="{{ route('event.accept', ['subdomain' => $role->subdomain, 'hash' => $eventHash]) }}" class="inline">
        @csrf
        <input type="hidden" name="redirect_to" value="appointments">
        <button type="submit" class="{{ $rowActionClass }} text-white bg-[var(--brand-button-bg)] hover:bg-[var(--brand-button-bg-hover)]">{{ __('messages.accept') }}</button>
    </form>
    {{-- Proposing a different time is the third real answer to a pending request, and the endpoint has
         always allowed it - the guest's own page offers it as "Change time". Rendered after the decision
         buttons so the row still leads with Decline/Accept. --}}
    @if (! $isViewer && $row['rescheduleBlocked'] === null)
        <a href="{{ route('appointments.booking_reschedule', ['subdomain' => $role->subdomain, 'saleHash' => UrlUtils::encodeId($s->id)]) }}"
           class="ms-3 text-xs text-[var(--brand-blue)] hover:underline">{{ __('messages.appointments_change_time') }}</a>
    @endif
@else
    {{-- Only offered once a decision has been made. On a pending row this link leads to the GUEST
         manage page, where "Cancel appointment" fires guest-cancellation emails - not something to
         put in front of an owner who is still deciding. --}}
    <a href="{{ route('appointments.manage', ['event_id' => $eventHash, 'secret' => $s->secret]) }}"
       target="_blank" rel="noopener"
       class="text-xs text-[var(--brand-blue)] hover:underline me-3">{{ __('messages.preview') }}</a>
    {{-- Preview and Reschedule are both "look at / change this booking"; Cancel is destructive and stays
         last. Deliberately absent from the pending branch above: that row asks one clear question. --}}
    @if (! $isViewer && $row['rescheduleBlocked'] === null)
        <a href="{{ route('appointments.booking_reschedule', ['subdomain' => $role->subdomain, 'saleHash' => UrlUtils::encodeId($s->id)]) }}"
           class="text-xs text-[var(--brand-blue)] hover:underline me-3">{{ __('messages.appointments_reschedule') }}</a>
    @endif
    {{-- bookingCancel() rejects a past booking, so offering Cancel here only produced a confirm prompt
         followed by an error. Note line 37 above already checked this - the two had drifted apart. --}}
    @if (! $isViewer && ! $row['cancelled'] && ! $row['past'])
        <form method="POST" action="{{ route('appointments.booking_cancel', ['subdomain' => $role->subdomain, 'saleHash' => UrlUtils::encodeId($s->id)]) }}"
              class="inline" data-confirm="{{ __('messages.are_you_sure') }}">
            @csrf
            <button type="submit" class="text-xs text-red-600 dark:text-red-400 hover:underline">{{ __('messages.appointments_cancel_booking') }}</button>
        </form>
    @endif
@endif
