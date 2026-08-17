<?php

namespace App\Services\Payments\Gateways;

use App\Models\Sale;
use App\Models\User;
use App\Services\Payments\CheckoutContext;
use App\Services\Payments\PaymentGatewayDriver;
use App\Utils\InvoiceNinja;
use App\Utils\UrlUtils;
use Symfony\Component\HttpFoundation\Response;

/**
 * Invoice Ninja, in either of its two modes.
 *
 * `invoice` raises an invoice per checkout and sends the buyer to its payment page. `payment_link`
 * hands the whole quantity selection to an Invoice Ninja subscription page and learns what was bought
 * from the post-purchase webhook. Both are per event, which is why this rail cannot be carted.
 */
class InvoiceNinjaGateway extends PaymentGatewayDriver
{
    public function key(): string
    {
        return 'invoiceninja';
    }

    public function label(?User $owner): string
    {
        if ($owner?->invoiceninja_company_name) {
            return 'Invoice Ninja - '.$owner->invoiceninja_company_name;
        }

        return 'Invoice Ninja';
    }

    public function isConfiguredFor(?User $owner): bool
    {
        return (bool) $owner?->invoiceninja_api_key;
    }

    public function referenceUrl(Sale $sale): ?string
    {
        $reference = $sale->transaction_reference;

        if (! $reference) {
            return null;
        }

        // A payment-link sale stores 'sub:<subscription id>' rather than an invoice id, so there is
        // no invoice page to link to. Falls back to showing the raw reference.
        if (str_starts_with($reference, 'sub:')) {
            return null;
        }

        // Deliberately the hosted Invoice Ninja app rather than the owner's own
        // invoiceninja_api_url: this preserves the behaviour the sales table has always had. Owners
        // running their own instance get an unhelpful link here, which is worth revisiting, but not
        // as a silent side effect of this refactor.
        return 'https://app.invoicing.co/#/invoices/'.$reference.'/edit';
    }

    /**
     * Custom UI: connecting is not a credentials form here.
     */
    public function settingsView(): ?string
    {
        return 'profile.partials.payments.invoiceninja';
    }

    /**
     * Invoice Ninja checkout, in whichever mode the owner configured. Moved here from
     * TicketController unchanged.
     */
    public function startCheckout(CheckoutContext $context): Response
    {
        return $this->invoiceninjaCheckout($context->subdomain, $context->sale, $context->event, $context->isEmbed);
    }

    private function invoiceninjaCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        $user = $event->user;

        if ($user->invoiceninja_mode === 'payment_link') {
            return $this->invoiceninjaPaymentLinkCheckout($subdomain, $sale, $event, $isEmbed);
        }

        return $this->invoiceninjaInvoiceCheckout($subdomain, $sale, $event, $isEmbed);
    }

    private function invoiceninjaInvoiceCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        try {
            $user = $event->user;
            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);
            $company = null;

            $foundClient = false;
            $clientMachesEmail = false;
            $requirePassword = false;
            $sendEmail = false;

            $client = $invoiceNinja->findClient($sale->email, $event->ticket_currency_code);

            if ($client) {
                $foundClient = true;
                if (auth()->user() && auth()->user()->email_verified_at) {
                    foreach ($client['contacts'] as $contact) {
                        if ($contact['email'] == auth()->user()->email) {
                            $clientMachesEmail = true;
                        }
                    }
                }
                if (! $clientMachesEmail) {
                    $company = $invoiceNinja->getCompany();
                    $requirePassword = $company['settings']['enable_client_portal_password'];
                }
            } else {
                $client = $invoiceNinja->createClient($sale->name, $sale->email, $event->ticket_currency_code);
            }

            if ($foundClient && ! $clientMachesEmail && ! $requirePassword) {
                $sendEmail = true;
            }

            // For grouped sales, aggregate SaleTickets across all sales in the group
            if ($sale->group_id && $sale->isPrimarySale()) {
                $allGroupSales = Sale::where('group_id', $sale->group_id)->with('saleTickets.ticket')->get();
                $aggregatedTickets = [];
                foreach ($allGroupSales as $gs) {
                    foreach ($gs->saleTickets as $st) {
                        $tid = $st->ticket_id;
                        if (isset($aggregatedTickets[$tid])) {
                            $aggregatedTickets[$tid]['quantity'] += $st->quantity;
                        } else {
                            $aggregatedTickets[$tid] = [
                                'ticket_id' => $tid,
                                'quantity' => $st->quantity,
                                'ticket' => $st->ticket,
                            ];
                        }
                    }
                }
                $invoiceSaleTickets = collect($aggregatedTickets)->map(function ($item) {
                    $st = new \App\Models\SaleTicket(['ticket_id' => $item['ticket_id'], 'quantity' => $item['quantity']]);
                    $st->setRelation('ticket', $item['ticket']);

                    return $st;
                });
            } else {
                $invoiceSaleTickets = $sale->saleTickets;
            }

            $lineItems = [];
            foreach ($invoiceSaleTickets as $saleTicket) {
                $lineItems[] = [
                    'product_key' => $saleTicket->ticket->type ?: ($saleTicket->ticket->is_addon ? __('messages.add_on') : __('messages.tickets')),
                    'notes' => $saleTicket->ticket->description ?: ($saleTicket->ticket->type ?: ($saleTicket->ticket->is_addon ? __('messages.add_on') : __('messages.tickets'))),
                    'quantity' => $saleTicket->quantity,
                    'cost' => $saleTicket->ticket->price,
                ];
            }

            // For grouped primaries, sum discounts across the whole group; otherwise use this sale's own values
            $isGroupedPrimary = $sale->group_id && $sale->isPrimarySale();
            $volumeDiscount = $isGroupedPrimary
                ? (float) Sale::where('group_id', $sale->group_id)->sum('volume_discount_amount')
                : (float) ($sale->volume_discount_amount ?? 0);
            if ($volumeDiscount > 0) {
                $lineItems[] = [
                    'product_key' => __('messages.volume_discount'),
                    'notes' => __('messages.volume_discount'),
                    'quantity' => 1,
                    'cost' => -$volumeDiscount,
                ];
            }

            $promoDiscount = $sale->legTotalDiscount();
            if ($promoDiscount > 0 && $sale->promoCode) {
                $lineItems[] = [
                    'product_key' => __('messages.discount'),
                    'notes' => $sale->promoCode->code,
                    'quantity' => 1,
                    'cost' => -$promoDiscount,
                ];
            }

            $giftTotal = $sale->legTotalGiftCard();
            if ($giftTotal > 0) {
                // The primary can carry no share while a guest does; resolve the card from any group row
                $giftCardRef = $sale->giftCard
                    ?: ($isGroupedPrimary ? Sale::where('group_id', $sale->group_id)->whereNotNull('gift_card_id')->first()?->giftCard : null);
                $lineItems[] = [
                    'product_key' => __('messages.gift_card'),
                    'notes' => $giftCardRef?->formattedCode() ?: __('messages.gift_card'),
                    'quantity' => 1,
                    'cost' => -$giftTotal,
                ];
            }

            $qrCodeUrl = route('ticket.qr_code', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
            $invoice = $invoiceNinja->createInvoice($client['id'], $lineItems, $qrCodeUrl, $sendEmail);

            $sale->transaction_reference = $invoice['id'];
            // Preserve per-seat payment_amount on grouped primaries; only overwrite for ungrouped sales
            if (! $isGroupedPrimary) {
                $sale->payment_amount = $invoice['amount'];
            }
            $sale->save();

            if ($sendEmail) {
                $url = route('ticket.view', ['event_id' => UrlUtils::encodeId($event->id), 'secret' => $sale->secret]);
                if ($isEmbed) {
                    $url .= '?embed=true';
                }

                return redirect($url);
            } else {
                return redirect($invoice['invitations'][0]['link']);
            }
        } catch (\Exception $e) {
            \Log::error('Invoice Ninja invoice checkout failed', [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return back()->with('error', __('messages.error'));
        }
    }

    private function invoiceninjaPaymentLinkCheckout($subdomain, $sale, $event, $isEmbed = false)
    {
        try {
            $user = $event->user;
            $invoiceNinja = new InvoiceNinja($user->invoiceninja_api_key, $user->invoiceninja_api_url);

            // Lazy-create the shared subscription for this event
            if (! $event->invoiceninja_subscription_id) {
                // Create an IN product for each ticket on the event
                foreach ($event->tickets as $ticket) {
                    if (! $ticket->invoiceninja_product_id) {
                        $productKey = $ticket->type ?: __('messages.tickets');
                        $product = $invoiceNinja->createProduct(
                            $productKey,
                            $ticket->description ?: $productKey,
                            $ticket->price
                        );
                        $ticket->invoiceninja_product_id = $product['id'];
                        $ticket->save();
                    }
                }

                // Create IN products for add-ons
                foreach ($event->addons as $addon) {
                    if (! $addon->invoiceninja_product_id) {
                        $productKey = $addon->type ?: __('messages.add_on');
                        $product = $invoiceNinja->createProduct(
                            $productKey,
                            $addon->description ?: $productKey,
                            $addon->price
                        );
                        $addon->invoiceninja_product_id = $product['id'];
                        $addon->save();
                    }
                }

                $optionalProductIds = $event->tickets->pluck('invoiceninja_product_id')
                    ->merge($event->addons->pluck('invoiceninja_product_id'))
                    ->filter()->values()->toArray();

                $encodedEventId = UrlUtils::encodeId($event->id);
                $subscriptionName = 'ES-'.($event->name ?: $encodedEventId).'-'.time();

                $webhookConfig = [
                    'post_purchase_url' => route('invoiceninja.event_purchase_webhook', ['event' => $encodedEventId]),
                    'post_purchase_rest_method' => 'post',
                    'post_purchase_headers' => ['X-Webhook-Secret' => $user->invoiceninja_webhook_secret],
                ];

                $promoCode = $event->promoCodes()->where('is_active', true)->first();

                $subscription = $invoiceNinja->createSubscription(
                    $subscriptionName,
                    $optionalProductIds,
                    $webhookConfig,
                    'auth.login-or-register,cart',
                    $promoCode?->code,
                    $promoCode ? (float) $promoCode->value : 0,
                    $promoCode ? ($promoCode->type !== 'percentage') : true
                );

                $event->invoiceninja_subscription_id = $subscription['id'];
                $event->invoiceninja_subscription_url = $subscription['purchase_page'];
                $event->save();
            }

            $sale->transaction_reference = 'sub:'.$event->invoiceninja_subscription_id;
            $sale->save();

            $purchaseUrl = $event->invoiceninja_subscription_url.'/v3';

            return redirect($purchaseUrl);
        } catch (\Exception $e) {
            \Log::warning('Invoice Ninja payment link checkout failed, falling back to invoice mode', [
                'sale_id' => $sale->id,
                'event_id' => $event->id,
                'error' => $e->getMessage(),
            ]);

            return $this->invoiceninjaInvoiceCheckout($subdomain, $sale, $event, $isEmbed);
        }
    }
}
