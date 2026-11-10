{{--
    Wallet pass buttons for a purchased ticket. Include with:
        @include('partials.wallet-buttons', ['sale' => $sale, 'event' => $event])
        @include('partials.wallet-buttons', ['sale' => $sale, 'event' => $legEvent, 'condensed' => true])

    Renders nothing unless a provider is configured AND the sale may be offered as a pass: each
    provider's canOffer() is the predicate its route shares, so a badge can never appear for a
    ticket the route would refuse. Google and Apple each draw their own block; an Apple device sees
    Apple's first, anything else Google's.

    THE WHITE STUB IS NOT DECORATION. Google ships the badge in black only, and its brand
    guidelines say to use it on white and light backgrounds. The ticket page is #0a0a0f and an
    ap-card goes dark in dark mode, so the badge needs to bring its own light ground with it. The
    8px padding doubles as the 8dp clear space the same guidelines require. Never restyle, recolour
    or rebuild the artwork itself - only Google's own asset may be used.

    APPLE'S BADGE IS THE OPERATOR'S TO ADD. Apple licenses the "Add to Apple Wallet" artwork per
    developer account, so it is not in the repository: an install that puts Apple's files in
    public/images/wallet/apple ({locale}.svg) shows them, black and unaltered as Apple's guidelines
    require; until then the button is plain text with no imitation of the badge.
--}}
@php
    $walletSale = $sale ?? null;
    $walletEvent = $event ?? null;
    $walletCondensed = $condensed ?? false;
    $walletGoogle = \App\Services\Wallet\GoogleWalletService::canOffer($walletSale, $walletEvent);
    $walletApple = \App\Services\Wallet\AppleWalletService::canOffer($walletSale, $walletEvent);
    $walletAppleFirst = (bool) preg_match('/iPhone|iPad|iPod|Macintosh/', (string) request()->userAgent());
@endphp

@if ($walletGoogle || $walletApple)
    @php
        $walletParams = ['event_id' => \App\Utils\UrlUtils::encodeId($walletEvent->id), 'secret' => $walletSale->secret];
        $walletBlocks = [];
        if ($walletApple) {
            $walletBlocks['apple'] = [
                'href' => route('ticket.wallet.apple', $walletParams),
                'label' => __('messages.add_to_apple_wallet'),
                'badge' => \App\Services\Wallet\AppleWalletService::badge(),
            ];
        }
        if ($walletGoogle) {
            $walletBlocks['google'] = [
                'href' => route('ticket.wallet.google', $walletParams),
                'label' => __('messages.add_to_google_wallet'),
                'badge' => 'images/wallet/google/'.\App\Services\Wallet\GoogleWalletService::badgeLocale().($walletCondensed ? '-condensed' : '').'.svg',
            ];
        }
        if (! $walletAppleFirst && count($walletBlocks) === 2) {
            $walletBlocks = array_reverse($walletBlocks, true);
        }
    @endphp
    @foreach ($walletBlocks as $walletKind => $walletBlock)
        @if ($walletKind === 'google')
            <a href="{{ $walletBlock['href'] }}"
               target="_blank"
               rel="noopener"
               title="{{ $walletBlock['label'] }}"
               class="print:hidden inline-flex items-center justify-center rounded-2xl bg-white p-[8px] shadow-lg shadow-black/20 transition-shadow duration-200 hover:shadow-xl">
                <img src="{{ asset($walletBlock['badge']) }}"
                     alt="{{ $walletBlock['label'] }}"
                     class="{{ $walletCondensed ? 'h-[48px]' : 'h-[50px]' }} w-auto" />
            </a>
        @elseif ($walletBlock['badge'])
            {{-- Apple's own artwork: no ground of our own and no target, the .pkpass opens Wallet. --}}
            <a href="{{ $walletBlock['href'] }}"
               title="{{ $walletBlock['label'] }}"
               class="print:hidden inline-flex items-center justify-center">
                <img src="{{ asset($walletBlock['badge']) }}"
                     alt="{{ $walletBlock['label'] }}"
                     class="{{ $walletCondensed ? 'h-[48px]' : 'h-[50px]' }} w-auto" />
            </a>
        @else
            {{-- Plain CSS (guest-ticket-styles), not utility classes: a class the built stylesheet
                 does not have yet renders as nothing. As tall as Google's badge with its ground. --}}
            <a href="{{ $walletBlock['href'] }}" class="gk-wallet-text {{ $walletCondensed ? 'is-condensed' : '' }}">{{ $walletBlock['label'] }}</a>
        @endif
    @endforeach
@endif
