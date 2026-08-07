<?php

return [
    'email_daily_limit' => (int) env('USAGE_EMAIL_DAILY_LIMIT', 500),
    'ai_daily_limit' => (int) env('USAGE_AI_DAILY_LIMIT', 200),
    'gcal_daily_limit' => (int) env('USAGE_GCAL_DAILY_LIMIT', 1000),
    'stripe_daily_limit' => (int) env('USAGE_STRIPE_DAILY_LIMIT', 500),
    'invoice_ninja_daily_limit' => (int) env('USAGE_INVOICE_NINJA_DAILY_LIMIT', 200),
    'caldav_daily_limit' => (int) env('USAGE_CALDAV_DAILY_LIMIT', 500),
    'stuck_translation_attempts' => (int) env('USAGE_STUCK_THRESHOLD', 3),

    // How long app:translate may run before it stops cleanly. It is invoked from a web request
    // (AppController::translateData), so it has to finish inside the cron lock and inside whatever
    // PHP-FPM / nginx allow. 0 disables the budget, which is the default for single-schedule runs.
    'translation_max_seconds' => (int) env('USAGE_TRANSLATION_MAX_SECONDS', 240),

    // A row that hit stuck_translation_attempts consecutive failures is retried once this long
    // after its last attempt, so a transient Gemini outage or quota window cannot freeze a
    // schedule permanently. Spend stays bounded at one retry per row per window.
    'translation_retry_after_hours' => (int) env('USAGE_TRANSLATION_RETRY_HOURS', 24),
    'ai_image_daily_limit_trial' => (int) env('AI_IMAGE_DAILY_LIMIT_TRIAL', 3),
    'ai_image_daily_limit_paid' => (int) env('AI_IMAGE_DAILY_LIMIT_PAID', 10),
    'ai_parse_daily_limit_trial' => (int) env('AI_PARSE_DAILY_LIMIT_TRIAL', 10),
    'ai_parse_daily_limit_pro' => (int) env('AI_PARSE_DAILY_LIMIT_PRO', 50),
    'ai_parse_daily_limit_enterprise' => (int) env('AI_PARSE_DAILY_LIMIT_ENTERPRISE', 100),
    'ai_agenda_daily_limit_enterprise' => (int) env('AI_AGENDA_DAILY_LIMIT_ENTERPRISE', 10),
    'ai_content_daily_limit_enterprise' => (int) env('AI_CONTENT_DAILY_LIMIT_ENTERPRISE', 50),

    // Anti-abuse caps on how many events a single schedule / user may create per day (hosted only).
    // Generous enough that no legitimate organizer is affected, low enough that mass abuse is stopped.
    'event_create_daily_limit_trial' => (int) env('EVENT_CREATE_DAILY_LIMIT_TRIAL', 100),
    'event_create_daily_limit_pro' => (int) env('EVENT_CREATE_DAILY_LIMIT_PRO', 500),
    'event_create_daily_limit_enterprise' => (int) env('EVENT_CREATE_DAILY_LIMIT_ENTERPRISE', 1000),
    'event_create_user_daily_limit_trial' => (int) env('EVENT_CREATE_USER_DAILY_LIMIT_TRIAL', 300),
    'event_create_user_daily_limit_pro' => (int) env('EVENT_CREATE_USER_DAILY_LIMIT_PRO', 1500),
    'event_create_user_daily_limit_enterprise' => (int) env('EVENT_CREATE_USER_DAILY_LIMIT_ENTERPRISE', 3000),

    // Free-plan allowances (hosted only; every paid plan, selfhost and the demo are unlimited).
    // Unlike the anti-abuse caps above these are product limits, so there is only a _free variant:
    // the limit helpers return null for every other tier before any counting happens.
    // The per-user figure is a backstop, not a second product limit - one owner may run many
    // schedules, so it stops the per-schedule allowance being multiplied by spreading events out.
    'ticket_sale_monthly_limit_free' => (int) env('TICKET_SALE_MONTHLY_LIMIT_FREE', 25),
    'ticket_sale_user_monthly_limit_free' => (int) env('TICKET_SALE_USER_MONTHLY_LIMIT_FREE', 50),
    'appointment_type_limit_free' => (int) env('APPOINTMENT_TYPE_LIMIT_FREE', 1),

    // Ceiling on each row table in the /admin/growth export. Hitting it is reported in
    // meta.truncated rather than silently shortening the table.
    'growth_row_cap' => (int) env('GROWTH_ROW_CAP', 20000),

    // How many talent/venue schedules one curator may pull events from, and how many
    // event links a single reconcile pass may write. Hitting the batch ceiling is logged
    // and the remainder is picked up by the next run rather than dropped.
    'curator_source_limit' => (int) env('CURATOR_SOURCE_LIMIT', 100),
    'curator_source_batch' => (int) env('CURATOR_SOURCE_BATCH', 50000),
];
