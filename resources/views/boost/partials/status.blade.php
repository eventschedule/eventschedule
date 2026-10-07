{{-- A campaign's state, as the portal's status mark (a dot and a word). One map for the list and
     both campaign pages: each used to carry its own table of pill colours, and they disagreed
     (a campaign awaiting review was amber on its own page and grey in the list). --}}
@php
    $boostStatusTone = match ($status) {
        'active' => 'is-on',
        // Waiting on somebody: the card, or the operator's review.
        'pending_payment', 'pending_review' => 'is-warn',
        'paused' => 'is-info',
        'failed', 'rejected' => 'is-bad',
        default => '',
    };
    $boostStatusKey = 'messages.boost_status_'.$status;
@endphp
<span class="event-status {{ $boostStatusTone }}">{{ \Illuminate\Support\Facades\Lang::has($boostStatusKey) ? __($boostStatusKey) : \Illuminate\Support\Str::headline($status) }}</span>
