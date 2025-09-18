<?php

return [
    'templates' => [
        'claim_role' => [
            'label' => 'Role invitation email',
            'description' => 'Sent to performers when they are added to an event schedule.',
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
    ],
];
