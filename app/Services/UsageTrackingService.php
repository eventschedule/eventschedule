<?php

namespace App\Services;

use App\Models\UsageDaily;

class UsageTrackingService
{
    // Email operations
    const EMAIL_TICKET = 'email_ticket';

    const EMAIL_NEWSLETTER = 'email_newsletter';

    const EMAIL_GRAPHIC = 'email_graphic';

    const EMAIL_TEST = 'email_test';

    // Gemini AI operations
    const GEMINI_TRANSLATE = 'gemini_translate';

    const GEMINI_PARSE_EVENT = 'gemini_parse_event';

    const GEMINI_PARSE_PARTS = 'gemini_parse_parts';

    const GEMINI_BLOG = 'gemini_blog';

    const GEMINI_BLOG_TOPIC = 'gemini_blog_topic';

    const GEMINI_TRANSLATE_GROUPS = 'gemini_translate_groups';

    const GEMINI_TRANSLATE_FIELDS = 'gemini_translate_fields';

    const GEMINI_TRANSLATE_FIELD_OPTIONS = 'gemini_translate_field_options';

    // YouTube
    const YOUTUBE_SEARCH = 'youtube_search';

    // Google Calendar operations
    const GCAL_CREATE = 'gcal_create';

    const GCAL_UPDATE = 'gcal_update';

    const GCAL_DELETE = 'gcal_delete';

    const GCAL_SYNC = 'gcal_sync';

    const GCAL_WEBHOOK = 'gcal_webhook';

    // Stripe operations
    const STRIPE_ACCOUNT = 'stripe_account';

    const STRIPE_PAYMENT = 'stripe_payment';

    const STRIPE_SUBSCRIPTION = 'stripe_subscription';

    // Invoice Ninja operations
    const INVOICENINJA_CLIENT = 'invoiceninja_client';

    const INVOICENINJA_INVOICE = 'invoiceninja_invoice';

    const INVOICENINJA_PAYMENT_LINK = 'invoiceninja_payment_link';

    // CalDAV operations
    const CALDAV_SYNC = 'caldav_sync';

    /**
     * Track a usage operation. Silently fails so tracking never disrupts actual operations.
     */
    public static function track(string $operation, int $roleId = 0): void
    {
        if (! config('app.hosted')) {
            return;
        }

        try {
            UsageDaily::record($operation, $roleId);
        } catch (\Exception $e) {
            // Tracking failures should never disrupt actual operations
        }
    }
}
