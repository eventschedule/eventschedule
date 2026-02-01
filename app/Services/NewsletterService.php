<?php

namespace App\Services;

use App\Jobs\SendNewsletterBatch;
use App\Mail\NewsletterEmail;
use App\Models\Newsletter;
use App\Models\NewsletterRecipient;
use App\Models\NewsletterSegment;
use App\Models\NewsletterUnsubscribe;
use App\Models\Role;
use App\Utils\MarkdownUtils;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;

class NewsletterService
{
    public function send(Newsletter $newsletter): bool
    {
        if (in_array($newsletter->status, ['sending', 'sent'])) {
            return false;
        }

        $role = $newsletter->role;
        if ($role && ! $role->canSendNewsletter()) {
            return false;
        }

        $sendToken = Str::random(64);
        $newsletter->update([
            'status' => 'sending',
            'send_token' => $sendToken,
        ]);

        $segmentIds = $newsletter->segment_ids ?? [];
        $recipients = $this->resolveRecipients($newsletter->role, $segmentIds);

        if ($recipients->isEmpty()) {
            $newsletter->update(['status' => 'sent', 'sent_at' => now(), 'sent_count' => 0]);

            return true;
        }

        $recipientIds = [];
        foreach ($recipients as $recipient) {
            $nr = NewsletterRecipient::create([
                'newsletter_id' => $newsletter->id,
                'user_id' => $recipient->user_id,
                'email' => $recipient->email,
                'name' => $recipient->name,
                'token' => Str::random(64),
                'status' => 'pending',
            ]);
            $recipientIds[] = $nr->id;
        }

        $chunks = array_chunk($recipientIds, 50);
        foreach ($chunks as $chunk) {
            SendNewsletterBatch::dispatch($newsletter->id, $chunk);
        }

        return true;
    }

    public function sendToRecipient(Newsletter $newsletter, NewsletterRecipient $recipient): bool
    {
        if ($this->isTestEmail($recipient->email)) {
            $recipient->update(['status' => 'sent', 'sent_at' => now()]);

            return false;
        }

        try {
            $html = $this->renderHtml($newsletter, $recipient);
            $html = $this->rewriteLinks($html, $recipient);
            $html = $this->insertTrackingPixel($html, $recipient);

            $mailable = new NewsletterEmail($newsletter, $recipient, $html);

            $role = $newsletter->role;
            if (config('app.hosted') && $role && $role->hasEmailSettings()) {
                $this->configureRoleMailer($role);
                $mailerName = 'role_'.$role->id;
                Mail::mailer($mailerName)->to($recipient->email)->send($mailable);
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

    public function resolveRecipients(Role $role, array $segmentIds): Collection
    {
        if (empty($segmentIds)) {
            $segments = NewsletterSegment::where('role_id', $role->id)
                ->where('type', 'all_followers')
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

        // If no segments found and no segmentIds, fall back to followers
        if ($allRecipients->isEmpty() && empty($segmentIds)) {
            $allRecipients = $role->followers()
                ->select('users.id', 'users.email', 'users.name', 'users.is_subscribed')
                ->where('users.is_subscribed', true)
                ->get()
                ->map(fn ($user) => (object) [
                    'user_id' => $user->id,
                    'email' => strtolower($user->email),
                    'name' => $user->name,
                ]);
        }

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

        $excludeEmails = array_merge($unsubscribedEmails, $unsubscribedUserEmails);

        $allRecipients = $allRecipients->filter(function ($recipient) use ($excludeEmails) {
            return ! in_array($recipient->email, $excludeEmails)
                && ! $this->isTestEmail($recipient->email);
        });

        return $allRecipients->values();
    }

    public function processBlocks(Newsletter $newsletter): array
    {
        $blocks = $newsletter->blocks ?? [];
        $role = $newsletter->role;

        foreach ($blocks as &$block) {
            $type = $block['type'] ?? '';

            if ($type === 'text' && ! empty($block['data']['content'])) {
                $block['data']['contentHtml'] = MarkdownUtils::convertToHtml($block['data']['content']);
            }

            if ($type === 'events') {
                $useAll = $block['data']['useAllEvents'] ?? true;
                $eventIds = $block['data']['eventIds'] ?? [];
                $block['data']['resolvedEvents'] = $this->getUpcomingEvents($role, $useAll ? null : $eventIds);
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

    public function renderHtml(Newsletter $newsletter, ?NewsletterRecipient $recipient = null): string
    {
        $style = array_merge(Newsletter::defaultStyleSettings(), $newsletter->style_settings ?? []);
        $blocks = $this->processBlocks($newsletter);
        $unsubscribeUrl = $recipient
            ? url('/nl/u/'.$recipient->token)
            : '#';

        $role = $newsletter->role;
        $isRtl = $role && $role->isRtl();

        $originalLocale = app()->getLocale();

        try {
            if ($role && is_valid_language_code($role->language_code)) {
                app()->setLocale($role->language_code);
            }

            return view('emails.newsletter', [
                'newsletter' => $newsletter,
                'style' => $style,
                'blocks' => $blocks,
                'role' => $role,
                'unsubscribeUrl' => $unsubscribeUrl,
                'recipient' => $recipient,
                'showBranding' => $role ? $role->showBranding() : false,
                'isRtl' => $isRtl,
            ])->render();
        } finally {
            app()->setLocale($originalLocale);
        }
    }

    public function renderPreview(Newsletter $newsletter): string
    {
        return $this->renderHtml($newsletter, null);
    }

    public function rewriteLinks(string $html, NewsletterRecipient $recipient): string
    {
        return preg_replace_callback(
            '/<a\s([^>]*?)href=["\']([^"\']+)["\']/i',
            function ($matches) use ($recipient) {
                $url = $matches[2];
                // Don't rewrite unsubscribe links or mailto links
                if (str_contains($url, '/nl/u/') || str_starts_with($url, 'mailto:') || $url === '#') {
                    return $matches[0];
                }
                $encodedUrl = base64_encode($url);
                $trackingUrl = url('/nl/c/'.$recipient->token.'/'.$encodedUrl);

                return '<a '.$matches[1].'href="'.$trackingUrl.'"';
            },
            $html
        );
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

    public function getUpcomingEvents(Role $role, ?array $eventIds = null): Collection
    {
        if ($eventIds) {
            $events = collect();
            foreach ($eventIds as $eventId) {
                $event = $role->events()
                    ->where('events.id', $eventId)
                    ->first();
                if ($event) {
                    $events->push($event);
                }
            }

            return $events;
        }

        return $role->events()
            ->where('starts_at', '>=', now())
            ->orderBy('starts_at', 'asc')
            ->limit(10)
            ->get();
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
            'status' => 'completed',
        ]);

        // Send winner to remaining recipients
        $winnerNewsletter = $winner === 'A' ? $variantA : $variantB;
        $this->sendToRemainingRecipients($abTest, $winnerNewsletter);
    }

    protected function calculateVariantScore(Newsletter $newsletter, string $criteria): float
    {
        $sentCount = $newsletter->recipients->where('status', 'sent')->count();
        if ($sentCount === 0) {
            return 0;
        }

        return match ($criteria) {
            'click_rate' => $newsletter->recipients->whereNotNull('clicked_at')->count() / $sentCount,
            default => $newsletter->recipients->whereNotNull('opened_at')->count() / $sentCount, // open_rate
        };
    }

    protected function sendToRemainingRecipients(\App\Models\NewsletterAbTest $abTest, Newsletter $winnerNewsletter): void
    {
        // Get all emails already sent in the A/B test
        $sentEmails = NewsletterRecipient::whereIn('newsletter_id', $abTest->newsletters->pluck('id'))
            ->pluck('email')
            ->map(fn ($e) => strtolower($e))
            ->toArray();

        // Resolve full recipient list and remove already-sent
        $allRecipients = $this->resolveRecipients($winnerNewsletter->role, $winnerNewsletter->segment_ids ?? []);
        $remaining = $allRecipients->filter(fn ($r) => ! in_array($r->email, $sentEmails));

        if ($remaining->isEmpty()) {
            return;
        }

        // Create a new newsletter for the remaining send
        $remainderNewsletter = $winnerNewsletter->replicate();
        $remainderNewsletter->ab_test_id = $abTest->id;
        $remainderNewsletter->ab_variant = null;
        $remainderNewsletter->status = 'sending';
        $remainderNewsletter->send_token = Str::random(64);
        $remainderNewsletter->save();

        $recipientIds = [];
        foreach ($remaining as $recipient) {
            $nr = NewsletterRecipient::create([
                'newsletter_id' => $remainderNewsletter->id,
                'user_id' => $recipient->user_id,
                'email' => $recipient->email,
                'name' => $recipient->name,
                'token' => Str::random(64),
                'status' => 'pending',
            ]);
            $recipientIds[] = $nr->id;
        }

        $chunks = array_chunk($recipientIds, 50);
        foreach ($chunks as $chunk) {
            SendNewsletterBatch::dispatch($remainderNewsletter->id, $chunk);
        }
    }

    protected function configureRoleMailer(Role $role): void
    {
        $emailSettings = $role->getEmailSettings();

        if (empty($emailSettings)) {
            return;
        }

        $mailerName = 'role_'.$role->id;

        Config::set("mail.mailers.{$mailerName}", [
            'transport' => 'smtp',
            'host' => $emailSettings['host'] ?? config('mail.mailers.smtp.host'),
            'port' => $emailSettings['port'] ?? config('mail.mailers.smtp.port'),
            'encryption' => $emailSettings['encryption'] ?? config('mail.mailers.smtp.encryption'),
            'username' => $emailSettings['username'] ?? null,
            'password' => $emailSettings['password'] ?? null,
            'timeout' => null,
            'local_domain' => config('mail.mailers.smtp.local_domain'),
        ]);
    }

    protected function isTestEmail(string $email): bool
    {
        $email = strtolower($email);

        $testDomains = [
            '@example.com', '@example.org', '@example.net',
            '@test.com', '@test.org', '@test.net',
            '@localhost',
        ];

        foreach ($testDomains as $domain) {
            if (str_contains($email, $domain)) {
                return true;
            }
        }

        return false;
    }
}
