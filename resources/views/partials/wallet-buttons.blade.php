{{--
    Wallet pass buttons for a purchased ticket. Include with:
        @include('partials.wallet-buttons', ['sale' => $sale, 'event' => $event])
        @include('partials.wallet-buttons', ['sale' => $sale, 'event' => $legEvent, 'condensed' => true])

    Renders nothing unless a provider is configured AND the sale may be offered as a pass -
    GoogleWalletService::canOffer() is the single predicate the route shares, so a badge can never
    appear for a ticket the route would refuse.

    This partial is the seam for a second provider: Apple Wallet would add its own block here and
    its own asset folder, and no call site would change.

    THE WHITE STUB IS NOT DECORATION. Google ships the badge in black only, and its brand
    guidelines say to use it on white and light backgrounds. The ticket page is #0a0a0f and an
    ap-card goes dark in dark mode, so the badge needs to bring its own light ground with it. The
    8px padding doubles as the 8dp clear space the same guidelines require. Never restyle, recolour
    or rebuild the artwork itself - only Google's own asset may be used.
--}}
@php
    $walletSale = $sale ?? null;
    $walletEvent = $event ?? null;
    $walletCondensed = $condensed ?? false;
@endphp

@if (\App\Services\Wallet\GoogleWalletService::canOffer($walletSale, $walletEvent))
    @php
        $walletBadge = 'images/wallet/google/'
            . \App\Services\Wallet\GoogleWalletService::badgeLocale()
            . ($walletCondensed ? '-condensed' : '') . '.svg';
        $walletLabel = __('messages.add_to_google_wallet');
    @endphp
    <a href="{{ route('ticket.wallet.google', [
            'event_id' => \App\Utils\UrlUtils::encodeId($walletEvent->id),
            'secret' => $walletSale->secret,
       ]) }}"
       target="_blank"
       rel="noopener"
       title="{{ $walletLabel }}"
       class="print:hidden inline-flex items-center justify-center rounded-2xl bg-white p-[8px] shadow-lg shadow-black/20 transition-shadow duration-200 hover:shadow-xl">
        <img src="{{ asset($walletBadge) }}"
             alt="{{ $walletLabel }}"
             class="{{ $walletCondensed ? 'h-[48px]' : 'h-[50px]' }} w-auto" />
    </a>
@endif
