<?php

namespace App\Utils;

use App\Models\Newsletter;
use App\Models\Role;

/**
 * The addresses the newsletter builder works with, for a schedule's newsletter or the platform
 * admin's. One list for the page that draws the builder (newsletter/partials/_builder) and for
 * the JSON a first save answers with: a new newsletter is saved without leaving the page, and the
 * builder then carries on with the addresses of the newsletter that now exists.
 */
class NewsletterBuilderRoutes
{
    /**
     * @param  Role|null  $role  the schedule; null on the platform admin's pages
     * @param  Newsletter|null  $newsletter  a saved newsletter, or null on the create page
     * @param  bool  $templateMode  the page that edits a saved template, which sends nothing
     */
    public static function for(?Role $role, ?Newsletter $newsletter, bool $templateMode = false): array
    {
        $saved = $newsletter && $newsletter->exists;

        if (! $role) {
            $routes = [
                'manage_segments' => route('admin.newsletters.segments'),
                'upload_image' => route('admin.newsletters.upload_image'),
                'audience' => route('admin.newsletters.audience'),
            ];

            if (! $saved) {
                return $routes + [
                    'store' => route('admin.newsletters.store'),
                    'preview' => route('admin.newsletters.preview_draft'),
                ];
            }

            $hash = ['hash' => UrlUtils::encodeId($newsletter->id)];

            return $routes + [
                'edit' => route('admin.newsletters.edit', $hash),
                'update' => route('admin.newsletters.update', $hash),
                'preview' => route('admin.newsletters.preview', $hash),
                'test_send' => route('admin.newsletters.test_send', $hash),
                'schedule' => route('admin.newsletters.schedule', $hash),
                'send' => route('admin.newsletters.send', $hash),
            ] + ($templateMode ? [] : ['save_as_template' => route('admin.newsletters.save_as_template', $hash)]);
        }

        $roleParam = ['role_id' => UrlUtils::encodeId($role->id)];
        $routes = [
            'manage_segments' => route('newsletter.segments', $roleParam),
            'upload_image' => route('newsletter.upload_image', $roleParam),
            'audience' => route('newsletter.audience', $roleParam),
        ];

        if (! $saved) {
            return $routes + [
                'store' => route('newsletter.store', $roleParam),
                'preview' => route('newsletter.preview_draft', $roleParam),
            ];
        }

        $params = $roleParam + ['hash' => UrlUtils::encodeId($newsletter->id)];

        return $routes + [
            'edit' => route('newsletter.edit', $params),
            'update' => route('newsletter.update', $params),
            'preview' => route('newsletter.preview', $params),
            'test_send' => route('newsletter.test_send', $params),
            'schedule' => route('newsletter.schedule', $params),
            'send' => route('newsletter.send', $params),
            'ab_test' => route('newsletter.ab_test', $params),
            'ab_send' => route('newsletter.ab_send', $params),
        ] + ($templateMode ? [] : ['save_as_template' => route('newsletter.save_as_template', $params)]);
    }
}
