<?php

namespace App\Services;

use App\Models\BackupJob;
use App\Models\Event;
use App\Models\EventComment;
use App\Models\EventFeedback;
use App\Models\EventPart;
use App\Models\EventPhoto;
use App\Models\EventPoll;
use App\Models\EventPollVote;
use App\Models\EventVideo;
use App\Models\Group;
use App\Models\Newsletter;
use App\Models\NewsletterAbTest;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterSegmentUser;
use App\Models\NewsletterUnsubscribe;
use App\Models\PromoCode;
use App\Models\Role;
use App\Models\Sale;
use App\Models\SaleTicket;
use App\Models\Ticket;
use App\Models\TicketWaitlist;
use App\Utils\CssUtils;
use App\Utils\MarkdownUtils;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class BackupService
{
    private const ROLE_EXPORT_FIELDS = [
        'type', 'is_unlisted', 'design', 'background', 'background_rotation', 'background_colors',
        'background_color', 'accent_color', 'font_color', 'font_family', 'name', 'name_en',
        'phone', 'email', 'website', 'address1', 'address1_en', 'address2', 'address2_en',
        'city', 'city_en', 'state', 'state_en', 'postal_code', 'country_code', 'language_code',
        'description', 'description_en', 'short_description', 'short_description_en',
        'accept_requests', 'event_request_form', 'require_account', 'use_24_hour_time', 'timezone',
        'formatted_address', 'geo_address', 'geo_lat', 'geo_lon', 'show_email', 'show_phone',
        'require_approval', 'event_layout', 'request_terms', 'request_terms_en', 'custom_css',
        'event_custom_fields', 'graphic_settings', 'agenda_ai_prompt', 'agenda_show_times',
        'agenda_show_description', 'agenda_save_image', 'slug_pattern', 'direct_registration',
        'feedback_enabled', 'feedback_delay_hours', 'fan_comments_enabled', 'fan_photos_enabled',
        'fan_videos_enabled', 'first_day_of_week', 'sponsor_logos', 'sponsor_section_title',
        'sponsor_section_title_en', 'custom_labels', 'ai_style_instructions', 'ai_content_instructions',
        'social_links', 'payment_links', 'youtube_links', 'background_image', 'header_image',
        'profile_image_url', 'header_image_url', 'background_image_url',
    ];

    private const EVENT_EXPORT_EXCLUDE = [
        'id', 'user_id', 'creator_role_id', 'translation_attempts', 'last_translated_at',
        'last_notified_fan_content_count', 'created_at', 'updated_at',
        'description_html', 'description_html_en', 'short_description_html', 'short_description_html_en',
        'ticket_notes_html', 'payment_instructions_html',
    ];

    private const MAX_SCHEDULES = 50;

    private const MAX_EVENTS_PER_SCHEDULE = 10000;

    private const MAX_TICKETS_PER_EVENT = 100;

    private const MAX_SALES_PER_SCHEDULE = 50000;

    private const MAX_RECIPIENTS_PER_SCHEDULE = 100000;

    public function exportSchedules(array $roles, bool $includeImages, BackupJob $job): array
    {
        $schedules = [];
        $imageFiles = [];
        $total = count($roles);

        foreach ($roles as $index => $role) {
            $job->update(['progress' => [
                'current' => $index + 1,
                'total' => $total,
                'current_label' => $role->name,
            ]]);

            $scheduleData = $this->exportRole($role, $includeImages, $imageFiles);
            $schedules[] = $scheduleData;
        }

        $meta = [
            'version' => '1.0',
            'app_version' => config('app.version', '1.0.0'),
            'exported_at' => now()->toIso8601String(),
            'exported_by' => $job->user->email,
            'includes_images' => $includeImages,
        ];

        return [
            'json' => ['meta' => $meta, 'schedules' => $schedules],
            'images' => $imageFiles,
        ];
    }

    private function exportRole(Role $role, bool $includeImages, array &$imageFiles): array
    {
        $stripNewsletterPii = config('app.hosted');

        $roleData = [];
        foreach (self::ROLE_EXPORT_FIELDS as $field) {
            $roleData[$field] = $role->getAttributes()[$field] ?? null;
        }
        $roleData['subdomain'] = $role->subdomain;

        if ($includeImages) {
            $this->collectRoleImages($role, $roleData, $imageFiles);
        }

        $groups = $role->groups()->get();
        $groupsData = [];
        foreach ($groups as $group) {
            $groupsData[] = [
                '_ref_id' => $group->id,
                'name' => $group->name,
                'name_en' => $group->name_en,
                'slug' => $group->slug,
                'color' => $group->color,
            ];
        }

        $events = $role->events()->get();
        $eventsData = [];
        foreach ($events as $event) {
            $eventsData[] = $this->exportEvent($event, $role, $includeImages, $imageFiles);
        }

        $newsletters = Newsletter::where('role_id', $role->id)->get();
        $newslettersData = [];
        foreach ($newsletters as $newsletter) {
            $newslettersData[] = $this->exportNewsletter($newsletter, $stripNewsletterPii);
        }

        $segments = NewsletterSegment::where('role_id', $role->id)->get();
        $segmentsData = [];
        foreach ($segments as $segment) {
            $segmentData = [
                '_ref_id' => $segment->id,
                'name' => $segment->name,
                'type' => $segment->type,
                'filter_criteria' => $segment->filter_criteria,
            ];
            if ($stripNewsletterPii) {
                $segmentData['segment_users'] = [];
            } else {
                $segmentUsers = NewsletterSegmentUser::where('newsletter_segment_id', $segment->id)->get();
                $segmentData['segment_users'] = $segmentUsers->map(function ($user) {
                    return [
                        'email' => $user->email,
                        'name' => $user->name,
                    ];
                })->toArray();
            }
            $segmentsData[] = $segmentData;
        }

        $abTests = NewsletterAbTest::where('role_id', $role->id)->get();
        $abTestsData = $abTests->map(function ($test) {
            return [
                '_ref_id' => $test->id,
                'name' => $test->name,
                'test_field' => $test->test_field,
                'sample_percentage' => $test->sample_percentage,
                'winner_criteria' => $test->winner_criteria,
                'winner_wait_hours' => $test->winner_wait_hours,
                'winner_selected_at' => $test->winner_selected_at?->toDateTimeString(),
                'winner_variant' => $test->winner_variant,
                'status' => $test->status,
            ];
        })->toArray();

        if ($stripNewsletterPii) {
            $unsubscribesData = [];
        } else {
            $unsubscribes = NewsletterUnsubscribe::where('role_id', $role->id)->get();
            $unsubscribesData = $unsubscribes->map(function ($unsub) {
                return [
                    'email' => $unsub->email,
                    'unsubscribed_at' => $unsub->unsubscribed_at,
                ];
            })->toArray();
        }

        return [
            'role' => $roleData,
            'groups' => $groupsData,
            'events' => $eventsData,
            'newsletters' => $newslettersData,
            'newsletter_segments' => $segmentsData,
            'newsletter_ab_tests' => $abTestsData,
            'newsletter_unsubscribes' => $unsubscribesData,
        ];
    }

    private function exportEvent(Event $event, Role $role, bool $includeImages, array &$imageFiles): array
    {
        $pivot = $event->pivot ?? $event->roles()->where('role_id', $role->id)->first()?->pivot;

        $eventData = [];
        $attributes = $event->getAttributes();
        $fillable = $event->getFillable();

        foreach ($fillable as $field) {
            if (in_array($field, self::EVENT_EXPORT_EXCLUDE)) {
                continue;
            }
            $eventData[$field] = $attributes[$field] ?? null;
        }

        $eventData['_ref_id'] = $event->id;
        $eventData['_group_ref_id'] = $pivot?->group_id;
        $eventData['_is_accepted'] = (bool) ($pivot?->is_accepted ?? true);
        $eventData['days_of_week'] = $event->days_of_week;
        $eventData['recurring_include_dates'] = $event->recurring_include_dates;
        $eventData['recurring_exclude_dates'] = $event->recurring_exclude_dates;

        if ($includeImages) {
            $this->collectEventImages($event, $eventData, $imageFiles);
        }

        $eventData['tickets'] = $this->exportTickets($event);
        $eventData['promo_codes'] = $this->exportPromoCodes($event);
        $eventData['sales'] = $this->exportSales($event);
        $eventData['parts'] = $this->exportParts($event);
        $eventData['polls'] = $this->exportPolls($event);
        $eventData['comments'] = $this->exportComments($event);
        $eventData['videos'] = $this->exportVideos($event);
        $eventData['feedbacks'] = $this->exportFeedbacks($event);
        $eventData['waitlists'] = $this->exportWaitlists($event);

        return $eventData;
    }

    private function exportTickets(Event $event): array
    {
        return Ticket::where('event_id', $event->id)->get()->map(function ($ticket) {
            return [
                '_ref_id' => $ticket->id,
                'type' => $ticket->type,
                'quantity' => $ticket->quantity,
                'sold' => $ticket->sold,
                'price' => $ticket->price,
                'description' => $ticket->description,
                'sales_end_at' => $ticket->sales_end_at,
                'custom_fields' => $ticket->custom_fields,
            ];
        })->toArray();
    }

    private function exportPromoCodes(Event $event): array
    {
        return PromoCode::where('event_id', $event->id)->get()->map(function ($code) {
            return [
                '_ref_id' => $code->id,
                '_ticket_ref_ids' => $code->ticket_ids,
                'code' => $code->code,
                'type' => $code->type,
                'value' => $code->value,
                'max_uses' => $code->max_uses,
                'times_used' => $code->times_used,
                'expires_at' => $code->expires_at,
                'is_active' => $code->is_active,
            ];
        })->toArray();
    }

    private function exportSales(Event $event): array
    {
        return Sale::where('event_id', $event->id)->with('saleTickets')->get()->map(function ($sale) {
            $saleData = [
                '_ref_id' => $sale->id,
                '_promo_code_ref_id' => $sale->promo_code_id,
                '_group_ref_id' => $sale->group_id,
                '_newsletter_ref_id' => $sale->newsletter_id,
                'name' => $sale->name,
                'email' => $sale->email,
                'phone' => $sale->phone,
                'event_date' => $sale->event_date,
                'status' => $sale->status,
                'payment_method' => $sale->payment_method,
                'payment_amount' => $sale->payment_amount,
                'transaction_reference' => $sale->transaction_reference,
                'discount_amount' => $sale->discount_amount,
                'utm_source' => $sale->utm_source,
                'utm_medium' => $sale->utm_medium,
                'utm_campaign' => $sale->utm_campaign,
                'feedback_sent_at' => $sale->feedback_sent_at?->toDateTimeString(),
                'created_at' => $sale->created_at?->toDateTimeString(),
            ];

            for ($i = 1; $i <= 10; $i++) {
                $field = "custom_value{$i}";
                $saleData[$field] = $sale->$field;
            }

            $saleData['sale_tickets'] = $sale->saleTickets->map(function ($st) {
                $stData = [
                    '_ticket_ref_id' => $st->ticket_id,
                    'seats' => $st->seats,
                    'quantity' => $st->quantity,
                ];
                for ($i = 1; $i <= 10; $i++) {
                    $field = "custom_value{$i}";
                    $stData[$field] = $st->$field;
                }

                return $stData;
            })->toArray();

            return $saleData;
        })->toArray();
    }

    private function exportParts(Event $event): array
    {
        return EventPart::where('event_id', $event->id)->get()->map(function ($part) {
            return [
                '_ref_id' => $part->id,
                'name' => $part->name,
                'name_en' => $part->name_en,
                'description' => $part->description,
                'description_en' => $part->description_en,
                'start_time' => $part->start_time,
                'end_time' => $part->end_time,
                'sort_order' => $part->sort_order,
            ];
        })->toArray();
    }

    private function exportPolls(Event $event): array
    {
        return EventPoll::where('event_id', $event->id)->get()->map(function ($poll) {
            $votes = EventPollVote::where('event_poll_id', $poll->id)->get()->map(function ($vote) {
                return [
                    'option_index' => $vote->option_index,
                    'event_date' => $vote->event_date,
                ];
            })->toArray();

            return [
                '_ref_id' => $poll->id,
                'question' => $poll->question,
                'options' => $poll->options,
                'is_active' => $poll->is_active,
                'sort_order' => $poll->sort_order,
                'allow_user_options' => $poll->allow_user_options,
                'require_option_approval' => $poll->require_option_approval,
                'pending_options' => $poll->pending_options,
                'votes' => $votes,
            ];
        })->toArray();
    }

    private function exportComments(Event $event): array
    {
        return EventComment::where('event_id', $event->id)->get()->map(function ($comment) {
            return [
                '_event_part_ref_id' => $comment->event_part_id,
                'event_date' => $comment->event_date,
                'comment' => $comment->comment,
                'is_approved' => $comment->is_approved,
            ];
        })->toArray();
    }

    private function exportVideos(Event $event): array
    {
        return EventVideo::where('event_id', $event->id)->get()->map(function ($video) {
            return [
                '_event_part_ref_id' => $video->event_part_id,
                'event_date' => $video->event_date,
                'youtube_url' => $video->youtube_url,
                'is_approved' => $video->is_approved,
            ];
        })->toArray();
    }

    private function exportFeedbacks(Event $event): array
    {
        return EventFeedback::where('event_id', $event->id)->get()->map(function ($feedback) {
            return [
                '_sale_ref_id' => $feedback->sale_id,
                'event_date' => $feedback->event_date,
                'rating' => $feedback->rating,
                'comment' => $feedback->comment,
            ];
        })->toArray();
    }

    private function exportWaitlists(Event $event): array
    {
        return TicketWaitlist::where('event_id', $event->id)->get()->map(function ($wl) {
            return [
                'event_date' => $wl->event_date,
                'name' => $wl->name,
                'email' => $wl->email,
                'status' => $wl->status,
                'locale' => $wl->locale,
                'notified_at' => $wl->notified_at,
                'expires_at' => $wl->expires_at,
            ];
        })->toArray();
    }

    private function exportNewsletter(Newsletter $newsletter, bool $stripPii = false): array
    {
        $data = [
            '_ref_id' => $newsletter->id,
            '_ab_test_ref_id' => $newsletter->ab_test_id,
            '_segment_ref_ids' => $newsletter->segment_ids,
            'subject' => $newsletter->subject,
            'blocks' => $newsletter->blocks,
            'style_settings' => $newsletter->style_settings,
            'template' => $newsletter->template,
            'event_ids' => $newsletter->event_ids,
            'status' => $newsletter->status,
            'scheduled_at' => $newsletter->scheduled_at,
            'sent_at' => $newsletter->sent_at,
            'ab_variant' => $newsletter->ab_variant,
            'sent_count' => $newsletter->sent_count,
            'open_count' => $newsletter->open_count,
            'click_count' => $newsletter->click_count,
            'type' => $newsletter->type,
        ];

        if ($stripPii) {
            $data['recipients'] = [];
        } else {
            $data['recipients'] = NewsletterRecipient::where('newsletter_id', $newsletter->id)
                ->get()
                ->map(function ($r) {
                    return [
                        'email' => $r->email,
                        'name' => $r->name,
                        'status' => $r->status,
                        'sent_at' => $r->sent_at,
                        'error_message' => $r->error_message,
                        'opened_at' => $r->opened_at,
                        'open_count' => $r->open_count,
                        'clicked_at' => $r->clicked_at,
                        'click_count' => $r->click_count,
                    ];
                })->toArray();
        }

        return $data;
    }

    private function collectRoleImages(Role $role, array &$roleData, array &$imageFiles): void
    {
        foreach (['profile_image_url', 'header_image_url', 'background_image_url'] as $field) {
            $rawValue = $role->getAttributes()[$field] ?? null;
            if ($rawValue && ! str_starts_with($rawValue, 'http')) {
                $storagePath = config('filesystems.default') == 'local' ? 'public/'.$rawValue : $rawValue;
                if (Storage::exists($storagePath)) {
                    $imageKey = 'images/'.$rawValue;
                    $imageFiles[$imageKey] = $storagePath;
                    $roleData['_'.$field] = $imageKey;
                }
            }
        }

        // Sponsor logos
        $sponsorLogos = $role->sponsor_logos;
        if (is_array($sponsorLogos)) {
            foreach ($sponsorLogos as $i => $logo) {
                $logoUrl = $logo['image'] ?? null;
                if ($logoUrl && ! str_starts_with($logoUrl, 'http')) {
                    $storagePath = config('filesystems.default') == 'local' ? 'public/'.$logoUrl : $logoUrl;
                    if (Storage::exists($storagePath)) {
                        $imageKey = 'images/'.$logoUrl;
                        $imageFiles[$imageKey] = $storagePath;
                        $roleData['_sponsor_logo_'.$i] = $imageKey;
                    }
                }
            }
        }
    }

    private function collectEventImages(Event $event, array &$eventData, array &$imageFiles): void
    {
        $rawFlyer = $event->getAttributes()['flyer_image_url'] ?? null;
        if ($rawFlyer && ! str_starts_with($rawFlyer, 'http')) {
            $storagePath = config('filesystems.default') == 'local' ? 'public/'.$rawFlyer : $rawFlyer;
            if (Storage::exists($storagePath)) {
                $imageKey = 'images/'.$rawFlyer;
                $imageFiles[$imageKey] = $storagePath;
                $eventData['_flyer_image'] = $imageKey;
            }
        }

        $photos = EventPhoto::where('event_id', $event->id)->get();
        $photosData = [];
        foreach ($photos as $photo) {
            $rawPhoto = $photo->getAttributes()['photo_url'] ?? null;
            if ($rawPhoto && ! str_starts_with($rawPhoto, 'http')) {
                $storagePath = config('filesystems.default') == 'local' ? 'public/'.$rawPhoto : $rawPhoto;
                if (Storage::exists($storagePath)) {
                    $imageKey = 'images/'.$rawPhoto;
                    $imageFiles[$imageKey] = $storagePath;
                    $photosData[] = [
                        '_photo_image' => $imageKey,
                        '_event_part_ref_id' => $photo->event_part_id,
                        'event_date' => $photo->event_date,
                        'is_approved' => $photo->is_approved,
                    ];
                }
            }
        }
        if (! empty($photosData)) {
            $eventData['photos'] = $photosData;
        }
    }

    // ========== IMPORT ==========

    public function validateBackupJson(array $data): array
    {
        $errors = [];

        if (! isset($data['meta']) || ! isset($data['schedules'])) {
            return ['Invalid backup file structure.'];
        }

        $meta = $data['meta'];
        if (! isset($meta['version']) || ! in_array($meta['version'], ['1.0'])) {
            $errors[] = 'Unsupported backup version.';
        }

        if (! is_array($data['schedules']) || empty($data['schedules'])) {
            $errors[] = 'No schedules found in backup.';
        }

        if (count($data['schedules']) > self::MAX_SCHEDULES) {
            $errors[] = 'Too many schedules in backup (max '.self::MAX_SCHEDULES.').';
        }

        return $errors;
    }

    public function getImportPreview(array $data): array
    {
        $preview = [];
        foreach ($data['schedules'] as $schedule) {
            $role = $schedule['role'] ?? [];
            $events = $schedule['events'] ?? [];

            $ticketCount = 0;
            $salesCount = 0;
            $dates = [];

            foreach ($events as $event) {
                $ticketCount += count($event['tickets'] ?? []);
                $salesCount += count($event['sales'] ?? []);
                if (! empty($event['starts_at'])) {
                    $dates[] = $event['starts_at'];
                }
            }

            sort($dates);
            $dateRange = '';
            if (! empty($dates)) {
                $first = \Carbon\Carbon::parse($dates[0])->format('M Y');
                $last = \Carbon\Carbon::parse(end($dates))->format('M Y');
                $dateRange = $first === $last ? $first : $first.' - '.$last;
            }

            $preview[] = [
                'subdomain' => $role['subdomain'] ?? 'unknown',
                'name' => $role['name'] ?? 'Unknown',
                'type' => $role['type'] ?? 'talent',
                'events' => count($events),
                'tickets' => $ticketCount,
                'sales' => $salesCount,
                'newsletters' => count($schedule['newsletters'] ?? []),
                'date_range' => $dateRange,
            ];
        }

        return $preview;
    }

    public function importSchedules(array $data, array $selectedIndices, int $userId, BackupJob $job): array
    {
        $report = [];
        $total = count($selectedIndices);
        $schedules = $data['schedules'];
        $includesImages = $data['meta']['includes_images'] ?? false;

        foreach ($selectedIndices as $step => $index) {
            if (! isset($schedules[$index])) {
                continue;
            }

            $scheduleData = $schedules[$index];
            $scheduleName = $scheduleData['role']['name'] ?? 'Unknown';

            $job->update(['progress' => [
                'current' => $step + 1,
                'total' => $total,
                'current_label' => $scheduleName,
            ]]);

            try {
                $scheduleReport = $this->importSchedule($scheduleData, $userId, $includesImages, $job);
                $report[] = array_merge(['name' => $scheduleName], $scheduleReport);
            } catch (\Exception $e) {
                report($e);
                $report[] = [
                    'name' => $scheduleName,
                    'error' => 'Failed to import schedule.',
                    'schedules' => ['success' => 0, 'failed' => 1, 'failures' => [$scheduleName.': Import failed']],
                ];
            }
        }

        return $report;
    }

    private function importSchedule(array $scheduleData, int $userId, bool $includesImages, BackupJob $job): array
    {
        $report = [
            'schedules' => ['success' => 0, 'failed' => 0, 'failures' => []],
            'sub_schedules' => ['success' => 0, 'failed' => 0, 'failures' => []],
            'events' => ['success' => 0, 'failed' => 0, 'failures' => []],
            'tickets' => ['success' => 0, 'failed' => 0, 'failures' => []],
            'sales' => ['success' => 0, 'failed' => 0, 'failures' => []],
            'promo_codes' => ['success' => 0, 'failed' => 0, 'failures' => []],
            'newsletters' => ['success' => 0, 'failed' => 0, 'failures' => []],
            'warnings' => [],
        ];

        $idMap = [
            'groups' => [],
            'events' => [],
            'tickets' => [],
            'promo_codes' => [],
            'sales' => [],
            'parts' => [],
            'newsletters' => [],
            'segments' => [],
            'ab_tests' => [],
            'polls' => [],
        ];

        return DB::transaction(function () use ($scheduleData, $userId, $includesImages, $job, &$report, &$idMap) {
            // Create Role + Groups
            $role = $this->importRole($scheduleData['role'] ?? [], $userId);
            $report['schedules']['success'] = 1;
            $report['subdomain'] = $role->subdomain;

            foreach ($scheduleData['groups'] ?? [] as $groupData) {
                try {
                    $group = $this->importGroup($groupData, $role);
                    if (isset($groupData['_ref_id'])) {
                        $idMap['groups'][$groupData['_ref_id']] = $group->id;
                    }
                    $report['sub_schedules']['success']++;
                } catch (\Exception $e) {
                    report($e);
                    $report['sub_schedules']['failed']++;
                    $report['sub_schedules']['failures'][] = ($groupData['name'] ?? 'Unknown').': Import failed';
                }
            }

            // Import AB Tests first (newsletters reference them)
            foreach ($scheduleData['newsletter_ab_tests'] ?? [] as $abTestData) {
                try {
                    $abTest = $this->importAbTest($abTestData, $role);
                    if (isset($abTestData['_ref_id'])) {
                        $idMap['ab_tests'][$abTestData['_ref_id']] = $abTest->id;
                    }
                } catch (\Exception $e) {
                    report($e);
                }
            }

            // Open ZIP once for all image imports
            $zip = null;
            if ($includesImages) {
                $zipPath = $job->file_path;
                if ($zipPath && Storage::disk('local')->exists($zipPath)) {
                    $zipFullPath = Storage::disk('local')->path($zipPath);
                    $zip = new \ZipArchive;
                    if ($zip->open($zipFullPath) !== true) {
                        $zip = null;
                    }
                }
            }

            // Import events
            $events = $scheduleData['events'] ?? [];
            if (count($events) > self::MAX_EVENTS_PER_SCHEDULE) {
                $report['warnings'][] = __('messages.backup_truncated_events', ['limit' => number_format(self::MAX_EVENTS_PER_SCHEDULE), 'total' => number_format(count($events))]);
                $events = array_slice($events, 0, self::MAX_EVENTS_PER_SCHEDULE);
            }

            $salesCount = 0;
            $salesTruncated = false;
            foreach ($events as $eventData) {
                try {
                    $event = $this->importEvent($eventData, $role, $userId, $idMap);
                    if (isset($eventData['_ref_id'])) {
                        $idMap['events'][$eventData['_ref_id']] = $event->id;
                    }
                    $report['events']['success']++;

                    // Import tickets
                    $tickets = $eventData['tickets'] ?? [];
                    if (count($tickets) > self::MAX_TICKETS_PER_EVENT) {
                        $report['warnings'][] = __('messages.backup_truncated_tickets', ['limit' => self::MAX_TICKETS_PER_EVENT, 'total' => count($tickets), 'event' => $eventData['name'] ?? '']);
                        $tickets = array_slice($tickets, 0, self::MAX_TICKETS_PER_EVENT);
                    }
                    foreach ($tickets as $ticketData) {
                        try {
                            $ticket = $this->importTicket($ticketData, $event);
                            if (isset($ticketData['_ref_id'])) {
                                $idMap['tickets'][$ticketData['_ref_id']] = $ticket->id;
                            }
                            $report['tickets']['success']++;
                        } catch (\Exception $e) {
                            report($e);
                            $report['tickets']['failed']++;
                            $report['tickets']['failures'][] = ($ticketData['type'] ?? 'Unknown').': Import failed';
                        }
                    }

                    // Import promo codes
                    foreach ($eventData['promo_codes'] ?? [] as $promoData) {
                        try {
                            $promo = $this->importPromoCode($promoData, $event, $idMap);
                            if (isset($promoData['_ref_id'])) {
                                $idMap['promo_codes'][$promoData['_ref_id']] = $promo->id;
                            }
                            $report['promo_codes']['success']++;
                        } catch (\Exception $e) {
                            report($e);
                            $report['promo_codes']['failed']++;
                            $report['promo_codes']['failures'][] = ($promoData['code'] ?? 'Unknown').': Import failed';
                        }
                    }

                    // Import sales
                    $sales = $eventData['sales'] ?? [];
                    foreach ($sales as $saleData) {
                        if ($salesCount >= self::MAX_SALES_PER_SCHEDULE) {
                            $salesTruncated = true;
                            break;
                        }
                        try {
                            $sale = $this->importSale($saleData, $event, $role, $userId, $idMap);
                            if (isset($saleData['_ref_id'])) {
                                $idMap['sales'][$saleData['_ref_id']] = $sale->id;
                            }
                            $report['sales']['success']++;
                            $salesCount++;

                            // Import sale tickets
                            foreach ($saleData['sale_tickets'] ?? [] as $stData) {
                                try {
                                    $this->importSaleTicket($stData, $sale, $idMap);
                                } catch (\Exception $e) {
                                    report($e);
                                }
                            }
                        } catch (\Exception $e) {
                            report($e);
                            $report['sales']['failed']++;
                            $report['sales']['failures'][] = ($saleData['name'] ?? 'Unknown').': Import failed';
                        }
                    }

                    // Import parts
                    foreach ($eventData['parts'] ?? [] as $partData) {
                        try {
                            $part = $this->importPart($partData, $event);
                            if (isset($partData['_ref_id'])) {
                                $idMap['parts'][$partData['_ref_id']] = $part->id;
                            }
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }

                    // Import polls
                    foreach ($eventData['polls'] ?? [] as $pollData) {
                        try {
                            $poll = $this->importPoll($pollData, $event);
                            if (isset($pollData['_ref_id'])) {
                                $idMap['polls'][$pollData['_ref_id']] = $poll->id;
                            }
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }

                    // Import comments
                    foreach ($eventData['comments'] ?? [] as $commentData) {
                        try {
                            $this->importComment($commentData, $event, $idMap);
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }

                    // Import videos
                    foreach ($eventData['videos'] ?? [] as $videoData) {
                        try {
                            $this->importVideo($videoData, $event, $idMap);
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }

                    // Import feedbacks
                    foreach ($eventData['feedbacks'] ?? [] as $feedbackData) {
                        try {
                            $this->importFeedback($feedbackData, $event, $idMap);
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }

                    // Import waitlists
                    foreach ($eventData['waitlists'] ?? [] as $wlData) {
                        try {
                            $this->importWaitlist($wlData, $event, $role);
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }

                    // Import images if included
                    if ($includesImages && $zip) {
                        $this->importEventImages($eventData, $event, $zip, $idMap);
                    }

                } catch (\Exception $e) {
                    report($e);
                    $report['events']['failed']++;
                    $report['events']['failures'][] = ($eventData['name'] ?? 'Unknown').': Import failed';
                }
            }

            if ($salesTruncated) {
                $report['warnings'][] = __('messages.backup_truncated_sales', ['limit' => number_format(self::MAX_SALES_PER_SCHEDULE)]);
            }

            // Recalculate ticket sold counts from actual imported SaleTickets
            foreach ($idMap['tickets'] as $newTicketId) {
                $actualSold = SaleTicket::where('ticket_id', $newTicketId)->sum('quantity');
                Ticket::where('id', $newTicketId)->update(['sold' => $actualSold]);
            }

            // Second pass: fix Sale group_id self-references
            foreach ($events as $eventData) {
                foreach ($eventData['sales'] ?? [] as $saleData) {
                    $groupRefId = $saleData['_group_ref_id'] ?? null;
                    if ($groupRefId && isset($idMap['sales'][$saleData['_ref_id'] ?? null])) {
                        $newSaleId = $idMap['sales'][$saleData['_ref_id']];
                        $newGroupId = $idMap['groups'][$groupRefId] ?? null;
                        if ($newGroupId) {
                            Sale::where('id', $newSaleId)->update(['group_id' => $newGroupId]);
                        }
                    }
                }
            }

            // Import segments (before newsletters, so segment_ids can be remapped)
            foreach ($scheduleData['newsletter_segments'] ?? [] as $segmentData) {
                try {
                    $segment = $this->importSegment($segmentData, $role, $idMap);
                    if (isset($segmentData['_ref_id'])) {
                        $idMap['segments'][$segmentData['_ref_id']] = $segment->id;
                    }

                    foreach ($segmentData['segment_users'] ?? [] as $userData) {
                        try {
                            $this->importSegmentUser($userData, $segment);
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }
                } catch (\Exception $e) {
                    report($e);
                }
            }

            // Import newsletters
            $recipientCount = 0;
            $recipientsTruncated = false;
            foreach ($scheduleData['newsletters'] ?? [] as $nlData) {
                try {
                    $newsletter = $this->importNewsletter($nlData, $role, $userId, $idMap);
                    if (isset($nlData['_ref_id'])) {
                        $idMap['newsletters'][$nlData['_ref_id']] = $newsletter->id;
                    }
                    $report['newsletters']['success']++;

                    // Import recipients
                    foreach ($nlData['recipients'] ?? [] as $recipientData) {
                        if ($recipientCount >= self::MAX_RECIPIENTS_PER_SCHEDULE) {
                            $recipientsTruncated = true;
                            break;
                        }
                        try {
                            $this->importRecipient($recipientData, $newsletter);
                            $recipientCount++;
                        } catch (\Exception $e) {
                            report($e);
                        }
                    }
                } catch (\Exception $e) {
                    report($e);
                    $report['newsletters']['failed']++;
                    $report['newsletters']['failures'][] = ($nlData['subject'] ?? 'Unknown').': Import failed';
                }
            }

            if ($recipientsTruncated) {
                $report['warnings'][] = __('messages.backup_truncated_recipients', ['limit' => number_format(self::MAX_RECIPIENTS_PER_SCHEDULE)]);
            }

            // Second pass: fix Sale newsletter_id references
            foreach ($events as $eventData) {
                foreach ($eventData['sales'] ?? [] as $saleData) {
                    $nlRefId = $saleData['_newsletter_ref_id'] ?? null;
                    if ($nlRefId && isset($idMap['sales'][$saleData['_ref_id'] ?? null]) && isset($idMap['newsletters'][$nlRefId])) {
                        $newSaleId = $idMap['sales'][$saleData['_ref_id']];
                        Sale::where('id', $newSaleId)->update(['newsletter_id' => $idMap['newsletters'][$nlRefId]]);
                    }
                }
            }

            // Import unsubscribes
            foreach ($scheduleData['newsletter_unsubscribes'] ?? [] as $unsubData) {
                try {
                    NewsletterUnsubscribe::withoutEvents(function () use ($role, $unsubData) {
                        NewsletterUnsubscribe::create([
                            'role_id' => $role->id,
                            'email' => $unsubData['email'],
                            'unsubscribed_at' => $unsubData['unsubscribed_at'] ?? now(),
                        ]);
                    });
                } catch (\Exception $e) {
                    report($e);
                }
            }

            // Import role images if included
            if ($includesImages && $zip) {
                $this->importRoleImages($scheduleData['role'] ?? [], $role, $zip);
            }

            // Close ZIP after all image imports
            if ($zip) {
                $zip->close();
            }

            return $report;
        });
    }

    private function importRole(array $data, int $userId): Role
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'type' => 'required|in:talent,venue,curator',
            'timezone' => 'nullable|string|max:100',
            'language_code' => 'nullable|string|max:5',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid schedule data: '.$validator->errors()->first());
        }

        // Generate unique subdomain
        $baseSubdomain = $data['subdomain'] ?? Str::slug($data['name']);
        $subdomain = $baseSubdomain;
        $counter = 1;
        while (Role::where('subdomain', $subdomain)->exists()) {
            $subdomain = $baseSubdomain.'-'.$counter;
            $counter++;
        }

        $role = new Role;
        foreach (self::ROLE_EXPORT_FIELDS as $field) {
            if (array_key_exists($field, $data)) {
                $role->$field = $data[$field];
            }
        }
        $role->subdomain = $subdomain;
        $role->user_id = $userId;

        // Clear local image paths (non-http) since they reference the source system.
        // External URLs are preserved. Local images will be restored by importRoleImages if included.
        foreach (['profile_image_url', 'header_image_url', 'background_image_url'] as $imgField) {
            if ($role->$imgField && ! str_starts_with($role->$imgField, 'http')) {
                $role->$imgField = null;
            }
        }

        // Clear local sponsor logo image paths
        $sponsorLogos = $role->sponsor_logos;
        if (is_array($sponsorLogos)) {
            foreach ($sponsorLogos as $i => &$logo) {
                if (isset($logo['image']) && $logo['image'] && ! str_starts_with($logo['image'], 'http')) {
                    $logo['image'] = null;
                }
            }
            unset($logo);
            $role->sponsor_logos = $sponsorLogos;
        }

        // Regenerate HTML fields from markdown
        $role->description_html = MarkdownUtils::convertToHtml($role->description);
        $role->description_html_en = MarkdownUtils::convertToHtml($role->description_en);
        $role->short_description_html = MarkdownUtils::convertToHtml($role->short_description);
        $role->short_description_html_en = MarkdownUtils::convertToHtml($role->short_description_en);
        $role->request_terms_html = MarkdownUtils::convertToHtml($role->request_terms);
        $role->request_terms_html_en = MarkdownUtils::convertToHtml($role->request_terms_en);

        if ($role->custom_css) {
            $role->custom_css = CssUtils::sanitizeCss($role->custom_css);
        }

        // Retry on unique constraint violation (race condition with concurrent imports)
        $saved = false;
        $maxRetries = 100;
        while (! $saved) {
            try {
                $role->saveQuietly();
                $saved = true;
            } catch (QueryException $e) {
                if ($e->errorInfo[1] === 1062) {
                    if ($counter >= $maxRetries) {
                        throw new \RuntimeException('Failed to generate unique subdomain after '.$maxRetries.' attempts');
                    }
                    $role->subdomain = $baseSubdomain.'-'.$counter;
                    $counter++;
                } else {
                    throw $e;
                }
            }
        }

        // Attach user as owner
        $role->users()->attach($userId, ['level' => 'owner']);

        return $role;
    }

    private function importGroup(array $data, Role $role): Group
    {
        $group = new Group;
        $group->role_id = $role->id;
        $group->name = $data['name'] ?? '';
        $group->name_en = $data['name_en'] ?? null;
        $group->slug = $data['slug'] ?? Str::slug($data['name'] ?? '');
        $group->color = $data['color'] ?? null;
        $group->saveQuietly();

        return $group;
    }

    private function importEvent(array $data, Role $role, int $userId, array &$idMap): Event
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:1000',
            'starts_at' => 'required|date',
            'duration' => 'nullable|numeric|min:0|max:10080',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid event data: '.$validator->errors()->first());
        }

        $event = new Event;

        $excludeFields = array_merge(self::EVENT_EXPORT_EXCLUDE, [
            '_ref_id', '_group_ref_id', '_is_accepted', '_flyer_image',
            'tickets', 'promo_codes', 'sales', 'parts', 'polls',
            'comments', 'videos', 'feedbacks', 'waitlists', 'photos',
            'days_of_week', 'recurring_include_dates', 'recurring_exclude_dates',
        ]);

        $fillable = $event->getFillable();
        foreach ($data as $field => $value) {
            if (str_starts_with($field, '_')) {
                continue;
            }
            if (in_array($field, $excludeFields)) {
                continue;
            }
            if (in_array($field, $fillable)) {
                $event->$field = $value;
            }
        }

        $event->creator_role_id = $role->id;
        $event->user_id = $userId;

        // Handle non-fillable fields
        $event->days_of_week = $data['days_of_week'] ?? null;
        if (! empty($data['recurring_include_dates'])) {
            $event->recurring_include_dates = $data['recurring_include_dates'];
        }
        if (! empty($data['recurring_exclude_dates'])) {
            $event->recurring_exclude_dates = $data['recurring_exclude_dates'];
        }

        // Regenerate HTML fields
        $event->description_html = MarkdownUtils::convertToHtml($event->description);
        $event->description_html_en = MarkdownUtils::convertToHtml($event->description_en);
        $event->short_description_html = MarkdownUtils::convertToHtml($event->short_description);
        $event->short_description_html_en = MarkdownUtils::convertToHtml($event->short_description_en);
        $event->ticket_notes_html = MarkdownUtils::convertToHtml($event->ticket_notes);
        $event->payment_instructions_html = MarkdownUtils::convertToHtml($event->payment_instructions);

        // Clear local flyer image path
        if ($event->flyer_image_url && ! str_starts_with($event->flyer_image_url, 'http')) {
            $event->flyer_image_url = null;
        }

        $event->saveQuietly();

        // Attach to role via pivot
        $pivotData = ['is_accepted' => $data['_is_accepted'] ?? true];
        $groupRefId = $data['_group_ref_id'] ?? null;
        if ($groupRefId && isset($idMap['groups'][$groupRefId])) {
            $pivotData['group_id'] = $idMap['groups'][$groupRefId];
        }
        $event->roles()->attach($role->id, $pivotData);

        return $event;
    }

    private function importTicket(array $data, Event $event): Ticket
    {
        $validator = Validator::make($data, [
            'type' => 'required|string|max:255',
            'quantity' => 'nullable|integer|min:0',
            'price' => 'required|numeric|min:0',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid ticket data: '.$validator->errors()->first());
        }

        $ticket = new Ticket;
        $ticket->event_id = $event->id;
        $ticket->type = $data['type'];
        $ticket->quantity = $data['quantity'] ?? null;
        $ticket->sold = $data['sold'] ?? null;
        $ticket->price = $data['price'];
        $ticket->description = $data['description'] ?? null;
        $ticket->sales_end_at = $data['sales_end_at'] ?? null;
        $ticket->custom_fields = $data['custom_fields'] ?? null;
        $ticket->description_html = MarkdownUtils::convertToHtml($data['description'] ?? null);
        $ticket->saveQuietly();

        return $ticket;
    }

    private function importPromoCode(array $data, Event $event, array &$idMap): PromoCode
    {
        $validator = Validator::make($data, [
            'code' => 'required|string|max:50',
            'type' => 'required|in:percentage,fixed',
            'value' => 'required|numeric|min:0.01',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid promo code data: '.$validator->errors()->first());
        }

        // Remap ticket_ids
        $ticketIds = $data['_ticket_ref_ids'] ?? [];
        $newTicketIds = [];
        if (is_array($ticketIds)) {
            foreach ($ticketIds as $refId) {
                if (isset($idMap['tickets'][$refId])) {
                    $newTicketIds[] = $idMap['tickets'][$refId];
                }
            }
        }

        $promo = new PromoCode;
        $promo->event_id = $event->id;
        $promo->code = $data['code'];
        $promo->type = $data['type'];
        $promo->value = $data['value'];
        $promo->max_uses = $data['max_uses'] ?? null;
        $promo->times_used = $data['times_used'] ?? 0;
        $promo->expires_at = $data['expires_at'] ?? null;
        $promo->is_active = $data['is_active'] ?? true;
        $promo->ticket_ids = ! empty($newTicketIds) ? $newTicketIds : null;
        $promo->saveQuietly();

        return $promo;
    }

    private function importSale(array $data, Event $event, Role $role, int $userId, array &$idMap): Sale
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'status' => 'required|in:unpaid,paid,cancelled,refunded,expired',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid sale data: '.$validator->errors()->first());
        }

        $sale = new Sale;
        $sale->event_id = $event->id;
        $sale->user_id = null;
        $sale->name = $data['name'];
        $sale->email = $data['email'];
        $sale->phone = $data['phone'] ?? null;
        $sale->secret = Str::random(32);
        $sale->event_date = $data['event_date'] ?? null;
        $sale->subdomain = $role->subdomain;
        $sale->status = $data['status'];
        $sale->payment_method = $data['payment_method'] ?? null;
        $sale->payment_amount = $data['payment_amount'] ?? null;
        $sale->transaction_reference = $data['transaction_reference'] ?? null;
        $sale->discount_amount = $data['discount_amount'] ?? null;
        $sale->utm_source = $data['utm_source'] ?? null;
        $sale->utm_medium = $data['utm_medium'] ?? null;
        $sale->utm_campaign = $data['utm_campaign'] ?? null;
        $sale->feedback_sent_at = $data['feedback_sent_at'] ?? null;

        // Remap promo_code_id
        $promoRefId = $data['_promo_code_ref_id'] ?? null;
        if ($promoRefId && isset($idMap['promo_codes'][$promoRefId])) {
            $sale->promo_code_id = $idMap['promo_codes'][$promoRefId];
        }

        // newsletter_id is remapped in the second pass after newsletters are imported

        for ($i = 1; $i <= 10; $i++) {
            $field = "custom_value{$i}";
            $sale->$field = $data[$field] ?? null;
        }

        // group_id set to null initially, fixed in second pass
        $sale->group_id = null;

        $sale->saveQuietly();

        // Preserve original created_at
        if (! empty($data['created_at'])) {
            Sale::where('id', $sale->id)->update(['created_at' => $data['created_at']]);
        }

        return $sale;
    }

    private function importSaleTicket(array $data, Sale $sale, array &$idMap): void
    {
        $ticketRefId = $data['_ticket_ref_id'] ?? null;
        $ticketId = $ticketRefId ? ($idMap['tickets'][$ticketRefId] ?? null) : null;

        if (! $ticketId) {
            return;
        }

        SaleTicket::withoutEvents(function () use ($data, $sale, $ticketId) {
            $st = new SaleTicket;
            $st->sale_id = $sale->id;
            $st->ticket_id = $ticketId;
            $st->seats = $data['seats'] ?? null;
            $st->quantity = $data['quantity'] ?? 1;

            for ($i = 1; $i <= 10; $i++) {
                $field = "custom_value{$i}";
                $st->$field = $data[$field] ?? null;
            }

            $st->save();
        });
    }

    private function importPart(array $data, Event $event): EventPart
    {
        $part = new EventPart;
        $part->event_id = $event->id;
        $part->name = $data['name'] ?? '';
        $part->name_en = $data['name_en'] ?? null;
        $part->description = $data['description'] ?? null;
        $part->description_en = $data['description_en'] ?? null;
        $part->description_html = MarkdownUtils::convertToHtml($data['description'] ?? null);
        $part->description_html_en = MarkdownUtils::convertToHtml($data['description_en'] ?? null);
        $part->start_time = $data['start_time'] ?? null;
        $part->end_time = $data['end_time'] ?? null;
        $part->sort_order = $data['sort_order'] ?? 0;
        $part->saveQuietly();

        return $part;
    }

    private function importPoll(array $data, Event $event): EventPoll
    {
        $poll = new EventPoll;
        $poll->event_id = $event->id;
        $poll->question = $data['question'] ?? '';
        $poll->options = $data['options'] ?? [];
        $poll->is_active = $data['is_active'] ?? true;
        $poll->sort_order = $data['sort_order'] ?? 0;
        $poll->allow_user_options = $data['allow_user_options'] ?? false;
        $poll->require_option_approval = $data['require_option_approval'] ?? false;
        $poll->pending_options = $data['pending_options'] ?? null;
        $poll->saveQuietly();

        // Import votes (anonymous, no user_id)
        foreach ($data['votes'] ?? [] as $voteData) {
            try {
                EventPollVote::withoutEvents(function () use ($poll, $voteData) {
                    EventPollVote::create([
                        'event_poll_id' => $poll->id,
                        'user_id' => null,
                        'option_index' => $voteData['option_index'],
                        'event_date' => $voteData['event_date'] ?? null,
                    ]);
                });
            } catch (\Exception $e) {
                report($e);
            }
        }

        return $poll;
    }

    private function importComment(array $data, Event $event, array &$idMap): void
    {
        $partRefId = $data['_event_part_ref_id'] ?? null;
        $partId = $partRefId ? ($idMap['parts'][$partRefId] ?? null) : null;

        EventComment::withoutEvents(function () use ($event, $partId, $data) {
            EventComment::create([
                'event_id' => $event->id,
                'event_part_id' => $partId,
                'event_date' => $data['event_date'] ?? null,
                'user_id' => null,
                'comment' => $data['comment'] ?? '',
                'is_approved' => $data['is_approved'] ?? true,
            ]);
        });
    }

    private function importVideo(array $data, Event $event, array &$idMap): void
    {
        $partRefId = $data['_event_part_ref_id'] ?? null;
        $partId = $partRefId ? ($idMap['parts'][$partRefId] ?? null) : null;

        EventVideo::withoutEvents(function () use ($event, $partId, $data) {
            EventVideo::create([
                'event_id' => $event->id,
                'event_part_id' => $partId,
                'event_date' => $data['event_date'] ?? null,
                'user_id' => null,
                'youtube_url' => $data['youtube_url'] ?? '',
                'is_approved' => $data['is_approved'] ?? true,
            ]);
        });
    }

    private function importFeedback(array $data, Event $event, array &$idMap): void
    {
        $saleRefId = $data['_sale_ref_id'] ?? null;
        $saleId = $saleRefId ? ($idMap['sales'][$saleRefId] ?? null) : null;

        if (! $saleId) {
            return;
        }

        EventFeedback::withoutEvents(function () use ($event, $saleId, $data) {
            EventFeedback::create([
                'event_id' => $event->id,
                'sale_id' => $saleId,
                'event_date' => $data['event_date'] ?? null,
                'rating' => $data['rating'] ?? null,
                'comment' => $data['comment'] ?? null,
            ]);
        });
    }

    private function importWaitlist(array $data, Event $event, Role $role): void
    {
        TicketWaitlist::withoutEvents(function () use ($event, $data, $role) {
            TicketWaitlist::create([
                'event_id' => $event->id,
                'event_date' => $data['event_date'] ?? null,
                'name' => $data['name'] ?? '',
                'email' => $data['email'] ?? '',
                'subdomain' => $role->subdomain,
                'status' => $data['status'] ?? 'waiting',
                'locale' => $data['locale'] ?? null,
                'notified_at' => $data['notified_at'] ?? null,
                'expires_at' => $data['expires_at'] ?? null,
            ]);
        });
    }

    private function importNewsletter(array $data, Role $role, int $userId, array &$idMap): Newsletter
    {
        $validator = Validator::make($data, [
            'subject' => 'required|string|max:500',
            'status' => 'required|in:draft,scheduled,sent',
        ]);

        if ($validator->fails()) {
            throw new \InvalidArgumentException('Invalid newsletter data: '.$validator->errors()->first());
        }

        // Remap ab_test_id
        $abTestRefId = $data['_ab_test_ref_id'] ?? null;
        $abTestId = $abTestRefId ? ($idMap['ab_tests'][$abTestRefId] ?? null) : null;

        // Remap segment_ids
        $segmentRefIds = $data['_segment_ref_ids'] ?? [];
        $newSegmentIds = [];
        if (is_array($segmentRefIds)) {
            foreach ($segmentRefIds as $refId) {
                if (isset($idMap['segments'][$refId])) {
                    $newSegmentIds[] = $idMap['segments'][$refId];
                }
            }
        }

        $newsletter = new Newsletter;
        $newsletter->role_id = $role->id;
        $newsletter->user_id = $userId;
        $newsletter->subject = $data['subject'];
        $newsletter->blocks = $data['blocks'] ?? null;
        $newsletter->style_settings = $data['style_settings'] ?? null;
        $newsletter->template = $data['template'] ?? 'default';
        // Remap event_ids
        $eventIds = $data['event_ids'] ?? null;
        if (is_array($eventIds)) {
            $newEventIds = [];
            foreach ($eventIds as $oldEventId) {
                if (isset($idMap['events'][$oldEventId])) {
                    $newEventIds[] = $idMap['events'][$oldEventId];
                }
            }
            $eventIds = ! empty($newEventIds) ? $newEventIds : null;
        }
        $newsletter->event_ids = $eventIds;
        $newsletter->segment_ids = ! empty($newSegmentIds) ? $newSegmentIds : null;
        $newsletter->status = $data['status'] === 'sent' ? 'sent' : 'draft';
        $newsletter->scheduled_at = null;
        $newsletter->sent_at = $data['sent_at'] ?? null;
        $newsletter->ab_test_id = $abTestId;
        $newsletter->ab_variant = $data['ab_variant'] ?? null;
        $newsletter->send_token = Str::random(32);
        $newsletter->sent_count = $data['sent_count'] ?? 0;
        $newsletter->open_count = $data['open_count'] ?? 0;
        $newsletter->click_count = $data['click_count'] ?? 0;
        $newsletter->type = $data['type'] ?? null;
        $newsletter->saveQuietly();

        return $newsletter;
    }

    private function importRecipient(array $data, Newsletter $newsletter): void
    {
        NewsletterRecipient::withoutEvents(function () use ($data, $newsletter) {
            NewsletterRecipient::create([
                'newsletter_id' => $newsletter->id,
                'user_id' => null,
                'email' => $data['email'] ?? '',
                'name' => $data['name'] ?? null,
                'token' => Str::random(32),
                'status' => $data['status'] ?? 'sent',
                'sent_at' => $data['sent_at'] ?? null,
                'error_message' => $data['error_message'] ?? null,
                'opened_at' => $data['opened_at'] ?? null,
                'open_count' => $data['open_count'] ?? 0,
                'clicked_at' => $data['clicked_at'] ?? null,
                'click_count' => $data['click_count'] ?? 0,
            ]);
        });
    }

    private function importAbTest(array $data, Role $role): NewsletterAbTest
    {
        $abTest = new NewsletterAbTest;
        $abTest->role_id = $role->id;
        $abTest->name = $data['name'] ?? '';
        $abTest->test_field = $data['test_field'] ?? null;
        $abTest->sample_percentage = $data['sample_percentage'] ?? null;
        $abTest->winner_criteria = $data['winner_criteria'] ?? null;
        $abTest->winner_wait_hours = $data['winner_wait_hours'] ?? null;
        $abTest->winner_selected_at = $data['winner_selected_at'] ?? null;
        $abTest->winner_variant = $data['winner_variant'] ?? null;
        $abTest->status = $data['status'] ?? 'completed';
        $abTest->saveQuietly();

        return $abTest;
    }

    private function importSegment(array $data, Role $role, array $idMap): NewsletterSegment
    {
        $segment = new NewsletterSegment;
        $segment->role_id = $role->id;
        $segment->name = $data['name'] ?? '';
        $segment->type = $data['type'] ?? 'manual';

        $criteria = $data['filter_criteria'] ?? null;
        if (is_array($criteria)) {
            if (! empty($criteria['event_id'])) {
                if (isset($idMap['events'][$criteria['event_id']])) {
                    $criteria['event_id'] = $idMap['events'][$criteria['event_id']];
                } else {
                    $criteria['event_id'] = null;
                }
            }
            if (! empty($criteria['group_id'])) {
                if (isset($idMap['groups'][$criteria['group_id']])) {
                    $criteria['group_id'] = $idMap['groups'][$criteria['group_id']];
                } else {
                    $criteria['group_id'] = null;
                }
            }
        }
        $segment->filter_criteria = $criteria;
        $segment->saveQuietly();

        return $segment;
    }

    private function importSegmentUser(array $data, NewsletterSegment $segment): void
    {
        NewsletterSegmentUser::withoutEvents(function () use ($data, $segment) {
            NewsletterSegmentUser::create([
                'newsletter_segment_id' => $segment->id,
                'user_id' => null,
                'email' => $data['email'] ?? '',
                'name' => $data['name'] ?? null,
            ]);
        });
    }

    private function importRoleImages(array $roleData, Role $role, \ZipArchive $zip): void
    {
        foreach (['profile_image_url', 'header_image_url', 'background_image_url'] as $field) {
            $imageKey = $roleData['_'.$field] ?? null;
            if (! $imageKey) {
                continue;
            }

            $imageData = $zip->getFromName($imageKey);
            if ($imageData === false || ! $this->isValidImageData($imageData)) {
                continue;
            }

            $extension = $this->safeImageExtension($imageKey);
            $filename = strtolower(Str::random(32).'.'.$extension);

            if (config('filesystems.default') == 'local') {
                Storage::put('public/'.$filename, $imageData);
            } else {
                Storage::put($filename, $imageData);
            }

            $role->$field = $filename;
        }

        // Sponsor logos
        $sponsorLogos = $role->sponsor_logos;
        if (is_array($sponsorLogos)) {
            $updated = false;
            foreach ($sponsorLogos as $i => &$logo) {
                $imageKey = $roleData['_sponsor_logo_'.$i] ?? null;
                if (! $imageKey) {
                    continue;
                }

                $imageData = $zip->getFromName($imageKey);
                if ($imageData === false || ! $this->isValidImageData($imageData)) {
                    continue;
                }

                $extension = $this->safeImageExtension($imageKey);
                $filename = strtolower(Str::random(32).'.'.$extension);

                if (config('filesystems.default') == 'local') {
                    Storage::put('public/'.$filename, $imageData);
                } else {
                    Storage::put($filename, $imageData);
                }

                $logo['image'] = $filename;
                $updated = true;
            }
            if ($updated) {
                $role->sponsor_logos = $sponsorLogos;
            }
        }

        $role->saveQuietly();
    }

    private function importEventImages(array $eventData, Event $event, \ZipArchive $zip, array &$idMap): void
    {
        // Flyer image
        $flyerKey = $eventData['_flyer_image'] ?? null;
        if ($flyerKey) {
            $imageData = $zip->getFromName($flyerKey);
            if ($imageData !== false && $this->isValidImageData($imageData)) {
                $extension = $this->safeImageExtension($flyerKey);
                $filename = strtolower(Str::random(32).'.'.$extension);

                if (config('filesystems.default') == 'local') {
                    Storage::put('public/'.$filename, $imageData);
                } else {
                    Storage::put($filename, $imageData);
                }

                $event->flyer_image_url = $filename;
                $event->saveQuietly();
            }
        }

        // Event photos
        foreach ($eventData['photos'] ?? [] as $photoData) {
            $photoKey = $photoData['_photo_image'] ?? null;
            if (! $photoKey) {
                continue;
            }

            $imageData = $zip->getFromName($photoKey);
            if ($imageData === false || ! $this->isValidImageData($imageData)) {
                continue;
            }

            $extension = $this->safeImageExtension($photoKey);
            $filename = strtolower(Str::random(32).'.'.$extension);

            if (config('filesystems.default') == 'local') {
                Storage::put('public/'.$filename, $imageData);
            } else {
                Storage::put($filename, $imageData);
            }

            $partRefId = $photoData['_event_part_ref_id'] ?? null;
            $partId = $partRefId ? ($idMap['parts'][$partRefId] ?? null) : null;

            EventPhoto::withoutEvents(function () use ($event, $partId, $photoData, $filename) {
                EventPhoto::create([
                    'event_id' => $event->id,
                    'event_part_id' => $partId,
                    'event_date' => $photoData['event_date'] ?? null,
                    'user_id' => null,
                    'photo_url' => $filename,
                    'is_approved' => $photoData['is_approved'] ?? true,
                ]);
            });
        }
    }

    private function isValidImageData(string $data): bool
    {
        return @getimagesizefromstring($data) !== false;
    }

    private function safeImageExtension(string $path): string
    {
        $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION) ?: 'jpg');

        return in_array($extension, $allowed) ? $extension : 'jpg';
    }
}
