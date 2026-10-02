@php
    $theme = \App\Utils\EmailTheme::owner($role ?? null);

    $start = $event->starts_at && $sale->event_date ? $event->getStartDateTime($sale->event_date, true) : null;
    $when = $start ? ($event->is_multi_day ? $event->getDateRangeDisplay($sale->event_date) : $start->translatedFormat('l, F j, Y')) : null;
    $time = $start && ! $event->is_multi_day ? $event->getStartEndTime($sale->event_date, (bool) $role?->use_24_hour_time) : null;

    $allSaleTickets = $sale->saleTickets->toBase();
    if (! empty($groupedSales) && $groupedSales->count() > 0) {
        foreach ($groupedSales as $gs) {
            $allSaleTickets = $allSaleTickets->merge($gs->saleTickets);
        }
    }
    $summarise = fn ($tickets, string $fallback) => $tickets->groupBy(fn ($st) => $st->ticket->type)
        ->map(fn ($group, $type) => ['name' => $type ?: $fallback, 'value' => '× '.$group->sum('quantity')])
        ->values()->all();
    $ticketRows = $summarise($allSaleTickets->filter(fn ($st) => $st->ticket && ! $st->ticket->is_addon), __('messages.ticket'));
    $addonRows = $summarise($allSaleTickets->filter(fn ($st) => $st->ticket && $st->ticket->is_addon), __('messages.add_on'));

    $plan = $sale->installmentPlan;
@endphp
<x-email.layout :theme="$theme" :title="__('messages.new_sale_notification_subject', ['event' => $event->name])" :preheader="$total.' · '.$event->name">
<x-email.heading :eyebrow="__('messages.new_sale')" auto>{{ $event->name }}</x-email.heading>

<x-email.text>{{ __('messages.new_sale_notification_greeting', ['name' => $recipient?->name ?? __('messages.hello')]) }},</x-email.text>

<x-email.highlight :value="$total" :caption="$paymentStatus.($when ? ' · '.$when : '').($time ? ' · '.$time : '')" />

{{-- Without this the organizer reads "Paid, EUR 1,200" when EUR 300 arrived. The total above is
     the ticket price, which is genuinely what was sold; this says how much of it has actually
     been collected so far. --}}
@if ($plan && $plan->status !== 'cancelled')
<x-email.callout tone="warning" :title="__('messages.payment_plan')">
{{ __('messages.installments_progress', ['paid' => $plan->paidCount(), 'count' => $plan->installment_count]) }}
&middot; {{ \App\Utils\MoneyUtils::format($plan->amount_paid, $plan->currency) }} {{ __('messages.installments_collected') }}
&middot; {{ \App\Utils\MoneyUtils::format($plan->amountRemaining(), $plan->currency) }} {{ __('messages.installments_outstanding') }}
</x-email.callout>
@endif

<x-email.details>
<x-email.item :label="__('messages.buyer')">{{ $sale->name }}</x-email.item>
@if ($sale->phone)
<x-email.item :label="__('messages.phone_number')" ltr>{{ $sale->phone }}</x-email.item>
@endif
<x-email.item :label="__('messages.email')" ltr wide>{{ $sale->email }}</x-email.item>
</x-email.details>

@if (! empty($groupedSales) && $groupedSales->count() > 0)
<x-email.section :label="__('messages.guests')" />
<x-email.list :items="$groupedSales->map(fn ($g) => $g->name.' ('.$g->email.')'.($g->phone ? ' - '.$g->phone : ''))->all()" />
@endif

@if ($ticketRows)
<x-email.section :label="__('messages.ticket_details')" />
<x-email.items :rows="$ticketRows" />
@endif

@if ($addonRows)
<x-email.section :label="__('messages.add_ons')" />
<x-email.items :rows="$addonRows" />
@endif

<x-email.button :href="$salesUrl">{{ __('messages.view_sales') }}</x-email.button>

<x-slot:footer>
@if ($unsubscribeUrl && empty($notificationEmailUnsubscribeUrl))
<x-email.footer :links="[[__('messages.unsubscribe'), $unsubscribeUrl]]" />
@endif
@include('emails.partials.notification_email_footer', ['scheduleName' => $role?->name])
</x-slot:footer>
</x-email.layout>
