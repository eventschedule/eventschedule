<?php

return [
    'templates' => [
        'claim_role' => [
            'label' => 'Role invitation email',
            'description' => 'Sent to performers when they are added to an event schedule.',
            'enabled' => true,
            'subject' => ':venue_name scheduled an event for :role_name',
            'subject_curated' => ':event_name at :venue_name was added to the :curator_name schedule',
            'body' => <<<'MD'
# Hello!

:subject_line

[View Event](:event_url)

Sign up to customize the event page or feel free to ignore this email.

[Sign Up](:sign_up_url)

To unsubscribe from future events [click here](:unsubscribe_url).

Thanks,  
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':role_name' => 'Name of the role or performer.',
                ':venue_name' => 'Name of the venue where the event takes place.',
                ':curator_name' => 'Name of the curator schedule, when applicable.',
                ':organizer_name' => 'Name of the organizer who created the event.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':sign_up_url' => 'Link for the recipient to sign up and claim the event.',
                ':unsubscribe_url' => 'Link that lets the recipient unsubscribe from future invitations.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
        'claim_venue' => [
            'label' => 'Venue invitation email',
            'description' => 'Sent to venue contacts when an event is scheduled at their location.',
            'enabled' => true,
            'subject' => ':role_name scheduled an event at :venue_name',
            'subject_curated' => ':event_name at :venue_name was added to the :curator_name schedule',
            'body' => <<<'MD'
# Hello!

:subject_line

[View Event](:event_url)

Sign up to customize the event page or feel free to ignore this email.

[Sign Up](:sign_up_url)

To unsubscribe from future events [click here](:unsubscribe_url).

Thanks,  
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':role_name' => 'Name of the role or performer.',
                ':venue_name' => 'Name of the venue where the event takes place.',
                ':curator_name' => 'Name of the curator schedule, when applicable.',
                ':organizer_name' => 'Name of the organizer who created the event.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':sign_up_url' => 'Link for the recipient to sign up and claim the event.',
                ':unsubscribe_url' => 'Link that lets the recipient unsubscribe from future invitations.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
        'ticket_sale_purchaser' => [
            'label' => 'Ticket reservation confirmation (purchaser)',
            'description' => 'Sent to attendees after they reserve tickets for an event.',
            'enabled' => true,
            'subject' => 'Your ticket reservation for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

- **Event:** :event_name
- **Date:** :event_date
- **Tickets:** :ticket_quantity

[View Event](:event_url)

[View Your Tickets](:ticket_view_url)

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':ticket_quantity' => 'Number of tickets reserved in the order.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':ticket_view_url' => 'Private link where the purchaser can view their order and tickets.',
                ':buyer_name' => 'Name provided by the purchaser.',
                ':buyer_email' => 'Email address provided by the purchaser.',
                ':order_reference' => 'Internal reference number for the order.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
        'ticket_sale_organizer' => [
            'label' => 'Ticket reservation notification (organizer)',
            'description' => 'Sent to organizers when a new ticket reservation is created.',
            'enabled' => true,
            'subject' => 'New ticket reservation for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

- **Buyer:** :buyer_name (:buyer_email)
- **Tickets:** :ticket_quantity
- **Date:** :event_date
- **Total:** :amount_total
- **Order #:** :order_reference

[View Event](:event_url)

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':ticket_quantity' => 'Number of tickets reserved in the order.',
                ':amount_total' => 'Total amount reserved or paid, including the currency.',
                ':buyer_name' => 'Name provided by the purchaser.',
                ':buyer_email' => 'Email address provided by the purchaser.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':order_reference' => 'Internal reference number for the order.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
        'ticket_reminder_purchaser' => [
            'label' => 'Ticket payment reminder (purchaser)',
            'description' => 'Sent to attendees to remind them to complete payment for an unpaid reservation.',
            'enabled' => true,
            'subject' => 'Reminder: Complete your ticket reservation for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

- **Event:** :event_name
- **Date:** :event_date
- **Tickets:** :ticket_quantity
- **Total Reserved:** :amount_total
- **Order #:** :order_reference

Complete your payment to keep your tickets. Reminders are sent every :reminder_interval_hours hour(s) until payment is received.

[Complete Payment](:ticket_view_url)

:payment_instructions_section

[View Event](:event_url)

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':ticket_quantity' => 'Number of tickets reserved in the order.',
                ':amount_total' => 'Total amount reserved or paid, including the currency.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':ticket_view_url' => 'Private link where the purchaser can view their order and tickets.',
                ':order_reference' => 'Internal reference number for the order.',
                ':app_name' => 'The application name configured in settings.',
                ':reminder_interval_hours' => 'Number of hours between payment reminder emails.',
                ':payment_instructions_section' => 'Payment instructions defined on the event, including a translated heading when available.',
            ],
        ],
        'ticket_timeout_purchaser' => [
            'label' => 'Ticket reservation timeout (purchaser)',
            'description' => 'Sent to attendees when an unpaid ticket reservation expires.',
            'enabled' => true,
            'subject' => 'Ticket reservation expired for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

Payment was not completed within the :expire_after_hours-hour reservation window, so your tickets have been released.

- **Event:** :event_name
- **Date:** :event_date
- **Tickets:** :ticket_quantity
- **Total Reserved:** :amount_total
- **Order #:** :order_reference

[Review Reservation](:ticket_view_url)

[View Event](:event_url)

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':ticket_quantity' => 'Number of tickets reserved in the order.',
                ':amount_total' => 'Total amount reserved or paid, including the currency.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':ticket_view_url' => 'Private link where the purchaser can review their order.',
                ':order_reference' => 'Internal reference number for the order.',
                ':expire_after_hours' => 'Number of hours the reservation remained active before expiring.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
        'ticket_timeout_organizer' => [
            'label' => 'Ticket reservation timeout (organizer)',
            'description' => 'Sent to organizers when an unpaid ticket reservation expires.',
            'enabled' => true,
            'subject' => 'Ticket reservation expired for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

The reservation expired after :expire_after_hours hour(s) without payment.

- **Buyer:** :buyer_name (:buyer_email)
- **Tickets:** :ticket_quantity
- **Date:** :event_date
- **Total Reserved:** :amount_total
- **Order #:** :order_reference

[View Tickets](:ticket_view_url)

[View Event](:event_url)

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':ticket_quantity' => 'Number of tickets reserved in the order.',
                ':amount_total' => 'Total amount reserved or paid, including the currency.',
                ':buyer_name' => 'Name provided by the purchaser.',
                ':buyer_email' => 'Email address provided by the purchaser.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':ticket_view_url' => 'Link to the event page filtered to ticket information.',
                ':order_reference' => 'Internal reference number for the order.',
                ':expire_after_hours' => 'Number of hours the reservation remained active before expiring.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
        'ticket_cancelled_purchaser' => [
            'label' => 'Ticket reservation cancelled (purchaser)',
            'description' => 'Sent to attendees when their ticket reservation is cancelled.',
            'enabled' => true,
            'subject' => 'Ticket reservation cancelled for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

Your ticket reservation has been cancelled. If you have any questions, please contact the event organizer.

- **Event:** :event_name
- **Date:** :event_date
- **Tickets:** :ticket_quantity
- **Total Reserved:** :amount_total
- **Order #:** :order_reference

[View Event](:event_url)

[View Reservation](:ticket_view_url)

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':ticket_quantity' => 'Number of tickets reserved in the order.',
                ':amount_total' => 'Total amount reserved or paid, including the currency.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':ticket_view_url' => 'Private link where the purchaser can review their reservation.',
                ':order_reference' => 'Internal reference number for the order.',
                ':buyer_name' => 'Name provided by the purchaser.',
                ':buyer_email' => 'Email address provided by the purchaser.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
        'ticket_cancelled_organizer' => [
            'label' => 'Ticket reservation cancelled (organizer)',
            'description' => 'Sent to organizers when a ticket reservation is cancelled.',
            'enabled' => true,
            'subject' => 'Ticket reservation cancelled for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

- **Buyer:** :buyer_name (:buyer_email)
- **Tickets:** :ticket_quantity
- **Date:** :event_date
- **Total Reserved:** :amount_total
- **Order #:** :order_reference

[View Tickets](:ticket_view_url)

[View Event](:event_url)

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':ticket_quantity' => 'Number of tickets reserved in the order.',
                ':amount_total' => 'Total amount reserved or paid, including the currency.',
                ':buyer_name' => 'Name provided by the purchaser.',
                ':buyer_email' => 'Email address provided by the purchaser.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':ticket_view_url' => 'Link to the event page filtered to ticket information.',
                ':order_reference' => 'Internal reference number for the order.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
        'ticket_paid_purchaser' => [
            'label' => 'Ticket payment confirmation (purchaser)',
            'description' => 'Sent to attendees after their order is marked as paid.',
            'enabled' => true,
            'subject' => 'Payment received for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

- **Event:** :event_name
- **Date:** :event_date
- **Amount Paid:** :amount_total
- **Order #:** :order_reference

[View Your Tickets](:ticket_view_url)

:wallet_links_markdown

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':amount_total' => 'Total amount paid, including the currency.',
                ':ticket_view_url' => 'Private link where the purchaser can view their order and tickets.',
                ':order_reference' => 'Internal reference number for the order.',
                ':app_name' => 'The application name configured in settings.',
                ':wallet_links_markdown' => 'Markdown links that let purchasers add their tickets to mobile wallets when enabled.',
            ],
        ],
        'ticket_paid_organizer' => [
            'label' => 'Ticket payment notification (organizer)',
            'description' => 'Sent to organizers when an order is marked as paid.',
            'enabled' => true,
            'subject' => 'Ticket payment received for :event_name',
            'body' => <<<'MD'
# Hello!

:subject_line

- **Buyer:** :buyer_name (:buyer_email)
- **Event:** :event_name
- **Date:** :event_date
- **Amount Paid:** :amount_total
- **Order #:** :order_reference

[View Event](:event_url)

Thanks,
:app_name
MD,
            'placeholders' => [
                ':subject_line' => 'The email subject line with placeholders applied.',
                ':event_name' => 'Name of the event.',
                ':event_date' => 'Date of the event, or "Date to be announced" when not available.',
                ':amount_total' => 'Total amount paid, including the currency.',
                ':buyer_name' => 'Name provided by the purchaser.',
                ':buyer_email' => 'Email address provided by the purchaser.',
                ':event_url' => 'Public link where the recipient can view the event.',
                ':order_reference' => 'Internal reference number for the order.',
                ':app_name' => 'The application name configured in settings.',
            ],
        ],
    ],
];
