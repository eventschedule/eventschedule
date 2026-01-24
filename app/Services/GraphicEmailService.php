<?php

namespace App\Services;

use App\Mail\GraphicEmail;
use App\Models\Event;
use App\Models\Role;
use App\Utils\GeminiUtils;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class GraphicEmailService
{
    /**
     * Send the graphic email to the specified recipient
     */
    public function sendGraphicEmail(Role $role, string $recipientEmail): bool
    {
        // Skip sending for demo role
        if (is_demo_role($role)) {
            Log::info('Skipping graphic email for demo role', ['role_id' => $role->id]);

            return false;
        }

        try {
            $settings = $role->graphic_settings;
            $layout = $settings['layout'] ?? 'grid';
            $directRegistration = ($settings['link_type'] ?? 'schedule') === 'registration';

            // Get the next 10 events with flyer images
            $events = Event::with('roles')
                ->whereHas('roles', function ($query) use ($role) {
                    $query->where('role_id', $role->id)->where('is_accepted', true);
                })
                ->where('starts_at', '>=', now())
                ->where('flyer_image_url', '!=', null)
                ->whereNull('days_of_week')
                ->orderBy('starts_at')
                ->limit(10)
                ->get();

            if ($events->isEmpty()) {
                Log::info('No events found for graphic email', ['role_id' => $role->id]);

                return false;
            }

            // Generate the graphic image
            $generator = new EventGraphicGenerator($role, $events, $layout, $directRegistration);
            $imageData = $generator->generate();

            // Generate event text
            $eventText = $this->generateEventText($role, $events, $directRegistration);

            // Apply AI prompt if configured (Enterprise feature)
            $aiPrompt = trim($settings['ai_prompt'] ?? '');
            if ($role->isEnterprise() && ! empty($aiPrompt) && config('services.google.gemini_key')) {
                $transformedText = $this->applyAiPrompt($eventText, $aiPrompt);
                if ($transformedText) {
                    $eventText = $transformedText;
                }
            }

            // Send the email
            if (config('app.hosted')) {
                // For hosted users, only send if role has email settings
                if ($role->hasEmailSettings()) {
                    $this->configureRoleMailer($role);
                    $mailerName = 'role_'.$role->id;
                    Mail::mailer($mailerName)->to($recipientEmail)->send(new GraphicEmail($role, $imageData, $eventText));
                } else {
                    // Use default mailer
                    Mail::to($recipientEmail)->send(new GraphicEmail($role, $imageData, $eventText));
                }
            } else {
                // For selfhost users, use system email settings
                Mail::to($recipientEmail)->send(new GraphicEmail($role, $imageData, $eventText));
            }

            return true;
        } catch (\Exception $e) {
            Log::error('Failed to send graphic email: '.$e->getMessage(), [
                'role_id' => $role->id,
                'recipient' => $recipientEmail,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }

    /**
     * Generate event text content
     */
    protected function generateEventText(Role $role, $events, bool $directRegistration = false): string
    {
        $text = '';

        foreach ($events as $event) {
            $startDate = $event->getStartDateTime(null, true);
            $dayName = $startDate->format('l');
            $dateStr = $startDate->format('j/n');
            $timeStr = $startDate->format('H:i');

            // Line 1: *DayName* d/m | HH:MM
            $text .= "*{$dayName}* {$dateStr} | {$timeStr}\n";

            // Line 2: *Event Name*:
            $text .= "*{$event->translatedName()}*:\n";

            // Line 3: Venue | City
            if ($event->venue) {
                $venueName = $event->venue->translatedName();
                $city = $event->venue->translatedCity();
                if ($city) {
                    $text .= "{$venueName} | {$city}\n";
                } else {
                    $text .= "{$venueName}\n";
                }
            }

            // Line 4: URL
            $eventUrl = $event->getGuestUrl($role->subdomain, null, true);
            if ($directRegistration && $event->registration_url) {
                if (str_contains($eventUrl, '?')) {
                    $eventUrl = str_replace('?', '/?', $eventUrl);
                } else {
                    $eventUrl .= '/';
                }
            }
            $text .= "{$eventUrl}\n";

            // Blank line between events
            $text .= "\n";
        }

        return $text;
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
