<?php

namespace App\Services;

use App\Http\Controllers\GraphicController;
use App\Mail\GraphicEmail;
use App\Models\Event;
use App\Models\Role;
use App\Utils\EventTextGenerator;
use App\Utils\GeminiUtils;
use App\Utils\OpenAIUtils;
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
            return false;
        }

        try {
            $settings = $role->graphic_settings;
            $layout = $settings['layout'] ?? 'grid';
            $directRegistration = $role->direct_registration;
            $excludeRecurring = $settings['exclude_recurring'] ?? false;

            // Use saved event_count setting (default 20, matching web preview)
            $eventLimit = GraphicController::resolveGraphicEventLimit($settings['event_count'] ?? null);

            // Build the base query for future events
            $baseQuery = function () use ($role, $excludeRecurring) {
                return Event::with(['roles', 'tickets', 'venue'])
                    ->whereHas('roles', function ($query) use ($role) {
                        $query->where('role_id', $role->id)->where('is_accepted', true);
                    })
                    ->upcomingOrOngoing()
                    ->where('is_private', false)
                    ->where('is_draft', false)
                    ->whereNull('event_password')
                    ->when($excludeRecurring, function ($query) {
                        $query->whereNull('days_of_week');
                    });
            };

            // Flyer events are always required for the graphic image
            $events = $baseQuery()
                ->whereNotNull('flyer_image_url')
                ->where('flyer_image_url', '!=', '')
                ->orderBy('starts_at')
                ->limit($eventLimit)
                ->get();

            if ($events->isEmpty()) {
                return false;
            }

            $numberEvents = (bool) ($settings['number_events'] ?? false);

            // Determine text events: when numbering is on, the text must match
            // the flyer-only list so badges and {number} stay in sync.
            $textShowAll = $settings['text_show_all'] ?? false;
            if ($numberEvents) {
                $textEvents = $events;
            } elseif ($textShowAll) {
                $textEvents = $baseQuery()->orderBy('starts_at')->get();
            } else {
                $textEvents = $baseQuery()->orderBy('starts_at')->limit($eventLimit)->get();
            }

            // Build options array for the generator (matching web preview)
            $datePosition = $settings['date_position'] ?? null;
            if (! in_array($layout, ['grid', 'row'])) {
                $datePosition = null;
            }
            $maxPerRow = $settings['max_per_row'] ?? null;
            if (! in_array($layout, ['grid', 'row'])) {
                $maxPerRow = null;
            }
            $overlayText = $settings['overlay_text'] ?? '';

            // Generate output in English instead of the schedule language (no-op for English schedules)
            $forceEnglish = ! $role->isEnglish() && (bool) ($settings['force_english'] ?? false);

            $options = [
                'date_position' => $datePosition,
                'max_per_row' => $maxPerRow,
                'overlay_text' => $overlayText,
                'header_image_url' => $settings['header_image_url'] ?? null,
                'header_text' => $settings['header_text'] ?? '',
                'footer_text' => $settings['footer_text'] ?? '',
                'number_events' => $numberEvents,
                'force_english' => $forceEnglish,
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
            $eventText = EventTextGenerator::generate($role, $textEvents, $directRegistration, $textTemplate, $urlSettings, $forceEnglish);

            // Apply AI prompt if configured (Enterprise feature)
            $aiPrompt = trim($settings['ai_prompt'] ?? '');
            $aiModel = $settings['ai_model'] ?? '';
            if ($role->isEnterprise() && ! empty($aiPrompt) && (config('services.google.gemini_key') || config('services.openai.api_key'))) {
                $transformedText = $this->applyAiPrompt($eventText, $aiPrompt, $aiModel);
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

            // Send the email to all recipients in a single email. When the
            // schedule's custom SMTP is failing, RoleMailerService marks the
            // role as failed, notifies the owner once, and returns false
            // without sending (it does not fall back to the platform mailer);
            // treat that as "not sent".
            $mailable = new GraphicEmail($role, $imageData, $eventText);
            $recipients = array_values($emailList);

            if (config('app.hosted')) {
                if (! app(RoleMailerService::class)->sendForRole($role, $recipients, $mailable)) {
                    return false;
                }
            } else {
                Mail::to($recipients)->send($mailable);
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
    protected function applyAiPrompt(string $eventText, string $aiPrompt, string $aiModel = ''): ?string
    {
        try {
            $prompt = "Transform the following event listing text according to these instructions: \"{$aiPrompt}\"\n\n";
            $prompt .= "Original text:\n{$eventText}\n\n";
            $prompt .= "Respond with only the transformed text, preserving the structure and URLs. Return JSON with a single 'text' field containing the result.";

            // Determine model and provider from config
            $models = config('services.ai.graphic_models', []);
            $modelConfig = $models[$aiModel] ?? null;
            $model = $modelConfig ? $aiModel : 'gemini-2.5-flash';
            $provider = $modelConfig ? $modelConfig['provider'] : 'gemini';

            if ($provider === 'openai') {
                $response = OpenAIUtils::sendTextRequest($prompt, null, 'content', ['model' => $model, 'timeout' => 55]);
            } else {
                $response = GeminiUtils::sendPrompt($prompt, 'content', ['model' => $model, 'timeout' => 55]);
            }

            if ($response && isset($response[0]['text'])) {
                return $response[0]['text'];
            }

            return null;
        } catch (\Exception $e) {
            Log::warning('Failed to apply AI prompt to event text: '.$e->getMessage());

            return null;
        }
    }
}
