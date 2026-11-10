{{--
    The "Add to ... Wallet" links under a ticket in a guest email (ticket_purchase, ticket_reminder).
    Wants $googleWalletUrl, $googleWalletBadge, $appleWalletUrl and $appleWalletBadge (each null when
    that wallet is off or the ticket may not be a pass; see WalletPasses::eligible()). Badges are
    embedded, never hotlinked: this app never asks a recipient's mail client to fetch an asset from
    someone else's server. A sent mail carries them as cid:, a preview (Mailer::render()) as data:.
    Google's badge may not be recoloured or rebuilt, so it ships as their own artwork; Apple's is the
    operator's to install (partials/wallet-buttons says why), and without it the link is plain text.
--}}
@if (($googleWalletUrl ?? null) || ($appleWalletUrl ?? null))
<x-email.text align="center" :gap="24">
@if ($appleWalletUrl ?? null)
@if (($appleWalletBadge ?? null) && is_file($appleWalletBadge))
@php($appleBadgeSize = getimagesize($appleWalletBadge) ?: [160, 50])
<a href="{{ $appleWalletUrl }}" style="text-decoration: none; display: inline-block; margin: 4px;"><img src="{{ $message->embedData(file_get_contents($appleWalletBadge), 'add-to-apple-wallet.png', 'image/png') }}" alt="{{ __('messages.add_to_apple_wallet') }}" width="{{ (int) round($appleBadgeSize[0] * 50 / max(1, $appleBadgeSize[1])) }}" height="50" style="display: inline-block; height: 50px; width: auto; border: 0;" /></a>
@else
<span style="display: inline-block; margin: 4px; vertical-align: middle;"><x-email.link :href="$appleWalletUrl">{{ __('messages.add_to_apple_wallet') }}</x-email.link></span>
@endif
@endif
@if (($googleWalletUrl ?? null) && ($googleWalletBadge ?? null) && is_file($googleWalletBadge))
@php($googleBadgeSize = getimagesize($googleWalletBadge) ?: [283, 50])
<a href="{{ $googleWalletUrl }}" target="_blank" rel="noopener" style="text-decoration: none; display: inline-block; margin: 4px;"><img src="{{ $message->embedData(file_get_contents($googleWalletBadge), 'add-to-google-wallet.png', 'image/png') }}" alt="{{ __('messages.add_to_google_wallet') }}" width="{{ (int) round($googleBadgeSize[0] * 50 / max(1, $googleBadgeSize[1])) }}" height="50" style="display: inline-block; height: 50px; width: auto; border: 0;" /></a>
@endif
</x-email.text>
@endif
