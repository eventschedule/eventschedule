{{-- A campaign's state, as the portal's status mark (a dot and a word). One map for the list and
     both campaign pages: each used to carry its own table of pill colours, and they disagreed
     (a campaign awaiting review was amber on its own page and grey in the list).
     A boost that runs through a partner's own Page has words of its own (`partner`): its
     "rejected" is Facebook's verdict, while on the network channel it is our moderators'. --}}
@php
    $boostPartner = $partner ?? false;
    $boostStatusTone = match ($status) {
        'active' => 'is-on',
        // Waiting on somebody: the card, the operator's review, the partner, or Facebook.
        'pending_payment', 'pending_review', 'pending_approval', 'awaiting_payment', 'in_review', 'declined' => 'is-warn',
        'paused', 'ready' => 'is-info',
        'failed', 'rejected' => 'is-bad',
        default => '',
    };
    $boostStatusKey = ($boostPartner ? 'messages.boost_partner_status_' : 'messages.boost_status_').$status;
@endphp
<span class="event-status {{ $boostStatusTone }}">{{ \Illuminate\Support\Facades\Lang::has($boostStatusKey) ? __($boostStatusKey) : \Illuminate\Support\Str::headline($status) }}</span>
