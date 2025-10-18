<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $event->translatedName() ?? $event->name }}</title>
    <style>
        body { font-family: sans-serif; margin: 0; padding: 2rem; background: #f8fafc; color: #111827; }
        header { margin-bottom: 2rem; }
        a { color: #2563eb; text-decoration: none; }
        a:hover { text-decoration: underline; }
        .card { background: #ffffff; border-radius: 0.75rem; box-shadow: 0 10px 25px rgba(15, 23, 42, 0.08); padding: 2rem; }
        .muted { color: #6b7280; font-size: 0.95rem; }
        .section { margin-top: 1.5rem; }
        .section h2 { margin-bottom: 0.5rem; font-size: 1rem; text-transform: uppercase; letter-spacing: 0.08em; color: #1f2937; }
        .pill { display: inline-block; padding: 0.25rem 0.75rem; border-radius: 9999px; background: #e0e7ff; color: #3730a3; font-size: 0.75rem; }
    </style>
</head>
<body>
<header>
    <a dusk="back-to-schedule-link" href="{{ route('role.view_guest', ['subdomain' => $role->subdomain]) }}">
        ← {{ __('messages.back_to_schedule') }}
    </a>
</header>

<main class="card">
    <div class="muted pill">{{ strtoupper($role->translatedName() ?? $role->name ?? __('messages.curator')) }}</div>

    <h1 style="margin-top: 1rem; font-size: 2rem;">{{ $event->translatedName() ?? $event->name }}</h1>

    @if ($event->starts_at)
        @php
            $startsAt = $event->starts_at instanceof \Carbon\Carbon
                ? $event->starts_at
                : \Illuminate\Support\Carbon::parse($event->starts_at);
            $displayTime = optional($startsAt)->copy()
                ?->timezone($role->timezone ?? config('app.timezone'))
                ?->format('M j, Y g:i A');
        @endphp

        @if ($displayTime)
            <p class="muted" style="margin-top: 0.25rem;">
                {{ $displayTime }}
            </p>
        @endif
    @endif

    @if ($event->venue)
        <div class="section">
            <h2>{{ __('messages.venue') }}</h2>
            <p>{{ $event->venue->translatedName() ?? $event->venue->name }}</p>
        </div>
    @endif

    <div class="section">
        <h2>{{ __('messages.description') }}</h2>
        <p>{{ $event->translatedDescription() ?? $event->description ?? __('messages.no_description') }}</p>
    </div>

    @if ($event->roles->isNotEmpty())
        <div class="section">
            <h2>{{ __('messages.featuring') }}</h2>
            <ul>
                @foreach ($event->roles as $eachRole)
                    <li>{{ $eachRole->translatedName() ?? $eachRole->name }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    @php
        $showTicketLink = $event->registration_url || $event->canSellTickets();
        $ticketUrl = $event->registration_url ?: request()->fullUrlWithQuery(['tickets' => 'true']);
        $ticketCtaLabel = $event->registration_url
            ? __('messages.view_event')
            : ($event->areTicketsFree() ? __('messages.get_tickets') : __('messages.buy_tickets'));
    @endphp

    @if ($showTicketLink && request()->get('tickets') !== 'true')
        <div class="section" style="margin-top: 2rem;">
            <a href="{{ $ticketUrl }}" dusk="buy-tickets-button" {{ $event->registration_url ? 'target="_blank"' : '' }}>
                {{ $ticketCtaLabel }}
            </a>
        </div>
    @endif

    @if ($event->canSellTickets() && request()->get('tickets') === 'true')
        <div class="section" style="margin-top: 2rem;">
            <form method="post" action="{{ route('event.checkout', ['subdomain' => $role->subdomain]) }}">
                @csrf
                <input type="hidden" name="event_id" value="{{ App\Utils\UrlUtils::encodeId($event->id) }}">
                <input type="hidden" name="event_date" value="{{ $date }}">
                <input type="hidden" name="subdomain" value="{{ $role->subdomain }}">

                <div style="display: grid; gap: 1.5rem;">
                    <label style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <span>{{ __('messages.name') }} *</span>
                        <input type="text" name="name" value="{{ old('name', optional(auth()->user())->name) }}" required>
                    </label>

                    <label style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <span>{{ __('messages.email') }} *</span>
                        <input type="email" name="email" value="{{ old('email', optional(auth()->user())->email) }}" required>
                    </label>

                    @foreach ($event->tickets as $index => $ticket)
                        <div>
                            <div style="font-weight: 600;">{{ $ticket->type }}</div>
                            @if ($ticket->description)
                                <div class="muted" style="margin-top: 0.25rem;">{{ $ticket->description }}</div>
                            @endif
                            <label style="display: flex; flex-direction: column; gap: 0.5rem; margin-top: 0.75rem;">
                                <span>{{ __('messages.quantity') }}</span>
                                <select id="ticket-{{ $index }}" name="tickets[{{ $ticket->id }}]">
                                    @for ($qty = 0; $qty <= max(10, (int) $ticket->quantity); $qty++)
                                        <option value="{{ $qty }}">{{ $qty }}</option>
                                    @endfor
                                </select>
                            </label>
                        </div>
                    @endforeach

                    <button type="submit" dusk="checkout-button" style="padding: 0.75rem 1.5rem; border-radius: 9999px; background: #4f46e5; color: #fff; font-weight: 600; border: none; cursor: pointer;">
                        {{ __('messages.checkout') }}
                    </button>
                </div>
            </form>
        </div>
    @endif

    <div class="section" style="margin-top: 2rem;">
        <a href="{{ url(route('event.edit', ['subdomain' => $role->subdomain, 'hash' => App\Utils\UrlUtils::encodeId($event->id)], false)) }}"
           dusk="edit-event-link">
            {{ __('messages.edit_event') }}
        </a>
    </div>
</main>
</body>
</html>
