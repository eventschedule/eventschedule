<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Role;
use App\Services\EventGraphicGenerator;
use App\Services\GraphicEmailService;
use App\Utils\GeminiUtils;
use Illuminate\Http\Request;

class GraphicController extends Controller
{
    public function generateGraphic(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $layout = $request->get('layout', 'grid');

        // Validate layout parameter
        if (! in_array($layout, ['grid', 'list'])) {
            $layout = 'grid';
        }

        $isPro = $role->isPro();
        $isEnterprise = $role->isEnterprise();
        $graphicSettings = $role->graphic_settings;

        return view('graphic.show', compact('role', 'layout', 'isPro', 'isEnterprise', 'graphicSettings'));
    }

    public function getSettings($subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        return response()->json([
            'settings' => $role->graphic_settings,
            'is_pro' => $role->isPro(),
            'is_enterprise' => $role->isEnterprise(),
        ]);
    }

    public function saveSettings(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $validated = $request->validate([
            'enabled' => 'boolean',
            'frequency' => 'in:daily,weekly,monthly',
            'ai_prompt' => 'nullable|string|max:500',
            'text_template' => 'nullable|string|max:2000',
            'link_type' => 'in:schedule,registration',
            'layout' => 'in:grid,list',
            'send_day' => 'integer|min:0|max:31',
            'send_hour' => 'integer|min:0|max:23',
            'use_screen_capture' => 'boolean',
            'recipient_emails' => 'nullable|string|max:1000',
        ]);

        // Merge with existing settings to preserve defaults
        $currentSettings = $role->graphic_settings;
        $newSettings = array_merge($currentSettings, $validated);

        $role->graphic_settings = $newSettings;
        $role->save();

        return response()->json([
            'success' => true,
            'message' => __('messages.settings_saved'),
            'settings' => $role->graphic_settings,
        ]);
    }

    public function sendTestEmail(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        // Require Enterprise plan for sending test emails
        if (! $role->isEnterprise()) {
            return response()->json([
                'success' => false,
                'message' => __('messages.enterprise_feature_email_scheduling'),
            ], 403);
        }

        // Use the authenticated user's email
        $user = auth()->user();
        if (! $user || empty($user->email)) {
            return response()->json([
                'success' => false,
                'message' => __('messages.email_required'),
            ], 400);
        }

        try {
            $service = new GraphicEmailService;
            $service->sendGraphicEmail($role, $user->email);

            return response()->json([
                'success' => true,
                'message' => __('messages.test_email_sent'),
            ]);
        } catch (\Exception $e) {
            \Log::error('Failed to send graphic test email: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => __('messages.email_failed').': '.$e->getMessage(),
            ], 500);
        }
    }

    public function generateGraphicData(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();
        $layout = $request->get('layout', 'grid');
        $directRegistration = $request->boolean('direct');

        // Validate layout parameter
        if (! in_array($layout, ['grid', 'list'])) {
            $layout = 'grid';
        }

        // Get the next 10 events
        $events = Event::with(['roles', 'tickets', 'venue'])
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
            return response()->json(['error' => __('messages.no_events_found')], 404);
        }

        $graphicSettings = $role->graphic_settings;

        // Use request parameters if provided, otherwise fall back to saved settings
        $useScreenCapture = $request->has('use_screen_capture')
            ? $request->boolean('use_screen_capture')
            : ($graphicSettings['use_screen_capture'] ?? false);

        if (config('services.capturekit.key') && $role->isEnterprise() && $useScreenCapture) {
            $url = $role->getGuestUrl($role->subdomain).'?embed=true&graphic=true';
            $url = 'https://api.capturekit.dev/capture?&access_key='.config('services.capturekit.key').'&viewport_width=950&full_page=true&url='.urlencode($url);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 60, // 60 second timeout
                CURLOPT_CONNECTTIMEOUT => 30, // 30 second connection timeout
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'EventSchedule/1.0',
                CURLOPT_HTTPHEADER => [
                    'Accept: image/png,image/*,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                ],
            ]);

            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                \Log::error('CaptureKit cURL error: '.$error);

                return redirect()->back()->with('error', 'Failed to generate graphic: '.$error);
            }

            if ($httpCode !== 200) {
                \Log::error('CaptureKit HTTP error: '.$httpCode);

                return redirect()->back()->with('error', 'Failed to generate graphic: HTTP '.$httpCode);
            }

            if (empty($imageData)) {
                \Log::error('CaptureKit returned empty response');

                return redirect()->back()->with('error', 'Failed to generate graphic: Empty response');
            }
        } else {
            // Use the service to generate the graphic with the specified layout
            $generator = new EventGraphicGenerator($role, $events, $layout, $directRegistration);
            $imageData = $generator->generate();
        }

        // Convert image data to base64 for display
        $imageBase64 = base64_encode($imageData);

        // Get text template - use request parameter if provided, otherwise fall back to saved settings
        $textTemplate = $request->has('text_template')
            ? $request->get('text_template', '')
            : ($graphicSettings['text_template'] ?? '');

        // Generate event text content
        $eventText = $this->generateEventText($role, $events, $directRegistration, $textTemplate);

        // Process text through AI if ai_prompt is set (Pro feature)
        // Use request parameter if provided, otherwise fall back to saved settings
        $aiPrompt = $request->has('ai_prompt')
            ? trim($request->get('ai_prompt', ''))
            : trim($graphicSettings['ai_prompt'] ?? '');
        if ($role->isPro() && ! empty($aiPrompt) && config('services.google.gemini_key')) {
            $aiProcessedText = $this->processTextWithAI($eventText, $aiPrompt);
            if ($aiProcessedText) {
                $eventText = $aiProcessedText;
            }
        }

        return response()->json([
            'image' => $imageBase64,
            'text' => $eventText,
            'download_url' => route('event.download_graphic', ['subdomain' => $role->subdomain, 'layout' => $layout]),
        ]);
    }

    public function downloadGraphic(Request $request, $subdomain)
    {
        $role = Role::subdomain($subdomain)->firstOrFail();

        $layout = $request->get('layout', 'grid');
        $directRegistration = $request->boolean('direct');

        // Validate layout parameter
        if (! in_array($layout, ['grid', 'list'])) {
            $layout = 'grid';
        }

        // Get the next 10 events
        $events = Event::with(['roles', 'tickets', 'venue'])
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
            return redirect()->back()->with('error', __('messages.no_events_found'));
        }

        $graphicSettings = $role->graphic_settings;
        $useScreenCapture = $graphicSettings['use_screen_capture'] ?? false;

        if (config('services.capturekit.key') && $role->isEnterprise() && $useScreenCapture) {
            $url = $role->getGuestUrl($role->subdomain).'?embed=true&graphic=true';
            $url = 'https://api.capturekit.dev/capture?&access_key='.config('services.capturekit.key').'&viewport_width=950&full_page=true&url='.urlencode($url);

            $ch = curl_init();
            curl_setopt_array($ch, [
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_TIMEOUT => 60, // 60 second timeout
                CURLOPT_CONNECTTIMEOUT => 30, // 30 second connection timeout
                CURLOPT_SSL_VERIFYPEER => true,
                CURLOPT_USERAGENT => 'EventSchedule/1.0',
                CURLOPT_HTTPHEADER => [
                    'Accept: image/png,image/*,*/*;q=0.8',
                    'Accept-Language: en-US,en;q=0.5',
                ],
            ]);

            $imageData = curl_exec($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            $error = curl_error($ch);
            curl_close($ch);

            if ($error) {
                \Log::error('CaptureKit cURL error: '.$error);

                return redirect()->back()->with('error', 'Failed to generate graphic: '.$error);
            }

            if ($httpCode !== 200) {
                \Log::error('CaptureKit HTTP error: '.$httpCode);

                return redirect()->back()->with('error', 'Failed to generate graphic: HTTP '.$httpCode);
            }

            if (empty($imageData)) {
                \Log::error('CaptureKit returned empty response');

                return redirect()->back()->with('error', 'Failed to generate graphic: Empty response');
            }
        } else {
            // Use the service to generate the graphic with the specified layout
            $generator = new EventGraphicGenerator($role, $events, $layout, $directRegistration);
            $imageData = $generator->generate();
        }

        // Generate filename based on layout
        $filename = $role->subdomain.'-upcoming-events.png';

        // Return the image as a response
        return response($imageData)
            ->header('Content-Type', 'image/png')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"")
            ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    private function generateEventText($role, $events, $directRegistration = false, $template = null)
    {
        $text = '';

        // Use provided template or default
        if (empty($template)) {
            $template = $this->getDefaultTemplate();
        }

        foreach ($events as $event) {
            $text .= $this->parseTemplate($template, $event, $role, $directRegistration);
            $text .= "\n";
        }

        return $text;
    }

    private function getDefaultTemplate()
    {
        return "*{day_name}* {date_dmy} | {time}\n*{event_name}*:\n{venue} | {city}\n{url}";
    }

    private function parseTemplate($template, $event, $role, $directRegistration)
    {
        // Set Carbon locale for translated date formats
        $locale = $role->language_code ?? 'en';
        \Carbon\Carbon::setLocale($locale);

        $startDate = $event->getStartDateTime(null, true);
        $endDate = $event->getEndDateTime(null, true);

        // Determine time format based on role's 24h setting
        $timeFormat = $role->use_24_hour_time ? 'H:i' : 'g:i A';

        // Build the URL
        $eventUrl = $event->getGuestUrl($role->subdomain, null, true);
        if ($directRegistration && $event->registration_url) {
            if (str_contains($eventUrl, '?')) {
                $eventUrl = str_replace('?', '/?', $eventUrl);
            } else {
                $eventUrl .= '/';
            }
        }

        // Build replacements array
        $replacements = [
            // Date/Time variables
            '{day_name}' => $startDate->translatedFormat('l'),
            '{day_short}' => $startDate->translatedFormat('D'),
            '{date_dmy}' => $startDate->format('j/n'),
            '{date_mdy}' => $startDate->format('n/j'),
            '{date_full_dmy}' => $startDate->format('d/m/Y'),
            '{date_full_mdy}' => $startDate->format('m/d/Y'),
            '{month}' => $startDate->format('n'),
            '{month_name}' => $startDate->translatedFormat('F'),
            '{month_short}' => $startDate->translatedFormat('M'),
            '{day}' => $startDate->format('j'),
            '{year}' => $startDate->format('Y'),
            '{time}' => $startDate->format($timeFormat),
            '{end_time}' => $endDate ? $endDate->format($timeFormat) : '',
            '{duration}' => $event->duration ?? '',

            // Event variables
            '{event_name}' => $event->translatedName(),
            '{description}' => $event->translatedDescription() ?? '',
            '{url}' => $eventUrl,

            // Venue variables
            '{venue}' => $event->venue ? ($event->venue->translatedName() ?? '') : '',
            '{city}' => $event->venue ? ($event->venue->translatedCity() ?? '') : '',
            '{address}' => $event->venue ? ($event->venue->address1 ?? '') : '',
            '{state}' => $event->venue ? ($event->venue->state ?? '') : '',
            '{country}' => $event->venue ? ($event->venue->country ?? '') : '',

            // Ticket variables
            '{currency}' => $event->ticket_currency_code ?? '',
            '{price}' => $this->getPrice($event),
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function getPrice($event)
    {
        if (! $event->tickets || $event->tickets->isEmpty()) {
            return '';
        }

        $prices = $event->tickets->pluck('price')->filter(function ($price) {
            return $price !== null;
        });

        if ($prices->isEmpty()) {
            return '';
        }

        // Check if all tickets are free
        $allFree = $prices->every(function ($price) {
            return $price === 0;
        });

        if ($allFree) {
            return __('messages.free');
        }

        // Return lowest price
        return $prices->min();
    }

    private function processTextWithAI($text, $aiPrompt)
    {
        try {
            $prompt = "Transform the following event list text according to this instruction: \"{$aiPrompt}\"\n\nEvent List:\n{$text}\n\nReturn only the transformed text as a JSON string with a single key 'text'.";

            $response = GeminiUtils::sendPrompt($prompt);

            if ($response && isset($response[0]['text'])) {
                return $response[0]['text'];
            }

            return null;
        } catch (\Exception $e) {
            \Log::error('Failed to process text with AI: '.$e->getMessage());

            return null;
        }
    }
}
