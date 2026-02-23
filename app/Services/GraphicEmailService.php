<?php

namespace App\Services;

use App\Mail\GraphicEmail;
use App\Models\Event;
use App\Models\Role;
use App\Utils\EventTextGenerator;
use App\Utils\GeminiUtils;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GraphicEmailService
{
    /**
     * Send the graphic email to the specified recipient(s)
     *
     * @param  string  $recipientEmails  Comma-separated email addresses
     */
    public function sendGraphicEmail(Role $role, string $recipientEmails): bool
    {
        // Skip sending for demo role
        if (is_demo_role($role)) {
            Log::info('Skipping graphic email for demo role', ['role_id' => $role->id]);

            return false;
        }

        try {
            $settings = $role->graphic_settings;
            $layout = $settings['layout'] ?? 'grid';
            $directRegistration = $role->direct_registration;
            $excludeRecurring = $settings['exclude_recurring'] ?? false;

            // Use saved event_count setting (default 20, matching web preview)
            $eventCountSetting = $settings['event_count'] ?? null;
            $eventLimit = $eventCountSetting ? (int) $eventCountSetting : 20;

            // Build the events query
            $query = Event::with(['roles', 'tickets', 'venue'])
                ->whereHas('roles', function ($query) use ($role) {
                    $query->where('role_id', $role->id)->where('is_accepted', true);
                })
                ->where('starts_at', '>=', now())
                ->whereNotNull('flyer_image_url')
                ->where('flyer_image_url', '!=', '')
                ->where('is_private', false)
                ->whereNull('event_password');

            // Only exclude recurring events if setting is true
            if ($excludeRecurring) {
                $query->whereNull('days_of_week');
            }

            $events = $query->orderBy('starts_at')
                ->limit($eventLimit)
                ->get();

            if ($events->isEmpty()) {
                Log::info('No events found for graphic email', ['role_id' => $role->id]);

                return false;
            }

            // Build options array for the generator (matching web preview)
            $datePosition = $settings['date_position'] ?? null;
            if (! in_array($layout, ['grid', 'row'])) {
                $datePosition = null;
            }
            $maxPerRow = $settings['max_per_row'] ?? null;
            if ($layout !== 'row') {
                $maxPerRow = null;
            }
            $overlayText = $settings['overlay_text'] ?? '';

            $options = [
                'date_position' => $datePosition,
                'max_per_row' => $maxPerRow,
                'overlay_text' => $overlayText,
                'header_image_url' => $settings['header_image_url'] ?? null,
            ];

            // Generate the graphic image
            $generator = new EventGraphicGenerator($role, $events, $layout, $directRegistration, $options);
            $imageData = $generator->generate();

            // Generate event text using shared template logic
            $textTemplate = $settings['text_template'] ?? '';
            $urlSettings = [
                'url_include_https' => $settings['url_include_https'] ?? false,
                'url_include_id' => $settings['url_include_id'] ?? false,
            ];
            $eventText = EventTextGenerator::generate($role, $events, $directRegistration, $textTemplate, $urlSettings);

            // Apply AI prompt if configured (Enterprise feature)
            $aiPrompt = trim($settings['ai_prompt'] ?? '');
            if ($role->isEnterprise() && ! empty($aiPrompt) && config('services.google.gemini_key')) {
                $transformedText = $this->applyAiPrompt($eventText, $aiPrompt);
                if ($transformedText) {
                    $eventText = $transformedText;
                }
            }

            // Parse comma-separated emails and validate
            $emailList = array_map('trim', explode(',', $recipientEmails));
            $emailList = array_filter($emailList, fn ($e) => filter_var($e, FILTER_VALIDATE_EMAIL));

            if (empty($emailList)) {
                Log::warning('No valid recipient emails provided', ['role_id' => $role->id, 'recipient_emails' => $recipientEmails]);

                return false;
            }

            // Send the email to all recipients in a single email
            if (config('app.hosted')) {
                // For hosted users, only send if role has email settings
                if ($role->hasEmailSettings()) {
                    $this->configureRoleMailer($role);
                    $mailerName = 'role_'.$role->id;
                    Mail::mailer($mailerName)->to(array_values($emailList))->send(new GraphicEmail($role, $imageData, $eventText));
                } else {
                    // Use default mailer
                    Mail::to(array_values($emailList))->send(new GraphicEmail($role, $imageData, $eventText));
                }
            } else {
                // For selfhost users, use system email settings
                Mail::to(array_values($emailList))->send(new GraphicEmail($role, $imageData, $eventText));
            }

            UsageTrackingService::track(UsageTrackingService::EMAIL_GRAPHIC, $role->id);

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send graphic email: '.$e->getMessage(), [
                'role_id' => $role->id,
                'recipients' => $recipientEmails,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Apply AI prompt to transform the event text
     */
    protected function applyAiPrompt(string $eventText, string $aiPrompt): ?string
    {
        try {
            $prompt = "Transform the following event listing text according to these instructions: \"{$aiPrompt}\"\n\n";
            $prompt .= "Original text:\n{$eventText}\n\n";
            $prompt .= "Respond with only the transformed text, preserving the structure and URLs. Return JSON with a single 'text' field containing the result.";

            $response = GeminiUtils::sendPrompt($prompt);

            if ($response && isset($response['text'])) {
                return $response['text'];
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to apply AI prompt to event text: '.$e->getMessage());

            return null;
        }
    }

    /**
     * Configure mailer with role-specific SMTP settings
     */
    protected function configureRoleMailer(Role $role): void
    {
        $emailSettings = $role->getEmailSettings();

        if (empty($emailSettings)) {
            return;
        }

        // Create a unique mailer name for this role
        $mailerName = 'role_'.$role->id;

        // Configure the mailer
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
}
