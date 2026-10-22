<?php

/*
 * N+1 queries the suite already knows about (tests/Support/QueryGuard.php).
 *
 * A key is `rule|file that asked|what it asked for`:
 *  - lazy:   an Eloquent relation lazy loaded on two or more models of one result
 *  - repeat: the same SELECT more than QueryGuard::REPEAT_LIMIT times in one request, job or command
 *
 * A finding whose key is not here fails the test that ran it. This list only shrinks: fix an entry
 * and take it off (CI fails a push that changed the file an entry names when no test hits it any
 * more). The value says why. "baseline YYYY-MM-DD" means it was here when the guard arrived and
 * nobody has looked yet; an entry added on purpose says in a sentence why it cannot be one query.
 */

return [
    'repeat|app/Services/CalendarArrival.php|calendar_import_holds|app/Http/Controllers/EventFeedController.php' => 'Publish all on a feed that is not being read publishes up to FeedActions::PUBLISH_AT_ONCE (25) events in the request, and each publish is a save of its own with its own webhooks and calendar push',
    'repeat|app/Services/WebhookService.php|roles|app/Http/Controllers/EventFeedController.php' => 'Publish all on a feed that is not being read publishes up to FeedActions::PUBLISH_AT_ONCE (25) events in the request, and each publish is a save of its own with its own webhooks and calendar push',
    'repeat|app/Services/WebhookService.php|webhooks|app/Http/Controllers/EventFeedController.php' => 'Publish all on a feed that is not being read publishes up to FeedActions::PUBLISH_AT_ONCE (25) events in the request, and each publish is a save of its own with its own webhooks and calendar push',
    'repeat|app/Models/Event.php|subscriptions|app/Http/Controllers/EventFeedController.php' => 'Publish all on a feed that is not being read publishes up to FeedActions::PUBLISH_AT_ONCE (25) events in the request, and each publish is a save of its own with its own webhooks and calendar push',
    'lazy|app/Traits/RoleBillable.php|App\\Models\\Role::subscriptions|-' => 'outside a request only: FreePlanLimitsTest asks the plan checks of separate fresh copies of one event, each resolving its own schedule, so there is no list to load them for',
];
