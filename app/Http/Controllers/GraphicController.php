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
            return response()->json(['error' => __('messages.no_events_found')], 404);
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

        // Convert image data to base64 for display
        $imageBase64 = base64_encode($imageData);

        // Generate event text content
        $eventText = $this->generateEventText($role, $events, $directRegistration);

        // Process text through AI if ai_prompt is set (Pro feature)
        $aiPrompt = trim($graphicSettings['ai_prompt'] ?? '');
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

    private function generateEventText($role, $events, $directRegistration = false)
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
