<?php

namespace App\Services;

use App\Jobs\SendNewsletterBatch;
use App\Mail\NewsletterEmail;
use App\Models\Event;
use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterUnsubscribe;
use App\Models\Role;
use App\Repos\EventRepo;
use App\Utils\MarkdownUtils;
use App\Utils\MoneyUtils;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterService
{
    public function send(Newsletter $newsletter): bool|array
    {
        $role = $newsletter->role;
        if (! $newsletter->isAdmin() && (! $role || ! $role->canSendNewsletter())) {
            Log::warning('Newsletter send blocked: role cannot send', [
                'newsletter_id' => $newsletter->id,
                'role_id' => $newsletter->role_id,
                'has_role' => ! is_null($role),
            ]);

            return false;
        }

        $sendToken = Str::random(64);
        $updated = Newsletter::where('id', $newsletter->id)
            ->whereIn('status', ['draft', 'scheduled'])
            ->update(['status' => 'sending', 'send_token' => $sendToken]);

        if ($updated === 0) {
            Log::warning('Newsletter send skipped: status already changed', [
                'newsletter_id' => $newsletter->id,
            ]);

            return false;
        }

        $newsletter->refresh();

        $segmentIds = $newsletter->segment_ids ?? [];
        $recipients = $newsletter->isAdmin()
            ? $this->resolveAdminRecipients($segmentIds)
            : $this->resolveRecipients($newsletter->role, $segmentIds);

        if ($recipients->isEmpty()) {
            $this->releaseToDraft($newsletter);

            return ['no_recipients', 0];
        }

        // Trust gate. Moved below recipient resolution so it can scale to the size of the send:
        // Role::canSendAudienceMail() lets a small audience through without SMTP or an SMS-verified
        // phone, and asks for verification above it. Resetting the status matters as much as the
        // refusal - send() has already claimed the row as 'sending' by this point.
        if (! $newsletter->isAdmin() && $role
            && ! $role->canSendAudienceMail($recipients->count(), $newsletter->user)) {
            Log::warning('Newsletter send blocked: requires SMTP or phone verification', [
                'newsletter_id' => $newsletter->id,
                'role_id' => $newsletter->role_id,
                'recipients' => $recipients->count(),
            ]);

            $this->releaseToDraft($newsletter);

            return ['requires_verification', $recipients->count()];
        }

        // Check if sending to these recipients would exceed the email limit
        if (! $newsletter->isAdmin() && $role) {
            $limit = $role->newsletterLimit();
            if ($limit !== null) {
                $used = $role->newslettersSentThisMonth();
                if ($used + $recipients->count() > $limit) {
                    $this->releaseToDraft($newsletter);

                    return ['limit_exceeded', $recipients->count()];
                }
            }
        }

        DB::beginTransaction();
        try {
            $chunk = [];
            foreach ($recipients as $recipient) {
                $chunk[] = [
                    'newsletter_id' => $newsletter->id,
                    'user_id' => $recipient->user_id,
                    'email' => $recipient->email,
                    'name' => $recipient->name,
                    'token' => Str::random(64),
                    'status' => 'pending',
                ];
                if (count($chunk) >= 500) {
                    NewsletterRecipient::insert($chunk);
                    $chunk = [];
                }
            }
            if (! empty($chunk)) {
                NewsletterRecipient::insert($chunk);
            }
            DB::commit();
        } catch (\Throwable $e) {
            // \Throwable, not \Exception. This is a RAW beginTransaction, so an \Error escaping
            // here leaves the transaction open - and both cron rails carry on afterwards
            // (ScheduleRunCommand catches Throwable per event; translateData does too), so every
            // later write that minute lands in a doomed transaction and is lost at teardown, with
            // the cache-backed tier keys surviving to mark the work done. Rolling back is correct
            // for an \Error as well; the connection is usable again either way.
            DB::rollBack();
            $newsletter->update([
                'status' => $newsletter->scheduled_at ? 'scheduled' : 'draft',
                'send_token' => null,
            ]);
            report($e);

            return false;
        }

        $batchIndex = 0;
        NewsletterRecipient::where('newsletter_id', $newsletter->id)
            ->where('status', 'pending')
            ->chunkById(50, function ($recipientChunk) use ($newsletter, &$batchIndex) {
                SendNewsletterBatch::dispatch($newsletter->id, $recipientChunk->pluck('id')->toArray())
                    ->delay(now()->addSeconds($batchIndex * 15));
                $batchIndex++;
            });

        return true;
    }

    /**
     * Hand a claimed newsletter back, terminally.
     *
     * Every refusal inside send() runs after the row has already been claimed as 'sending', so
     * each one has to release it - and all three must release it the SAME way. Returning it to
     * 'scheduled' with a scheduled_at already in the past puts it straight back in
     * ProcessScheduledNewsletters' queue (which selects status = 'scheduled' AND
     * scheduled_at <= now()), so it is re-picked every minute on both cron rails: the whole
     * recipient set re-resolved and the same refusal logged, forever, while the composer goes on
     * showing it as scheduled and the owner is told nothing.
     *
     * Draft is terminal here, because the cron only reads 'scheduled' rows, and it puts the
     * newsletter somewhere the owner will actually look - where opening it hits the same gate
     * that refused it and shows them why.
     */
    private function releaseToDraft(Newsletter $newsletter): void
    {
        $newsletter->update([
            'status' => 'draft',
            'scheduled_at' => null,
            'send_token' => null,
        ]);
    }

    public function sendToRecipient(Newsletter $newsletter, NewsletterRecipient $recipient, bool $isTest = false, ?array $processedBlocks = null): bool
    {
        if (! $isTest && $this->isTestEmail($recipient->email)) {
            $recipient->update(['status' => 'skipped']);

            return false;
        }

        try {
            if ($processedBlocks === null) {
                $processedBlocks = $this->processBlocks($newsletter);
            }
            $html = $this->renderHtml($newsletter, $recipient, $processedBlocks);
            $html = $this->rewriteLinks($html, $recipient);
            $html = $this->insertTrackingPixel($html, $recipient);

            $mailable = new NewsletterEmail($newsletter, $recipient, $html, $processedBlocks);

            $role = $newsletter->role;
            // The HTML above was rendered in the schedule's language; the text part is rendered by
            // the mailer, later, in whatever locale the request or the worker has.
            if ($role && is_valid_language_code($role->language_code)) {
                $mailable->locale($role->language_code);
            }
            if (config('app.hosted') && $role) {
                if (! app(RoleMailerService::class)->sendForRole($role, $recipient->email, $mailable)) {
                    // The schedule's custom SMTP is failing; the message was not
                    // sent and we do not fall back to the platform mailer. Mark
                    // the recipient as failed rather than sent.
                    $recipient->update([
                        'status' => 'failed',
                        'error_message' => substr(
                            $role->email_settings_failed_message
                                ?: __('messages.email_settings_failed_warning_title'),
                            0,
                            500
                        ),
                    ]);

                    return false;
                }
            } else {
                Mail::to($recipient->email)->send($mailable);
            }

            $recipient->update(['status' => 'sent', 'sent_at' => now()]);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send newsletter email: '.$e->getMessage(), [
                'newsletter_id' => $newsletter->id,
                'recipient_id' => $recipient->id,
                'error' => $e->getMessage(),
            ]);

            $recipient->update([
                'status' => 'failed',
                'error_message' => substr($e->getMessage(), 0, 500),
            ]);

            return false;
        }
    }

    /**
     * Per-instance memo. NewsletterController::send() now resolves recipients to size the trust
     * gate, and send() resolves them again to build the recipient rows - and each pass plucks
     * every unsubscribed address on the platform. Recipients cannot change mid-request, so one
     * resolution per (role, segments) is enough.
     */
    private array $resolvedRecipients = [];

    public function resolveRecipients(Role $role, array $segmentIds): Collection
    {
        $ids = $segmentIds;
        sort($ids);
        $memoKey = $role->id.':'.implode(',', $ids);

        if (isset($this->resolvedRecipients[$memoKey])) {
            return $this->resolvedRecipients[$memoKey];
        }

        return $this->resolvedRecipients[$memoKey] = $this->resolveRecipientsUncached($role, $segmentIds);
    }

    private function resolveRecipientsUncached(Role $role, array $segmentIds): Collection
    {
        if (empty($segmentIds)) {
            // Both audience types, not just account followers. This is the "everyone" default, and
            // an account-less subscriber is as much a follower of this schedule as a user row is.
            $segments = NewsletterSegment::where('role_id', $role->id)
                ->whereIn('type', ['all_followers', 'all_subscribers'])
                ->get();
        } else {
            $segments = NewsletterSegment::where('role_id', $role->id)
                ->whereIn('id', $segmentIds)
                ->get();
        }

        $allRecipients = collect();
        foreach ($segments as $segment) {
            $allRecipients = $allRecipients->merge($segment->resolveRecipients());
        }

        // Default audience: no segment was asked for and none is saved, so this send means
        // "everyone", which is what messages.default_all_followers promises in the composer.
        //
        // ADDITIVE, and unconditional whenever no segment was asked for. Two earlier shapes were
        // both wrong:
        //   - checking $allRecipients->isEmpty() AFTER the member merge below. members() always
        //     includes the owner, so the check could never be true and the branch was dead code -
        //     a schedule with no saved segment mailed only itself.
        //   - checking $segments->isEmpty(). An owner who had ever saved an all_followers segment
        //     (an ordinary thing to do) then reached account followers only, and the account-less
        //     audience silently got nothing while the composer promised "all followers".
        // unique('email') below collapses anyone a segment already contributed.
        if (empty($segmentIds)) {
            $allRecipients = $allRecipients->merge($role->followers()
                ->select('users.id', 'users.email', 'users.name', 'users.is_subscribed')
                ->where('users.is_subscribed', true)
                ->get()
                ->map(fn ($user) => (object) [
                    'user_id' => $user->id,
                    'email' => strtolower($user->email),
                    'name' => $user->name,
                ])
                // toBase(): merge() on an Eloquent collection keys by getKey(), which these plain
                // objects do not have.
                ->toBase()
            )->merge(
                \App\Models\RoleSubscriber::where('role_id', $role->id)
                    ->confirmed()
                    ->get(['email', 'name'])
                    ->map(fn ($subscriber) => (object) [
                        'user_id' => null,
                        'email' => strtolower($subscriber->email),
                        'name' => $subscriber->name,
                    ])
                    ->toBase()
            );
        }

        // Always include schedule members (owner, admin, viewer)
        $members = $role->members()
            ->select('users.id', 'users.email', 'users.name', 'users.is_subscribed')
            ->where('users.is_subscribed', true)
            ->whereNotNull('users.email_verified_at')
            ->get()
            ->map(fn ($user) => (object) [
                'user_id' => $user->id,
                'email' => strtolower($user->email),
                'name' => $user->name,
            ]);
        $allRecipients = $allRecipients->merge($members);

        // Deduplicate by lowercase email
        $allRecipients = $allRecipients->unique('email');

        // Exclude unsubscribes
        $unsubscribedEmails = NewsletterUnsubscribe::where('role_id', $role->id)
            ->pluck('email')
            ->map(fn ($email) => strtolower($email))
            ->toArray();

        // Exclude users with is_subscribed = false
        $unsubscribedUserEmails = \App\Models\User::where('is_subscribed', false)
            ->pluck('email')
            ->map(fn ($email) => strtolower($email))
            ->toArray();

        $excludeEmails = array_flip(array_merge($unsubscribedEmails, $unsubscribedUserEmails));

        $allRecipients = $allRecipients->filter(function ($recipient) use ($excludeEmails) {
            return ! isset($excludeEmails[$recipient->email])
                && ! $this->isTestEmail($recipient->email);
        });

        return $allRecipients->values();
    }

    public function resolveAdminRecipients(array $segmentIds): Collection
    {
        if (empty($segmentIds)) {
            // Default to all subscribed, verified users
            $allRecipients = \App\Models\User::whereNotNull('email_verified_at')
                ->where('is_subscribed', true)
                ->whereNull('admin_newsletter_unsubscribed_at')
                ->select('id', 'email', 'name')
                ->get()
                ->map(fn ($user) => (object) [
                    'user_id' => $user->id,
                    'email' => strtolower($user->email),
                    'name' => $user->name,
                ]);
        } else {
            $segments = NewsletterSegment::whereNull('role_id')
                ->whereIn('id', $segmentIds)
                ->get();

            $allRecipients = collect();
            foreach ($segments as $segment) {
                $allRecipients = $allRecipients->merge($segment->resolveRecipients());
            }

            // Safety net: filter unsubscribed users regardless of segment implementation
            $unsubscribedEmails = array_flip(
                \App\Models\User::whereNotNull('admin_newsletter_unsubscribed_at')
                    ->orWhere('is_subscribed', false)
                    ->pluck('email')
                    ->map(fn ($e) => strtolower($e))
                    ->all()
            );
            $allRecipients = $allRecipients->reject(fn ($r) => isset($unsubscribedEmails[$r->email]));
        }

        // Deduplicate by lowercase email
        $allRecipients = $allRecipients->unique('email');

        $allRecipients = $allRecipients->filter(function ($recipient) {
            return ! $this->isTestEmail($recipient->email);
        });

        return $allRecipients->values();
    }

    /**
     * A newsletter's blocks with everything the views print already resolved: a Text block's HTML,
     * each event as a plain row (eventRow()), the sponsors, the poll, the video's own link.
     *
     * Called once per send (SendNewsletterBatch hands the result to every recipient's render), so
     * anything a view would otherwise ask each event for belongs here. Resolved in the SCHEDULE's
     * language: the job runs in the dispatcher's locale, and a row carries month and weekday
     * names, "Free" and "Sold Out".
     */
    public function processBlocks(Newsletter $newsletter): array
    {
        return $this->inScheduleLocale($newsletter->role, fn () => $this->resolveBlocks($newsletter));
    }

    private function resolveBlocks(Newsletter $newsletter): array
    {
        $blocks = $newsletter->blocks ?? [];
        $role = $newsletter->role;
        $upcoming = null;

        foreach ($blocks as &$block) {
            $type = $block['type'] ?? '';

            // Only ever the HTML the markdown renders to: the view prints contentHtml unescaped,
            // and a stored block can carry one of its own, since the builder's JSON is saved as
            // posted (SanitizesNewsletterContent::parseBlocks() leaves the key alone) and a backup
            // restores it as it was. With no content, one used to be printed as it was.
            if ($type === 'text') {
                $block['data']['contentHtml'] = ! empty($block['data']['content'])
                    ? MarkdownUtils::convertToHtml($block['data']['content'])
                    : '';
            }

            if ($type === 'events') {
                $pairs = collect();
                if ($role) {
                    $pairs = ($block['data']['useAllEvents'] ?? true)
                        ? ($upcoming ??= $this->upcomingEvents($role))
                        : $this->pickedEvents($role, $block['data']['eventIds'] ?? []);
                }

                $block['data']['resolvedEvents'] = $pairs->map(fn (array $pair) => $this->eventRow($pair['event'], $pair['date'], $role))->values();
                $block['data']['scheduleUrl'] = $role?->getGuestUrl(true);
            }

            // Everything the block prints comes from the video id read out of its YouTube link:
            // the link it opens, which is rebuilt, and the thumbnail. The id is matched anywhere in
            // the stored url, so "javascript:...//youtu.be/<id>" used to be linked as it was, and a
            // videoId or thumbnailUrl stored with the block was printed beside whatever url it held.
            if ($type === 'video') {
                $videoUrl = $block['data']['url'] ?? '';
                unset($block['data']['videoId'], $block['data']['thumbnailUrl']);
                $block['data']['url'] = '';
                if (is_string($videoUrl) && preg_match('/(?:youtube\.com\/watch\?.*v=|youtu\.be\/|youtube\.com\/shorts\/)([a-zA-Z0-9_-]{11})/', $videoUrl, $m)) {
                    $block['data']['videoId'] = $m[1];
                    $block['data']['url'] = 'https://www.youtube.com/watch?v='.$m[1];
                    $block['data']['thumbnailUrl'] = route('youtube.thumbnail', ['id' => $m[1], 'q' => 'hq']);
                }
            }

            if ($type === 'sponsors') {
                $source = $block['data']['source'] ?? 'schedule';
                if ($source === 'first_event' && $role) {
                    $firstEvent = ($upcoming ??= $this->upcomingEvents($role))->first()['event'] ?? null;
                    $block['data']['resolvedSponsors'] = $firstEvent
                        ? $firstEvent->getEffectiveSponsorLogos($role)
                        : [];
                } elseif ($role) {
                    $block['data']['resolvedSponsors'] = $role->getSponsorLogos();
                } else {
                    $block['data']['resolvedSponsors'] = [];
                }
                $block['data']['sponsorTitle'] = $role ? $role->translatedSponsorSectionTitle() : '';
            }

            if ($type === 'poll') {
                $block['data']['resolvedPoll'] = null;
                if ($role) {
                    foreach ($upcoming ??= $this->upcomingEvents($role) as $pair) {
                        $event = $pair['event'];
                        $poll = $event->activePolls()->first();
                        if ($poll) {
                            $block['data']['resolvedPoll'] = [
                                'question' => $poll->question,
                                'options' => $poll->options,
                                'eventName' => $event->name,
                                'eventUrl' => $event->getGuestUrl($role->subdomain, $event->days_of_week ? $pair['date'] : null, true),
                            ];
                            break;
                        }
                    }
                }
            }
        }

        return $blocks;
    }

    public function deriveEventIds(Newsletter $newsletter): ?array
    {
        $blocks = $newsletter->blocks ?? [];
        $allEventIds = [];
        $hasEventBlock = false;

        foreach ($blocks as $block) {
            if (($block['type'] ?? '') === 'events') {
                $hasEventBlock = true;
                $useAll = $block['data']['useAllEvents'] ?? true;
                if (! $useAll && ! empty($block['data']['eventIds'])) {
                    $allEventIds = array_merge($allEventIds, $block['data']['eventIds']);
                }
            }
        }

        if (! $hasEventBlock) {
            return [];
        }

        return empty($allEventIds) ? null : array_unique($allEventIds);
    }

    public function renderHtml(Newsletter $newsletter, ?NewsletterRecipient $recipient = null, ?array $processedBlocks = null): string
    {
        $style = array_merge(Newsletter::defaultStyleSettings(), $newsletter->style_settings ?? []);
        $blocks = $processedBlocks ?? $this->processBlocks($newsletter);
        $unsubscribeUrl = $recipient
            ? url('/nl/u/'.$recipient->token)
            : '#';

        // '#' rather than null in renderPreview(), matching $unsubscribeUrl above: dropping the line
        // entirely made the owner's preview disagree with what recipients actually receive, showing
        // "Unsubscribe" where the sent mail says "Manage your account - Unsubscribe". renderPreview()
        // already neutralises every link with `a { pointer-events: none !important }`, so a '#' here
        // is as inert as the one the unsubscribe link has always used.
        $manageUrl = $recipient
            ? url('/sub/m/'.$recipient->token)
            : '#';

        $role = $newsletter->role;
        $isRtl = $role ? $role->isRtl() : false;

        return $this->inScheduleLocale($role, fn () => view('emails.newsletter', [
            'newsletter' => $newsletter,
            'style' => $style,
            'blocks' => $blocks,
            'role' => $role,
            'unsubscribeUrl' => $unsubscribeUrl,
            'manageUrl' => $manageUrl,
            'recipient' => $recipient,
            'showBranding' => $role ? $role->showBranding() : false,
            'isRtl' => $isRtl,
        ])->render());
    }

    /**
     * Run $work in the schedule's language, or in English for a platform newsletter, and put the
     * locale back whatever happens.
     */
    private function inScheduleLocale(?Role $role, callable $work): mixed
    {
        $original = app()->getLocale();

        try {
            if ($role && is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            } elseif (! $role) {
                app()->setLocale('en');
            }

            return $work();
        } finally {
            app()->setLocale($original);
        }
    }

    public function renderPreview(Newsletter $newsletter): string
    {
        $html = $this->renderHtml($newsletter, null);

        $style = '<style>a { pointer-events: none !important; cursor: default !important; }</style>';
        $html = str_replace('</head>', $style.'</head>', $html);

        return $html;
    }

    /**
     * What ties a click link to the one address it was written for. Without it the link went
     * wherever its last segment said, for anybody holding any recipient's token.
     */
    public static function clickSignature(string $token, string $url): string
    {
        return substr(hash_hmac('sha256', 'newsletter-click|'.$token.'|'.$url, (string) config('app.key')), 0, 32);
    }

    public function rewriteLinks(string $html, NewsletterRecipient $recipient): string
    {
        return preg_replace_callback(
            '/<a\s([^>]*?)href=["\']([^"\']+)["\']/i',
            function ($matches) use ($recipient) {
                // The attribute as a browser reads it. Blade writes "&" as "&amp;", and encoding
                // that text sent every reader of a link with two query parameters to
                // "?a=1&amp;b=2": the second parameter arrived named "amp;b".
                $url = html_entity_decode($matches[2], ENT_QUOTES | ENT_HTML5);
                // Don't rewrite unsubscribe links or mailto links.
                //
                // /sub/m/ is exempt for a different reason than /nl/u/: a mail gateway that
                // prefetches footer links would otherwise record a phantom CLICK through
                // NewsletterTrackingController, inflating click-through for every schedule that
                // sends one. The page itself is prefetch-safe - it mutates nothing - but the
                // tracking hop in front of it is not.
                // The scheme in any case: a phone's keyboard writes "Mailto:", which the views link
                // (UrlUtils::safeActionHref()) and the tracking route then refused with a 404.
                if (str_contains($url, '/nl/u/') || str_contains($url, '/sub/m/') || preg_match('/^(?:mailto|tel):/i', $url) || $url === '#') {
                    return $matches[0];
                }
                $encodedUrl = rtrim(strtr(base64_encode($url), '+/', '-_'), '=');
                $trackingUrl = url('/nl/c/'.$recipient->token.'/'.self::clickSignature((string) $recipient->token, $url).'/'.$encodedUrl);

                return '<a '.$matches[1].'href="'.$trackingUrl.'"';
            },
            $html
        ) ?? $html;
    }

    public function insertTrackingPixel(string $html, NewsletterRecipient $recipient): string
    {
        $pixelUrl = url('/nl/o/'.$recipient->token);
        $pixel = '<img src="'.$pixelUrl.'" width="1" height="1" alt="" style="display:block;width:1px;height:1px;border:0;" />';

        if (str_contains($html, '</body>')) {
            return str_replace('</body>', $pixel.'</body>', $html);
        }

        return $html.$pixel;
    }

    /** How far ahead a series may begin and still be listed. The schedule page's own list stops at 60 days. */
    private const SERIES_HORIZON_DAYS = 366;

    /**
     * What the schedule's own page lists as upcoming, as [event, date] pairs: accepted on the
     * schedule, public, and a recurring series under its NEXT date (EventRepo::upcomingForGuest()).
     *
     * This used to be `$role->events()` filtered to `starts_at >= now`. That relation has no
     * accepted filter, so a booking request the schedule had not answered was mailed to its
     * audience as one of its events; and a series, whose starts_at is its FIRST date, dropped out
     * the day after it began.
     *
     * Two things differ from the page. A series may begin up to a year out, as a one-off may: the
     * page's 60 days would leave out a weekly night announced three months ahead. And each pair is
     * the occurrence a reader can still go to (aheadOfNow()), which is why twice the number wanted
     * is asked for: a series moved to its next date can fall behind events that were below the cut.
     *
     * @return Collection<int, array{event: Event, date: ?string}>
     */
    private function upcomingEvents(Role $role, int $limit = 10): Collection
    {
        $pairs = app(EventRepo::class)->upcomingForGuest($role, null, $limit * 2, self::SERIES_HORIZON_DAYS)
            ->map(fn (array $pair) => $this->aheadOfNow($pair['event'], $pair['date']))
            ->filter()
            ->sortBy(fn (array $pair) => $pair['date'].' '.$pair['event']->localTimeOfDay())
            ->take($limit)
            ->values();

        $this->loadForRows($pairs->pluck('event'));

        return $pairs;
    }

    /**
     * What the builder's event picker offers: the events a mail could list, each under the date
     * the mail would print it with.
     *
     * From the same list as the mail, and that is the point. The picker used to run its own
     * query (`$role->events()` from today on), so it offered events the mail then left out (not
     * accepted, private, cancelled, behind a password) and could not offer a running series,
     * whose starts_at is its first date.
     *
     * @return array<int, array{id: int, name: string, date: string}>
     */
    public function eventChoices(Role $role, int $limit = 50): array
    {
        return $this->upcomingEvents($role, $limit)
            ->map(fn (array $pair) => [
                'id' => $pair['event']->id,
                'name' => $pair['event']->name,
                'date' => (string) ($this->eventRow($pair['event'], $pair['date'], $role)['date'] ?? ''),
            ])
            ->all();
    }

    /**
     * The events an owner picked by hand, as [event, date] pairs in the order of the dates they
     * will show: a series under its next date, anything else under its own.
     *
     * Not in the stored order. The builder's picker is a column of tick boxes, so that order is
     * only the order they were ticked in, and it printed 11, 16, 8 down a column of dates.
     *
     * One that is over is left out, like a cancelled one. A cloned newsletter or a saved template
     * carries its ticked ids for months, the builder lists only what is coming and so cannot
     * untick them, and in date order a finished show would lead the mail.
     *
     * @return Collection<int, array{event: Event, date: ?string}>
     */
    private function pickedEvents(Role $role, array $eventIds): Collection
    {
        $events = $role->events()
            ->whereIn('events.id', $eventIds)
            ->wherePivot('is_accepted', true)
            ->with(['roles', 'creatorRole'])
            ->get()
            ->filter(fn (Event $event) => ! $event->is_draft && ! $event->is_private && ! $event->is_cancelled && ! $event->isPasswordProtected())
            ->unique('id');

        $pairs = $events
            ->map(fn (Event $event) => $event->days_of_week
                ? $this->aheadOfNow($event, $this->nextOccurrence($event))
                : ($this->isOver($event) ? null : ['event' => $event, 'date' => $event->saleEventDateFromStartsAt()]))
            ->filter()
            ->sortBy(fn (array $pair) => ($pair['date'] ?? '9999-99-99').' '.$pair['event']->localTimeOfDay())
            ->values()
            ->toBase();

        $this->loadForRows($pairs->pluck('event'));

        return $pairs;
    }

    /**
     * [event, date] for the occurrence a reader can still go to, or null when a series has none.
     *
     * A series is dated by DAY: nextOccurrenceFrom() answers today whatever the clock says, so a
     * daily nine o'clock class mailed at two was listed first, under this morning, with a price
     * and a Get Tickets button for a date its own page refuses. Once today's has begun the pair
     * is the next one (Event::occurrencesAfter(), which the schedule page's lead uses too).
     */
    private function aheadOfNow(Event $event, ?string $date): ?array
    {
        if (! $event->days_of_week) {
            return ['event' => $event, 'date' => $date];
        }

        if (! $date) {
            return null;
        }

        $zone = $event->scheduleTimezone();

        if ($event->getStartDateTime($date, true, $zone)->gt(Carbon::now($zone))) {
            return ['event' => $event, 'date' => $date];
        }

        $next = $event->occurrencesAfter(null, 1)[0] ?? null;

        return $next ? ['event' => $event, 'date' => $next] : null;
    }

    /**
     * A series' next day within the horizon, or null: none left, or a recurrence that cannot be
     * read. matchesDate() throws on a restored series (an interval of 0, a days_of_week shorter
     * than seven characters, an end date that is not a date), and one such event must cost
     * itself, not the newsletter: EventRepo::nextOccurrenceOrNull() guards the page the same way.
     */
    private function nextOccurrence(Event $event): ?string
    {
        try {
            return $event->nextOccurrenceFrom(null, self::SERIES_HORIZON_DAYS);
        } catch (\Throwable $e) {
            report($e);

            return null;
        }
    }

    /** Whether a one-off event has ended: nothing a reader can still go to. */
    private function isOver(Event $event): bool
    {
        if (! $event->starts_at) {
            return false;
        }

        return $event->getEndDateTime(null, true)->isPast();
    }

    /** One query for every event's tickets, which a row's price line reads. */
    private function loadForRows(Collection $events): void
    {
        \Illuminate\Database\Eloquent\Collection::make($events->all())->loadMissing('tickets');
    }

    /**
     * One event as the plain strings a newsletter prints, for the occurrence on $date.
     *
     * Resolved here, once per send, and not in the view: the view is rendered once per RECIPIENT,
     * and it used to ask each event for its address, its venue and its picture every time. The
     * views and the x-newsletter components read these keys and nothing else of an event.
     *
     * The clock is the event's own schedule's (getStartDateTime() with no override), and the
     * names of months and days are the mail's language: this runs inside inScheduleLocale().
     */
    private function eventRow(Event $event, ?string $date, Role $role): array
    {
        // An event with no owning schedule (older rows) has no clock of its own and would print
        // on the app's: the sender's is the one its page on this schedule has always used.
        $zone = $event->creator_role_id ? $event->scheduleTimezone() : ($role->timezone ?: $event->scheduleTimezone());
        $start = $event->starts_at ? $event->getStartDateTime($date, true, $zone) : null;
        $thisYear = $start && $start->year === Carbon::now($zone)->year;

        // The flyer, else a performer's photo. Never the sending schedule's own picture: it is
        // already the masthead, and on a venue's newsletter it stood in for every flyer-less event.
        $image = $large = $size = null;
        if ($event->flyer_image_url) {
            $image = $event->getImageUrl(480);
            $large = $event->getImageUrl(960);
            $size = $event->imageSourceDimensions();
        } elseif (($talent = $event->role()) && $talent->id !== $role->id && $talent->profile_image_url) {
            $image = $large = $talent->getProfileImageUrl(480);
            $size = $talent->imageSourceDimensions();
        }

        $venue = $event->venue;
        // The event page's own gate for its price line (event/show-guest): sign-up events and
        // tickets sold elsewhere print none, and neither does a date that can no longer be bought.
        $selling = ! $event->rsvp_enabled && $event->canSellTickets($date);
        $summary = $selling ? $event->ticketPriceSummary($date) : null;
        $state = $selling ? $event->ticketSaleState($date) : null;
        $buy = $summary && ! $summary['free'] && $state === 'open';

        return [
            'name' => $event->name,
            'url' => $event->getGuestUrl($role->subdomain, $event->days_of_week ? $date : null, true),
            'image' => $image,
            'imageLarge' => $large,
            'ratio' => $size ? $size[0] / max(1, $size[1]) : null,
            'month' => $start?->translatedFormat('M'),
            'day' => $start?->format('j'),
            'weekday' => $start?->translatedFormat('l'),
            'date' => match (true) {
                ! $start => null,
                (bool) $event->is_multi_day => $this->shortDateRange($start, $start->copy()->addMinutes($event->durationInMinutes()), $thisYear),
                default => $start->translatedFormat($thisYear ? 'D, M j' : 'D, M j, Y'),
            },
            // format(), not translatedFormat(): Carbon translates the meridiem in some languages
            // ("8:00 's middags" in Dutch, "8:00 вечера" in Russian) and the event page, which
            // the row links to, says "8:00 PM" in all of them (Event::getStartEndTime()).
            'time' => $start && ! $event->is_multi_day && ! $event->hasDateOnlyStart()
                ? $start->format($role->use_24_hour_time ? 'H:i' : 'g:i A')
                : null,
            'multiDay' => (bool) $event->is_multi_day,
            'repeat' => $event->recurrenceSummary()['label'] ?? null,
            // A venue's own name under every one of its own events says nothing.
            'venue' => $venue && $venue->id !== $role->id ? trim((string) $venue->name) : null,
            'price' => match (true) {
                $state === 'sold_out' => __('messages.sold_out'),
                ! $summary => null,
                // Event::priceWording(): a free type beside paid ones is Free, never "From $0".
                $summary['says'] === 'free' => __('messages.free'),
                $summary['says'] === 'from' => __('messages.price_from', ['price' => MoneyUtils::format($summary['min'], $summary['currency'])]),
                default => MoneyUtils::format($summary['min'], $summary['currency']),
            },
            'soldOut' => $state === 'sold_out',
            'low' => $state === 'open' && ($summary['low'] ?? false) ? __('messages.few_left') : null,
            // Whether the action sells: Bold fills that button and outlines one that only opens the page.
            'buy' => $buy,
            'cta' => $buy ? __('messages.get_tickets') : __('messages.view_event'),
        ];
    }

    /** "Nov 1 - 3", "Oct 30 - Nov 1", with the year only when it is not this one. */
    private function shortDateRange(Carbon $start, Carbon $end, bool $thisYear): string
    {
        $year = $thisYear && $end->year === $start->year ? '' : ', '.$end->year;

        return $start->month === $end->month && $start->year === $end->year
            ? $start->translatedFormat('M j').' - '.$end->format('j').$year
            : $start->translatedFormat('M j').' - '.$end->translatedFormat('M j').$year;
    }

    public function selectAbTestWinner(\App\Models\NewsletterAbTest $abTest): void
    {
        $abTest->load('newsletters.recipients');

        $variantA = $abTest->newsletters->where('ab_variant', 'A')->first();
        $variantB = $abTest->newsletters->where('ab_variant', 'B')->first();

        if (! $variantA || ! $variantB) {
            return;
        }

        $criteria = $abTest->winner_criteria;

        $scoreA = $this->calculateVariantScore($variantA, $criteria);
        $scoreB = $this->calculateVariantScore($variantB, $criteria);

        $winner = $scoreA >= $scoreB ? 'A' : 'B';

        $abTest->update([
            'winner_variant' => $winner,
            'winner_selected_at' => now(),
        ]);

        // Send winner to remaining recipients
        $winnerNewsletter = $winner === 'A' ? $variantA : $variantB;
        $this->sendToRemainingRecipients($abTest, $winnerNewsletter);

        $abTest->update(['status' => 'completed']);
    }

    protected function calculateVariantScore(Newsletter $newsletter, string $criteria): float
    {
        $sent = $newsletter->recipients->where('status', 'sent');
        $sentCount = $sent->count();
        if ($sentCount === 0) {
            return 0;
        }

        return match ($criteria) {
            'click_rate' => $sent->whereNotNull('clicked_at')->count() / $sentCount,
            default => $sent->whereNotNull('opened_at')->count() / $sentCount, // open_rate
        };
    }

    protected function sendToRemainingRecipients(\App\Models\NewsletterAbTest $abTest, Newsletter $winnerNewsletter): void
    {
        // Check for existing remainder newsletter from a prior attempt
        $remainderNewsletter = Newsletter::where('ab_test_id', $abTest->id)
            ->whereNull('ab_variant')
            ->first();

        if ($remainderNewsletter && $remainderNewsletter->status === 'sent') {
            return;
        }

        // Get all emails already sent in the A/B test
        $sentEmails = array_flip(
            NewsletterRecipient::whereIn('newsletter_id', $abTest->newsletters->pluck('id'))
                ->pluck('email')
                ->map(fn ($e) => strtolower($e))
                ->toArray()
        );

        // Resolve full recipient list and remove already-sent
        $allRecipients = $winnerNewsletter->isAdmin()
            ? $this->resolveAdminRecipients($winnerNewsletter->segment_ids ?? [])
            : $this->resolveRecipients($winnerNewsletter->role, $winnerNewsletter->segment_ids ?? []);
        $remaining = $allRecipients->filter(fn ($r) => ! isset($sentEmails[$r->email]));

        if (! $remainderNewsletter) {
            if ($remaining->isEmpty()) {
                return;
            }

            $remainderNewsletter = $winnerNewsletter->replicate();
            $remainderNewsletter->ab_test_id = $abTest->id;
            $remainderNewsletter->ab_variant = null;
            $remainderNewsletter->status = 'sending';
            $remainderNewsletter->send_token = Str::random(64);
            $remainderNewsletter->save();
        }

        // Exclude recipients already created on the remainder newsletter
        $existingRemainderEmails = array_flip(
            NewsletterRecipient::where('newsletter_id', $remainderNewsletter->id)
                ->pluck('email')
                ->map(fn ($e) => strtolower($e))
                ->toArray()
        );

        $remaining = $remaining->filter(fn ($r) => ! isset($existingRemainderEmails[$r->email]));

        if ($remaining->isEmpty()) {
            return;
        }

        DB::beginTransaction();
        try {
            $chunk = [];
            foreach ($remaining as $recipient) {
                $chunk[] = [
                    'newsletter_id' => $remainderNewsletter->id,
                    'user_id' => $recipient->user_id,
                    'email' => $recipient->email,
                    'name' => $recipient->name,
                    'token' => Str::random(64),
                    'status' => 'pending',
                ];
                if (count($chunk) >= 500) {
                    NewsletterRecipient::insert($chunk);
                    $chunk = [];
                }
            }
            if (! empty($chunk)) {
                NewsletterRecipient::insert($chunk);
            }
            DB::commit();
        } catch (\Throwable $e) {
            // \Throwable for the same reason as the sibling block above: a raw transaction left
            // open by an \Error poisons every write the rest of the cron tick makes.
            DB::rollBack();
            $remainderNewsletter->update(['status' => 'draft', 'send_token' => null]);
            report($e);

            throw $e;
        }

        $batchIndex = 0;
        NewsletterRecipient::where('newsletter_id', $remainderNewsletter->id)
            ->where('status', 'pending')
            ->chunkById(50, function ($recipientChunk) use ($remainderNewsletter, &$batchIndex) {
                SendNewsletterBatch::dispatch($remainderNewsletter->id, $recipientChunk->pluck('id')->toArray())
                    ->delay(now()->addSeconds($batchIndex * 15));
                $batchIndex++;
            });
    }

    protected function isTestEmail(string $email): bool
    {
        $email = strtolower($email);

        $testDomains = [
            '@example.com', '@example.org', '@example.net',
            '@test.com', '@test.org', '@test.net',
            '@localhost',
        ];

        $emailDomain = substr($email, strrpos($email, '@'));

        foreach ($testDomains as $domain) {
            if ($emailDomain === $domain) {
                return true;
            }
        }

        return false;
    }
}
