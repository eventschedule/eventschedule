<?php

return [
    'email_daily_limit' => (int) env('USAGE_EMAIL_DAILY_LIMIT', 500),
    'ai_daily_limit' => (int) env('USAGE_AI_DAILY_LIMIT', 200),
    'gcal_daily_limit' => (int) env('USAGE_GCAL_DAILY_LIMIT', 1000),
    'stripe_daily_limit' => (int) env('USAGE_STRIPE_DAILY_LIMIT', 500),
    'invoice_ninja_daily_limit' => (int) env('USAGE_INVOICE_NINJA_DAILY_LIMIT', 200),
    'caldav_daily_limit' => (int) env('USAGE_CALDAV_DAILY_LIMIT', 500),
    'stuck_translation_attempts' => (int) env('USAGE_STUCK_THRESHOLD', 3),
    'ai_image_daily_limit_trial' => (int) env('AI_IMAGE_DAILY_LIMIT_TRIAL', 3),
    'ai_image_daily_limit_paid' => (int) env('AI_IMAGE_DAILY_LIMIT_PAID', 10),
];
