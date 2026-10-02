@php
    $theme = \App\Utils\EmailTheme::owner($role ?? null);
    $overdue = $kind === 'overdue';
    $heading = $overdue ? __('messages.installment_digest_overdue_heading') : __('messages.installment_digest_due_heading');
    $body = $overdue ? __('messages.installment_digest_overdue_body') : __('messages.installment_digest_due_body');
    $totalLabel = \App\Utils\MoneyUtils::format($total, $currency ?? 'USD');
    $across = trans_choice('messages.installment_digest_across', count($rows), ['count' => count($rows)]);

    // Buyer-supplied names and event names. Escaped by the table component, and this is an
    // email so there is no Vue mount to worry about.
    //
    // The date arrives as Y-m-d and is formatted here, in the organizer's language. Its day and
    // month are held together so four columns at phone width break it in two, not three, while
    // the year may still wrap so the table fits the card.
    $dueDate = fn ($value) => filled($value)
        ? preg_replace('/^(\d+) /u', "\$1\u{00A0}", \Carbon\Carbon::parse($value)->translatedFormat('j M Y'))
        : '';
    $tableRows = array_map(fn ($row) => [
        $row['name'],
        $row['event'],
        str_replace(' ', "\u{00A0}", \App\Utils\MoneyUtils::format($row['amount'], $row['currency'])),
        $dueDate($row['due_at']),
    ], $rows);
@endphp
<x-email.layout :theme="$theme" :title="$heading" :preheader="$totalLabel.' '.$across">
{{-- The total is the headline: what the organizer is being told about, at a glance. --}}
<x-email.heading :eyebrow="$heading" :tone="$overdue ? 'warning' : null" :subtitle="$across">{{ $totalLabel }}</x-email.heading>

<x-email.text>{{ $body }}</x-email.text>

<x-email.table :head="[__('messages.name'), __('messages.event'), __('messages.amount'), __('messages.date')]" :align="['start', 'start', 'end', 'end']" :rows="$tableRows" />

<x-email.button :href="route('sales', ['tab' => 'installments'])">{{ __('messages.installment_digest_view_tab') }}</x-email.button>

<x-slot:footer>
@include('emails.partials.notification_email_footer', ['scheduleName' => $role?->name])
</x-slot:footer>
</x-email.layout>
